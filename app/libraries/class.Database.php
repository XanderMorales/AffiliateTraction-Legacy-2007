<?php
//////////////////////////////////////////////////
// DATABASE CONSTANTS in class.Database.php
define("DBHOST", "rhino.affiliatetraction.com");
define("DBNAME", "offers");
define("DBUSERNAME", "webdev");
define("DBPASSWORD", "fdsfdsfsd");

/*
 * Database class that wraps calls to the MySQL database via PDO
 */
class AT_Database {
    static private $_dbh = NULL;
    static private $_mysqlErrorInfo = NULL;

    /* Call a Stored Proceedure
     * <p>array callStoredProceedure( string statement [, array parameters [, array datatypes]] )
     * <p>example: Database::callStoredProceedure('stored_proceedure(?,?,?,?)', $paramArray, $datatypeArray);
     * 
     * @param storedProceedure the SQL call to the stored proceedure
     * @param paramArray an array of the parameter to pass to the query
     * @param datatypeArray an array of the data types in the paramArray
     * @return an array of associative arrays of the query results, or false on error
     * @throws PDOException
     */
    static function callStoredProceedure($storedProceedure, $paramArray = NULL, $datatypeArray = NULL)
    {
        self::init();

        $sth = self::$_dbh->prepare('CALL '.$storedProceedure);
        if( isset( $paramArray ) )
            self::bind_params($sth, $paramArray, $datatypeArray);

        $sth->execute();
        if( self::$_dbh->errorCode() <> '00000' ){
            self::$_mysqlErrorInfo = self::$_dbh->errorInfo();
            throw new PDOException("Database::callStoredProceedure() error: " . self::$_mysqlErrorInto[2]);
        }
        
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Call a SQL Prepared Statement
     * <p>array callSQL( string statement [, array parameters [, array datatypes]] )
     * <p>example: Database::callSQL('SELECT * FROM tablename WHERE a=? && b=? && c=? && d=?', $paramArray, $datatypeArray);
     * 
     * @param sql the SQL query
     * @param paramArray an array of the parameter to pass to the query
     * @param datatypeArray an array of the data types in the paramArray
     * @return an array of associative arrays of the query results, or false on error
     * @throws PDOException
     */
    static function callSQL($sql, $paramArray = NULL, $datatypeArray = NULL)
    {
        self::init();
        
        $sth = self::$_dbh->prepare($sql);
        
        if( isset( $paramArray ) )
            self::bind_params($sth, $paramArray, $datatypeArray);
        
        $sth->execute();
        if( self::$_dbh->errorCode() <> '00000' ){
            self::$_mysqlErrorInfo = self::$_dbh->errorInfo();
            throw new PDOException("Database::callSQL() error: " . self::$_mysqlErrorInto[2]);
        }
        
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /*
     * Gets MySQL Error Code [0] and Error Message [2]
     * 
     * @return an array of error code[0] and message[2]
     */
    static function errorInfo()
    {
        return self::$_mysqlErrorInfo;   
    }
	
	static function lastInsertId()
	{
		return self::$_dbh->lastInsertID();
	}

    /*
     * Initialize Database Connection
     * @throws PDOException
     */
    static private function init()
    {
		self::$_mysqlErrorInfo = NULL;
		
        try
        {
            self::$_dbh = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME, DBUSERNAME, DBPASSWORD);
        }
        catch( PDOException $e )
        {
            throw new PDOException("Database::init() error: " . $e);
        }
    }

    /* 
     * Bind values to the SQL
     * @throws PDOException
     */
    static private function bind_params( &$sth, &$paramArray, &$datatypeArray )
    {
        if( isset( $paramArray ) )
        {
            $arrayCount = count($paramArray);
            for( $i = 0 ; $i < $arrayCount ; $i++ )
            {
                $param = (isset($paramArray[$i]) ? $paramArray[$i] : NULL);
                $datatype = (isset($datatypeArray[$i]) ? $datatypeArray[$i] : NULL);
                
                if( !$sth->bindValue(($i+1), $param, $datatype) )
                    throw new PDOException("Database::bind_params() error: binding failure.");
            }
        }
    }
    
    ////////////////////////////////////////////////////
    // Public Methods
    ////////////////////////////////////////////////////
    private $dbhandle;
    
    function __construct($host, $dbname, $dbusername, $dbpassword)
    {
        try
        {
            $this->dbhandle = new PDO('mysql:host='.$host.';dbname='.$dbname, $dbusername, $dbpassword);
        }
        catch( PDOException $e )
        {
            throw new PDOException("Database::init() error: " . $e);
        }
    }
    
    /* Call a Stored Proceedure
     * <p>array callStoredProceedure( string statement [, array parameters [, array datatypes]] )
     * <p>example: Database::callStoredProceedure('stored_proceedure(?,?,?,?)', $paramArray, $datatypeArray);
     * 
     * @param storedProceedure the SQL call to the stored proceedure
     * @param paramArray an array of the parameter to pass to the query
     * @param datatypeArray an array of the data types in the paramArray
     * @return an array of associative arrays of the query results, or false on error
     * @throws PDOException
     */
    function callProceedure($storedProceedure, $paramArray = NULL, $datatypeArray = NULL)
    {
        $sth = $this->dbhandle->prepare('CALL '.$storedProceedure);
        if( isset( $paramArray ) )
            Database::bind_params($sth, $paramArray, $datatypeArray);

        $sth->execute();
        if( $this->dbhandle->errorCode() <> '00000' ){
            $_mysqlErrorInfo = $this->dbhandle->errorInfo();
            throw new PDOException("Database::callStoredProceedure() error: " . $_mysqlErrorInto[2]);
        }
        
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Call a SQL Prepared Statement
     * <p>array callSQL( string statement [, array parameters [, array datatypes]] )
     * <p>example: Database::callSQL('SELECT * FROM tablename WHERE a=? && b=? && c=? && d=?', $paramArray, $datatypeArray);
     * 
     * @param sql the SQL query
     * @param paramArray an array of the parameter to pass to the query
     * @param datatypeArray an array of the data types in the paramArray
     * @return an array of associative arrays of the query results, or false on error
     * @throws PDOException
     */
    function callQuery($sql, $paramArray = NULL, $datatypeArray = NULL)
    {
        $sth = $this->dbhandle->prepare($sql);
        
        if( isset( $paramArray ) )
            Database::bind_params($sth, $paramArray, $datatypeArray);
        
        $sth->execute();
        if( $this->dbhandle->errorCode() <> '00000' ){
            $_mysqlErrorInfo = $this->dbhandle->errorInfo();
            throw new PDOException("Database::callSQL() error: " . $_mysqlErrorInto[2]);
        }
        
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

}
?>

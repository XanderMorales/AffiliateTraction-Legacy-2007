<?php
require_once('AbstractService.php');
require_once('IService.php');

class LeadsDatabase extends AbstractService implements IService
{
    private $_host, $_database, $_user, $_password;
    private $_referingURL;
    
    private $_firstName, $_lastName;
    private $_businessName;
    private $_email;
    private $_workPhone;
    private $_subject, $_notes;
    private $_leadOrigin, $_referingUrl, $_timestamp;
    private $_miscText;
    
    public function __construct($host, $database, $user, $password)
    {
        parent::__construct();

        $this->_host        = $host;
        $this->_database    = $database;
        $this->_user        = $user;
        $this->_password    = $password;
        
        $this->_firstName       = '';
        $this->_lastName        = '';
        $this->_businessName    = '';
        $this->_email           = '';
        $this->_workPhone       = '';
        $this->_subject         = '';
        $this->_notes           = '';
        $this->_leadOrigin      = '';
        $this->_referingURL     = '';
        $this->_timestamp       = time();
        $this->_miscText        = '';
    }
    
    public function Send()
    {
        $query = "
            INSERT INTO affiliatetraction_leads (
                    date, name, company, phone, email, regarding, comments, query_string,
                    lead_form, form_time, referring_url, server_name, request_uri, http_user_agent,
                    server_addr, remote_addr, request_method
                ) VALUES (NOW(),?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                
        $params = array( $this->_firstName.' '.$this->_lastName, $this->_businessName, $this->_workPhone, $this->_email,
            $this->_subject, $this->_notes, ' ', $this->_leadOrigin, $this->_timestamp, $this->_referingUrl,
            $_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI'], $_SERVER['HTTP_USER_AGENT'],
            $_SERVER['SERVER_ADDR'], $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_METHOD']);
        print_r($params);
        $dataTypes = array(PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_STR,
            PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_INT, PDO::PARAM_INT, PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_STR,
            PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_STR);
        
        try
        {
            $dbhandle = new PDO('mysql:host='.$this->_host.';dbname='.$this->_database, $this->_user, $this->_password);
            
            $sth = $dbhandle->prepare($query);
            
            $arrayCount = count($params);
            for( $i = 0 ; $i < $arrayCount ; $i++ )
            {
                $param = (isset($params[$i]) ? $params[$i] : NULL);
                $datatype = (isset($dataTypes[$i]) ? $dataTypes[$i] : NULL);
                
                if( !$sth->bindValue(($i+1), $param, $datatype) )
                    throw new PDOException("Database::bind_params() error: binding failure.");
            }
            
            $sth->execute();
            
            if( $dbhandle->errorCode() <> '00000' )
                throw new PDOException("Database::callSQL() error: " . $dbhandle->errorInfo());
        }
        catch(PDOException $e)
        {
            //echo 'PDO Exception: ' . $e->getMessage();
            return false;
        }
    }
    
    ////////////////////////////////////////////////////////////////////
    // Implement IService Interface
    ////////////////////////////////////////////////////////////////////
    
    /**
    * @param string $name
    */
    function setFirstName($name)
    {
        $this->_firstName = (string)$name;
    }
    
    /**
    * @param string $name
    */
    function setLastName($name)
    {
        $this->_lastName = (string)$name;
    }
    
    /**
    * @param string $name
    */
    function setBusinessName($name)
    {
        $this->_businessName = (string)$name;
    }
    
    /**
    * @param string $email
    */
    function setEmail($email)
    {
        $this->_email = (string)$email;
    }
    
    /**
    * @param string $phone
    */
    function setWorkPhone($phone)
    {
        $this->_workPhone = (string)$phone;
    }
    
    /**
    * @param string $phone
    */
    function setWorkPhoneExt($phone)
    {
        //$this->_workPhone = (string)$phone;
    }
    
    /**
    * @param string $subject
    */
    function setSubject($subject)
    {
        $this->_subject = (string)$subject;
    }
    
    /**
    * @param string $notes
    */
    function setNotes($notes)
    {
        $this->_notes = (string)$notes;
    }
    
    /**
    * @param string $name
    */
    function setLeadOrigin($name)
    {
        $this->_leadOrigin = (string)$name;   
    }
    
    /**
    * @param string $url
    */
    function setReferingURL($url)
    {
        $this->_referingUrl = (string)$url;
    }
    
    /**
    * @param int $timestamp
    */
    function setTimestamp($timestamp)
    {
        $this->_timestamp = (int)$timestamp;
    }
    
    /**
    * Unsupported
    * 
    * @param string $text
    */
    function setMisc($text)
    {
        $this->_miscText = (string)$text;
    }

}

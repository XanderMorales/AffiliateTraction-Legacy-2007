<?php
require_once('app/libraries/class.Database.php');

/*
 * Database class that wraps calls to the MySQL database via PDO
 */
class Network {

    public function __construct(){}
    
	///////////////////////////////////////////////////////////
	// Public Interface
	///////////////////////////////////////////////////////////

	public function listAll()
	{
		$query = "SELECT network_id, network_name, network_active FROM networks ORDER BY network_active DESC, network_name";
		
		$response = $this->dbQuery($query);
        
        return $response;
	}

    public function listActive()
    {
        $query = "SELECT networks.network_id, networks.network_name, COUNT(networks.network_id) AS merchant_count
                        FROM networks
                        INNER JOIN merchant_networks ON (networks.network_id = merchant_networks.network_id)
                        WHERE networks.network_active = TRUE
                        GROUP BY networks.network_id
                        ORDER BY networks.network_name";

        return $this->dbQuery($query);
    }

    public function load($id)
    {
		$query = "SELECT * FROM networks WHERE network_id = ? LIMIT 1";
		
		$response = $this->dbQuery($query, array($id), array( PDO::PARAM_INT ));
        
        return $response[0];
    }

    public function create($name)
    {
		$query = "INSERT INTO networks (network_active, network_name) VALUES (0, ?)";
		
		$response = $this->dbQuery($query, array($name), array( PDO::PARAM_STR ));
		
        return AT_Database::lastInsertId();
    }

    public function update($id, $name, $active, $signup, $login)
    {
		$query = "UPDATE networks SET
						network_name=?,
						network_active=?,
						network_signup_link=?,
						network_login_link=?
					WHERE network_id=? LIMIT 1";
		
		$response = $this->dbQuery($query,
				array($name, $active, $signup, $login, $id),
				array( PDO::PARAM_STR, PDO::PARAM_INT, PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_INT ));
		
        return $id;
    }
    
    public function delete($id)
    {
		$query = "DELETE FROM networks WHERE network_id=? LIMIT 1";
		
		$this->dbQuery($query, array($id), array( PDO::PARAM_INT ));
		
		// MySQL trigger deletes merchant_networks dependents
		
		return true;
    }
	
    public function addMerchant($id, $merchantID, $signup, $login)
    {
		$this->removeMerchant($id, $merchantID);
		$query = "INSERT INTO merchant_networks
					(network_id, merchant_id, merchant_signup_link, merchant_login_link)
					VALUES (?,?,?,?)";
		
		$this->dbQuery( $query,
				array( $id, $merchantID, $signup, $login),
				array(  PDO::PARAM_INT, PDO::PARAM_INT, PDO::PARAM_STR, PDO::PARAM_STR ));
		
		return $id;
    }
    
    public function removeMerchant($id, $merchantID)
    {
		$query = "DELETE FROM merchant_networks WHERE network_id=? AND merchant_id=?";
		
		$this->dbQuery($query, array($id, $merchantID), array( PDO::PARAM_INT, PDO::PARAM_INT ));
		
		return $id;
    }
    
    ////////////////////////////////////////////////////
    // Private Methods
    ////////////////////////////////////////////////////
    
	private function dbQuery($query, $params=null, $paramTypes=null)
	{
		$response = null;
		try
		{
			$response = AT_Database::callSQL($query, $params, $paramTypes);
		}
		catch(PDOException $e){}
        
        return $response;
	}
}
?>

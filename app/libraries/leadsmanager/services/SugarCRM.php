<?php
require_once('AbstractService.php');
require_once('IService.php');

class SugarCRM extends AbstractService implements IService
{
    private $_subject;
    
    public function __construct($campaignID, $userID, $leadType)
    {
        parent::__construct();
        
        $this->addParam('entryPoint', 'WebToLeadCapture');
        $this->addParam('campaign_id', $campaignID);
        $this->addParam('assigned_user_id', $userID);
        $this->addParam('lead_type_c', $leadType);
        
        $this->_subject = '';
    }
    
    public function Send()
    {
        //echo $this->buildQueryString();
        //return;
        $ch = curl_init();
        // set URL and other appropriate options
        curl_setopt( $ch, CURLOPT_URL, 'http://crm.affiliatetraction.com/index.php' );
        curl_setopt( $ch, CURLOPT_HEADER, FALSE );
        curl_setopt( $ch, CURLOPT_POST, TRUE );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $this->buildQueryString() );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        // grab URL and pass it to the browser
        curl_exec( $ch );
        // close curl resource, and free up system resources
        curl_close( $ch );
    }
    
    ////////////////////////////////////////////////////////////////////
    // Implement IService Interface
    ////////////////////////////////////////////////////////////////////
    
    /**
    * @param string $name
    */
    function setFirstName($name)
    {
        $this->addParam('first_name', (string)$name);
    }
    
    /**
    * This field is required.
    * @param string $name   if null, then 'Not Available'
    */
    function setLastName($name)
    {
        if( !isset($name) || $name == '' )
            $name = 'Not Available';
        $this->addParam('last_name', (string)$name);
    }
    
    /**
    * @param string $name
    */
    function setBusinessName($name)
    {
         $this->addParam('account_name', (string)$name);
    }
    
    /**
    * @param string $email
    */
    function setEmail($email)
    {
        $this->addParam('webtolead_email1', (string)$email);
    }
    
    /**
    * @param string $phone
    */
    function setWorkPhone($phone)
    {
        $this->addParam('phone_work', (string)$phone);
    }
    
    /**
    * @param string $ext
    */
    function setWorkPhoneExt($ext)
    {
        $this->addParam('phone_ext_c', (string)$ext);
    }

    /**
    * @param string $subject
    */
    function setSubject($subject)
    {
        $this->_subject = (string)$subject . "\n\r";
    }
    
    function setNotes($notes)
    {
        $this->addParam('quick_notes_c', $this->_subject . (string)$notes);
    }    
    
    /**
    * Unsupported
    * 
    * @param string $name
    */
    function setLeadOrigin($name)
    {
        //$this->_leadOrigin = (string)$name;
    }
    
    /**
    * Unsupported
    * 
    * @param string $url
    */
    function setReferingURL($url)
    {
        //$this->_referingUrl = (string)$url;
    }
    
    /**
    * Unsupported
    * 
    * @param int $timestamp
    */
    function setTimestamp($timestamp)
    {
        //$this->_timestamp = (int)$timestamp;
    }
    
    /**
    * Unsupported
    * 
    * @param string $text
    */
    function setMisc($text)
    {
        //$this->_miscText = (string)$text;
    }
}

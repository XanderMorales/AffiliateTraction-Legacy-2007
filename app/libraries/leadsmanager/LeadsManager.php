<?php
require_once('services/IService.php');

/**
* Manages leads services that implement IService.
* Add services, then batch call IService inferface and execute IService::Save().
* <code>
* $lead = new LeadsManager();
* $lead->setFirstName($firstName);
* $lead->setLastName($lastName);
* $lead->set...
* $lead->addService(new EmailDelivery($emailTo));
* $lead->addService(new SugarCRM($campaign_id, $assigned_user_id, $lead_type_c));
* $lead->Save();
* </code>
*/
final class LeadsManager implements IService
{
    private $_services;
    
    private $_firstName, $_lastName;
    private $_businessName;
    private $_email;
    private $_workPhone, $_workPhoneExt;
    private $_subject, $_notes;
    private $_leadOrigin, $_referingUrl, $_timestamp;
    private $_miscText;
    
    //////////////////////////////////////////////////////////
    // Public Interface
    //////////////////////////////////////////////////////////
    
    public function __construct()
    {
        $this->_services = new ArrayObject();
        
        $this->_firstName       = '';
        $this->_lastName        = '';
        $this->_businessName    = '';
        $this->_email           = '';
        $this->_workPhone       = '';
        $this->_workPhoneExt    = '';
        $this->_subject         = '';
        $this->_notes           = '';
        $this->_leadOrigin      = '';
        $this->_referingURL     = '';
        $this->_timestamp       = time();
        $this->_miscText        = '';
    }
    
    /**
    * Add a service to the collection.
    * @param IService $service
    */
    public function addService(IService $service)
    {
        $this->_services->append($service);
    }
    
    /**
    * Send the lead to all services.
    * @override
    */
    public function Send()
    {
        // Loop through Services Collection
        $service = $this->_services->getIterator();
        while( $service->valid() )
        {
            // Add params via IService interface
            $service->current()->setFirstName(      $this->_firstName );
            $service->current()->setLastName(       $this->_lastName );
            $service->current()->setBusinessName(   $this->_businessName );
            $service->current()->setEmail(          $this->_email );
            $service->current()->setWorkPhone(      $this->_workPhone );
            $service->current()->setWorkPhoneExt(   $this->_workPhoneExt );
            $service->current()->setSubject(        $this->_subject );
            $service->current()->setNotes(          $this->_notes );
            $service->current()->setLeadOrigin(     $this->_leadOrigin );
            $service->current()->setReferingURL(    $this->_referingUrl );
            $service->current()->setTimestamp(      $this->_timestamp );
            $service->current()->setMisc(           $this->_miscText );
            
            try {
                // Call IService::Save()
                $service->current()->Send();
            } catch(Exception $e) {}

            $service->next();
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
    function setWorkPhoneExt($ext)
    {
        $this->_workPhoneExt = (string)$ext;
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
    * Set additional text that a service can optionally include.
    * 
    * @param string $text
    */
    function setMisc($text)
    {
        $this->_miscText = (string)$text;
    }
}

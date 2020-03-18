<?php
require_once('app/libraries/class.Database.php');
require_once('app/libraries/class.Merchant.php');
require_once('app/libraries/class.Network.php');
require_once('phpmailer/class.phpmailer.php');
require_once('app/libraries/leadsmanager/LeadsManager.php');
require_once('app/libraries/leadsmanager/services/SugarCRM.php');

class Publishers_Offers_Model extends Model
{
    private $merchantClass;
    private $networkClass;
    
    private $cache_listActiveNetworks;
    private $cache_searchMerchantsInNetworks;
    private $cache_merchantOfferSummary;
    private $cache_merchantDetails;
    private $cache_networksByMerchant;
    
    public $searchMerchantsInNetworks_network_array;
    public $merchantOfferSummary_merchant_id;
    public $merchantDetail_merchant_id;
    public $networksBy_merchant_id;
    public $popup_text_title;
    public $popup_text_id;
    
    public $messages_array;
    
//////////////////////////////////////////////////////////////////////////////////////////
// PUBLIC METHODS
//////////////////////////////////////////////////////////////////////////////////////////
 
	public function __construct()
	{
		parent::__construct();
        
        $this->merchantClass = new Merchant();
        $this->networkClass = new Network();
        
        $this->searchMerchantsInNetworks_network_array = array('id'=>'all');
        $this->merchantOfferSummary_merchant_id = 0;
        $this->merchantDetail_merchant_id = 0;
        $this->emailMerchantList_data = array();
        $this->popup_text_title = 'No Title';
        $this->popup_text_id = 0;
        
        $this->messages_array = array();
	}
	
    /**
    * Return an array of active networks ordered by Name.
    * 
    * @param    bool    cache the query for later use
    * @return   array   network_id, network_name, merchant_count
    */
    public function listActiveNetworks($cache=true)
    {
        if( $cache && $this->cache_listActiveNetworks )
            return $this->cache_listActiveNetworks;

        $response = $this->networkClass->listActive();
        //print_r($response);
        if( $cache )
            $this->cache_listActiveNetworks = $response;
         
        return $response;
    }
    
    /**
    * Search for Merchants by Network ID
    * 
    * @param    bool    cache the query for later use
    * @return   array   merchant_id, merchant_name, merchant_cps, merchant_avgsales
    */
    public function searchMerchantsInNetworks($cache=true)
    {
        if( $cache && $this->cache_searchMerchantsInNetworks )
            return $this->cache_searchMerchantsInNetworks;   

        // array('id'=>$id, 'ppc'=>$ppc, 'coupons'=>$coupons, 'freebies'=>$freebies, 'datafeed'=>$datafeed);
        //print_r($this->searchMerchantsInNetworks_network_array);
        $response = $this->merchantClass->listByNetwork(
            $this->searchMerchantsInNetworks_network_array['id'],
            $this->searchMerchantsInNetworks_network_array
        );
        
        if( $cache )
            $this->cache_searchMerchantsInNetworks = $response;
        
        return $response;
    }
    
    /**
    * Search for Merchant Summary by Merchant ID
    * 
    * @param    bool    cache the query for later use
    * @return   array   merchant_id, merchant_name, merchant_top10epc, merchant_convrate, merchant_pay_frequency, merchant_text
    */
    public function getMerchantOfferSummary($cache=true)
    {
        if( $cache && $this->cache_merchantOfferSummary )
            return $this->cache_merchantOfferSummary;   

        $response = $this->merchantClass->getSummary( $this->merchantOfferSummary_merchant_id );
        
        if( $response )
        {
            $response['merchant_datafeed']      = $this->yesNo(   $response['merchant_datafeed']);
            $response['merchant_top10epc']      = $this->valueOrTBD(    $response['merchant_top10epc'], '$');
            $response['merchant_convrate']      = $this->valueOrTBD(    $response['merchant_convrate'], '', '%');
        }

        if( $cache )
            $this->cache_merchantOfferSummary = $response;
            
        return $response;
    }
    
    public function getMerchantDetails($cache=true)
    {
        if( $cache && $this->cache_merchantDetails )
            return $this->cache_merchantDetails;
            
        $response = $this->merchantClass->load($this->merchantDetail_merchant_id);

        $response['merchant_avgsales']          = $this->valueOrTBD(  $response['merchant_avgsales'], '$');
        $response['merchant_convrate']          = $this->valueOrTBD(  $response['merchant_convrate'], '$');
        $response['merchant_cps']               = $this->valueOrTBD(  $response['merchant_cps'], '', '%');
        $response['merchant_cookieduration']    = $this->valueOrTBD(  $response['merchant_cookieduration'], '', ' Days');
        $response['merchant_cpaflat']           = $this->valueOrTBD(  $response['merchant_cpaflat'], '$', '.00');
        $response['merchant_pay_threshold']     = $this->valueOrTBD(  $response['merchant_pay_threshold'], '$', '.00');
        $response['merchant_pay_frequency']     = $this->daysToWords( $response['merchant_pay_frequency']);
        $response['merchant_convrate']          = $this->valueOrTBD(  $response['merchant_convrate'], '', '%');
        $response['merchant_top10epc']          = $this->valueOrTBD(  $response['merchant_top10epc']);
        $response['merchant_ppcbidding']        = $this->yesNo(       $response['merchant_ppcbidding']);
        $response['merchant_datafeed']          = $this->yesNo(       $response['merchant_datafeed']);

        if( $cache )
            $this->cache_merchantDetails = $response;
            
        return $response;
    }
    
    public function listNetworksByMerchant($cache=true)
    {
        if( $cache && $this->cache_networksByMerchant )
            return $this->cache_networksByMerchant;
            
         $response = $this->merchantClass->listNetworks($this->networksBy_merchant_id);      

        if( $cache )
            $this->cache_networksByMerchant = $response;
            
        return $response;
    }
    
    /**
    * Get Merchants by IDs in array or all Merchants if null.
    * 
    * @param mixed $id_array array of Merchant IDs or null
    */
    public function getSelectedMerchants($id_array=null)
    {
        $query = "SELECT * FROM merchants
                        LEFT JOIN merchant_texts ON (merchant_texts.merchant_text_id = merchants.merchant_description_id)
                        WHERE FALSE";
        
        if( is_array($id_array) )
        {
            foreach( $id_array as $id )
                if( is_numeric( $id ) )
                    $query .= " OR merchants.merchant_id = " . $id;
        }
        else
            $query .= " OR merchants.merchant_active = 1 ";
                        
        $query .= " ORDER BY merchants.merchant_name";
        //echo $query;
        $response = $this->dbQuery($query);
        
        for( $i=0 ; $i < count($response) ; $i++)
        {
            $response[$i]['merchant_avgsales']          = $this->valueOrTBD(  $response[$i]['merchant_avgsales'], '$');
            $response[$i]['merchant_convrate']          = $this->valueOrTBD(  $response[$i]['merchant_convrate'], '$');
            $response[$i]['merchant_cps']               = $this->valueOrTBD(  $response[$i]['merchant_cps'], '', '%');
            $response[$i]['merchant_cookieduration']    = $this->valueOrTBD(  $response[$i]['merchant_cookieduration'], '', ' Days');
            $response[$i]['merchant_cpaflat']           = $this->valueOrTBD(  $response[$i]['merchant_cpaflat'], '$', '.00');
            $response[$i]['merchant_pay_threshold']     = $this->valueOrTBD(  $response[$i]['merchant_pay_threshold'], '$', '.00');
            $response[$i]['merchant_pay_frequency']     = $this->daysToWords( $response[$i]['merchant_pay_frequency']);
            $response[$i]['merchant_convrate']          = $this->valueOrTBD(  $response[$i]['merchant_convrate'], '', '%');
            $response[$i]['merchant_top10epc']          = $this->valueOrTBD(  $response[$i]['merchant_top10epc']);
            $response[$i]['merchant_ppcbidding']        = $this->yesNo(       $response[$i]['merchant_ppcbidding']);
            $response[$i]['merchant_datafeed']          = $this->yesNo(       $response[$i]['merchant_datafeed']);
        }
        
        return $response;
    }
    
    public function emailMerchantList($str_firstName, $str_lastName, $str_email, $bool_updates, $campaign_id, $assigned_user_id, $lead_type_c, $array_offers)
    {
        if( !isset($array_offers) )
        {
            array_push($this->messages_array, '<h2 style="margin: 20px;text-align:center;">You need to select at least one merchant. Please try again.</h2>');
            return;
        }
        
        $merchants = $this->getSelectedMerchants($array_offers);
        $notes = $bool_updates ? 'Please send me updates on these offers'."\r\n\r\n" : 'Do not send me updates'."\r\n\r\n";
        foreach( $merchants as $merchant )
            $notes .= $merchant['merchant_name'] . "\r\n";
            
        $lead = new LeadsManager();

        $lead->setFirstName($str_firstName);
        $lead->setLastName($str_lastName);
        $lead->setEmail($str_email);
        $lead->setBusinessName($str_firstName.' '.$str_lastName);
        $lead->setSubject('Our Offers');
        $lead->setNotes($notes);
        $lead->addService(new SugarCRM($campaign_id, $assigned_user_id, $lead_type_c));
        
        $lead->Send();
        
        $mail = new PHPMailer();

        $mail->IsSMTP(); // send via SMTP
        $mail->Host = "localhost"; // SMTP servers
        $mail->Mailer   = "smtp";

        $mail->From = "info@affiliatetraction.com";
        $mail->FromName = "AffiliateTraction";
        $mail->AddAddress($str_email, $str_firstName.' '.$str_lastName);
        $mail->AddReplyTo('info@affiliatetraction.com');
        $mail->AddBCC('lead@affiliatetraction.com');

        $mail->IsHTML(true); // send as HTML
        $mail->Subject = "Merchant Offers List";
        $mail->Body = $this->buildEmailBody($str_firstName.' '.$str_lastName, $bool_updates, $merchants);

        if($mail->Send())
        {
            array_push($this->messages_array, '<h2 style="margin: 20px;text-align:center;">Your email has been sent to '. $_GET['_email'] .'</h2>');
        }
        else
        {
            array_push($this->messages_array, '<h2 style="margin: 20px;text-align:center;">'.$mail->ErrorInfo.'</h2>');
        }
        
    }
    
    public function popupInfo()
    {
        return array($this->popup_text_title, $this->merchantClass->loadText($this->popup_text_id));
    }
    
//////////////////////////////////////////////////////////////////////////////////////////
// PRIVATE METHODS
//////////////////////////////////////////////////////////////////////////////////////////
 
    private function dbQuery($query, $params=null, $dataTypes=null)
    {
        try
        {
            $networksArray = AT_Database::callSQL($query, $params, $dataTypes);
        }
        catch(PDOException $e)
        {
            return false;
        }
         
        return $networksArray;
    }
    
    private function valueOrTBD($value, $pre='', $post='')
    {
        if( $value == null )
            return 'TBD';
        return $pre . $value . $post;
    }

    private function daysToWords($days)
    {
        if( $days == null )
            return 'TBD';
        else if( $days == 7 )
            return 'Weekly';
        else if( $days == 30 )
            return 'Monthly';
        else
            return $days . " Days";
    }

    private function yesNo($value)
    {
        return $value ? 'Yes' : 'No';
    }

    private function buildEmailBody($name, $bool_updates, $merchantArray)
    {
        $email_message = '<html>
        <head>
        <title>AffiliateTraction - Merchant Offers List</title>
        <style type="text/css">
        <!--
        html,body {
            background-color: white; 
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px; 
            color: black;
        }

        h1, h2, h3, h4, h5, h6 { color: #339900; }

        th {
            color: #339900;
            text-align: right;
            border: none;
        }

        td {
            border: thin solid #CCC;
            background: #EEE;
            color: black;
            text-align: center;
            font-size: 11pt;
            font-weight: bold
        }

        p { color: black; }
        -->
        </style>
        </head>

        <body>
        <table width="600" align="center" border="0">
          <tr><th>
            <p align="left">Dear '. $name .',</p>
            <p align="left">Thank you for requesting this list of merchant offers.</p>'.
            ($bool_updates ? '<p align="left">You have requested that you be notified of updates to the following merchants.</p>' : '').
          '</th></tr>
        </table>';

        foreach( $merchantArray as $merchant )
            $email_message .= '
            <table width="600" border="2" align="center" cellpadding="10" cellspacing="0" bordercolor="#CCCCCC"><tr><th>
              <img width="140" height="40" src="http://www.affiliatetraction.com/media/images/merchantLogos/logo_'. $merchant['merchant_id'] .'.jpg" style="float: right;" />
              <h2 align="left">'. $merchant['merchant_name'] .'</h2>
              
                  <h3 align="left">Program Description:</h3>
                <p align="left">'. $merchant['merchant_text'] .'</p>
                <table width="580" border="1" cellpadding="5" cellspacing="5" bordercolor="#999999">
                  <tr>
                    <th>Average Sales:</th>  
                    <td>'. $merchant['merchant_avgsales'] .'</td>  
                    <th>Payout Threshold:</th>  
                    <td>'. $merchant['merchant_convrate'] .'</td>  
                  </tr>
                  <tr>
                    <th>CPS:</th>  
                    <td>'. $merchant['merchant_cps'] .'</td>  
                    <th>Cookie Duration:</th>  
                    <td>'. $merchant['merchant_cookieduration'] .'</td>  
                  </tr>
                  <tr>
                    <th>CPA/Flat:</th>  
                    <td>'. $merchant['merchant_cpaflat'] . ($merchant['merchant_cpaflat_tag'] ? ' - ' . $merchant['merchant_cpaflat_tag'] : '') .'</td>  
                    <th>Payout Frequency:</th>  
                    <td>'. $merchant['merchant_pay_frequency'] .'</td>  
                  </tr>
                  <tr>
                    <th>Conv Rate:</th>  
                    <td>'. $merchant['merchant_convrate'] . '</td>  
                    <th>Top 10 EPC:</th>  
                    <td>'. $merchant['merchant_top10epc'] .'</td>  
                  </tr>
                  <tr>
                    <th>PPC Bidding:</th>  
                    <td>'. $merchant['merchant_ppcbidding'] .
                        ($merchant['merchant_ppcbidding_id'] ? ' <a href="javascript://" onclick="openPopup(\'PPC Bidding\', '. $merchant['merchant_ppcbidding_id'] .')">(see restrictions)</a>' : '') .
                    '</td>  
                    <th>Datafeed:</th>  
                    <td>'. $merchant['merchant_datafeed'] .'</td>
                  </tr>
                </table>
                         
            </td></th></table>
            <br />
            ';    

        $email_message .= '
        </body>
        </html>
        ';
        
        return $email_message;
    }
}
?>

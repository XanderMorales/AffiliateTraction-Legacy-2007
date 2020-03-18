<?php
require_once('class.Database.php');
require_once('phpmailer/class.phpmailer.php');
session_start();

class Admin
{
    
    static function login($email, $password)
    {
        if(! ($email && $password) )
            return false;
        
        $query = "SELECT admin_id, admin_name FROM admins WHERE admin_email=? AND admin_password=? LIMIT 1";
        
        try
        {
            $response = Database::callSQL($query, array( $email, $password), array( PDO::PARAM_STR, PDO::PARAM_STR ));
        }
        catch(PDOException $e){}

        if( $response[0] )
        {
            $_SESSION['admin'] = $response[0];
            return true;
        }
        
        $_SESSION['admin'] = null;
        return false;
    }
    
    static function logout()
    {
        $_SESSION['admin'] = null;
    }
    
    static function isLogin()
    {
        return isset($_SESSION['admin']);
    }
    
    static function recoverPassword($email)
    {
        if(!$email )
            return false;
        
        $query = "SELECT admin_password, admin_name FROM admins WHERE admin_email=? LIMIT 1";
        
        try
        {
            $response = Database::callSQL($query, array($email), array(PDO::PARAM_STR));
        }
        catch(PDOException $e){}

        if( $response[0] )
        {
            $mail = new PHPMailer();

            $mail->IsSMTP(); // send via SMTP
            $mail->Host = "localhost"; // SMTP servers
            $mail->Mailer = "smtp";

            $mail->From = "info@affiliatetraction.com";
            $mail->FromName = "AffiliateTraction";
            $mail->AddAddress($_POST['_email'], $response['admin_name']);
            $mail->AddReplyTo('info@affiliatetraction.com');

            $mail->IsHTML(false); // send as HTML
            $mail->Subject = "AffiliateTraction Admin Password";
            $mail->Body = 'Your admin password is: '. $response[0]['admin_password'];
            #$mail->AltBody  =  "This is the text-only body";

            if($mail->Send())
                return true;
        }

        return false;
    }
    
}
?>

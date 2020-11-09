<?php
namespace envios\model;

use PHPMailer\PHPMailer\PHPMailer;
use core\ConfigGlobal;


require_once ("apps/core/global_header.inc");
require_once ("apps/core/global_object.inc");
require_once(ConfigGlobal::$dir_libs.'/vendor/autoload.php');

class TramityMail extends PHPMailer {
    
    
    public function __construct($exceptions = FALSE) {
        parent::__construct($exceptions);
        
        $this->setServer();
        $this->setRecipients();
    }
    

    public function setServer () {
        //Server settings
        //$this->SMTPDebug = SMTP::DEBUG_SERVER;              // Enable verbose debug output
        $this->isSMTP();                                      // Send using SMTP
        /*
        $this->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $this->Host       = 'smtp.gmail.com';                 // Set the SMTP server to send through
        $this->Port       = 587;                              // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
        $this->SMTPAuth   = true;                             // Enable SMTP authentication
        $this->Username   = 'dani@moneders.net';              // SMTP username
        $this->Password   = 'nou9vic!';                       // password
        */
        $this->SMTPSecure = $_SESSION['oConfig']->getSMTPSecure();
        $this->Host       = $_SESSION['oConfig']->getSMTPHost();
        $this->Port       = $_SESSION['oConfig']->getSMTPPort();
        $this->SMTPAuth   = $_SESSION['oConfig']->getSMTPAuth();
        $this->Username   = $_SESSION['oConfig']->getSMTPUser();
        $this->Password   = $_SESSION['oConfig']->getSMTPPwd();
        
        
    }
    
    public function setRecipients () {
        //Recipients
        $sigla = $_SESSION['oConfig']->getSigla();
        $tramity = "Tramity - $sigla";
        $this->setFrom('dani@moneders.net', $tramity);
        $this->addReplyTo('dani@moneders.net', $tramity);
        //$oMail->addCC('cc@example.com');
        //$oMail->addBCC('bcc@example.com');
    }
    
}
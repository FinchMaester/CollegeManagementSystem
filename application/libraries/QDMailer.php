<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

use PHPMailer\PHPMailer\PHPMailer;

class QDMailer {
    public $mail;
    
    public function __construct() {
        // Temporarily suppress deprecation warnings
        error_reporting(error_reporting() & ~E_DEPRECATED);
        
        // Use Composer's autoloader for PHPMailer
        require_once(APPPATH . '../vendor/autoload.php');
        $this->mail = new PHPMailer(true);
        
        // Restore error reporting
        error_reporting(error_reporting() | E_DEPRECATED); 
        
        
        
    }        
}
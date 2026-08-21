<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email_service {
    
    protected $CI;
    protected $email_config;
    
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->library('email');
        
        // Load email configuration
        $this->email_config = array(
            'protocol' => 'smtp',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_user' => 'rubikhanal8@gmail.com',
            'smtp_pass' => 'fntxemcolkmagbqo',
            'smtp_crypto' => 'tls',
            'mailtype' => 'html',
            'charset' => 'utf-8',
            'wordwrap' => TRUE,
            'newline' => "\r\n",
            'crlf' => "\r\n"
        );
        
        // Initialize email with config
        $this->CI->email->initialize($this->email_config);
    }
    
    /**
     * Send staff credentials email
     */
    public function send_staff_credentials($staff_email, $staff_name, $employee_id, $password) {
        try {
            // Email content
            $subject = 'Welcome to College System - Your Account Credentials';
            $message = $this->get_credentials_email_template($staff_name, $employee_id, $password, $staff_email);
            
            // Set email parameters
            $this->CI->email->from('rubikhanal8@gmail.com', 'College System');
            $this->CI->email->to($staff_email);
            $this->CI->email->subject($subject);
            $this->CI->email->message($message);
            
            // Send email
            if ($this->CI->email->send()) {
                log_message('info', 'Staff credentials email sent successfully to: ' . $staff_email);
                return array('status' => true, 'message' => 'Email sent successfully');
            } else {
                $error = $this->CI->email->print_debugger();
                log_message('error', 'Failed to send staff credentials email: ' . $error);
                return array('status' => false, 'message' => 'Email sending failed: ' . $error);
            }
            
        } catch (Exception $e) {
            log_message('error', 'Email service exception: ' . $e->getMessage());
            return array('status' => false, 'message' => 'Email service error: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate email template for staff credentials
     */
    private function get_credentials_email_template($staff_name, $employee_id, $password, $email) {
        $school_name = 'College System';
        $login_url = base_url('site/login');
        
        $message = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Welcome to ' . $school_name . '</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f9f9f9; padding: 20px; }
                .credentials { background-color: #e8f5e8; padding: 15px; border-left: 4px solid #4CAF50; margin: 20px 0; }
                .footer { background-color: #333; color: white; padding: 15px; text-align: center; font-size: 12px; }
                .btn { background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0; }
                .warning { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 5px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Welcome to ' . $school_name . '</h1>
                </div>
                
                <div class="content">
                    <h2>Dear ' . htmlspecialchars($staff_name) . ',</h2>
                    
                    <p>Your account has been successfully created in our system. Here are your login credentials:</p>
                    
                    <div class="credentials">
                        <h3>Your Login Credentials:</h3>
                        <p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>
                        <p><strong>Employee ID:</strong> ' . htmlspecialchars($employee_id) . '</p>
                        <p><strong>Password:</strong> ' . htmlspecialchars($password) . '</p>
                    </div>
                    
                    <p>Please click the button below to login:</p>
                    <a href="' . $login_url . '" class="btn">Login to Portal</a>
                    
                    <div class="warning">
                        <strong>Important:</strong> For security reasons, please change your password after first login.
                    </div>
                    
                    <p>Best regards,<br>
                    <strong>' . $school_name . ' Administration</strong></p>
                </div>
                
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' ' . $school_name . '</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $message;
    }
}
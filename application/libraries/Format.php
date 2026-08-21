<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Format
{
    public function __construct() { }
    
    public function factory($data, $status_code = 200)
    {
        return array(
            'data' => $data,
            'status' => $status_code
        );
    }
    
    // Add other format methods as needed
}
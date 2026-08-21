<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

class MY_REST_Controller extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
    }
}
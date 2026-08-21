<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Rbac_debug extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('rbac');
    }

    public function index()
    {
        // Debug tooling: Super Admin only.
        if (!$this->rbac->hasPrivilege('superadmin', 'can_view')) {
            access_denied();
        }

        // Force build (if not already cached)
        $this->rbac->hasPrivilege('student', 'can_view');

        $admin = $this->session->userdata('admin');
        $roles = array();
        if (is_array($admin) && isset($admin['roles']) && is_array($admin['roles'])) {
            foreach ($admin['roles'] as $name => $id) {
                $roles[] = array('name' => (string) $name, 'id' => (int) $id);
            }
        }

        $cache = $this->session->userdata('rbac_perm_cache');
        if (!is_array($cache)) {
            $cache = array();
        }

        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'schsettings/index');
        $this->session->set_userdata('subsub_menu', 'admin/rbac_debug/index');

        $data = array();
        $data['title'] = 'RBAC Debug (Merged Role Permissions)';
        $data['roles'] = $roles;
        $data['cache'] = $cache;
        $data['merged'] = (isset($cache['merged']) && is_array($cache['merged'])) ? $cache['merged'] : array();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/rbac_debug/index', $data);
        $this->load->view('layout/footer', $data);
    }
}


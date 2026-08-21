<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Generatestaffidcard extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('media_storage');
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('generate_staff_id_card', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Certificate');
        $this->session->set_userdata('sub_menu', 'admin/generatestaffidcard');
        $idcardlist            = $this->Generatestaffidcard_model->getstaffidcard();
        $data['idcardlist']    = $idcardlist;
        $staffRole             = $this->staff_model->getStaffRole();
        $data['staffRolelist'] = $staffRole;
        $this->load->view('layout/header');
        $this->load->view('admin/generatestaffidcard/generatestaffidcardview', $data);
        $this->load->view('layout/footer');
    }

    public function search()
    {
        $this->session->set_userdata('top_menu', 'Certificate');
        $this->session->set_userdata('sub_menu', 'admin/generatestaffidcard');
        $staffRole               = $this->staff_model->getStaffRole();
        $data['staffRolelist']   = $staffRole;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $idcardlist              = $this->Generatestaffidcard_model->getstaffidcard();
        $data['idcardlist']      = $idcardlist;
        $this->form_validation->set_rules('id_card', $this->lang->line('id_card_template'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == true) {
            $role                 = $this->input->post('role_id');
            $data['role_id']      = $this->input->post('role_id');
            $id_card              = $this->input->post('id_card');
            $idcardResult         = $this->Generatestaffidcard_model->getidcardbyid($id_card);
            $data['idcardResult'] = $idcardResult;
            $resultlist           = $this->staff_model->getEmployee($role, 1);
            $data['resultlist']   = $resultlist;
        }

        $this->load->view('layout/header');
        $this->load->view('admin/generatestaffidcard/generatestaffidcardview', $data);
        $this->load->view('layout/footer');
    }

    public function generatemultiple()
    {
        // Enable error reporting for debugging
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        // Start output buffering to catch any unexpected output
        ob_start();
        
        try {
            // Log to console via JavaScript
            echo "<script>console.log('=== GENERATE MULTIPLE START ===');</script>";
            
            // Get POST data
            $staffid = $this->input->post('data');
            $idcard = $this->input->post('id_card');
            
            echo "<script>console.log('Received POST data:', " . json_encode(['data_length' => strlen($staffid ?? ''), 'id_card' => $idcard]) . ");</script>";
            
            // Validate input
            if (empty($staffid)) {
                echo "<script>console.error('ERROR: No staff data received');</script>";
                echo json_encode(['error' => 'No staff data received']);
                return;
            }
            
            if (empty($idcard)) {
                echo "<script>console.error('ERROR: No ID card template selected');</script>";
                echo json_encode(['error' => 'No ID card template selected']);
                return;
            }
            
            // Decode staff array
            $staff_array = json_decode($staffid);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo "<script>console.error('ERROR: JSON decode failed:', '" . json_last_error_msg() . "');</script>";
                echo json_encode(['error' => 'Invalid JSON data: ' . json_last_error_msg()]);
                return;
            }
            
            echo "<script>console.log('Decoded staff array:', " . json_encode(['count' => count($staff_array), 'data' => $staff_array]) . ");</script>";
            
            // Get settings and ID card template
            $data['sch_setting'] = $this->setting_model->get();
            $data['id_card'] = $this->Generatestaffidcard_model->getidcardbyid($idcard);
            
            if (empty($data['id_card'])) {
                echo "<script>console.error('ERROR: ID card template not found for ID:', " . json_encode($idcard) . ");</script>";
                echo json_encode(['error' => 'ID card template not found']);
                return;
            }
            
            echo "<script>console.log('ID Card Template loaded:', " . json_encode([
                'id' => $data['id_card'][0]->id ?? 'N/A',
                'title' => $data['id_card'][0]->title ?? 'N/A',
                'enable_vertical_card' => $data['id_card'][0]->enable_vertical_card ?? 'N/A'
            ]) . ");</script>";
            
            // Build staff ID array
            $staffid_arr = array();
            foreach ($staff_array as $key => $value) {
                if (isset($value->staff_id)) {
                    $staffid_arr[] = $value->staff_id;
                }
            }
            
            echo "<script>console.log('Staff IDs to fetch:', " . json_encode($staffid_arr) . ");</script>";
            
            if (empty($staffid_arr)) {
                echo "<script>console.error('ERROR: No valid staff IDs found');</script>";
                echo json_encode(['error' => 'No valid staff IDs found']);
                return;
            }
            
            // Get staff details
            $staffs = $this->Generatestaffidcard_model->getEmployee($staffid_arr, 1);
            
            if (empty($staffs)) {
                echo "<script>console.error('ERROR: No staff records found in database');</script>";
                echo json_encode(['error' => 'No staff records found']);
                return;
            }
            
            echo "<script>console.log('Staff records fetched:', " . json_encode(['count' => count($staffs)]) . ");</script>";
            
            // Log each staff member details
            foreach ($staffs as $idx => $staff) {
                $staff_info = [
                    'index' => $idx + 1,
                    'id' => $staff->id ?? 'N/A',
                    'employee_id' => $staff->employee_id ?? 'N/A',
                    'name' => ($staff->name ?? '') . ' ' . ($staff->surname ?? ''),
                    'has_image' => !empty($staff->image),
                    'image_path' => $staff->image ?? 'default'
                ];
                echo "<script>console.log('Staff " . ($idx + 1) . ":', " . json_encode($staff_info) . ");</script>";
            }
            
            // Generate barcodes
            $barcode_errors = [];
            foreach ($staffs as $key => $staffs_value) {
                try {
                    $staffs[$key]->barcode = $this->customlib->generatestaffbarcode($staffs_value->employee_id);
                    echo "<script>console.log('Barcode generated for:', " . json_encode(['employee_id' => $staffs_value->employee_id]) . ");</script>";
                } catch (Exception $e) {
                    $error_msg = $e->getMessage();
                    echo "<script>console.warn('Barcode generation failed for employee " . $staffs_value->employee_id . ":', '" . addslashes($error_msg) . "');</script>";
                    $staffs[$key]->barcode = null;
                    $barcode_errors[] = [
                        'employee_id' => $staffs_value->employee_id,
                        'error' => $error_msg
                    ];
                }
            }
            
            if (!empty($barcode_errors)) {
                echo "<script>console.warn('Barcode errors summary:', " . json_encode($barcode_errors) . ");</script>";
            }
            
            $data['staffs'] = $staffs;
            
            // Log data before loading view
            $log_data = [
                'staff_count' => count($data['staffs']),
                'id_card_exists' => !empty($data['id_card']),
                'settings_exists' => !empty($data['sch_setting'])
            ];
            echo "<script>console.log('About to load view with:', " . json_encode($log_data) . ");</script>";
            
            // Clear any buffered debug output
            $debug_output = ob_get_clean();
            
            // Start fresh output buffer for view
            ob_start();
            
            // Load the view (WITHOUT any script tags that could break document.write)
            try {
                $id_cards = $this->load->view('admin/generatestaffidcard/generatemultiplestaffidcard', $data, true);
                $view_errors = ob_get_clean();
                
                if (!empty($view_errors)) {
                    echo $debug_output;
                    echo "<script>console.error('View generated with PHP notices/warnings:', " . json_encode($view_errors) . ");</script>";
                }
                
                echo "<script>console.log('View loaded successfully. HTML length:', " . strlen($id_cards) . ");</script>";
                echo "<script>console.log('=== GENERATE MULTIPLE COMPLETE ===');</script>";
                
            } catch (Exception $e) {
                ob_end_clean();
                echo $debug_output;
                echo "<script>console.error('View loading FAILED:', '" . addslashes($e->getMessage()) . "');</script>";
                echo json_encode(['error' => 'View rendering failed: ' . $e->getMessage()]);
                return;
            }
            
            // Output debug scripts first, then clean HTML
            echo $debug_output;
            echo $id_cards;
            
        } catch (Exception $e) {
            // Catch any unexpected errors
            $error_output = ob_get_clean();
            echo $error_output;
            echo "<script>console.error('FATAL ERROR:', '" . addslashes($e->getMessage()) . "');</script>";
            echo "<script>console.error('Stack trace:', '" . addslashes($e->getTraceAsString()) . "');</script>";
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
    }
}
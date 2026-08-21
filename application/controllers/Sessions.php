<?php


if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}


class Sessions extends Admin_Controller
{


    public function __construct()
    {
        parent::__construct();
    }


    public function index()
    {
        if (!$this->rbac->hasPrivilege('session_setting', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'sessions/index');
        $data['title']       = 'Session List';
        $session_result      = $this->session_model->getAllSession();
        $data['sessionlist'] = $session_result;
        $this->load->view('layout/header', $data);
        $this->load->view('session/sessionList', $data);
        $this->load->view('layout/footer', $data);
    }


    public function view($id)
    {
        if (!$this->rbac->hasPrivilege('session_setting', 'can_view')) {
            access_denied();
        }
        $data['title']   = 'Session List';
        $session         = $this->session_model->get($id);
        $data['session'] = $session;
        $this->load->view('layout/header', $data);
        $this->load->view('session/sessionShow', $data);
        $this->load->view('layout/footer', $data);
    }


    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('session_setting', 'can_delete')) {
            access_denied();
        }
        $this->session->set_flashdata('list_msg', '<div class="alert alert-success text-left">' . $this->lang->line('delete_message') . '</div>');
        $this->session_model->remove($id);
        redirect('sessions/index');
    }


    public function create()
    {
        if (!$this->rbac->hasPrivilege('session_setting', 'can_add')) {
            access_denied();
        }
        $session_result      = $this->session_model->getAllSession();
        $data['sessionlist'] = $session_result;
        $data['title']       = 'Add Session';
        $this->form_validation->set_rules('session', $this->lang->line('session'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('session/sessionList', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $sessionInput = $this->input->post('session');
            // Convert to BS format if it's in AD format
            $sessionBS = $this->customlib->convertSessionToBSIfNeeded($sessionInput);
            
            $data = array(
                'session' => $sessionBS,
            );
            $this->session_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('sessions/index');
        }
    }


    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('session_setting', 'can_edit')) {
            access_denied();
        }
        $session_result      = $this->session_model->getAllSession();
        $data['sessionlist'] = $session_result;
        $data['title']       = 'Edit Session';
        $data['id']          = $id;
        $session             = $this->session_model->get($id);
        $data['session']     = $session;
        $this->form_validation->set_rules('session', $this->lang->line('session'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('session/sessionEdit', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $sessionInput = $this->input->post('session');
            // Convert to BS format if it's in AD format
            $sessionBS = $this->customlib->convertSessionToBSIfNeeded($sessionInput);
            
            $data = array(
                'id'      => $id,
                'session' => $sessionBS,
            );
            $this->session_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>');
            redirect('sessions/index');
        }
    }


    public function getCurrentAndFutureSessions() {
        try {
            // Get current session - try multiple sources
            $currentSessionId = null;
           
            // Method 1: From session data
            if ($this->session->userdata('session_id')) {
                $currentSessionId = $this->session->userdata('session_id');
            }
           
            // Method 2: From school settings if session data is empty
            if (!$currentSessionId) {
                $this->db->select('session_id');
                $this->db->from('sch_settings');
                $this->db->limit(1);
                $query = $this->db->get();
               
                if ($query->num_rows() > 0) {
                    $result = $query->row();
                    $currentSessionId = $result->session_id;
                }
            }
           
            // Method 3: Get current session based on current date
            if (!$currentSessionId) {
                // Use BS year for comparison (current BS year is around 2081-2082)
                $currentBSYear = date('Y') + 57; // Convert AD to BS
                $this->db->select('id');
                $this->db->from('sessions');
                $this->db->like('session', $currentBSYear);
                $this->db->order_by('id', 'DESC');
                $this->db->limit(1);
                $query = $this->db->get();
               
                if ($query->num_rows() > 0) {
                    $result = $query->row();
                    $currentSessionId = $result->id;
                }
            }
           
            // Build the query for current and future sessions
            // ULTRA DIRECT FILTER: Use raw SQL to exclude invalid sessions
            $currentBSYear = date('Y') + 57; // 2081
            
            // Build WHERE clause with strict filtering
            $whereClause = "(";
            $whereClause .= "session LIKE '207%' OR "; // 2070-2079
            $whereClause .= "session LIKE '208%' OR "; // 2080-2089
            $whereClause .= "session LIKE '209%'";    // 2090-2099
            $whereClause .= ")";
            
            if ($currentSessionId) {
                $whereClause .= " AND id >= " . intval($currentSessionId);
            } else {
                $whereClause .= " AND (session LIKE '{$currentBSYear}-%' OR session LIKE '" . ($currentBSYear + 1) . "-%')";
            }
            
            // Use raw query for maximum control
            $sql = "SELECT * FROM sessions WHERE {$whereClause} ORDER BY id ASC";
            $query = $this->db->query($sql);
            $sessions = $query->result_array();
            
            log_message('debug', 'SQL Query: ' . $sql);
            log_message('debug', 'Sessions from DB (after filter): ' . count($sessions));
            
            // ULTRA STRICT FILTERING: Double-check every session
            $validSessions = array();
            foreach ($sessions as $session) {
                if (isset($session['session'])) {
                    $originalSession = trim($session['session']);
                    
                    // Parse and validate
                    $parts = explode('-', $originalSession);
                    if (count($parts) == 2) {
                        $year1 = intval($parts[0]);
                        
                        // STRICT: Only accept sessions in 2070-2090 range
                        // Reject anything else immediately (including 2138-39)
                        if ($year1 >= 2070 && $year1 <= 2090) {
                            // Valid BS session, use as-is
                            $session['session'] = $originalSession;
                            $validSessions[] = $session;
                            log_message('debug', '✓ ACCEPTED: ' . $originalSession);
                        } else {
                            // Try conversion as last resort
                            $convertedSession = $this->customlib->convertSessionToBS($originalSession);
                            $convertedParts = explode('-', $convertedSession);
                            
                            if (count($convertedParts) == 2) {
                                $convertedYear1 = intval($convertedParts[0]);
                                
                                // Only accept if converted result is in valid range
                                if ($convertedYear1 >= 2070 && $convertedYear1 <= 2090) {
                                    $session['session'] = $convertedSession;
                                    $validSessions[] = $session;
                                    log_message('debug', '✓ CONVERTED & ACCEPTED: ' . $originalSession . ' -> ' . $convertedSession);
                                } else {
                                    log_message('debug', '✗ REJECTED (invalid after conversion): ' . $originalSession . ' -> ' . $convertedSession . ' (year: ' . $convertedYear1 . ')');
                                }
                            } else {
                                log_message('debug', '✗ REJECTED (invalid format): ' . $originalSession);
                            }
                        }
                    } else {
                        log_message('debug', '✗ REJECTED (wrong format): ' . $originalSession);
                    }
                }
            }
            
            // Use only valid sessions
            $sessions = $validSessions;
            
            log_message('debug', '=== FINAL RESULT ===');
            log_message('debug', 'Valid sessions: ' . count($sessions));
            foreach ($sessions as $s) {
                log_message('debug', '  - ' . $s['session']);
            }
            
            // Debug logging
            log_message('debug', 'Current Session ID: ' . $currentSessionId);
            log_message('debug', 'Valid sessions found: ' . count($sessions));
            
            header('Content-Type: application/json');
            echo json_encode($sessions);
           
        } catch (Exception $e) {
            log_message('error', 'Error in getCurrentAndFutureSessions: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([]);
        }
    }
   
   


}


<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Manual per-record sync triggers (Super Admin tooling).
 */
class Ugc_sync extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        // IMPORTANT: Do NOT call $this->auth->is_logged_in() here (Admin_Controller does).
        // This controller is used by AJAX and must return JSON instead of redirecting.
        $this->load->library('rbac');
        $this->load->library('hemis_client');
        $this->load->model('ugc_sync_state_model');
        $this->load->model('ugc_error_log_model');
        $this->load->model('api_building_model');
        $this->load->model('staff_model');
        $this->load->model('student_model');
    }

    protected function guard()
    {
        $admin = $this->session->userdata('admin');
        $isLoggedIn = is_array($admin) && !empty($admin);

        if (!$isLoggedIn) {
            $this->output->set_status_header(401);
            $this->_json(array('status' => 0, 'message' => 'Not logged in'));
            exit;
        }

        if (!$this->rbac->hasPrivilege('superadmin', 'can_edit')) {
            $this->output->set_status_header(403);
            $this->_json(array('status' => 0, 'message' => 'Permission denied'));
            exit;
        }
    }

    protected function require_post()
    {
        if (strtoupper((string) $this->input->method()) !== 'POST') {
            if ($this->input->is_ajax_request()) {
                $this->output->set_status_header(405);
                $this->_json(array('status' => 0, 'message' => 'Method not allowed'));
                exit;
            }
            show_error('Method not allowed', 405);
        }
    }

    public function building($id = null)
    {
        $this->guard();
        $this->require_post();
        $id = (int) $id;
        if ($id < 1) {
            show_error('Missing building id', 400);
        }

        $row = $this->api_building_model->get($id);
        if (!$row) {
            show_error('Building not found', 404);
        }

        $out = $this->hemis_client->sync_building_update($id, $row);
        $this->_update_state_and_log('Buildings', $id, 'PUT', '/api/Buildings/' . $id, $row, $out);
        $this->_json($out);
    }

    public function employee($id = null)
    {
        $this->guard();
        // Allow GET for convenience when visiting in browser.
        // Still protected by guard() (logged-in + superadmin).
        $id = (int) $id;
        if ($id < 1) {
            show_error('Missing employee id', 400);
        }

        $staff = $this->staff_model->get($id);
        if (!$staff) {
            show_error('Employee not found', 404);
        }

        $out = $this->_run_staff_ugc_employee_sync($id, $staff);
        $this->_json($out);
    }

    /**
     * POST/PATCH staff to UGC using multipart + DB files; persist hemis_employee_id + sync state.
     *
     * @param int   $id
     * @param array $staff
     * @return array
     */
    protected function _run_staff_ugc_employee_sync($id, array $staff)
    {
        $id = (int) $id;
        $hid = isset($staff['hemis_employee_id']) ? (int) $staff['hemis_employee_id'] : 0;
        $payload = $this->hemis_client->map_staff_row_to_hemis_employee_payload($staff);

        if ($hid > 0) {
            $out = $this->hemis_client->sync_employee_after_local_update($id);
            $method = 'PATCH';
            $path = '/api/Employee/' . $hid;
        } else {
            $out = $this->hemis_client->sync_employee_after_local_create($id);
            $method = 'POST';
            $path = '/api/Employee';
        }

        $this->staff_model->apply_hemis_employee_sync_result($id, is_array($out) ? $out : array());

        $staff = $this->staff_model->get($id);
        if (is_array($staff) && !empty($staff['hemis_employee_id'])) {
            $out['hemis_employee_id'] = (int) $staff['hemis_employee_id'];
            if (empty($out['synced'])) {
                $out['synced'] = true;
            }
        }

        $this->_update_state_and_log('Employee', $id, $method, $path, $payload, is_array($out) ? $out : array());
        return is_array($out) ? $out : array();
    }

    /**
     * Manual sync: one student → POST /api/Student/Create (rebuilt from DB snapshot).
     * GET or POST /admin/ugc_sync/student/{id}
     */
    public function student($id = null)
    {
        $this->guard();
        $id = (int) $id;
        if ($id < 1) {
            show_error('Missing student id', 400);
        }

        $row = $this->student_model->get_student_by_id($id);
        if (!$row) {
            show_error('Student not found', 404);
        }

        $this->load->helper('hemis_sync_post_from_db');
        $payload = hemis_student_db_row_to_create_post($row);

        $out = $this->hemis_client->sync_student_after_database_snapshot($id);
        $this->_update_state_and_log('Student', $id, 'POST', '/api/Student/Create', $payload, $out);
        $this->_json($out);
    }

    /**
     * Debug: show effective HEMIS config (token redacted).
     * Useful to confirm which remote_base_url is being used.
     */
    public function debug_config()
    {
        $this->guard();
        $this->load->model('hemis_integration_model');
        $cfg = $this->hemis_integration_model->get_effective_config();
        $token = isset($cfg['remote_bearer_token']) ? (string) $cfg['remote_bearer_token'] : '';
        $cfg['remote_bearer_token'] = $token !== '' ? ('[set len=' . strlen($token) . ']') : '[empty]';
        $this->_json(array('status' => 1, 'cfg' => $cfg));
    }

    /**
     * Debug: check whether staff schema has HEMIS columns.
     * GET /admin/ugc_sync/debug_staff_schema
     */
    public function debug_staff_schema()
    {
        $this->guard();

        $exists = $this->db->table_exists('staff');
        $cols = array(
            'citizenship_no' => $exists ? $this->db->field_exists('citizenship_no', 'staff') : false,
            'graduated_from' => $exists ? $this->db->field_exists('graduated_from', 'staff') : false,
        );
        $this->_json(array(
            'status' => 1,
            'table_exists' => $exists,
            'columns' => $cols,
        ));
    }

    protected function guard_web()
    {
        $admin = $this->session->userdata('admin');
        if (!is_array($admin) || empty($admin)) {
            redirect('site/login');
        }
        if (!$this->rbac->hasPrivilege('superadmin', 'can_edit')) {
            access_denied();
        }
    }

    /**
     * Compare POST vs PATCH HEMIS payloads for one student (Super Admin).
     * GET /admin/ugc_sync/debug_student/{students.id}
     * GET /admin/ugc_sync/debug_student/{students.id}?format=json
     */
    public function debug_student($id = null)
    {
        $this->guard_web();
        $id = (int) $id;
        if ($id < 1) {
            show_error('Missing students.id', 400);
        }

        $report = $this->hemis_client->diagnostic_student_post_vs_patch($id);

        if ($this->input->get('format') === 'json') {
            $this->_json(array('status' => 1, 'data' => $report));
            return;
        }

        $data['report'] = $report;
        $data['student_id'] = $id;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/ugc_sync/debug_student', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * Live PATCH retry for diagnostics (Super Admin). POST only.
     * POST /admin/ugc_sync/debug_student_try_patch/{students.id}
     */
    public function debug_student_try_patch($id = null)
    {
        $this->guard();
        $this->require_post();
        $id = (int) $id;
        if ($id < 1) {
            $this->_json(array('status' => 0, 'message' => 'Missing students.id'));
            return;
        }
        $out = $this->hemis_client->sync_student_update_from_database_snapshot($id);
        $this->_json(array('status' => !empty($out['synced']) ? 1 : 0, 'result' => $out));
    }

    /**
     * Compare POST vs PATCH HEMIS payloads for one employee/staff (Super Admin).
     * GET /admin/ugc_sync/debug_employee/{staff.id}
     * GET /admin/ugc_sync/debug_employee/{staff.id}?format=json
     */
    public function debug_employee($id = null)
    {
        $this->guard_web();
        $id = (int) $id;
        if ($id < 1) {
            show_error('Missing staff.id', 400);
        }

        $report = $this->hemis_client->diagnostic_employee_post_vs_patch($id);

        if ($this->input->get('format') === 'json') {
            $this->_json(array('status' => 1, 'data' => $report));
            return;
        }

        $data['report'] = $report;
        $data['staff_id'] = $id;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/ugc_sync/debug_employee', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * Live sync retry for diagnostics (Super Admin). POST only.
     * PATCH when a HEMIS employee id exists, otherwise POST Create.
     * POST /admin/ugc_sync/debug_employee_try_sync/{staff.id}
     */
    public function debug_employee_try_sync($id = null)
    {
        $this->guard();
        $this->require_post();
        $id = (int) $id;
        if ($id < 1) {
            $this->_json(array('status' => 0, 'message' => 'Missing staff.id'));
            return;
        }
        $staff = $this->staff_model->get($id);
        $hid = (is_array($staff) && isset($staff['hemis_employee_id'])) ? (int) $staff['hemis_employee_id'] : 0;
        $out = ($hid > 0)
            ? $this->hemis_client->sync_employee_after_local_update($id)
            : $this->hemis_client->sync_employee_after_local_create($id);
        $this->_json(array('status' => !empty($out['synced']) ? 1 : 0, 'result' => $out));
    }

    /**
     * Debug: return latest ugc_error_logs row(s) for a module with request/response.
     * GET /admin/ugc_sync/debug_latest_log?module=Employee&limit=1
     */
    public function debug_latest_log()
    {
        $this->guard();
        $module = trim((string) $this->input->get('module'));
        if ($module === '') {
            $module = 'Employee';
        }
        $source = strtolower(trim((string) $this->input->get('source')));
        if ($source === '') {
            // Default to remote HTTP attempt logs (from Hemis_client), not the local "manual sync attempted" row.
            $source = 'remote';
        }
        $limit = (int) $this->input->get('limit');
        if ($limit < 1) {
            $limit = 1;
        }
        if ($limit > 5) {
            $limit = 5;
        }

        $q = $this->db->select('id, created_at, module, http_method, http_code, endpoint, message, request_headers, request_body, response_body')
            ->from('ugc_error_logs')
            ->where('module', $module);
        if ($source === 'remote') {
            // Hemis_client logs full URL endpoints; Ugc_sync manual logs store only the path.
            $q->like('endpoint', 'http', 'after');
        } elseif ($source === 'manual') {
            $q->not_like('endpoint', 'http', 'after');
        }
        $rows = $q->order_by('id', 'DESC')->limit($limit)->get()->result_array();

        // Redact Authorization header if present.
        foreach ($rows as &$r) {
            if (!empty($r['request_headers'])) {
                $h = (string) $r['request_headers'];
                $h = preg_replace('/Authorization\\s*:\\s*Bearer\\s+[^\\r\\n]+/i', 'Authorization: Bearer [redacted]', $h);
                $r['request_headers'] = $h;
            }
        }
        unset($r);

        $this->_json(array('status' => 1, 'data' => $rows, 'source' => $source));
    }

    /**
     * Refresh UGC metadata cache (Phase 1 GET endpoints).
     * POST /admin/ugc_sync/refresh_metadata
     */
    public function refresh_metadata()
    {
        $this->guard();
        // Allow GET for convenience when visiting in browser.
        // Still protected by guard() (logged-in + superadmin).

        $this->load->model('ugc_metadata_model');
        $this->ugc_metadata_model->ensure_tables();

        $results = array();

        // Batch
        $b = $this->hemis_client->hemis_get_batch();
        if (!empty($b['ok']) && is_array($b['data'])) {
            $this->ugc_metadata_model->upsert_batches($b['data']);
        }
        $results['batch'] = $b;

        // Fiscal Year (auth none per PDF, but Hemis_client always adds Bearer if configured; should still work)
        $fy = $this->hemis_client->hemis_get_fiscal_year();
        if (!empty($fy['ok']) && is_array($fy['data'])) {
            $this->ugc_metadata_model->upsert_fiscal_years($fy['data']);
        }
        $results['fiscal_year'] = $fy;

        // Programs
        $p = $this->hemis_client->hemis_get_college_programs();
        if (!empty($p['ok']) && is_array($p['data'])) {
            $this->ugc_metadata_model->upsert_programs($p['data']);
        }
        $results['programs'] = $p;

        // Address
        $a = $this->hemis_client->hemis_get_address_all();
        if (!empty($a['ok']) && is_array($a['data'])) {
            $this->ugc_metadata_model->upsert_addresses($a['data']);
        }
        $results['address'] = $a;

        // Management (departments + sections)
        $d = $this->hemis_client->hemis_get_management_departments();
        if (!empty($d['ok']) && is_array($d['data'])) {
            $this->ugc_metadata_model->upsert_departments($d['data']);
        }
        $results['departments'] = $d;

        $s = $this->hemis_client->hemis_get_management_sections();
        if (!empty($s['ok']) && is_array($s['data'])) {
            $this->ugc_metadata_model->upsert_sections($s['data']);
        }
        $results['sections'] = $s;

        $this->_json(array('status' => 1, 'results' => $results));
    }

    /**
     * GET cached UGC departments (for UI dropdowns).
     */
    public function departments_get()
    {
        $this->guard();
        $this->load->model('ugc_metadata_model');
        $rows = $this->ugc_metadata_model->get_departments_cached();
        $this->_json(array('status' => 1, 'data' => $rows));
    }

    /**
     * GET cached UGC sections (for UI dropdowns).
     */
    public function sections_get()
    {
        $this->guard();
        $this->load->model('ugc_metadata_model');
        $rows = $this->ugc_metadata_model->get_sections_cached();
        $this->_json(array('status' => 1, 'data' => $rows));
    }

    protected function _update_state_and_log($module, $localId, $method, $path, $payload, array $hemisOut)
    {
        $http = isset($hemisOut['http_code']) ? (int) $hemisOut['http_code'] : null;
        if ($http === null && isset($hemisOut['status_code'])) {
            $http = (int) $hemisOut['status_code'];
        }
        $synced = !empty($hemisOut['synced']);
        $hemisId = isset($hemisOut['hemis_employee_id']) ? (int) $hemisOut['hemis_employee_id'] : 0;
        if ($hemisId < 1 && (string) $module === 'Employee') {
            $staff = $this->staff_model->get((int) $localId);
            if (is_array($staff) && !empty($staff['hemis_employee_id'])) {
                $hemisId = (int) $staff['hemis_employee_id'];
                $synced = true;
            }
        }
        $blocked = isset($hemisOut['sync_pending']) && $hemisOut['sync_pending'] === 'UGC_DEV_BLOCKER';
        $status = ($synced || $hemisId > 0) ? 'synced' : ($blocked ? 'blocked' : (!empty($hemisOut['error']) ? 'error' : 'pending'));

        $this->ugc_sync_state_model->upsert((string) $module, (int) $localId, array(
            'status'         => $status,
            'ugc_record_id'  => $hemisId > 0 ? (string) $hemisId : null,
            'last_synced_at' => ($synced || $hemisId > 0) ? date('Y-m-d H:i:s') : null,
            'last_http_code' => $http,
            'last_error'     => ($status === 'error' && !empty($hemisOut['error']))
                ? substr((string) $hemisOut['error'], 0, 500)
                : null,
        ));

        // Always log manual attempts so the badge can link to the log even in local/mock mode.
        $this->ugc_error_log_model->log(array(
            'module'          => (string) $module,
            'endpoint'        => (string) $path,
            'http_method'     => strtoupper((string) $method),
            'http_code'       => $http,
            'message'         => $synced ? 'manual sync ok' : (!empty($hemisOut['error']) ? substr((string) $hemisOut['error'], 0, 255) : 'manual sync attempted'),
            'request_headers' => json_encode(array()),
            'request_body'    => json_encode($payload),
            'response_body'   => json_encode($hemisOut),
        ));
    }

    protected function _json($data)
    {
        $this->output->set_content_type('application/json');
        echo json_encode($data);
    }
}


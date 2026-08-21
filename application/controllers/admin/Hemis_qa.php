<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * QA-only admin screens for HEMIS-shaped infrastructure APIs (Buildings, Library, FEV).
 * Mirrors api/Buildings, api/Library, api/FurnitureEquipmentVehicle local save + Hemis_client sync.
 */
class Hemis_qa extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('api_building_model');
        $this->load->model('library_model');
        $this->load->model('api_fev_model');
        $this->load->model('setting_model');
        $this->load->model('ugc_sync_state_model');
        $this->load->library('hemis_client');
    }

    /**
     * Valid campus API token (tokens table) for same-host curl to api/*.
     *
     * @return string
     */
    protected function _qa_campus_bearer_token()
    {
        $q = $this->db->where('expired', 0)->order_by('id', 'DESC')->limit(1)->get('tokens');
        if ($q && $q->num_rows() > 0) {
            return (string) $q->row()->token;
        }
        return '';
    }

    /**
     * POST JSON to a campus REST route (browser QA — triggers same controllers as mobile/API).
     *
     * @param string $uri e.g. api/StudentUpgrade
     * @return array{http_code:int, body:string, curl_error:string}
     */
    protected function _qa_post_json_to_campus($uri, array $payload)
    {
        $out = array('http_code' => 0, 'body' => '', 'curl_error' => '');
        if (!function_exists('curl_init')) {
            $out['curl_error'] = 'PHP curl extension not available';
            return $out;
        }
        $url = site_url($uri);
        $token = $this->_qa_campus_bearer_token();
        if ($token === '') {
            $out['curl_error'] = 'No active row in tokens table (create a campus API token first).';
            return $out;
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . $token,
            ),
        ));
        $body = curl_exec($ch);
        $out['curl_error'] = (string) curl_error($ch);
        $out['http_code'] = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $out['body'] = is_string($body) ? $body : '';
        return $out;
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('general_setting', 'can_view')) {
            access_denied();
        }

        $data['title'] = 'HEMIS infrastructure (QA)';
        $data['buildings'] = $this->db->table_exists('api_buildings')
            ? $this->api_building_model->get_all()
            : array();
        $sch = $this->db->get('sch_settings')->row_array();
        $data['default_campus_id'] = !empty($sch['id']) ? (int) $sch['id'] : 1;

        if (strtoupper($this->input->method(true)) === 'POST') {
            $action = $this->input->post('hemis_qa_action');
            if ($action === 'building') {
                $payload = array(
                    'houseName'               => $this->input->post('houseName'),
                    'areaCoveredByBuilding'   => $this->input->post('areaCoveredByBuilding'),
                    'noOfClassrooms'          => $this->input->post('noOfClassrooms'),
                    'areaCoveredByAllRooms'   => $this->input->post('areaCoveredByAllRooms'),
                    'ownershipOfBuilding'     => $this->input->post('ownershipOfBuilding'),
                    'hasInternetConnection'   => $this->input->post('hasInternetConnection'),
                    'remarks'                 => $this->input->post('remarks'),
                );
                $newId = $this->api_building_model->create($payload);
                if ($newId) {
                    $payload['id'] = (int) $newId;
                    $data['last_hemis'] = $this->hemis_client->sync_building_create($payload);
                    $data['flash_ok'] = 'Building saved (id ' . (int) $newId . ').';
                } else {
                    $data['flash_err'] = 'Building save failed (check api_buildings table).';
                }
            } elseif ($action === 'library') {
                $library_data = array(
                    'libraryName'             => $this->input->post('libraryName'),
                    'buildingId'              => $this->input->post('buildingId'),
                    'campusId'                => $this->input->post('campusId'),
                    'noOfLibraryRooms'        => $this->input->post('noOfLibraryRooms'),
                    'totalAreaOfLibraryRoom'  => $this->input->post('totalAreaOfLibraryRoom'),
                    'noOfReadingHalls'        => $this->input->post('noOfReadingHalls'),
                    'areaOfReadingHall'       => $this->input->post('areaOfReadingHall'),
                    'noOfBooks'               => $this->input->post('noOfBooks'),
                    'noOfDailyJournals'       => $this->input->post('noOfDailyJournals'),
                    'noOfWeeklyJournals'      => $this->input->post('noOfWeeklyJournals'),
                    'noOfMonthlyJournals'     => $this->input->post('noOfMonthlyJournals'),
                    'noOfAnnualJournals'      => $this->input->post('noOfAnnualJournals'),
                    'hasELibraryFacility'     => $this->input->post('hasELibraryFacility'),
                    'remarks'                 => $this->input->post('remarks'),
                );
                $id = $this->library_model->create_library($library_data);
                if ($id) {
                    $library_data['id'] = (int) $id;
                    $data['last_hemis'] = $this->hemis_client->sync_library_create($library_data);
                    $data['flash_ok'] = 'Library saved (id ' . (int) $id . '). areaOfReadingHall / noOfBooks persisted to api_library.';
                } else {
                    $data['flash_err'] = 'Library save failed.';
                }
            } elseif ($action === 'fev') {
                $payload = array(
                    'itemType'        => $this->input->post('itemType'),
                    'itemName'        => $this->input->post('itemName'),
                    'noOfQty'         => $this->input->post('noOfQty'),
                    'itemDescription' => $this->input->post('itemDescription'),
                    'remarks'         => $this->input->post('remarks'),
                );
                $newId = $this->api_fev_model->create($payload);
                if ($newId) {
                    $payload['id'] = (int) $newId;
                    $data['last_hemis'] = $this->hemis_client->sync_fev_create($payload);
                    $data['flash_ok'] = 'Furniture/Equipment/Vehicle saved (id ' . (int) $newId . ').';
                } else {
                    $data['flash_err'] = 'FEV save failed.';
                }
            } elseif ($action === 'reactivate_1097') {
                $this->db->where('id', 1097)->update('students', array(
                    'is_active'  => 'yes',
                    'dis_reason' => null,
                    'dis_note'   => null,
                    'disable_at' => null,
                ));
                $cur = (int) $this->setting_model->getCurrentSession();
                $this->db->where('student_id', 1097)->where('session_id', $cur)->update('student_session', array(
                    'is_leave'  => 0,
                    'is_active' => 'yes',
                ));
                $this->db->where('student_id', 1097)->where('session_id !=', $cur)->update('student_session', array(
                    'is_active' => 'no',
                    'is_leave'  => 0,
                ));
                $data['flash_ok'] = 'Student 1097 reactivated for current session (other session rows set inactive).';
            } elseif ($action === 'student_upgrade_api') {
                $payload = array(array(
                    'studentId'  => (int) $this->input->post('su_studentId'),
                    'programId'  => (int) $this->input->post('su_programId'),
                    'year'       => $this->input->post('su_year'),
                    'semester'   => $this->input->post('su_semester'),
                    'batchId'    => (int) $this->input->post('su_batchId'),
                ));
                $res = $this->_qa_post_json_to_campus('api/StudentUpgrade', $payload);
                $data['last_api_http'] = $res['http_code'];
                $data['last_api_body'] = $res['body'];
                if ($res['curl_error'] !== '') {
                    $data['flash_err'] = 'cURL: ' . $res['curl_error'];
                } else {
                    $data['flash_ok'] = 'StudentUpgrade API HTTP ' . $res['http_code'];
                }
            } elseif ($action === 'dropout_test_api') {
                $sid = (int) $this->input->post('do_studentId');
                $payload = array(
                    'studentId'   => $sid,
                    'reason'      => (string) $this->input->post('do_reason'),
                    'dateOfEntry' => (string) $this->input->post('do_date'),
                    'remarks'     => (string) $this->input->post('do_remarks'),
                );
                $res = $this->_qa_post_json_to_campus('api/DropOut', $payload);
                $data['last_api_http'] = $res['http_code'];
                $data['last_api_body'] = $res['body'];
                if ($res['curl_error'] !== '') {
                    $data['flash_err'] = 'cURL: ' . $res['curl_error'];
                } else {
                    $data['flash_ok'] = 'DropOut API HTTP ' . $res['http_code'] . ' — check students.dis_reason and application/logs for HEMIS DropOut outbound reason.';
                }
            }
            $data['buildings'] = $this->db->table_exists('api_buildings')
                ? $this->api_building_model->get_all()
                : array();
        }

        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'admin/hemis_qa');

        // Precompute building sync map for the view (avoid loading models in view context).
        $data['ugc_building_sync_map'] = array();
        if (!empty($data['buildings']) && is_array($data['buildings'])) {
            $ids = array();
            foreach ($data['buildings'] as $b) {
                $ids[] = (int) ($b['id'] ?? 0);
            }
            $data['ugc_building_sync_map'] = $this->ugc_sync_state_model->map_states('Buildings', $ids);
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/hemis_qa/index', $data);
        $this->load->view('layout/footer', $data);
    }
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Development-only harness: simulate multipart PATCH to api/Student/{id} (Update_patch)
 * with ppSizePhoto, then inspect HEMIS sync behaviour and local file resolution.
 *
 * Usage (GET):
 *   /test_sync/patch/{student_id}?token=YOUR_BEARER_TOKEN
 *   /test_sync/patch?token=...&id=123
 * Optional query:
 *   image=/absolute/or/relative/path/to.jpg  — default: 1×1 PNG in temp dir
 *   dry_run=1 — build payload and file diagnostics only (no HTTP to API)
 *
 * Requires: ENVIRONMENT !== 'production'. Remove or protect before public deploy.
 */
class Test_Sync extends CI_Controller
{
    /** @var string 1×1 transparent PNG */
    protected static $minimal_png_b64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    public function __construct()
    {
        parent::__construct();
        if (ENVIRONMENT === 'production') {
            show_error('Test_Sync is disabled in production.', 403);
        }
        $this->load->database();
        // Student_model::__construct() requires setting_model (current session / date).
        $this->load->model('setting_model');
        $this->load->model('student_model');
        $this->load->model('hemis_integration_model');
        $this->load->helper('hemis_student');
    }

    public function index()
    {
        header('Content-Type: text/plain; charset=utf-8');
        echo "Test_Sync (development only)\n\n";
        echo "PATCH simulation with ppSizePhoto (maps to api/Student::Update_patch via PATCH api/Student/{id}):\n";
        echo "  " . site_url('test_sync/patch/123') . "?token=BEARER_TOKEN\n";
        echo "  Add &dry_run=1 to skip HTTP and only show payload + file resolution.\n";
        echo "  Add &image=path\\to\\photo.jpg to use a specific file (relative to FCPATH if not absolute).\n";
    }

    /**
     * Run multipart PATCH against local api/Student/{id} (same as campus Update_patch).
     *
     * @param int|string|null $student_id URI segment
     */
    public function patch($student_id = null)
    {
        $sid = (int) ($student_id ?: $this->input->get('id'));
        if ($sid < 1) {
            return $this->_json(array('error' => 'Provide student id: /test_sync/patch/123 or ?id=123'), 400);
        }

        $dry = filter_var($this->input->get('dry_run'), FILTER_VALIDATE_BOOLEAN);

        $token = trim((string) $this->input->get('token'));
        if (!$dry && $token === '') {
            return $this->_json(array(
                'error' => 'Missing Bearer token. Pass ?token=... (opaque campus token or JWT), or use &dry_run=1 to skip HTTP.',
            ), 400);
        }

        $row = $this->student_model->get_student_by_id($sid);
        if (empty($row)) {
            $sample = array();
            if ($this->db->table_exists('students')) {
                $q = $this->db->select('id, admission_no, firstname, lastname')
                    ->from('students')
                    ->order_by('id', 'ASC')
                    ->limit(20)
                    ->get();
                $sample = $q ? $q->result_array() : array();
            }
            return $this->_json(array(
                'error' => 'Student not found',
                'student_id' => $sid,
                'hint' => 'No row in `students` with this id. Use an id that exists in your database (see sample_students).',
                'students_table_count' => $this->db->table_exists('students') ? (int) $this->db->count_all('students') : null,
                'sample_students' => $sample,
            ), 404);
        }

        $cfg = $this->hemis_integration_model->get_effective_config();
        if (!is_array($cfg)) {
            $cfg = array();
        }

        $image_param = $this->input->get('image');
        $resolved = $this->_resolve_sample_image_path($image_param);
        if (isset($resolved['error'])) {
            return $this->_json(array('error' => $resolved['error']), 400);
        }
        $sample_path = $resolved['path'];

        $payload = $this->_build_patch_form_fields($row);
        $file_diag = $this->_diagnose_pp_size_photo_paths($row);

        if ($dry) {
            return $this->_json(array(
                'dry_run' => true,
                'student_id' => $sid,
                'hemis_config' => $this->_safe_hemis_config_summary($cfg),
                'sample_image_for_request' => $sample_path,
                'existing_pp_size_photo_resolution' => $file_diag,
                'form_field_keys' => array_keys($payload),
                'note' => 'No HTTP request sent (dry_run=1).',
            ));
        }

        $url = rtrim(site_url(), '/') . '/api/Student/' . $sid;

        if (!function_exists('curl_init')) {
            return $this->_json(array('error' => 'PHP curl extension required'), 500);
        }
        if (!class_exists('CURLFile')) {
            return $this->_json(array('error' => 'CURLFile not available (PHP curl file uploads)'), 500);
        }

        $mime = 'image/png';
        if (is_file($sample_path) && function_exists('mime_content_type')) {
            $m = @mime_content_type($sample_path);
            if (is_string($m) && $m !== '') {
                $mime = $m;
            }
        }

        $postfields = $payload;
        $postfields['ppSizePhoto'] = new CURLFile($sample_path, $mime, basename($sample_path));

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_CUSTOMREQUEST  => 'PATCH',
            CURLOPT_POSTFIELDS     => $postfields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_HTTPHEADER     => array(
                'Accept: application/json',
                'Authorization: Bearer ' . $token,
            ),
        ));

        $body = curl_exec($ch);
        $curl_err = curl_error($ch);
        $http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($body, true);

        $hemis_note = null;
        if (is_array($decoded) && isset($decoded['hemisSync'])) {
            $hs = $decoded['hemisSync'];
            if (is_array($hs) && !empty($hs['mode']) && $hs['mode'] === 'mock') {
                $hemis_note = 'HEMIS mock_mode: outbound PATCH to UGC was skipped; hemis_sync_at updated locally. '
                    . 'Use hemis_integration settings (mock off) + hemis_student_id set to verify real CURL forward.';
                log_message('info', 'Test_Sync: mock_mode hemisSync payload not sent remotely; local ppSizePhoto sample was '
                    . $sample_path . ' (multipart fields would include ppSizePhoto + ' . count($payload) . ' text fields).');
            }
        }

        log_message('info', 'Test_Sync patch: student=' . $sid . ' http=' . $http_code . ' sample_file=' . $sample_path);

        return $this->_json(array(
            'student_hemis_student_id' => isset($row['hemis_student_id']) ? (int) $row['hemis_student_id'] : null,
            'request' => array(
                'method' => 'PATCH',
                'url' => $url,
                'ppSizePhoto_curlfile' => array(
                    'path' => $sample_path,
                    'is_readable' => is_readable($sample_path),
                    'mime' => $mime,
                    'basename' => basename($sample_path),
                ),
            ),
            'response' => array(
                'http_code' => $http_code,
                'curl_error' => $curl_err !== '' ? $curl_err : null,
                'body_raw' => $body,
                'body_json' => $decoded !== null ? $decoded : null,
            ),
            'hemis_config' => $this->_safe_hemis_config_summary($cfg),
            'existing_pp_size_photo_resolution' => $file_diag,
            'interpretation' => $hemis_note,
        ), $http_code >= 400 && $http_code > 0 ? $http_code : 200);
    }

    /**
     * Same path resolution as Hemis_client::_merge_student_db_files_into_postfields for pp_size_photo.
     */
    protected function _diagnose_pp_size_photo_paths(array $student_row)
    {
        $col = 'pp_size_photo';
        $raw = isset($student_row[$col]) ? $student_row[$col] : null;
        $out = array(
            'db_column' => $col,
            'db_value' => $raw,
        );
        if ($raw === null || $raw === '') {
            $out['resolved_absolute'] = null;
            $out['is_file'] = false;
            $out['note'] = 'No stored path; after PATCH upload, Hemis sync will use $_FILES in the same request.';
            return $out;
        }

        $path = (string) $raw;
        $base = rtrim(FCPATH, "/\\") . DIRECTORY_SEPARATOR;
        if (!is_file($path)) {
            $try = $base . str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, ltrim($path, '/'));
            if (is_file($try)) {
                $path = $try;
            }
        }
        $out['resolved_absolute'] = $path;
        $out['is_file'] = is_file($path);
        if ($out['is_file']) {
            $out['bytes'] = filesize($path);
        }
        return $out;
    }

    /**
     * @param array $cfg hemis_integration_model::get_effective_config()
     */
    protected function _safe_hemis_config_summary(array $cfg)
    {
        return array(
            'sync_enabled' => !empty($cfg['sync_enabled']),
            'sync_student_update' => !empty($cfg['sync_student_update']),
            'mock_mode' => !empty($cfg['mock_mode']),
            'remote_base_url' => isset($cfg['remote_base_url']) ? (string) $cfg['remote_base_url'] : '',
            'local_mock_base_url' => isset($cfg['local_mock_base_url']) ? (string) $cfg['local_mock_base_url'] : '',
            'has_bearer_token' => !empty(trim((string) (isset($cfg['remote_bearer_token']) ? $cfg['remote_bearer_token'] : ''))),
        );
    }

    /**
     * Build multipart text fields matching api/Student::Update_patch validation rules.
     *
     * @param array $s students row
     * @return array<string,string>
     */
    protected function _build_patch_form_fields(array $s)
    {
        $this->load->model('program_model');

        $gender = strtolower(trim((string) ($s['gender'] ?? 'male')));
        if ($gender === 'female' || $gender === 'f') {
            $gender = 'female';
        } elseif ($gender === 'other') {
            $gender = 'other';
        } else {
            $gender = 'male';
        }

        $program_id = (int) ($s['program_id'] ?? 0);
        $program = $program_id > 0 ? $this->program_model->get_program_by_id($program_id) : null;
        $program_name = (is_array($program) && !empty($program['title'])) ? $program['title'] : 'Program';

        $faculty_name = 'Faculty';
        if (is_array($program) && !empty($program['faculty_id']) && $this->db->table_exists('faculty')) {
            $fq = $this->db->select('name')->from('faculty')->where('id', (int) $program['faculty_id'])->limit(1)->get();
            if ($fq && $fq->num_rows() > 0) {
                $fr = $fq->row_array();
                if (!empty($fr['name'])) {
                    $faculty_name = $fr['name'];
                }
            }
        }

        $class_id = isset($s['class_id']) ? (int) $s['class_id'] : 0;
        $level_name = 'Level';
        if ($class_id > 0) {
            $this->load->model('class_model');
            $cl = $this->class_model->getAll($class_id);
            if (is_array($cl)) {
                if (!empty($cl['level_name'])) {
                    $level_name = $cl['level_name'];
                } elseif (!empty($cl['class'])) {
                    $level_name = $cl['class'];
                }
            }
        }

        $admission_year_id = hemis_resolve_admission_year_id($s);
        if ($admission_year_id === null || $admission_year_id < 1) {
            $admission_year_id = (int) date('Y');
        }

        $cy = isset($s['complition_year']) ? preg_replace('/\D/', '', (string) $s['complition_year']) : '';
        if (strlen($cy) !== 4) {
            $cy = (string) ((int) date('Y') + 4);
        }

        $nepali = trim(
            (string) ($s['first_name_np'] ?? '') . ' '
            . (string) ($s['middle_name_np'] ?? '') . ' '
            . (string) ($s['last_name_np'] ?? '')
        );
        if ($nepali === '') {
            $nepali = 'टेस्ट नाम';
        }

        $do_bad = $s['dob_ad'] ?? $s['dob'] ?? date('Y-m-d');
        $do_bbs = '2070/01/01';

        $fields = array(
            'firstName' => (string) ($s['firstname'] ?? 'Test'),
            'middleName' => (string) ($s['middlename'] ?? ''),
            'lastName' => (string) ($s['lastname'] ?? 'Student'),
            'gender' => $gender,
            'admissionYearId' => (string) $admission_year_id,
            'campusId' => (string) (int) ($s['school_id'] ?? 1),
            'nepaliName' => $nepali,
            'doBBS' => $do_bbs,
            'doBAD' => (string) $do_bad,
            'ethnicity' => (string) (hemis_resolve_ethnicity_value($s) ?: 'Other'),
            'nationality' => (string) (hemis_resolve_nationality_value($s) ?: 'Nepali'),
            'disabilityStatus' => (string) ($s['disability_status'] ?? 'no'),
            'pProvince' => (string) ($s['permanent_province'] ?? '1'),
            'pDistrict' => (string) ($s['permanent_district'] ?? '1'),
            'pLocalLevel' => (string) ($s['permanent_local_level'] ?? '1'),
            'pWardNo' => (string) ($s['permanent_ward_no'] ?? '1'),
            'fatherName' => (string) ($s['father_name'] ?? 'Father'),
            'guardianName' => (string) ($s['guardian_name'] ?? 'Guardian'),
            'programId' => (string) max(1, $program_id ?: 1),
            'programName' => $program_name,
            'levelName' => $level_name,
            'facultyName' => $faculty_name,
            'complitionYear' => $cy,
            'dateOfEnrollment' => (string) ($s['admission_date'] ?? date('Y-m-d')),
            'fiscalYearId' => (string) (int) ($s['fiscal_year_id'] ?? 1),
            'year' => (string) ($s['semister_or_year_no'] ?? '1'),
            'religion' => (string) ($s['religion'] ?? 'Hindu'),
        );

        return $fields;
    }

    /**
     * @param string|null $image_param query ?image=
     * @return array{path:string}|array{error:string}
     */
    protected function _resolve_sample_image_path($image_param)
    {
        if ($image_param !== null && $image_param !== '') {
            $p = (string) $image_param;
            if (is_file($p) && is_readable($p)) {
                return array('path' => $p);
            }
            $try = rtrim(FCPATH, "/\\") . DIRECTORY_SEPARATOR . str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, ltrim($p, '/'));
            if (is_file($try) && is_readable($try)) {
                return array('path' => $try);
            }
            return array('error' => 'image= path not readable (tried absolute and FCPATH-relative): ' . $p);
        }

        $tmp = tempnam(sys_get_temp_dir(), 'tsync_');
        if ($tmp === false) {
            return array('error' => 'Could not create temp file for sample PNG');
        }
        $png = base64_decode(self::$minimal_png_b64, true);
        if ($png === false) {
            @unlink($tmp);
            return array('error' => 'Sample PNG decode failed');
        }
        $target = $tmp . '.png';
        @unlink($tmp);
        if (file_put_contents($target, $png) === false) {
            return array('error' => 'Could not write sample PNG to ' . $target);
        }
        return array('path' => $target);
    }

    protected function _json(array $data, $status = 200)
    {
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}

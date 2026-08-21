<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Local cache for UGC HEMIS reference metadata (Phase 1).
 *
 * Tables are created lazily to avoid a hard migration dependency.
 */
class Ugc_metadata_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function ensure_tables()
    {
        // Student extra FK columns (added lazily).
        if ($this->db->table_exists('students') && !$this->db->field_exists('ugc_program_id', 'students')) {
            $this->db->query("ALTER TABLE `students` ADD COLUMN `ugc_program_id` INT NULL AFTER `program_id`");
        }
        if ($this->db->table_exists('students') && !$this->db->field_exists('ugc_batch_id', 'students')) {
            $this->db->query("ALTER TABLE `students` ADD COLUMN `ugc_batch_id` INT NULL AFTER `ugc_program_id`");
        }
        if ($this->db->table_exists('students') && !$this->db->field_exists('ugc_fiscal_year_id', 'students')) {
            $this->db->query("ALTER TABLE `students` ADD COLUMN `ugc_fiscal_year_id` INT NULL AFTER `ugc_batch_id`");
        }
        if ($this->db->table_exists('students') && !$this->db->field_exists('ugc_t_province', 'students')) {
            $this->db->query("ALTER TABLE `students` ADD COLUMN `ugc_t_province` VARCHAR(100) NULL AFTER `temporary_province`");
            $this->db->query("ALTER TABLE `students` ADD COLUMN `ugc_t_district` VARCHAR(100) NULL AFTER `temporary_district`");
            $this->db->query("ALTER TABLE `students` ADD COLUMN `ugc_t_local_level` VARCHAR(150) NULL AFTER `temporary_local_level`");
            $this->db->query("ALTER TABLE `students` ADD COLUMN `ugc_p_province` VARCHAR(100) NULL AFTER `permanent_province`");
            $this->db->query("ALTER TABLE `students` ADD COLUMN `ugc_p_district` VARCHAR(100) NULL AFTER `permanent_district`");
            $this->db->query("ALTER TABLE `students` ADD COLUMN `ugc_p_local_level` VARCHAR(150) NULL AFTER `permanent_local_level`");
        }

        // Staff extra FK columns (added lazily).
        if ($this->db->table_exists('staff') && !$this->db->field_exists('ugc_department_id', 'staff')) {
            $this->db->query("ALTER TABLE `staff` ADD COLUMN `ugc_department_id` INT NULL AFTER `department`");
        }
        if ($this->db->table_exists('staff') && !$this->db->field_exists('ugc_section_id', 'staff')) {
            $this->db->query("ALTER TABLE `staff` ADD COLUMN `ugc_section_id` INT NULL AFTER `section_id`");
        }

        // Programs (ProgramMgmt/GetCollegePrograms)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `ugc_programs` (
              `program_id` INT NOT NULL,
              `program_name` VARCHAR(255) NULL,
              `short_name` VARCHAR(50) NULL,
              `level_id` INT NULL,
              `level_name` VARCHAR(100) NULL,
              `faculty_id` INT NULL,
              `faculty_name` VARCHAR(150) NULL,
              `program_type` VARCHAR(50) NULL,
              `duration` VARCHAR(20) NULL,
              `code` VARCHAR(50) NULL,
              `status` TINYINT(1) NULL,
              `raw_json` MEDIUMTEXT NULL,
              `synced_at` DATETIME NULL,
              PRIMARY KEY (`program_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Batch (/api/Batch)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `ugc_batches` (
              `id` INT NOT NULL,
              `name` VARCHAR(50) NULL,
              `batch_nepali` VARCHAR(50) NULL,
              `idx` INT NULL,
              `is_active` TINYINT(1) NULL,
              `raw_json` MEDIUMTEXT NULL,
              `synced_at` DATETIME NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Fiscal years (/api/FiscalYear) — authentication: none (per PDF)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `ugc_fiscal_years` (
              `id` INT NOT NULL,
              `year_nepali` VARCHAR(50) NULL,
              `year_english` VARCHAR(50) NULL,
              `active_fiscal_year` TINYINT(1) NULL,
              `idx` INT NULL,
              `raw_json` MEDIUMTEXT NULL,
              `synced_at` DATETIME NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Federal address (/api/Address/GetAll)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `ugc_addresses` (
              `id` INT NOT NULL,
              `combined_code` VARCHAR(20) NULL,
              `province_code` VARCHAR(10) NULL,
              `province` VARCHAR(100) NULL,
              `province_nepali` VARCHAR(100) NULL,
              `district_code` VARCHAR(10) NULL,
              `district` VARCHAR(100) NULL,
              `district_nepali` VARCHAR(100) NULL,
              `local_level_code` VARCHAR(10) NULL,
              `local_level` VARCHAR(150) NULL,
              `local_level_nepali` VARCHAR(150) NULL,
              `ecological_belt` VARCHAR(50) NULL,
              `no_of_ward` INT NULL,
              `raw_json` MEDIUMTEXT NULL,
              `synced_at` DATETIME NULL,
              PRIMARY KEY (`id`),
              KEY `idx_province` (`province`),
              KEY `idx_district` (`district`),
              KEY `idx_local_level` (`local_level`),
              KEY `idx_combined_code` (`combined_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Departments (/api/Management/Department)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `ugc_departments` (
              `id` INT NOT NULL,
              `department_name` VARCHAR(200) NULL,
              `status` TINYINT(1) NULL,
              `remarks` VARCHAR(255) NULL,
              `raw_json` MEDIUMTEXT NULL,
              `synced_at` DATETIME NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Sections (/api/Managent/Section)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `ugc_sections` (
              `id` INT NOT NULL,
              `section_name` VARCHAR(200) NULL,
              `status` TINYINT(1) NULL,
              `remarks` VARCHAR(255) NULL,
              `raw_json` MEDIUMTEXT NULL,
              `synced_at` DATETIME NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Employee positions (/api/EmployeePositions) — PDF §5.10
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `ugc_employee_positions` (
              `id` INT NOT NULL,
              `category` VARCHAR(100) NULL,
              `type` VARCHAR(100) NULL,
              `post_name` VARCHAR(255) NULL,
              `idx` INT NULL,
              `university_id` INT NULL,
              `status` TINYINT(1) NULL,
              `remarks` VARCHAR(255) NULL,
              `raw_json` MEDIUMTEXT NULL,
              `synced_at` DATETIME NULL,
              PRIMARY KEY (`id`),
              KEY `idx_category_type` (`category`, `type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        if ($this->db->table_exists('ugc_employee_positions')
            && !$this->db->field_exists('university_id', 'ugc_employee_positions')) {
            $this->load->dbforge();
            $this->dbforge->add_column('ugc_employee_positions', array(
                'university_id' => array(
                    'type' => 'INT',
                    'null' => true,
                ),
            ), 'idx');
        }
    }

    public function upsert_programs(array $rows)
    {
        $this->ensure_tables();
        $now = date('Y-m-d H:i:s');
        foreach ($rows as $r) {
            if (!is_array($r)) {
                continue;
            }
            $pid = isset($r['programId']) ? (int) $r['programId'] : (isset($r['id']) ? (int) $r['id'] : 0);
            if ($pid < 1) {
                continue;
            }
            $data = array(
                'program_id'   => $pid,
                'program_name' => $r['programName'] ?? null,
                'short_name'   => $r['shortName'] ?? null,
                'level_id'     => isset($r['levelId']) ? (int) $r['levelId'] : null,
                'level_name'   => $r['levelName'] ?? null,
                'faculty_id'   => isset($r['facultyId']) ? (int) $r['facultyId'] : null,
                'faculty_name' => $r['facultyName'] ?? null,
                'program_type' => $r['programType'] ?? null,
                'duration'     => $r['duration'] ?? null,
                'code'         => $r['code'] ?? null,
                'status'       => isset($r['status']) ? (int) ((bool) $r['status']) : null,
                'raw_json'     => json_encode($r),
                'synced_at'    => $now,
            );

            $exists = $this->db->select('program_id')->from('ugc_programs')->where('program_id', $pid)->get()->row_array();
            if ($exists) {
                $this->db->where('program_id', $pid)->update('ugc_programs', $data);
            } else {
                $this->db->insert('ugc_programs', $data);
            }
        }
    }

    public function get_programs_cached()
    {
        if (!$this->db->table_exists('ugc_programs')) {
            return array();
        }
        return $this->db->select('program_id as programId, program_name as programName, short_name as shortName, level_id as levelId, level_name as levelName, faculty_id as facultyId, faculty_name as facultyName, program_type as programType, duration, code, status')
            ->from('ugc_programs')
            ->order_by('level_name', 'ASC')
            ->order_by('program_name', 'ASC')
            ->get()->result_array();
    }

    public function get_batches_cached()
    {
        if (!$this->db->table_exists('ugc_batches')) {
            return array();
        }
        return $this->db->select('id, name, batch_nepali as batchNepali, idx as `index`, is_active as isActive')
            ->from('ugc_batches')
            ->order_by('idx', 'DESC')
            ->order_by('batch_nepali', 'DESC')
            ->get()->result_array();
    }

    public function get_fiscal_years_cached()
    {
        if (!$this->db->table_exists('ugc_fiscal_years')) {
            return array();
        }
        return $this->db->select('id, year_nepali as yearNepali, year_english as yearEnglish, active_fiscal_year as activeFiscalYear, idx as `index`')
            ->from('ugc_fiscal_years')
            ->order_by('active_fiscal_year', 'DESC')
            ->order_by('idx', 'DESC')
            ->get()->result_array();
    }

    public function get_addresses_cached()
    {
        if (!$this->db->table_exists('ugc_addresses')) {
            return array();
        }
        return $this->db->select('id, combined_code as combinedCode, province_code as provinceCode, province, province_nepali as provinceNepali, district_code as districtCode, district, district_nepali as districtNepali, local_level_code as localLevelCode, local_level as localLevel, local_level_nepali as localLevelNepali, ecological_belt as ecologicalBelt, no_of_ward as noOfWard')
            ->from('ugc_addresses')
            ->order_by('province', 'ASC')
            ->order_by('district', 'ASC')
            ->order_by('local_level', 'ASC')
            ->get()->result_array();
    }

    /**
     * Distinct UGC provinces for report / filter dropdowns.
     */
    public function get_distinct_provinces()
    {
        if (!$this->db->table_exists('ugc_addresses')) {
            return array();
        }
        return $this->db->select('DISTINCT province', false)
            ->from('ugc_addresses')
            ->where('province IS NOT NULL', null, false)
            ->where('province !=', '')
            ->order_by('province', 'ASC')
            ->get()
            ->result_array();
    }

    /**
     * Distinct districts within a UGC province label (fuzzy " Province" suffix).
     */
    public function get_districts_by_province_name($province)
    {
        $province = trim((string) $province);
        if ($province === '' || !$this->db->table_exists('ugc_addresses')) {
            return array();
        }
        $base = preg_replace('/\s+province$/i', '', $province);
        $this->db->select('DISTINCT district', false)
            ->from('ugc_addresses')
            ->group_start()
            ->where('province', $province)
            ->or_where('province', $base)
            ->or_where('province', $base . ' Province')
            ->group_end()
            ->where('district IS NOT NULL', null, false)
            ->where('district !=', '')
            ->order_by('district', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Local levels in a UGC province + district (combined_code + label).
     */
    public function get_local_levels_by_province_district($province, $district)
    {
        $province = trim((string) $province);
        $district = trim((string) $district);
        if ($province === '' || $district === '' || !$this->db->table_exists('ugc_addresses')) {
            return array();
        }
        $base = preg_replace('/\s+province$/i', '', $province);
        $this->db->select('combined_code, local_level')
            ->from('ugc_addresses')
            ->group_start()
            ->where('province', $province)
            ->or_where('province', $base)
            ->or_where('province', $base . ' Province')
            ->group_end()
            ->where('district', $district)
            ->where('local_level IS NOT NULL', null, false)
            ->where('local_level !=', '')
            ->order_by('local_level', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_departments_cached()
    {
        if (!$this->db->table_exists('ugc_departments')) {
            return array();
        }
        return $this->db->select('id, department_name as departmentName, status, remarks')
            ->from('ugc_departments')
            ->order_by('department_name', 'ASC')
            ->get()->result_array();
    }

    public function get_sections_cached()
    {
        if (!$this->db->table_exists('ugc_sections')) {
            return array();
        }
        return $this->db->select('id, section_name as sectionName, status, remarks')
            ->from('ugc_sections')
            ->order_by('section_name', 'ASC')
            ->get()->result_array();
    }

    public function upsert_batches(array $rows)
    {
        $this->ensure_tables();
        $now = date('Y-m-d H:i:s');
        foreach ($rows as $r) {
            if (!is_array($r) || empty($r['id'])) {
                continue;
            }
            $id = (int) $r['id'];
            $data = array(
                'id'           => $id,
                'name'         => $r['name'] ?? null,
                'batch_nepali' => $r['batchNepali'] ?? null,
                'idx'          => isset($r['index']) ? (int) $r['index'] : null,
                'is_active'    => isset($r['isActive']) ? (int) ((bool) $r['isActive']) : null,
                'raw_json'     => json_encode($r),
                'synced_at'    => $now,
            );
            $exists = $this->db->select('id')->from('ugc_batches')->where('id', $id)->get()->row_array();
            if ($exists) {
                $this->db->where('id', $id)->update('ugc_batches', $data);
            } else {
                $this->db->insert('ugc_batches', $data);
            }
        }
    }

    public function upsert_fiscal_years(array $rows)
    {
        $this->ensure_tables();
        $now = date('Y-m-d H:i:s');
        foreach ($rows as $r) {
            if (!is_array($r) || empty($r['id'])) {
                continue;
            }
            $id = (int) $r['id'];
            $data = array(
                'id'                => $id,
                'year_nepali'       => $r['yearNepali'] ?? null,
                'year_english'      => $r['yearEnglish'] ?? null,
                'active_fiscal_year'=> isset($r['activeFiscalYear']) ? (int) ((bool) $r['activeFiscalYear']) : null,
                'idx'               => isset($r['index']) ? (int) $r['index'] : null,
                'raw_json'          => json_encode($r),
                'synced_at'         => $now,
            );
            $exists = $this->db->select('id')->from('ugc_fiscal_years')->where('id', $id)->get()->row_array();
            if ($exists) {
                $this->db->where('id', $id)->update('ugc_fiscal_years', $data);
            } else {
                $this->db->insert('ugc_fiscal_years', $data);
            }
        }
    }

    public function upsert_addresses(array $rows)
    {
        $this->ensure_tables();
        $now = date('Y-m-d H:i:s');
        foreach ($rows as $r) {
            if (!is_array($r) || empty($r['id'])) {
                continue;
            }
            $id = (int) $r['id'];
            $data = array(
                'id'               => $id,
                'combined_code'    => $r['combinedCode'] ?? null,
                'province_code'    => $r['provinceCode'] ?? null,
                'province'         => $r['province'] ?? null,
                'province_nepali'  => $r['provinceNepali'] ?? null,
                'district_code'    => $r['districtCode'] ?? null,
                'district'         => $r['district'] ?? null,
                'district_nepali'  => $r['districtNepali'] ?? null,
                'local_level_code' => $r['localLevelCode'] ?? null,
                'local_level'      => $r['localLevel'] ?? null,
                'local_level_nepali'=> $r['localLevelNepali'] ?? null,
                'ecological_belt'  => $r['ecologicalBelt'] ?? null,
                'no_of_ward'       => isset($r['noOfWard']) ? (int) $r['noOfWard'] : null,
                'raw_json'         => json_encode($r),
                'synced_at'        => $now,
            );
            $exists = $this->db->select('id')->from('ugc_addresses')->where('id', $id)->get()->row_array();
            if ($exists) {
                $this->db->where('id', $id)->update('ugc_addresses', $data);
            } else {
                $this->db->insert('ugc_addresses', $data);
            }
        }
    }

    public function upsert_departments(array $rows)
    {
        $this->ensure_tables();
        $now = date('Y-m-d H:i:s');
        foreach ($rows as $r) {
            if (!is_array($r) || empty($r['id'])) {
                continue;
            }
            $id = (int) $r['id'];
            $data = array(
                'id'              => $id,
                'department_name' => $r['departmentName'] ?? null,
                'status'          => isset($r['status']) ? (int) ((bool) $r['status']) : null,
                'remarks'         => $r['remarks'] ?? null,
                'raw_json'        => json_encode($r),
                'synced_at'       => $now,
            );
            $exists = $this->db->select('id')->from('ugc_departments')->where('id', $id)->get()->row_array();
            if ($exists) {
                $this->db->where('id', $id)->update('ugc_departments', $data);
            } else {
                $this->db->insert('ugc_departments', $data);
            }
        }
    }

    public function upsert_sections(array $rows)
    {
        $this->ensure_tables();
        $now = date('Y-m-d H:i:s');
        foreach ($rows as $r) {
            if (!is_array($r) || empty($r['id'])) {
                continue;
            }
            $id = (int) $r['id'];
            $data = array(
                'id'           => $id,
                'section_name' => $r['sectionName'] ?? null,
                'status'       => isset($r['status']) ? (int) ((bool) $r['status']) : null,
                'remarks'      => $r['remarks'] ?? null,
                'raw_json'     => json_encode($r),
                'synced_at'    => $now,
            );
            $exists = $this->db->select('id')->from('ugc_sections')->where('id', $id)->get()->row_array();
            if ($exists) {
                $this->db->where('id', $id)->update('ugc_sections', $data);
            } else {
                $this->db->insert('ugc_sections', $data);
            }
        }
    }

    /**
     * Upsert rows from GET /api/EmployeePositions (normalized list items).
     *
     * @param array $rows List of arrays with id, category, type, postName, index, status, remarks
     */
    public function upsert_employee_positions(array $rows)
    {
        $this->ensure_tables();
        if (!$this->db->table_exists('ugc_employee_positions')) {
            return;
        }
        $hasUniversityIdCol = $this->db->field_exists('university_id', 'ugc_employee_positions');
        $now = date('Y-m-d H:i:s');
        foreach ($rows as $r) {
            if (!is_array($r) || empty($r['id'])) {
                continue;
            }
            $id = (int) $r['id'];
            $data = array(
                'id'         => $id,
                'category'   => $r['category'] ?? null,
                'type'       => $r['type'] ?? null,
                'post_name'  => $r['postName'] ?? ($r['post_name'] ?? null),
                'idx'        => isset($r['index']) ? (int) $r['index'] : (isset($r['idx']) ? (int) $r['idx'] : null),
                'status'     => isset($r['status']) ? (int) ((bool) $r['status']) : null,
                'remarks'    => $r['remarks'] ?? null,
                'raw_json'   => json_encode($r),
                'synced_at'  => $now,
            );
            if ($hasUniversityIdCol) {
                $uid = $r['universityId'] ?? $r['UniversityId'] ?? null;
                if ($uid !== null && $uid !== '') {
                    $data['university_id'] = (int) $uid;
                }
            }
            $exists = $this->db->select('id')->from('ugc_employee_positions')->where('id', $id)->get()->row_array();
            if ($exists) {
                $this->db->where('id', $id)->update('ugc_employee_positions', $data);
            } else {
                $this->db->insert('ugc_employee_positions', $data);
            }
        }
    }

    /**
     * Official HEMIS doc sample rows mapped to ugc_employee_positions columns:
     * id (UGC position id), category, type, post_name, idx, status, remarks.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function _document_sample_employee_positions_rows()
    {
        return array(
            array('id' => 38, 'category' => 'Teaching', 'type' => 'Teaching', 'post_name' => 'Professor', 'idx' => 1, 'status' => 1, 'remarks' => '', 'university_id' => 1),
            array('id' => 39, 'category' => 'Teaching', 'type' => 'Teaching', 'post_name' => 'Reader', 'idx' => 2, 'status' => 1, 'remarks' => '', 'university_id' => 1),
            array('id' => 42, 'category' => 'Teaching', 'type' => 'Teaching', 'post_name' => 'Lecturer', 'idx' => 3, 'status' => 1, 'remarks' => '', 'university_id' => 1),
            array('id' => 43, 'category' => 'Teaching', 'type' => 'Teaching', 'post_name' => 'Asst. Lecturer', 'idx' => 4, 'status' => 1, 'remarks' => '', 'university_id' => 1),
            array('id' => 44, 'category' => 'Teaching', 'type' => 'Teaching', 'post_name' => 'Teaching Assistant', 'idx' => 5, 'status' => 1, 'remarks' => '', 'university_id' => 1),
            array('id' => 45, 'category' => 'Teaching', 'type' => 'Teaching', 'post_name' => 'Other', 'idx' => 6, 'status' => 1, 'remarks' => '', 'university_id' => 1),
            array('id' => 40, 'category' => 'Nonteaching', 'type' => 'Administrative', 'post_name' => 'Ass. Accountant', 'idx' => 1, 'status' => 1, 'remarks' => '', 'university_id' => 1),
            array('id' => 41, 'category' => 'Nonteaching', 'type' => 'Other', 'post_name' => 'Office Assistant', 'idx' => 2, 'status' => 1, 'remarks' => '', 'university_id' => 1),
            array('id' => 46, 'category' => 'Nonteaching', 'type' => 'Administrative', 'post_name' => 'First Class', 'idx' => 7, 'status' => 1, 'remarks' => '', 'university_id' => 1),
            array('id' => 47, 'category' => 'Nonteaching', 'type' => 'Administrative', 'post_name' => 'Second Class', 'idx' => 8, 'status' => 1, 'remarks' => '', 'university_id' => 1),
            array('id' => 48, 'category' => 'Nonteaching', 'type' => 'Administrative', 'post_name' => 'Third Class', 'idx' => 9, 'status' => 1, 'remarks' => '', 'university_id' => 1),
            array('id' => 49, 'category' => 'Nonteaching', 'type' => 'Administrative', 'post_name' => 'Assistants', 'idx' => 10, 'status' => 1, 'remarks' => '', 'university_id' => 1),
            array('id' => 50, 'category' => 'Nonteaching', 'type' => 'Technical', 'post_name' => 'First Class', 'idx' => 11, 'status' => 1, 'remarks' => '', 'university_id' => 1),
            array('id' => 51, 'category' => 'Nonteaching', 'type' => 'Technical', 'post_name' => 'Second Class', 'idx' => 12, 'status' => 1, 'remarks' => '', 'university_id' => 1),
            array('id' => 52, 'category' => 'Nonteaching', 'type' => 'Technical', 'post_name' => 'Third Class', 'idx' => 13, 'status' => 1, 'remarks' => '', 'university_id' => 1),
            array('id' => 53, 'category' => 'Nonteaching', 'type' => 'Technical', 'post_name' => 'Assistants', 'idx' => 14, 'status' => 1, 'remarks' => '', 'university_id' => 1),
            array('id' => 54, 'category' => 'Nonteaching', 'type' => 'Other', 'post_name' => 'Driver', 'idx' => 15, 'status' => 1, 'remarks' => '', 'university_id' => 1),
            array('id' => 55, 'category' => 'Nonteaching', 'type' => 'Other', 'post_name' => 'Helper', 'idx' => 16, 'status' => 1, 'remarks' => '', 'university_id' => 1),
            array('id' => 56, 'category' => 'Nonteaching', 'type' => 'Other', 'post_name' => 'Other', 'idx' => 17, 'status' => 1, 'remarks' => '', 'university_id' => 1),
        );
    }

    /**
     * Rows for staff dropdown / Hemis_client cache fallback (DB only — no remote pull).
     *
     * @return array<int, array<string, mixed>>
     */
    protected function _employee_positions_rows_from_db()
    {
        return $this->db->select('id, category, type, post_name as postName, idx as `index`, status, remarks, raw_json')
            ->from('ugc_employee_positions')
            ->where('status', 1)
            ->order_by('category', 'ASC')
            ->order_by('type', 'ASC')
            ->order_by('idx', 'ASC')
            ->order_by('post_name', 'ASC')
            ->get()->result_array();
    }

    /**
     * Parse departmentId / sectionId from a cached position row (raw_json or scalar keys).
     *
     * @param array $row
     * @return array{departmentId:int,sectionId:int}
     */
    public function parse_position_row_org_ids(array $row)
    {
        $dept = 0;
        $sec = 0;
        $pick = function ($src, $keys) {
            foreach ($keys as $k) {
                if (isset($src[$k]) && $src[$k] !== '' && $src[$k] !== null) {
                    return (int) $src[$k];
                }
            }
            return 0;
        };
        $dept = $pick($row, array('departmentId', 'DepartmentId', 'department_id'));
        $sec = $pick($row, array('sectionId', 'SectionId', 'section_id'));
        if (($dept < 1 && $sec < 1) && !empty($row['raw_json'])) {
            $raw = json_decode((string) $row['raw_json'], true);
            if (is_array($raw)) {
                if ($dept < 1) {
                    $dept = $pick($raw, array('departmentId', 'DepartmentId', 'department_id'));
                }
                if ($sec < 1) {
                    $sec = $pick($raw, array('sectionId', 'SectionId', 'section_id'));
                }
            }
        }
        return array('departmentId' => $dept, 'sectionId' => $sec);
    }

    /**
     * Whether a HEMIS EmployeePositions row is teaching staff (category/type).
     */
    public function is_teaching_position_row(array $row)
    {
        $cat = strtolower(trim((string) ($row['category'] ?? '')));
        $typ = strtolower(trim((string) ($row['type'] ?? '')));
        return ($cat === 'teaching' || strpos($typ, 'teach') !== false);
    }

    /**
     * Positions for staff forms with parsed org ids and isTeaching flag.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get_employee_positions_for_staff_form()
    {
        $rows = $this->get_employee_positions_cached_or_refresh();
        if (!is_array($rows)) {
            return array();
        }
        foreach ($rows as $i => $row) {
            if (!is_array($row)) {
                continue;
            }
            $org = $this->parse_position_row_org_ids($row);
            $rows[$i]['departmentId'] = $org['departmentId'];
            $rows[$i]['sectionId'] = $org['sectionId'];
            $rows[$i]['isTeaching'] = $this->is_teaching_position_row($row);
        }
        return $rows;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function get_employee_positions_from_db_only()
    {
        $this->ensure_tables();
        if (!$this->db->table_exists('ugc_employee_positions')) {
            return array();
        }

        return $this->_employee_positions_rows_from_db();
    }

    /**
     * Lookup one cached HEMIS employee position by UGC id.
     *
     * @param int $id
     * @return array|null { id, category, type, postName, ... }
     */
    public function get_employee_position_by_id($id)
    {
        $id = (int) $id;
        if ($id < 1 || !$this->db->table_exists('ugc_employee_positions')) {
            return null;
        }
        $row = $this->db->select('id, category, type, post_name as postName, idx as `index`, status, remarks, raw_json')
            ->from('ugc_employee_positions')
            ->where('id', $id)
            ->limit(1)
            ->get()
            ->row_array();
        if (!is_array($row) || empty($row)) {
            return null;
        }
        $org = $this->parse_position_row_org_ids($row);
        $row['departmentId'] = $org['departmentId'];
        $row['sectionId'] = $org['sectionId'];
        $row['isTeaching'] = $this->is_teaching_position_row($row);
        return $row;
    }

    /**
     * Insert doc sample positions that are not already present (INSERT IGNORE per row).
     *
     * @return int Number of rows newly inserted this run
     */
    public function seed_employee_positions_if_empty()
    {
        $table = 'ugc_employee_positions';
        $this->ensure_tables();
        if (!$this->db->table_exists($table)) {
            return 0;
        }

        $count = (int) $this->db->count_all($table);
        if ($count > 0) {
            log_message('info', 'HEMIS: positions cache already has ' . $count . ' rows; inserting missing seed ids only');
        }

        $hasUniversityIdCol = $this->db->field_exists('university_id', $table);
        $now = date('Y-m-d H:i:s');
        $inserted = 0;
        foreach ($this->_document_sample_employee_positions_rows() as $row) {
            $id = (int) $row['id'];
            $universityId = isset($row['university_id']) ? (int) $row['university_id'] : null;
            $payload = array(
                'id'        => $id,
                'category'  => $row['category'],
                'type'      => $row['type'],
                'post_name' => $row['post_name'],
                'idx'       => isset($row['idx']) ? (int) $row['idx'] : null,
                'status'    => isset($row['status']) ? (int) $row['status'] : 1,
                'remarks'   => isset($row['remarks']) ? (string) $row['remarks'] : '',
                'raw_json'  => json_encode(array(
                    'id'           => $id,
                    'category'     => $row['category'],
                    'type'         => $row['type'],
                    'postName'     => $row['post_name'],
                    'index'        => isset($row['idx']) ? (int) $row['idx'] : null,
                    'status'       => isset($row['status']) ? (bool) $row['status'] : true,
                    'remarks'      => $row['remarks'] ?? '',
                    'universityId' => $universityId,
                    '_seed'        => 'hemis_doc_sample',
                )),
                'synced_at' => $now,
            );
            if ($hasUniversityIdCol && $universityId !== null) {
                $payload['university_id'] = $universityId;
            }
            $sql = $this->db->insert_string($table, $payload);
            $sql = preg_replace('/^INSERT\s+INTO/i', 'INSERT IGNORE INTO', $sql);
            $this->db->query($sql);
            if ($this->db->affected_rows() > 0) {
                $inserted++;
            }
        }

        log_message('info', 'HEMIS: seeded employee positions from doc sample; new rows this run: ' . $inserted);
        return $inserted;
    }

    /**
     * TEMPORARY diagnostics / full reseed — clears cache table only.
     *
     * @return bool
     */
    public function truncate_employee_positions_cache()
    {
        $table = 'ugc_employee_positions';
        if (!$this->db->table_exists($table)) {
            return false;
        }
        $this->db->truncate($table);
        return true;
    }

    /**
     * Cached employee positions for staff forms; refreshes from HEMIS when table is empty.
     *
     * @param bool $force_refresh When true, pull from HEMIS again and replace cache.
     * @return array rows: id, category, type, postName, index, status, remarks
     */
    public function get_employee_positions_cached_or_refresh($force_refresh = false)
    {
        $this->ensure_tables();
        if (!$this->db->table_exists('ugc_employee_positions')) {
            return array();
        }

        $count = (int) $this->db->count_all('ugc_employee_positions');
        if (!$force_refresh && $count > 0) {
            return $this->_employee_positions_rows_from_db();
        }

        $this->load->library('hemis_client');
        $r = $this->hemis_client->hemis_get_employee_positions();
        if (empty($r['ok']) || !isset($r['data'])) {
            return $this->_employee_positions_rows_from_db();
        }

        $data = $r['data'];
        $list = array();
        if (is_array($data)) {
            if ($this->_hemis_positions_is_list($data)) {
                foreach ($data as $row) {
                    if (is_array($row) && !empty($row['id'])) {
                        $list[] = $row;
                    }
                }
            } elseif (!empty($data['id'])) {
                $list[] = $data;
            }
        }
        if (!empty($list)) {
            $this->upsert_employee_positions($list);
        }

        return $this->_employee_positions_rows_from_db();
    }

    /**
     * @param array $arr
     * @return bool
     */
    protected function _hemis_positions_is_list(array $arr)
    {
        if ($arr === array()) {
            return true;
        }
        return array_keys($arr) === range(0, count($arr) - 1);
    }
}


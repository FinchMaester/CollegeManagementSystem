<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Persistence for HEMIS-shaped Library API (api_library table).
 */
class Library_model extends CI_Model
{
    protected $table = 'api_library';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * @param array $data camelCase keys from API
     * @return int|false insert id
     */
    public function create_library(array $data)
    {
        if (!$this->db->table_exists($this->table)) {
            log_message('error', 'api_library table missing; run application/sql/api_library_table.sql');
            return false;
        }
        $row = $this->_map_to_row($data);
        $row['created_at'] = date('Y-m-d H:i:s');
        $row['updated_at'] = $row['created_at'];
        if ($this->db->insert($this->table, $row)) {
            return (int) $this->db->insert_id();
        }
        return false;
    }

    /**
     * @return array[] list of camelCase rows (PDF GET /api/Library)
     */
    public function get_all_libraries()
    {
        if (!$this->db->table_exists($this->table)) {
            return array();
        }
        $this->db->order_by('id', 'ASC');
        $rows = $this->db->get($this->table)->result_array();
        $out = array();
        foreach ($rows as $row) {
            $item = $this->_map_from_row($row);
            $item['buildingName'] = '';
            $item['campusName'] = '';
            $out[] = $item;
        }
        return $out;
    }

    /**
     * @return array|null camelCase API shape
     */
    public function get_library($id)
    {
        if (!$this->db->table_exists($this->table)) {
            return null;
        }
        $q = $this->db->get_where($this->table, array('id' => (int) $id));
        $row = $q->row_array();
        return $row ? $this->_map_from_row($row) : null;
    }

    /**
     * @param array $data partial camelCase keys
     * @return bool
     */
    public function update_library($id, array $data)
    {
        if (!$this->db->table_exists($this->table)) {
            return false;
        }
        $row = $this->_map_to_row($data, true);
        if (empty($row)) {
            return false;
        }
        $row['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', (int) $id);
        return $this->db->update($this->table, $row) && $this->db->affected_rows() >= 0;
    }

    protected function _map_to_row(array $data, $partial = false)
    {
        $map = array(
            'libraryName'             => 'library_name',
            'buildingId'              => 'building_id',
            'campusId'                => 'campus_id',
            'noOfLibraryRooms'        => 'no_library_rooms',
            'totalAreaOfLibraryRoom'  => 'total_area_library_room',
            'noOfReadingHalls'        => 'no_reading_halls',
            'areaOfReadingHall'       => 'area_reading_hall',
            'noOfBooks'               => 'no_books',
            'noOfDailyJournals'       => 'no_daily_journals',
            'noOfWeeklyJournals'      => 'no_weekly_journals',
            'noOfMonthlyJournals'     => 'no_monthly_journals',
            'noOfAnnualJournals'      => 'no_annual_journals',
            'hasELibraryFacility'     => 'has_e_library_facility',
            'remarks'                 => 'remarks',
        );
        $row = array();
        foreach ($map as $camel => $col) {
            if (!array_key_exists($camel, $data)) {
                continue;
            }
            $v = $data[$camel];
            if ($camel === 'hasELibraryFacility') {
                $row[$col] = $v ? 1 : 0;
            } else {
                $row[$col] = $v;
            }
        }
        if (!$partial && empty($row)) {
            return array();
        }
        return $row;
    }

    protected function _map_from_row(array $row)
    {
        return array(
            'id'                       => (int) $row['id'],
            'libraryName'             => $row['library_name'],
            'buildingId'              => (int) $row['building_id'],
            'campusId'                => (int) $row['campus_id'],
            'noOfLibraryRooms'        => (int) $row['no_library_rooms'],
            'totalAreaOfLibraryRoom'  => (float) $row['total_area_library_room'],
            'noOfReadingHalls'        => (int) $row['no_reading_halls'],
            'areaOfReadingHall'       => (float) $row['area_reading_hall'],
            'noOfBooks'               => (int) $row['no_books'],
            'noOfDailyJournals'       => (int) $row['no_daily_journals'],
            'noOfWeeklyJournals'      => (int) $row['no_weekly_journals'],
            'noOfMonthlyJournals'     => (int) $row['no_monthly_journals'],
            'noOfAnnualJournals'      => (int) $row['no_annual_journals'],
            'hasELibraryFacility'     => (bool) $row['has_e_library_facility'],
            'remarks'                 => $row['remarks'],
        );
    }
}

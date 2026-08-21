<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('staff_hemis_level_options')) {
    function staff_hemis_level_options()
    {
        return array('SEE/SLC', '+2', 'Bachelors', 'Masters', 'CTEVT');
    }
}

if (!function_exists('staff_hemis_board_options')) {
    function staff_hemis_board_options()
    {
        return array(
            'Tribhuvan University(TU)'   => 'Tribhuvan University (TU)',
            'Pokhara University(PU)'     => 'Pokhara University (PU)',
            'Purbanchal University(PU)'  => 'Purbanchal University (PU)',
            'Kathmandu University(KU)'   => 'Kathmandu University (KU)',
            'Nepal Sankrit University'   => 'Nepal Sankrit University',
        );
    }
}

if (!function_exists('staff_hemis_decode_json_list')) {
    function staff_hemis_decode_json_list($raw)
    {
        if ($raw === null || $raw === '') {
            return array();
        }
        if (is_array($raw)) {
            return array_values($raw);
        }
        $decoded = json_decode((string) $raw, true);
        return is_array($decoded) ? array_values($decoded) : array();
    }
}

if (!function_exists('staff_hemis_qualification_rows_from_staff')) {
    /**
     * Build repeatable qualification rows for staff create/edit forms.
     *
     * @param array $staff
     * @return array[]
     */
    function staff_hemis_qualification_rows_from_staff(array $staff = array())
    {
        $boards = staff_hemis_decode_json_list(isset($staff['previous_board_university_college']) ? $staff['previous_board_university_college'] : '');
        $regs = staff_hemis_decode_json_list(isset($staff['previous_registration_no']) ? $staff['previous_registration_no'] : '');
        $legacy_levels = staff_hemis_decode_json_list(isset($staff['previous_level_of_study']) ? $staff['previous_level_of_study'] : '');
        $legacy_institutions = staff_hemis_decode_json_list(isset($staff['previous_institution_name']) ? $staff['previous_institution_name'] : '');

        $graduated_list = staff_hemis_decode_json_list(isset($staff['graduated_from']) ? $staff['graduated_from'] : '');
        if (empty($graduated_list) && !empty($staff['graduated_from']) && is_string($staff['graduated_from'])) {
            $graduated_list = array($staff['graduated_from']);
        }
        if (empty($graduated_list) && !empty($legacy_institutions)) {
            $graduated_list = $legacy_institutions;
        }

        $level_list = staff_hemis_decode_json_list(isset($staff['level_name']) ? $staff['level_name'] : '');
        if (empty($level_list) && !empty($staff['level_name']) && is_string($staff['level_name'])) {
            $level_list = array($staff['level_name']);
        }
        if (empty($level_list) && !empty($legacy_levels)) {
            $level_list = $legacy_levels;
        }

        $program_list = staff_hemis_decode_json_list(isset($staff['program_name']) ? $staff['program_name'] : '');
        if (empty($program_list) && !empty($staff['program_name']) && is_string($staff['program_name'])) {
            $program_list = array($staff['program_name']);
        }

        $faculty_list = staff_hemis_decode_json_list(isset($staff['faculty_name']) ? $staff['faculty_name'] : '');
        if (empty($faculty_list) && !empty($staff['faculty_name']) && is_string($staff['faculty_name'])) {
            $faculty_list = array($staff['faculty_name']);
        }

        $enrolled_list = staff_hemis_decode_json_list(isset($staff['enrolled_year']) ? $staff['enrolled_year'] : '');
        if (empty($enrolled_list) && !empty($staff['enrolled_year']) && is_string($staff['enrolled_year'])) {
            $enrolled_list = array($staff['enrolled_year']);
        }

        $passed_list = staff_hemis_decode_json_list(isset($staff['passed_year']) ? $staff['passed_year'] : '');
        if (empty($passed_list) && !empty($staff['passed_year']) && is_string($staff['passed_year'])) {
            $passed_list = array($staff['passed_year']);
        }

        $count = max(
            1,
            count($boards),
            count($regs),
            count($graduated_list),
            count($level_list),
            count($program_list),
            count($faculty_list),
            count($enrolled_list),
            count($passed_list)
        );

        $rows = array();
        for ($i = 0; $i < $count; $i++) {
            $rows[] = array(
                'graduated_from'          => isset($graduated_list[$i]) ? $graduated_list[$i] : '',
                'level_name'              => isset($level_list[$i]) ? $level_list[$i] : '',
                'program_name'            => isset($program_list[$i]) ? $program_list[$i] : '',
                'previous_board_university_college' => isset($boards[$i]) ? $boards[$i] : '',
                'previous_registration_no' => isset($regs[$i]) ? $regs[$i] : '',
                'faculty_name'            => isset($faculty_list[$i]) ? $faculty_list[$i] : '',
                'enrolled_year'           => isset($enrolled_list[$i]) ? $enrolled_list[$i] : '',
                'passed_year'             => isset($passed_list[$i]) ? $passed_list[$i] : '',
            );
        }

        return $rows;
    }
}

if (!function_exists('staff_hemis_first_non_empty_scalar')) {
    function staff_hemis_first_non_empty_scalar($posted)
    {
        if (is_array($posted)) {
            foreach ($posted as $v) {
                $s = trim((string) $v);
                if ($s !== '') {
                    return $s;
                }
            }
            return '';
        }
        return trim((string) $posted);
    }
}

if (!function_exists('staff_hemis_filter_post_array')) {
    function staff_hemis_filter_post_array($posted)
    {
        if (!is_array($posted)) {
            $s = trim((string) $posted);
            return $s === '' ? array() : array($s);
        }
        $out = array();
        foreach ($posted as $v) {
            $s = trim((string) $v);
            if ($s !== '') {
                $out[] = $s;
            }
        }
        return $out;
    }
}

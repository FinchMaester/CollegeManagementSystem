<?php

defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('marksheet_layout_default_columns')) {
    /**
     * Default column definitions when JSON is empty (theory-style marks).
     *
     * @return array<int, array{id:string,label:string,visible:bool}>
     */
    function marksheet_layout_default_columns()
    {
        return array(
            array('id' => 'subject_name', 'label' => 'Subject', 'visible' => true),
            array('id' => 'full_marks', 'label' => 'Full Marks', 'visible' => true),
            array('id' => 'pass_marks', 'label' => 'Pass Marks', 'visible' => true),
            array('id' => 'obtained_marks', 'label' => 'Obtained', 'visible' => true),
            array('id' => 'result_pf', 'label' => 'Result', 'visible' => true),
            array('id' => 'remarks', 'label' => 'Remarks', 'visible' => false),
        );
    }
}

if (!function_exists('marksheet_layout_preset')) {
    /**
     * Named presets for the designer (theory / practical / gpa-style).
     *
     * @param string $name theory|practical|gpa|bbs
     * @return array
     */
    function marksheet_layout_preset($name)
    {
        $name = strtolower((string) $name);
        if ($name === 'bbs') {
            return array(
                array('id' => 'subject_name', 'label' => 'Subject', 'visible' => true),
                array('id' => 'full_marks', 'label' => 'Full Marks', 'visible' => true),
                array('id' => 'pass_marks', 'label' => 'Pass Marks', 'visible' => true),
                array('id' => 'obtained_marks', 'label' => 'Mark Obtained', 'visible' => true),
            );
        }
        if ($name === 'practical') {
            return array(
                array('id' => 'subject_name', 'label' => 'Subject', 'visible' => true),
                array('id' => 'full_marks', 'label' => 'Full Marks', 'visible' => true),
                array('id' => 'pass_marks', 'label' => 'Pass Marks', 'visible' => true),
                array('id' => 'viva_marks', 'label' => 'Viva', 'visible' => true),
                array('id' => 'project_marks', 'label' => 'Project', 'visible' => true),
                array('id' => 'obtained_marks', 'label' => 'Total Obtained', 'visible' => true),
                array('id' => 'grade_letter', 'label' => 'Grade', 'visible' => true),
                array('id' => 'remarks', 'label' => 'Remarks', 'visible' => true),
            );
        }
        if ($name === 'gpa') {
            return array(
                array('id' => 'sn', 'label' => 'SN', 'visible' => true),
                array('id' => 'subject_name', 'label' => 'Subject', 'visible' => true),
                array('id' => 'credit_hours', 'label' => 'Credit Hrs', 'visible' => true),
                array('id' => 'grade_point', 'label' => 'Grade Point', 'visible' => true),
                array('id' => 'letter_grade', 'label' => 'Grade', 'visible' => true),
                array('id' => 'remarks', 'label' => 'Remarks', 'visible' => false),
            );
        }
        return marksheet_layout_default_columns();
    }
}

if (!function_exists('marksheet_parse_layout_json')) {
    /**
     * @param string|null $json
     * @return array|null
     */
    function marksheet_parse_layout_json($json)
    {
        if ($json === null || $json === '') {
            return null;
        }
        $decoded = json_decode($json, true);
        if (!is_array($decoded) || empty($decoded)) {
            return null;
        }
        $out = array();
        foreach ($decoded as $col) {
            if (!is_array($col) || empty($col['id'])) {
                continue;
            }
            $vis = true;
            if (isset($col['visible'])) {
                $vis = ($col['visible'] === true || $col['visible'] === 1 || $col['visible'] === '1');
            }
            $out[] = array(
                'id'      => (string) $col['id'],
                'label'   => isset($col['label']) ? (string) $col['label'] : $col['id'],
                'visible' => $vis,
            );
        }
        return $out ?: null;
    }
}

if (!function_exists('marksheet_visible_columns')) {
    /**
     * @param array|null $columns
     * @return array
     */
    function marksheet_visible_columns($columns)
    {
        if (empty($columns) || !is_array($columns)) {
            return array();
        }
        $vis = array();
        foreach ($columns as $c) {
            if (isset($c['visible']) && $c['visible'] === false) {
                continue;
            }
            $vis[] = $c;
        }
        return $vis;
    }
}

if (!function_exists('marksheet_student_detail_default_layout')) {
    /**
     * Default two-column student identity rows (matches legacy print layout).
     *
     * @return array{left:array,right:array}
     */
    function marksheet_student_detail_default_layout()
    {
        return array(
            'left'  => array(
                array('id' => 'admission_no', 'label' => 'Admission No', 'visible' => true),
                array('id' => 'faculty', 'label' => 'Faculty', 'visible' => true),
                array('id' => 'roll_no', 'label' => 'Roll No.', 'visible' => true),
                array('id' => 'father_name', 'label' => 'Father / Husband Name', 'visible' => true),
                array('id' => 'dob', 'label' => 'Date Of Birth', 'visible' => true),
            ),
            'right' => array(
                array('id' => 'symbol_no', 'label' => 'Symbol Number', 'visible' => true),
                array('id' => 'student_name', 'label' => 'Name', 'visible' => true),
                array('id' => 'mother_name', 'label' => 'Mother Name', 'visible' => true),
                array('id' => 'class_section', 'label' => 'Academic Level', 'visible' => true),
                array('id' => 'program', 'label' => 'Program', 'visible' => false),
                array('id' => 'address', 'label' => 'Address', 'visible' => false),
                array('id' => 'gender', 'label' => 'Gender', 'visible' => false),
            ),
        );
    }
}

if (!function_exists('marksheet_parse_student_detail_json')) {
    /**
     * @param string|null $json
     * @return array{left:array,right:array}
     */
    function marksheet_parse_student_detail_json($json)
    {
        $def = marksheet_student_detail_default_layout();
        if ($json === null || $json === '') {
            return $def;
        }
        $d = json_decode($json, true);
        if (!is_array($d) || !isset($d['left']) || !isset($d['right'])) {
            return $def;
        }
        $norm_side = function ($arr) {
            $out = array();
            if (!is_array($arr)) {
                return $out;
            }
            foreach ($arr as $it) {
                if (!is_array($it) || empty($it['id'])) {
                    continue;
                }
                $vis = true;
                if (isset($it['visible'])) {
                    $vis = ($it['visible'] === true || $it['visible'] === 1 || $it['visible'] === '1');
                }
                $out[] = array(
                    'id'      => (string) $it['id'],
                    'label'   => isset($it['label']) ? (string) $it['label'] : (string) $it['id'],
                    'visible' => $vis,
                );
            }
            return $out;
        };
        $left  = $norm_side($d['left']);
        $right = $norm_side($d['right']);
        if (empty($left) && empty($right)) {
            return $def;
        }
        return array('left' => $left, 'right' => $right);
    }
}

if (!function_exists('marksheet_student_detail_template_allows')) {
    /**
     * Respect legacy template_marksheets switches for each field.
     *
     * @param object $template template_marksheets row
     * @param string $field_id
     * @return bool
     */
    function marksheet_student_detail_template_allows($template, $field_id)
    {
        if (!is_object($template)) {
            return true;
        }
        $map = array(
            'admission_no'  => 'is_admission_no',
            'roll_no'       => 'is_roll_no',
            'father_name'   => 'is_father_name',
            'mother_name'   => 'is_mother_name',
            'dob'           => 'is_dob',
            'class_section' => 'is_class',
            'program'       => 'is_program',
            'address'       => 'is_address',
            'gender'        => 'is_gender',
        );
        if (!isset($map[$field_id])) {
            return true;
        }
        $prop = $map[$field_id];
        return !empty($template->$prop);
    }
}

if (!function_exists('marksheet_footer_default_config')) {
    /**
     * Printed marksheet footer slots (signature row). Order: program teacher, date, college chief, seal.
     *
     * @return array<string, array{show:bool,label:string}>
     */
    function marksheet_footer_default_config()
    {
        return array(
            'program_teacher' => array('show' => true, 'label' => 'Program Teacher'),
            'date'            => array('show' => true, 'label' => 'Date'),
            'college_chief'   => array('show' => true, 'label' => 'College Chief'),
            'college_seal'    => array('show' => true, 'label' => 'College Seal'),
        );
    }
}

if (!function_exists('marksheet_parse_footer_json')) {
    /**
     * @param string|null $json
     * @return array<string, array{show:bool,label:string}>
     */
    function marksheet_parse_footer_json($json)
    {
        $def = marksheet_footer_default_config();
        if ($json === null || $json === '') {
            return $def;
        }
        $d = json_decode($json, true);
        if (!is_array($d)) {
            return $def;
        }
        foreach (array_keys($def) as $k) {
            if (!isset($d[$k]) || !is_array($d[$k])) {
                continue;
            }
            if (array_key_exists('show', $d[$k])) {
                $def[$k]['show'] = ($d[$k]['show'] === true || $d[$k]['show'] === 1 || $d[$k]['show'] === '1');
            }
            if (isset($d[$k]['label']) && (string) $d[$k]['label'] !== '') {
                $def[$k]['label'] = (string) $d[$k]['label'];
            }
        }
        return $def;
    }
}

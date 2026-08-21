<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Two-column student identity block (order from template.student_detail_layout_json).
 *
 * @var object $template
 * @var array  $student_value
 * @var object $exam
 * @var object $sch_setting
 * @var string $symbol_no display symbol from students table (parent resolves)
 */

$this->load->helper('marksheet_layout');
$__sd = marksheet_parse_student_detail_json(isset($template->student_detail_layout_json) ? $template->student_detail_layout_json : null);

$__rn = ($exam->use_exam_roll_no)
    ? ($student_value['exam_roll_no'] ?? '')
    : ($student_value['student_roll_no'] ?? $student_value['roll_no'] ?? '');

$__ci = &get_instance();
$__smid = (is_object($sch_setting) && isset($sch_setting->middlename)) ? $sch_setting->middlename : '';
$__slast = (is_object($sch_setting) && isset($sch_setting->lastname)) ? $sch_setting->lastname : '';

$__render_sd_cell = function ($field_id) use ($student_value, $__smid, $__slast, $symbol_no, $__rn, $template, $__ci) {
    switch ($field_id) {
        case 'admission_no':
            echo htmlspecialchars((string) ($student_value['admission_no'] ?? ''), ENT_QUOTES, 'UTF-8');
            return;
        case 'roll_no':
            echo htmlspecialchars((string) $__rn, ENT_QUOTES, 'UTF-8');
            return;
        case 'faculty':
            $fac = trim((string) ($student_value['faculty_name'] ?? ''));
            if ($fac === '') {
                $fac = trim((string) ($student_value['section'] ?? ''));
            }
            echo htmlspecialchars($fac, ENT_QUOTES, 'UTF-8');
            return;
        case 'father_name':
            echo htmlspecialchars((string) ($student_value['father_name'] ?? ''), ENT_QUOTES, 'UTF-8');
            return;
        case 'dob':
            echo !empty($student_value['dob']) ? htmlspecialchars($__ci->customlib->dateformat($student_value['dob']), ENT_QUOTES, 'UTF-8') : '';
            return;
        case 'symbol_no':
            echo htmlspecialchars((string) $symbol_no, ENT_QUOTES, 'UTF-8');
            return;
        case 'student_name':
            echo htmlspecialchars(
                $__ci->customlib->getFullName(
                    $student_value['firstname'] ?? '',
                    $student_value['middlename'] ?? '',
                    $student_value['lastname'] ?? '',
                    $__smid,
                    $__slast
                ),
                ENT_QUOTES,
                'UTF-8'
            );
            return;
        case 'mother_name':
            echo htmlspecialchars((string) ($student_value['mother_name'] ?? ''), ENT_QUOTES, 'UTF-8');
            return;
        case 'class_section':
            if (!empty($template->is_section) && !empty($student_value['section'])) {
                echo htmlspecialchars((string) ($student_value['class'] ?? ''), ENT_QUOTES, 'UTF-8')
                    . ' (' . htmlspecialchars((string) ($student_value['section'] ?? ''), ENT_QUOTES, 'UTF-8') . ')';
            } elseif (!empty($student_value['class'])) {
                echo htmlspecialchars((string) $student_value['class'], ENT_QUOTES, 'UTF-8');
            } else {
                echo htmlspecialchars((string) ($student_value['class_id'] ?? ''), ENT_QUOTES, 'UTF-8');
            }
            return;
        case 'program':
            $__p = trim((string) ($student_value['program_title'] ?? ''));
            if ($__p === '') {
                $__p = trim((string) ($student_value['program_name'] ?? $student_value['program'] ?? ''));
            }
            echo htmlspecialchars($__p, ENT_QUOTES, 'UTF-8');
            return;
        case 'address':
            $__a = trim((string) ($student_value['current_address'] ?? ''));
            if ($__a === '') {
                $__a = trim((string) ($student_value['permanent_address'] ?? ''));
            }
            echo htmlspecialchars($__a, ENT_QUOTES, 'UTF-8');
            return;
        case 'gender':
            echo htmlspecialchars(ucfirst(strtolower((string) ($student_value['gender'] ?? ''))), ENT_QUOTES, 'UTF-8');
            return;
        default:
            echo '';
    }
};

$__emit_rows = function ($rows) use ($__render_sd_cell, $template) {
    foreach ($rows as $row) {
        if (isset($row['visible']) && $row['visible'] === false) {
            continue;
        }
        if (!marksheet_student_detail_template_allows($template, $row['id'])) {
            continue;
        }
        echo '<tr><th style="text-align:left">' . htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8') . '</th><td style="text-align:left">';
        $__render_sd_cell($row['id']);
        echo '</td></tr>';
    }
};
?>
    <!-- student identity (configurable columns) -->
    <table cellpadding="0" cellspacing="0" width="98%" style="margin:8px auto 0;">
        <tr>
            <td style="width:50%;vertical-align:top;">
                <table class="denifittable" style="width:100%">
                    <?php $__emit_rows($__sd['left']); ?>
                </table>
            </td>
            <td style="width:50%;vertical-align:top;">
                <table class="denifittable" style="width:100%">
                    <?php $__emit_rows($__sd['right']); ?>
                </table>
            </td>
        </tr>
    </table>

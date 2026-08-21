<?php
/**
 * HEMIS Employee Position dropdown (GET /api/EmployeePositions cache).
 * Expects: $ugc_employee_positions (array), $sel_pos (scalar), $position_category (string, optional)
 */
$ugc_pos = isset($ugc_employee_positions) && is_array($ugc_employee_positions) ? $ugc_employee_positions : array();
$sel_pos = isset($sel_pos) ? $sel_pos : '';
$position_category = isset($position_category) ? (string) $position_category : '';
?>
<input type="hidden" id="hemis_position_category_legacy" value="<?php echo html_escape($position_category); ?>" style="display:none" />
<?php if (empty($ugc_pos)) { ?>
<div class="alert alert-warning">
    UGC Employee Positions unavailable. Enter position ID manually, then save staff and refresh metadata.
</div>
<input type="number"
       id="employee_position_id"
       name="employee_position_id"
       value="<?php echo html_escape($sel_pos); ?>"
       class="form-control"
       placeholder="Enter HEMIS position ID" />
<?php } else { ?>
<select id="employee_position_id" name="employee_position_id" class="form-control" required>
    <option value=""><?php echo $this->lang->line('select'); ?></option>
    <?php
    $by_cat = array();
    foreach ($ugc_pos as $row) {
        if (!is_array($row) || empty($row['id'])) {
            continue;
        }
        $cat = (isset($row['category']) && (string) $row['category'] !== '') ? (string) $row['category'] : 'Other';
        if (!isset($by_cat[$cat])) {
            $by_cat[$cat] = array();
        }
        $by_cat[$cat][] = $row;
    }
    ksort($by_cat);
    foreach ($by_cat as $cat_label => $rows) {
        echo '<optgroup label="' . htmlspecialchars($cat_label, ENT_QUOTES, 'UTF-8') . '">';
        foreach ($rows as $row) {
            $pid = (int) $row['id'];
            $typ = isset($row['type']) ? (string) $row['type'] : '';
            $cat = isset($row['category']) ? (string) $row['category'] : '';
            $pn  = isset($row['postName']) ? (string) $row['postName'] : (isset($row['post_name']) ? (string) $row['post_name'] : '');
            $emp_type = (strcasecmp($typ, 'Teaching') === 0) ? 'teaching' : $typ;
            $deptId = isset($row['departmentId']) ? (int) $row['departmentId'] : 0;
            $secId = isset($row['sectionId']) ? (int) $row['sectionId'] : 0;
            $isTeaching = !empty($row['isTeaching']);
            $opt_label = trim(($typ !== '' ? '[' . $typ . '] ' : '') . $pn . ' (ID ' . $pid . ')');
            $selected = ($sel_pos !== '' && (string) $sel_pos === (string) $pid) ? ' selected="selected"' : '';
            echo '<option value="' . $pid . '"'
                . ' data-post-name="' . htmlspecialchars($pn, ENT_QUOTES, 'UTF-8') . '"'
                . ' data-type="' . htmlspecialchars($typ, ENT_QUOTES, 'UTF-8') . '"'
                . ' data-employee-type="' . htmlspecialchars($emp_type, ENT_QUOTES, 'UTF-8') . '"'
                . ' data-category="' . htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') . '"'
                . ' data-department-id="' . $deptId . '"'
                . ' data-section-id="' . $secId . '"'
                . ' data-is-teaching="' . ($isTeaching ? '1' : '0') . '"'
                . $selected . '>'
                . htmlspecialchars($opt_label, ENT_QUOTES, 'UTF-8')
                . '</option>';
        }
        echo '</optgroup>';
    }
    if ($sel_pos !== '' && (int) $sel_pos > 0) {
        $found = false;
        foreach ($ugc_pos as $row) {
            if (is_array($row) && isset($row['id']) && (string) (int) $row['id'] === (string) (int) $sel_pos) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            echo '<option value="' . (int) $sel_pos . '" selected="selected">'
                . htmlspecialchars('Current ID ' . (int) $sel_pos . ' (not in cache)', ENT_QUOTES, 'UTF-8')
                . '</option>';
        }
    }
    ?>
</select>
<span class="help-block small">Select first — fills type, post name, department/section (when in position data), and HEMIS <code>position</code> category.</span>
<?php } ?>

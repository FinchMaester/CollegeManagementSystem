<?php
$row = isset($row) && is_array($row) ? $row : array();
$index = isset($index) ? (int) $index : 0;
$is_first = ($index === 0);
$level_options = staff_hemis_level_options();
$board_options = staff_hemis_board_options();

$val = function ($key) use ($row, $index) {
    $posted = set_value($key . '[]');
    if (is_array($posted) && array_key_exists($index, $posted)) {
        return $posted[$index];
    }
    $single = set_value($key);
    if ($index === 0 && $single !== '' && !is_array($single)) {
        return $single;
    }
    return isset($row[$key]) ? $row[$key] : '';
};
?>
<div class="hemis-qualification-row qualification-row" data-index="<?php echo $index; ?>"<?php echo $is_first ? '' : ' style="margin-top:20px;padding-top:20px;border-top:1px solid #eee;"'; ?>>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="graduated_from_<?php echo $index; ?>">Graduated From (HEMIS)</label><?php if ($is_first) { ?><small class="req"> *</small><?php } ?>
                <input id="graduated_from_<?php echo $index; ?>" name="graduated_from[]" type="text" class="form-control" placeholder="e.g. TU, PU, school name" value="<?php echo html_escape($val('graduated_from')); ?>" />
                <?php if ($is_first) { ?><span class="text-danger"><?php echo form_error('graduated_from'); ?></span><?php } ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="program_name_<?php echo $index; ?>">Program Name (HEMIS)</label>
                <input id="program_name_<?php echo $index; ?>" name="program_name[]" type="text" class="form-control" placeholder="e.g. TSLC, B.Ed." value="<?php echo html_escape($val('program_name')); ?>" />
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="level_name_<?php echo $index; ?>">Level Name (HEMIS)</label>
                <select id="level_name_<?php echo $index; ?>" name="level_name[]" class="form-control">
                    <option value="">Select Level</option>
                    <?php
                    $sel_level = $val('level_name');
                    foreach ($level_options as $opt) {
                        $selected = ($sel_level === $opt) ? ' selected' : '';
                        echo '<option value="' . html_escape($opt) . '"' . $selected . '>' . html_escape($opt) . '</option>';
                    }
                    if ($sel_level !== '' && !in_array($sel_level, $level_options, true)) {
                        echo '<option value="' . html_escape($sel_level) . '" selected>' . html_escape($sel_level) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="previous_board_university_college_<?php echo $index; ?>">Board / University / College</label>
                <select id="previous_board_university_college_<?php echo $index; ?>" name="previous_board_university_college[]" class="form-control">
                    <option value="">Select Board/University/College</option>
                    <?php
                    $sel_board = $val('previous_board_university_college');
                    foreach ($board_options as $board_val => $board_label) {
                        $selected = ($sel_board === $board_val) ? ' selected' : '';
                        echo '<option value="' . html_escape($board_val) . '"' . $selected . '>' . html_escape($board_label) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="previous_registration_no_<?php echo $index; ?>">Registration No</label>
                <input id="previous_registration_no_<?php echo $index; ?>" name="previous_registration_no[]" type="text" class="form-control" placeholder="Registration Number" value="<?php echo html_escape($val('previous_registration_no')); ?>" />
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="faculty_name_<?php echo $index; ?>">Graduated Faculty (HEMIS)</label>
                <input id="faculty_name_<?php echo $index; ?>" name="faculty_name[]" type="text" class="form-control" placeholder="Faculty of graduation" value="<?php echo html_escape($val('faculty_name')); ?>" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="enrolled_year_<?php echo $index; ?>">Enrolled Year (HEMIS)</label>
                <input id="enrolled_year_<?php echo $index; ?>" name="enrolled_year[]" type="text" class="form-control" placeholder="e.g. 2075" value="<?php echo html_escape($val('enrolled_year')); ?>" />
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="passed_year_<?php echo $index; ?>">Passed Year (HEMIS)</label>
                <input id="passed_year_<?php echo $index; ?>" name="passed_year[]" type="text" class="form-control" placeholder="e.g. 2079" value="<?php echo html_escape($val('passed_year')); ?>" />
            </div>
        </div>
        <div class="col-md-1" style="padding-top:25px;">
            <?php if ($is_first) { ?>
                <button type="button" class="btn btn-sm btn-add-hemis-qualification" onclick="addHemisQualificationRow()" style="background-color:#3c8dbc;color:white;">
                    <i class="fa fa-plus"></i>
                </button>
            <?php } else { ?>
                <button type="button" class="btn btn-sm btn-remove-hemis-qualification" onclick="removeHemisQualificationRow(<?php echo $index; ?>)" style="background-color:#dd4b39;color:white;">
                    <i class="fa fa-minus"></i>
                </button>
            <?php } ?>
        </div>
    </div>
</div>

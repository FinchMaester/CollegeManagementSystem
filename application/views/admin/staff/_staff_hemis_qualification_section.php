<?php
$this->load->helper('staff_hemis_qualification');
$staff_row = isset($staff) && is_array($staff) ? $staff : array();
$hemis_qualification_rows = staff_hemis_qualification_rows_from_staff($staff_row);
$level_options_json = json_encode(staff_hemis_level_options());
$board_options_json = json_encode(staff_hemis_board_options());
$teaching_faculty_value = set_value('teaching_faculty_name', isset($staff_row['teaching_faculty_name']) ? $staff_row['teaching_faculty_name'] : '');
?>
<div class="hemis-qualification-section">
    <div class="row" style="margin-bottom:10px;">
        <div class="col-md-12">
            <h4 class="pagetitleh2" style="margin:0 0 6px;font-size:15px;">HEMIS qualification (sync)</h4>
            <p class="text-muted small" style="margin-bottom:0;">Repeatable rows for HEMIS sync and local qualification history. Row 1 is sent to HEMIS on sync.</p>
        </div>
    </div>
    <div id="hemis-qualifications-container">
        <?php foreach ($hemis_qualification_rows as $idx => $qrow) {
            $this->load->view('admin/staff/_staff_hemis_qualification_row', array(
                'row'   => $qrow,
                'index' => $idx,
            ));
        } ?>
    </div>
    <div class="row" style="margin-top:10px;">
        <div class="col-md-4">
            <div class="form-group">
                <label for="teaching_faculty_name">Teaching Faculty (HEMIS)</label>
                <input id="teaching_faculty_name" name="teaching_faculty_name" type="text" class="form-control" placeholder="For teaching staff" value="<?php echo html_escape($teaching_faculty_value); ?>" />
                <span class="help-block small">Non-teaching: leave blank.</span>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
window.hemisQualificationRowIndex = <?php echo max(0, count($hemis_qualification_rows) - 1); ?>;
window.hemisLevelOptions = <?php echo $level_options_json; ?>;
window.hemisBoardOptions = <?php echo $board_options_json; ?>;

window.buildHemisLevelSelect = function (index, selected) {
    var html = '<select id="level_name_' + index + '" name="level_name[]" class="form-control">';
    html += '<option value="">Select Level</option>';
    (window.hemisLevelOptions || []).forEach(function (opt) {
        html += '<option value="' + opt + '"' + (selected === opt ? ' selected' : '') + '>' + opt + '</option>';
    });
    if (selected && (window.hemisLevelOptions || []).indexOf(selected) === -1) {
        html += '<option value="' + selected + '" selected>' + selected + '</option>';
    }
    html += '</select>';
    return html;
};

window.buildHemisBoardSelect = function (index, selected) {
    var html = '<select id="previous_board_university_college_' + index + '" name="previous_board_university_college[]" class="form-control">';
    html += '<option value="">Select Board/University/College</option>';
    var boards = window.hemisBoardOptions || {};
    Object.keys(boards).forEach(function (val) {
        html += '<option value="' + val + '"' + (selected === val ? ' selected' : '') + '>' + boards[val] + '</option>';
    });
    html += '</select>';
    return html;
};

window.addHemisQualificationRow = function () {
    window.hemisQualificationRowIndex++;
    var i = window.hemisQualificationRowIndex;
    var html = ''
        + '<div class="hemis-qualification-row qualification-row" data-index="' + i + '" style="margin-top:20px;padding-top:20px;border-top:1px solid #eee;">'
        + '<div class="row">'
        + '<div class="col-md-4"><div class="form-group"><label for="graduated_from_' + i + '">Graduated From (HEMIS)</label>'
        + '<input id="graduated_from_' + i + '" name="graduated_from[]" type="text" class="form-control" placeholder="e.g. TU, PU, school name" /></div></div>'
        + '<div class="col-md-4"><div class="form-group"><label for="program_name_' + i + '">Program Name (HEMIS)</label>'
        + '<input id="program_name_' + i + '" name="program_name[]" type="text" class="form-control" placeholder="e.g. TSLC, B.Ed." /></div></div>'
        + '<div class="col-md-4"><div class="form-group"><label for="level_name_' + i + '">Level Name (HEMIS)</label>'
        + window.buildHemisLevelSelect(i, '') + '</div></div></div>'
        + '<div class="row">'
        + '<div class="col-md-4"><div class="form-group"><label for="previous_board_university_college_' + i + '">Board / University / College</label>'
        + window.buildHemisBoardSelect(i, '') + '</div></div>'
        + '<div class="col-md-4"><div class="form-group"><label for="previous_registration_no_' + i + '">Registration No</label>'
        + '<input id="previous_registration_no_' + i + '" name="previous_registration_no[]" type="text" class="form-control" placeholder="Registration Number" /></div></div>'
        + '<div class="col-md-4"><div class="form-group"><label for="faculty_name_' + i + '">Graduated Faculty (HEMIS)</label>'
        + '<input id="faculty_name_' + i + '" name="faculty_name[]" type="text" class="form-control" placeholder="Faculty of graduation" /></div></div>'
        + '</div>'
        + '<div class="row">'
        + '<div class="col-md-4"><div class="form-group"><label for="enrolled_year_' + i + '">Enrolled Year (HEMIS)</label>'
        + '<input id="enrolled_year_' + i + '" name="enrolled_year[]" type="text" class="form-control" placeholder="e.g. 2075" /></div></div>'
        + '<div class="col-md-4"><div class="form-group"><label for="passed_year_' + i + '">Passed Year (HEMIS)</label>'
        + '<input id="passed_year_' + i + '" name="passed_year[]" type="text" class="form-control" placeholder="e.g. 2079" /></div></div>'
        + '<div class="col-md-1" style="padding-top:25px;">'
        + '<button type="button" class="btn btn-sm btn-remove-hemis-qualification" onclick="removeHemisQualificationRow(' + i + ')" style="background-color:#dd4b39;color:white;"><i class="fa fa-minus"></i></button>'
        + '</div></div></div>';
    $('#hemis-qualifications-container').append(html);
};

window.removeHemisQualificationRow = function (index) {
    if ($('#hemis-qualifications-container .hemis-qualification-row').length <= 1) {
        alert('At least one qualification row is required.');
        return false;
    }
    $('#hemis-qualifications-container .hemis-qualification-row[data-index="' + index + '"]').remove();
};
</script>

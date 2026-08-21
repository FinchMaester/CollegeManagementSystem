                            <div class="col-sm-4 col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('faculty'); ?></label>
                                    <select id="section_id" name="section_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('program'); ?></label>
                                    <select id="program_id" name="program_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('class'); ?></label>
                                    <select id="class_id" name="class_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                </div>
                            </div>
<script>
$(document).ready(function () {
    if (window._libraryAcademicFiltersBound) {
        return;
    }
    window._libraryAcademicFiltersBound = true;
    $.ajax({
        url: '<?php echo base_url(); ?>sections/getAll',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            var html = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.each(response, function (index, section) {
                html += '<option value="' + section.id + '">' + section.section + '</option>';
            });
            $('#section_id').html(html);
        },
        error: function () {
            $('#section_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
        }
    });
    $(document).on('change', '#section_id', function () {
        $('#program_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
        $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
        var sid = $(this).val();
        if (sid) {
            libraryGetProgramsBySection(sid);
        }
    });
    $(document).on('change', '#program_id', function () {
        $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
        var pid = $(this).val();
        if (pid) {
            libraryGetClassesByProgram(pid);
        }
    });
});
function libraryGetProgramsBySection(section_id) {
    $.ajax({
        type: 'POST',
        url: '<?php echo base_url(); ?>admin/timetable/getProgramsBySection',
        data: {section_id: section_id},
        dataType: 'json',
        success: function (data) {
            var html = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.each(data, function (i, obj) {
                html += '<option value="' + obj.id + '">' + obj.title + (obj.code && obj.code !== obj.title ? ' (' + obj.code + ')' : '') + '</option>';
            });
            $('#program_id').html(html);
        }
    });
}
function libraryGetClassesByProgram(program_id) {
    $.ajax({
        type: 'POST',
        url: '<?php echo base_url(); ?>admin/timetable/getClassesByProgram',
        data: {program_id: program_id, section_id: $('#section_id').val()},
        dataType: 'json',
        success: function (data) {
            var html = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.each(data, function (i, obj) {
                html += '<option value="' + obj.id + '">' + obj.class + (obj.degree ? ' - ' + obj.degree : '') + '</option>';
            });
            $('#class_id').html(html);
        }
    });
}
</script>

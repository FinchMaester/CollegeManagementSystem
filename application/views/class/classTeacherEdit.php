<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat(); ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">


    <section class="content-header">
        <h1>
            <i class="fa fa-mortar-board"></i> <?php echo $this->lang->line('academics') ?></h1>
    </section>


    <!-- Main content -->
    <section class="content">
        <div class="row">
            <?php
            if ($this->rbac->hasPrivilege('assign_class_teacher', 'can_add') || $this->rbac->hasPrivilege('assign_class_teacher', 'can_edit')) {
                ?>
                <div class="col-md-4">
                    <!-- Horizontal Form -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo $this->lang->line('edit_assign_class_teacher') ?></h3>


                        </div>
                        <form id="form1" action="<?php echo base_url('admin/teacher/update_class_teacher/' . $class_id . "/" . $section_id . "/" . $program_id); ?>"  method="post" accept-charset="utf-8">
                            <input type="hidden" name="prev_class_id" value="<?php echo $class_id; ?>">
                            <input type="hidden" name="prev_section_id" value="<?php echo $section_id; ?>">
                            <input type="hidden" name="prev_program_id" value="<?php echo $program_id; ?>">
                            <div class="box-body">


                                <?php
                                    if ($this->session->flashdata('msg')) {                          
                                        echo $this->session->flashdata('msg');
                                        $this->session->unset_userdata('msg');
                                    }
                                ?>


                                <?php
                                if (isset($error_message)) {
                                    echo "<div class='alert alert-danger'>" . $error_message . "</div>";
                                }
                                ?>
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('faculty'); ?></label><small class="req"> *</small>
                                    <select class="form-control" id="section_id" name="section" >
                                        <option value=''><?php echo $this->lang->line('select') ?></option>
                                        <?php
                                        foreach ($sectionlist as $section_key => $section_value) {
                                            ?>
                                            <option value="<?php echo $section_value["id"] ?>" <?php echo ($section_value["id"] == $section_id) ? 'selected' : ''; ?>><?php echo $section_value["section"] ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('section'); ?></span>
                                </div>


                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                    <select id="program_id" name="program_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                </div>


                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('academic_level'); ?></label><small class="req"> *</small>
                                    <select class="form-control" name="class" id="class_id">
                                        <option value=''><?php echo $this->lang->line('select') ?></option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('class'); ?></span>
                                </div>


                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('class_teacher'); ?></label><small class="req"> *</small>


                                    <?php
                                    foreach ($teacherlist as $tvalue) {
                                        $is_checked = in_array($tvalue['id'], $selected_teachers) ? 'checked' : '';
                                        ?>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="teachers[]" value="<?php echo $tvalue['id'] ?>" <?php echo $is_checked; ?> ><?php echo $tvalue['name'] . " " . $tvalue['surname'] . " (" . $tvalue['employee_id'] . ")"; ?>
                                            </label>
                                        </div>
                                        <?php
                                    }
                                    ?>


                                    <span class="text-danger"><?php echo form_error('teachers[]'); ?></span>
                                </div>


                            </div><!-- /.box-body -->


                            <div class="box-footer">
                                <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </form>
                    </div>


                </div><!--/.col (right) -->
                <!-- left column -->
            <?php } ?>
            <div class="col-md-<?php
            if ($this->rbac->hasPrivilege('assign_class_teacher', 'can_add') || $this->rbac->hasPrivilege('assign_class_teacher', 'can_edit')) {
                echo "8";
            } else {
                echo "12";
            }
            ?>">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('class_teacher_list'); ?></h3>
                        <div class="box-tools pull-right">
                        </div><!-- /.box-tools -->
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="table-responsive mailbox-messages overflow-visible">
                            <div class="download_label"><?php echo $this->lang->line('class_teacher_list'); ?></div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>


                                        <th><?php echo $this->lang->line('class'); ?>
                                        </th>
                                        <th><?php echo $this->lang->line('section'); ?>
                                        </th>
                                        <th><?php echo $this->lang->line('program'); ?></th>
                                        <th><?php echo $this->lang->line('class_teacher'); ?>
                                        </th>


                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 0;


                                    foreach ($assignteacherlist as $teacher) {
                                        ?>


                                        <tr>
                                            <td class="mailbox-name">
                                                <?php echo $teacher["class"] . " - " . $teacher["degree"]; ?>


                                            </td>




                                            <td>


                                                <?php echo $teacher["section"]; ?>


                                            </td>
                                            <td>
                                                <?php echo isset($teacher["program"]) ? $teacher["program"] : 'N/A'; ?>
                                            </td>
                                            <td>
                                                <?php echo isset($teacher["teacher_names"]) ? $teacher["teacher_names"] : 'No teachers assigned'; ?>
                                            </td>
                                            <td class="mailbox-date pull-right"">
                                                <?php
                                                if ($this->rbac->hasPrivilege('assign_class_teacher', 'can_edit')) {
                                                    ?>
                                                    <a href="<?php echo base_url(); ?>admin/teacher/update_class_teacher/<?php echo $teacher["class_id"]; ?>/<?php echo $teacher["section_id"]; ?>/<?php echo isset($teacher["program_id"]) ? $teacher["program_id"] : '0'; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                    <?php
                                                }
                                                if ($this->rbac->hasPrivilege('assign_class_teacher', 'can_delete')) {
                                                    ?>
                                                    <a href="<?php echo base_url(); ?>admin/teacher/classteacherdelete/<?php echo $teacher["class_id"]; ?>/<?php echo $teacher["section_id"]; ?>/<?php echo isset($teacher["program_id"]) ? $teacher["program_id"] : '0'; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
                                                        <i class="fa fa-remove"></i>
                                                    </a>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <?php
                                        $i++;
                                    }
                                    ?>


                                </tbody>
                            </table><!-- /.table -->






                        </div><!-- /.mail-box-messages -->
                    </div><!-- /.box-body -->
                </div>
            </div><!--/.col (left) -->
            <!-- right column -->




        </div>
        <div class="row">
            <!-- left column -->


            <!-- right column -->
            <div class="col-md-12">


            </div><!--/.col (right) -->
        </div>   <!-- /.row -->
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->


<script type="text/javascript">
    $(document).ready(function () {
        var section_id_initial = '<?php echo $section_id; ?>';
        var program_id_initial = '<?php echo $program_id; ?>';
        var class_id_initial = '<?php echo $class_id; ?>';


        // Initial population of program and academic level if section (faculty) is pre-selected
        if (section_id_initial) {
            getProgramsBySection(section_id_initial, program_id_initial);
        }


        // Section (Faculty) change event
        $(document).on('change', '#section_id', function (e) {
            var section_id = $(this).val();
            $('#program_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');


            if (section_id) {
                getProgramsBySection(section_id);
            }
        });


        // Program change event
        $(document).on('change', '#program_id', function (e) {
            var program_id = $(this).val();
            var section_id = $('#section_id').val(); // Section (Faculty) is also needed for getClassesByProgram


            $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');


            if (program_id) {
                getClassesByProgram(program_id, section_id);
            }
        });


        function getProgramsBySection(section_id, selected_program_id = null) {
            var base_url = '<?php echo base_url() ?>';
            $.ajax({
                type: "POST",
                url: base_url + "admin/timetable/getProgramsBySection", // Reusing timetable controller
                data: {'section_id': section_id, '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'},
                dataType: "json",
                success: function (data) {
                    var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                    $.each(data, function (i, obj) {
                        var sel = "";
                        if (selected_program_id == obj.id) {
                            sel = "selected";
                        }
                        div_data += "<option value=" + obj.id + " " + sel + ">" + obj.title + (obj.code && obj.code !== obj.title ? " (" + obj.code + ")" : "") + "</option>";
                    });
                    $('#program_id').html(div_data);
                    if (selected_program_id) {
                        getClassesByProgram(selected_program_id, section_id, class_id_initial);
                    }
                },
                error: function () {
                    $('#program_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                }
            });
        }


        function getClassesByProgram(program_id, section_id, selected_class_id = null) {
            var base_url = '<?php echo base_url() ?>';
            $.ajax({
                type: "POST",
                url: base_url + "admin/timetable/getClassesByProgram", // Reusing timetable controller
                data: {'program_id': program_id, 'section_id': section_id, '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'},
                dataType: "json",
                success: function (data) {
                    var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                    $.each(data, function (i, obj) {
                        var sel = "";
                        if (selected_class_id == obj.id) {
                            sel = "selected";
                        }
                        div_data += "<option value=" + obj.id + " " + sel + ">" + obj.class + " - " + obj.degree + "</option>";
                    });
                    $('#class_id').html(div_data);
                },
                error: function () {
                    $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                }
            });
        }
    });
</script>


<?php


function check_in_array($find, $array) {


    foreach ($array as $element) {
        if ($find == $element["id"]) {
            return TRUE;
        }
    }
    return FALSE;
}
?>


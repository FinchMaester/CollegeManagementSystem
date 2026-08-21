<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-flask"></i> <?php //echo $this->lang->line('homework'); ?>
    </section>
    <!-- Main content -->
    <section class="content">
        <?php $this->load->view('homework/_homeworkordailyassignmentreport'); ?>
        <div class="box removeboxmius">
            <div class="box-header ptbnull"></div>
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
            </div>
            <form  class="assign_teacher_form" action="<?php echo base_url(); ?>homework/evaluation_report" method="post" enctype="multipart/form-data">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            <?php if ($this->session->flashdata('msg')) {?>
                                <?php
                                    echo $this->session->flashdata('msg');
                                    $this->session->unset_userdata('msg');
                                ?>
                            <?php }?>
                            <?php echo $this->customlib->getCSRF(); ?>
                        </div>
                        <div class="col-md-3 col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('faculty'); ?><small class="req"> *</small></label>
                                <select id="section_id" name="section_id" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="class_id_error text-danger"><?php echo form_error('section_id'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('program'); ?><small class="req"> *</small></label>
                                <select id="program_id" name="program_id" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="class_id_error text-danger"><?php echo form_error('program_id'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('academic_level'); ?><small class="req"> *</small></label>
                                <select id="class_id" name="class_id" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="class_id_error text-danger"><?php echo form_error('class_id'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('subject_group'); ?><small class="req"> *</small></label>
                                <select id="subject_group_id" name="subject_group_id" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="section_id_error text-danger"><?php echo form_error('subject_group_id'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('subject'); ?><small class="req"> *</small></label>
                                <select id="subject_id" name="subject_id" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="section_id_error text-danger"><?php echo form_error('subject_id'); ?></span>
                            </div>
                        </div>
                    </div>
                    <button type="submit" id="search_filter" name="search" value="search_filter" class="btn btn-primary btn-sm checkbox-toggle pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                </div>
            </form>


            <div class="col-md-12" id="errorinfo">
            </div>
            <div class="" id="box_display">
                <div class="box-header ptbnull"></div>
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-users"> </i> <?php echo $this->lang->line('homework_evaluation_report'); ?></h3>
                </div>
                <div class="box-body table-responsive">
                    <div class="download_label"> <?php echo $this->lang->line('homework_evaluation_report') . "<br>";
$this->customlib->get_postmessage();
?></div>
                    <table class="table table-striped table-bordered table-hover example" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th><?php echo $this->lang->line('subject') ?></th>
                                <th><?php echo $this->lang->line('homework_date'); ?> </th>
                                <th><?php echo $this->lang->line('submission_date'); ?></th>
                                <th><?php echo $this->lang->line('complete_incomplete'); ?></th>
                                <th><?php echo $this->lang->line('complete'); ?>%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
if (!empty($resultlist)) {


    foreach ($resultlist as $key => $homework) {
        ?>
                                    <tr>
                                        <td><?php echo $homework["subject_name"]; ?> <?php if($homework["subject_code"]){ echo '('.$homework["subject_code"].')'; } ?></td>
                                        <td><?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($homework['homework_date'])); ?></td>
                                        <td><?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($homework['submit_date'])); ?></td>
                                        <td><?php
if (!empty($report[$homework['id']])) {
            echo $report[$homework['id']]['completed'] . "/" . ($report[$homework['id']]["total"] - $report[$homework['id']]["completed"]);
        }
        ?></td>
                                        <td><?php
if (!empty($report[$homework['id']])) {
            echo $report[$homework['id']]["percentage"];
        }
        ?></td>
                                    </tr>
        <?php
}
}
?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div><!--./box box-primary-->
    </section>
</div>


<div class="modal fade" id="report" tabindex="-1" role="dialog" aria-labelledby="evaluation">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-media-content">
            <div class="modal-header modal-media-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="box-title"><?php echo $this->lang->line('homework_evaluation'); ?></h4>
            </div>
            <div class="modal-body pt0 pb0" id="evaluation_details">
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function () {


        var date_format = '<?php echo $result = strtr($this->customlib->getSchoolDateFormat(), ['d' => 'dd', 'm' => 'mm', 'Y' => 'yyyy']) ?>';
        $('#homework_date,#submit_date,#homeworkdate,#submitdate').datepicker({
            format: date_format,
            autoclose: true,
            language: '<?php echo $language_name ?>'
        });
    });
</script>
<script type="text/javascript">
    var date_format = '<?php echo $result = strtr($this->customlib->getSchoolDateFormat(), ['d' => 'dd', 'm' => 'MM', 'Y' => 'yyyy']) ?>';
    $(document).ready(function (e) {
        var section_id = '<?php echo set_value('section_id') ?>';
        var program_id = '<?php echo set_value('program_id') ?>';
        var class_id = '<?php echo set_value('class_id') ?>';
        var subject_group_id = '<?php echo set_value('subject_group_id') ?>';
        var subject_id = '<?php echo set_value('subject_id') ?>';


        // Fetch all sections (faculty)
        $.ajax({
            url: '<?php echo base_url(); ?>sections/getAll',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                var html = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                $.each(response, function (index, section) {
                    var selected = (section_id == section.id) ? 'selected' : '';
                    html += '<option value="' + section.id + '" ' + selected + '>' + section.section + '</option>';
                });
                $('#section_id').html(html);
                if(section_id) {
                    getProgramsBySection(section_id, program_id);
                }
            },
            error: function(xhr, status, error) {
                $('#section_id').html('<option value="">Error loading sections</option>');
            }
        });


        if (class_id && section_id && program_id) {
            getClassesByProgram(program_id, class_id);
        }
        if (class_id && section_id) {
            getSubjectGroupsByClassAndSection(class_id, section_id, subject_group_id);
        }
        if (subject_group_id) {
            getSubjectsBySubjectGroup(subject_group_id, subject_id);
        }


        $(document).on('change', '#section_id', function (e) {
            $('#program_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            $('#subject_group_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            $('#subject_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            var section_id = $(this).val();
            if (section_id) {
                getProgramsBySection(section_id);
            }
        });


        $(document).on('change', '#program_id', function (e) {
            $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            $('#subject_group_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            $('#subject_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            var program_id = $(this).val();
            if (program_id) {
                getClassesByProgram(program_id);
            }
        });


        $(document).on('change', '#class_id', function (e) {
            $('#subject_group_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            $('#subject_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            var class_id = $(this).val();
            var section_id = $('#section_id').val();
            if (class_id && section_id) {
                getSubjectGroupsByClassAndSection(class_id, section_id);
            }
        });


        $(document).on('change', '#subject_group_id', function (e) {
            $('#subject_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            var subject_group_id = $(this).val();
            if (subject_group_id) {
                getSubjectsBySubjectGroup(subject_group_id);
            }
        });


        function getProgramsBySection(section_id, selected_program_id = null) {
            if (section_id != "") {
                var base_url = '<?php echo base_url() ?>';
                var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                $.ajax({
                    type: "POST",
                    url: base_url + "admin/timetable/getProgramsBySection",
                    data: {'section_id': section_id},
                    dataType: "json",
                    success: function (data) {
                        $.each(data, function (i, obj) {
                            var sel = "";
                            if (selected_program_id == obj.id) {
                                sel = "selected";
                            }
                            div_data += "<option value=" + obj.id + " " + sel + ">" + obj.title + (obj.code && obj.code !== obj.title ? " (" + obj.code + ")" : "") + "</option>";
                        });
                        $('#program_id').html(div_data);
                        if (selected_program_id) {
                            getClassesByProgram(selected_program_id, class_id);
                        }
                    },
                    error: function() {
                        $('#program_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                    }
                });
            }
        }


        function getClassesByProgram(program_id, selected_class_id = null) {
            if (program_id != "") {
                var base_url = '<?php echo base_url() ?>';
                var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                $.ajax({
                    type: "POST",
                    url: base_url + "admin/timetable/getClassesByProgram",
                    data: {'program_id': program_id},
                    dataType: "json",
                    success: function (data) {
                        $.each(data, function (i, obj) {
                            var sel = "";
                            if (selected_class_id == obj.id) {
                                sel = "selected";
                            }
                            div_data += "<option value=" + obj.id + " " + sel + ">" + obj.class + " - " + obj.degree + "</option>";
                        });
                        $('#class_id').html(div_data);
                        if (selected_class_id) {
                            var section_id = $('#section_id').val();
                            getSubjectGroupsByClassAndSection(selected_class_id, section_id, subject_group_id);
                        }
                    },
                    error: function() {
                        $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                    }
                });
            }
        }


        function getSubjectGroupsByClassAndSection(class_id, section_id, selected_subject_group_id = null) {
            if (class_id != "" && section_id != "") {
                var base_url = '<?php echo base_url() ?>';
                var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                $.ajax({
                    type: 'POST',
                    url: base_url + 'admin/subjectgroup/getGroupByClassandSection',
                    data: {'class_id': class_id, 'section_id': section_id},
                    dataType: 'JSON',
                    success: function (data) {
                        $.each(data, function (i, obj) {
                            var sel = "";
                            if (selected_subject_group_id == obj.subject_group_id) {
                                sel = "selected";
                            }
                            div_data += "<option value=" + obj.subject_group_id + " " + sel + ">" + obj.name + "</option>";
                        });
                        $('#subject_group_id').html(div_data);
                    },
                    error: function () {
                        $('#subject_group_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                    }
                });
            }
        }


        function getSubjectsBySubjectGroup(subject_group_id, selected_subject_id = null) {
            if (subject_group_id != "") {
                var base_url = '<?php echo base_url() ?>';
                var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                $.ajax({
                    type: 'POST',
                    url: base_url + 'admin/subjectgroup/getGroupsubjects',
                    data: {'subject_group_id': subject_group_id},
                    dataType: 'JSON',
                    success: function (data) {
                        $.each(data, function (i, obj) {
                            var sel = "";
                            if (selected_subject_id == obj.id) {
                                sel = "selected";
                            }
                            var code = '';
                            if(obj.code){
                                code = " (" + obj.code + ") ";
                            }
                            div_data += "<option value=" + obj.id + " " + sel + ">" + obj.name + code + "</option>";
                        });
                        $('#subject_id').html(div_data);
                    },
                    error: function () {
                        $('#subject_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                    }
                });
            }
        }
    });
</script>


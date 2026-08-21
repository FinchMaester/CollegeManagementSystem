<div class="content-wrapper">    
    <!-- Main content -->
    <section class="content">
        <?php $this->load->view('homework/_homeworkordailyassignmentreport'); ?>        
            <div class="box removeboxmius">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                   
                    <form action="<?php echo site_url('homework/homeworkreport') ?>" method="post">
                    <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            <?php if ($this->session->flashdata('msg')) { ?>
                                <?php
                                    echo $this->session->flashdata('msg');
                                    $this->session->unset_userdata('msg');
                                ?>
                            <?php } ?>
                            <?php echo $this->customlib->getCSRF(); ?>
                        </div>
                        <div class="col-md-3 col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('faculty'); ?></label>
                                <select id="section_id" name="section_id" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="text-danger" id="error_section_id"></span>
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('program'); ?></label>
                                <select id="program_id" name="program_id" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="text-danger" id="error_program_id"></span>
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('academic_level'); ?></label>
                                <select id="class_id" name="class_id" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="text-danger" id="error_class_id"></span>
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('subject_group'); ?></label>
                                <select id="subject_group_id" name="subject_group_id" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="text-danger" id="error_subject_group_id"></span>
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('subject'); ?></label>
                                <select id="subject_id" name="subject_id" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="text-danger" id="error_subject_id"></span>
                            </div>
                        </div>
                    </div>
                    <button type="submit" id="search_filter" name="search" value="search_filter" class="btn btn-primary btn-sm checkbox-toggle pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                </div>
                    </form>
                    <div class="">
                        <div class="box-header ptbnull"></div>
                        <div class="box-header ptbnull">
                            <h3 class="box-title titlefix"><i class="fa fa-money"></i>
                                <?php echo $this->lang->line('homework_report') ?></h3>
                        </div>
                        <div class="box-body table-responsive overflow-visible">
                            <div class="download_label"><?php echo $this->lang->line('homework_report') ;
                                        ?></div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                            <tr>
                                                <th><?php echo $this->lang->line('class') ?></th>
                                                <th><?php echo $this->lang->line('section') ?></th>
                                                <th><?php echo $this->lang->line('subject_group'); ?></th>
                                                <th><?php echo $this->lang->line('subject') ?></th>
                                                <th><?php echo $this->lang->line('homework_date'); ?></th>
                                                <th><?php echo $this->lang->line('submission_date'); ?></th>
                                                <th><?php echo $this->lang->line('student_count'); ?></th>
                                                <th><?php echo $this->lang->line('homework_submitted'); ?></th>
                                                <th class="text-start-lg"><?php echo $this->lang->line('pending_student'); ?></th>
                                                 
                                            </tr>
                                        </thead>
                                <tbody>
                                     <?php
                                    if (empty($resultlist)) {
                                        ?>


                                        <?php
                                    } else {
                                        $count = 1;
                                        foreach ($resultlist as $student) {                                          
                                            ?>
                                            <tr>
                                                <td><?php echo $student['class']; ?></td>
                                                <td><?php echo $student['section']; ?></td>
                                                <td><?php echo $student['name']; ?></td>                          
                                                <td><?php echo $student['subject_name']; ?><?php if($student['subject_code']){ echo ' ('.$student['subject_code'].')'; }?></td>
                                                <td><?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($student['homework_date'])); ?></td>  
                                                <td><?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($student['submit_date']));
                                                ?></td>                                                
                                                <td>
                                                    <a class="studentlist cursor-pointer" id="load" data-toggle="tooltip"  data-clss-id="<?php echo $student['class_id']; ?>" data-section-id="<?php echo $student['section_id']; ?>" data-homework-id="<?php echo $student['id']; ?>" data-type="student_count" ><?php echo $student['student_count']; ?></a>    
                                                </td>                                                
                                                <td>
                                                    <a  class="studentlist cursor-pointer" id="load" data-toggle="tooltip"  data-clss-id="<?php echo $student['class_id']; ?>" data-section-id="<?php echo $student['section_id']; ?>" data-homework-id="<?php echo $student['id']; ?>" data-type="homework_submitted" ><?php echo $student['assignments']; ?></a>
                                                </td>                                                
                                                <td>
                                                    <a class="studentlist cursor-pointer text-left displayblock" id="load" data-toggle="tooltip" data-clss-id="<?php echo $student['class_id']; ?>" data-section-id="<?php echo $student['section_id']; ?>" data-homework-id="<?php echo $student['id']; ?>" data-type="pending_student" ><?php echo $student['student_count']-$student['assignments']; ?></a>                                        
                                                </td>                                                  
                                            </tr>
                                            <?php
                                            $count++;
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div><!--./box box-primary-->
            </div><!--./col-md-12-->
        </section>
</div>  


<div id="studentModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-xl">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line('student_list'); ?></h4>
            </div>
            <div class="modal-body scroll-area">
            </div>
        </div>
    </div>
</div>
 
<script type="text/javascript">


$(document).ready(function(){
  $('#studentModal').modal({backdrop:'static', keyboard:false, show: false});
});


      $(document).on('click', '.studentlist', function () {
        var $this = $(this);
        var date=$this.data('date');    
     
        $.ajax({
            type: 'POST',
            url: baseurl + "homework/getStudentByClassSection",
            data: {'class_id':$this.data('clssId'), 'section_id':$this.data('sectionId'), 'homework_id':$this.data('homeworkId'), 'type':$this.data('type')},
            dataType: 'JSON',
            beforeSend: function () {
                $this.button('loading');
            },
            success: function (data) {
                $('#studentModal .modal-body').html(data.page);
                $('#studentModal').modal('show');
                $this.button('reset');
            },
            error: function (xhr) { // if error occured
                alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                $this.button('reset');
            },
            complete: function () {
                $this.button('reset');
            }
        });
    });
</script>


<script type="text/javascript">
    $(document).ready(function () {
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


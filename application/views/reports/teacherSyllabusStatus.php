<?php
$language = $this->customlib->getLanguage();
$language_name = $language["short_code"];
?>
<div class="content-wrapper">
    <section class="content">
        <?php $this->load->view('reports/_lesson_plan'); ?>
        <div class="box removeboxmius">
            <div class="box-header ptbnull">
                <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
            </div>
            <form class="assign_teacher_form" action="<?php echo base_url(); ?>report/teachersyllabusstatus/" method="post" enctype="multipart/form-data">
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
                                <label><?php echo $this->lang->line('faculty'); ?></label><small class="req"> *</small>
                                <select id="section_id" name="section_id" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="class_id_error text-danger"><?php echo form_error('section_id'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                <select id="program_id" name="program_id" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="class_id_error text-danger"><?php echo form_error('program_id'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('academic_level'); ?></label><small class="req"> *</small>
                                <select id="class_id" name="class_id" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="class_id_error text-danger"><?php echo form_error('class_id'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('subject_group'); ?></label><small class="req"> *</small>
                                <select id="subject_group_id" name="subject_group_id" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="section_id_error text-danger"><?php echo form_error('subject_group_id'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('subject'); ?></label><small class="req"> *</small>
                                <select id="subject_id" name="subject_id" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="class_id_error text-danger"><?php echo form_error('subject_id'); ?></span>
                            </div>
                        </div>
                    </div>
                    <button type="submit" id="search_filter" name="search" value="search_filter" class="btn btn-primary btn-sm checkbox-toggle pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
            </form>
        </div>


        <?php if (!empty($subjects_data) && $subject_name != '') {
            ?>
             
            <div class="box-body" id="transfee">                
                <div class="box-header ptbnull">
                    <h3 class="box-title titlefix"><i class="fa fa-money"></i>  <?php
                                echo $this->lang->line('subject_lesson_plan_report_for') . ": " . $subject_name;
                                if ($subject_name != '') {
                                    echo " " . $this->lang->line('complete') . " " . $subject_complete . "%";
                                }
                                ?></h3>
                </div>                        
                <div class="download_label">
                            <?php
                                echo $this->lang->line('subject_lesson_plan_report_for') . ": " . $subject_name;
                                if ($subject_name != '') {
                                    echo " " . $this->lang->line('complete') . " " . $subject_complete . "%";
                                }
                                ?>
                </div>
                <table class="table table-bordered syllbus" id="headerTable">                    
                    <tbody>
                        <?php
                        foreach ($subjects_data as $key => $value) {
                            ?>
                            <tr>
                                <td>
                                    <table class="table table-striped table-bordered table-hover example" >
                                        <thead>
                                            <tr>  
                                                <th class="text-left"><?php echo $this->lang->line('teacher'); ?></th>    
                                                <th class="text-left"><?php echo $this->lang->line('lesson_name'); ?></th>    
                                                <th class="text-left"><?php echo $this->lang->line('topic_name'); ?></th>
                                                <th class="text-left"><?php echo $this->lang->line('sub_topic'); ?></th>
                                                <th class="text-left"><?php echo $this->lang->line('date'); ?></th>
                                                <th><?php echo $this->lang->line('time_from'); ?></th>
                                                <th class="text-left"><?php echo $this->lang->line('time_to'); ?></th>                
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($value['teachers_summary'] as $teachers_summarykey => $teachers_summaryvalue) {
                                                foreach ($teachers_summaryvalue['summary_report'] as $summary_report_key => $summary_report_value) {
                                                    ?>
                                                    <tr>  
                                                        <td class="text-left"><?php echo $teachers_summaryvalue['name']; ?></td>  
                                                        <td class="text-left"><?php echo $summary_report_value['lesson_name'] ?></td>
                                                        <td class="text-left"><?php echo $summary_report_value['topic_name'] ?></td>
                                                        <td class="text-left"><?php echo $summary_report_value['sub_topic'] ?></td>                  
                                                        <td class="text-left"><?php echo date($this->customlib->getSchoolDateFormat(), strtotime($summary_report_value['date'])); ?></td>
                                                        <td class="text-left"><?php echo $summary_report_value['time_from']; ?></td>
                                                        <td class="text-left"><?php echo $summary_report_value['time_to']; ?></td>
                                                    </tr>
                                                    <?php }
                                                }
                                                ?>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
            <?php } ?>
                    </tbody>
                </table>
            </div>
    <?php
} else {
    ?>
            <div class="tab-pane active table-responsive box-body" >
                <div class="download_label"> <?php echo $this->lang->line('subject_lesson_plan_report'); ?></div>
                <table class="table table-striped table-bordered table-hover example" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th><?php echo $this->lang->line('teacher'); ?></th>    
                            <th><?php echo $this->lang->line('lesson_name'); ?></th>      
                            <th><?php echo $this->lang->line('topic_name'); ?></th>
                            <th><?php echo $this->lang->line('sub_topic'); ?></th>            
                            <th><?php echo $this->lang->line('date'); ?></th>
                            <th><?php echo $this->lang->line('time_from'); ?></th>
                            <th><?php echo $this->lang->line('time_to'); ?></th>
                            <th class="pull-right noExport"><?php echo $this->lang->line('action'); ?></th>    
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
<?php }
?>
    </section>
</div>


<div class="modal fade syllbus" id="assignsyllabus" tabindex="-1" role="dialog" aria-labelledby="evaluation" style="padding-left: 0 !important">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-media-content">
            <div class="modal-header modal-media-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="box-title" ><?php echo $this->lang->line('lesson_plan'); ?> </h4>
            </div>
            <div class="modal-body pt0 pb0">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="row">                              
                            <div id="syllabus_result" class=""></div>
                        </div><!--./row-->
                    </div><!--./col-md-12-->
                </div><!--./row-->
            </div>
        </div>
    </div>
</div>


<div class="modal fade syllbus" id="lacture_youtube_modal"  role="dialog" aria-labelledby="evaluation" style="background: rgba(0, 0, 0, 0.98);" >
    <div class="modal-dialog modal-lg" role="document" style="width:100%;height:100%">
        <div class="modal-content modal-media-content">
            <div class="modal-header modal-media-header">
                <button type="button" onclick="videoUrlBlank()" class="close" data-dismiss="modal">&times;</button>
                <h4 class="box-title" ><?php echo $this->lang->line('youtube_link') ?></h4>
            </div>
            <div class="modal-body pt0 pb0">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="row"  style="width:100%;height:660px">
                            <div id="video_url" ></div>
                        </div><!--./row-->
                    </div><!--./col-md-12-->
                </div><!--./row-->
            </div>
        </div>
    </div>
</div>


<script>
    $(document).on('submit','.assign_teacher_form', function(){
        document.getElementById('search_filter').disabled = true;
    });
   
    function subject_syllabusdelete(syllabus_id) {
        if (confirm('<?PHP echo $this->lang->line('delete_confirm') ?>')) {
            $.ajax({
                type: "POST",
                url: base_url + "admin/syllabus/delete_subject_syllabus",
                data: {'id': syllabus_id},
                success: function (data) {
                    successMsg('<?php echo $this->lang->line("delete_message"); ?>');
                    window.location.reload(true);
                },
            });
        }
    }
   
    function videoUrlBlank() {
        $('#video_url').html('');
    }
</script>


<script>
    function run_video(lacture_youtube_url) {
        $('#lacture_youtube_modal').modal('show');
        var str = lacture_youtube_url;
        var res = str.split("=");
        $('#video_url').html('<iframe width="100%" height="650px" src="//www.youtube.com/embed/' + res[1] + '?rel=0&version=3&modestbranding=1&autoplay=1&controls=1&showinfo=1&loop=1mode=opaque&amp;rel=0&amp;autohide=1&amp;showinfo=0&amp;wmode=transparent" frameborder="0" allowfullscreen></iframe>');
    }
</script>


<script>
    function get_subject_syllabus(id, staff_id) {
        $('#assignsyllabus').modal('show');
        $('#syllabus_result').html('');
        $.ajax({
            type: "POST",
            url: base_url + "admin/syllabus/get_subject_syllabus",
            data: {'id': id, 'staff_id': staff_id},
            success: function (data) {
                $('#syllabus_result').html(data);
            },
        });
    }


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


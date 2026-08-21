<div class="content-wrapper">
    <!-- Main content -->
    <section class="content">
        <?php $this->load->view('homework/_homeworkordailyassignmentreport'); ?>
        <div class="box removeboxmius">
            <div class="box-header ptbnull"></div>
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
            </div>          
            
            <form  class="search_daily_assignment_form" action="<?php echo base_url(); ?>homework/dailyassignmentreportvalidation" method="post" enctype="multipart/form-data">             
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            <?php if ($this->session->flashdata('msg')) {?>
                                <?php 
                                    echo $this->session->flashdata('msg'); 
                                    $this->session->unset_userdata('msg');
                                ?>
                            <?php } ?>
                            <?php echo $this->customlib->getCSRF(); ?>
                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('search_type'); ?></label><small class="req"> *</small>
                                <select class="form-control" name="search_type" onchange="showdate(this.value)">

                                    <?php foreach ($searchlist as $key => $search) { ?>
                                    <option value="<?php echo $key ?>" <?php
                                            if ((isset($search_type)) && ($search_type == $key)) {

                                                echo "selected";
                                            }
                                            ?>><?php echo $search ?></option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger" id="error_search_type"></span>
                                </div>
                        </div>
                        <div id='date_result'></div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('faculty'); ?><small class="req"> *</small></label>
                                <select id="section_id" name="section_id" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="text-danger" id="error_section_id"></span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('program'); ?><small class="req"> *</small></label>
                                <select id="program_id" name="program_id" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="text-danger" id="error_program_id"></span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('academic_level'); ?><small class="req"> *</small></label>
                                <select id="class_id" name="class_id" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="text-danger" id="error_class_id"></span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('subject_group'); ?><small class="req"> *</small></label>
                                <select id="subject_group_id" name="subject_group_id" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="text-danger" id="error_subject_group_id"></span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('subject'); ?><small class="req"> *</small></label>
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

            <div class="col-md-12" id="errorinfo">
            </div>
            <div class="" id="box_display">
                <div class="box-header ptbnull"></div>
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-users"> </i> <?php echo $this->lang->line('daily_assignment_report'); ?> </h3>
                </div>
                <div class="box-body table-responsive">
                    
                    <table class="table table-striped table-bordered table-hover dailyassignmentlist" data-export-title="<?php echo $this->lang->line('daily_assignment_report'); ?>">
                        <thead>
                            <tr>
                                <th><?php echo $this->lang->line('student_name') ?></th>
                                <th><?php echo $this->lang->line('class'); ?> </th>
                                <th><?php echo $this->lang->line('section'); ?></th>
                                <th><?php echo $this->lang->line('total_assignment'); ?></th>
                                <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div><!--./box box-primary-->
    </section>
</div>

<div class="modal fade" id="dailyassignmentdetails" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content modal-media-content">
            <div class="modal-header modal-media-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="box-title"><?php echo $this->lang->line('daily_assignment_details'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="scroll-area-inside">
                    <div id="assigndata"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function dailyassignmentdetails(student_id)
    {
        var search_type = $('select[name="search_type"] option:selected').val();
        var subject_id = $('select[name="subject_id"] option:selected').val();        
        var date_from = $("#date_from").val();
        var date_to = $("#date_to").val();     

        $.ajax({
            type: 'POST',
            url: base_url + 'homework/dailyassignmentdetails',
            data: {'student_id': student_id,'search_type':search_type,'subject_id':subject_id,'date_from':date_from,'date_to':date_to},
            dataType: 'JSON',
            success: function (response) {
               $('#assigndata').html(response.page);
            }  
        });
    }
</script>

<script type="text/javascript">

    var save_method; //for save method string
    var update_id; //for save method string

    $(document).ready(function () {
        var section_id = '';
        var program_id = '';
        var class_id = '';
        var subject_group_id = '';
        var subject_id = '';

        // Fetch all sections (faculty)
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
            error: function(xhr, status, error) {
                $('#section_id').html('<option value="">Error loading sections</option>');
            }
        });

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

        function getProgramsBySection(section_id) {
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
                            div_data += "<option value=" + obj.id + ">" + obj.title + (obj.code && obj.code !== obj.title ? " (" + obj.code + ")" : "") + "</option>";
                        });
                        $('#program_id').html(div_data);
                    },
                    error: function() {
                        $('#program_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                    }
                });
            }
        }

        function getClassesByProgram(program_id) {
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
                            div_data += "<option value=" + obj.id + ">" + obj.class + " - " + obj.degree + "</option>";
                        });
                        $('#class_id').html(div_data);
                    },
                    error: function() {
                        $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                    }
                });
            }
        }

        function getSubjectGroupsByClassAndSection(class_id, section_id) {
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
                            div_data += "<option value=" + obj.subject_group_id + ">" + obj.name + "</option>";
                        });
                        $('#subject_group_id').html(div_data);
                    },
                    error: function () {
                        $('#subject_group_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                    }
                });
            }
        }

        function getSubjectsBySubjectGroup(subject_group_id) {
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
                            var code = '';
                            if(obj.code){
                                code = " (" + obj.code + ") ";
                            }
                            div_data += "<option value=" + obj.id + ">" + obj.name + code + "</option>";
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

    $(document).on('submit','.search_daily_assignment_form',function(e){
    e.preventDefault(); // avoid to execute the actual submit of the form.
    var $this = $(this).find("button[type=submit]:focus");  
    var form = $(this);
    var url = form.attr('action');
    var form_data = form.serializeArray();

    // Required fields check
    var requiredFields = ['class_id', 'section_id', 'subject_group_id', 'subject_id'];
    var missing = [];
    requiredFields.forEach(function(field) {
        if (!$('[name="'+field+'"]').val()) {
            missing.push(field.replace('_', ' '));
        }
    });
    if (missing.length > 0) {
        alert('Please select: ' + missing.join(', '));
        return false;
    }

    $.ajax({
           url: url,
           type: "POST",
           dataType:'JSON',
           data: form_data, // serializes the form's elements.
              beforeSend: function () {
                $('[id^=error]').html("");
                $this.button('loading'); 
               },
              success: function(response) { // your success handler
                
                if(response.error && response.data && response.data.length === 0) {
                    // DataTables-compatible error response
                    if(response.error) {
                        let errorMsg = '';
                        $.each(response.error, function(key, value) {
                            if(value) errorMsg += value + '\n';
                        });
                        if(errorMsg) alert(errorMsg);
                    }
                    return;
                }
                if(!response.status){
                    $.each(response.error, function(key, value) {
                    $('#error_' + key).html(value);
                    });
                }else{
                 
                   initDatatable('dailyassignmentlist','homework/searchdailyassignmentreport',function(d) {
                        d.class_id = $('#class_id').val();
                        d.section_id = $('#section_id').val();
                        d.subject_group_id = $('#subject_group_id').val();
                        d.subject_id = $('#subject_id').val();
                        d.search_type = $('select[name="search_type"]').val();
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                        // Add CSRF token
                        d['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
                   },[],100,

                    [{ "bSortable": false, "aTargets": [ -1,] ,'sClass': 'dt-body-right'},{ "bSortable": false, "aTargets": [ -2 ] }],                    

                    );
                }
              },
             error: function(xhr) { // your error handler
                 $this.button('reset');
                 console.log(xhr.responseText); // Log the error response for debugging
                 alert('An error occurred while processing your request. Please check the console for details.');
             },
             complete: function() {
             $this.button('reset');
             }
         });
});
</script>
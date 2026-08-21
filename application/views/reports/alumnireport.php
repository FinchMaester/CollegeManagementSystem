<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
<script src="<?php echo base_url(); ?>backend/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
<div class="content-wrapper" style="min-height: 946px;">  
    <section class="content-header">
        <h1>
            <i class="fa fa-user-plus"></i> <?php //echo $this->lang->line('student_information'); ?> <small><?php //echo $this->lang->line('student1'); ?></small></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php if ($this->session->flashdata('msg')) { ?> <div class="alert alert-success">  <?php echo $this->session->flashdata('msg'); $this->session->unset_userdata('msg'); ?> </div> <?php } ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <form role="form" action="<?php echo site_url('report/alumnireport') ?>" method="post" class="">
                                        <?php echo $this->customlib->getCSRF(); ?>
                                        <div class="col-sm-3">
                                            <div class="form-group"> 
                                                <label><?php echo $this->lang->line('pass_out_session'); ?></label> <small class="req"> *</small> 
                                                <select autofocus="" id="session_id" name="session_id" class="form-control" >
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php
                                                    foreach ($sessionlist as $sessions) {
                                                        ?>
                                                        <option value="<?php echo $sessions['id'] ?>" <?php if (set_value('session_id') == $sessions['id']) echo "selected=selected" ?>><?php echo $sessions['session'] ?></option>
                                                        <?php
                                                        $count++;
                                                    }
                                                    ?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('session_id'); ?></span>
                                            </div>  
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                                <select id="section_id" name="section_id" class="form-control">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                                <select id="program_id" name="program_id" class="form-control">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><?php echo $this->lang->line('class'); ?></label> <small class="req"> *</small>
                                                <select id="class_id" name="class_id" class="form-control">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                </select>
                                                <span class="text-danger" id="error_class_id"></span>
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-sm pull-right checkbox-toggle"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                            </div>
                                        </div>
                                    </form>    
                                </div> 
                            </div><!--./col-md-6-->
                            <div class="col-md-2">
                                <div class="row">
                                </div>
                            </div>
                        </div><!--./row-->
                    </div>

                    <?php
                    if (isset($resultlist)) {
                        ?>
                        <div class="nav-tabs-custom border0 navnoshadow">
                            <div class="box-header ptbnull">
                                <h3 class="box-title titlefix"><i class="fa fa-users"></i> <?php echo $title; ?></h3>
                            </div> 

                            <div class="tab-content">
                                <div class="download_label"><?php echo $title; ?></div>
                                <div class="tab-pane active table-responsive no-padding" >
                                    <table class="table table-striped table-bordered table-hover example" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th><?php echo $this->lang->line('admission_no'); ?></th>
                                                <th><?php echo $this->lang->line('student_name'); ?></th>
                                                <th><?php echo $this->lang->line('class'); ?></th>
                                                <th><?php echo $this->lang->line('gender'); ?></th>
                                                <th><?php echo $this->lang->line('current_email'); ?></th>
                                                <th><?php echo $this->lang->line('date_of_birth'); ?></th>
                                                <th><?php echo $this->lang->line('current_address'); ?></th>
                                                <th><?php echo $this->lang->line('occupation'); ?></th>         
                                                <th><?php echo $this->lang->line('current_phone'); ?></th>
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
                                                        <td><?php echo $student['admission_no']; ?></td>
                                                        <td>
                                                            <?php echo $this->customlib->getFullName($student['firstname'],$student['middlename'],$student['lastname'],$sch_setting->middlename,$sch_setting->lastname); ?>
                                                        </td>
                                                        <td><?php echo $student['class']; ?></td>
                                                        <td><?php echo $student['gender']; ?></td>
                                                        <td>
                                                            <?php
                                                            if (array_key_exists($student['id'], $alumni_studets)) {
                                                                echo $alumni_studets[$student['id']]['current_email'];
                                                            }
                                                            ?>
                                                        </td>												  
                                                        <td><?php echo $this->customlib->dateformat($student['dob']); ?></td>						  
                                                        <td>
                                                            <?php
                                                            if (array_key_exists($student['id'], $alumni_studets)) {
                                                                echo $alumni_studets[$student['id']]['address'];
                                                            } else {
                                                                echo $student['current_address'];
                                                            }
                                                            ?> <?php echo $student['city'] ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            if (array_key_exists($student['id'], $alumni_studets)) {

                                                                echo $alumni_studets[$student['id']]['occupation'];
                                                            }
                                                            ?>
                                                        </td>						  
                                                        <td>
                                                            <?php
                                                            if (array_key_exists($student['id'], $alumni_studets)) {
                                                                echo $alumni_studets[$student['id']]['current_phone'];
                                                            }
                                                            ?>
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
                        </div>
                    </div><!--./box box-primary -->   
                    <?php
                }
                ?>
            </div>  
        </div> 
    </section>
</div>
<div class="modal fade" id="add_alumni" tabindex="-1" role="dialog" aria-labelledby="evaluation" style="padding-left: 0 !important">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-media-content">
            <div class="modal-header modal-media-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="box-title"> <?php echo $this->lang->line('add_alumni_details'); ?></h4>
            </div>
            <div class="modal-body pt0 pb0" >
                <form id="formadd" method="post" class="ptt10" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="row">
                                <input type="hidden" id="student_id"  name="student_id">
                                <input type="hidden" id="id"  name="id">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="pwd"><?php echo $this->lang->line('current_phone'); ?></label><small class="req"> *</small>
                                        <input type="text" id="current_phone" name="current_phone" class="form-control">
                                    </div>
                                </div> 
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="pwd"><?php echo $this->lang->line('current_email'); ?></label>
                                        <input type="text" id="current_email" name="current_email" class="form-control" >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('occupation'); ?></label>
                                        <textarea name="occupation" id="occupation" class="form-control" >

                                        </textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('address'); ?></label>
                                        <textarea name="address" id="address" class="form-control" >

                                        </textarea>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="pwd"><?php echo $this->lang->line('photo'); ?></label>
                                        <input type="file" id="file"  name="file" class="form-control filestyle">
                                    </div>
                                </div>
                            </div><!--./row-->
                        </div><!--./col-md-12-->
                    </div><!--./row-->
            </div><!--./row-->
            <div class="box-footer">
                <div class="pull-right paddA10">
                    <button type="submit" class="btn btn-info" id="submit" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('please_wait'); ?>"><?php echo $this->lang->line('save') ?></button>
                </div>
            </div>
            </form>
        </div>
    </div>
</div>
</div> 

<script type="application/javascript">

    function deletestudent(id){

    var result = confirm("<?php echo $this->lang->line('delete_confirm'); ?>");
    if(result){
    $.ajax({
    url: "<?php echo base_url(); ?>admin/alumni/deletestudent/"+id,
    type: "POST",         

    success: function (res)
    { 
    successMsg('<?php echo $this->lang->line("delete_message"); ?>');

    window.location.reload(true);

    },
    error: function (xhr) { // if error occured
    alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");

    },
    complete: function () {

    }
    });
    }

    }
</script>

<script type="text/javascript">

    function add(student_id) {
        $.ajax({
            type: "POST",
            url: base_url + "admin/alumni/get_alumnidetails",
            data: {'student_id': student_id},
            dataType: "json",
            success: function (data) {
                $('#id').val(data.id);
                $('#current_email').val(data.current_email);
                $('#current_phone').val(data.current_phone);
                $('#occupation').val(data.occupation);
                $('#address').val(data.address);
                $('#student_id').val(student_id);
                $("#add_alumni").modal("show");
            }
        });
    }

    $("#formadd").on('submit', (function (e) {
        e.preventDefault();

        var $this = $(this).find("button[type=submit]:focus");

        $.ajax({
            url: "<?php echo site_url("admin/alumni/add") ?>",
            type: "POST",
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function () {
                $this.button('loading');
            },
            success: function (res)
            {
                if (res.status == "fail") {
                    var message = "";
                    $.each(res.error, function (index, value) {
                        message += value;
                    });
                    errorMsg(message);
                } else {
                    successMsg(res.message);
                    window.location.reload(true);
                }
            },
            error: function (xhr) { // if error occured
                alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                $this.button('reset');
            },
            complete: function () {
                $this.button('reset');
            }
        });
    }));

$(document).ready(function () {
    // Store selected values
    var selectedSection = '<?php echo set_value("section_id") ?>';
    var selectedProgram = '<?php echo set_value("program_id") ?>';
    var selectedClass = '<?php echo set_value("class_id") ?>';
    
    // Fetch all sections on page load
    loadSections();
    
    function loadSections() {
        $.ajax({
            url: '<?php echo base_url(); ?>sections/getAll',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                
                $.each(response, function (index, section) {
                    var selected = (selectedSection == section.id) ? 'selected' : '';
                    html += '<option value="' + section.id + '" ' + selected + '>' + section.section + '</option>';
                });
                
                $('#section_id').html(html);
                
                // If there's a pre-selected section, load programs
                if(selectedSection) {
                    loadPrograms(selectedSection);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error loading sections:", error);
            }
        });
    }
    
    function loadPrograms(sectionId) {
        if (!sectionId) return;
        
        $('#program_id').html('<option value="">Loading...</option>');
        $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        
        $.ajax({
            url: '<?php echo base_url(); ?>programs/getBySection', 
            method: 'POST',
            data: {
                section_id: sectionId,
                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function (response) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                
                if(response && response.length > 0) {
                    $.each(response, function (index, program) {
                        var selected = (selectedProgram == program.id) ? 'selected' : '';
                        html += '<option value="' + program.id + '" ' + selected + '>' + program.title + '</option>';
                    });
                } else {
                    html += '<option value="" disabled>No programs found</option>';
                }
                
                $('#program_id').html(html);
                
                // If there's a pre-selected program, load classes
                if(selectedProgram) {
                    loadClasses(sectionId, selectedProgram);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching programs:", error);
                $('#program_id').html('<option value="">Error loading programs</option>');
            }
        });
    }
    
    function loadClasses(sectionId, programId) {
        if (!sectionId || !programId) return;
        
        $('#class_id').html('<option value="">Loading...</option>');
        
        $.ajax({
            url: '<?php echo base_url(); ?>classes/getBySectionAndProgram',
            method: 'POST',
            data: {
                section_id: sectionId,
                program_id: programId,
                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function(response) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                
                if (response && response.length > 0) {
                    $.each(response, function(index, classItem) {
                        var selected = (selectedClass == classItem.id) ? 'selected' : '';
                        html += '<option value="' + classItem.id + '" ' + selected + '>' + 
                               (classItem.degree ? classItem.degree + ' - ' : '') + 
                               classItem.class + '</option>';
                    });
                } else {
                    html += '<option value="" disabled>No classes found for this combination</option>';
                }
                
                $('#class_id').html(html);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                $('#class_id').html('<option value="">Error loading classes</option>');
            }
        });
    }
    
    // Event handlers
    $('#section_id').on('change', function() {
        var sectionId = $(this).val();
        selectedSection = sectionId;
        selectedProgram = '';
        selectedClass = '';
        
        if (sectionId) {
            loadPrograms(sectionId);
        } else {
            $('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
            $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        }
    });
    
    $('#program_id').on('change', function() {
        var programId = $(this).val();
        var sectionId = $('#section_id').val();
        selectedProgram = programId;
        selectedClass = '';
        
        if (sectionId && programId) {
            loadClasses(sectionId, programId);
        } else {
            $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        }
    });
});
</script>
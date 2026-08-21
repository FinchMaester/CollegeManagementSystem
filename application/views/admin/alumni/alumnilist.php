<?php
    $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
 
<link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
<script src="<?php echo base_url(); ?>backend/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-user-plus"></i>  <small></small></h1>
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
                        <?php if ($this->session->flashdata('msg')) {?> <div class="alert alert-success">  <?php echo $this->session->flashdata('msg'); $this->session->unset_userdata('msg'); ?> </div> <?php }?>
                        <div class="row">
                            <div class="col-md-7">
                                <div class="row">
                                    <form role="form" action="<?php echo site_url('admin/alumni/alumnilist') ?>" method="post" class="">
                                        <?php echo $this->customlib->getCSRF(); ?>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label for="session_id"><?php echo $this->lang->line('pass_out_session'); ?></label><small class="req"> *</small>
                                                <select autofocus="" id="session_id" name="session_id" class="form-control">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php
                                                    if (!empty($sessionlist)) {
                                                        foreach ($sessionlist as $sessions) {
                                                            ?>
                                                            <option value="<?php echo $sessions['id']; ?>" <?php echo set_select('session_id', $sessions['id']); ?>><?php echo $sessions['session']; ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('session_id'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label for="section_id"><?php echo $this->lang->line('faculty'); ?></label>
                                                <select id="section_id" name="section_id" class="form-control" >
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label for="program_id"><?php echo $this->lang->line('program'); ?></label>
                                                <select id="program_id" name="program_id" class="form-control" >
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label for="class_id"><?php echo $this->lang->line('class'); ?> <small class="text-muted">(optional)</small></label>
                                                <select id="class_id" name="class_id" class="form-control" >
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                                <small class="text-muted">Last enrolled level when they passed out.</small>
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-sm pull-right checkbox-toggle"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div><!--./col-md-7-->
                            <div class="col-md-5">
                                <div class="row">
                                    <form role="form" action="<?php echo site_url('admin/alumni/alumnilist') ?>" method="post" class="">
                                        <?php echo $this->customlib->getCSRF(); ?>
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label>Search by admission no / name / phone / email</label>
                                                <input type="text" name="search_text" value="<?php echo set_value('search_text'); ?>" class="form-control" placeholder="Admission no, name, phone or email">
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <button type="submit" name="search" value="search_full" class="btn btn-primary pull-right btn-sm checkbox-toggle"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div><!--./col-md-5-->
                        </div><!--./row-->
                    </div>
                    <?php
if (isset($resultlist)) {
    ?>
                        <div class="nav-tabs-custom border0 navnoshadow">
                            <div class="box-header ptbnull"></div>
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true"><i class="fa fa-list"></i> <?php echo $this->lang->line('list_view'); ?></a></li>
                                <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false"><i class="fa fa-newspaper-o"></i> <?php echo $this->lang->line('details_view'); ?></a></li>
                            </ul>
                            <div class="tab-content">
                                <div class="download_label"><?php echo $title; ?></div>
                                <div class="tab-pane active table-responsive no-padding overflow-visible" id="tab_1">
                                    <table class="table table-striped table-bordered table-hover example" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th><?php echo $this->lang->line('admission_no'); ?></th>
                                                <th><?php echo $this->lang->line('student_name'); ?></th>
                                                <th><?php echo $this->lang->line('class'); ?></th>
                                                <th><?php echo $this->lang->line('gender'); ?></th>
                                                <th><?php echo $this->lang->line('current_email'); ?></th>
                                                <th><?php echo $this->lang->line('current_phone'); ?></th>
                                                <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
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
                                                            <?php echo $this->customlib->getFullName($student['firstname'], $student['middlename'], $student['lastname'], $sch_setting->middlename, $sch_setting->lastname); ?>
                                                        </td>
                                                        <td><?php echo $student['class']; ?></td>
                                                        <td><?php echo $this->lang->line(strtolower($student['gender'])); ?></td>
                                                        <td><?php
if (array_key_exists($student['id'], $alumni_studets)) {
                echo $alumni_studets[$student['id']]['current_email'];
            }
            ?></td>
                                                        <td><?php
if (array_key_exists($student['id'], $alumni_studets)) {
                echo $alumni_studets[$student['id']]['current_phone'];
            }
            ?></td>
                                                        <td class="pull-right">
                                                            <?php
if (array_key_exists($student['id'], $alumni_studets)) {
                if ($this->rbac->hasPrivilege('manage_alumni', 'can_edit')) {
                    ?>


                                                                    <a href="#" onclick="add('<?php echo $student['id']; ?>')" class="btn btn-default btn-xs" data-toggle="tooltip" title="" data-original-title="<?php echo $this->lang->line('edit') ?>"><i class="fa fa-pencil"></i></a>
                                                                    <?php
}
                if ($this->rbac->hasPrivilege('manage_alumni', 'can_delete')) {
                    ?>
                                                                    <a href="#" onclick="deletestudent('<?php echo $student['id']; ?>')" data-toggle="tooltip" class="btn btn-default btn-xs"  title="<?php echo $this->lang->line('delete') ?>"><i class="fa fa-remove"></i></a>
                                                                    <?php
}
            } else {
                if ($this->rbac->hasPrivilege('manage_alumni', 'can_add')) {
                    ?>
                                                                    <a href="#" onclick="add('<?php echo $student['id']; ?>')" class="btn btn-default btn-xs" data-toggle="tooltip" title="" data-original-title="<?php echo $this->lang->line('add') ?>">


                                                                        <i class="fa fa-plus"></i>
                                                                    </a>
                                                                <?php }
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
                                <div class="tab-pane" id="tab_2">
                                    <?php if (empty($resultlist)) {
        ?>
                                        <div class="alert alert-info"><?php echo $this->lang->line('no_record_found'); ?></div>
                                        <?php
} else {
        $count = 1;
       
        foreach ($resultlist as $student) {
           
           if (array_key_exists($student['id'], $alumni_studets) && (!empty($alumni_studets[$student['id']]['photo']))) {
                    $image = 'uploads/alumni_student_images/'.$alumni_studets[$student['id']]['photo'];
            } elseif(!empty($student["image"])) {
                    $image = $student["image"];
            } else {
                $image = "uploads/student_images/no_image.png";
            }            
            ?>
                                            <div class="carousel-row">
                                                <div class="slide-row">
                                                    <div id="carousel-2" class="carousel slide slide-carousel" data-ride="carousel">
                                                        <div class="carousel-inner">
                                                            <div class="item active">
                                                                <a href="<?php echo base_url(); ?>student/view/<?php echo $student['id'] ?>">
                                                                 <img class="img-responsive img-thumbnail width150" alt="<?php echo $student["firstname"] . " " . $student["lastname"] ?>" src="<?php echo $this->media_storage->getImageURL($image); ?>" alt="Image">
                                                             </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="slide-content">
                                                        <h4><a href="<?php echo base_url(); ?>student/view/<?php echo $student['id'] ?>">  <?php echo $this->customlib->getFullName($student['firstname'], $student['middlename'], $student['lastname'], $sch_setting->middlename, $sch_setting->lastname); ?></a></h4>
                                                        <div class="row">
                                                            <div class="col-xs-6 col-md-6">
                                                                <address>
                                                                    <strong><b><?php echo $this->lang->line('class'); ?>: </b><?php echo $student['class']; ?></strong><br>
                                                                    <b><?php echo $this->lang->line('admission_no'); ?>: </b><?php echo $student['admission_no'] ?><br/>
                                                                    <b><?php echo $this->lang->line('date_of_birth'); ?>:
            <?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($student['dob'])); ?><br>
                                                                        <b><?php echo $this->lang->line('gender'); ?>:&nbsp;</b><?php echo $this->lang->line(strtolower($student['gender'])) ?><br>
                                                                        <b><?php echo $this->lang->line('national_identification_number'); ?>:&nbsp;</b><?php echo $student['adhar_no'] ?><br>
                                                                        </address>
                                                                        </div>
                                                                        <div class="col-xs-6 col-md-6">
                                                                            <b><?php echo $this->lang->line('current_phone'); ?>: </b> <abbr title="Phone"><i class="fa fa-phone-square"></i>&nbsp;</abbr> <?php
if (array_key_exists($student['id'], $alumni_studets)) {
                echo $alumni_studets[$student['id']]['current_phone'];
            }
            ?><br>
                                                                            <b>
                                                                                <b><?php echo $this->lang->line('current_email'); ?>: </b> <abbr title="Phone">&nbsp;</abbr> <?php
if (array_key_exists($student['id'], $alumni_studets)) {
                echo $alumni_studets[$student['id']]['current_email'];
            }
            ?><br>
                                                                                <b><?php echo $this->lang->line('current_address'); ?>:&nbsp;</b><?php
if (array_key_exists($student['id'], $alumni_studets)) {
                echo $alumni_studets[$student['id']]['address'];
            } else {


                echo $student['current_address'];
            }
            ?> <?php echo $student['city'] ?><br>
                                                                                <?php if (array_key_exists($student['id'], $alumni_studets)) {
                ?><b><?php echo $this->lang->line('occupation'); ?>:&nbsp;</b><?php
echo $alumni_studets[$student['id']]['occupation'];
            }
            ?> <br>
                                                                                </div>
                                                                                </div>
                                                                                </div>
                                                                                <div class="slide-footer">
                                                                                    <span class="pull-right buttons">










                                                                                        <?php
if (array_key_exists($student['id'], $alumni_studets)) {
                if ($this->rbac->hasPrivilege('manage_alumni', 'can_edit')) {
                    ?>


                                                                                                <a href="#" onclick="add('<?php echo $student['id']; ?>')" class="btn btn-default btn-xs" data-toggle="tooltip" title="" data-original-title="<?php echo $this->lang->line('edit') ?>"><i class="fa fa-pencil"></i></a>
                <?php } if ($this->rbac->hasPrivilege('manage_alumni', 'can_delete')) { ?>
                                                                                                <a href="#" onclick="deletestudent('<?php echo $student['id']; ?>')" data-toggle="tooltip" class="btn btn-default btn-xs"  title="<?php echo $this->lang->line('delete') ?>"><i class="fa fa-remove"></i></a>
                                                                                                <?php
}
            } else {
                if ($this->rbac->hasPrivilege('manage_alumni', 'can_add')) {
                    ?>
                                                                                                <a href="#" onclick="add('<?php echo $student['id']; ?>')" class="btn btn-default btn-xs" data-toggle="tooltip" title="" data-original-title="<?php echo $this->lang->line('add') ?>">


                                                                                                    <i class="fa fa-plus"></i>
                                                                                                </a>
                                                                                            <?php }
            }
            ?>
                                                                                    </span>
                                                                                </div>
                                                                        </div>
                                                                        </div>
                                                                        <?php
}
        $count++;
    }
    ?>
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
                                                                            <h4 class="box-title" ><span id="title_id"> <?php echo $this->lang->line('manage_alumni_details'); ?></span></h4>
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
                                                                                                    <label for="pwd"><?php echo $this->lang->line('current_photo'); ?></label>
                                                                                                    <input type="file" id="documents"  name="documents" class="form-control filestyle">
                                                                                                </div>
                                                                                            </div>
                                                                                        </div><!--./row-->
                                                                                    </div><!--./col-md-12-->
                                                                                </div><!--./row-->
                                                                        </div><!--./row-->
                                                                        <div class="box-footer">
                                                                            <div class="pull-right paddA10">
                                                                                <button type="submit" class="btn btn-info" id="submit" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('please_wait') ?>"><?php echo $this->lang->line('save') ?></button>
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
        $("#submit").prop("disabled", true);        
        $.ajax({
            url: "<?php echo site_url("admin/alumni/add") ?>",
            type: "POST",
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function () {


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
                $("#submit").prop("disabled", false);
            },
            complete: function () {              
                $("#submit").prop("disabled", false);
            }          


        });
    }));


var section_id = '<?php echo set_value('section_id') ?>';
var program_id = '<?php echo set_value('program_id') ?>';
var class_id = '<?php echo set_value('class_id') ?>';
$(document).ready(function () {
    // Fetch all faculties (sections)
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
            // If there's a pre-selected section, trigger program load
            if(section_id) {
                getProgramsBySection(section_id, program_id);
            }
        },
        error: function(xhr, status, error) {
            $('#section_id').html('<option value="">Error loading sections</option>');
        }
    });
    // Faculty/Section change event
    $(document).on('change', '#section_id', function (e) {
        $('#program_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
        $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
        var section_id = $(this).val();
        if (section_id) {
            getProgramsBySection(section_id);
        }
    });
    // Program change event
    $(document).on('change', '#program_id', function (e) {
        $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
        var program_id = $(this).val();
        if (program_id) {
            getClassesByProgram(program_id);
        }
    });
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
                // If we have a selected program, trigger class loading
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
            },
            error: function() {
                $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            }
        });
    }
}
</script>


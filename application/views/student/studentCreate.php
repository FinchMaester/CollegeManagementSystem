<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<link href="<?php echo base_url(); ?>backend/multiselect/css/jquery.multiselect.css" rel="stylesheet">
<script src="<?php echo base_url(); ?>backend/multiselect/js/jquery.multiselect.js"></script>
<div class="content-wrapper">
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="pull-right box-tools impbtntitle">
                        <?php if ($this->rbac->hasPrivilege('import_student', 'can_view')) {?>
                            <?php /* Use link styled as button — <button> inside <a> is invalid and can submit parent form / confuse browsers */ ?>
                            <a class="btn btn-primary btn-sm" href="<?php echo site_url('student/import'); ?>"><i class="fa fa-upload"></i> <?php echo $this->lang->line('import_student'); ?></a>
                        <?php }
?>
                    </div>
                    <form id="form1" action="<?php echo site_url('student/create') ?>" name="employeeform" method="post" accept-charset="utf-8" enctype="multipart/form-data">
                        <div class="">
                            <div class="bozero">
                                <h4 class="pagetitleh-whitebg"><?php echo $this->lang->line('student'); ?> <?php echo $this->lang->line('admission'); ?> </h4>
                                <div class="around10">
                                    <?php if ($this->session->flashdata('msg')) {
    ?>
                                        <?php
                                        echo $this->session->flashdata('msg');
                                            $this->session->unset_userdata('msg');
                                            ?>
                                    <?php }?>
                                    <div id="form-alert"></div>

                                    <?php //if (isset($error_message)) {?>
                                        <!--<div class="alert alert-warning"><?php //echo $error_message; ?></div> -->
                                    <?php //}?>
                                    <?php echo $this->customlib->getCSRF(); ?>
                                    <input type="hidden" name="sibling_name" value="<?php echo set_value('sibling_name'); ?>" id="sibling_name_next">
                                    <input type="hidden" name="sibling_id" value="<?php echo set_value('sibling_id', 0); ?>" id="sibling_id">
                                    <div class="col-md-12">
                                        <h4 class="pagetitleh2" style="margin-top:0;">Academic details</h4>
                                        <p class="text-muted small">HEMIS / UGC fields use official Program, Batch, and Fiscal Year metadata.</p>
                                    </div>
                                    <div class="row">
                                        <?php if (!$adm_auto_insert) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('admission_no'); ?></label> <small class="req"> *</small>
                                                    <input autofocus="" id="admission_no" name="admission_no" placeholder="" type="text" class="form-control"  value="<?php echo set_value('admission_no'); ?>" />
                                                    <span class="text-danger"><?php echo form_error('admission_no'); ?></span>
                                                </div>
                                            </div>
                                        <?php }?>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="admission_year"><?php echo $this->lang->line('admission_year'); ?></label><small class="req"> *</small>
                                                <select id="admission_year" name="admission_year" class="form-control">
                                                    <option value="">Select Year</option>
                                                    <option value="2076" <?php echo set_select('admission_year', '2076'); ?>>2076</option>
                                                    <option value="2077" <?php echo set_select('admission_year', '2077'); ?>>2077</option>
                                                    <option value="2078" <?php echo set_select('admission_year', '2078'); ?>>2078</option>
                                                    <option value="2079" <?php echo set_select('admission_year', '2079'); ?>>2079</option>
                                                    <option value="2080" <?php echo set_select('admission_year', '2080'); ?>>2080</option>
                                                    <option value="2081" <?php echo set_select('admission_year', '2081'); ?>>2081</option>
                                                    <option value="2082" <?php echo set_select('admission_year', '2082'); ?>>2082</option>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('admission_year'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="ugc_fiscal_year_id">UGC Fiscal Year <span class="label label-info">HEMIS</span></label>
                                                <select id="ugc_fiscal_year_id" name="ugc_fiscal_year_id" class="form-control">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('ugc_fiscal_year_id'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="ugc_batch_id">UGC Batch <span class="label label-info">HEMIS</span></label>
                                                <select id="ugc_batch_id" name="ugc_batch_id" class="form-control">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('ugc_batch_id'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="admissionYearId">Admission Year ID <span class="label label-info">HEMIS</span></label><small class="req"> *</small>
                                                <input id="admissionYearId" name="admissionYearId" type="number" class="form-control" min="1" value="<?php echo set_value('admissionYearId'); ?>" />
                                                <small class="text-muted">Uses UGC Batch ID (same catalog at KBMC). Auto-filled from UGC Batch; editable.</small>
                                                <small id="admissionYearId_hint" class="text-muted"></small>
                                                <span class="text-danger"><?php echo form_error('admissionYearId'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="batch_name_nepali">Batch Name (Nepali) <span class="label label-info">HEMIS</span></label>
                                                <input id="batch_name_nepali" name="batch_name_nepali" type="text" class="form-control" value="<?php echo set_value('batch_name_nepali'); ?>" readonly />
                                                <small class="text-muted">Auto-filled from UGC Batch selection.</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="batch_name">Batch Name <span class="label label-info">HEMIS</span></label>
                                                <input id="batch_name" name="batch_name" type="text" class="form-control" value="<?php echo set_value('batch_name'); ?>" readonly />
                                                <small class="text-muted">Optional; can be filled later if needed.</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Faculty <span class="label label-info">HEMIS</span></label>
                                                <p class="form-control-static" id="ugc_faculty_display" style="min-height:20px;border-bottom:1px solid #eee;">—</p>
                                                <small class="text-muted">From selected UGC Program</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="ugc_program_id">UGC Program <span class="label label-info">HEMIS</span></label>
                                                <select id="ugc_program_id" name="ugc_program_id" class="form-control">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                </select>
                                                <small id="ugc_program_hint" class="text-muted"></small>
                                                <span class="text-danger"><?php echo form_error('ugc_program_id'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Academic level <span class="label label-info">HEMIS</span></label>
                                                <p class="form-control-static" id="ugc_academic_level_display" style="min-height:20px;border-bottom:1px solid #eee;">—</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <?php if ($sch_setting->admission_date) {?>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="admission_date"><?php echo $this->lang->line('admission_date'); ?></label>
                                                <input id="admission_date" name="admission_date" placeholder="YYYY-MM-DD (BS)" type="text" class="form-control date-bs nepali-datepicker" data-nepali-date="true" value="<?php echo set_value('admission_date'); ?>" autocomplete="off" />
                                                <span class="text-danger"><?php echo form_error('admission_date'); ?></span>
                                            </div>
                                        </div>
                                        <?php } ?>
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
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="semister_or_year_no">Academic Year / Semester <span class="label label-info">HEMIS</span></label>
                                                <input id="semister_or_year_no" name="semister_or_year_no" type="text" class="form-control" value="<?php echo set_value('semister_or_year_no'); ?>" readonly />
                                                <small class="text-muted">Auto from Class + UGC program type (annual → year, semester → semester).</small>
                                                <span class="text-danger"><?php echo form_error('semister_or_year_no'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="complition_year">Expected completion year <span class="label label-info">HEMIS</span></label>
                                                <input id="complition_year" name="complition_year" type="number" class="form-control" min="1970" max="2100" placeholder="e.g. 2085" value="<?php echo set_value('complition_year'); ?>" />
                                                <small class="text-muted">Maps to HEMIS <code>complitionYear</code>.</small>
                                                <span class="text-danger"><?php echo form_error('complition_year'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1"><?php echo $this->lang->line('academic_program_duration'); ?></label><small class="req"> *</small>
                                                <input id="academic_program_duration" name="academic_program_duration" placeholder="" type="text" class="form-control"  value="<?php echo set_value('academic_program_duration'); ?>" />
                                                <span class="text-danger"><?php echo form_error('academic_program_duration'); ?></span>
                                            </div>
                                        </div>
                                        <?php if ($sch_setting->roll_no) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="roll_no"><?php echo $this->lang->line('roll_number'); ?></label>
                                                    <input id="roll_no" name="roll_no" placeholder="" type="text" class="form-control" value="<?php echo set_value('roll_no'); ?>" />
                                                    <span class="text-danger"><?php echo form_error('roll_no'); ?></span>
                                                </div>
                                            </div>
                                        <?php }?>
                                    </div>
                                    <div class="col-md-12">
                                        <h4 class="pagetitleh2" style="margin-top:15px;">Basic details</h4>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1"><?php echo $this->lang->line('first_name'); ?></label><small class="req"> *</small>
                                                <input id="firstname" name="firstname" placeholder="" type="text" class="form-control"  value="<?php echo set_value('firstname'); ?>" />
                                                <span class="text-danger"><?php echo form_error('firstname'); ?></span>
                                            </div>
                                        </div>
                                        <?php if ($sch_setting->middlename) {?>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('middle_name'); ?></label>
                                                    <input id="middlename" name="middlename" placeholder="" type="text" class="form-control"  value="<?php echo set_value('middlename'); ?>" />
                                                    <span class="text-danger"><?php echo form_error('middlename'); ?></span>
                                                </div>
                                            </div>
                                        <?php }?>
                                        <?php if ($sch_setting->lastname) {?>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('last_name'); ?></label><small class="req"> *</small>
                                                    <input id="lastname" name="lastname" placeholder="" type="text" class="form-control"  value="<?php echo set_value('lastname'); ?>" />
                                                    <span class="text-danger"><?php echo form_error('lastname'); ?></span>
                                                </div>
                                            </div>
                                        <?php }?>


                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1"><?php echo $this->lang->line('first_name_np'); ?></label><small class="req"> *</small>
                                                <input id="first_name_np" name="first_name_np" placeholder="" type="text" class="form-control"  value="<?php echo set_value('first_name_np'); ?>" />
                                                <span class="text-danger"><?php echo form_error('first_name_np'); ?></span>
                                            </div>
                                        </div>
                                        <?php if ($sch_setting->middlename) {?>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('middle_name_np'); ?></label>
                                                    <input id="middle_name_np" name="middle_name_np" placeholder="" type="text" class="form-control"  value="<?php echo set_value('middle_name_np'); ?>" />
                                                    <span class="text-danger"><?php echo form_error('middle_name_np'); ?></span>
                                                </div>
                                            </div>
                                        <?php }?>
                                        <?php if ($sch_setting->lastname) {?>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('last_name_np'); ?></label><small class="req"> *</small>
                                                    <input id="last_name_np" name="last_name_np" placeholder="" type="text" class="form-control"  value="<?php echo set_value('last_name_np'); ?>" />
                                                    <span class="text-danger"><?php echo form_error('last_name_np'); ?></span>
                                                </div>
                                            </div>
                                        <?php }?>
                                        
                                    </div>
                                    <div class="row">


                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="exampleInputFile"> <?php echo $this->lang->line('gender'); ?></label><small class="req"> *</small>
                                            <select class="form-control" name="gender">
                                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                <?php
                                                    foreach ($genderList as $key => $value) {
                                                        ?>
                                                    <option value="<?php echo $key; ?>" <?php echo (set_value('gender') == $key) ? 'selected' : ''; ?>><?php echo $value; ?></option>
                                                    <?php
                                                    }
                                                ?>
                                            </select>
                                            <span class="text-danger"><?php echo form_error('gender'); ?></span>
                                        </div>
                                    </div>
                                                                            
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="dob"><?php echo $this->lang->line('date_of_birth_bs'); ?></label><small class="req"> *</small>

                                            <div class="form-group">
                                                <input id="dob" name="dob" placeholder="YYYY-MM-DD (BS)" type="text" class="form-control date-bs nepali-datepicker" data-nepali-date="true" value="<?php echo isset($req['dob']) ? $req['dob'] : set_value('dob'); ?>" autocomplete="off" />
                                            </div>

                                            <span class="text-danger"><?php echo form_error('dob'); ?></span>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="dob_ad"><?php echo $this->lang->line('date_of_birth'); ?></label>
                                            <input id="dob_ad" name="dob_ad" placeholder="" type="text" class="form-control date" value="<?php echo set_value('dob_ad'); ?>" />
                                            <span class="text-danger"><?php echo form_error('dob_ad'); ?></span>
                                        </div>
                                    </div>

                                        
                                        <?php if ($sch_setting->mobile_no) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('mobile_number'); ?></label>
                                                    <input id="mobileno" name="mobileno" placeholder="" type="text" class="form-control"  value="<?php echo set_value('mobileno'); ?>" />
                                                    <span class="text-danger"><?php echo form_error('mobileno'); ?></span>
                                                </div>
                                            </div>
                                            <?php } ?>
                                            <?php if ($sch_setting->student_email) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('email'); ?></label>
                                                    <input id="email" name="email" placeholder="" type="text" class="form-control"  value="<?php echo set_value('email'); ?>" />
                                                    <span class="text-danger"><?php echo form_error('email'); ?></span>
                                                </div>
                                            </div>
                                            <?php } ?>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="ethnicity">Ethnicity <span class="label label-info">HEMIS</span></label><small class="req"> *</small>
                                                <input id="ethnicity" name="ethnicity" list="ethnicity_datalist" placeholder="e.g. Brahmin / Kshetri / Tharu" type="text" class="form-control" value="<?php echo set_value('ethnicity'); ?>" autocomplete="off" />
                                                <datalist id="ethnicity_datalist">
                                                    <?php
                                                    $eth_opts = isset($ethnicities) && is_array($ethnicities) ? $ethnicities : array();
                                                    foreach ($eth_opts as $eth_val) {
                                                        echo '<option value="' . htmlspecialchars($eth_val, ENT_QUOTES, 'UTF-8') . '">';
                                                    }
                                                    ?>
                                                </datalist>
                                                <small class="text-muted">Required by HEMIS. Legacy Caste is filled automatically from this value.</small>
                                                <span class="text-danger"><?php echo form_error('ethnicity'); ?></span>
                                            </div>
                                        </div>

                                        <?php if ($sch_setting->category) {
                                            ?>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('category'); ?></label>
                                                    <select  id="category_id" name="category_id" class="form-control" >
                                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                        <?php foreach ($categorylist as $category) {?>
                                                            <option value="<?php echo $category['id'] ?>" <?php
                                                            if (set_value('category_id') == $category['id']) {
                                                                    echo "selected=selected";
                                                                }
                                                                    ?>><?php echo $category['category'] ?></option>
                                                                    <?php $count++;
                                                            }
                                                        ?>
                                                    </select>
                                                    <span class="text-danger"><?php echo form_error('category_id'); ?></span>
                                                </div>
                                            </div>
                                            <?php }if ($sch_setting->religion) {?>
                                                <div class="col-md-2">
                                                <div class="form-group">
                                                <label for="religion"><?php echo $this->lang->line('religion'); ?></label>
                                                <select id="religion" name="religion" class="form-control">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php
                                                    foreach ($religions as $key => $religion) {
                                                        ?>
                                                        <option value="<?php echo $key; ?>"><?php echo $religion; ?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                                </div>
                                            <?php } ?>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1"><?php echo $this->lang->line('edj'); ?></label>
                                                <input id="edj" name="edj" placeholder="" type="text" class="form-control"  value="<?php echo set_value('edj'); ?>" />
                                                <span class="text-danger"><?php echo form_error('edj'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="citizenship">Citizenship number <span class="label label-info">HEMIS</span></label>
                                                <input id="citizenship" name="citizenship" type="text" class="form-control" value="<?php echo set_value('citizenship'); ?>" />
                                                <span class="text-danger"><?php echo form_error('citizenship'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="citizenship_issue_dist">Citizenship issued district <span class="label label-info">HEMIS</span></label>
                                                <input id="citizenship_issue_dist" name="citizenship_issue_dist" type="text" class="form-control" value="<?php echo set_value('citizenship_issue_dist'); ?>" />
                                                <span class="text-danger"><?php echo form_error('citizenship_issue_dist'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="nationality">Nationality</label>
                                                <input id="nationality" name="nationality" type="text" class="form-control" value="<?php echo set_value('nationality'); ?>" />
                                                <span class="text-danger"><?php echo form_error('nationality'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <?php if ($sch_setting->national_identification_no) { ?>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="adhar_no"><?php echo $this->lang->line('national_identification_number'); ?></label>
                                                <input id="adhar_no" name="adhar_no" placeholder="" type="text" class="form-control"  value="<?php echo set_value('adhar_no'); ?>" />
                                                <span class="text-danger"><?php echo form_error('adhar_no'); ?></span>
                                            </div>
                                        </div>
                                        <?php } ?>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="disability_status">Disability Status</label>
                                                <input id="disability_status" name="disability_status" type="text" class="form-control" value="<?php echo set_value('disability_status'); ?>" />
                                                <span class="text-danger"><?php echo form_error('disability_status'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="disability_type">Disability Type</label>
                                                <input id="disability_type" name="disability_type" type="text" class="form-control" value="<?php echo set_value('disability_type'); ?>" />
                                                <span class="text-danger"><?php echo form_error('disability_type'); ?></span>
                                            </div>
                                        </div>
                                        <?php if ($sch_setting->student_photo) { ?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputFile"><?php echo $this->lang->line('student_photo'); ?></label>
                                                    <div><input class="filestyle form-control" type='file' name='file' id="file" size='20' />
                                                    </div>
                                                    <span class="text-danger"><?php echo form_error('file'); ?></span></div>
                                            </div>
                                        <?php } ?>
                                    </div>

<div class="row">
                                                <?php if ($sch_setting->student_height) {?>
                                            <div class="col-md-3 col-xs-12">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('height'); ?></label>
    <?php ?>
                                                    <input type="text" name="height" class="form-control" value="<?php echo set_value('height'); ?>" >
                                                    <span class="text-danger"><?php echo form_error('height'); ?></span>
                                                </div>
                                            </div>
                                                <?php }if ($sch_setting->student_weight) {?>
                                            <div class="col-md-3 col-xs-12">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('weight'); ?></label>
    <?php ?>
                                                    <input type="text" name="weight" class="form-control" value="<?php echo set_value('weight'); ?>">
                                                    <span class="text-danger"><?php echo form_error('weight'); ?></span>
                                                </div>
                                            </div>
<?php }if ($sch_setting->measurement_date) {?>
                                            <div class="col-md-3 col-xs-12">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('measurement_date'); ?></label>
    <?php ?>
                                                    <input type="text" id="measure_date" value="<?php echo set_value('measure_date', date($this->customlib->getSchoolDateFormat())); ?>" name="measure_date" class="form-control date">
                                                    <span class="text-danger"><?php echo form_error('measure_date'); ?></span>
                                                </div>
                                            </div>
<?php }?> 
                                        <div class="col-md-3" style="display:none;">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1"><?php echo $this->lang->line('fees_discount'); ?></label>
                                                <input id="fees_discount" name="fees_discount" placeholder="" type="text" class="form-control"  value="<?php echo set_value('fees_discount', 0); ?>"  />
                                                <span class="text-danger"><?php echo form_error('fees_discount'); ?></span>
                                            </div>
                                        </div>

                                        <!-- <div class="col-md-3 pt25">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <button type="button" class="btn btn-sm mysiblings anchorbtn "><i class="fa fa-plus"></i> <?php echo $this->lang->line('add_sibling'); ?></button>
                                                </div>
                                                <div class="col-md-7">
                                                    <div id='sibling_id' class="pt6"> <span id="sibling_name" class="label label-success "><?php echo set_value('sibling_name'); ?></span></div>
                                                </div>
                                            </div>
                                        </div> -->
                                    </div>
                                </div>
                                </div>
                            </div>
                         <?php if ($sch_setting->route_list) {
    ?>
                                            <?php
if ($this->module_lib->hasActive('transport')) {
        ?>
                                                <div class="bozero">
                                                    <h4 class="pagetitleh2">
        <?php echo $this->lang->line('transport_details'); ?>
                                                    </h4>
                                                    <div class="row around10">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="exampleInputEmail1"><?php echo $this->lang->line('route_list'); ?></label>
                                                                <select  class="form-control" id="vehroute_id" name="vehroute_id">

                                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                                    <?php
foreach ($vehroutelist as $vehroute) {
            ?>
                                                                        <optgroup label=" <?php echo $vehroute['route_title']; ?>">
                                                                            <?php
$vehicles = $vehroute['vehicles'];
            if (!empty($vehicles)) {
                foreach ($vehicles as $key => $value) {
                    ?>

                                                                                    <option value="<?php echo $value->vec_route_id ?>" <?php echo set_select('vehroute_id', $value->vec_route_id); ?> data-fee="">
                                                                                    <?php echo $value->vehicle_no ?>
                                                                                    </option>
                                                                                    <?php
}
            }
            ?>
                                                                        </optgroup>
                                                                        <?php
}
        ?>
                                                                </select>
                                    <span class="text-danger"><?php echo form_error('vehroute_id'); ?></span>
                                                            </div>
                                                        </div>
                                                         <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="exampleInputEmail1"><?php echo $this->lang->line('pickup_point'); ?></label>
                                                                <select  class="form-control" id="pickup_point" name="route_pickup_point_id">

                                                                </select>

                             <span class="text-danger"><?php echo form_error('route_pickup_point_id'); ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="exampleInputEmail1"><?php echo $this->lang->line('fees_month'); ?></label>

                                <select class="form-control" id="transport_feemaster_id" name="transport_feemaster_id[]" multiple="multiple" >

                                                                    <?php
foreach ($transport_fees as $key => $value) {
            ?>
                                                                        <option <?php echo set_select('transport_feemaster_id[]', $value['id']); ?> value="<?php echo $value['id']; ?>"> <?php echo $this->lang->line(strtolower($value['month'])); ?></option>
                                                                        <?php
}
        ?>

                                                                </select>

                     <span class="text-danger"><?php echo form_error('transport_feemaster_id[]'); ?></span>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php }?>
                                            <?php
if ($this->module_lib->hasActive('hostel')) {
        ?>
        <?php if ($sch_setting->hostel_id) {
            ?>
                                                    <div class="bozero">
                                                        <h4 class="pagetitleh2">
            <?php echo $this->lang->line('hostel_details'); ?></label></label>
                                                        </h4>

                                                        <div class="row around10">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('hostel'); ?></label>

                                                                    <select class="form-control" id="hostel_id" name="hostel_id">

                                                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                                        <?php
foreach ($hostelList as $hostel_key => $hostel_value) {
                ?>

                                                                            <option value="<?php echo $hostel_value['id'] ?>" <?php echo set_select('hostel_id', $hostel_value['id']); ?>>
                                                                            <?php echo $hostel_value['hostel_name']; ?>
                                                                            </option>
                                                                            <?php
}
            ?>
                                                                    </select>
                                                                    <span class="text-danger"><?php echo form_error('hostel_id'); ?></span>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('room_no'); ?></label>
                                                                    <select  id="hostel_room_id" name="hostel_room_id" class="form-control" >
                                                                        <option value=""   ><?php echo $this->lang->line('select'); ?></option>
                                                                    </select>
                                                                    <span class="text-danger"><?php echo form_error('hostel_room_id'); ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php }?> <?php }
}
?> 
                         <div class="mainstudent">
                             <div id="fade"></div>
                        <div id="modal">

                            <i class="fa fa-spinner fa-spin fa-1x fa-fw"></i><span class="sr-only">Loading...</span>
                            <img id="loader" src="<?php //echo base_url('backend/images/chatloading.gif'); ?>">
                        </div>

                         <h4 class="pagetitleh2">
            <?php echo $this->lang->line('fees_details'); ?>
            <span class="float-right bmedium total_fees_alloted">
             <?php

$view_total_fees = 0;
foreach ($feesessiongroup_model as $feesessiongroup_key => $feesessiongroup_value) {
    $total_fees = 0;

    foreach ($feesessiongroup_value->feetypes as $fee_type_key => $fee_type_value) {
        $total_fees += $fee_type_value->amount;
    }

    if (isset($_POST['fee_session_group_id'])) {
        if (in_array($feesessiongroup_value->id, $_POST['fee_session_group_id'])) {
            $view_total_fees += $total_fees;
        }
    }
}
if(!empty($view_total_fees)){
echo amountFormat($view_total_fees);
}
?>
<input type="hidden" name="total_post_fees" value="<?php echo $view_total_fees; ?>">
            </span>
                         </h4>
                                                    <div class="row around10 mb10">
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="program_fee">Program Fee</label>
                                                                <input id="program_fee" name="program_fee" type="number" step="0.01" class="form-control" value="<?php echo set_value('program_fee'); ?>" />
                                                                <span class="text-danger"><?php echo form_error('program_fee'); ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="scholarship_amt">Scholarship Amount</label>
                                                                <input id="scholarship_amt" name="scholarship_amt" type="number" step="0.01" class="form-control" value="<?php echo set_value('scholarship_amt'); ?>" />
                                                                <span class="text-danger"><?php echo form_error('scholarship_amt'); ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="paid_amount">Paid Amount</label>
                                                                <input id="paid_amount" name="paid_amount" type="number" step="0.01" class="form-control" value="<?php echo set_value('paid_amount'); ?>" />
                                                                <span class="text-danger"><?php echo form_error('paid_amount'); ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row around10">
                                                        <div class="col-md-12">
                                      <?php
if (!empty($feesessiongroup_model)) {
    ?>
<div class="table-responsive border0">
<table class="table mb0">
    <tbody>
        <?php
foreach ($feesessiongroup_model as $feesessiongroup_key => $feesessiongroup_value) {
        $total_fees = 0;

        foreach ($feesessiongroup_value->feetypes as $fee_type_key => $fee_type_value) {
            $total_fees += $fee_type_value->amount;
        }
        ?>
                                          <tr>
                                            <td colspan="3" class="mailbox-name white-space-nowrap border0">
                                                  <div class="panel-group1 mb0">
    <div class="panel panel-default1">
      <div class="panel-heading pt5 pb5">
        <h6 class="panel-title panel-title1 overflow-hidden">
          <input class="fee_group_chk vertical-middle" type="checkbox" name="fee_session_group_id[]" value="<?php echo $feesessiongroup_value->id; ?>" <?php echo set_checkbox('fee_session_group_id[]', $feesessiongroup_value->id); ?>>
          <a class="display-inline collapsed box-plus-panel" data-toggle="collapse" href="#collapse_fees_<?php echo $feesessiongroup_value->id ?>">
             <span class="font14"><?php echo $feesessiongroup_value->group_name; ?></span></a>
          <span class="float-right bmedium pt3 fee_group_total" data-amount="<?php echo ($total_fees); ?>"><?php echo amountFormat($total_fees); ?></span>
        </h6>
      </div>
      <div id="collapse_fees_<?php echo $feesessiongroup_value->id ?>" class="panel-collapse collapse">
            <ul class="list-group student_fee_list ui-sortable">
                <li class="list-group-item"><div class="displayinline stfirstdiv bmedium font14 pl-65"><?php echo $this->lang->line('fees_type'); ?></div>
                    <div class="due_date bmedium font14"><?php echo $this->lang->line('due_date'); ?></div>
                    <div class="tools bmedium font14"><?php echo $this->lang->line('amount'); ?> (<?php echo $currency_symbol; ?>)</div>
                </li>
            <?php
foreach ($feesessiongroup_value->feetypes as $fee_type_key => $fee_type_value) {
            ?>
                    <li class="list-group-item">
                        <div class="displayinline stfirstdiv pl-65"><?php echo $fee_type_value->type . " (" . $fee_type_value->code . ")" ?></div>
                    <small class="due_date"><i class="fa fa-calendar"></i> <?php
echo $this->customlib->dateformat($fee_type_value->due_date);
            ?></small>
                <div class="tools">
                       <?php echo amountFormat($fee_type_value->amount); ?>
                                  </div>
                    </li>
                  <?php
}
        ?>
         </ul>
      </div>
    </div>
  </div>
                                            </td>
                                          </tr>
                                        <?php
}
    ?>
    </tbody>
</table>
</div>
    <?php
}
?>
                                                        </div>
                                                    </div>
                                                </div>

                            <?php if (($sch_setting->father_name) || ($sch_setting->father_phone) || ($sch_setting->father_occupation) || ($sch_setting->father_pic) || ($sch_setting->mother_name) || ($sch_setting->mother_phone) || ($sch_setting->mother_occupation) || ($sch_setting->mother_pic) || ($sch_setting->guardian_name) || ($sch_setting->guardian_occupation) || ($sch_setting->guardian_relation) || ($sch_setting->guardian_phone) || ($sch_setting->guardian_email) || ($sch_setting->guardian_pic) || ($sch_setting->guardian_address)) {
    ?>
                            <div class="bozero">
                                <h4 class="pagetitleh2"><?php echo $this->lang->line('parent_guardian_detail'); ?></h4>
                                <div class="around10">
                                    <div class="row">
<?php if ($sch_setting->father_name) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('father_name'); ?></label>
                                                    <input id="father_name" name="father_name" placeholder="" type="text" class="form-control"  value="<?php echo set_value('father_name'); ?>" />
                                                    <span class="text-danger"><?php echo form_error('father_name'); ?></span>
                                                </div>
                                            </div>
<?php }if ($sch_setting->father_phone) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('father_phone'); ?></label>
                                                    <input id="father_phone" name="father_phone" placeholder="" type="text" class="form-control"  value="<?php echo set_value('father_phone'); ?>" />
                                                    <span class="text-danger"><?php echo form_error('father_phone'); ?></span>
                                                </div>
                                            </div>
<?php }if ($sch_setting->father_occupation) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('father_occupation'); ?></label>
                                                    <input id="father_occupation" name="father_occupation" placeholder="" type="text" class="form-control"  value="<?php echo set_value('father_occupation'); ?>" />
                                                    <span class="text-danger"><?php echo form_error('father_occupation'); ?></span>
                                                </div>
                                            </div>
<?php }if ($sch_setting->father_pic) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputFile"><?php echo $this->lang->line('father_photo'); ?></label>
                                                    <div><input class="filestyle form-control" type='file' name='father_pic' id="file" size='20' />
                                                    </div>
                                                    <span class="text-danger"><?php echo form_error('file'); ?></span></div>
                                            </div>
<?php }?>
                                    </div>
                                    <div class="row">
<?php if ($sch_setting->mother_name) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('mother_name'); ?></label>
                                                    <input id="mother_name" name="mother_name" placeholder="" type="text" class="form-control"  value="<?php echo set_value('mother_name'); ?>" />
                                                    <span class="text-danger"><?php echo form_error('mother_name'); ?></span>
                                                </div>
                                            </div>
<?php }if ($sch_setting->mother_phone) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('mother_phone'); ?></label>
                                                    <input id="mother_phone" name="mother_phone" placeholder="" type="text" class="form-control"  value="<?php echo set_value('mother_phone'); ?>" />
                                                    <span class="text-danger"><?php echo form_error('mother_phone'); ?></span>
                                                </div>
                                            </div>
<?php }if ($sch_setting->mother_occupation) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('mother_occupation'); ?></label>
                                                    <input id="mother_occupation" name="mother_occupation" placeholder="" type="text" class="form-control"  value="<?php echo set_value('mother_occupation'); ?>" />
                                                    <span class="text-danger"><?php echo form_error('mother_occupation'); ?></span>
                                                </div>
                                            </div>
<?php }if ($sch_setting->mother_pic) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputFile"><?php echo $this->lang->line('mother_photo'); ?></label>
                                                    <div><input class="filestyle form-control" type='file' name='mother_pic' id="file" size='20' />
                                                    </div>
                                                    <span class="text-danger"><?php echo form_error('file'); ?></span></div>
                                            </div>
<?php }?>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="father_email">Father Email</label>
                                                <input id="father_email" name="father_email" type="email" class="form-control" value="<?php echo set_value('father_email'); ?>" />
                                                <span class="text-danger"><?php echo form_error('father_email'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="mother_email">Mother Email</label>
                                                <input id="mother_email" name="mother_email" type="email" class="form-control" value="<?php echo set_value('mother_email'); ?>" />
                                                <span class="text-danger"><?php echo form_error('mother_email'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
if ($sch_setting->guardian_name) {
        ?>
                                        <div class="row">
                                        <div class="form-group col-md-12">
                                            <label><?php echo $this->lang->line('if_guardian_is'); ?><small class="req"> *</small>&nbsp;&nbsp;&nbsp;</label>
                                            <label class="radio-inline">
                                                <input type="radio" name="guardian_is" <?php
echo set_value('guardian_is') == "father" ? "checked" : "";
        ?>   value="father"> <?php echo $this->lang->line('father'); ?>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="guardian_is" <?php
echo set_value('guardian_is') == "mother" ? "checked" : "";
        ?>   value="mother"> <?php echo $this->lang->line('mother'); ?>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="guardian_is" <?php
echo set_value('guardian_is') == "other" ? "checked" : "";
        ?>   value="other"> <?php echo $this->lang->line('other'); ?>
                                            </label>
                                            <span class="text-danger"><?php echo form_error('guardian_is'); ?></span>
                                        </div>
                                    </div>
                                        <?php
}
    ?>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="row">
                                                 <?php
if ($sch_setting->guardian_name) {
        ?>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('guardian_name'); ?></label><small class="req"> *</small>
                                                        <input id="guardian_name" name="guardian_name" placeholder="" type="text" class="form-control"  value="<?php echo set_value('guardian_name'); ?>" />
                                                        <span class="text-danger"><?php echo form_error('guardian_name'); ?></span>
                                                    </div>
                                                </div>
<?php }if ($sch_setting->guardian_relation) {?>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('guardian_relation'); ?></label>
                                                            <input id="guardian_relation" name="guardian_relation" placeholder="" type="text" class="form-control"  value="<?php echo set_value('guardian_relation'); ?>" />
                                                            <span class="text-danger"><?php echo form_error('guardian_relation'); ?></span>
                                                        </div>
                                                    </div>
                                    <?php }?>
                                            </div>
                                            <div class="row">
                                                <?php
if ($sch_setting->guardian_phone) {
        ?>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('guardian_phone'); ?></label><small class="req"> *</small>
                                                        <input id="guardian_phone" name="guardian_phone" placeholder="" type="text" class="form-control"  value="<?php echo set_value('guardian_phone'); ?>" />
                                                        <span class="text-danger"><?php echo form_error('guardian_phone'); ?></span>
                                                    </div>
                                                </div>
                                            <?php }
    if ($sch_setting->guardian_occupation) {
        ?>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('guardian_occupation'); ?></label>
                                                        <input id="guardian_occupation" name="guardian_occupation" placeholder="" type="text" class="form-control"  value="<?php echo set_value('guardian_occupation'); ?>" />
                                                        <span class="text-danger"><?php echo form_error('guardian_occupation'); ?></span>
                                                    </div>
                                                </div>
                                            <?php }?>
                                            </div>
                                        </div>
                                    <?php if ($sch_setting->guardian_email) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('guardian_email'); ?></label>
                                                    <input id="guardian_email" name="guardian_email" placeholder="" type="text" class="form-control"  value="<?php echo set_value('guardian_email'); ?>" />
                                                    <span class="text-danger"><?php echo form_error('guardian_email'); ?></span>
                                                </div>
                                            </div>
<?php }if ($sch_setting->guardian_pic) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputFile"><?php echo $this->lang->line('guardian_photo'); ?></label>
                                                    <div><input class="filestyle form-control" type='file' name='guardian_pic' id="file" size='20' />
                                                    </div>
                                                    <span class="text-danger"><?php echo form_error('file'); ?></span></div>
                                            </div>
<?php }if ($sch_setting->guardian_address) {?>
                                            <div class="col-md-6">
                                                <label for="exampleInputEmail1"><?php echo $this->lang->line('guardian_address'); ?></label>
                                                <textarea id="guardian_address" name="guardian_address" placeholder="" class="form-control" rows="2"><?php echo set_value('guardian_address'); ?></textarea>
                                                <span class="text-danger"><?php echo form_error('guardian_address'); ?></span>
                                            </div>
<?php }?>
                                    </div>
                                </div>
                            </div>
<?php }?>
<?php
    // Keep this expanded: it contains required HEMIS fields (ward, ethnicity, UGC address helpers).
    $expand_more_details = true;
?>
                            <div class="box-group">
                                <div class="panel box border0 mb0">
                                    <div class="addmoredetail-title">
                                        <a data-widget="collapse" data-original-title="Collapse" class="btn boxplus">
                                            <i class="fa fa-fw fa-minus"></i><?php echo $this->lang->line('add_more_details'); ?>
                                        </a>
                                    </div>
                                    <div class="box-body">
                                        <div class="mb25 bozero">
                                            <h4 class="pagetitleh2">Contact details</h4>
                                            <div class="ugc-address-post-hiddens" style="display:none;" aria-hidden="true">
                                                <input type="hidden" name="ugc_t_province_name" value="<?php echo set_value('ugc_t_province'); ?>">
                                                <input type="hidden" name="ugc_t_district_name" value="<?php echo set_value('ugc_t_district'); ?>">
                                                <input type="hidden" name="ugc_t_local_level_name" value="<?php echo set_value('ugc_t_local_level'); ?>">
                                                <input type="hidden" name="ugc_t_combined_code" value="<?php echo set_value('ugc_t_combined_code'); ?>">
                                                <input type="hidden" name="ugc_p_province_name" value="<?php echo set_value('ugc_p_province'); ?>">
                                                <input type="hidden" name="ugc_p_district_name" value="<?php echo set_value('ugc_p_district'); ?>">
                                                <input type="hidden" name="ugc_p_local_level_name" value="<?php echo set_value('ugc_p_local_level'); ?>">
                                                <input type="hidden" name="ugc_p_combined_code" value="<?php echo set_value('ugc_p_combined_code'); ?>">
                                            </div>

                                            <div class="row around10">


<?php if ($sch_setting->current_address) {?>
                                                    <div class="col-md-12 row">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input type="checkbox" id="autofill_current_address" onclick="return auto_fill_guardian_address();">
    <?php echo $this->lang->line('if_guardian_address_is_current_address'); ?>
                                                            </label>
                                                        </div>

                                                        
                                                        <div class="legacy-address-cascade" style="display:none;" aria-hidden="true">
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="temporary_province"><?php echo $this->lang->line('temporary_province'); ?> <span class="label label-default">legacy</span></label>
                                                                <select id="temporary_province" name="temporary_province" class="form-control">
                                                                    <option value=""><?php echo $this->lang->line('select_province'); ?></option>
                                                                    <?php foreach ($provinces as $province) { ?>
                                                                        <option value="<?php echo $province['id']; ?>" <?php echo (set_value('temporary_province') == $province['id']) ? 'selected' : ''; ?>><?php echo $province['name']; ?></option>
                                                                    <?php } ?>
                                                                </select>
                                                                <span class="text-danger"><?php echo form_error('temporary_province'); ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="temporary_district"><?php echo $this->lang->line('temporary_district'); ?></label>
                                                                <select id="temporary_district" name="temporary_district" class="form-control">
                                                                    <option value=""><?php echo $this->lang->line('select_district'); ?></option>
                                                                    <?php if(isset($districts) && !empty($districts)): ?>
                                                                        <?php foreach($districts as $district): ?>
                                                                            <option value="<?php echo $district['id']; ?>" <?php echo (set_value('temporary_district') == $district['id']) ? 'selected' : ''; ?>><?php echo $district['name']; ?></option>
                                                                        <?php endforeach; ?>
                                                                    <?php endif; ?>
                                                                </select>
                                                                <span class="text-danger"><?php echo form_error('temporary_district'); ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="temporary_local_level"><?php echo $this->lang->line('temporary_local_level'); ?></label>
                                                                <select id="temporary_local_level" name="temporary_local_level" class="form-control">
                                                                    <option value=""><?php echo $this->lang->line('select_local_level'); ?></option>
                                                                    <?php if(isset($local_levels) && !empty($local_levels)): ?>
                                                                        <?php foreach($local_levels as $local_level): ?>
                                                                            <option value="<?php echo $local_level['id']; ?>" <?php echo (set_value('temporary_local_level') == $local_level['id']) ? 'selected' : ''; ?>><?php echo $local_level['name']; ?></option>
                                                                        <?php endforeach; ?>
                                                                    <?php endif; ?>
                                                                </select>
                                                                <span class="text-danger"><?php echo form_error('temporary_local_level'); ?></span>
                                                            </div>
                                                        </div>
                                                        </div>
                                                        <div class="col-md-3 ugc-address-cascade-col">
                                                            <div class="form-group">
                                                                <label for="ugc_t_province"><?php echo $this->lang->line('temporary_province'); ?> <span class="label label-info">HEMIS</span></label>
                                                                <small class="text-muted" style="display:block;margin-bottom:4px;">Official UGC address list</small>
                                                                <select id="ugc_t_province" class="form-control" title="HEMIS / UGC province"></select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 ugc-address-cascade-col">
                                                            <div class="form-group">
                                                                <label for="ugc_t_district"><?php echo $this->lang->line('temporary_district'); ?> <span class="label label-info">HEMIS</span></label>
                                                                <small class="text-muted" style="display:block;margin-bottom:4px;">&nbsp;</small>
                                                                <select id="ugc_t_district" class="form-control"></select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 ugc-address-cascade-col">
                                                            <div class="form-group">
                                                                <label for="ugc_t_local_level"><?php echo $this->lang->line('temporary_local_level'); ?> <span class="label label-info">HEMIS</span></label>
                                                                <small class="text-muted" style="display:block;margin-bottom:4px;">Local level / municipality</small>
                                                                <select id="ugc_t_local_level" class="form-control"></select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="temporary_ward_no"><?php echo $this->lang->line('temporary_ward_no'); ?></label>
                                                                <select id="temporary_ward_no" name="temporary_ward_no" class="form-control">
                                                                    <option value=""><?php echo $this->lang->line('select_ward'); ?></option>
                                                                    <?php for ($i = 1; $i <= 32; $i++): ?>
                                                                        <option value="<?php echo $i; ?>" <?php echo (set_value('temporary_ward_no') == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                                                    <?php endfor; ?>
                                                                </select>
                                                                <span class="text-danger"><?php echo form_error('temporary_ward_no'); ?></span>
                                                            </div>
                                                        </div>
                                                    
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('current_address'); ?></label>
                                                            <textarea id="current_address" name="current_address" placeholder=""  class="form-control" ><?php echo set_value('current_address'); ?></textarea>
                                                            <span class="text-danger"><?php echo form_error('current_address'); ?></span>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('temporary_house_no'); ?></label>
                                                            <input id="temporary_house_no" name="temporary_house_no" placeholder="" type="text" class="form-control"  value="<?php echo set_value('temporary_house_no'); ?>" />
                                                            <span class="text-danger"><?php echo form_error('temporary_house_no'); ?></span>
                                                        </div>
                                                    </div>


                                                    </div>

                                                   
                                                    
<?php }if ($sch_setting->permanent_address) {?>
                                                    <div class="col-md-12">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input type="checkbox" id="autofill_address"onclick="return auto_fill_address();">
    <?php echo $this->lang->line('if_temporary_address_is_current_address'); ?>
                                                            </label>
                                                        </div>


                                                        <div class="legacy-address-cascade" style="display:none;" aria-hidden="true">
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="permanent_province"><?php echo $this->lang->line('permanent_province'); ?> <span class="label label-default">legacy</span></label>
                                                                <select id="permanent_province" name="permanent_province" class="form-control">
                                                                    <option value=""><?php echo $this->lang->line('select_province'); ?></option>
                                                                    <?php foreach ($provinces as $province) { ?>
                                                                        <option value="<?php echo $province['id']; ?>" <?php echo (set_value('permanent_province') == $province['id']) ? 'selected' : ''; ?>><?php echo $province['name']; ?></option>
                                                                    <?php } ?>
                                                                </select>
                                                                <span class="text-danger"><?php echo form_error('permanent_province'); ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="permanent_district"><?php echo $this->lang->line('permanent_district'); ?></label>
                                                                <select id="permanent_district" name="permanent_district" class="form-control">
                                                                    <option value=""><?php echo $this->lang->line('select_district'); ?></option>
                                                                    <?php if(isset($permanent_districts) && !empty($permanent_districts)): ?>
                                                                        <?php foreach($permanent_districts as $district): ?>
                                                                            <option value="<?php echo $district['id']; ?>" <?php echo (set_value('permanent_district') == $district['id']) ? 'selected' : ''; ?>><?php echo $district['name']; ?></option>
                                                                        <?php endforeach; ?>
                                                                    <?php endif; ?>
                                                                </select>
                                                                <span class="text-danger"><?php echo form_error('permanent_district'); ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="permanent_local_level"><?php echo $this->lang->line('permanent_local_level'); ?></label>
                                                                <select id="permanent_local_level" name="permanent_local_level" class="form-control">
                                                                    <option value=""><?php echo $this->lang->line('select_local_level'); ?></option>
                                                                    <?php if(isset($permanent_local_levels) && !empty($permanent_local_levels)): ?>
                                                                        <?php foreach($permanent_local_levels as $local_level): ?>
                                                                            <option value="<?php echo $local_level['id']; ?>" <?php echo (set_value('permanent_local_level') == $local_level['id']) ? 'selected' : ''; ?>><?php echo $local_level['name']; ?></option>
                                                                        <?php endforeach; ?>
                                                                    <?php endif; ?>
                                                                </select>
                                                                <span class="text-danger"><?php echo form_error('permanent_local_level'); ?></span>
                                                            </div>
                                                        </div>
                                                        </div>
                                                        <div class="col-md-3 ugc-address-cascade-col">
                                                            <div class="form-group">
                                                                <label for="ugc_p_province"><?php echo $this->lang->line('permanent_province'); ?> <span class="label label-info">HEMIS</span></label>
                                                                <small class="text-muted" style="display:block;margin-bottom:4px;">Official UGC address list</small>
                                                                <select id="ugc_p_province" class="form-control" title="HEMIS / UGC province"></select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 ugc-address-cascade-col">
                                                            <div class="form-group">
                                                                <label for="ugc_p_district"><?php echo $this->lang->line('permanent_district'); ?> <span class="label label-info">HEMIS</span></label>
                                                                <small class="text-muted" style="display:block;margin-bottom:4px;">&nbsp;</small>
                                                                <select id="ugc_p_district" class="form-control"></select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 ugc-address-cascade-col">
                                                            <div class="form-group">
                                                                <label for="ugc_p_local_level"><?php echo $this->lang->line('permanent_local_level'); ?> <span class="label label-info">HEMIS</span></label>
                                                                <small class="text-muted" style="display:block;margin-bottom:4px;">Local level / municipality</small>
                                                                <select id="ugc_p_local_level" class="form-control"></select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="permanent_ward_no"><?php echo $this->lang->line('permanent_ward_no'); ?></label>
                                                                <select id="permanent_ward_no" name="permanent_ward_no" class="form-control">
                                                                    <option value=""><?php echo $this->lang->line('select_ward'); ?></option>
                                                                    <?php for ($i = 1; $i <= 32; $i++): ?>
                                                                        <option value="<?php echo $i; ?>" <?php echo (set_value('permanent_ward_no') == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                                                    <?php endfor; ?>
                                                                </select>
                                                                <span class="text-danger"><?php echo form_error('permanent_ward_no'); ?></span>
                                                            </div>
                                                        </div>

                                                    
                                                    <div class="col-md-4">
                                                    <div class="form-group">
                                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('permanent_address'); ?></label>
                                                            <textarea id="permanent_address" name="permanent_address" placeholder="" class="form-control"><?php echo set_value('permanent_address'); ?></textarea>
                                                            <span class="text-danger"><?php echo form_error('permanent_address'); ?></span>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('permanent_house_no'); ?></label>
                                                            <input id="permanent_house_no" name="permanent_house_no" placeholder="" type="text" class="form-control"  value="<?php echo set_value('permanent_house_no'); ?>" />
                                                            <span class="text-danger"><?php echo form_error('permanent_house_no'); ?></span>
                                                        </div>
                                                    </div>
                                                    <!-- Hidden fields to store old values -->
                                                    <input type="hidden" id="old_temporary_district" value="<?php echo set_value('temporary_district'); ?>">
                                                    <input type="hidden" id="old_temporary_local_level" value="<?php echo set_value('temporary_local_level'); ?>">
                                                    <input type="hidden" id="old_permanent_district" value="<?php echo set_value('permanent_district'); ?>">
                                                    <input type="hidden" id="old_permanent_local_level" value="<?php echo set_value('permanent_local_level'); ?>">
                                                                                                        
                                                    </div>
<?php }?>
                                            </div>
                                        </div>

                                        <div class="tshadow mb25 bozero">
                                            <h4 class="pagetitleh2"><?php echo $this->lang->line('miscellaneous_details'); ?>
                                            </h4>
                                                <div class="row around10">
                                                    <?php if ($sch_setting->bank_account_no) {?>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('bank_account_number'); ?></label>
                                                            <input id="bank_account_no" name="bank_account_no" placeholder="" type="text" class="form-control"  value="<?php echo set_value('bank_account_no'); ?>" />
                                                            <span class="text-danger"><?php echo form_error('bank_account_no'); ?></span>
                                                        </div>
                                                    </div><?php }if ($sch_setting->bank_name) {?>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('bank_name'); ?></label>
                                                            <input id="bank_name" name="bank_name" placeholder="" type="text" class="form-control"  value="<?php echo set_value('bank_name'); ?>" />
                                                            <span class="text-danger"><?php echo form_error('bank_name'); ?></span>
                                                        </div>
                                                    </div>
                                                    <?php }if ($sch_setting->ifsc_code) {?>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('ifsc_code'); ?></label>
                                                            <input id="ifsc_code" name="ifsc_code" placeholder="" type="text" class="form-control"  value="<?php echo set_value('ifsc_code'); ?>" />
                                                            <span class="text-danger"><?php echo form_error('ifsc_code'); ?></span>
                                                        </div>
                                                    </div>
                                                <?php }?>
                                                </div>
                                            <div class="row around10">
<?php if ($sch_setting->local_identification_no) {?>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail1">
    <?php echo $this->lang->line('local_identification_number'); ?>
                                                            </label>
                                                            <input id="samagra_id" name="samagra_id" placeholder="" type="text" class="form-control"  value="<?php echo set_value('samagra_id'); ?>" />
                                                            <span class="text-danger"><?php echo form_error('samagra_id'); ?></span>
                                                        </div>
                                                    </div>
<?php } ?>
<?php if ($sch_setting->rte) {
    ?>
                                                    <div class="col-md-4">
                                                        <label><?php echo $this->lang->line('rte'); ?></label>
                                                        <div class="radio" style="margin-top: 2px;">
                                                            <label><input class="radio-inline" type="radio" name="rte" value="Yes"  <?php
echo set_value('rte') == "yes" ? "checked" : "";
    ?>  ><?php echo $this->lang->line('yes'); ?></label>
                                                            <label><input class="radio-inline" checked="checked" type="radio" name="rte" value="No" <?php
echo set_value('rte') == "no" ? "checked" : "";
    ?>  ><?php echo $this->lang->line('no'); ?></label>
                                                        </div>
                                                        <span class="text-danger"><?php echo form_error('rte'); ?></span>
                                                    </div>
<?php } ?>
<?php if ($sch_setting->is_blood_group) { ?>
                                                    <div class="col-md-3 col-xs-12">
                                                        <div class="form-group">
                                                            <label><?php echo $this->lang->line('blood_group'); ?></label>
                                                            <select class="form-control" name="blood_group">
                                                                <option value=""><?php echo $this->lang->line('select') ?></option>
                                                                <?php foreach ($bloodgroup as $bgkey => $bgvalue) { ?>
                                                                    <option value="<?php echo $bgvalue ?>"><?php echo $bgvalue ?></option>
                                                                <?php } ?>
                                                            </select>
                                                            <span class="text-danger"><?php echo form_error('blood_group'); ?></span>
                                                        </div>
                                                    </div>
<?php } ?>
<?php if ($sch_setting->is_student_house) { ?>
                                                    <div class="col-md-3 col-xs-12">
                                                        <div class="form-group">
                                                            <label><?php echo $this->lang->line('house') ?></label>
                                                            <select class="form-control" name="house">
                                                                <option value=""><?php echo $this->lang->line('select') ?></option>
                                                                <?php foreach ($houses as $hkey => $hvalue) { ?>
                                                                    <option value="<?php echo $hvalue["id"] ?>"><?php echo $hvalue["house_name"] ?></option>
                                                                <?php } ?>
                                                            </select>
                                                            <span class="text-danger"><?php echo form_error('house'); ?></span>
                                                        </div>
                                                    </div>
<?php } ?>
<?php if ($sch_setting->previous_school_details) {?>

                                            </div>


                                            <div id="previous-study-container">
                                                <div class="previous-study-group" data-index="0">
                                                    <div class="row around10">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="previous_level_of_study"><?php echo $this->lang->line('previous_level_of_study'); ?></label>
                                                                <select id="previous_level_of_study" name="previous_level_of_study[]" class="form-control">
                                                                    <option value="">Select Level</option>
                                                                    <option value="SEE/SLC" <?php echo set_select('previous_level_of_study[]', 'SEE/SLC'); ?>>SEE/SLC</option>
                                                                    <option value="+2" <?php echo set_select('previous_level_of_study[]', '+2'); ?>>+2</option>
                                                                    <option value="Bachelors" <?php echo set_select('previous_level_of_study[]', 'Bachelors'); ?>>Bachelors</option>
                                                                    <option value="Masters" <?php echo set_select('previous_level_of_study[]', 'Masters'); ?>>Masters</option>
                                                                    <option value="CTEVT" <?php echo set_select('previous_level_of_study[]', 'CTEVT'); ?>>CTEVT</option>
                                                                </select>
                                                                <span class="text-danger"><?php echo form_error('previous_level_of_study'); ?></span>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="previous_board_university_college"><?php echo $this->lang->line('previous_board_university_college'); ?></label>
                                                                <select id="previous_board_university_college" name="previous_board_university_college[]" class="form-control">
                                                                    <option value="">Select Board/University/College</option>
                                                                    <option value="Tribhuvan University(TU)" <?php echo set_select('previous_board_university_college[]', 'Tribhuvan University(TU)'); ?>>Tribhuvan University (TU)</option>
                                                                    <option value="Pokhara University(PU)" <?php echo set_select('previous_board_university_college[]', 'Pokhara University(PU)'); ?>>Pokhara University (PU)</option>
                                                                    <option value="Purbanchal University(PU)" <?php echo set_select('previous_board_university_college[]', 'Purbanchal University(PU)'); ?>>Purbanchal University (PU)</option>
                                                                    <option value="Kathmandu University(KU)" <?php echo set_select('previous_board_university_college[]', 'Kathmandu University(KU)'); ?>>Kathmandu University (KU)</option>
                                                                    <option value="Nepal Sankrit University" <?php echo set_select('previous_board_university_college[]', 'Nepal Sankrit University'); ?>>Nepal Sankrit University</option>
                                                                </select>
                                                                <span class="text-danger"><?php echo form_error('previous_board_university_college'); ?></span>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="previous_registration_no"><?php echo $this->lang->line('previous_registration_no'); ?></label>
                                                                <input id="previous_registration_no" name="previous_registration_no[]" placeholder="" type="text" class="form-control" value="<?php 
                                                                    $prev_reg_no = set_value('previous_registration_no');
                                                                    echo is_array($prev_reg_no) ? (isset($prev_reg_no[0]) ? $prev_reg_no[0] : '') : $prev_reg_no;
                                                                ?>" />
                                                                <span class="text-danger"><?php echo form_error('previous_registration_no'); ?></span>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <div class="form-group">
                                                                <label for="previous_institution_name"><?php echo $this->lang->line('previous_school_details'); ?></label>
                                                                <textarea class="form-control" rows="3" placeholder="" name="previous_institution_name[]" id="previous_institution_name"></textarea>
                                                                <span class="text-danger"><?php echo form_error('previous_institution_name'); ?></span>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-md-1">
                                                            <div class="form-group">
                                                                <label>&nbsp;</label>
                                                                <button type="button" class="btn btn-success btn-sm add-previous-study" style="display: block; margin-top: 5px;">
                                                                    <i class="fa fa-plus"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php }if ($sch_setting->student_note) {?>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('note'); ?></label>
                                                    <textarea class="form-control" rows="3" placeholder="" name="note"></textarea>
                                                    <span class="text-danger"><?php echo form_error('note'); ?></span>
                                                </div>
                                            </div>
                                        <?php }?>
                                    
                                        </div>
            <div id='upload_documents_hide_show'>
    <?php if ($sch_setting->upload_documents) {?>
    <div class="row">
        <div class="col-md-12">
            <div class="tshadow bozero">
                <h4 class="pagetitleh2">
                    <?php echo $this->lang->line('upload_documents'); ?>
                    <button type="button" class="btn btn-primary btn-sm pull-right" id="add_document_btn">
                        <i class="fa fa-plus"></i> Add Document
                    </button>
                </h4>
                <div class="row around10">
                    <div class="col-md-6">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th><?php echo $this->lang->line('title'); ?></th>
                                    <th><?php echo $this->lang->line('documents'); ?></th>
                                </tr>
                                <tr>
                                    <td>1.</td>
                                    <td>
                                        <input type="text" name='first_title' class="form-control" 
                                            value="<?php echo set_value('first_title'); ?>" placeholder="">
                                    </td>
                                    <td>
                                        <input class="filestyle form-control" type='file' name='first_doc' id="doc1">
                                        <?php if(isset($_FILES['first_doc']['name']) && !empty($_FILES['first_doc']['name'])){ ?>
                                            <span class="text-info"><?php echo $_FILES['first_doc']['name']; ?></span>
                                        <?php } ?>
                                        <span class="text-danger"><?php echo form_error('first_doc'); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2.</td>
                                    <td>
                                        <input type="text" name='second_title' class="form-control" 
                                            value="<?php echo set_value('second_title'); ?>" placeholder="">
                                    </td>
                                    <td>
                                        <input class="filestyle form-control" type='file' name='second_doc' id="doc2">
                                        <?php if(isset($_FILES['second_doc']['name']) && !empty($_FILES['second_doc']['name'])){ ?>
                                            <span class="text-info"><?php echo $_FILES['second_doc']['name']; ?></span>
                                        <?php } ?>
                                        <span class="text-danger"><?php echo form_error('second_doc'); ?></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th><?php echo $this->lang->line('title'); ?></th>
                                    <th><?php echo $this->lang->line('documents'); ?></th>
                                </tr>
                                <tr>
                                    <td>3.</td>
                                    <td>
                                        <input type="text" name='fourth_title' class="form-control" 
                                            value="<?php echo set_value('fourth_title'); ?>" placeholder="">
                                    </td>
                                    <td>
                                        <input class="filestyle form-control" type='file' name='fourth_doc' id="doc3">
                                        <?php if(isset($_FILES['fourth_doc']['name']) && !empty($_FILES['fourth_doc']['name'])){ ?>
                                            <span class="text-info"><?php echo $_FILES['fourth_doc']['name']; ?></span>
                                        <?php } ?>
                                        <span class="text-danger"><?php echo form_error('fourth_doc'); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>4.</td>
                                    <td>
                                        <input type="text" name='fifth_title' class="form-control" 
                                            value="<?php echo set_value('fifth_title'); ?>" placeholder="">
                                    </td>
                                    <td>
                                        <input class="filestyle form-control" type='file' name='fifth_doc' id="doc4">
                                        <?php if(isset($_FILES['fifth_doc']['name']) && !empty($_FILES['fifth_doc']['name'])){ ?>
                                            <span class="text-info"><?php echo $_FILES['fifth_doc']['name']; ?></span>
                                        <?php } ?>
                                        <span class="text-danger"><?php echo form_error('fifth_doc'); ?></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Additional Dynamic Documents Section -->
                    <div class="col-md-12" id="additional_documents_section" style="display: none;">
                        <hr>
                        <h5><?php echo $this->lang->line('additional_documents'); ?></h5>
                        <table class="table" id="additional_documents_table">
                            <tbody>
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th><?php echo $this->lang->line('title'); ?></th>
                                    <th><?php echo $this->lang->line('documents'); ?></th>
                                    <th style="width: 50px">Action</th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php }?>
    <div class="row">
        <div class="col-md-12">
            <div class="tshadow bozero" style="margin-top:10px;">
                <h5 class="pagetitleh2" style="font-size:14px;margin-top:0;">HEMIS / registration documents <span class="label label-info">HEMIS</span></h5>
                <p class="text-muted small">Optional scans for central sync. Student profile photo stays under Basic details.</p>
                <div class="row around10">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="pp_size_photo">PP size photo</label>
                            <input id="pp_size_photo" name="pp_size_photo" type="file" class="form-control filestyle" accept=".jpg,.jpeg,.png,.webp,.pdf" />
                            <span class="text-danger"><?php echo form_error('pp_size_photo'); ?></span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="citizenship_front">Citizenship front</label>
                            <input id="citizenship_front" name="citizenship_front" type="file" class="form-control filestyle" accept=".jpg,.jpeg,.png,.webp,.pdf" />
                            <span class="text-danger"><?php echo form_error('citizenship_front'); ?></span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="citizenship_back">Citizenship back</label>
                            <input id="citizenship_back" name="citizenship_back" type="file" class="form-control filestyle" accept=".jpg,.jpeg,.png,.webp,.pdf" />
                            <span class="text-danger"><?php echo form_error('citizenship_back'); ?></span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="nid_pic">NID pic</label>
                            <input id="nid_pic" name="nid_pic" type="file" class="form-control filestyle" accept=".jpg,.jpeg,.png,.webp,.pdf" />
                            <span class="text-danger"><?php echo form_error('nid_pic'); ?></span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="digital_signature">Digital signature</label>
                            <input id="digital_signature" name="digital_signature" type="file" class="form-control filestyle" accept=".jpg,.jpeg,.png,.webp,.pdf" />
                            <span class="text-danger"><?php echo form_error('digital_signature'); ?></span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="receipt_image">Receipt image</label>
                            <input id="receipt_image" name="receipt_image" type="file" class="form-control filestyle" accept=".jpg,.jpeg,.png,.webp,.pdf" />
                            <span class="text-danger"><?php echo form_error('receipt_image'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

 <!-- Student Details Section (entrance / shift / flags — fee amounts are under Fee details) -->
<div class="tshadow mb25 bozero">
    <h4 class="pagetitleh2"><?php echo $this->lang->line('student_details'); ?></h4>
    <div class="row around10">
        <div class="col-md-3">
            <div class="form-group">
                <label for="entrance_score">Entrance Score</label>
                <input id="entrance_score" name="entrance_score" type="text" class="form-control" value="<?php echo set_value('entrance_score'); ?>" />
                <span class="text-danger"><?php echo form_error('entrance_score'); ?></span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="entrance_symbol">Entrance Symbol</label>
                <input id="entrance_symbol" name="entrance_symbol" type="text" class="form-control" value="<?php echo set_value('entrance_symbol'); ?>" />
                <span class="text-danger"><?php echo form_error('entrance_symbol'); ?></span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="medium">Medium</label>
                <input id="medium" name="medium" type="text" class="form-control" value="<?php echo set_value('medium'); ?>" />
                <span class="text-danger"><?php echo form_error('medium'); ?></span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="shift">Shift</label>
                <input id="shift" name="shift" type="text" class="form-control" value="<?php echo set_value('shift'); ?>" />
                <span class="text-danger"><?php echo form_error('shift'); ?></span>
            </div>
        </div>
    </div>
    <div class="row around10">
        <div class="col-md-3">
            <div class="form-group">
                <label for="type">Type</label>
                <input id="type" name="type" type="text" class="form-control" value="<?php echo set_value('type'); ?>" />
                <span class="text-danger"><?php echo form_error('type'); ?></span>
            </div>
        </div>
    </div>
    <div class="row around10">
        <div class="col-md-3">
            <div class="checkbox">
                <label><input type="checkbox" name="is_verified" value="1" <?php echo set_checkbox('is_verified', '1'); ?>> Is Verified</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="checkbox">
                <label><input type="checkbox" name="has_scolorship" value="1" <?php echo set_checkbox('has_scolorship', '1'); ?>> Has Scholarship</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="checkbox">
                <label><input type="checkbox" name="is_mukta_kamaiya" value="1" <?php echo set_checkbox('is_mukta_kamaiya', '1'); ?>> Is Mukta Kamaiya</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="checkbox">
                <label><input type="checkbox" name="is_from_martyr_family" value="1" <?php echo set_checkbox('is_from_martyr_family', '1'); ?>> Is From Martyr Family</label>
            </div>
        </div>
    </div>
    <div class="row around10">
        <?php echo display_custom_fields('students'); ?>
    </div>
</div>

<!-- End Student Details Section -->


                                    </div>
                                </div>
                            </div>
                            <div class="box-footer">
                                <button type="submit" class="btn btn-info pull-right" id="addloader"><?php echo $this->lang->line('save'); ?></button>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
</div>

<div class="modal fade" id="mySiblingModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title title modal_title"></h4>
            </div>
            <div class="modal-body pb0">
                <div class="form-horizontal">
                    <div class="box-body pt0 pb0">
                        <input  type="hidden" class="form-control" id="transport_student_session_id"  value="0" readonly="readonly"/>
                        <div class="form-group">
                            <div class="sibling_msg">
                        </div>
                            <label for="inputEmail3" class="col-sm-2 control-label"><?php echo $this->lang->line('class'); ?></label>
                            <div class="col-sm-10">
                                <select  id="sibiling_class_id" name="sibiling_class_id" class="form-control"  >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <?php
                                        foreach ($classlist as $class) {
                                            ?>
                                            <option value="<?php echo $class['id'] ?>"<?php
                                        if (set_value('sibiling_class_id') == $class['id']) {
                                                echo "selected=selected";
                                            }
                                            ?>><?php echo $class['class'] ?></option>
                                        <?php
                                        $count++;
                                        }
                                        ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-2 control-label"><?php echo $this->lang->line('section'); ?></label>
                            <div class="col-sm-10">
                                <select  id="sibiling_section_id" name="sibiling_section_id" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="text-danger" id="transport_amount_error"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-2 control-label"><?php echo $this->lang->line('student'); ?>
                            </label>
                            <div class="col-sm-10">
                                <select  id="sibiling_student_id" name="sibiling_student_id" class="form-control" >
                                    <option value=""   ><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="text-danger" id="sibiling_student_id"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary add_sibling" id="load" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Processing"><i class="fa fa-user"></i> <?php echo $this->lang->line('add'); ?></button>
            </div>
        </div>
    </div>
</div>


<script>
$(function () {
    var $form = $('#form1');
    var $submitBtn = $('#addloader');
    var defaultBtnHtml = $submitBtn.html() || '<?php echo $this->lang->line('save'); ?>';
    var $alertBox = $('#form-alert');
    var buttonResetTimeout = null;

    function toggleButton(isLoading) {
        // Clear any existing timeout
        if (buttonResetTimeout) {
            clearTimeout(buttonResetTimeout);
            buttonResetTimeout = null;
        }
        
        if (isLoading) {
            $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin fa-1x fa-fw"></i><?php echo $this->lang->line('loading'); ?>');
            
            // Safety timeout - reset button after 2 minutes if no response
            buttonResetTimeout = setTimeout(function() {
                console.warn('Button reset timeout triggered - forcing button reset');
                $submitBtn.prop('disabled', false).html(defaultBtnHtml);
                showAlert('warning', 'Request is taking longer than expected. Please check if the student was created.');
            }, 120000);
        } else {
            $submitBtn.prop('disabled', false).html(defaultBtnHtml);
        }
    }

    function renderErrors(errors) {
        if (!errors) {
            return '';
        }
        var list = '<ul class="mb0">';
        $.each(errors, function (field, message) {
            if (message) {
                list += '<li>' + message + '</li>';
            }
        });
        list += '</ul>';
        return list;
    }

    function showAlert(type, message) {
        $alertBox.html('<div class="alert alert-' + type + '">' + message + '</div>');
    }

    $form.on('submit', function (e) {
        e.preventDefault();
        e.stopPropagation();
        
        var formData = new FormData(this);
        toggleButton(true);
        $alertBox.empty();
        
        console.log('Submitting form via AJAX to:', $form.attr('action'));

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            dataType: 'json',
            timeout: 120000
        }).done(function (response) {
            console.log('AJAX Success - Response:', response);

            if (response && response.success) {
                var successMsg = response.message || '<?php echo $this->lang->line('success_message'); ?>';
                var hemisErr = (response.hemis_sync_error != null && String(response.hemis_sync_error).trim() !== '')
                    ? String(response.hemis_sync_error).trim()
                    : (response.hemis_sync && response.hemis_sync.error ? String(response.hemis_sync.error).trim() : '');
                if (hemisErr !== '') {
                    successMsg += '<div class="alert alert-warning text-left" style="margin-top:10px"><strong>HEMIS / UGC sync:</strong> '
                        + $('<div/>').text(hemisErr).html()
                        + ' The student record was saved locally; see Student Search or students.hemis_sync_error.</div>';
                }
                showAlert('success', successMsg);
                $form[0].reset();
                setTimeout(function() {
                    if ($alertBox.length && $alertBox.offset()) {
                        $('html, body').animate({scrollTop: $alertBox.offset().top - 40}, 400);
                    }
                }, 100);
            } else {
                var message = (response && response.message) ? response.message : '<?php echo $this->lang->line('something_went_wrong'); ?>';
                message += renderErrors(response ? response.errors : null);
                showAlert('danger', message);
            }
        }).fail(function (xhr, status, error) {
            console.error('AJAX Error:', status, error, xhr.status);
            var response = null;
            try {
                if (xhr.responseText && xhr.responseText.trim() !== '') {
                    response = JSON.parse(xhr.responseText);
                }
            } catch (e) {
                // Not JSON
            }

            toggleButton(false);

            var errorMsg = '<?php echo $this->lang->line('something_went_wrong'); ?>';
            if (response && response.message) {
                errorMsg = response.message;
                errorMsg += renderErrors(response.errors || null);
            } else if (status === 'timeout') {
                errorMsg = 'Request timed out. Please check your connection and whether the student was saved before trying again.';
            } else if (xhr.status === 419 || (xhr.responseText && xhr.responseText.indexOf('csrf') !== -1)) {
                errorMsg = 'Security token expired. Reload the page and try again.';
            } else if (xhr.status >= 500) {
                errorMsg = 'Server error occurred. Please try again.';
            } else if (xhr.responseText && (xhr.responseText.indexOf('<!DOCTYPE') !== -1 || xhr.responseText.indexOf('<html') !== -1)) {
                errorMsg = 'Unexpected response from server. Reload the page and try again, or contact support.';
            }
            showAlert('danger', errorMsg);
        }).always(function () {
            toggleButton(false);
        });
        
        return false;
    });
});
</script>

<script type="text/javascript">
    $(document).ready(function () {
    // Optional helper: auto-fill HEMIS admissionYearId from BS admission_year via config map.
    var hemisAdmissionYearMap = <?php
        $hemisCfg = $this->config->item('hemis');
        $map = (is_array($hemisCfg) && isset($hemisCfg['admission_year_id_map']) && is_array($hemisCfg['admission_year_id_map']))
            ? $hemisCfg['admission_year_id_map'] : array();
        echo json_encode($map);
    ?>;
    function tryAutofillAdmissionYearId() {
        var $ay = $('#admission_year');
        var $aid = $('#admissionYearId');
        var $hint = $('#admissionYearId_hint');
        if ($ay.length === 0 || $aid.length === 0) return;
        if ($aid.val()) return;
        var key = ($ay.val() || '').toString();
        if (!key) return;
        if (hemisAdmissionYearMap && hemisAdmissionYearMap[key]) {
            $aid.val(String(hemisAdmissionYearMap[key]));
            if ($hint.length) {
                $hint.text('Auto-filled from admission year ' + key + ' (config map).');
            }
        }
    }
    $('#admission_year').off('change.hemisAid').on('change.hemisAid', tryAutofillAdmissionYearId);
    tryAutofillAdmissionYearId();

    // HEMIS/UGC lists do not depend on local section — load in parallel with sections (avoid waterfall).
    loadUgcPrograms();
    loadUgcBatches();
    loadUgcFiscalYears();

    // Fetch all sections
    $.ajax({
        url: '<?php echo base_url(); ?>sections/getAll',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            var section_id = '<?php echo set_value("section_id") ?>';
            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
            
            $.each(response, function (index, section) {
                var selected = (section_id == section.id) ? 'selected' : '';
                html += '<option value="' + section.id + '" ' + selected + '>' + section.section + '</option>';
            });
            
            $('#section_id').html(html);
            
            // If there's a pre-selected section, trigger program load
            if(section_id) {
                $('#section_id').trigger('change');
            }
        }
    });
});

function loadUgcBatches(preselect) {
    $('#ugc_batch_id').html('<option value="">Loading...</option>');
    $.ajax({
        url: '<?php echo base_url(); ?>student/ugc_batches',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            var selected_id = preselect || '<?php echo set_value("ugc_batch_id") ?>';
            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
            var rows = (response && response.data) ? response.data : [];
            if (rows.length > 0) {
                if (!selected_id) {
                    // Prefer active + latest index, else latest index.
                    var best = null;
                    $.each(rows, function (_, b) {
                        if (!best) {
                            best = b;
                            return;
                        }
                        var bActive = !!b.isActive;
                        var bestActive = !!best.isActive;
                        var bIdx = (b.index != null) ? parseInt(b.index, 10) : -999999;
                        var bestIdx = (best.index != null) ? parseInt(best.index, 10) : -999999;
                        if (bestActive !== bActive) {
                            if (bActive) {
                                best = b;
                            }
                            return;
                        }
                        if (bIdx > bestIdx) {
                            best = b;
                        }
                    });
                    if (best && best.id) {
                        selected_id = String(best.id);
                    }
                }
                $.each(rows, function (index, b) {
                    var label = (b.batchNepali || b.name || '');
                    var sel = (String(selected_id) === String(b.id)) ? 'selected' : '';
                    var dataName = (b.name || '');
                    var dataNep = (b.batchNepali || '');
                    html += '<option value="' + b.id + '" data-name="' + $('<div>').text(dataName).html() + '" data-nepali="' + $('<div>').text(dataNep).html() + '" ' + sel + '>' + label + '</option>';
                });
            } else {
                html += '<option value="" disabled>No UGC batches found</option>';
            }
            $('#ugc_batch_id').html(html);
            syncBatchNamesFromUgc();
            tryAutoselectUgcBatchFromAdmissionYear();
        },
        error: function () {
            $('#ugc_batch_id').html('<option value="">Error loading UGC batches</option>');
        }
    });
}

function syncBatchNamesFromUgc() {
    var $sel = $('#ugc_batch_id');
    if ($sel.length === 0) return;
    var id = $sel.val();
    if (!id) return;

    var $opt = $sel.find('option:selected');
    var nep = ($opt.data('nepali') || '').toString();
    var eng = ($opt.data('name') || '').toString();
    if (!nep) {
        nep = ($opt.text() || '').toString();
    }
    if ($('#batch_name_nepali').length) {
        $('#batch_name_nepali').val(nep);
    }
    if ($('#batch_name').length) {
        $('#batch_name').val(eng);
    }
    // KBMC/HEMIS: UGC Batch catalog is the admission-year ID space — keep admissionYearId aligned with batch.
    if ($('#admissionYearId').length) {
        $('#admissionYearId').val(id);
    }
}

$(document).on('change', '#ugc_batch_id', function () {
    syncBatchNamesFromUgc();
});

function tryAutoselectUgcBatchFromAdmissionYear() {
    var $ay = $('#admission_year');
    var $batch = $('#ugc_batch_id');
    if ($ay.length === 0 || $batch.length === 0) return;

    // Don't override if operator already picked a batch.
    if ($batch.val()) return;

    var ay = ($ay.val() || '').toString();
    if (!ay) return;

    // Match Admission Year (BS) to batchNepali.
    var matches = $batch.find('option[data-nepali]').filter(function () {
        return String($(this).data('nepali')) === ay;
    });
    if (matches.length === 1) {
        $batch.val(matches.first().val());
        syncBatchNamesFromUgc();
    }
}

$('#admission_year').off('change.ugcBatchAuto').on('change.ugcBatchAuto', function () {
    // Wait until UGC batches are loaded; if not yet loaded, this will do nothing.
    tryAutoselectUgcBatchFromAdmissionYear();
});

function loadUgcFiscalYears(preselect) {
    $('#ugc_fiscal_year_id').html('<option value="">Loading...</option>');
    $.ajax({
        url: '<?php echo base_url(); ?>student/ugc_fiscal_years',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            var selected_id = preselect || '<?php echo set_value("ugc_fiscal_year_id") ?>';
            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
            var rows = (response && response.data) ? response.data : [];
            if (rows.length > 0) {
                if (!selected_id) {
                    // Default to active fiscal year when user hasn't chosen one.
                    var active = null;
                    $.each(rows, function (_, fy) {
                        if (fy && fy.activeFiscalYear) {
                            active = fy;
                            return false;
                        }
                    });
                    if (active && active.id) {
                        selected_id = String(active.id);
                    }
                }
                $.each(rows, function (index, fy) {
                    var label = (fy.yearNepali || fy.yearEnglish || '');
                    var sel = (String(selected_id) === String(fy.id)) ? 'selected' : '';
                    html += '<option value="' + fy.id + '" ' + sel + '>' + label + '</option>';
                });
            } else {
                html += '<option value="" disabled>No UGC fiscal years found</option>';
            }
            $('#ugc_fiscal_year_id').html(html);
        },
        error: function () {
            $('#ugc_fiscal_year_id').html('<option value="">Error loading UGC fiscal years</option>');
        }
    });
}

function ugcEscAttr(s) {
    if (s == null || s === undefined) {
        return '';
    }
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;')
        .replace(/'/g, '&#39;');
}

function syncUgcProgramDisplays() {
    var $o = $('#ugc_program_id option:selected');
    if (!$o.length || !$o.val()) {
        $('#ugc_faculty_display').text('—');
        $('#ugc_academic_level_display').text('—');
        return;
    }
    var fac = $o.attr('data-faculty');
    var lev = $o.attr('data-level');
    if ((!fac || fac === '') && (!lev || lev === '')) {
        var txt = ($o.text() || '').trim();
        var sepi = txt.indexOf('—');
        if (sepi !== -1) {
            lev = txt.substring(sepi + 1).trim();
            fac = '';
        }
    }
    $('#ugc_faculty_display').text(fac || '—');
    $('#ugc_academic_level_display').text(lev || '—');
}

function _ugcProgramOptionHtml(program, selected_id) {
    var pid = program.programId != null ? program.programId : program.id;
    var label = (program.programName || program.shortName || program.name || '');
    if (program.levelName) {
        label = label ? (label + ' — ' + program.levelName) : program.levelName;
    }
    var sel = (String(selected_id) === String(pid)) ? 'selected' : '';
    var fac = program.facultyName || '';
    var lev = program.levelName || '';
    var dur = program.duration || '';
    var ptype = program.programType || '';
    return '<option value="' + pid + '" data-faculty="' + ugcEscAttr(fac) + '" data-level="' + ugcEscAttr(lev) + '" data-duration="' + ugcEscAttr(dur) + '" data-program-type="' + ugcEscAttr(ptype) + '" ' + sel + '>' + $('<div/>').text(label).html() + '</option>';
}

var __campusFromUgc = { program_id: '', active: false };

function applyPendingCampusProgram() {
    var want = __campusFromUgc.program_id ? String(__campusFromUgc.program_id) : '';
    if (!want) return;
    var $opt = $('#program_id option[value="' + want.replace(/"/g, '\\"') + '"]');
    if ($opt.length) {
        $('#program_id').val(want);
        __campusFromUgc.program_id = '';
        __campusFromUgc.active = false;
        $('#program_id').trigger('change');
    }
}

function suggestCampusFromUgcProgram() {
    var ugcId = $('#ugc_program_id').val();
    if (!ugcId) return;

    var $o = $('#ugc_program_id option:selected');
    var dur = $o.attr('data-duration') || '';
    if (dur && !$('#academic_program_duration').val()) {
        $('#academic_program_duration').val(dur);
    }

    $.ajax({
        url: '<?php echo base_url(); ?>student/resolve_campus_from_ugc_program',
        method: 'GET',
        dataType: 'json',
        data: { ugc_program_id: ugcId },
        success: function (res) {
            if (!res || res.status != 1 || !res.data) return;
            var d = res.data;
            if (res.hint) {
                $('#ugc_program_hint').text(res.hint);
            }
            if (d.duration && !$('#academic_program_duration').val()) {
                $('#academic_program_duration').val(d.duration);
            }
            if (!d.section_id) return;

            var curSec = String($('#section_id').val() || '');
            var curProg = String($('#program_id').val() || '');
            // Only auto-fill when faculty/program empty, or match is config-backed and user hasn't finished placement.
            var shouldFill = (!curSec || !curProg || String(d.section_id) !== curSec || (d.program_id && String(d.program_id) !== curProg));
            if (!shouldFill) return;

            __campusFromUgc.program_id = d.program_id ? String(d.program_id) : '';
            __campusFromUgc.active = true;
            if (String($('#section_id').val()) === String(d.section_id)) {
                // Programs may already be loaded for this faculty.
                if (typeof loadPrograms === 'function') {
                    loadPrograms(String(d.section_id), __campusFromUgc.program_id);
                } else {
                    $('#section_id').trigger('change');
                }
                setTimeout(applyPendingCampusProgram, 400);
            } else {
                $('#section_id').val(String(d.section_id)).trigger('change');
                setTimeout(applyPendingCampusProgram, 700);
            }
        }
    });
}

function loadUgcPrograms(preselect) {
    $('#ugc_program_id').html('<option value="">Loading...</option>');
    $.ajax({
        // Try cache first to avoid empty live responses blocking admissions.
        url: '<?php echo base_url(); ?>student/ugc_programs',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            var selected_id = preselect || '<?php echo set_value("ugc_program_id") ?>';
            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
            var rows = (response && response.data) ? response.data : [];
            if (response && response.message) {
                $('#ugc_program_hint').text(response.message);
            }
            if (rows.length > 0) {
                $.each(rows, function (index, program) {
                    html += _ugcProgramOptionHtml(program, selected_id);
                });
            } else {
                html += '<option value="" disabled>No UGC programs found</option>';
            }
            $('#ugc_program_id').html(html);
            suggestUgcProgramFromLocal();
            syncUgcProgramDisplays();

            // If cache is empty, try live fetch once.
            if (rows.length === 0) {
                $.ajax({
                    url: '<?php echo base_url(); ?>student/ugc_programs?live=1',
                    method: 'GET',
                    dataType: 'json',
                    success: function (r2) {
                        var rows2 = (r2 && r2.data) ? r2.data : [];
                        if (r2 && r2.message) {
                            $('#ugc_program_hint').text(r2.message);
                        }
                        if (rows2.length > 0) {
                            var html2 = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                            $.each(rows2, function (_, program) {
                                html2 += _ugcProgramOptionHtml(program, selected_id);
                            });
                            $('#ugc_program_id').html(html2);
                            suggestUgcProgramFromLocal();
                            syncUgcProgramDisplays();
                        }
                    }
                });
            }
        },
        error: function () {
            $('#ugc_program_id').html('<option value="">Error loading UGC programs</option>');
            syncUgcProgramDisplays();
        }
    });
}

function _normProgramName(s) {
    s = (s || '').toString().toLowerCase();
    s = s.replace(/[^a-z0-9]+/g, ' ').replace(/\s+/g, ' ').trim();
    return s;
}

function suggestUgcProgramFromLocal() {
    var $local = $('#program_id');
    var $ugc = $('#ugc_program_id');
    var $hint = $('#ugc_program_hint');

    if ($local.length === 0 || $ugc.length === 0) return;

    var localId = $local.val();
    var localText = $local.find('option:selected').text() || '';
    var localNorm = _normProgramName(localText);

    if (!localId || !localNorm) {
        if ($hint.length) $hint.text('');
        return;
    }

    // Don't override if user already selected UGC program.
    if ($ugc.val()) {
        if ($hint.length) $hint.text('');
        return;
    }

    var $ugcOpts = $ugc.find('option').filter(function () {
        var v = $(this).attr('value');
        return v && v !== '';
    });
    if ($ugcOpts.length === 0) return;

    var exact = [];
    var contains = [];
    $ugcOpts.each(function () {
        var $o = $(this);
        var text = $o.text() || '';
        var norm = _normProgramName(text);
        if (!norm) return;

        if (norm === localNorm) {
            exact.push($o);
        } else if (localNorm.length >= 6 && (norm.indexOf(localNorm) !== -1 || localNorm.indexOf(norm) !== -1)) {
            contains.push($o);
        }
    });

    var pick = null;
    if (exact.length === 1) {
        pick = exact[0];
    } else if (exact.length === 0 && contains.length === 1) {
        pick = contains[0];
    }

    if (pick) {
        $ugc.val(pick.val());
        if ($hint.length) {
            $hint.text('Auto-selected from local Program: ' + localText);
        }
        if (typeof syncUgcProgramDisplays === 'function') {
            syncUgcProgramDisplays();
        }
    } else {
        if ($hint.length) $hint.text('');
    }
}

// When section changes, fetch programs (local) + UGC programs (central)
$(document).on('change', '#section_id', function (e) {
    var section_id = $(this).val();
    
    // Clear program and class dropdowns
    $('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
    $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
    
    if (section_id != '') {
        // Show loading indicator
        $('#program_id').html('<option value="">Loading...</option>');
        
        // Fetch programs for this section (local)
        $.ajax({
            url: '<?php echo base_url(); ?>programs/getBySection', 
            method: 'POST',
            data: {section_id: section_id},
            dataType: 'json',
            success: function (response) {
                var program_id = '<?php echo set_value("program_id") ?>';
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                
                if(response.length > 0) {
                    $.each(response, function (index, program) {
                        var selected = (program_id == program.id) ? 'selected' : '';
                        html += '<option value="' + program.id + '" ' + selected + '>' + program.title + '</option>';
                    });
                } else {
                    html += '<option value="" disabled>No programs found</option>';
                }
                
                $('#program_id').html(html);
                suggestUgcProgramFromLocal();
                applyPendingCampusProgram();
                
                // If there's a pre-selected program, trigger class load
                if(program_id) {
                    $('#program_id').trigger('change');
                } else if (__campusFromUgc.program_id) {
                    applyPendingCampusProgram();
                }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching programs:", error);
                console.error("Response:", xhr.responseText);
                $('#program_id').html('<option value="">Error loading programs</option>');
            }
        });
    }
});

// When program changes, fetch classes
$(document).on('change', '#program_id', function (e) {
    var section_id = $('#section_id').val();
    var program_id = $(this).val();
    
    console.log("Fetching classes for Section:", section_id, "Program:", program_id);
    
    $('#class_id').html('<option value="">Loading...</option>');
    
    $.ajax({
        url: '<?php echo base_url(); ?>classes/getBySectionAndProgram',
        method: 'POST',
        data: {
            section_id: section_id,
            program_id: program_id,
            <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
        },
        dataType: 'json',
        success: function(response) {
            console.log("Raw response:", response);
            
            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
            
            if (response && response.length > 0) {
                $.each(response, function(index, classItem) {
                    html += '<option value="' + classItem.id + '">' + 
                           (classItem.degree ? classItem.degree + ' - ' : '') + 
                           classItem.class + '</option>';
                });
            } else {
                html += '<option value="" disabled>No classes found for this combination</option>';
                console.warn("No classes found for Section:", section_id, "Program:", program_id);
            }
            
            $('#class_id').html(html);
            refreshSemisterOrYearNoFromClass();
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", status, error);
            console.error("Response:", xhr.responseText);
            $('#class_id').html('<option value="">Error loading classes</option>');
        }
    });
    suggestUgcProgramFromLocal();
});

$(document).on('change', '#ugc_program_id', function () {
    $('#ugc_program_hint').text('');
    syncUgcProgramDisplays();
    suggestCampusFromUgcProgram();
    refreshSemisterOrYearNoFromClass();
});

function refreshSemisterOrYearNoFromClass() {
    var $sy = $('#semister_or_year_no');
    if ($sy.length === 0) return;
    var classId = $('#class_id').val() || '';
    var ugcProg = $('#ugc_program_id').val() || '';
    if (!classId) {
        $sy.val('');
        return;
    }
    $.ajax({
        url: '<?php echo base_url(); ?>student/derive_semister_or_year_no',
        type: 'GET',
        dataType: 'json',
        data: { class_id: classId, ugc_program_id: ugcProg },
        success: function (res) {
            if (res && res.status == 1) {
                $sy.val(res.semister_or_year_no || '');
            }
        }
    });
}

$(document).on('change', '#class_id', function () {
    refreshSemisterOrYearNoFromClass();
});
    
    $(document).ready(function () {
        var date_format = '<?php echo $result = strtr($this->customlib->getSchoolDateFormat(), ['d' => 'dd', 'm' => 'mm', 'Y' => 'yyyy']) ?>';
        var class_id = $('#class_id').val();
        var section_id = '<?php echo set_value('section_id', 0) ?>';
        var hostel_id = $('#hostel_id').val();
        var hostel_room_id = '<?php echo set_value('hostel_room_id', 0) ?>';
        var vehroute_id = '<?php echo set_value('vehroute_id', 0) ?>';
        var route_pickup_point_id = '<?php echo set_value('route_pickup_point_id', 0) ?>';
        getHostel(hostel_id, hostel_room_id);
        getSectionByClass(class_id, section_id);
        get_pickup_point(vehroute_id,route_pickup_point_id);

        $(".color").colorpicker();

        $("#btnreset").click(function () {
            $("#form1")[0].reset();
        });

        $(document).on('change', '#hostel_id', function (e) {
            var hostel_id = $(this).val();
            getHostel(hostel_id, 0);
        });

        function getSectionByClass(class_id, section_id) {

            if (class_id != "") {
                $('#section_id').html("");
                var base_url = '<?php echo base_url() ?>';
                var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                var url = "<?php
                $userdata = $this->customlib->getUserData();
                if (($userdata["role_id"] == 2)) {
                    echo "getClassTeacherSection";
                } else {
                    echo "getByClass";
                }
                ?>";

            }
        }

    $(document).on('change','#vehroute_id',function(){

   var vehroute_id=$(this).val();
   get_pickup_point(vehroute_id,0);
    });

    function get_pickup_point(vehroute_id,pickuppoint_id){
         if (vehroute_id != "") {

           var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
        $.ajax({
                url: baseurl+'admin/pickuppoint/get_pickupdropdownlist',
                type: "POST",
                data:{vehroute_id:vehroute_id},
                dataType: 'json',
                 beforeSend: function() {
                    $('#pickup_point').html('');
                },
                success: function(res) {

                    $.each(res, function (index, value) {
                         var sel = "";
                            if (pickuppoint_id == value.route_pickup_point_id) {
                                sel = "selected";
                            }

                        div_data += "<option  value=" + value.route_pickup_point_id + " " + sel + ">" + value.name + "</option>";
                    });

                    $('#pickup_point').html(div_data);
                },
                   error: function(xhr) { // if error occured
                   alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
            },
            complete: function() {

            }
            });
    }

    }

        function getHostel(hostel_id, hostel_room_id) {
            if (hostel_room_id == "") {
                hostel_room_id = 0;
            }

            if (hostel_id != "") {
                $('#hostel_room_id').html("");

                var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                $.ajax({
                    type: "GET",
                    url: baseurl + "admin/hostelroom/getRoom",
                    data: {'hostel_id': hostel_id},
                    dataType: "json",
                    beforeSend: function () {
                        $('#hostel_room_id').addClass('dropdownloading');
                    },
                    success: function (data) {
                        $.each(data, function (i, obj)
                        {
                            var sel = "";
                            if (hostel_room_id == obj.id) {
                                sel = "selected";
                            }

                            div_data += "<option value=" + obj.id + " " + sel + ">" + obj.room_no + " (" + obj.room_type + ")" + "</option>";

                        });
                        $('#hostel_room_id').append(div_data);
                    },
                    complete: function () {
                        $('#hostel_room_id').removeClass('dropdownloading');
                    }
                });
            }
        }
    });

    function auto_fill_guardian_address() {
        if ($("#autofill_current_address").is(':checked'))
        {
            $('#current_address').val($('#guardian_address').val());
        }
    }

    function auto_fill_address() {
        if ($("#autofill_address").is(':checked'))
        {
            $('#permanent_address').val($('#current_address').val());
        }
    }

    $('input:radio[name="guardian_is"]').change(
            function () {
                if ($(this).is(':checked')) {
                    var value = $(this).val();
                    if (value == "father") {
                        var father_relation = "<?php echo $this->lang->line('father'); ?>";
                        $('#guardian_name').val($('#father_name').val());
                        $('#guardian_phone').val($('#father_phone').val());
                        $('#guardian_occupation').val($('#father_occupation').val());
                        $('#guardian_relation').val(father_relation);
                    } else if (value == "mother") {
                        var mother_relation = "<?php echo $this->lang->line('mother'); ?>";
                        $('#guardian_name').val($('#mother_name').val());
                        $('#guardian_phone').val($('#mother_phone').val());
                        $('#guardian_occupation').val($('#mother_occupation').val());
                        $('#guardian_relation').val(mother_relation);
                    } else {
                        $('#guardian_name').val("");
                        $('#guardian_phone').val("");
                        $('#guardian_occupation').val("");
                        $('#guardian_relation').val("")
                    }
                }
            });
</script>

<script type="text/javascript">
    $(".mysiblings").click(function () {
        $('.sibling_msg').html("");
        $('.modal_title').html('<b>' + "<?php echo $this->lang->line('add_sibling'); ?>" + '</b>');
        $('#mySiblingModal').modal({
            backdrop: 'static',
            keyboard: false,
            show: true
        });
    });
</script>

<script type="text/javascript">

    $(document).on('change', '#sibiling_class_id', function (e) {
        $('#sibiling_section_id').html("");
        var class_id = $(this).val();
        var base_url = '<?php echo base_url() ?>';
        var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
        $.ajax({
            type: "GET",
            url: base_url + "sections/getByClass",
            data: {'class_id': class_id},
            dataType: "json",
            success: function (data) {
                $.each(data, function (i, obj)
                {
                    div_data += "<option value=" + obj.section_id + ">" + obj.section + "</option>";
                });
                $('#sibiling_section_id').append(div_data);
            }
        });
    });

    $(document).on('change', '#sibiling_section_id', function (e) {
        getStudentsByClassAndSection();
    });

    function getStudentsByClassAndSection() {

        $('#sibiling_student_id').html("");
        var class_id = $('#sibiling_class_id').val();
        var section_id = $('#sibiling_section_id').val();
        var student_id = '<?php echo set_value('student_id') ?>';
        var base_url = '<?php echo base_url() ?>';
        var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
        $.ajax({
            type: "GET",
            url: base_url + "student/getByClassAndSection",
            data: {'class_id': class_id, 'section_id': section_id},
            dataType: "json",
            success: function (data) {
                $.each(data, function (i, obj)
                {
                    var sel = "";
                    if (section_id == obj.section_id) {
                        sel = "selected=selected";
                    }

                    if (obj.admission_no == null) {
                        div_data += "<option value=" + obj.id + ">" + obj.full_name +  "</option>";
                    } else {
                        div_data += "<option value=" + obj.id + ">" + obj.full_name +  " (" + obj.admission_no + ") " + "</option>";
                    }

                });
                $('#sibiling_student_id').append(div_data);
            }
        });
    }

    $(document).on('click', '.add_sibling', function () {
        var student_id = $('#sibiling_student_id').val();
        var base_url = '<?php echo base_url() ?>';
        if (student_id.length > 0) {
            $.ajax({
                type: "GET",
                url: base_url + "student/getStudentRecordByID",
                data: {'student_id': student_id},
                dataType: "json",
                success: function (data) {
                    $('#sibling_name').text("<?php echo $this->lang->line('sibling'); ?> : " + data.full_name);
                    $('#sibling_name_next').val(data.firstname + " " + data.lastname);
                    $('#sibling_id').val(student_id);
                    $('#father_name').val(data.father_name);
                    $('#father_phone').val(data.father_phone);
                    $('#father_occupation').val(data.father_occupation);
                    $('#mother_name').val(data.mother_name);
                    $('#mother_phone').val(data.mother_phone);
                    $('#mother_occupation').val(data.mother_occupation);
                    $('#guardian_name').val(data.guardian_name);
                    $('#guardian_relation').val(data.guardian_relation);
                    $('#guardian_address').val(data.guardian_address);
                    $('#guardian_phone').val(data.guardian_phone);
                    $('#state').val(data.state);
                    $('#city').val(data.city);
                    $('#pincode').val(data.pincode);
                    $('#current_address').val(data.current_address);
                    $('#permanent_address').val(data.permanent_address);
                    $('#guardian_occupation').val(data.guardian_occupation);
                    $("input[name=guardian_is][value='" + data.guardian_is + "']").prop("checked", true);
                    $('#mySiblingModal').modal('hide');
                }
            });
        } else {
            $('.sibling_msg').html("<div class='alert alert-danger text-center'><?php echo $this->lang->line('no_student_selected') ?></div>");
        }
    });
</script>

<script type="text/javascript" src="<?php echo base_url(); ?>backend/dist/js/savemode.js"></script>

<script>

$('#transport_feemaster_id').multiselect({
    columns: 1,
    placeholder: '<?php echo $this->lang->line('select_month') ?>',
    search: true
});

$('#fee_session_group_id').multiselect({
    columns: 1,
    placeholder: '<?php echo $this->lang->line('select_fees') ?>',
    search: true
});

</script>
<script type="text/javascript">
    var total_fees_alloted= parseFloat($("input[name='total_post_fees']").val());
    $(document).ready(function(){
        $(document).on('change','.fee_group_chk',function(){

        if ($(this).prop("checked")) {
            total_fees_alloted +=parseFloat($(this).closest('div').find('span.fee_group_total').data('amount'));
        }
        else {
            total_fees_alloted -=parseFloat($(this).closest('div').find('span.fee_group_total').data('amount'));
        }
//==============
        $.ajax({
            type: "POST",
            url: base_url + "admin/currency/getAmountFormat",
            data: {'total_fees_alloted': total_fees_alloted},
            dataType: "json",
             beforeSend: function() {
         $('#fade').css("display", "block");
         $('#modal').css("display", "block");
             },
            success: function (data) {
                console.log(data);
                 $('.total_fees_alloted').text(data.amount);
               $("#fade").fadeOut(1000);
         $("#modal").fadeOut(1000);
            },
             error: function(xhr) { // if error occured
         $("#fade").fadeOut(1000);
         $("#modal").fadeOut(1000);
            },
            complete: function() {
              $("#fade").fadeOut(1000);
         $("#modal").fadeOut(1000);
            }
        });
//==============

    });
    });
</script>

<script type="text/javascript">
$(document).ready(function () {
    // Add hidden fields to store old values if they don't exist
    if ($('#old_temporary_district').length === 0) {
        $('form').append('<input type="hidden" id="old_temporary_district" value="<?php echo set_value("temporary_district"); ?>">');
        $('form').append('<input type="hidden" id="old_temporary_local_level" value="<?php echo set_value("temporary_local_level"); ?>">');
        $('form').append('<input type="hidden" id="old_permanent_district" value="<?php echo set_value("permanent_district"); ?>">');
        $('form').append('<input type="hidden" id="old_permanent_local_level" value="<?php echo set_value("permanent_local_level"); ?>">');
    }
    
    // Debug - log the old values
    console.log("Old temporary district: " + $('#old_temporary_district').val());
    console.log("Old temporary local level: " + $('#old_temporary_local_level').val());
    console.log("Old permanent district: " + $('#old_permanent_district').val());
    console.log("Old permanent local level: " + $('#old_permanent_local_level').val());

    // Function to initialize temporary address on page load
    function initTemporaryAddress() {
        var temp_province = $('#temporary_province').val();
        if (temp_province != '') {
            // Load districts for the selected province
            $.ajax({
                url: '<?php echo base_url("student/getDistrictsByProvince"); ?>',
                method: 'POST',
                data: {province_id: temp_province},
                dataType: 'json',
                success: function (response) {
                    $('#temporary_district').find('option').not(':first').remove();
                    
                    $.each(response, function (index, district) {
                        $('#temporary_district').append('<option value="' + district.id + '">' + district.name + '</option>');
                    });
                    
                    // Set the old district value if it exists
                    var old_temp_district = $('#old_temporary_district').val();
                    if (old_temp_district != '') {
                        console.log("Setting temp district to: " + old_temp_district);
                        $('#temporary_district').val(old_temp_district);
                        
                        // Now load the local levels based on this district
                        loadTemporaryLocalLevels(old_temp_district);
                    }
                }
            });
        }
    }
    
    // Function to load temporary local levels
    function loadTemporaryLocalLevels(district_id) {
        if (district_id != '') {
            $.ajax({
                url: '<?php echo base_url("student/getMunicipalitiesByDistrict"); ?>',
                method: 'POST',
                data: {district_id: district_id},
                dataType: 'json',
                success: function (response) {
                    $('#temporary_local_level').find('option').not(':first').remove();
                    
                    $.each(response, function (index, municipality) {
                        $('#temporary_local_level').append('<option value="' + municipality.id + '">' + municipality.name + '</option>');
                    });
                    
                    // Set the old local level value if it exists
                    var old_temp_local_level = $('#old_temporary_local_level').val();
                    if (old_temp_local_level != '') {
                        console.log("Setting temp local level to: " + old_temp_local_level);
                        $('#temporary_local_level').val(old_temp_local_level);
                    }
                }
            });
        }
    }
    
    // Function to initialize permanent address on page load
    function initPermanentAddress() {
        var perm_province = $('#permanent_province').val();
        if (perm_province != '' && !$('#autofill_address').is(':checked')) {
            // Load districts for the selected province
            $.ajax({
                url: '<?php echo base_url("student/getDistrictsByProvince"); ?>',
                method: 'POST',
                data: {province_id: perm_province},
                dataType: 'json',
                success: function (response) {
                    $('#permanent_district').find('option').not(':first').remove();
                    
                    $.each(response, function (index, district) {
                        $('#permanent_district').append('<option value="' + district.id + '">' + district.name + '</option>');
                    });
                    
                    // Set the old district value if it exists
                    var old_perm_district = $('#old_permanent_district').val();
                    if (old_perm_district != '') {
                        console.log("Setting perm district to: " + old_perm_district);
                        $('#permanent_district').val(old_perm_district);
                        
                        // Now load the local levels based on this district
                        loadPermanentLocalLevels(old_perm_district);
                    }
                }
            });
        }
    }
    
    // Function to load permanent local levels
    function loadPermanentLocalLevels(district_id) {
        if (district_id != '' && !$('#autofill_address').is(':checked')) {
            $.ajax({
                url: '<?php echo base_url("student/getMunicipalitiesByDistrict"); ?>',
                method: 'POST',
                data: {district_id: district_id},
                dataType: 'json',
                success: function (response) {
                    $('#permanent_local_level').find('option').not(':first').remove();
                    
                    $.each(response, function (index, municipality) {
                        $('#permanent_local_level').append('<option value="' + municipality.id + '">' + municipality.name + '</option>');
                    });
                    
                    // Set the old local level value if it exists
                    var old_perm_local_level = $('#old_permanent_local_level').val();
                    if (old_perm_local_level != '') {
                        console.log("Setting perm local level to: " + old_perm_local_level);
                        $('#permanent_local_level').val(old_perm_local_level);
                    }
                    if (typeof syncUgcMirrorLegacyToHidden === 'function') {
                        syncUgcMirrorLegacyToHidden();
                    }
                }
            });
        }
    }
    
    // Call initialization functions with a slight delay to ensure DOM is ready
    setTimeout(function() {
        initTemporaryAddress();
        initPermanentAddress();
    }, 500);
    
    // Regular event handlers (as before)
    // Temporary Address - Province change event
    $('#temporary_province').change(function () {
        var province_id = $(this).val();
        
        if (province_id != '') {
            $.ajax({
                url: '<?php echo base_url("student/getDistrictsByProvince"); ?>',
                method: 'POST',
                data: {province_id: province_id},
                dataType: 'json',
                success: function (response) {
                    // Clear and update district dropdown
                    $('#temporary_district').find('option').not(':first').remove();
                    $('#temporary_local_level').find('option').not(':first').remove();
                    
                    $.each(response, function (index, district) {
                        $('#temporary_district').append('<option value="' + district.id + '">' + district.name + '</option>');
                    });
                    
                    // If autofill is checked, update permanent address too
                    if ($('#autofill_address').is(':checked')) {
                        $('#permanent_province').val(province_id);
                        copyDistrictsToPermAddress(response);
                    }
                }
            });
        } else {
            $('#temporary_district').find('option').not(':first').remove();
            $('#temporary_local_level').find('option').not(':first').remove();
        }
    });
    
    // Temporary Address - District change event
    $('#temporary_district').change(function () {
        var district_id = $(this).val();
        
        if (district_id != '') {
            $.ajax({
                url: '<?php echo base_url("student/getMunicipalitiesByDistrict"); ?>',
                method: 'POST',
                data: {district_id: district_id},
                dataType: 'json',
                success: function (response) {
                    // Clear and update municipality dropdown
                    $('#temporary_local_level').find('option').not(':first').remove();
                    
                    $.each(response, function (index, municipality) {
                        $('#temporary_local_level').append('<option value="' + municipality.id + '">' + municipality.name + '</option>');
                    });
                    
                    // If autofill is checked, update permanent address too
                    if ($('#autofill_address').is(':checked')) {
                        $('#permanent_district').val(district_id);
                        copyLocalLevelsToPermAddress(response);
                    }
                }
            });
        } else {
            $('#temporary_local_level').find('option').not(':first').remove();
        }
    });
    
    // Remaining event handlers remain the same...
    // Temporary Local Level change event
    $('#temporary_local_level').change(function () {
        if ($('#autofill_address').is(':checked')) {
            $('#permanent_local_level').val($(this).val());
            if (typeof syncUgcMirrorLegacyToHidden === 'function') {
                syncUgcMirrorLegacyToHidden();
            }
        }
    });
    
    // Temporary Ward No change event
    $('#temporary_ward_no').change(function () {
        if ($('#autofill_address').is(':checked')) {
            $('#permanent_ward_no').val($(this).val());
        }
    });
    
    // Current Address change event
    $('#current_address').on('input', function () {
        if ($('#autofill_address').is(':checked')) {
            $('#permanent_address').val($(this).val());
        }
    });
    
    // Temporary House No change event
    $('#temporary_house_no').on('input', function () {
        if ($('#autofill_address').is(':checked')) {
            $('#permanent_house_no').val($(this).val());
        }
    });
    
    // Permanent Address - Province change event
    $('#permanent_province').change(function () {
        var province_id = $(this).val();
        
        if (province_id != '' && !$('#autofill_address').is(':checked')) {
            $.ajax({
                url: '<?php echo base_url("student/getDistrictsByProvince"); ?>',
                method: 'POST',
                data: {province_id: province_id},
                dataType: 'json',
                success: function (response) {
                    // Clear and update district dropdown
                    $('#permanent_district').find('option').not(':first').remove();
                    $('#permanent_local_level').find('option').not(':first').remove();
                    
                    $.each(response, function (index, district) {
                        $('#permanent_district').append('<option value="' + district.id + '">' + district.name + '</option>');
                    });
                }
            });
        } else if (province_id == '') {
            $('#permanent_district').find('option').not(':first').remove();
            $('#permanent_local_level').find('option').not(':first').remove();
        }
    });
    
    // Permanent Address - District change event
    $('#permanent_district').change(function () {
        var district_id = $(this).val();
        
        if (district_id != '' && !$('#autofill_address').is(':checked')) {
            $.ajax({
                url: '<?php echo base_url("student/getMunicipalitiesByDistrict"); ?>',
                method: 'POST',
                data: {district_id: district_id},
                dataType: 'json',
                success: function (response) {
                    // Clear and update municipality dropdown
                    $('#permanent_local_level').find('option').not(':first').remove();
                    
                    $.each(response, function (index, municipality) {
                        $('#permanent_local_level').append('<option value="' + municipality.id + '">' + municipality.name + '</option>');
                    });
                }
            });
        } else if (district_id == '') {
            $('#permanent_local_level').find('option').not(':first').remove();
        }
    });
    
    // Auto fill address checkbox change event
    $('#autofill_address').change(function() {
        auto_fill_address();
    });
});

// Helper functions (same as before)
function copyDistrictsToPermAddress(districts) {
    $('#permanent_district').find('option').not(':first').remove();
    $('#permanent_local_level').find('option').not(':first').remove();
    
    $.each(districts, function (index, district) {
        $('#permanent_district').append('<option value="' + district.id + '">' + district.name + '</option>');
    });
    
    // Set the same district value as temporary
    var temp_district_val = $('#temporary_district').val();
    if (temp_district_val != '') {
        $('#permanent_district').val(temp_district_val);
        
        // Get local levels for this district
        $.ajax({
            url: '<?php echo base_url("student/getMunicipalitiesByDistrict"); ?>',
            method: 'POST',
            data: {district_id: temp_district_val},
            dataType: 'json',
            success: function (response) {
                copyLocalLevelsToPermAddress(response);
            }
        });
    } else if (typeof syncUgcMirrorLegacyToHidden === 'function') {
        syncUgcMirrorLegacyToHidden();
    }
}

function copyLocalLevelsToPermAddress(local_levels) {
    $('#permanent_local_level').find('option').not(':first').remove();
    
    $.each(local_levels, function (index, municipality) {
        $('#permanent_local_level').append('<option value="' + municipality.id + '">' + municipality.name + '</option>');
    });
    
    // Set the same local level value as temporary
    var temp_local_level_val = $('#temporary_local_level').val();
    if (temp_local_level_val != '') {
        $('#permanent_local_level').val(temp_local_level_val);
    }
    if (typeof syncUgcMirrorLegacyToHidden === 'function') {
        syncUgcMirrorLegacyToHidden();
    }
}

function auto_fill_address() {
    if ($('#autofill_address').is(':checked')) {
        // Get temporary address values
        var temp_province_val = $('#temporary_province').val();
        var temp_district_val = $('#temporary_district').val();
        var temp_local_level_val = $('#temporary_local_level').val();
        var temp_ward_no = $('#temporary_ward_no').val();
        var temp_address = $('#current_address').val();
        var temp_house_no = $('#temporary_house_no').val();
        
        // Set province
        $('#permanent_province').val(temp_province_val);
        
        // If province is selected, fetch and set districts
        if (temp_province_val != '') {
            $.ajax({
                url: '<?php echo base_url("student/getDistrictsByProvince"); ?>',
                method: 'POST',
                data: {province_id: temp_province_val},
                dataType: 'json',
                success: function (response) {
                    copyDistrictsToPermAddress(response);
                }
            });
        }
        
        // Set other fields
        $('#permanent_ward_no').val(temp_ward_no);
        $('#permanent_address').val(temp_address);
        $('#permanent_house_no').val(temp_house_no);
        if (typeof ugcApplyCascadeSelection === 'function' && window.__ugcAddressRows && window.__ugcAddressRows.length) {
            var utp = $.trim($('#ugc_t_province').val() || '');
            var utd = $.trim($('#ugc_t_district').val() || '');
            var utc = $.trim($('#ugc_t_local_level').val() || '');
            ugcApplyCascadeSelection('ugc_p', utp, utd, utc);
        }
        if (temp_province_val === '') {
            if (typeof syncUgcMirrorLegacyToHidden === 'function') {
                syncUgcMirrorLegacyToHidden();
            }
        }
    } else {
        // Clear permanent address fields if autofill is unchecked
        $('#permanent_province').val('');
        $('#permanent_district').find('option').not(':first').remove();
        $('#permanent_local_level').find('option').not(':first').remove();
        $('#permanent_ward_no').val('');
        $('#permanent_address').val('');
        $('#permanent_house_no').val('');
        if (typeof ugcApplyCascadeSelection === 'function' && window.__ugcAddressRows && window.__ugcAddressRows.length) {
            ugcApplyCascadeSelection('ugc_p', '', '', '');
        }
        if (typeof syncUgcMirrorLegacyToHidden === 'function') {
            syncUgcMirrorLegacyToHidden();
        }
    }
    
    return true;
}
// Add this function to your existing JavaScript file
function registerStudentAPI(studentData, token) {
    // Show loading indicator
    $('#addloader').html('<i class="fa fa-spinner fa-spin fa-1x fa-fw"></i>Loading...');
    
    // Create form data object for sending multipart/form-data
    var formData = new FormData();
    
    // Add all student properties to formData
    Object.keys(studentData).forEach(function(key) {
        formData.append(key, studentData[key]);
    });
    
    // Handle file uploads if present
    if ($('#ppSizePhoto')[0] && $('#ppSizePhoto')[0].files[0]) {
        formData.append('ppSizePhoto', $('#ppSizePhoto')[0].files[0]);
    }
    if ($('#citizenshipFront')[0] && $('#citizenshipFront')[0].files[0]) {
        formData.append('citizenshipFront', $('#citizenshipFront')[0].files[0]);
    }
    if ($('#citizenshipBack')[0] && $('#citizenshipBack')[0].files[0]) {
        formData.append('citizenshipBack', $('#citizenshipBack')[0].files[0]);
    }
    
    // Make API request
    $.ajax({
        url: '<?php echo rtrim(base_url(), '/'); ?>/api/Student/Create',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.status === "Success") {
                alert('Student registered successfully');
                // Reset form or redirect as needed
                $("#form1")[0].reset();
            } else {
                alert('Registration failed: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            alert('Error: ' + xhr.responseText);
        },
        complete: function() {
            // Reset loading button
            $('#addloader').html('Submit');
        }
    });
}
</script>

<script type="text/javascript">
function ugcAddressUniq(list, key) {
    var seen = {};
    var out = [];
    $.each(list, function(_, row) {
        var v = row[key] || '';
        if (!v) return;
        if (!seen[v]) {
            seen[v] = true;
            out.push(v);
        }
    });
    out.sort();
    return out;
}

function ugcAddressFilter(list, province, district) {
    return $.grep(list, function(row) {
        if (province && row.province !== province) return false;
        if (district && row.district !== district) return false;
        return true;
    });
}

function fillSelect($el, values, selected) {
    var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
    $.each(values, function(_, v) {
        var sel = (String(selected) === String(v)) ? 'selected' : '';
        html += '<option value="' + $('<div>').text(v).html() + '" ' + sel + '>' + $('<div>').text(v).html() + '</option>';
    });
    $el.html(html);
}

function fillSelectLocalLevel($el, rows, selectedCombinedCode) {
    var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
    var seen = {};
    $.each(rows, function(_, row) {
        var code = row.combinedCode || '';
        var name = row.localLevel || '';
        if (!code || !name) return;
        if (seen[code]) return;
        seen[code] = true;
        var sel = (String(selectedCombinedCode) === String(code)) ? 'selected' : '';
        html += '<option value="' + $('<div>').text(code).html() + '" ' + sel + '>' + $('<div>').text(name).html() + '</option>';
    });
    $el.html(html);
}

/**
 * Rebuild UGC/HEMIS cascade selects from cached rows (used when copying temporary → permanent).
 * @param {string} prefix ugc_t or ugc_p
 * @param {string} province Province label (value in ugc province select)
 * @param {string} district District label
 * @param {string} combinedCode UGC combinedCode for local level option value
 */
function ugcApplyCascadeSelection(prefix, province, district, combinedCode) {
    var rows = window.__ugcAddressRows || [];
    if (!rows.length) {
        return;
    }
    var $prov = $('#' + prefix + '_province');
    var $dist = $('#' + prefix + '_district');
    var $loc = $('#' + prefix + '_local_level');
    if (!$prov.length || !$dist.length || !$loc.length) {
        return;
    }
    province = $.trim(String(province || ''));
    district = $.trim(String(district || ''));
    combinedCode = $.trim(String(combinedCode || ''));
    fillSelect($prov, ugcAddressUniq(rows, 'province'), province);
    var drows = ugcAddressFilter(rows, province, null);
    fillSelect($dist, ugcAddressUniq(drows, 'district'), district);
    var lrows = ugcAddressFilter(rows, province, district);
    fillSelectLocalLevel($loc, lrows, combinedCode);
    syncUgcHiddenFieldsFromCascade(prefix);
}

/** Strip trailing "Province" so e.g. "Sudurpaschim Province" can match UGC row "Sudurpashchim". */
function ugcNormalizeProvinceLabel(s) {
    s = $.trim(s || '');
    if (!s) {
        return '';
    }
    return s.replace(/\s+province$/i, '').trim();
}
/** Match PHP hemis_student_fix_common_local_level_typos for UGC row vs legacy label compare. */
function ugcNormalizeLocalLevelForMatch(s) {
    s = $.trim(String(s || ''));
    if (!s) {
        return '';
    }
    return s.replace(/Metropolitian/gi, 'Metropolitan').toLowerCase();
}
/** Loose province compare: case-insensitive + optional " Province" suffix mismatch. */
function ugcProvinceLabelsMatch(rowProvince, formProvince) {
    var a = $.trim(String(rowProvince || ''));
    var b = $.trim(String(formProvince || ''));
    if (!a || !b) {
        return false;
    }
    if (a.toLowerCase() === b.toLowerCase()) {
        return true;
    }
    var an = ugcNormalizeProvinceLabel(a).toLowerCase();
    var bn = ugcNormalizeProvinceLabel(b).toLowerCase();
    return an === bn;
}
/**
 * Find UGC combinedCode for a province + district + local level name (Address/GetAll row shape).
 * @param {Array} rows from student/ugc_addresses
 */
function ugcFindCombinedCode(rows, provinceText, districtText, localLevelText) {
    if (!rows || !rows.length) {
        return '';
    }
    var p = $.trim(provinceText || '');
    var d = $.trim(districtText || '').toLowerCase();
    var l = ugcNormalizeLocalLevelForMatch(localLevelText);
    if (!p || !d || !l) {
        return '';
    }
    var i;
    for (i = 0; i < rows.length; i++) {
        var row = rows[i];
        var rd = $.trim(String(row.district || '')).toLowerCase();
        var rl = ugcNormalizeLocalLevelForMatch(row.localLevel || '');
        if (ugcProvinceLabelsMatch(row.province, p) && rd === d && rl === l) {
            return String(row.combinedCode || '');
        }
    }
    var matches = [];
    for (i = 0; i < rows.length; i++) {
        row = rows[i];
        rd = $.trim(String(row.district || '')).toLowerCase();
        rl = ugcNormalizeLocalLevelForMatch(row.localLevel || '');
        if (rd === d && rl === l) {
            matches.push(String(rows[i].combinedCode || ''));
        }
    }
    if (matches.length === 1 && matches[0]) {
        return matches[0];
    }
    return '';
}

/**
 * Copy visible UGC/HEMIS cascade values into the hidden POST fields the server reads (names + combinedCode).
 * Local-level select uses value=combinedCode and display text=municipality name — the API expects both.
 *
 * @param {string} prefix ugc_p or ugc_t
 */
function syncUgcHiddenFieldsFromCascade(prefix) {
    var $prov = $('#' + prefix + '_province');
    var $dist = $('#' + prefix + '_district');
    var $loc = $('#' + prefix + '_local_level');
    if (!$prov.length || !$dist.length || !$loc.length) {
        return;
    }
    var pv = $.trim($prov.val() || '');
    var dv = $.trim($dist.val() || '');
    var code = $.trim($loc.val() || '');
    var $opt = $loc.find('option:selected');
    var locTxt = ($opt.length && $opt.val()) ? $.trim($opt.text() || '') : '';
    if (locTxt.toLowerCase().indexOf('select') === 0) {
        locTxt = '';
    }
    $('input[name="' + prefix + '_province_name"]').val(pv);
    $('input[name="' + prefix + '_district_name"]').val(dv);
    $('input[name="' + prefix + '_local_level_name"]').val(locTxt);
    $('input[name="' + prefix + '_combined_code"]').val(code);
}

/** Before POST: always flush UGC widgets into hiddens; optionally backfill from legacy if combined codes still empty. */
function syncAllUgcAddressHiddensForSubmit() {
    syncUgcMirrorLegacyToHidden();
    var pCode = $.trim($('input[name="ugc_p_combined_code"]').val() || '');
    var tCode = $.trim($('input[name="ugc_t_combined_code"]').val() || '');
    if (!pCode) {
        syncUgcHiddenFieldsFromCascade('ugc_p');
        pCode = $.trim($('input[name="ugc_p_combined_code"]').val() || '');
    }
    if (!tCode) {
        syncUgcHiddenFieldsFromCascade('ugc_t');
        tCode = $.trim($('input[name="ugc_t_combined_code"]').val() || '');
    }
    if ((!pCode || !tCode) && window.__ugcAddressRows && window.__ugcAddressRows.length) {
        syncUgcMirrorLegacyToHidden();
    }
}

/**
 * Require complete HEMIS/UGC cascade + combinedCode when the official address list is available.
 * @returns {string|null} error message or null
 */
function validateUgcLegacyMatchesCache() {
    if (!window.__ugcAddressRows || !window.__ugcAddressRows.length) {
        return null;
    }
    syncAllUgcAddressHiddensForSubmit();
    var msgs = [];
    function hasSel($el) {
        return $el.length && $el.val() !== undefined && $el.val() !== null && String($el.val()) !== '';
    }
    function checkUgc(prefix, label) {
        var $p = $('#' + prefix + '_province');
        if (!$p.length) {
            return;
        }
        var $d = $('#' + prefix + '_district');
        var $l = $('#' + prefix + '_local_level');
        var any = hasSel($p) || hasSel($d) || hasSel($l);
        var all = hasSel($p) && hasSel($d) && hasSel($l);
        if (any && !all) {
            msgs.push(label + ': choose province, district, and local level together (or clear all three).');
            return;
        }
        if (!all) {
            msgs.push(label + ': select province, district, and local level from the official UGC list (required for HEMIS).');
            return;
        }
        var code = $.trim($('input[name="' + prefix + '_combined_code"]').val() || '');
        if (!code) {
            msgs.push(label + ': could not resolve locality code. Re-select local level from the list.');
        }
    }
    checkUgc('ugc_t', 'Temporary address');
    checkUgc('ugc_p', 'Permanent address');
    return msgs.length ? msgs.join('\n') : null;
}

/**
 * Copy visible legacy address dropdowns (temporary_*, permanent_*) into hidden POST fields
 * (ugc_*_province_name, etc.) when legacy labels match cached UGC address rows (also sets combined_code).
 */
function syncUgcMirrorLegacyToHidden() {
    if ($('.legacy-address-cascade').length
        && $('.legacy-address-cascade').length === $('.legacy-address-cascade:hidden').length) {
        return;
    }
    var rows = window.__ugcAddressRows || [];
    function pair(localPrefix, ugcPrefix) {
        var $prov = $('#' + localPrefix + '_province');
        var $dist = $('#' + localPrefix + '_district');
        var $loc  = $('#' + localPrefix + '_local_level');
        var $hProv = $('input[name="' + ugcPrefix + '_province_name"]');
        var $hDist = $('input[name="' + ugcPrefix + '_district_name"]');
        var $hLoc  = $('input[name="' + ugcPrefix + '_local_level_name"]');
        var $hCode = $('input[name="' + ugcPrefix + '_combined_code"]');
        function optText($el) {
            var t = $el.find('option:selected').text();
            return (t && t.toLowerCase().indexOf('select') !== 0) ? $.trim(t) : '';
        }
        var pt = optText($prov);
        var dt = optText($dist);
        var lt = optText($loc);
        $hProv.val(pt);
        $hDist.val(dt);
        $hLoc.val(lt);
        var code = ugcFindCombinedCode(rows, pt, dt, lt);
        if (code) {
            $hCode.val(code);
        } else if (pt && dt && lt) {
            $hCode.val('');
        }
    }
    pair('temporary', 'ugc_t');
    pair('permanent', 'ugc_p');
}

function initUgcAddressCascade(prefix, initial) {
    // prefix: ugc_t or ugc_p
    var $prov = $('#' + prefix + '_province');
    var $dist = $('#' + prefix + '_district');
    var $loc  = $('#' + prefix + '_local_level');
    var $hProv = $('input[name="'+prefix+'_province_name"]');
    var $hDist = $('input[name="'+prefix+'_district_name"]');
    var $hLoc  = $('input[name="'+prefix+'_local_level_name"]');
    var $hCode = $('input[name="'+prefix+'_combined_code"]');

    $.ajax({
        url: '<?php echo base_url(); ?>student/ugc_addresses?live=1',
        method: 'GET',
        dataType: 'json',
        success: function(resp) {
            var rows = (resp && resp.data) ? resp.data : [];
            window.__ugcAddressRows = rows;
            var provinces = ugcAddressUniq(rows, 'province');
            fillSelect($prov, provinces, initial.province || '');

            function refreshDistricts() {
                var p = $prov.val();
                var drows = ugcAddressFilter(rows, p, null);
                fillSelect($dist, ugcAddressUniq(drows, 'district'), initial.district || '');
                refreshLocals();
            }
            function refreshLocals() {
                var p = $prov.val();
                var d = $dist.val();
                var lrows = ugcAddressFilter(rows, p, d);
                fillSelectLocalLevel($loc, lrows, initial.combinedCode || '');
                syncHidden();
            }
            function syncHidden() {
                syncUgcMirrorLegacyToHidden();
                var code = $.trim($('input[name="' + prefix + '_combined_code"]').val() || '');
                if (!code) {
                    syncUgcHiddenFieldsFromCascade(prefix);
                }
            }

            $prov.off('change.ugc').on('change.ugc', function() {
                initial.district = '';
                initial.localLevel = '';
                refreshDistricts();
            });
            $dist.off('change.ugc').on('change.ugc', function() {
                initial.localLevel = '';
                refreshLocals();
            });
            $loc.off('change.ugc').on('change.ugc', function() {
                syncHidden();
            });

            // Seed
            refreshDistricts();
        },
        error: function() {
            $prov.html('<option value="">Error</option>');
            $dist.html('<option value="">Error</option>');
            $loc.html('<option value="">Error</option>');
        }
    });
}

$(document).ready(function() {
    // Temporary
    initUgcAddressCascade('ugc_t', {
        province: '<?php echo set_value("ugc_t_province"); ?>',
        district: '<?php echo set_value("ugc_t_district"); ?>',
        localLevel: '<?php echo set_value("ugc_t_local_level"); ?>',
        combinedCode: '<?php echo set_value("ugc_t_combined_code"); ?>'
    });

    // Permanent
    initUgcAddressCascade('ugc_p', {
        province: '<?php echo set_value("ugc_p_province"); ?>',
        district: '<?php echo set_value("ugc_p_district"); ?>',
        localLevel: '<?php echo set_value("ugc_p_local_level"); ?>',
        combinedCode: '<?php echo set_value("ugc_p_combined_code"); ?>'
    });

    // UX: mirror legacy address dropdowns into hidden ugc_*_name fields on change and after cascade loads.
    function bindLegacyMirror(localPrefix, ugcPrefix) {
        var $prov = $('#' + localPrefix + '_province');
        var $dist = $('#' + localPrefix + '_district');
        var $loc  = $('#' + localPrefix + '_local_level');
        function syncOne() {
            syncUgcMirrorLegacyToHidden();
        }
        $prov.off('change.ugcAuto').on('change.ugcAuto', syncOne);
        $dist.off('change.ugcAuto').on('change.ugcAuto', syncOne);
        $loc.off('change.ugcAuto').on('change.ugcAuto', syncOne);
    }
    bindLegacyMirror('temporary', 'ugc_t');
    bindLegacyMirror('permanent', 'ugc_p');
    syncUgcMirrorLegacyToHidden();

    $('.legacy-address-cascade').hide();
    $('.ugc-address-cascade-col').show();

    $('#ugc_t_province, #ugc_t_district, #ugc_t_local_level').on('change.ugcAutofillPerm', function () {
        if (!$('#autofill_address').is(':checked')) {
            return;
        }
        if (typeof ugcApplyCascadeSelection !== 'function' || !window.__ugcAddressRows || !window.__ugcAddressRows.length) {
            return;
        }
        var utp = $.trim($('#ugc_t_province').val() || '');
        var utd = $.trim($('#ugc_t_district').val() || '');
        var utc = $.trim($('#ugc_t_local_level').val() || '');
        ugcApplyCascadeSelection('ugc_p', utp, utd, utc);
    });

    $('#form1').on('submit', function (e) {
        syncAllUgcAddressHiddensForSubmit();
        var ugcAddrErr = validateUgcLegacyMatchesCache();
        if (ugcAddrErr) {
            e.preventDefault();
            alert(ugcAddrErr);
            return false;
        }
    });
});
</script>


<script>
$(document).ready(function() {
    let previousStudyIndex = 0;
    
    // Add new previous study fields
    $(document).on('click', '.add-previous-study', function() {
        previousStudyIndex++;
        const newFields = `
            <div class="previous-study-group" data-index="${previousStudyIndex}">
                <div class="row around10">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="previous_level_of_study_${previousStudyIndex}"><?php echo $this->lang->line('previous_level_of_study'); ?></label>
                            <select id="previous_level_of_study_${previousStudyIndex}" name="previous_level_of_study[]" class="form-control">
                                <option value="">Select Level</option>
                                <option value="SEE/SLC">SEE/SLC</option>
                                <option value="+2">+2</option>
                                <option value="Bachelors">Bachelors</option>
                                <option value="Masters">Masters</option>
                                <option value="CTEVT">CTEVT</option>
                            </select>
                            <span class="text-danger"></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="previous_board_university_college_${previousStudyIndex}"><?php echo $this->lang->line('previous_board_university_college'); ?></label>
                            <select id="previous_board_university_college_${previousStudyIndex}" name="previous_board_university_college[]" class="form-control">
                                <option value="">Select Board/University/College</option>
                                <option value="Tribhuvan University(TU)">Tribhuvan University (TU)</option>
                                <option value="Pokhara University(PU)">Pokhara University (PU)</option>
                                <option value="Purbanchal University(PU)">Purbanchal University (PU)</option>
                                <option value="Kathmandu University(KU)">Kathmandu University (KU)</option>
                                <option value="Nepal Sankrit University">Nepal Sankrit University</option>
                            </select>
                            <span class="text-danger"></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="previous_registration_no_${previousStudyIndex}"><?php echo $this->lang->line('previous_registration_no'); ?></label>
                            <input id="previous_registration_no_${previousStudyIndex}" name="previous_registration_no[]" placeholder="" type="text" class="form-control" />
                            <span class="text-danger"></span>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="previous_institution_name_${previousStudyIndex}"><?php echo $this->lang->line('previous_school_details'); ?></label>
                            <textarea class="form-control" rows="3" placeholder="" name="previous_institution_name[]" id="previous_institution_name_${previousStudyIndex}"></textarea>
                            <span class="text-danger"></span>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-sm remove-previous-study" style="display: block; margin-top: 5px;">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#previous-study-container').append(newFields);
    });
    
    // Remove previous study fields
    $(document).on('click', '.remove-previous-study', function() {
        $(this).closest('.previous-study-group').remove();
    });
});
</script>

<script>
$(document).ready(function() {
    let additionalDocumentIndex = 5; // Start from 5 since we have 4 fixed documents

    // Add new document row
    $('#add_document_btn').click(function() {
        // Show additional documents section if hidden
        $('#additional_documents_section').show();
        
        let newRow = `
            <tr class="additional-document-row" data-index="${additionalDocumentIndex}">
                <td>${additionalDocumentIndex}.</td>
                <td>
                    <input type="text" name='additional_title[]' class="form-control" placeholder="">
                </td>
                <td>
                    <div class="input-group">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name='additional_doc[]' id="doc_${additionalDocumentIndex}" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <label class="custom-file-label" for="doc_${additionalDocumentIndex}">Choose file</label>
                        </div>
                    </div>
                    <span class="text-danger error-msg"></span>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove_additional_document_btn">
                        <i class="fa fa-minus"></i>
                    </button>
                </td>
            </tr>
        `;
        
        $('#additional_documents_table tbody').append(newRow);
        
        // Initialize file input styling for the new row
        $('#doc_' + additionalDocumentIndex).on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
        });
        
        additionalDocumentIndex++;
    });

    // Remove additional document row
    $(document).on('click', '.remove_additional_document_btn', function() {
        $(this).closest('tr').remove();
        updateAdditionalRowNumbers();
        
        // Hide additional section if no rows left
        if ($('#additional_documents_table tbody tr.additional-document-row').length === 0) {
            $('#additional_documents_section').hide();
        }
    });

    // Update row numbers for additional documents
    function updateAdditionalRowNumbers() {
        let startIndex = 5;
        $('#additional_documents_table tbody tr.additional-document-row').each(function(index) {
            let currentIndex = startIndex + index;
            $(this).find('td:first').text(currentIndex + '.');
            $(this).attr('data-index', currentIndex);
        });
        additionalDocumentIndex = startIndex + $('#additional_documents_table tbody tr.additional-document-row').length;
    }
    
    // Initialize file input styling for existing file inputs
    $('input[type="file"]').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        if ($(this).next('.custom-file-label').length) {
            $(this).next('.custom-file-label').html(fileName);
        }
    });
});
</script>



<!-- NEPALI DATEPICKER - Handled by Global Initializer -->
<script type="text/javascript">
$(document).ready(function () {
    console.log('Student create page loaded - Nepali datepickers will be initialized by global script');
    console.log('jQuery available:', typeof $);
    console.log('$.fn.nepaliDatePicker available:', typeof $.fn.nepaliDatePicker);
    
    // Auto-convert BS date to AD date when DOB BS is changed
    function convertBSToAD(bsDateStr, targetAdFieldId) {
        if (!bsDateStr || bsDateStr.trim() === '') {
            $('#' + targetAdFieldId).val('');
            return;
        }
        
        try {
            // Use NepaliFunctions.ConvertToEnglish if available (preferred method)
            if (window.NepaliFunctions && typeof window.NepaliFunctions.ConvertToEnglish === 'function') {
                var engDateObj = window.NepaliFunctions.ConvertToEnglish(bsDateStr);
                if (engDateObj && engDateObj.year && engDateObj.month && engDateObj.day) {
                    var adYear = engDateObj.year;
                    var adMonth = String(engDateObj.month).padStart(2, '0');
                    var adDay = String(engDateObj.day).padStart(2, '0');
                    var adDateStr = adYear + '-' + adMonth + '-' + adDay;
                    $('#' + targetAdFieldId).val(adDateStr);
                    console.log('Converted BS to AD:', bsDateStr, '->', adDateStr);
                    return;
                }
            }
            
            // Fallback: Parse BS date and use BS2AD if available
            var bsParts = bsDateStr.split('-');
            if (bsParts.length === 3) {
                var bsYear = parseInt(bsParts[0]);
                var bsMonth = parseInt(bsParts[1]);
                var bsDay = parseInt(bsParts[2]);
                
                if (window.NepaliFunctions && typeof window.NepaliFunctions.BS2AD === 'function') {
                    var adDate = window.NepaliFunctions.BS2AD({
                        year: bsYear,
                        month: bsMonth,
                        day: bsDay
                    });
                    if (adDate && adDate.year && adDate.month && adDate.day) {
                        var adYear = adDate.year;
                        var adMonth = String(adDate.month).padStart(2, '0');
                        var adDay = String(adDate.day).padStart(2, '0');
                        var adDateStr = adYear + '-' + adMonth + '-' + adDay;
                        $('#' + targetAdFieldId).val(adDateStr);
                        console.log('Converted BS to AD:', bsDateStr, '->', adDateStr);
                        return;
                    }
                }
            }
            
            console.warn('Date conversion functions not available for:', bsDateStr);
        } catch (e) {
            console.error('Error converting BS to AD:', e);
        }
    }
    
    // Function to ensure datepickers are initialized and working
    function initializeDatePickers() {
        // Wait for plugin to be available
        if (typeof $.fn.nepaliDatePicker === 'undefined') {
            setTimeout(initializeDatePickers, 200);
            return;
        }
        
        // Initialize DOB BS field
        var $dobField = $('#dob');
        if ($dobField.length && !$dobField.data('ndp-initialized') && !$dobField.data('nepaliDatePicker')) {
            try {
                $dobField.nepaliDatePicker({
                    ndpTriggerButton: false,
                    dateFormat: 'YYYY-MM-DD',
                    ndpYear: true,
                    ndpMonth: true,
                    ndpYearCount: 90,
                    readOnlyInput: false,
                    ndpTrigger: 'click'
                });
                $dobField.data('ndp-initialized', true);
                console.log('Initialized DOB BS datepicker');
            } catch (e) {
                console.error('Error initializing DOB datepicker:', e);
            }
        }
        
        // Initialize admission_date field
        var $admissionField = $('#admission_date');
        if ($admissionField.length && !$admissionField.data('ndp-initialized') && !$admissionField.data('nepaliDatePicker')) {
            try {
                $admissionField.nepaliDatePicker({
                    ndpTriggerButton: false,
                    dateFormat: 'YYYY-MM-DD',
                    ndpYear: true,
                    ndpMonth: true,
                    ndpYearCount: 15,
                    readOnlyInput: false,
                    ndpTrigger: 'click'
                });
                $admissionField.data('ndp-initialized', true);
                console.log('Initialized admission_date datepicker');
            } catch (e) {
                console.error('Error initializing admission_date datepicker:', e);
            }
        }
        
        // Attach conversion handler for DOB field
        if ($dobField.length) {
            // Remove existing handlers to avoid duplicates
            $dobField.off('nepali.datepicker.dateChanged.convert change.convert blur.convert input.convert');
            
            // Listen for Nepali datepicker date change event (multiple event names for compatibility)
            $dobField.on('nepali.datepicker.dateChanged.convert', function(e) {
                var bsDate = $(this).val();
                if (bsDate) {
                    setTimeout(function() {
                        convertBSToAD(bsDate, 'dob_ad');
                    }, 100);
                }
            });
            
            // Also listen for the dateChange event (alternative event name)
            $dobField.on('dateChange.convert', function() {
                var bsDate = $(this).val();
                if (bsDate) {
                    setTimeout(function() {
                        convertBSToAD(bsDate, 'dob_ad');
                    }, 100);
                }
            });
            
            // Listen for manual input changes
            $dobField.on('change.convert blur.convert input.convert', function() {
                var bsDate = $(this).val();
                if (bsDate && bsDate.match(/^\d{4}-\d{2}-\d{2}$/)) {
                    convertBSToAD(bsDate, 'dob_ad');
                }
            });
            
            // Use datepicker's onSelect callback if available
            if ($dobField.data('nepaliDatePicker')) {
                try {
                    var datepickerInstance = $dobField.data('nepaliDatePicker');
                    if (datepickerInstance && typeof datepickerInstance.on === 'function') {
                        datepickerInstance.on('dateChanged', function() {
                            var bsDate = $dobField.val();
                            if (bsDate) {
                                setTimeout(function() {
                                    convertBSToAD(bsDate, 'dob_ad');
                                }, 100);
                            }
                        });
                    }
                } catch (e) {
                    console.log('Could not attach datepicker callback:', e);
                }
            }
            
            // Convert initial value if present
            var initialBSDate = $dobField.val();
            if (initialBSDate && initialBSDate.match(/^\d{4}-\d{2}-\d{2}$/)) {
                setTimeout(function() {
                    convertBSToAD(initialBSDate, 'dob_ad');
                }, 500);
            }
            
            // Monitor for value changes using MutationObserver as fallback
            if (typeof MutationObserver !== 'undefined') {
                var observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                            var bsDate = $dobField.val();
                            if (bsDate && bsDate.match(/^\d{4}-\d{2}-\d{2}$/)) {
                                convertBSToAD(bsDate, 'dob_ad');
                            }
                        }
                    });
                });
                observer.observe($dobField[0], { attributes: true, attributeFilter: ['value'] });
            }
        }
    }
    
    // Start initialization
    initializeDatePickers();
    
    // Retry initialization if datepickers weren't ready
    setTimeout(function() {
        initializeDatePickers();
    }, 1000);
    
    // Also ensure calendar shows on click
    $(document).on('click', '#dob, #admission_date', function() {
        var $field = $(this);
        if (typeof $.fn.nepaliDatePicker === 'undefined') {
            return;
        }
        try {
            if (!$field.data('nepaliDatePicker') && !$field.data('ndp-initialized')) {
                $field.nepaliDatePicker({
                    ndpTriggerButton: false,
                    dateFormat: 'YYYY-MM-DD',
                    ndpYear: true,
                    ndpMonth: true,
                    ndpYearCount: ($field.attr('id') === 'dob' ? 90 : 15),
                    readOnlyInput: false,
                    ndpTrigger: 'click'
                });
                $field.data('ndp-initialized', true);
            }
            if ($field.data('nepaliDatePicker') || $field.data('ndp-initialized')) {
                try {
                    $field.nepaliDatePicker('show');
                } catch (e2) { /* ignore */ }
            }
        } catch (e) {
            console.error('Error showing datepicker:', e);
        }
    });
    
    // Additional handler: Watch for calendar date selection by monitoring DOM changes
    setInterval(function() {
        var $dobField = $('#dob');
        if ($dobField.length && $dobField.val()) {
            var currentVal = $dobField.val();
            var lastVal = $dobField.data('last-converted-value') || '';
            if (currentVal !== lastVal && currentVal.match(/^\d{4}-\d{2}-\d{2}$/)) {
                $dobField.data('last-converted-value', currentVal);
                convertBSToAD(currentVal, 'dob_ad');
            }
        }
    }, 300);
});
</script>
                         


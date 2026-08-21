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
                    <form action="<?php echo site_url("student/edit/" . $id) ?>" id="employeeform" name="employeeform"
                        method="post" accept-charset="utf-8" enctype="multipart/form-data">
                        <div class="box-body">
                            <div class="">
                            <div class="bozero">
                                <h4 class="pagetitleh-whitebg"><?php echo $this->lang->line('edit_student'); ?></h4>
                                <div class="around10">
                                    <?php if ($this->session->flashdata('msg')) { ?>
                                        <?php echo $this->session->flashdata('msg');
                                        $this->session->unset_userdata('msg'); ?>
                                    <?php } ?>
                                    <div id="form-alert"></div>
                                    <?php echo $this->customlib->getCSRF(); ?>
                                    <input type="hidden" name="student_session_id"
                                        value="<?php echo set_value('student_session_id', isset($student['student_session_id']) ? $student['student_session_id'] : ''); ?>">
                                    <input type="hidden" name="student_id"
                                        value="<?php echo set_value('id', isset($student['id']) ? $student['id'] : ''); ?>">
                                    <input type="hidden" name="sibling_name"
                                        value="<?php echo set_value('sibling_name', 0); ?>" id="sibling_name_next">
                                    <input type="hidden" name="sibling_id"
                                        value="<?php echo set_value('sibling_id', 0); ?>" id="sibling_id">

                                    <div class="col-md-12">
                                        <h4 class="pagetitleh2" style="margin-top:0;">Academic details</h4>
                                        <p class="text-muted small">HEMIS / UGC fields use official Program, Batch, and Fiscal Year metadata.</p>
                                    </div>
                                    <div class="row">
                                        <?php if (!$adm_auto_insert) { ?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('admission_no'); ?></label><small class="req"> *</small>
                                                    <input autofocus="" id="admission_no" name="admission_no" placeholder="" type="text" class="form-control"
                                                        value="<?php echo set_value('admission_no', isset($student['admission_no']) ? $student['admission_no'] : ''); ?>" />
                                                    <span class="text-danger"><?php echo form_error('admission_no'); ?></span>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="admission_year"><?php echo $this->lang->line('admission_year'); ?></label><small class="req"> *</small>
                                                <select id="admission_year" name="admission_year" class="form-control">
                                                    <option value="">Select Year</option>
                                                    <option value="2076" <?php echo ($student['admission_year'] == '2076') ? 'selected' : ''; ?>>2076</option>
                                                    <option value="2077" <?php echo ($student['admission_year'] == '2077') ? 'selected' : ''; ?>>2077</option>
                                                    <option value="2078" <?php echo ($student['admission_year'] == '2078') ? 'selected' : ''; ?>>2078</option>
                                                    <option value="2079" <?php echo ($student['admission_year'] == '2079') ? 'selected' : ''; ?>>2079</option>
                                                    <option value="2080" <?php echo ($student['admission_year'] == '2080') ? 'selected' : ''; ?>>2080</option>
                                                    <option value="2081" <?php echo ($student['admission_year'] == '2081') ? 'selected' : ''; ?>>2081</option>
                                                    <option value="2082" <?php echo ($student['admission_year'] == '2082') ? 'selected' : ''; ?>>2082</option>
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
                                                <input id="admissionYearId" name="admissionYearId" type="number" class="form-control" min="1" value="<?php echo set_value('admissionYearId', isset($student['admissionYearId']) ? $student['admissionYearId'] : ''); ?>" />
                                                <small class="text-muted">Uses UGC Batch ID (same catalog at KBMC). Auto-filled from UGC Batch; editable.</small>
                                                <small id="admissionYearId_hint" class="text-muted"></small>
                                                <span class="text-danger"><?php echo form_error('admissionYearId'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="batch_name_nepali">Batch Name (Nepali) <span class="label label-info">HEMIS</span></label>
                                                <input id="batch_name_nepali" name="batch_name_nepali" type="text" class="form-control" value="<?php echo set_value('batch_name_nepali', isset($student['batch_name_nepali']) ? $student['batch_name_nepali'] : ''); ?>" readonly />
                                                <small class="text-muted">Auto-filled from UGC Batch selection.</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="batch_name">Batch Name <span class="label label-info">HEMIS</span></label>
                                                <input id="batch_name" name="batch_name" type="text" class="form-control" value="<?php echo set_value('batch_name', isset($student['batch_name']) ? $student['batch_name'] : ''); ?>" readonly />
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
                                        <?php if ($sch_setting->admission_date) {
                                            $admission_date = "";
                                            if ($student['admission_date'] != '0000-00-00' && $student['admission_date'] != '') {
                                                $admission_date = $student['admission_date'];
                                            }
                                            ?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="admission_date"><?php echo $this->lang->line('admission_date'); ?></label>
                                                    <input id="admission_date" name="admission_date" placeholder="YYYY-MM-DD (BS)" type="text" class="form-control date-bs nepali-datepicker" data-nepali-date="true"
                                                        value="<?php echo set_value('admission_date', $admission_date) ?>" autocomplete="off" />
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
                                                <input id="semister_or_year_no" name="semister_or_year_no" type="text" class="form-control" value="<?php echo set_value('semister_or_year_no', isset($student['semister_or_year_no']) ? $student['semister_or_year_no'] : ''); ?>" readonly />
                                                <small class="text-muted">Auto from Class + UGC program type (annual → year, semester → semester).</small>
                                                <span class="text-danger"><?php echo form_error('semister_or_year_no'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="complition_year">Expected completion year <span class="label label-info">HEMIS</span></label>
                                                <input id="complition_year" name="complition_year" type="number" class="form-control" min="1970" max="2100" placeholder="e.g. 2085" value="<?php echo set_value('complition_year', isset($student['complition_year']) && $student['complition_year'] ? $student['complition_year'] : ''); ?>" />
                                                <small class="text-muted">Maps to HEMIS <code>complitionYear</code>.</small>
                                                <span class="text-danger"><?php echo form_error('complition_year'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="academic_program_duration"><?php echo $this->lang->line('program_Duration'); ?></label><small class="req"> *</small>
                                                <input id="academic_program_duration" name="academic_program_duration" placeholder="" type="text" class="form-control"
                                                    value="<?php echo set_value('academic_program_duration', isset($student['academic_program_duration']) ? $student['academic_program_duration'] : ''); ?>" />
                                                <span class="text-danger"><?php echo form_error('academic_program_duration'); ?></span>
                                            </div>
                                        </div>
                                        <?php if ($sch_setting->roll_no) { ?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="roll_no"><?php echo $this->lang->line('roll_number'); ?></label>
                                                    <input id="roll_no" name="roll_no" placeholder="" type="text" class="form-control"
                                                        value="<?php echo set_value('roll_no', isset($student['roll_no']) ? $student['roll_no'] : ''); ?>" />
                                                    <span class="text-danger"><?php echo form_error('roll_no'); ?></span>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>

                                    <div class="col-md-12">
                                        <h4 class="pagetitleh2" style="margin-top:15px;">Basic details</h4>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label
                                                    for="exampleInputEmail1"><?php echo $this->lang->line('first_name'); ?></label><small
                                                    class="req"> *</small>
                                                <input id="firstname" name="firstname" placeholder="" type="text"
                                                    class="form-control"
                                                    value="<?php echo set_value('firstname', $student['firstname']); ?>" />
                                                <input type="hidden" name="studentid"
                                                    value="<?php echo $student["id"] ?>">
                                                <span class="text-danger"><?php echo form_error('firstname'); ?></span>
                                            </div>
                                        </div>

                                        <?php if ($sch_setting->middlename) { ?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label
                                                        for="exampleInputEmail1"><?php echo $this->lang->line('middle_name'); ?></label>
                                                    <input id="middlename" name="middlename" placeholder="" type="text"
                                                        class="form-control"
                                                        value="<?php echo set_value('middlename', $student['middlename']); ?>" />
                                                    <span class="text-danger"><?php echo form_error('middlename'); ?></span>
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <?php if ($sch_setting->lastname) { ?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label
                                                        for="exampleInputEmail1"><?php echo $this->lang->line('last_name'); ?></label><small
                                                        class="req"> *</small>
                                                    <input id="lastname" name="lastname" placeholder="" type="text"
                                                        class="form-control"
                                                        value="<?php echo set_value('lastname', $student['lastname']); ?>" />
                                                    <span class="text-danger"><?php echo form_error('lastname'); ?></span>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label
                                                    for="exampleInputEmail1"><?php echo $this->lang->line('first_name_np'); ?></label><small
                                                    class="req"> *</small>
                                                <input id="first_name_np" name="first_name_np" placeholder="" type="text"
                                                    class="form-control"
                                                    value="<?php echo set_value('first_name_np', $student['first_name_np']); ?>" />
                                                <input type="hidden" name="studentid"
                                                    value="<?php echo $student["id"] ?>">
                                                <span class="text-danger"><?php echo form_error('first_name_np'); ?></span>
                                            </div>
                                        </div>
                                        <?php if ($sch_setting->middlename) { ?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('middle_name_np'); ?></label>
                                                    <input id="middle_name_np" name="middle_name_np" placeholder="" type="text" class="form-control" value="<?php echo set_value('middle_name_np', $student['middle_name_np']); ?>" />
                                                    <span class="text-danger"><?php echo form_error('middle_name_np'); ?></span>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label
                                                    for="exampleInputEmail1"><?php echo $this->lang->line('last_name_np'); ?></label><small
                                                    class="req"> *</small>
                                                <input id="last_name_np" name="last_name_np" placeholder="" type="text"
                                                    class="form-control"
                                                    value="<?php echo set_value('last_name_np', $student['last_name_np']); ?>" />
                                                <input type="hidden" name="studentid"
                                                    value="<?php echo $student["id"] ?>">
                                                <span class="text-danger"><?php echo form_error('last_name_np'); ?></span>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label
                                                    for="exampleInputFile"><?php echo $this->lang->line('gender'); ?></label><small
                                                    class="req"> *</small>
                                                <select class="form-control" name="gender">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php foreach ($genderList as $key => $value) { ?>
                                                        <option value="<?php echo $key; ?>" <?php if ($student['gender'] == $key) {
                                                               echo "selected";
                                                           } ?>>
                                                            <?php echo $value; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('gender'); ?></span>
                                            </div>
                                        </div>

<!-- Nepali Date of Birth Field (match staffedit logic) -->
<div class="col-md-3">
    <div class="form-group">
        <label for="dob_bs"><?php echo $this->lang->line('date_of_birth_bs'); ?></label>
        <input id="dob_bs" name="dob" placeholder="YYYY-MM-DD (BS)" type="text" class="form-control date-bs nepali-datepicker" data-nepali-date="true" value="<?php echo set_value('dob', isset($student['dob']) && $student['dob'] && $student['dob'] != '0000-00-00' ? $student['dob'] : ''); ?>" autocomplete="off" />
        <span class="text-danger"><?php echo form_error('dob'); ?></span>
    </div>
</div>

                                       <!-- English Date of Birth Field -->
<div class="col-md-3">
    <div class="form-group">
        <label for="dob_ad"><?php echo $this->lang->line('date_of_birth'); ?></label>
        <?php
        $dob_ad_value = '';
        if (!empty($student['dob_ad']) && $student['dob_ad'] !== '0000-00-00') {
            $dob_ad_value = date($this->customlib->getSchoolDateFormat(), strtotime($student['dob_ad']));
        }
        ?>
        <input id="dob_ad"
            name="dob_ad"
            type="text"
            class="form-control date"
            value="<?php echo set_value('dob_ad', $dob_ad_value); ?>"
            placeholder="Select date"
            autocomplete="off"
        />
        <span class="text-danger"><?php echo form_error('dob_ad'); ?></span>
    </div>
</div>

                                        <?php if ($sch_setting->mobile_no) { ?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label
                                                        for="exampleInputEmail1"><?php echo $this->lang->line('mobile_number'); ?></label>
                                                    <input id="mobileno" name="mobileno" placeholder="" type="text"
                                                        class="form-control"
                                                        value="<?php echo set_value('mobileno', $student['mobileno']); ?>" />
                                                    <span class="text-danger"><?php echo form_error('mobileno'); ?></span>
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <?php if ($sch_setting->student_email) { ?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label
                                                        for="exampleInputEmail1"><?php echo $this->lang->line('email'); ?></label>
                                                    <input id="email" name="email" placeholder="" type="text"
                                                        class="form-control"
                                                        value="<?php echo set_value('email', $student['email']); ?>" />
                                                    <span class="text-danger"><?php echo form_error('email'); ?></span>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="ethnicity">Ethnicity <span class="label label-info">HEMIS</span></label><small class="req"> *</small>
                                                <?php
                                                $eth_val = set_value('ethnicity', isset($student['ethnicity']) ? $student['ethnicity'] : (isset($student['cast']) ? $student['cast'] : ''));
                                                ?>
                                                <input id="ethnicity" name="ethnicity" list="ethnicity_datalist" placeholder="e.g. Brahmin / Kshetri / Tharu" type="text" class="form-control" value="<?php echo htmlspecialchars($eth_val, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off" />
                                                <datalist id="ethnicity_datalist">
                                                    <?php
                                                    $eth_opts = isset($ethnicities) && is_array($ethnicities) ? $ethnicities : array();
                                                    foreach ($eth_opts as $eth_label) {
                                                        echo '<option value="' . htmlspecialchars($eth_label, ENT_QUOTES, 'UTF-8') . '">';
                                                    }
                                                    ?>
                                                </datalist>
                                                <small class="text-muted">Required by HEMIS. Legacy Caste is filled automatically from this value.</small>
                                                <span class="text-danger"><?php echo form_error('ethnicity'); ?></span>
                                            </div>
                                        </div>
                                        <?php if ($sch_setting->category) { ?>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('category'); ?></label>
                                                    <select id="category_id" name="category_id" class="form-control">
                                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                        <?php foreach ($categorylist as $category) { ?>
                                                            <option value="<?php echo $category['id'] ?>" <?php if ($student['category_id'] == $category['id']) {
                                                                   echo "selected =selected";
                                                               } ?>>
                                                                <?php echo $category['category']; ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                    <span class="text-danger"><?php echo form_error('category_id'); ?></span>
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <?php if ($sch_setting->religion) { ?>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="religion"><?php echo $this->lang->line('religion'); ?></label>
                                                    <select id="religion" name="religion" class="form-control">
                                                        <option value="">Select Religion</option>
                                                        <?php foreach ($religions as $id => $name): ?>
                                                            <option value="<?php echo $id; ?>" <?php echo set_select('religion', $id, ($student['religion'] == $id)); ?>>
                                                                <?php echo $name; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <span class="text-danger"><?php echo form_error('religion'); ?></span>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="edj"><?php echo $this->lang->line('edj'); ?></label>
                                                <input id="edj" name="edj" placeholder="" type="text" class="form-control"
                                                    value="<?php echo set_value('edj', isset($student['edj']) ? $student['edj'] : ''); ?>" />
                                                <span class="text-danger"><?php echo form_error('edj'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="citizenship">Citizenship number <span class="label label-info">HEMIS</span></label>
                                                <input id="citizenship" name="citizenship" type="text" class="form-control" value="<?php echo set_value('citizenship', isset($student['citizenship']) ? $student['citizenship'] : ''); ?>" />
                                                <span class="text-danger"><?php echo form_error('citizenship'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="citizenship_issue_dist">Citizenship issued district <span class="label label-info">HEMIS</span></label>
                                                <input id="citizenship_issue_dist" name="citizenship_issue_dist" type="text" class="form-control" value="<?php echo set_value('citizenship_issue_dist', isset($student['citizenship_issue_dist']) ? $student['citizenship_issue_dist'] : ''); ?>" />
                                                <span class="text-danger"><?php echo form_error('citizenship_issue_dist'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="nationality">Nationality</label>
                                                <input id="nationality" name="nationality" type="text" class="form-control" value="<?php echo set_value('nationality', isset($student['nationality']) ? $student['nationality'] : ''); ?>" />
                                                <span class="text-danger"><?php echo form_error('nationality'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <?php if ($sch_setting->national_identification_no) { ?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="adhar_no"><?php echo $this->lang->line('national_identification_number'); ?></label>
                                                    <input id="adhar_no" name="adhar_no" placeholder="" type="text" class="form-control" value="<?php echo set_value('adhar_no', isset($student['adhar_no']) ? $student['adhar_no'] : ''); ?>" />
                                                    <span class="text-danger"><?php echo form_error('adhar_no'); ?></span>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="disability_status">Disability Status</label>
                                                <input id="disability_status" name="disability_status" type="text" class="form-control" value="<?php echo set_value('disability_status', isset($student['disability_status']) ? $student['disability_status'] : ''); ?>" />
                                                <span class="text-danger"><?php echo form_error('disability_status'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="disability_type">Disability Type</label>
                                                <input id="disability_type" name="disability_type" type="text" class="form-control" value="<?php echo set_value('disability_type', isset($student['disability_type']) ? $student['disability_type'] : ''); ?>" />
                                                <span class="text-danger"><?php echo form_error('disability_type'); ?></span>
                                            </div>
                                        </div>
                                        <?php if ($sch_setting->student_photo) { ?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputFile"><?php echo $this->lang->line('student_photo'); ?></label>
                                                    <div>
                                                        <?php if (!empty($student['image'])) { ?>
                                                            <img src="<?php echo html_escape($this->customlib->studentUploadPublicUrl($student['image'])); ?>"
                                                                class="img-thumbnail" width="100" alt=""
                                                                onerror="this.style.display='none'">
                                                        <?php } ?>
                                                        <input class="filestyle form-control" type='file' name='file' id="file" size='20' />
                                                    </div>
                                                    <span class="text-danger"><?php echo form_error('file'); ?></span>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="row">
                                        <?php if ($sch_setting->student_height) { ?>
                                            <div class="col-md-3 col-xs-12">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('height'); ?></label>
                                                    <input type="text" name="height" class="form-control" value="<?php echo set_value('height', isset($student['height']) ? $student['height'] : ''); ?>">
                                                    <span class="text-danger"><?php echo form_error('height'); ?></span>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <?php if ($sch_setting->student_weight) { ?>
                                            <div class="col-md-3 col-xs-12">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('weight'); ?></label>
                                                    <input type="text" name="weight" class="form-control" value="<?php echo set_value('weight', isset($student['weight']) ? $student['weight'] : ''); ?>">
                                                    <span class="text-danger"><?php echo form_error('weight'); ?></span>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <?php if ($sch_setting->measurement_date) { ?>
                                            <div class="col-md-3 col-xs-12">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('measurement_date'); ?></label>
                                                    <input type="text" id="measure_date" value="<?php echo set_value('measure_date', isset($student['measure_date']) ? $student['measure_date'] : date($this->customlib->getSchoolDateFormat())); ?>" name="measure_date" class="form-control date">
                                                    <span class="text-danger"><?php echo form_error('measure_date'); ?></span>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="col-md-3" style="display:none;">
                                            <div class="form-group">
                                                <label for="fees_discount"><?php echo $this->lang->line('fees_discount'); ?></label>
                                                <input id="fees_discount" name="fees_discount" placeholder="" type="text" class="form-control" value="<?php echo set_value('fees_discount', isset($student['fees_discount']) ? $student['fees_discount'] : '0'); ?>" />
                                                <span class="text-danger"><?php echo form_error('fees_discount'); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                        <!-- <div class="col-md-3 pt25">
                                            <div class="row">
                                                <div class="col-lg-5 col-md-6 col-sm-3 col-xs-5">
                                                    <button type="button"
                                                        class="btn btn-sm btn-primary mysiblings anchorbtn"><i
                                                            class="fa fa-plus"></i>
                                                        <?php echo $this->lang->line('add_sibling'); ?></button>
                                                </div>
                                                <div class="col-lg-7 col-md-6 col-sm-9 col-xs-7">
                                                    <div class="pt6 overflowtextdot">
                                                        <span id="sibling_name"
                                                            class="label label-success"><?php echo set_value('sibling_name'); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> -->

                                    <div class="row">
                                        <?php echo display_custom_fields('students', $student['id']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                            <?php if (!empty($siblings)) { ?>
                                <div class="tshadow mb25 bozero sibling_div relative">
                                    <h3 class="pagetitleh2"><?php echo $this->lang->line('sibling'); ?></h3>
                                    <div class="box-tools sibbtnposition">
                                        <button type="button"
                                            class="btn btn-primary btn-sm remove_sibling"><?php echo $this->lang->line('remove_sibling'); ?></button>
                                    </div>
                                    <div class="around10">
                                        <div class="row">
                                            <input type="hidden" name="siblings_counts" class="siblings_counts"
                                                value="<?php echo $siblings_counts; ?>">
                                            <?php foreach ($siblings as $sibling_key => $sibling_value) { ?>
                                                <div class="col-xs-12 col-sm-6 col-md-4 sib_div"
                                                    id="sib_div_<?php echo $sibling_value->id ?>"
                                                    data-sibling_id="<?php echo $sibling_value->id ?>">
                                                    <div class="withsiblings">
                                                        <img src="<?php
                                                        if (!empty($sibling_value->image)) {
                                                            echo html_escape($this->customlib->studentUploadPublicUrl($sibling_value->image));
                                                        } else {
                                                            if ($sibling_value->gender == 'Female') {
                                                                echo base_url() . "uploads/student_images/default_female.jpg";
                                                            } else {
                                                                echo base_url() . "uploads/student_images/default_male.jpg";
                                                            }
                                                        }
                                                        ?>" alt="" class="" />
                                                        <div class="withsiblings-content">
                                                            <h5><a
                                                                    href="#"><?php echo $this->customlib->getFullname($sibling_value->firstname, $sibling_value->middlename, $sibling_value->lastname, $sch_setting->middlename, $sch_setting->lastname) ?></a>
                                                            </h5>
                                                            <p>
                                                                <b><?php echo $this->lang->line('admission_no'); ?></b>:<?php echo $sibling_value->admission_no; ?><br />
                                                                <b><?php echo $this->lang->line('class'); ?></b>:<?php echo $sibling_value->class; ?><br />
                                                                <b><?php echo $this->lang->line('section'); ?></b>:<?php echo $sibling_value->section; ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
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
                                                                <input id="program_fee" name="program_fee" type="number" step="0.01" class="form-control" value="<?php echo set_value('program_fee', isset($student['program_fee']) ? $student['program_fee'] : ''); ?>" />
                                                                <span class="text-danger"><?php echo form_error('program_fee'); ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="scholarship_amt">Scholarship Amount</label>
                                                                <input id="scholarship_amt" name="scholarship_amt" type="number" step="0.01" class="form-control" value="<?php echo set_value('scholarship_amt', isset($student['scholarship_amt']) ? $student['scholarship_amt'] : ''); ?>" />
                                                                <span class="text-danger"><?php echo form_error('scholarship_amt'); ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="paid_amount">Paid Amount</label>
                                                                <input id="paid_amount" name="paid_amount" type="number" step="0.01" class="form-control" value="<?php echo set_value('paid_amount', isset($student['paid_amount']) ? $student['paid_amount'] : ''); ?>" />
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
          <input type="hidden" name="fee_session_group_id[]" value="0">
          <input class="fee_group_chk vertical-middle" type="checkbox" name="fee_session_group_id[]" value="<?php echo $feesessiongroup_value->id; ?>"
          <?php
            // Check if this fee group is checked in POST (form error), else check if it's already assigned to the student
            $checked = false;
            if (isset($_POST['fee_session_group_id']) && is_array($_POST['fee_session_group_id'])) {
                $checked = in_array($feesessiongroup_value->id, $_POST['fee_session_group_id']);
            } else {
                // Check if this group is assigned to the student (from DB)
                if (isset($feesessiongroup_value->student_fees_master_id) && $feesessiongroup_value->student_fees_master_id > 0) {
                    $checked = true;
                }
            }
            echo $checked ? 'checked' : '';
          ?>
          >
          <a class="display-inline collapsed box-plus-panel" data-toggle="collapse" href="#collapse_fees_<?php echo $feesessiongroup_value->id ?>">
            <span class="font14"><?php echo $feesessiongroup_value->group_name; ?></span>
          </a>
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

                            <?php if (($sch_setting->father_name) || ($sch_setting->father_phone) || ($sch_setting->father_occupation) || ($sch_setting->father_pic) || ($sch_setting->mother_name) || ($sch_setting->mother_phone) || ($sch_setting->mother_occupation) || ($sch_setting->mother_pic) || ($sch_setting->guardian_name) || ($sch_setting->guardian_occupation) || ($sch_setting->guardian_relation) || ($sch_setting->guardian_phone) || ($sch_setting->guardian_email) || ($sch_setting->guardian_pic) || ($sch_setting->guardian_address)) { ?>
                                <div class="tshadow mb25 bozero">
                                    <h4 class="pagetitleh2"><?php echo $this->lang->line('parent_guardian_detail'); ?></h4>
                                    <div class="around10">
                                        <div class="row">
                                            <?php if ($sch_setting->father_name) { ?>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label
                                                            for="exampleInputEmail1"><?php echo $this->lang->line('father_name'); ?></label>
                                                        <input id="father_name" name="father_name" placeholder="" type="text"
                                                            class="form-control"
                                                            value="<?php echo set_value('father_name', $student['father_name']); ?>" />
                                                        <span
                                                            class="text-danger"><?php echo form_error('father_name'); ?></span>
                                                    </div>
                                                </div>
                                            <?php } ?>

                                            <?php if ($sch_setting->father_phone) { ?>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label
                                                            for="exampleInputEmail1"><?php echo $this->lang->line('phone_no'); ?></label>
                                                        <input id="father_phone" name="father_phone" placeholder="" type="text"
                                                            class="form-control"
                                                            value="<?php echo set_value('father_phone', $student['father_phone']); ?>" />
                                                        <span
                                                            class="text-danger"><?php echo form_error('father_phone'); ?></span>
                                                    </div>
                                                </div>
                                            <?php } ?>

                                            <?php if ($sch_setting->father_occupation) { ?>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label
                                                            for="exampleInputEmail1"><?php echo $this->lang->line('father_occupation'); ?></label>
                                                        <input id="father_occupation" name="father_occupation" placeholder=""
                                                            type="text" class="form-control"
                                                            value="<?php echo set_value('father_occupation', $student['father_occupation']); ?>" />
                                                        <span
                                                            class="text-danger"><?php echo form_error('father_occupation'); ?></span>
                                                    </div>
                                                </div>
                                            <?php } ?>

                                            <?php if ($sch_setting->father_pic) { ?>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label
                                                            for="exampleInputFile"><?php echo $this->lang->line('father_photo'); ?></label>
                                                        <div>
                                                            <?php if (!empty($student['father_pic'])) { ?>
                                                                <img src="<?php echo html_escape($this->customlib->studentUploadPublicUrl($student['father_pic'])); ?>"
                                                                    class="img-thumbnail" width="100">
                                                            <?php } ?>
                                                            <input class="filestyle form-control" type='file' name='father_pic'
                                                                id="file" size='20' />
                                                        </div>
                                                        <span class="text-danger"><?php echo form_error('father_pic'); ?></span>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>

                                        <div class="row">
                                            <?php if ($sch_setting->mother_name) { ?>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label
                                                            for="exampleInputEmail1"><?php echo $this->lang->line('mother_name'); ?></label>
                                                        <input id="mother_name" name="mother_name" placeholder="" type="text"
                                                            class="form-control"
                                                            value="<?php echo set_value('mother_name', $student['mother_name']); ?>" />
                                                        <span
                                                            class="text-danger"><?php echo form_error('mother_name'); ?></span>
                                                    </div>
                                                </div>
                                            <?php } ?>

                                            <?php if ($sch_setting->mother_phone) { ?>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label
                                                            for="exampleInputEmail1"><?php echo $this->lang->line('mother_phone'); ?></label>
                                                        <input id="mother_phone" name="mother_phone" placeholder="" type="text"
                                                            class="form-control"
                                                            value="<?php echo set_value('mother_phone', $student['mother_phone']); ?>" />
                                                        <span
                                                            class="text-danger"><?php echo form_error('mother_phone'); ?></span>
                                                    </div>
                                                </div>
                                            <?php } ?>

                                            <?php if ($sch_setting->mother_occupation) { ?>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label
                                                            for="exampleInputEmail1"><?php echo $this->lang->line('mother_occupation'); ?></label>
                                                        <input id="mother_occupation" name="mother_occupation" placeholder=""
                                                            type="text" class="form-control"
                                                            value="<?php echo set_value('mother_occupation', $student['mother_occupation']); ?>" />
                                                        <span
                                                            class="text-danger"><?php echo form_error('mother_occupation'); ?></span>
                                                    </div>
                                                </div>
                                            <?php } ?>

                                            <?php if ($sch_setting->mother_pic) { ?>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label
                                                            for="exampleInputFile"><?php echo $this->lang->line('mother_photo'); ?></label>
                                                        <div>
                                                            <?php if (!empty($student['mother_pic'])) { ?>
                                                                <img src="<?php echo html_escape($this->customlib->studentUploadPublicUrl($student['mother_pic'])); ?>"
                                                                    class="img-thumbnail" width="100">
                                                            <?php } ?>
                                                            <input class="filestyle form-control" type='file' name='mother_pic'
                                                                id="file" size='20' />
                                                        </div>
                                                        <span class="text-danger"><?php echo form_error('mother_pic'); ?></span>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>

                                        <?php if ($sch_setting->guardian_name) { ?>
                                            <div class="row">
                                                <div class="form-group col-md-12">
                                                    <label><?php echo $this->lang->line('if_guardian_is'); ?></label><small
                                                        class="req"> *</small>&nbsp;&nbsp;&nbsp;
                                                    <label class="radio-inline">
                                                        <input type="radio" name="guardian_is" <?php if ($student['guardian_is'] == "father") {
                                                            echo "checked";
                                                        } ?>
                                                            value="father"> <?php echo $this->lang->line('father'); ?>
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="guardian_is" <?php if ($student['guardian_is'] == "mother") {
                                                            echo "checked";
                                                        } ?>
                                                            value="mother"> <?php echo $this->lang->line('mother'); ?>
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="guardian_is" <?php if ($student['guardian_is'] == "other") {
                                                            echo "checked";
                                                        } ?>
                                                            value="other"> <?php echo $this->lang->line('other'); ?>
                                                    </label>
                                                    <span class="text-danger"><?php echo form_error('guardian_is'); ?></span>
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <?php if ($sch_setting->guardian_name) { ?>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label
                                                                    for="exampleInputEmail1"><?php echo $this->lang->line('guardian_name'); ?></label><small
                                                                    class="req"> *</small>
                                                                <input id="guardian_name" name="guardian_name" placeholder=""
                                                                    type="text" class="form-control"
                                                                    value="<?php echo set_value('guardian_name', $student['guardian_name']); ?>" />
                                                                <span
                                                                    class="text-danger"><?php echo form_error('guardian_name'); ?></span>
                                                            </div>
                                                        </div>
                                                    <?php } ?>

                                                    <?php if ($sch_setting->guardian_relation) { ?>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label
                                                                    for="exampleInputEmail1"><?php echo $this->lang->line('guardian_relation'); ?></label>
                                                                <input id="guardian_relation" name="guardian_relation"
                                                                    placeholder="" type="text" class="form-control"
                                                                    value="<?php echo set_value('guardian_relation', $student['guardian_relation']); ?>" />
                                                                <span
                                                                    class="text-danger"><?php echo form_error('guardian_relation'); ?></span>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                </div>

                                                <div class="row">
                                                    <?php if ($sch_setting->guardian_phone) { ?>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label
                                                                    for="exampleInputEmail1"><?php echo $this->lang->line('guardian_phone'); ?></label><small
                                                                    class="req"> *</small>
                                                                <input id="guardian_phone" name="guardian_phone" placeholder=""
                                                                    type="text" class="form-control"
                                                                    value="<?php echo set_value('guardian_phone', $student['guardian_phone']); ?>" />
                                                                <span
                                                                    class="text-danger"><?php echo form_error('guardian_phone'); ?></span>
                                                            </div>
                                                        </div>
                                                    <?php } ?>

                                                    <?php if ($sch_setting->guardian_occupation) { ?>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label
                                                                    for="exampleInputEmail1"><?php echo $this->lang->line('guardian_occupation'); ?></label>
                                                                <input id="guardian_occupation" name="guardian_occupation"
                                                                    placeholder="" type="text" class="form-control"
                                                                    value="<?php echo set_value('guardian_occupation', $student['guardian_occupation']); ?>" />
                                                                <span
                                                                    class="text-danger"><?php echo form_error('guardian_occupation'); ?></span>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            </div>

                                            <?php if ($sch_setting->guardian_email) { ?>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label
                                                            for="exampleInputEmail1"><?php echo $this->lang->line('guardian_email'); ?></label>
                                                        <input id="guardian_email" name="guardian_email" placeholder=""
                                                            type="text" class="form-control"
                                                            value="<?php echo set_value('guardian_email', $student['guardian_email']); ?>" />
                                                        <span
                                                            class="text-danger"><?php echo form_error('guardian_email'); ?></span>
                                                    </div>
                                                </div>
                                            <?php } ?>

                                            <?php if ($sch_setting->guardian_pic) { ?>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label
                                                            for="exampleInputFile"><?php echo $this->lang->line('guardian_photo'); ?></label>
                                                        <div>
                                                            <?php if (!empty($student['guardian_pic'])) { ?>
                                                                <img src="<?php echo html_escape($this->customlib->studentUploadPublicUrl($student['guardian_pic'])); ?>"
                                                                    class="img-thumbnail" width="100">
                                                            <?php } ?>
                                                            <input class="filestyle form-control" type='file'
                                                                name='guardian_pic' id="file" size='20' />
                                                        </div>
                                                        <span
                                                            class="text-danger"><?php echo form_error('guardian_pic'); ?></span>
                                                    </div>
                                                </div>
                                            <?php } ?>

                                            <?php if ($sch_setting->guardian_address) { ?>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label
                                                            for="guardian_address"><?php echo $this->lang->line('guardian_address'); ?></label>
                                                        <input id="guardian_address" name="guardian_address" placeholder=""
                                                            type="text" class="form-control"
                                                            value="<?php echo set_value('guardian_address', is_array($student['guardian_address']) ? implode(', ', $student['guardian_address']) : $student['guardian_address']); ?>" />
                                                        <span
                                                            class="text-danger"><?php echo form_error('guardian_address'); ?></span>
                                                    </div>
                                                </div>
                                            <?php } ?>

                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

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
                    <input type="hidden" name="ugc_t_province_name" value="<?php echo set_value('ugc_t_province', isset($student['ugc_t_province']) ? $student['ugc_t_province'] : ''); ?>">
                    <input type="hidden" name="ugc_t_district_name" value="<?php echo set_value('ugc_t_district', isset($student['ugc_t_district']) ? $student['ugc_t_district'] : ''); ?>">
                    <input type="hidden" name="ugc_t_local_level_name" value="<?php echo set_value('ugc_t_local_level', isset($student['ugc_t_local_level']) ? $student['ugc_t_local_level'] : ''); ?>">
                    <input type="hidden" name="ugc_t_combined_code" value="<?php echo set_value('ugc_t_combined_code', isset($student['ugc_t_combined_code']) ? $student['ugc_t_combined_code'] : ''); ?>">
                    <input type="hidden" name="ugc_p_province_name" value="<?php echo set_value('ugc_p_province', isset($student['ugc_p_province']) ? $student['ugc_p_province'] : ''); ?>">
                    <input type="hidden" name="ugc_p_district_name" value="<?php echo set_value('ugc_p_district', isset($student['ugc_p_district']) ? $student['ugc_p_district'] : ''); ?>">
                    <input type="hidden" name="ugc_p_local_level_name" value="<?php echo set_value('ugc_p_local_level', isset($student['ugc_p_local_level']) ? $student['ugc_p_local_level'] : ''); ?>">
                    <input type="hidden" name="ugc_p_combined_code" value="<?php echo set_value('ugc_p_combined_code', isset($student['ugc_p_combined_code']) ? $student['ugc_p_combined_code'] : ''); ?>">
                </div>
                <div class="row around10">
                <?php if ($sch_setting->current_address) { ?>
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
                                        <option value="<?php echo $province['id']; ?>" <?php echo (isset($student['temporary_province']) && $student['temporary_province'] == $province['id']) ? 'selected' : ''; ?>>
                                            <?php echo $province['name']; ?>
                                        </option>
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
                                    <?php if (isset($temporary_districts) && !empty($temporary_districts)) {
                                        foreach ($temporary_districts as $district) { ?>
                                            <option value="<?php echo $district['id']; ?>" <?php echo (isset($student['temporary_district']) && $student['temporary_district'] == $district['id']) ? 'selected' : ''; ?>>
                                                <?php echo $district['name']; ?>
                                            </option>
                                        <?php }
                                    } ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('temporary_district'); ?></span>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="temporary_local_level"><?php echo $this->lang->line('temporary_local_level'); ?></label>
                                <select id="temporary_local_level" name="temporary_local_level" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select_local_level'); ?></option>
                                    <?php if (isset($temporary_municipalities) && !empty($temporary_municipalities)) {
                                        foreach ($temporary_municipalities as $municipality) { ?>
                                            <option value="<?php echo $municipality['id']; ?>" <?php echo (isset($student['temporary_local_level']) && $student['temporary_local_level'] == $municipality['id']) ? 'selected' : ''; ?>>
                                                <?php echo $municipality['name']; ?>
                                            </option>
                                        <?php }
                                    } ?>
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
                                        <option value="<?php echo $i; ?>" <?php echo (isset($student['temporary_ward_no']) && $student['temporary_ward_no'] == $i) ? 'selected' : ''; ?>>
                                            <?php echo $i; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('temporary_ward_no'); ?></span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="current_address"><?php echo $this->lang->line('current_address'); ?></label>
                                <input id="current_address" name="current_address" placeholder="" type="text" class="form-control" value="<?php echo set_value('current_address', isset($student['current_address']) ? (is_array($student['current_address']) ? implode(', ', $student['current_address']) : $student['current_address']) : ''); ?>" />
                                <span class="text-danger"><?php echo form_error('current_address'); ?></span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('temporary_house_no'); ?></label>
                                <input id="temporary_house_no" name="temporary_house_no" placeholder="" type="text" class="form-control" value="<?php echo isset($student['temporary_house_no']) ? html_escape($student['temporary_house_no']) : ''; ?>" />
                                <span class="text-danger"><?php echo form_error('temporary_house_no'); ?></span>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php if ($sch_setting->permanent_address) { ?>
                    <div class="col-md-12">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="autofill_address" onclick="return auto_fill_address();">
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
                                        <option value="<?php echo $province['id']; ?>" <?php echo (isset($student['permanent_province']) && $student['permanent_province'] == $province['id']) ? 'selected' : ''; ?>>
                                            <?php echo $province['name']; ?>
                                        </option>
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
                                    <?php if (isset($permanent_districts) && !empty($permanent_districts)) {
                                        foreach ($permanent_districts as $district) { ?>
                                            <option value="<?php echo $district['id']; ?>" <?php echo (isset($student['permanent_district']) && $student['permanent_district'] == $district['id']) ? 'selected' : ''; ?>>
                                                <?php echo $district['name']; ?>
                                            </option>
                                        <?php }
                                    } ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('permanent_district'); ?></span>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="permanent_local_level"><?php echo $this->lang->line('permanent_local_level'); ?></label>
                                <select id="permanent_local_level" name="permanent_local_level" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select_local_level'); ?></option>
                                    <?php if (isset($permanent_municipalities) && !empty($permanent_municipalities)) {
                                        foreach ($permanent_municipalities as $municipality) { ?>
                                            <option value="<?php echo $municipality['id']; ?>" <?php echo (isset($student['permanent_local_level']) && $student['permanent_local_level'] == $municipality['id']) ? 'selected' : ''; ?>>
                                                <?php echo $municipality['name']; ?>
                                            </option>
                                        <?php }
                                    } ?>
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
                                        <option value="<?php echo $i; ?>" <?php echo (isset($student['permanent_ward_no']) && $student['permanent_ward_no'] == $i) ? 'selected' : ''; ?>>
                                            <?php echo $i; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('permanent_ward_no'); ?></span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="permanent_address"><?php echo $this->lang->line('permanent_address'); ?></label>
                                <input id="permanent_address" name="permanent_address" placeholder="" type="text" class="form-control" value="<?php echo set_value('permanent_address', isset($student['permanent_address']) ? (is_array($student['permanent_address']) ? implode(', ', $student['permanent_address']) : $student['permanent_address']) : ''); ?>" />
                                <span class="text-danger"><?php echo form_error('permanent_address'); ?></span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('permanent_house_no'); ?></label>
                                <input id="permanent_house_no" name="permanent_house_no" placeholder="" type="text" class="form-control" value="<?php echo isset($student['permanent_house_no']) ? html_escape($student['permanent_house_no']) : ''; ?>" />
                                <span class="text-danger"><?php echo form_error('permanent_house_no'); ?></span>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            </div>

            <div class="tshadow mb25 bozero">
                <h4 class="pagetitleh2"><?php echo $this->lang->line('miscellaneous_details'); ?></h4>
                <div class="around10">
                    <div class="row">
                        <?php if ($sch_setting->bank_account_no) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bank_account_no"><?php echo $this->lang->line('bank_account_number'); ?></label>
                                    <input id="bank_account_no" name="bank_account_no" placeholder="" type="text" class="form-control" value="<?php echo set_value('bank_account_no', is_array($student['bank_account_no']) ? implode(', ', $student['bank_account_no']) : $student['bank_account_no']); ?>" />
                                    <span class="text-danger"><?php echo form_error('bank_account_no'); ?></span>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if ($sch_setting->bank_name) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bank_name"><?php echo $this->lang->line('bank_name'); ?></label>
                                    <input id="bank_name" name="bank_name" placeholder="" type="text" class="form-control" value="<?php echo set_value('bank_name', is_array($student['bank_name']) ? implode(', ', $student['bank_name']) : $student['bank_name']); ?>" />
                                    <span class="text-danger"><?php echo form_error('bank_name'); ?></span>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if ($sch_setting->local_identification_no) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="samagra_id"><?php echo $this->lang->line('local_identification_number'); ?></label>
                                    <input id="samagra_id" name="samagra_id" placeholder="" type="text" class="form-control" value="<?php echo set_value('samagra_id', is_array($student['samagra_id']) ? implode(', ', $student['samagra_id']) : $student['samagra_id']); ?>" />
                                    <span class="text-danger"><?php echo form_error('samagra_id'); ?></span>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if ($sch_setting->rte) { ?>
                            <div class="col-md-4">
                                <label><?php echo $this->lang->line('rte'); ?></label>
                                <div class="radio" style="margin-top: 2px;">
                                    <label><input class="radio-inline" type="radio" name="rte" value="Yes" <?php echo set_value('rte', $student['rte']) == "Yes" ? "checked" : ""; ?>><?php echo $this->lang->line('yes'); ?></label>
                                    <label><input class="radio-inline" type="radio" name="rte" value="No" <?php echo set_value('rte', $student['rte']) == "No" ? "checked" : ""; ?>><?php echo $this->lang->line('no'); ?></label>
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
                                            <option value="<?php echo $bgvalue ?>" <?php if ($bgvalue == $student["blood_group"]) {
                                                   echo "selected";
                                               } ?>>
                                                <?php echo $bgvalue ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('blood_group'); ?></span>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-12" id="previous_education_container">
                            <?php 
                            // Get the arrays from student data - ensure they are arrays
                            $previous_levels = [];
                            $previous_boards = [];
                            $previous_registrations = [];
                            $previous_institutions = [];
                            
                            // Safely decode JSON data
                            if (!empty($student['previous_level_of_study'])) {
                                if (is_string($student['previous_level_of_study'])) {
                                    $previous_levels = json_decode($student['previous_level_of_study'], true) ?: [];
                                } else if (is_array($student['previous_level_of_study'])) {
                                    $previous_levels = $student['previous_level_of_study'];
                                }
                            }
                            
                            if (!empty($student['previous_board_university_college'])) {
                                if (is_string($student['previous_board_university_college'])) {
                                    $previous_boards = json_decode($student['previous_board_university_college'], true) ?: [];
                                } else if (is_array($student['previous_board_university_college'])) {
                                    $previous_boards = $student['previous_board_university_college'];
                                }
                            }
                            
                            if (!empty($student['previous_registration_no'])) {
                                if (is_string($student['previous_registration_no'])) {
                                    $previous_registrations = json_decode($student['previous_registration_no'], true) ?: [];
                                } else if (is_array($student['previous_registration_no'])) {
                                    $previous_registrations = $student['previous_registration_no'];
                                }
                            }
                            
                            if (!empty($student['previous_institution_name'])) {
                                if (is_string($student['previous_institution_name'])) {
                                    $previous_institutions = json_decode($student['previous_institution_name'], true) ?: [];
                                } else if (is_array($student['previous_institution_name'])) {
                                    $previous_institutions = $student['previous_institution_name'];
                                }
                            }
                            
                            // Determine the number of records to display
                            $max_count = max(
                                count($previous_levels),
                                count($previous_boards),
                                count($previous_registrations),
                                count($previous_institutions)
                            );
                            
                            // If no records exist, display one empty section
                            if ($max_count == 0) {
                                $max_count = 1;
                            }
                            
                            // Loop through each set of values
                            for ($i = 0; $i < $max_count; $i++): 
                                $level = isset($previous_levels[$i]) ? $previous_levels[$i] : '';
                                $board = isset($previous_boards[$i]) ? $previous_boards[$i] : '';
                                $registration = isset($previous_registrations[$i]) ? $previous_registrations[$i] : '';
                                $institution = isset($previous_institutions[$i]) ? $previous_institutions[$i] : '';
                            ?>
                            <div class="row previous-education-section mb-3">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="previous_level_of_study_<?php echo $i; ?>"><?php echo $this->lang->line('previous_level_of_study'); ?></label>
                                        <select id="previous_level_of_study_<?php echo $i; ?>" name="previous_level_of_study[]" class="form-control">
                                            <option value="">Select Level</option>
                                            <option value="SEE/SLC" <?php echo ($level == 'SEE/SLC') ? 'selected' : ''; ?>>SEE/SLC</option>
                                            <option value="+2" <?php echo ($level == '+2') ? 'selected' : ''; ?>>+2</option>
                                            <option value="Bachelors" <?php echo ($level == 'Bachelors') ? 'selected' : ''; ?>>Bachelors</option>
                                            <option value="Masters" <?php echo ($level == 'Masters') ? 'selected' : ''; ?>>Masters</option>
                                            <option value="CTEVT" <?php echo ($level == 'CTEVT') ? 'selected' : ''; ?>>CTEVT</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="previous_board_university_college_<?php echo $i; ?>"><?php echo $this->lang->line('previous_board_university_college'); ?></label>
                                        <select id="previous_board_university_college_<?php echo $i; ?>" name="previous_board_university_college[]" class="form-control">
                                            <option value="">Select Board/University/College</option>
                                            <option value="Tribhuvan University(TU)" <?php echo ($board == 'Tribhuvan University(TU)') ? 'selected' : ''; ?>>Tribhuvan University (TU)</option>
                                            <option value="Pokhara University(PU)" <?php echo ($board == 'Pokhara University(PU)') ? 'selected' : ''; ?>>Pokhara University (PU)</option>
                                            <option value="Purbanchal University(PU)" <?php echo ($board == 'Purbanchal University(PU)') ? 'selected' : ''; ?>>Purbanchal University (PU)</option>
                                            <option value="Kathmandu University(KU)" <?php echo ($board == 'Kathmandu University(KU)') ? 'selected' : ''; ?>>Kathmandu University (KU)</option>
                                            <option value="Nepal Sankrit University" <?php echo ($board == 'Nepal Sankrit University') ? 'selected' : ''; ?>>Nepal Sankrit University</option>
                                            <option value="Agriculture and Forestry University" <?php echo ($board == 'Agriculture and Forestry University') ? 'selected' : ''; ?>>Agriculture and Forestry University</option>
                                            <option value="Mid-Western University" <?php echo ($board == 'Mid-Western University') ? 'selected' : ''; ?>>Mid-Western University</option>
                                            <option value="Far-Western University" <?php echo ($board == 'Far-Western University') ? 'selected' : ''; ?>>Far-Western University</option>
                                            <option value="National Examination Board (NEB)" <?php echo ($board == 'National Examination Board (NEB)') ? 'selected' : ''; ?>>National Examination Board (NEB)</option>
                                            <option value="CTEVT" <?php echo ($board == 'CTEVT') ? 'selected' : ''; ?>>CTEVT</option>
                                            <option value="Other" <?php echo ($board == 'Other') ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="previous_registration_no_<?php echo $i; ?>"><?php echo $this->lang->line('previous_registration_no'); ?></label>
                                        <input id="previous_registration_no_<?php echo $i; ?>" name="previous_registration_no[]" placeholder="" type="text" class="form-control" value="<?php echo html_escape($registration); ?>" />
                                    </div>
                                </div>

                                <?php if ($sch_setting->previous_school_details) { ?>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="previous_institution_name_<?php echo $i; ?>"><?php echo $this->lang->line('previous_school_details'); ?></label>
                                        <input id="previous_institution_name_<?php echo $i; ?>" name="previous_institution_name[]" placeholder="" type="text" class="form-control" value="<?php echo html_escape($institution); ?>" />
                                    </div>
                                </div>
                                <?php } ?>

                                <div class="col-md-1" style="padding-top: 30px;">
                                    <?php if ($i == 0): ?>
                                        <button type="button" class="btn btn-success btn-sm add-previous-education"><i class="fa fa-plus"></i></button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-danger btn-sm remove-previous-education"><i class="fa fa-minus"></i></button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>



                        <?php if ($sch_setting->student_note) { ?>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="note"><?php echo $this->lang->line('note'); ?></label>
                                    <textarea class="form-control" rows="3" name="note"><?php echo set_value('note', is_array($student['note']) ? implode(', ', $student['note']) : $student['note']); ?></textarea>
                                    <span class="text-danger"><?php echo form_error('note'); ?></span>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div id='upload_documents_hide_show'>
    <?php if ($sch_setting->upload_documents) { ?>
        <div class="row">
            <div class="col-md-12">
                <div class="tshadow bozero">
                    <h4 class="pagetitleh2"><?php echo $this->lang->line('upload_documents'); ?></h4>
                    <div class="row around10">
                        <div class="col-md-12 mb-10">
                            <button type="button" id="add-document" class="btn btn-success btn-sm pull-left">
                                <i class="fa fa-plus"></i>
                                <?php echo $this->lang->line('add_document'); ?>
                            </button>
                            <button type="button" id="remove-selected-docs" class="btn btn-danger btn-sm pull-right">
                                <i class="fa fa-trash"></i>
                                <?php echo $this->lang->line('remove_selected'); ?>
                            </button>
                        </div>

                        <div class="col-md-6">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th style="width: 10px">#</th>
                                        <th><?php echo $this->lang->line('title'); ?></th>
                                        <th><?php echo $this->lang->line('documents'); ?></th>
                                        <th><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                    <tr class="document-row" data-doc-id="<?php echo isset($student_docs[0]) ? $student_docs[0]['id'] : ''; ?>">
                                        <td>
                                            <?php if (!empty($student_docs[0]['id'])) { ?>
                                                <input type="checkbox" class="doc-checkbox" value="<?php echo $student_docs[0]['id']; ?>">
                                            <?php } ?>
                                            1.
                                        </td>
                                        <td>
                                            <?php
                                            $first_title = "";
                                            $first_doc = "";
                                            $first_doc_id = "";

                                            if (isset($student_docs[0])) {
                                                $first_title = $student_docs[0]['title'];
                                                $first_doc = $student_docs[0]['doc'];
                                                $first_doc_id = $student_docs[0]['id'];
                                            }
                                            ?>
                                            <input type="text" name='first_title' class="form-control" value="<?php echo $first_title; ?>" placeholder="">
                                            <input type="hidden" name="first_doc_id" value="<?php echo $first_doc_id; ?>">
                                        </td>
                                        <td>
                                            <input class="filestyle form-control" type='file' name='first_doc' id="doc1">
                                            <?php if (!empty($first_doc)) { ?>
                                                <input type="hidden" name="first_doc_old" value="<?php echo $first_doc; ?>">
                                                <a href="<?php echo html_escape($this->customlib->studentDocumentPublicUrl($student['id'], $first_doc)); ?>" target="_blank" rel="noopener noreferrer">
                                                    <span class="text-success"><?php echo $first_doc; ?></span>
                                                </a>
                                            <?php } ?>
                                            <span class="text-danger"><?php echo form_error('first_doc'); ?></span>
                                        </td>
                                        <td>
                                            <?php if (!empty($first_doc_id)) { ?>
                                                <button type="button" class="btn btn-danger btn-xs" onclick="removeDocument(<?php echo $first_doc_id; ?>, this)">
                                                    <i class="fa fa-trash"></i>
                                                    <?php echo $this->lang->line('remove'); ?>
                                                </button>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <tr class="document-row" data-doc-id="<?php echo isset($student_docs[1]) ? $student_docs[1]['id'] : ''; ?>">
                                        <td>
                                            <?php if (!empty($student_docs[1]['id'])) { ?>
                                                <input type="checkbox" class="doc-checkbox" value="<?php echo $student_docs[1]['id']; ?>">
                                            <?php } ?>
                                            2.
                                        </td>
                                        <td>
                                            <?php
                                            $second_title = "";
                                            $second_doc = "";
                                            $second_doc_id = "";

                                            if (isset($student_docs[1])) {
                                                $second_title = $student_docs[1]['title'];
                                                $second_doc = $student_docs[1]['doc'];
                                                $second_doc_id = $student_docs[1]['id'];
                                            }
                                            ?>
                                            <input type="text" name='second_title' class="form-control" value="<?php echo $second_title; ?>" placeholder="">
                                            <input type="hidden" name="second_doc_id" value="<?php echo $second_doc_id; ?>">
                                        </td>
                                        <td>
                                            <input class="filestyle form-control" type='file' name='second_doc' id="doc2">
                                            <?php if (!empty($second_doc)) { ?>
                                                <input type="hidden" name="second_doc_old" value="<?php echo $second_doc; ?>">
                                                <a href="<?php echo html_escape($this->customlib->studentDocumentPublicUrl($student['id'], $second_doc)); ?>" target="_blank" rel="noopener noreferrer">
                                                    <span class="text-success"><?php echo $second_doc; ?></span>
                                                </a>
                                            <?php } ?>
                                            <span class="text-danger"><?php echo form_error('second_doc'); ?></span>
                                        </td>
                                        <td>
                                            <?php if (!empty($second_doc_id)) { ?>
                                                <button type="button" class="btn btn-danger btn-xs" onclick="removeDocument(<?php echo $second_doc_id; ?>, this)">
                                                    <i class="fa fa-trash"></i>
                                                    <?php echo $this->lang->line('remove'); ?>
                                                </button>
                                            <?php } ?>
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
                                        <th><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                    <tr class="document-row" data-doc-id="<?php echo isset($student_docs[2]) ? $student_docs[2]['id'] : ''; ?>">
                                        <td>
                                            <?php if (!empty($student_docs[2]['id'])) { ?>
                                                <input type="checkbox" class="doc-checkbox" value="<?php echo $student_docs[2]['id']; ?>">
                                            <?php } ?>
                                            3.
                                        </td>
                                        <td>
                                            <?php
                                            $fourth_title = "";
                                            $fourth_doc = "";
                                            $fourth_doc_id = "";

                                            if (isset($student_docs[2])) {
                                                $fourth_title = $student_docs[2]['title'];
                                                $fourth_doc = $student_docs[2]['doc'];
                                                $fourth_doc_id = $student_docs[2]['id'];
                                            }
                                            ?>
                                            <input type="text" name='fourth_title' class="form-control" value="<?php echo $fourth_title; ?>" placeholder="">
                                            <input type="hidden" name="fourth_doc_id" value="<?php echo $fourth_doc_id; ?>">
                                        </td>
                                        <td>
                                            <input class="filestyle form-control" type='file' name='fourth_doc' id="doc3">
                                            <?php if (!empty($fourth_doc)) { ?>
                                                <input type="hidden" name="fourth_doc_old" value="<?php echo $fourth_doc; ?>">
                                                <a href="<?php echo html_escape($this->customlib->studentDocumentPublicUrl($student['id'], $fourth_doc)); ?>" target="_blank" rel="noopener noreferrer">
                                                    <span class="text-success"><?php echo $fourth_doc; ?></span>
                                                </a>
                                            <?php } ?>
                                            <span class="text-danger"><?php echo form_error('fourth_doc'); ?></span>
                                        </td>
                                        <td>
                                            <?php if (!empty($fourth_doc_id)) { ?>
                                                <button type="button" class="btn btn-danger btn-xs" onclick="removeDocument(<?php echo $fourth_doc_id; ?>, this)">
                                                    <i class="fa fa-trash"></i>
                                                    <?php echo $this->lang->line('remove'); ?>
                                                </button>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <tr class="document-row" data-doc-id="<?php echo isset($student_docs[3]) ? $student_docs[3]['id'] : ''; ?>">
                                        <td>
                                            <?php if (!empty($student_docs[3]['id'])) { ?>
                                                <input type="checkbox" class="doc-checkbox" value="<?php echo $student_docs[3]['id']; ?>">
                                            <?php } ?>
                                            4.
                                        </td>
                                        <td>
                                            <?php
                                            $fifth_title = "";
                                            $fifth_doc = "";
                                            $fifth_doc_id = "";

                                            if (isset($student_docs[3])) {
                                                $fifth_title = $student_docs[3]['title'];
                                                $fifth_doc = $student_docs[3]['doc'];
                                                $fifth_doc_id = $student_docs[3]['id'];
                                            }
                                            ?>
                                            <input type="text" name='fifth_title' class="form-control" value="<?php echo $fifth_title; ?>" placeholder="">
                                            <input type="hidden" name="fifth_doc_id" value="<?php echo $fifth_doc_id; ?>">
                                        </td>
                                        <td>
                                            <input class="filestyle form-control" type='file' name='fifth_doc' id="doc4">
                                            <?php if (!empty($fifth_doc)) { ?>
                                                <input type="hidden" name="fifth_doc_old" value="<?php echo $fifth_doc; ?>">
                                                <a href="<?php echo html_escape($this->customlib->studentDocumentPublicUrl($student['id'], $fifth_doc)); ?>" target="_blank" rel="noopener noreferrer">
                                                    <span class="text-success"><?php echo $fifth_doc; ?></span>
                                                </a>
                                            <?php } ?>
                                            <span class="text-danger"><?php echo form_error('fifth_doc'); ?></span>
                                        </td>
                                        <td>
                                            <?php if (!empty($fifth_doc_id)) { ?>
                                                <button type="button" class="btn btn-danger btn-xs" onclick="removeDocument(<?php echo $fifth_doc_id; ?>, this)">
                                                    <i class="fa fa-trash"></i>
                                                    <?php echo $this->lang->line('remove'); ?>
                                                </button>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <!-- Dynamic documents will be added here -->
                                    <?php if (isset($student_docs) && count($student_docs) > 4) { ?>
                                        <?php for ($i = 4; $i < count($student_docs); $i++) { ?>
                                            <tr class="document-row dynamic-document" data-doc-id="<?php echo $student_docs[$i]['id']; ?>">
                                                <td>
                                                    <input type="checkbox" class="doc-checkbox" value="<?php echo $student_docs[$i]['id']; ?>">
                                                    <?php echo ($i + 1); ?>.
                                                </td>
                                                <td>
                                                    <input type="text" name='dynamic_title[]' class="form-control" value="<?php echo $student_docs[$i]['title']; ?>" placeholder="">
                                                    <input type="hidden" name="dynamic_doc_id[]" value="<?php echo $student_docs[$i]['id']; ?>">
                                                </td>
                                                <td>
                                                    <input class="filestyle form-control" type='file' name='dynamic_doc[]'>
                                                    <?php if (!empty($student_docs[$i]['doc'])) { ?>
                                                        <input type="hidden" name="dynamic_doc_old[]" value="<?php echo $student_docs[$i]['doc']; ?>">
                                                        <a href="<?php echo html_escape($this->customlib->studentDocumentPublicUrl($student['id'], $student_docs[$i]['doc'])); ?>" target="_blank" rel="noopener noreferrer">
                                                            <span class="text-success"><?php echo $student_docs[$i]['doc']; ?></span>
                                                        </a>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-xs remove-dynamic-document">
                                                        <i class="fa fa-trash"></i>
                                                        <?php echo $this->lang->line('remove'); ?>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="tshadow bozero" style="margin-top:10px;">
            <h5 class="pagetitleh2" style="font-size:14px;margin-top:0;">HEMIS / registration documents <span class="label label-info">HEMIS</span></h5>
            <p class="text-muted small">Optional scans for central sync. Student profile photo stays under Basic details.</p>
            <div class="row around10">
                <?php
                $hemis_doc_fields = array(
                    'pp_size_photo'     => 'PP size photo',
                    'citizenship_front' => 'Citizenship front',
                    'citizenship_back'  => 'Citizenship back',
                    'nid_pic'           => 'NID pic',
                    'digital_signature' => 'Digital signature',
                    'receipt_image'     => 'Receipt image',
                );
                $hemis_doc_image_exts = array('jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp');
                foreach ($hemis_doc_fields as $doc_field => $doc_label):
                    $doc_val = isset($student[$doc_field]) ? trim((string) $student[$doc_field]) : '';
                    $doc_url = ($doc_val !== '') ? $this->customlib->studentUploadPublicUrl($doc_val) : '';
                    $doc_ext = ($doc_val !== '') ? strtolower(pathinfo($doc_val, PATHINFO_EXTENSION)) : '';
                ?>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="<?php echo $doc_field; ?>"><?php echo $doc_label; ?></label>
                        <?php if ($doc_url !== ''): ?>
                            <div class="mb5" style="margin-bottom:6px;">
                                <?php if (in_array($doc_ext, $hemis_doc_image_exts)): ?>
                                    <a href="<?php echo html_escape($doc_url); ?>" target="_blank" rel="noopener noreferrer">
                                        <img src="<?php echo html_escape($doc_url); ?>" alt="<?php echo html_escape($doc_label); ?>" style="max-height:70px;max-width:100%;border:1px solid #ddd;padding:2px;border-radius:3px;" />
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo html_escape($doc_url); ?>" target="_blank" rel="noopener noreferrer" class="text-success"><i class="fa fa-file-o"></i> View current file</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <input id="<?php echo $doc_field; ?>" name="<?php echo $doc_field; ?>" type="file" class="form-control filestyle" accept=".jpg,.jpeg,.png,.webp,.pdf" />
                        <span class="text-danger"><?php echo form_error($doc_field); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
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
                <input id="entrance_score" name="entrance_score" type="text" class="form-control" value="<?php echo set_value('entrance_score', isset($student['entrance_score']) ? $student['entrance_score'] : ''); ?>" />
                <span class="text-danger"><?php echo form_error('entrance_score'); ?></span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="entrance_symbol">Entrance Symbol</label>
                <input id="entrance_symbol" name="entrance_symbol" type="text" class="form-control" value="<?php echo set_value('entrance_symbol', isset($student['entrance_symbol']) ? $student['entrance_symbol'] : ''); ?>" />
                <span class="text-danger"><?php echo form_error('entrance_symbol'); ?></span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="medium">Medium</label>
                <input id="medium" name="medium" type="text" class="form-control" value="<?php echo set_value('medium', isset($student['medium']) ? $student['medium'] : ''); ?>" />
                <span class="text-danger"><?php echo form_error('medium'); ?></span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="shift">Shift</label>
                <input id="shift" name="shift" type="text" class="form-control" value="<?php echo set_value('shift', isset($student['shift']) ? $student['shift'] : ''); ?>" />
                <span class="text-danger"><?php echo form_error('shift'); ?></span>
            </div>
        </div>
    </div>
    <div class="row around10">
        <div class="col-md-3">
            <div class="form-group">
                <label for="type">Type</label>
                <input id="type" name="type" type="text" class="form-control" value="<?php echo set_value('type', isset($student['type']) ? $student['type'] : ''); ?>" />
                <span class="text-danger"><?php echo form_error('type'); ?></span>
            </div>
        </div>
    </div>
    <div class="row around10">
        <div class="col-md-3">
            <div class="form-group">
                <label for="father_email">Father Email</label>
                <input id="father_email" name="father_email" type="email" class="form-control" value="<?php echo set_value('father_email', isset($student['father_email']) ? $student['father_email'] : ''); ?>" />
                <span class="text-danger"><?php echo form_error('father_email'); ?></span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="mother_email">Mother Email</label>
                <input id="mother_email" name="mother_email" type="email" class="form-control" value="<?php echo set_value('mother_email', isset($student['mother_email']) ? $student['mother_email'] : ''); ?>" />
                <span class="text-danger"><?php echo form_error('mother_email'); ?></span>
            </div>
        </div>
    </div>
    <div class="row around10">
        <div class="col-md-3">
            <div class="checkbox">
                <label><input type="hidden" name="is_verified" value="0"><input type="checkbox" name="is_verified" value="1" <?php echo set_checkbox('is_verified', '1', isset($student['is_verified']) && $student['is_verified'] == 1); ?>> Is Verified</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="checkbox">
                <label><input type="hidden" name="has_scolorship" value="0"><input type="checkbox" name="has_scolorship" value="1" <?php echo set_checkbox('has_scolorship', '1', isset($student['has_scolorship']) && $student['has_scolorship'] == 1); ?>> Has Scholarship</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="checkbox">
                <label><input type="hidden" name="is_mukta_kamaiya" value="0"><input type="checkbox" name="is_mukta_kamaiya" value="1" <?php echo set_checkbox('is_mukta_kamaiya', '1', isset($student['is_mukta_kamaiya']) && $student['is_mukta_kamaiya'] == 1); ?>> Is Mukta Kamaiya</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="checkbox">
                <label><input type="hidden" name="is_from_martyr_family" value="0"><input type="checkbox" name="is_from_martyr_family" value="1" <?php echo set_checkbox('is_from_martyr_family', '1', isset($student['is_from_martyr_family']) && $student['is_from_martyr_family'] == 1); ?>> Is From Martyr Family</label>
            </div>
        </div>
    </div>
</div>
                                </div>
                            </div>
                            <div class="box-footer">
                                <button type="submit" id="submitbtn"
                                    class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
<script type="text/javascript">
    function removeDocument(docId, button) {
        if (docId) {
            $.ajax({
                url: '<?php echo site_url("student/removeDocument"); ?>',
                type: 'POST',
                data: {
                    doc_id: docId,
                    <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
                },
                dataType: 'json',
                success: function (response) {
                    if (response.status) {
                        // Find the row containing the button
                        var row = $(button).closest('tr');

                        // Clear document fields
                        row.find('input[type=text]').val('');
                        row.find('input[type=hidden]').val('');
                        row.find('a').hide();

                        // Hide the remove button
                        $(button).hide();

                        // Remove checkbox if exists
                        row.find('.doc-checkbox').remove();

                        // Show success notification
                        successMsg(response.message);
                    } else {
                        errorMsg(response.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.log(xhr.responseText);
                    errorMsg('<?php echo $this->lang->line("something_went_wrong"); ?>');
                }
            });
        }
    }

    // New function to remove multiple documents
    function removeMultipleDocuments(docIds) {
        $.ajax({
            url: '<?php echo site_url("student/removeMultipleDocuments"); ?>',
            type: 'POST',
            data: {
                doc_ids: docIds.join(','),
                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function (response) {
                if (response.status) {
                    // For each removed document, clear the corresponding row
                    $.each(docIds, function (index, docId) {
                        var checkbox = $('.doc-checkbox[value="' + docId + '"]');
                        var row = checkbox.closest('tr');

                        // Clear document fields
                        row.find('input[type=text]').val('');
                        row.find('input[type=hidden]').val('');
                        row.find('a').hide();

                        // Hide the remove button
                        row.find('.btn-danger').hide();

                        // Remove the checkbox
                        checkbox.remove();
                    });

                    // Show success notification
                    successMsg(response.message);
                } else {
                    errorMsg(response.message);
                }
            },
            error: function (xhr, status, error) {
                console.log(xhr.responseText);
                errorMsg('<?php echo $this->lang->line("something_went_wrong"); ?>');
            }
        });
    }

    // Initialize when document is ready
    $(document).ready(function () {
        // Handle "Remove Selected" button click
        $('#remove-selected-docs').click(function () {
            var selectedDocs = [];
            $('.doc-checkbox:checked').each(function () {
                selectedDocs.push($(this).val());
            });

            if (selectedDocs.length === 0) {
                errorMsg('<?php echo $this->lang->line("no_document_selected"); ?>');
                return;
            }

            if (confirm('<?php echo $this->lang->line("are_you_sure_delete_selected_documents"); ?>')) {
                removeMultipleDocuments(selectedDocs);
            }
        });
    });
</script>
<script type="text/javascript">

    $(document).ready(function () {
        var date_format = '<?php echo $result = strtr($this->customlib->getSchoolDateFormat(), ['d' => 'dd', 'm' => 'mm', 'Y' => 'yyyy']) ?>';
        var class_id = $('#class_id').val();
        var section_id = '<?php echo set_value('section_id', $student['section_id']) ?>';
        var hostel_id = $('#hostel_id').val();
        var hostel_room_id = '<?php echo set_value('hostel_room_id', $student['hostel_room_id']) ?>';
        var vehroute_id = '<?php echo set_value('vehroute_id', $student['vehroute_id']) ?>';
        var route_pickup_point_id = '<?php echo set_value('route_pickup_point_id', $student['route_pickup_point_id']) ?>';
        getHostel(hostel_id, hostel_room_id);
        get_pickup_point(vehroute_id, route_pickup_point_id);


        $(document).on('click', '#sibiling_class_id', function () {
            var class_id = $(this).val();
            getSectionByClass(class_id, 0, 'sibiling_section_id');
        });

        $("#btnreset").click(function () {
            $("#form1")[0].reset();
        });

        $(document).on('change', '#hostel_id', function (e) {
            var hostel_id = $(this).val();
            getHostel(hostel_id, 0);
        });

        $(document).on('change', '#sibiling_section_id', function (e) {
            getStudentsByClassAndSection();
        });

        function getStudentsByClassAndSection() {
            $('#sibiling_student_id').html("");
            var class_id = $('#sibiling_class_id').val();
            var section_id = $('#sibiling_section_id').val();
            var current_student_id = $('.current_student_id').val();
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';

            $.ajax({
                type: "GET",
                url: baseurl + "student/getByClassAndSectionExcludeMe",
                data: { 'class_id': class_id, 'section_id': section_id, 'current_student_id': current_student_id },
                dataType: "json",
                beforeSend: function () {
                    $('#sibiling_student_id').addClass('dropdownloading');
                },
                success: function (data) {
                    $.each(data, function (i, obj) {
                        var sel = "";
                        if (section_id == obj.section_id) {
                            sel = "selected=selected";
                        }

                        if (obj.admission_no == null) {
                            div_data += "<option value=" + obj.id + ">" + obj.full_name + "</option>";
                        } else {
                            div_data += "<option value=" + obj.id + ">" + obj.full_name + " (" + obj.admission_no + ") " + "</option>";
                        }
                    });
                    $('#sibiling_student_id').append(div_data);
                },
                complete: function () {
                    $('#sibiling_student_id').removeClass('dropdownloading');
                }
            });
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
                    data: { 'hostel_id': hostel_id },
                    dataType: "json",
                    beforeSend: function () {
                        $('#hostel_room_id').addClass('dropdownloading');
                    },
                    success: function (data) {
                        $.each(data, function (i, obj) {
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
        if ($("#autofill_current_address").is(':checked')) {
            $('#current_address').val($('#guardian_address').val());
        }
    }

    function auto_fill_address() {
        if ($("#autofill_address").is(':checked')) {
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

<!-- <div class="modal" id="mySiblingModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title modal_sibling_title"></h4>
            </div>
            <div class="modal-body pb0 modal_sibling_body pr-2">
                <div class="form-horizontal">
                    <input type="hidden" name="current_student_id" class="current_student_id" value="0">
                    <div class="sibling_msg">
                    </div>
                    <div class="sibling_content">
                        <div class="col-lg-12">
                            <div class="form-group">

                                <label for="inputEmail3"
                                    class="col-sm-2 control-label"><?php echo $this->lang->line('class'); ?></label>
                                <div class="col-sm-10">
                                    <select id="sibiling_class_id" name="sibiling_class_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
                                        foreach ($classlist as $class) {
                                            ?>
                                            <option value="<?php echo $class['id'] ?>" <?php
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
                                <label for="inputPassword3"
                                    class="col-sm-2 control-label"><?php echo $this->lang->line('section'); ?></label>
                                <div class="col-sm-10">
                                    <select id="sibiling_section_id" name="sibiling_section_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>

                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputPassword3"
                                    class="col-sm-2 control-label"><?php echo $this->lang->line('student'); ?>
                                </label>
                                <div class="col-sm-10">
                                    <select id="sibiling_student_id" name="sibiling_student_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer clearboth">
                <button type="button" class="btn btn-primary btn-sm add_sibling" id="load"
                    data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Processing"><i
                        class="fa fa-user"></i> <?php echo $this->lang->line('add'); ?></button>
            </div>
        </div>
    </div>
</div> -->

<div class="modal" id="deleteModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title del_modal_title"></h4>
            </div>
            <div class="modal-hidden">
                <input type="hidden" name="id" value="0" class="hd_input">
            </div>
            <div class="modal-body del_modal_body">
            </div>
            <div class="modal-footer">
                <button type="button"
                    class="btn btn-sm btn-primary delete_confirm"><?php echo $this->lang->line('confirm'); ?></button>
                <button type="button" class="btn btn-sm btn-default"
                    data-dismiss="modal"><?php echo $this->lang->line('cancel'); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    $('#deleteModal').on('shown.bs.modal', function () {
        $(".del_modal_title").html("<?php echo $this->lang->line('delete_confirm') ?>");
        $(".del_modal_body").html("<p><?php echo $this->lang->line('are_you_sure_you_want_to_remove_sibling'); ?></p>");
    })

    $(document).on('click', '.remove_sibling', function () {
        $('#deleteModal').modal('show');
    });

    $(document).on('click', '.add_sibling', function () {
        var student_id = $('#sibiling_student_id').val();
        if (student_id.length == '') {
            $('.sibling_msg').html("<div class='alert alert-danger text-center'> <?php echo $this->lang->line('no_student_selected'); ?> </div>");

        } else {
            var $this = $(this);

            $.ajax({
                type: "GET",
                url: baseurl + "student/getStudentRecordByID",
                data: { 'student_id': student_id },
                dataType: "json",
                beforeSend: function () {
                    $this.button('loading');
                },
                success: function (data) {

                    $('#sibling_name').text("<?php echo $this->lang->line('sibling'); ?>: " + data.full_name);
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
                },
                complete: function () {
                    $this.button('reset');
                }
            });
        }
    });

    $(document).on('click', '.mysiblings', function () {
        $('#mySiblingModal').modal('show');
    });

    $('#mySiblingModal').on('shown.bs.modal', function () {
        $('.sibling_msg').html("");
        $('.modal_sibling_title').html('<b>' + "<?php echo $this->lang->line('sibling'); ?>" + '</b>');
        $('.current_student_id').val($("input[name='student_id']").val());
        if ($('.siblings_counts').length && $('.siblings_counts').val().length) {
            var msg = "";
            msg += "<div class='alert alert-danger text-center'>";
            msg += "<?php echo $this->lang->line('please_remove_previous_siblings'); ?>";
            msg += "</div>";
            $('.sibling_msg').html(msg);
            $(".sibling_content, .modal-footer", this).css("display", "none");
        } else {
            $(".sibling_content, .modal-footer", this).css("display", "block");
        }
    });

    $(document).ready(function () {

        $('#mySiblingModal').modal({
            backdrop: 'static',
            keyboard: false,
            show: false
        });

        $('#deleteModal').modal({
            backdrop: 'static',
            keyboard: false,
            show: false
        });

        $(document).on('click', '.delete_confirm', function () {
            $('#deleteModal').modal('hide');
            $('.sibling_div').remove();
        });
    });
</script>

<script>

    $('#specialistOpt').multiselect({
        columns: 1,
        placeholder: '<?php echo $this->lang->line('select_month'); ?>',
        search: true
    });

    $(document).on('change', '#vehroute_id', function () {

        var vehroute_id = $(this).val();
        get_pickup_point(vehroute_id, 0);
    });

    function get_pickup_point(vehroute_id, pickuppoint_id) {
        if (vehroute_id != "") {

            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.ajax({
                url: baseurl + 'admin/pickuppoint/get_pickupdropdownlist',
                type: "POST",
                data: { vehroute_id: vehroute_id },
                dataType: 'json',
                beforeSend: function () {
                    $('#pickup_point').html('');
                },
                success: function (res) {

                    $.each(res, function (index, value) {
                        var sel = "";
                        if (pickuppoint_id == value.route_pickup_point_id) {
                            sel = "selected";
                        }

                        div_data += "<option  value=" + value.route_pickup_point_id + " " + sel + ">" + value.name + "</option>";
                    });

                    $('#pickup_point').html(div_data);
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

    // Get stored values (session placement — must match sections/programs/classes IDs as strings)
    var stored_section_id = <?php echo json_encode((string) set_value('section_id', isset($student['section_id']) ? $student['section_id'] : '')); ?>;
    var stored_program_id = <?php echo json_encode((string) set_value('program_id', isset($student['program_id']) ? $student['program_id'] : '')); ?>;
    var stored_class_id = <?php echo json_encode((string) set_value('class_id', isset($student['class_id']) ? $student['class_id'] : '')); ?>;
    
    loadUgcPrograms();
    loadUgcFiscalYears();
    loadUgcBatches();
    setTimeout(function () { refreshSemisterOrYearNoFromClass(); }, 800);

    // Fetch all sections
    $.ajax({
        url: '<?php echo base_url(); ?>sections/getAll',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
            
            $.each(response, function (index, section) {
                var selected = (String(stored_section_id) === String(section.id)) ? 'selected' : '';
                html += '<option value="' + section.id + '" ' + selected + '>' + section.section + '</option>';
            });
            
            $('#section_id').html(html);
            
            // If there's a stored section, load programs
            if(stored_section_id) {
                loadPrograms(stored_section_id, stored_program_id, stored_class_id);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error fetching sections:", error);
            $('#section_id').html('<option value="">Error loading sections</option>');
        }
    });
});

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
    var $opt = $('#program_id option').filter(function () { return String($(this).val()) === want; });
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

            __campusFromUgc.program_id = d.program_id ? String(d.program_id) : '';
            __campusFromUgc.active = true;
            if (String($('#section_id').val()) === String(d.section_id)) {
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
        syncUgcProgramDisplays();
    } else {
        if ($hint.length) $hint.text('');
    }
}

function loadUgcFiscalYears(preselect_ugc_fiscal_year_id) {
    $('#ugc_fiscal_year_id').html('<option value="">Loading...</option>');
    $.ajax({
        url: '<?php echo base_url(); ?>student/ugc_fiscal_years',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            var stored_ugc_fiscal_year_id = preselect_ugc_fiscal_year_id || <?php echo json_encode((string) set_value('ugc_fiscal_year_id', isset($student['ugc_fiscal_year_id']) ? $student['ugc_fiscal_year_id'] : '')); ?>;
            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
            var rows = (response && response.data) ? response.data : [];
            if (rows && rows.length > 0) {
                if (!stored_ugc_fiscal_year_id) {
                    var active = null;
                    $.each(rows, function (_, fy) {
                        if (fy && fy.activeFiscalYear) {
                            active = fy;
                            return false;
                        }
                    });
                    if (active && active.id) {
                        stored_ugc_fiscal_year_id = String(active.id);
                    }
                }
                $.each(rows, function (index, fy) {
                    var label = (fy.yearNepali || fy.yearEnglish || '');
                    var sel = (String(stored_ugc_fiscal_year_id) === String(fy.id)) ? 'selected' : '';
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

function loadUgcBatches(preselect_ugc_batch_id) {
    $('#ugc_batch_id').html('<option value="">Loading...</option>');
    $.ajax({
        url: '<?php echo base_url(); ?>student/ugc_batches',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            var stored_ugc_batch_id = preselect_ugc_batch_id || <?php echo json_encode((string) set_value('ugc_batch_id', isset($student['ugc_batch_id']) ? $student['ugc_batch_id'] : '')); ?>;
            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
            var rows = (response && response.data) ? response.data : [];
            if (rows && rows.length > 0) {
                $.each(rows, function (index, batch) {
                    var label = (batch.batchNepali || batch.name || '');
                    var sel = (String(stored_ugc_batch_id) === String(batch.id)) ? 'selected' : '';
                    var dataName = (batch.name || '');
                    var dataNep = (batch.batchNepali || '');
                    html += '<option value="' + batch.id + '" data-name="' + $('<div>').text(dataName).html() + '" data-nepali="' + $('<div>').text(dataNep).html() + '" ' + sel + '>' + label + '</option>';
                });
            } else {
                html += '<option value=\"\" disabled>No UGC batches found</option>';
            }
            $('#ugc_batch_id').html(html);
            syncBatchNamesFromUgc();
            tryAutoselectUgcBatchFromAdmissionYear();
        },
        error: function () {
            $('#ugc_batch_id').html('<option value=\"\">Error loading UGC batches</option>');
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
    if ($batch.val()) return;

    var ay = ($ay.val() || '').toString();
    if (!ay) return;

    var matches = $batch.find('option[data-nepali]').filter(function () {
        return String($(this).data('nepali')) === ay;
    });
    if (matches.length === 1) {
        $batch.val(matches.first().val());
        syncBatchNamesFromUgc();
    }
}

$('#admission_year').off('change.ugcBatchAuto').on('change.ugcBatchAuto', function () {
    tryAutoselectUgcBatchFromAdmissionYear();
});

// Function to load programs (local)
function loadPrograms(section_id, preselect_program_id = '', preselect_class_id = '') {
    if (!section_id) return;
    
    // Show loading indicator
    $('#program_id').html('<option value="">Loading...</option>');
    $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
    
    $.ajax({
        url: '<?php echo base_url(); ?>programs/getBySection', 
        method: 'POST',
        data: {
            section_id: section_id,
            <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
        },
        dataType: 'json',
        success: function (response) {
            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
            
            if(response && response.length > 0) {
                $.each(response, function (index, program) {
                    var selected = (String(preselect_program_id) === String(program.id)) ? 'selected' : '';
                    html += '<option value="' + program.id + '" ' + selected + '>' + program.title + '</option>';
                });
            } else {
                html += '<option value="" disabled>No programs found</option>';
            }
            
            $('#program_id').html(html);
            suggestUgcProgramFromLocal();
            if (!preselect_program_id && __campusFromUgc.program_id) {
                applyPendingCampusProgram();
            } else if (preselect_program_id) {
                // If there's a preselected program, load classes
                loadClasses(section_id, preselect_program_id, preselect_class_id);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error fetching programs:", error);
            console.error("Response:", xhr.responseText);
            $('#program_id').html('<option value="">Error loading programs</option>');
        }
    });
}

// Function to load classes
function loadClasses(section_id, program_id, preselect_class_id = '') {
    if (!section_id || !program_id) return;
    
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
                    var selected = (String(preselect_class_id) === String(classItem.id)) ? 'selected' : '';
                    html += '<option value="' + classItem.id + '" ' + selected + '>' + 
                           (classItem.degree ? classItem.degree + ' - ' : '') + 
                           classItem.class + '</option>';
                });
            } else {
                html += '<option value="" disabled>No classes found for this combination</option>';
                console.warn("No classes found for Section:", section_id, "Program:", program_id);
            }
            
            $('#class_id').html(html);
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", status, error);
            console.error("Response:", xhr.responseText);
            $('#class_id').html('<option value="">Error loading classes</option>');
        }
    });
}

// When section changes manually, fetch programs
$(document).on('change', '#section_id', function (e) {
    var section_id = $(this).val();
    
    // Clear program and class dropdowns
    $('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
    $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
    
    if (section_id != '') {
        loadPrograms(section_id);
    }
});

// When program changes manually, fetch classes
$(document).on('change', '#program_id', function (e) {
    var section_id = $('#section_id').val();
    var program_id = $(this).val();
    
    // Clear class dropdown
    $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
    
    if (section_id && program_id) {
        loadClasses(section_id, program_id);
    }
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
 * Rebuild UGC/HEMIS cascade selects from cached rows (temporary → permanent autofill).
 * @param {string} prefix ugc_t or ugc_p
 * @param {string} province Province label
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

/** Strip trailing "Province" so legacy dropdown text aligns with UGC cache province names. */
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
 * Find UGC combinedCode for province + district + local level (cached Address/GetAll rows).
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
 * Copy UGC cascade selections into hidden POST fields (server reads ugc_*_name + combined_code).
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
 * Mirror legacy province/district/municipality into hidden ugc_* fields when labels match cached UGC rows.
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

            refreshDistricts();
        },
        error: function() {
            $prov.html('<option value="">Error</option>');
            $dist.html('<option value="">Error</option>');
            $loc.html('<option value="">Error</option>');
        }
    });
}

function loadUgcPrograms(preselect) {
    $('#ugc_program_id').html('<option value="">Loading...</option>');
    $.ajax({
        // Cache-first to avoid empty live responses blocking edits.
        url: '<?php echo base_url(); ?>student/ugc_programs',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            var selected_id = preselect || <?php echo json_encode((string) set_value('ugc_program_id', isset($student['ugc_program_id']) ? $student['ugc_program_id'] : '')); ?>;
            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
            var rows = (response && response.data) ? response.data : [];
            if (response && response.message) {
                $('#ugc_program_hint').text(response.message);
            }
            if (rows.length > 0) {
                $.each(rows, function (_, program) {
                    html += _ugcProgramOptionHtml(program, selected_id);
                });
            } else {
                html += '<option value="" disabled>No UGC programs found</option>';
            }
            $('#ugc_program_id').html(html);
            suggestUgcProgramFromLocal();
            syncUgcProgramDisplays();

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

$(document).ready(function() {
    window.__ugcEditInitialP = {
        province: <?php echo json_encode((string) set_value('ugc_p_province', isset($student['ugc_p_province']) ? $student['ugc_p_province'] : '')); ?>,
        district: <?php echo json_encode((string) set_value('ugc_p_district', isset($student['ugc_p_district']) ? $student['ugc_p_district'] : '')); ?>,
        combinedCode: <?php echo json_encode((string) set_value('ugc_p_combined_code', isset($student['ugc_p_combined_code']) ? $student['ugc_p_combined_code'] : '')); ?>
    };
    initUgcAddressCascade('ugc_t', {
        province: <?php echo json_encode((string) set_value('ugc_t_province', isset($student['ugc_t_province']) ? $student['ugc_t_province'] : '')); ?>,
        district: <?php echo json_encode((string) set_value('ugc_t_district', isset($student['ugc_t_district']) ? $student['ugc_t_district'] : '')); ?>,
        localLevel: <?php echo json_encode((string) set_value('ugc_t_local_level', isset($student['ugc_t_local_level']) ? $student['ugc_t_local_level'] : '')); ?>,
        combinedCode: <?php echo json_encode((string) set_value('ugc_t_combined_code', isset($student['ugc_t_combined_code']) ? $student['ugc_t_combined_code'] : '')); ?>
    });
    initUgcAddressCascade('ugc_p', {
        province: <?php echo json_encode((string) set_value('ugc_p_province', isset($student['ugc_p_province']) ? $student['ugc_p_province'] : '')); ?>,
        district: <?php echo json_encode((string) set_value('ugc_p_district', isset($student['ugc_p_district']) ? $student['ugc_p_district'] : '')); ?>,
        localLevel: <?php echo json_encode((string) set_value('ugc_p_local_level', isset($student['ugc_p_local_level']) ? $student['ugc_p_local_level'] : '')); ?>,
        combinedCode: <?php echo json_encode((string) set_value('ugc_p_combined_code', isset($student['ugc_p_combined_code']) ? $student['ugc_p_combined_code'] : '')); ?>
    });

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

    $('#employeeform').on('submit', function (e) {
        syncAllUgcAddressHiddensForSubmit();
        var ugcAddrErr = validateUgcLegacyMatchesCache();
        if (ugcAddrErr) {
            e.preventDefault();
            alert(ugcAddrErr);
            return false;
        }
        if ($("#submitbtn").length) {
            $("#submitbtn").button('loading');
        }
    });
});
</script>

<script type="text/javascript">
    var total_fees_alloted = parseFloat($("input[name='total_post_fees']").val());
    $(document).ready(function () {
        $(document).on('change', '.fee_group_chk', function () {

            if ($(this).prop("checked")) {

                total_fees_alloted += parseFloat($(this).closest('div').find('span.fee_group_total').data('amount'));
            }
            else {
                total_fees_alloted -= parseFloat($(this).closest('div').find('span.fee_group_total').data('amount'));
            }

            //==============
            $.ajax({
                type: "POST",
                url: base_url + "admin/currency/getAmountFormat",
                data: { 'total_fees_alloted': total_fees_alloted },
                dataType: "json",
                beforeSend: function () {
                    $('#fade').css("display", "block");
                    $('#modal').css("display", "block");
                },
                success: function (data) {

                    $('.total_fees_alloted').text(data.amount);
                    $("#fade").fadeOut(1000);
                    $("#modal").fadeOut(1000);
                },
                error: function (xhr) { // if error occured
                    $("#fade").fadeOut(1000);
                    $("#modal").fadeOut(1000);
                },
                complete: function () {
                    $("#fade").fadeOut(1000);
                    $("#modal").fadeOut(1000);
                }
            });
            //==============

        });
    });
</script>

<script type="text/javascript" src="<?php echo base_url(); ?>backend/dist/js/savemode.js"></script>

<script type="text/javascript">
    $(document).ready(function () {
    // Store initial values for form recovery
    var initialTempProvince = '<?php echo isset($student["temporary_province"]) ? $student["temporary_province"] : ""; ?>';
    var initialTempDistrict = '<?php echo isset($student["temporary_district"]) ? $student["temporary_district"] : ""; ?>';
    var initialTempLocalLevel = '<?php echo isset($student["temporary_local_level"]) ? $student["temporary_local_level"] : ""; ?>';
    var initialTempWardNo = '<?php echo isset($student["temporary_ward_no"]) ? $student["temporary_ward_no"] : ""; ?>';
    var initialTempHouseNo = '<?php echo isset($student["temporary_house_no"]) ? $student["temporary_house_no"] : ""; ?>';
    var initialTempAddress = '<?php echo isset($student["current_address"]) ? (is_array($student["current_address"]) ? implode(", ", $student["current_address"]) : $student["current_address"]) : ""; ?>';
    
    var initialPermProvince = '<?php echo isset($student["permanent_province"]) ? $student["permanent_province"] : ""; ?>';
    var initialPermDistrict = '<?php echo isset($student["permanent_district"]) ? $student["permanent_district"] : ""; ?>';
    var initialPermLocalLevel = '<?php echo isset($student["permanent_local_level"]) ? $student["permanent_local_level"] : ""; ?>';
    var initialPermWardNo = '<?php echo isset($student["permanent_ward_no"]) ? $student["permanent_ward_no"] : ""; ?>';
    var initialPermHouseNo = '<?php echo isset($student["permanent_house_no"]) ? $student["permanent_house_no"] : ""; ?>';
    var initialPermAddress = '<?php echo isset($student["permanent_address"]) ? (is_array($student["permanent_address"]) ? implode(", ", $student["permanent_address"]) : $student["permanent_address"]) : ""; ?>';

    // Set initial values and load dependent dropdowns on page load
    function initializeAddressFields() {
        // Set initial values from PHP or form submission errors
        var tempProvince = '<?php echo set_value("temporary_province", isset($student["temporary_province"]) ? $student["temporary_province"] : ""); ?>';
        var tempDistrict = '<?php echo set_value("temporary_district", isset($student["temporary_district"]) ? $student["temporary_district"] : ""); ?>';
        var tempLocalLevel = '<?php echo set_value("temporary_local_level", isset($student["temporary_local_level"]) ? $student["temporary_local_level"] : ""); ?>';
        var permProvince = '<?php echo set_value("permanent_province", isset($student["permanent_province"]) ? $student["permanent_province"] : ""); ?>';
        var permDistrict = '<?php echo set_value("permanent_district", isset($student["permanent_district"]) ? $student["permanent_district"] : ""); ?>';
        var permLocalLevel = '<?php echo set_value("permanent_local_level", isset($student["permanent_local_level"]) ? $student["permanent_local_level"] : ""); ?>';
        
        // Set initial values for temporary address fields
        $('#temporary_province').val(tempProvince);
        $('#temporary_ward_no').val('<?php echo set_value("temporary_ward_no", isset($student["temporary_ward_no"]) ? $student["temporary_ward_no"] : ""); ?>');
        $('#current_address').val('<?php echo set_value("current_address", isset($student["current_address"]) ? (is_array($student["current_address"]) ? implode(", ", $student["current_address"]) : $student["current_address"]) : ""); ?>');
        $('#temporary_house_no').val('<?php echo set_value("temporary_house_no", isset($student["temporary_house_no"]) ? $student["temporary_house_no"] : ""); ?>');
        
        // Set initial values for permanent address fields
        $('#permanent_province').val(permProvince);
        $('#permanent_ward_no').val('<?php echo set_value("permanent_ward_no", isset($student["permanent_ward_no"]) ? $student["permanent_ward_no"] : ""); ?>');
        $('#permanent_address').val('<?php echo set_value("permanent_address", isset($student["permanent_address"]) ? (is_array($student["permanent_address"]) ? implode(", ", $student["permanent_address"]) : $student["permanent_address"]) : ""); ?>');
        $('#permanent_house_no').val('<?php echo set_value("permanent_house_no", isset($student["permanent_house_no"]) ? $student["permanent_house_no"] : ""); ?>');
        
        // Check if autofill was checked before form submission
        var wasAutofillChecked = '<?php echo set_value("autofill_address", isset($_POST["autofill_address"]) ? "true" : "false"); ?>';
        if (wasAutofillChecked === 'true') {
            $('#autofill_address').prop('checked', true);
        }
        
        // Load districts for temporary province
        if (tempProvince) {
            loadDistricts('temporary', tempProvince, tempDistrict, tempLocalLevel);
        }
        
        // Load districts for permanent province if autofill is not checked
        if (permProvince && !$('#autofill_address').is(':checked')) {
            loadDistricts('permanent', permProvince, permDistrict, permLocalLevel);
        }
    }
    
    // Helper function to load districts
    function loadDistricts(addressType, provinceId, selectedDistrictId, selectedLocalLevelId) {
        $.ajax({
            url: '<?php echo base_url("student/getDistrictsByProvince"); ?>',
            method: 'POST',
            data: { province_id: provinceId },
            dataType: 'json',
            success: function (response) {
                var districtDropdown = $('#' + addressType + '_district');
                districtDropdown.find('option').not(':first').remove();
                
                $.each(response, function (index, district) {
                    districtDropdown.append('<option value="' + district.id + '">' + district.name + '</option>');
                });
                
                if (selectedDistrictId) {
                    districtDropdown.val(selectedDistrictId);
                    loadLocalLevels(addressType, selectedDistrictId, selectedLocalLevelId);
                }
            },
            error: function (xhr, status, error) {
                console.error("Error fetching districts:", error);
            }
        });
    }
    
    // Helper function to load local levels
    function loadLocalLevels(addressType, districtId, selectedLocalLevelId) {
        $.ajax({
            url: '<?php echo base_url("student/getMunicipalitiesByDistrict"); ?>',
            method: 'POST',
            data: { district_id: districtId },
            dataType: 'json',
            success: function (response) {
                var localLevelDropdown = $('#' + addressType + '_local_level');
                localLevelDropdown.find('option').not(':first').remove();
                
                $.each(response, function (index, municipality) {
                    localLevelDropdown.append('<option value="' + municipality.id + '">' + municipality.name + '</option>');
                });
                
                if (selectedLocalLevelId) {
                    localLevelDropdown.val(selectedLocalLevelId);
                }
            },
            error: function (xhr, status, error) {
                console.error("Error fetching municipalities:", error);
            }
        });
    }

    // Call the function to initialize address fields
    initializeAddressFields();

    // Temporary Address - Province change event
    $('#temporary_province').change(function () {
        var province_id = $(this).val();

        if (province_id != '') {
            $.ajax({
                url: '<?php echo base_url("student/getDistrictsByProvince"); ?>',
                method: 'POST',
                data: { province_id: province_id },
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
                },
                error: function (xhr, status, error) {
                    console.error("Error fetching districts:", error);
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
                data: { district_id: district_id },
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
                },
                error: function (xhr, status, error) {
                    console.error("Error fetching municipalities:", error);
                }
            });
        } else {
            $('#temporary_local_level').find('option').not(':first').remove();
        }
    });

    // Permanent Address - Province change event
    $('#permanent_province').change(function () {
        var province_id = $(this).val();

        if (province_id != '' && !$('#autofill_address').is(':checked')) {
            $.ajax({
                url: '<?php echo base_url("student/getDistrictsByProvince"); ?>',
                method: 'POST',
                data: { province_id: province_id },
                dataType: 'json',
                success: function (response) {
                    // Clear and update district dropdown
                    $('#permanent_district').find('option').not(':first').remove();
                    $('#permanent_local_level').find('option').not(':first').remove();

                    $.each(response, function (index, district) {
                        $('#permanent_district').append('<option value="' + district.id + '">' + district.name + '</option>');
                    });
                },
                error: function (xhr, status, error) {
                    console.error("Error fetching districts:", error);
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
                data: { district_id: district_id },
                dataType: 'json',
                success: function (response) {
                    // Clear and update municipality dropdown
                    $('#permanent_local_level').find('option').not(':first').remove();

                    $.each(response, function (index, municipality) {
                        $('#permanent_local_level').append('<option value="' + municipality.id + '">' + municipality.name + '</option>');
                    });
                },
                error: function (xhr, status, error) {
                    console.error("Error fetching municipalities:", error);
                }
            });
        } else if (district_id == '') {
            $('#permanent_local_level').find('option').not(':first').remove();
        }
    });

    // Auto fill address checkbox change event
    $('#autofill_address').change(function () {
        auto_fill_address();
    });

    // Temporary Local Level change event
    $('#temporary_local_level').change(function () {
        if ($('#autofill_address').is(':checked')) {
            $('#permanent_local_level').val($(this).val());
            syncUgcMirrorLegacyToHidden();
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
});

// Helper function to copy districts from temp to perm address
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
            data: { district_id: temp_district_val },
            dataType: 'json',
            success: function (response) {
                copyLocalLevelsToPermAddress(response);
            },
            error: function (xhr, status, error) {
                console.error("Error fetching municipalities:", error);
            }
        });
    } else {
        syncUgcMirrorLegacyToHidden();
    }
}

// Helper function to copy local levels from temp to perm address
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
    syncUgcMirrorLegacyToHidden();
}

// Auto fill address function
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
                data: { province_id: temp_province_val },
                dataType: 'json',
                success: function (response) {
                    copyDistrictsToPermAddress(response);
                },
                error: function (xhr, status, error) {
                    console.error("Error fetching districts:", error);
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
            syncUgcMirrorLegacyToHidden();
        }
    } else {
        // Get initial permanent address values
        var initialPermProvince = '<?php echo isset($student["permanent_province"]) ? $student["permanent_province"] : ""; ?>';
        var initialPermDistrict = '<?php echo isset($student["permanent_district"]) ? $student["permanent_district"] : ""; ?>';
        var initialPermLocalLevel = '<?php echo isset($student["permanent_local_level"]) ? $student["permanent_local_level"] : ""; ?>';
        var initialPermWardNo = '<?php echo isset($student["permanent_ward_no"]) ? $student["permanent_ward_no"] : ""; ?>';
        var initialPermAddress = '<?php echo isset($student["permanent_address"]) ? (is_array($student["permanent_address"]) ? implode(", ", $student["permanent_address"]) : $student["permanent_address"]) : ""; ?>';
        var initialPermHouseNo = '<?php echo isset($student["permanent_house_no"]) ? $student["permanent_house_no"] : ""; ?>';
        
        // Reset permanent address fields to their initial values
        $('#permanent_province').val(initialPermProvince);
        
        if (initialPermProvince) {
            $.ajax({
                url: '<?php echo base_url("student/getDistrictsByProvince"); ?>',
                method: 'POST',
                data: { province_id: initialPermProvince },
                dataType: 'json',
                success: function (response) {
                    $('#permanent_district').find('option').not(':first').remove();
                    $.each(response, function (index, district) {
                        $('#permanent_district').append('<option value="' + district.id + '">' + district.name + '</option>');
                    });
                    $('#permanent_district').val(initialPermDistrict);
                    
                    if (initialPermDistrict) {
                        $.ajax({
                            url: '<?php echo base_url("student/getMunicipalitiesByDistrict"); ?>',
                            method: 'POST',
                            data: { district_id: initialPermDistrict },
                            dataType: 'json',
                            success: function (response) {
                                $('#permanent_local_level').find('option').not(':first').remove();
                                $.each(response, function (index, municipality) {
                                    $('#permanent_local_level').append('<option value="' + municipality.id + '">' + municipality.name + '</option>');
                                });
                                $('#permanent_local_level').val(initialPermLocalLevel);
                                syncUgcMirrorLegacyToHidden();
                            }
                        });
                    } else {
                        syncUgcMirrorLegacyToHidden();
                    }
                }
            });
        } else {
            syncUgcMirrorLegacyToHidden();
        }
        
        $('#permanent_ward_no').val(initialPermWardNo);
        $('#permanent_address').val(initialPermAddress);
        $('#permanent_house_no').val(initialPermHouseNo);
        if (typeof ugcApplyCascadeSelection === 'function' && window.__ugcAddressRows && window.__ugcAddressRows.length && window.__ugcEditInitialP) {
            ugcApplyCascadeSelection('ugc_p', window.__ugcEditInitialP.province, window.__ugcEditInitialP.district, window.__ugcEditInitialP.combinedCode);
        }
    }

    return true;
}
function auto_fill_guardian_address() {
    return true;
}
</script>



<script>
$(document).ready(function() {
    // Add new education section
    $(document).on('click', '.add-previous-education', function() {
        var $template = $('.previous-education-section:first').clone();
        var totalSections = $('.previous-education-section').length;
        
        // Clear values in the cloned section
        $template.find('input').val('');
        $template.find('select').val('');
        
        // Update IDs and names to avoid duplicates
        $template.find('[id]').each(function() {
            var oldId = $(this).attr('id');
            var newId = oldId.replace(/\d+$/, '') + totalSections;
            $(this).attr('id', newId);
        });
        
        // Update labels to match new IDs
        $template.find('label').each(function() {
            var oldFor = $(this).attr('for');
            if (oldFor) {
                var newFor = oldFor.replace(/\d+$/, '') + totalSections;
                $(this).attr('for', newFor);
            }
        });
        
        // Change the button to remove button
        $template.find('.add-previous-education')
            .removeClass('btn-success add-previous-education')
            .addClass('btn-danger remove-previous-education')
            .html('<i class="fa fa-minus"></i>');
        
        // Append to container
        $('#previous_education_container').append($template);
    });
    
    // Remove education section
    $(document).on('click', '.remove-previous-education', function() {
        var $this = $(this);
        var $section = $this.closest('.previous-education-section');
        
        // Don't allow removal if it's the only section
        if ($('.previous-education-section').length > 1) {
            $section.remove();
            
            // If we're removing and only one section remains, make sure it has the add button
            if ($('.previous-education-section').length === 1) {
                $('.previous-education-section .remove-previous-education')
                    .removeClass('btn-danger remove-previous-education')
                    .addClass('btn-success add-previous-education')
                    .html('<i class="fa fa-plus"></i>');
            }
        }
    });
});
</script>

<script>
$(document).ready(function() {
    let dynamicDocCounter = <?php echo isset($student_docs) ? count($student_docs) : 4; ?>;
    
    // Add new document row
    $('#add-document').click(function() {
        dynamicDocCounter++;
        let newRow = `
            <tr class="document-row dynamic-document" data-doc-id="">
                <td>
                    ${dynamicDocCounter}.
                </td>
                <td>
                    <input type="text" name='dynamic_title[]' class="form-control" value="" placeholder="">
                    <input type="hidden" name="dynamic_doc_id[]" value="">
                </td>
                <td>
                    <div class="input-group">
                        <label class="input-group-btn">
                            <span class="btn btn-primary">
                                Choose File <input type="file" name="dynamic_doc[]" style="display: none;">
                            </span>
                        </label>
                        <input type="text" class="form-control" readonly>
                    </div>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-xs remove-dynamic-document">
                        <i class="fa fa-trash"></i>
                        <?php echo $this->lang->line('remove'); ?>
                    </button>
                </td>
            </tr>
        `;
        
        $('.col-md-6:last .table tbody').append(newRow);
        updateRowNumbers();
    });
    
    // Remove dynamic document row
    $(document).on('click', '.remove-dynamic-document', function() {
        $(this).closest('tr').remove();
        updateRowNumbers();
        dynamicDocCounter--;
    });
    
    // Update row numbers
    function updateRowNumbers() {
        $('.document-row').each(function(index) {
            $(this).find('td:first').html(function() {
                let content = $(this).html();
                return content.replace(/\d+\./, (index + 1) + '.');
            });
        });
    }
    
    // Remove selected documents
    $('#remove-selected-docs').click(function() {
        $('.doc-checkbox:checked').each(function() {
            let docId = $(this).val();
            let row = $(this).closest('tr');
            
            if (row.hasClass('dynamic-document')) {
                row.remove();
                dynamicDocCounter--;
            } else {
                // For fixed documents, just clear the data
                row.find('input[type="text"]').val('');
                row.find('input[type="file"]').val('');
                row.find('input[type="hidden"][name$="_doc_id"]').val('');
                row.find('a').remove();
                $(this).prop('checked', false);
            }
        });
        updateRowNumbers();
    });
});

// Function for removing individual documents (existing functionality)
function removeDocument(docId, element) {
    if (confirm('Are you sure you want to remove this document?')) {
        $.ajax({
            url: '<?php echo base_url(); ?>student/remove_document',
            type: 'POST',
            data: {doc_id: docId},
            success: function(response) {
                $(element).closest('tr').find('input[type="text"]').val('');
                $(element).closest('tr').find('input[type="file"]').val('');
                $(element).closest('tr').find('input[type="hidden"][name$="_doc_id"]').val('');
                $(element).closest('tr').find('a').remove();
                $(element).remove();
            }
        });
    }
}
</script>

<!-- NEPALI DATEPICKER - Handled by Global Initializer -->
<script type="text/javascript">
$(document).ready(function() {
    console.log('Student edit page loaded - Initializing Nepali datepickers');
    
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
        
        // DOB BS + admission BS: init only if not already bound (avoid destroy/reinit races with nepali-datepicker-global.js MutationObserver).
        var $dobField = $('#dob_bs');
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
            $dobField.off('nepali.datepicker.dateChanged.convert change.convert blur.convert input.convert dateChange.convert');
            
            // Listen for Nepali datepicker date change event (multiple event names for compatibility)
            $dobField.on('nepali.datepicker.dateChanged.convert', function(e) {
                var bsDate = $(this).val();
                if (bsDate) {
                    setTimeout(function() {
                        convertBSToAD(bsDate, 'dob_ad');
                    }, 50);
                }
            });
            
            // Also listen for the dateChange event (alternative event name)
            $dobField.on('dateChange.convert', function() {
                var bsDate = $(this).val();
                if (bsDate) {
                    setTimeout(function() {
                        convertBSToAD(bsDate, 'dob_ad');
                    }, 50);
                }
            });
            
            // Listen for manual input changes
            $dobField.on('change.convert blur.convert input.convert', function() {
                var bsDate = $(this).val();
                if (bsDate && bsDate.match(/^\d{4}-\d{2}-\d{2}$/)) {
                    convertBSToAD(bsDate, 'dob_ad');
                }
            });
            
            // Convert initial value if present
            var initialBSDate = $dobField.val();
            if (initialBSDate && initialBSDate.match(/^\d{4}-\d{2}-\d{2}$/)) {
                setTimeout(function() {
                    convertBSToAD(initialBSDate, 'dob_ad');
                }, 300);
            }
            
            // Polling fallback for instant conversion (like create blade)
            setInterval(function() {
                var $dobField = $('#dob_bs');
                if ($dobField.length && $dobField.val()) {
                    var currentVal = $dobField.val();
                    var lastVal = $dobField.data('last-converted-value') || '';
                    if (currentVal !== lastVal && currentVal.match(/^\d{4}-\d{2}-\d{2}$/)) {
                        $dobField.data('last-converted-value', currentVal);
                        convertBSToAD(currentVal, 'dob_ad');
                    }
                }
            }, 200);
        }
    }
    
    // Start initialization immediately
    initializeDatePickers();
    
    // Retry initialization multiple times to ensure it works with existing values
    setTimeout(function() {
        initializeDatePickers();
    }, 500);
    
    // Retry initialization if datepickers weren't ready
    setTimeout(function() {
        initializeDatePickers();
    }, 1000);
    
    // Also ensure calendar binds on click/focus
    $(document).on('click', '#dob_bs, #admission_date', function() {
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
                    ndpYearCount: ($field.attr('id') === 'dob_bs' ? 90 : 15),
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
});
</script>

<?php
if (!empty($feesessiongroup_model)) {
    foreach ($feesessiongroup_model as $feesessiongroup_value) {
        if (isset($feesessiongroup_value->student_fees_master_id) && $feesessiongroup_value->student_fees_master_id > 0) {
            echo '<input type="hidden" name="prev_fees_group[]" value="' . $feesessiongroup_value->id . '">';
        }
    }
}
?>








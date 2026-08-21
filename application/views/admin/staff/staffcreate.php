<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-sitemap"></i> <?php echo $this->lang->line('human_resource'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
    <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <form id="form1" action="<?php echo site_url('admin/staff/create') ?>" name="employeeform" method="post" accept-charset="utf-8" enctype="multipart/form-data">
                        <div class="box-body">
                            <div class="alert alert-info">
                                Staff email is their login username, password is generated automatically and sent to staff email.
                            </div>
                            <div class="tshadow mb25 bozero">
                                <div class="box-tools pull-right pt3">
                                    <a class="btn btn-sm btn-primary" href="<?php echo base_url(); ?>admin/staff/import" autocomplete="off">
                                        <i class="fa fa-plus"></i> <?php echo $this->lang->line('import_staff'); ?>
                                    </a>
                                </div>
                                <h4 class="pagetitleh2"><?php echo $this->lang->line('basic_information'); ?> </h4>

                                <div class="around10">
                                    <?php if ($this->session->flashdata('msg')) { ?>
                                        <?php echo $this->session->flashdata('msg');
                                        $this->session->unset_userdata('msg'); ?>
                                    <?php } ?>
                                    <?php echo $this->customlib->getCSRF(); ?>
                                    <?php $this->load->view('admin/staff/_staff_ugc_address_hiddens', array('staff' => array())); ?>

                                    <!-- Row 1: Staff ID, Role, Salutation -->
                                    <div class="row">
                                        <?php if (!$staffid_auto_insert) { ?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="employee_id"><?php echo $this->lang->line('staff_id'); ?></label><small class="req"> *</small>
                                                    <input id="employee_id" name="employee_id" type="text" class="form-control" value="<?php echo set_value('employee_id') ?>" />
                                                    <span class="text-danger"><?php echo form_error('employee_id'); ?></span>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="role"><?php echo $this->lang->line('role'); ?></label><small class="req"> *</small>
                                                <select id="role" name="role" class="form-control">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php foreach ($roles as $key => $role) { ?>
                                                        <option value="<?php echo $role['id'] ?>" <?php echo set_select('role', $role['id']); ?>><?php echo $role["name"] ?></option>
                                                    <?php } ?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('role'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="salutation"><?php echo $this->lang->line('salutation'); ?></label><small class="req"> *</small>
                                                <input id="salutation" name="salutation" type="text" class="form-control" placeholder="Mr. / Ms. / Mrs." value="<?php echo set_value('salutation') ?>" />
                                                <span class="text-danger"><?php echo form_error('salutation'); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Row 2: First Name, Middle Name, Last Name -->
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="name"><?php echo $this->lang->line('first_name'); ?></label><small class="req"> *</small>
                                                <input id="name" name="name" type="text" class="form-control" value="<?php echo set_value('name') ?>" />
                                                <span class="text-danger"><?php echo form_error('name'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="middle_name"><?php echo $this->lang->line('middle_name'); ?></label>
                                                <input id="middle_name" name="middle_name" type="text" class="form-control" value="<?php echo set_value('middle_name') ?>" />
                                                <span class="text-danger"><?php echo form_error('middle_name'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="surname"><?php echo $this->lang->line('last_name'); ?></label>
                                                <input id="surname" name="surname" type="text" class="form-control" value="<?php echo set_value('surname') ?>" />
                                                <span class="text-danger"><?php echo form_error('surname'); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Row 3: Nepali Names -->
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="first_name_np"><?php echo $this->lang->line('first_name_np'); ?></label>
                                                <input id="first_name_np" name="firstnameNp" type="text" class="form-control" value="<?php echo set_value('firstnameNp') ?>" />
                                                <span class="text-danger"><?php echo form_error('firstnameNp'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="middle_name_np"><?php echo $this->lang->line('middle_name_np'); ?></label>
                                                <input id="middle_name_np" name="middlenameNp" type="text" class="form-control" value="<?php echo set_value('middlenameNp') ?>" />
                                                <span class="text-danger"><?php echo form_error('middlenameNp'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="last_name_np"><?php echo $this->lang->line('last_name_np'); ?></label>
                                                <input id="last_name_np" name="lastnameNp" type="text" class="form-control" value="<?php echo set_value('lastnameNp') ?>" />
                                                <span class="text-danger"><?php echo form_error('lastnameNp'); ?></span>
                                            </div>
                                        </div>
                                    </div>


                                    <!-- Row 4: Email, Gender -->
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="email"><?php echo $this->lang->line('email'); ?></label><small class="req"> *</small>
                                                <input id="email" name="email" placeholder="" type="text" class="form-control" value="<?php echo set_value('email') ?>" />
                                                <span class="text-danger"><?php echo form_error('email'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="gender"><?php echo $this->lang->line('gender'); ?></label><small class="req"> *</small>
                                                <select class="form-control" name="gender">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php foreach ($genderList as $key => $value) { ?>
                                                        <option value="<?php echo $key; ?>" <?php echo set_select('gender', $key, set_value('gender')); ?>><?php echo $value; ?></option>
                                                    <?php } ?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('gender'); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Row 5: Date of Birth, Date of Joining, Phone, Emergency Contact -->
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="dob_bs"><?php echo $this->lang->line('date_of_birth_bs'); ?></label>
                                                <div class="form-group">
                                                    <input id="dob_bs" name="dob_bs" placeholder="YYYY-MM-DD" type="text" class="form-control nepali-datepicker" value="<?php echo set_value('dob_bs') ?>" />
                                                </div>
                                                <span class="text-danger"><?php echo form_error('dob_bs'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="dob"><?php echo $this->lang->line('date_of_birth'); ?></label><small class="req"> *</small>
                                                <input id="dob" name="dob" placeholder="YYYY-MM-DD" type="text" class="form-control date no-nepali-datepicker" data-no-nepali="true" value="<?php echo set_value('dob') ?>" />
                                                <span class="help-block small text-muted">Auto-filled from B.S. date above.</span>
                                                <span class="text-danger"><?php echo form_error('dob'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="date_of_joining"><?php echo $this->lang->line('date_of_joining'); ?> (BS)</label><small class="req"> *</small>
                                                <input id="date_of_joining" name="date_of_joining" placeholder="" type="text" class="form-control nepali-datepicker" value="<?php echo set_value('date_of_joining') ?>" />
                                                <span class="text-danger"><?php echo form_error('date_of_joining'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="contactno"><?php echo $this->lang->line('phone'); ?></label><small class="req"> *</small>
                                                <input id="mobileno" name="contact_no" placeholder="" type="text" class="form-control" value="<?php echo set_value('contact_no') ?>" />
                                                <span class="text-danger"><?php echo form_error('contact_no'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="emergency_no"><?php echo $this->lang->line('emergency_contact_number'); ?></label>
                                                <input id="emergency_no" name="emergency_contact_no" placeholder="" type="text" class="form-control" value="<?php echo set_value('emergency_contact_no') ?>" />
                                                <span class="text-danger"><?php echo form_error('emergency_contact_no'); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Row 6: Marital Status, Religion, Ethnicity, Photo -->
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="marital_status"><?php echo $this->lang->line('marital_status'); ?></label>
                                                <select class="form-control" name="marital_status">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php foreach ($marital_status as $makey => $mavalue) { ?>
                                                        <option value="<?php echo $mavalue ?>" <?php echo set_select('marital_status', $mavalue, set_value('marital_status')); ?>><?php echo $mavalue; ?></option>
                                                    <?php } ?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('marital_status'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="religion"><?php echo $this->lang->line('religion'); ?></label><small class="req"> *</small>
                                                <select name="religion" class="form-control">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <option value="Hinduism" <?php echo set_select('religion', 'Hinduism'); ?>>Hinduism</option>
                                                    <option value="Buddhism" <?php echo set_select('religion', 'Buddhism'); ?>>Buddhism</option>
                                                    <option value="Islam" <?php echo set_select('religion', 'Islam'); ?>>Islam</option>
                                                    <option value="Christianity" <?php echo set_select('religion', 'Christianity'); ?>>Christianity</option>
                                                    <option value="Kirat" <?php echo set_select('religion', 'Kirat'); ?>>Kirat</option>
                                                    <option value="Jainism" <?php echo set_select('religion', 'Jainism'); ?>>Jainism</option>
                                                    <option value="Sikhism" <?php echo set_select('religion', 'Sikhism'); ?>>Sikhism</option>
                                                    <option value="Others" <?php echo set_select('religion', 'Others'); ?>>Others</option>
                                                </select>

                                                <span class="text-danger"><?php echo form_error('religion'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="caste"><?php echo $this->lang->line('caste'); ?></label>
                                                <select name="caste" class="form-control">
                                                    <option value="" disabled selected>Select</option>
                                                    <option value="Brahmin" <?php echo set_select('caste', 'Brahmin'); ?>>Brahmin</option>
                                                    <option value="Chhetri" <?php echo set_select('caste', 'Chhetri'); ?>>Chhetri</option>
                                                    <option value="Dalit" <?php echo set_select('caste', 'Dalit'); ?>>Dalit</option>
                                                    <option value="Janajati" <?php echo set_select('caste', 'Janajati'); ?>>Janajati</option>
                                                    <option value="Madhesi" <?php echo set_select('caste', 'Madhesi'); ?>>Madhesi</option>
                                                    <option value="Muslim" <?php echo set_select('caste', 'Muslim'); ?>>Muslim</option>
                                                    <option value="Tharu" <?php echo set_select('caste', 'Tharu'); ?>>Tharu</option>
                                                    <option value="Others" <?php echo set_select('caste', 'Others'); ?>>Others</option>
                                                </select>
                                                <span class="help-block">Syncs to HEMIS as <code>ethnicity</code>.</span>
                                                <span class="text-danger"><?php echo form_error('caste'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="file"><?php echo $this->lang->line('photo'); ?></label>
                                                <div><input class="filestyle form-control" type='file' name='file' id="file" size='20' /></div>
                                                <span class="text-danger"><?php echo form_error('file'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="father_name"><?php echo $this->lang->line('father_name'); ?></label>
                                                <input id="father_name" name="father_name" type="text" class="form-control" value="<?php echo set_value('father_name') ?>" />
                                                <span class="text-danger"><?php echo form_error('father_name'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="mother_name"><?php echo $this->lang->line('mother_name'); ?></label>
                                                <input id="mother_name" name="mother_name" placeholder="" type="text" class="form-control" value="<?php echo set_value('mother_name') ?>" />
                                                <span class="text-danger"><?php echo form_error('mother_name'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="spouse_name">Spouse name</label>
                                                <input id="spouse_name" name="spouse_name" type="text" class="form-control" maxlength="255" value="<?php echo set_value('spouse_name'); ?>" placeholder="UGC / HEMIS HR" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="dependent_info">Dependent info</label>
                                                <textarea id="dependent_info" name="dependent_info" class="form-control" rows="2" placeholder="Children / dependents (short text)"><?php echo set_value('dependent_info'); ?></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <?php $this->load->view('admin/staff/_staff_hemis_basic_fields', array(
                                        'staff' => array(),
                                        'ugc_employee_positions' => isset($ugc_employee_positions) ? $ugc_employee_positions : array(),
                                        'ugc_departments' => isset($ugc_departments) ? $ugc_departments : array(),
                                        'ugc_sections' => isset($ugc_sections) ? $ugc_sections : array(),
                                        'contract_type' => $contract_type,
                                    )); ?>
                                </div>
                            </div>

             <!-- Permanent Address -->
<div class="tshadow mb25 bozero">
    <h3 class="pagetitleh2"><?php echo $this->lang->line('permanent_address'); ?></h3>
    <div class="around10">
        <div class="row">
            <?php $this->load->view('admin/staff/_staff_ugc_address_row', array('prefix' => 'ugc_p', 'staff' => array())); ?>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="permanent_ward_no"><?php echo $this->lang->line('ward'); ?></label><small class="req"> *</small>
                    <select id="permanent_ward_no" name="permanent_ward_no" class="form-control">
                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                        <?php for ($i = 1; $i <= 32; $i++) { ?>
                            <option value="<?php echo $i; ?>" <?php echo set_select('permanent_ward_no', $i); ?>><?php echo $i; ?></option>
                        <?php } ?>
                    </select>
                    <span class="text-danger"><?php echo form_error('permanent_ward_no'); ?></span>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="permanent_house_no"><?php echo $this->lang->line('permanent_house_no'); ?></label>
                    <input id="permanent_house_no" name="permanent_house_no" placeholder="" type="text" class="form-control" value="<?php echo set_value('permanent_house_no') ?>" />
                    <span class="text-danger"><?php echo form_error('permanent_house_no'); ?></span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="permanent_address"><?php echo $this->lang->line('permanent_address'); ?></label>
                    <input id="permanent_address" name="permanent_address" placeholder="" type="text" class="form-control" value="<?php echo set_value('permanent_address') ?>" />
                    <span class="text-danger"><?php echo form_error('permanent_address'); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Temporary Address -->
<div class="tshadow mb25 bozero">
    <h3 class="pagetitleh2"><?php echo $this->lang->line('current_address'); ?></h3>
    <div class="around10">
        <div class="row">
            <div class="col-md-12">
                <div class="form-check" style="margin-bottom:10px;">
                    <input type="checkbox" class="form-check-input" id="same_as_permanent" name="same_as_permanent" value="1" onclick="copyPermanentAddress()">
                    <label class="form-check-label" for="same_as_permanent"><?php echo $this->lang->line('if_permanent_address_is_current_address'); ?></label>
                </div>
            </div>
        </div>
        <div class="row">
            <?php $this->load->view('admin/staff/_staff_ugc_address_row', array('prefix' => 'ugc_t', 'staff' => array())); ?>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="temporary_ward_no"><?php echo $this->lang->line('ward'); ?></label>
                    <select id="temporary_ward_no" name="temporary_ward_no" class="form-control">
                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                        <?php for ($i = 1; $i <= 32; $i++) { ?>
                            <option value="<?php echo $i; ?>" <?php echo set_select('temporary_ward_no', $i); ?>><?php echo $i; ?></option>
                        <?php } ?>
                    </select>
                    <span class="text-danger"><?php echo form_error('temporary_ward_no'); ?></span>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="local_address"><?php echo $this->lang->line('current_address'); ?></label>
                    <input id="local_address" name="local_address" placeholder="" type="text" class="form-control" value="<?php echo set_value('local_address') ?>" />
                    <span class="text-danger"><?php echo form_error('local_address'); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

                            <div class="tshadow mb25 bozero">
                                <h3 class="pagetitleh2">Qualification</h3>
                                <div class="around10">
                                    <?php $this->load->view('admin/staff/_staff_hemis_qualification_section', array('staff' => array())); ?>

                                    <!-- Overall Qualification and Work Experience -->
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="qualification">Overall Qualification</label>
                                                <textarea id="qualification" name="qualification" class="form-control" rows="4" placeholder="Overall qualification summary"><?php echo set_value('qualification'); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="work_exp">Work Experience</label>
                                                <textarea id="work_exp" name="work_exp" class="form-control" rows="4" placeholder="Work experience details"><?php echo set_value('work_exp'); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Documents -->
                            <div class="tshadow mb25 bozero">
                                <h3 class="pagetitleh2"><?php echo $this->lang->line('additional_document'); ?></h3>
                                <div class="around10">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="citizenship"><?php echo $this->lang->line('citizenship'); ?></label>
                                                <input class="filestyle form-control" type='file' name='citizenship[]' id="citizenship" multiple size='20' />
                                                <div id="citizenship-preview" class="preview-container mt-2"></div>
                                                <span class="text-danger"><?php echo form_error('citizenship'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="nationalId"><?php echo $this->lang->line('national_id'); ?></label>
                                                <input class="filestyle form-control" type='file' name='nationalId[]' id="nationalId" multiple size='20' />
                                                <div id="nationalId-preview" class="preview-container mt-2"></div>
                                                <span class="text-danger"><?php echo form_error('nationalId'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="ctz_issue_district_code"><?php echo $this->lang->line('ctz_issue_district_code'); ?></label>
                                                <input id="ctz_issue_district_code" name="ctz_issue_district_code" placeholder="" type="text" class="form-control" value="<?php echo set_value('ctz_issue_district_code') ?>" />
                                                <span class="text-danger"><?php echo form_error('ctz_issue_district_code'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="nid_no"><?php echo $this->lang->line('nid_no'); ?></label>
                                                <input id="nid_no" name="nid_no" placeholder="" type="text" class="form-control" value="<?php echo set_value('nid_no') ?>" />
                                                <span class="text-danger"><?php echo form_error('nid_no'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- Additional Information -->
                            <div class="box-group collapsed-box">
                                <div class="panel box box-success collapsed-box">
                                    <div class="box-header with-border">
                                        <a href="#" class="collapsed btn boxplus staff-more-details-toggle" data-original-title="Collapse">
                                            <i class="fa fa-fw fa-plus"></i><?php echo $this->lang->line('add_more_details'); ?>
                                        </a>
                                    </div>
                                    <div class="box-body">
                                        <!-- Payroll Information -->
                                        <div class="tshadow mb25 bozero">
                                            <h4 class="pagetitleh2"><?php echo $this->lang->line('payroll'); ?></h4>
                                            <div class="row around10">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="epf_no"><?php echo $this->lang->line('epf_no'); ?></label>
                                                        <input id="epf_no" name="epf_no" placeholder="" type="text" class="form-control" value="<?php echo set_value('epf_no') ?>" />
                                                        <span class="text-danger"><?php echo form_error('epf_no'); ?></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="basic_salary"><?php echo $this->lang->line('basic_salary'); ?></label>
                                                        <input type="text" class="form-control" name="basic_salary" value="<?php echo set_value('basic_salary') ?>">
                                                        <span class="text-danger"><?php echo form_error('basic_salary'); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <!-- Bank Account Details -->
                                        <!-- Bank Account Details (moved to HEMIS / UGC Sync Essentials) -->


                                        <!-- Social Media -->
                                        <div class="tshadow mb25 bozero">
                                            <h4 class="pagetitleh2"><?php echo $this->lang->line('social_media_link'); ?></h4>
                                            <div class="row around10">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="facebook"><?php echo $this->lang->line('facebook_url'); ?></label>
                                                        <input id="facebook" name="facebook" placeholder="" type="text" class="form-control" value="<?php echo set_value('facebook') ?>" />
                                                        <span class="text-danger"><?php echo form_error('facebook'); ?></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="twitter"><?php echo $this->lang->line('twitter_url'); ?></label>
                                                        <input id="twitter" name="twitter" placeholder="" type="text" class="form-control" value="<?php echo set_value('twitter') ?>" />
                                                        <span class="text-danger"><?php echo form_error('twitter'); ?></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="linkedin"><?php echo $this->lang->line('linkedin_url'); ?></label>
                                                            <input id="linkedin" name="linkedin" placeholder="" type="text"
                                                                class="form-control"
                                                                value="<?php echo set_value('linkedin') ?>" />
                                                            <span
                                                                class="text-danger"><?php echo form_error('linkedin'); ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="exampleInputEmail1"><?php echo $this->lang->line('instagram_url'); ?></label>
                                                            <input id="instagram" name="instagram" placeholder=""
                                                                type="text" class="form-control"
                                                                value="<?php echo set_value('instagram') ?>" />
                                                        </div>
                                                    </div>
                                            </div>
                                        </div>

                                         <!-- Other Details -->
                                         <div class="tshadow mb25 bozero">
                                            <h4 class="pagetitleh2"><?php echo $this->lang->line('other_details'); ?></h4>
                                            <div class="row around10">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="work_phone"><?php echo $this->lang->line('work_phone'); ?></label>
                                                        <input id="work_phone" name="work_phone" placeholder="" type="text" class="form-control" value="<?php echo set_value('work_phone') ?>" />
                                                        <span class="text-danger"><?php echo form_error('work_phone'); ?></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="work_email"><?php echo $this->lang->line('work_email'); ?></label>
                                                        <input id="work_email" name="work_email" placeholder="" type="text" class="form-control" value="<?php echo set_value('work_email') ?>" />
                                                        <span class="text-danger"><?php echo form_error('work_email'); ?></span>
                                                    </div>
                                                </div>
                
                                            </div>
                                        </div>

                                            <?php
                                 if ($sch_setting->staff_upload_documents) { ?>
                                    <div id='upload_documents_hide_show'>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="tshadow bozero">
                                                    <h4 class="pagetitleh2">
                                                        <?php echo $this->lang->line('upload_documents'); ?>
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
                                                                        <td><?php echo $this->lang->line('resume'); ?></td>
                                                                        <td>
                                                                            <input class="filestyle form-control" type='file' name='first_doc' id="doc1">
                                                                            <span class="text-danger"><?php echo form_error('first_doc'); ?></span>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>3.</td>
                                                                        <td><?php echo $this->lang->line('resignation_letter'); ?></td>
                                                                        <td>
                                                                            <input class="filestyle form-control" type='file' name='third_doc' id="doc3">
                                                                            <span class="text-danger"><?php echo form_error('third_doc'); ?></span>
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
                                                                        <td>2.</td>
                                                                        <td><?php echo $this->lang->line('joining_letter'); ?></td>
                                                                        <td>
                                                                            <input class="filestyle form-control" type='file' name='second_doc' id="doc2">
                                                                            <span class="text-danger"><?php echo form_error('second_doc'); ?></span>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>4.</td>
                                                                        <td>
                                                                            <div style="display: flex; align-items: center;">
                                                                                <select name="other_document_name[0]" class="form-control" style="margin-right: 5px;">
                                                                                    <option value="">Select Document Type</option>
                                                                                    <option value="certificate copy">Certificate Copy</option>
                                                                                    <option value="transcript copy">Transcript Copy</option>
                                                                                    <option value="marksheet copy">Marksheet Copy</option>
                                                                                    <option value="reference letter">Reference Letter</option>
                                                                                </select>
                                                                                <button type="button" id="add_document_btn" style="margin-left: 5px; padding: 6px 12px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">+</button>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <input class="filestyle form-control" type='file' name='other_document_file[0]' id="doc4">
                                                                            <span class="text-danger"><?php echo form_error('other_document_file'); ?></span>
                                                                        </td>
                                                                    </tr>
                                                                    <tbody id="additional_documents_container">
                                                                        <!-- Additional document rows will be added here -->
                                                                    </tbody>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                
                                    <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        let documentCounter = 1; // Start from 1 since 0 is already used
                                        
                                        document.getElementById('add_document_btn').addEventListener('click', function() {
                                            const container = document.getElementById('additional_documents_container');
                                            const newRow = document.createElement('tr');
                                            newRow.className = 'document-row';
                                            newRow.setAttribute('data-index', documentCounter);
                                            
                                            newRow.innerHTML = `
                                                <td></td>
                                                <td>
                                                    <div style="display: flex; align-items: center;">
                                                        <select name="other_document_name[${documentCounter}]" class="form-control" style="margin-right: 5px;">
                                                            <option value="">Select Document Type</option>
                                                            <option value="certificate copy">Certificate Copy</option>
                                                            <option value="transcript copy">Transcript Copy</option>
                                                            <option value="marksheet copy">Marksheet Copy</option>
                                                            <option value="reference letter">Reference Letter</option>
                                                        </select>
                                                        <button type="button" class="remove-document-btn" style="margin-left: 5px; padding: 6px 12px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">-</button>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input class="form-control" type="file" name="other_document_file[${documentCounter}]">Choose File
                                                </td>
                                            `;
                                            
                                            container.appendChild(newRow);
                                            documentCounter++;
                                            
                                            // Add event listener for remove button
                                            newRow.querySelector('.remove-document-btn').addEventListener('click', function() {
                                                newRow.remove();
                                            });
                                        });
                                    });
                                    </script>
                                <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" id="submitbtn"
                                class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
</div>
</section>
</div>


<script>
    $(function () {
        $('#form1').submit(function () {
            $("#submitbtn").button('loading');
        });
    })
</script>
<script type="text/javascript" src="<?php echo base_url(); ?>backend/dist/js/savemode.js"></script>

<?php $this->load->view('admin/staff/_hemis_employee_position_script'); ?>
<?php $this->load->view('admin/staff/_staff_hemis_org_unit_script'); ?>


<script type="text/javascript">
function updateSameAsPermamentStatus() {
    var isChecked = $('#same_as_permanent').is(':checked');
    if ($('#is_same_as_permanent_hidden').length === 0) {
        $('<input>').attr({
            type: 'hidden',
            id: 'is_same_as_permanent_hidden',
            name: 'is_same_as_permanent',
            value: '0'
        }).appendTo($('#same_as_permanent').parent());
    }
    $('#is_same_as_permanent_hidden').val(isChecked ? '1' : '0');
}

function copyPermanentAddress() {
    if (!$('#ugc_p_province').length) {
        return;
    }
    if ($('#same_as_permanent').is(':checked')) {
        var p = $('#ugc_p_province').val();
        var d = $('#ugc_p_district').val();
        var c = $('#ugc_p_local_level').val();
        if (p) { $('#ugc_t_province').val(p).trigger('change.staffugc'); }
        setTimeout(function () {
            if (d) { $('#ugc_t_district').val(d).trigger('change.staffugc'); }
            setTimeout(function () {
                if (c) { $('#ugc_t_local_level').val(c).trigger('change.staffugc'); }
                if (typeof syncUgcHiddenFieldsFromCascade === 'function') {
                    syncUgcHiddenFieldsFromCascade('ugc_t');
                }
            }, 300);
        }, 300);
        $('#temporary_ward_no').val($('#permanent_ward_no').val());
        $('#local_address').val($('#permanent_address').val());
    } else {
        $('#ugc_t_province').val('').trigger('change.staffugc');
        $('#temporary_ward_no').val('');
        $('#local_address').val('');
    }
    setTimeout(updateSameAsPermamentStatus, 500);
}

$(document).ready(function() {
    updateSameAsPermamentStatus();
    $('#same_as_permanent').on('change.staffcopy', function() {
        copyPermanentAddress();
    });
    $('#permanent_ward_no, #permanent_address, #temporary_ward_no, #local_address').on('change keyup', updateSameAsPermamentStatus);
});
</script>

<script type="text/javascript">
(function () {
    function bindFilePreview(inputId, previewId) {
        var input = document.getElementById(inputId);
        var previewContainer = document.getElementById(previewId);
        if (!input || !previewContainer) {
            return;
        }
        input.addEventListener('change', function () {
            previewContainer.innerHTML = '';
            for (var i = 0; i < this.files.length; i++) {
                var file = this.files[i];
                if (file.type.match('image.*')) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        var img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.maxWidth = '100px';
                        img.style.maxHeight = '100px';
                        img.style.marginRight = '5px';
                        previewContainer.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                }
            }
        });
    }
    bindFilePreview('citizenship', 'citizenship-preview');
    bindFilePreview('nationalId', 'nationalId-preview');
})();
</script>
<!-- NEPALI DATEPICKER - Handled by Global Initializer -->
<?php $staff_bs_ad_convert_on_load = true; ?>
<?php $this->load->view('admin/staff/_staff_bs_ad_date_script'); ?>
<?php $this->load->view('admin/staff/_staff_more_details_script'); ?>
<?php $this->load->view('admin/staff/_staff_ugc_address_script'); ?>

<script>
$(function () {
  // --- SAFEGUARD: force English-only on #dob ---
  var $dob = $('#dob');

  // Make sure it does NOT carry the Nepali class
  $dob.removeClass('nepali-datepicker');

  // Some Nepali picker builds add inline handlers or namespaced events.
  // Strip the common ones defensively:
  $dob.removeAttr('onfocus onclick onkeydown');
  try { $dob.off('.ndp'); } catch (e) {}       // if plugin used a namespace
  try { $dob.off('focus click keydown'); } catch (e) {}

  // Remove common injected popup containers (different builds use different classnames)
  $dob.siblings('.ndp-datepicker, .ndp-datepicker-container, .nepali-calendar, .nepali-date-picker').remove();

  // Re-init English picker cleanly
  if ($.fn.datepicker) {
    $dob.datepicker('destroy'); // in case it was partially bound
    $dob.datepicker({
      format: 'yyyy-mm-dd',
      autoclose: true
    }).data('ad-init', true);
  }

  // Optional: ensure your BS fields keep the Nepali picker
  if ($.fn.nepaliDatePicker) {
    $('.nepali-datepicker').each(function () {
      if (!$(this).data('bs-init')) {
        $(this).nepaliDatePicker({
          ndpYear: true,
          ndpMonth: true,
          ndpYearCount: 70
        }).data('bs-init', true);
      }
    });
  }
});
</script>








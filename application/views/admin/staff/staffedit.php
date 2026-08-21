<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-sitemap"></i> <?php echo $this->lang->line('human_resource'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <form id="form1" action="<?php echo site_url('admin/staff/edit/' . $staff['id']) ?>" id="employeeform" name="employeeform" method="post" accept-charset="utf-8" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <div class="box-body">
                            <div class="alert alert-info">
                                Staff email is their login username, password is generated automatically and send to staff email.
                            </div>
                            <div class="tshadow mb25 bozero">
                                <div class="box-tools pull-right pt3">
                                    <a class="btn btn-sm btn-primary" href="<?php echo base_url(); ?>admin/staff/import" autocomplete="off"><i class="fa fa-plus"></i> <?php echo $this->lang->line('import_staff'); ?></a>
                                </div>
                                <h4 class="pagetitleh2"><?php echo $this->lang->line('basic_information'); ?> </h4>

                                <div class="around10">
                                    <?php if ($this->session->flashdata('msg')) { ?>
                                        <?php echo $this->session->flashdata('msg');
                                        $this->session->unset_userdata('msg'); ?>
                                    <?php } ?>
                                    <?php echo $this->customlib->getCSRF(); ?>
                                    <?php $this->load->view('admin/staff/_staff_ugc_address_hiddens', array('staff' => isset($staff) ? $staff : array())); ?>

                                    <div class="row">
                                        <?php if (!$sch_setting->staffid_auto_insert) { ?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="employee_id"><?php echo $this->lang->line('staff_id'); ?></label><small class="req"> *</small>
                                                    <input id="employee_id" name="employee_id" type="text" class="form-control" value="<?php echo set_value('employee_id', $staff['employee_id']); ?>" />
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
                                                        <option value="<?php echo $role['id'] ?>" <?php echo set_select('role', $role['id'], ($staff['role_id'] == $role['id'])); ?>><?php echo $role["name"] ?></option>
                                                    <?php } ?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('role'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="salutation"><?php echo $this->lang->line('salutation'); ?></label><small class="req"> *</small>
                                                <input id="salutation" name="salutation" type="text" class="form-control" placeholder="Mr. / Ms. / Mrs." value="<?php echo set_value('salutation', $staff['salutation'] ?? ''); ?>" />
                                                <span class="text-danger"><?php echo form_error('salutation'); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Row 2: First Name, Middle Name, Last Name -->
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="name"><?php echo $this->lang->line('first_name'); ?></label><small class="req"> *</small>
                                                <input id="name" name="name" type="text" class="form-control" value="<?php echo set_value('name', $staff['name']); ?>" />
                                                <span class="text-danger"><?php echo form_error('name'); ?></span>
                                            </div>
                                        </div>
                                       
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="middle_name"><?php echo $this->lang->line('middle_name'); ?></label>
                                                <input id="middle_name" name="middle_name" type="text" class="form-control" value="<?php echo set_value('middle_name', $staff['middle_name'] ?? ''); ?>" />
                                                <span class="text-danger"><?php echo form_error('middle_name'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="surname"><?php echo $this->lang->line('last_name'); ?></label>
                                                <input id="surname" name="surname" type="text" class="form-control" value="<?php echo set_value('surname', $staff['surname'] ?? ''); ?>" />
                                                <span class="text-danger"><?php echo form_error('surname'); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Row 3: Nepali Names -->
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="first_name_np"><?php echo $this->lang->line('first_name_np'); ?></label>
                                                <input id="first_name_np" name="firstnameNp" type="text" class="form-control" value="<?php echo set_value('firstnameNp', $staff['firstnameNp'] ?? ''); ?>" />
                                                <span class="text-danger"><?php echo form_error('firstnameNp'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="middle_name_np"><?php echo $this->lang->line('middle_name_np'); ?></label>
                                                <input id="middle_name_np" name="middlenameNp" type="text" class="form-control" value="<?php echo set_value('middlenameNp', $staff['middlenameNp'] ?? ''); ?>" />
                                                <span class="text-danger"><?php echo form_error('middlenameNp'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="last_name_np"><?php echo $this->lang->line('last_name_np'); ?></label>
                                                <input id="last_name_np" name="lastnameNp" type="text" class="form-control" value="<?php echo set_value('lastnameNp', $staff['lastnameNp'] ?? ''); ?>" />
                                                <span class="text-danger"><?php echo form_error('lastnameNp'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Row 4: Email, Gender -->
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="email"><?php echo $this->lang->line('email'); ?></label><small class="req"> *</small>
                                                <input id="email" name="email" type="text" class="form-control" value="<?php echo set_value('email', $staff['email'] ?? ''); ?>" />
                                                <span class="text-danger"><?php echo form_error('email'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="gender"><?php echo $this->lang->line('gender'); ?></label><small class="req"> *</small>
                                                <select class="form-control" name="gender">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php foreach ($genderList as $key => $value) { ?>
                                                        <option value="<?php echo $key; ?>" <?php echo set_select('gender', $key, ($staff['gender'] == $key)); ?>><?php echo $value; ?></option>
                                                    <?php } ?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('gender'); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Row 5: Date of Birth, Date of Joining, Phone, Emergency Contact -->
                                    <div class="row">
                                    <?php
                                    $edit_dob_bs = set_value('dob_bs');
                                    if ($edit_dob_bs === '' && !empty($staff['dob_bs'])) {
                                        $edit_dob_bs = str_replace('/', '-', $staff['dob_bs']);
                                    }
                                    $edit_dob_ad = set_value('dob');
                                    if ($edit_dob_ad === '' && !empty($staff['dob']) && $staff['dob'] !== '0000-00-00') {
                                        $edit_dob_ad = date('Y-m-d', strtotime($staff['dob']));
                                    }
                                    $edit_join_bs = set_value('date_of_joining');
                                    if ($edit_join_bs === '' && !empty($staff['date_of_joining'])) {
                                        $edit_join_bs = str_replace('/', '-', $staff['date_of_joining']);
                                    }
                                    ?>
                                    <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="dob_bs"><?php echo $this->lang->line('date_of_birth_bs'); ?><small class="req"> *</small></label>
                                                <input id="dob_bs" name="dob_bs" placeholder="YYYY-MM-DD" type="text" class="form-control nepali-datepicker" value="<?php echo html_escape($edit_dob_bs); ?>" required />
                                                <span class="text-danger"><?php echo form_error('dob_bs'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="dob"><?php echo $this->lang->line('date_of_birth'); ?></label><small class="req"> </small>
                                                <input id="dob" name="dob" type="text" class="form-control date no-nepali-datepicker" data-no-nepali="true" placeholder="YYYY-MM-DD" value="<?php echo html_escape($edit_dob_ad); ?>" />
                                                <span class="help-block small text-muted">Auto-filled from B.S. date above.</span>
                                                <span class="text-danger"><?php echo form_error('dob'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="date_of_joining"><?php echo $this->lang->line('date_of_joining'); ?> (BS)</label><small class="req"> *</small>
                                                <input id="date_of_joining" name="date_of_joining" type="text" class="form-control nepali-datepicker" placeholder="YYYY-MM-DD" value="<?php echo html_escape($edit_join_bs); ?>" />
                                                <span class="text-danger"><?php echo form_error('date_of_joining'); ?></span>
                                            </div>
                                        </div>
                                        <!-- Fixed: Phone -->
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="contact_no"><?php echo $this->lang->line('phone'); ?></label><small class="req"> *</small>
                                                <input id="mobileno" name="contact_no" placeholder="" type="text" class="form-control" value="<?php echo set_value('contact_no', $staff['contact_no'] ?? ''); ?>" />
                                                <span class="text-danger"><?php echo form_error('contact_no'); ?></span>
                                            </div>
                                        </div>
                                        <!-- Fixed: Emergency Contact Number -->
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="emergency_contact_no"><?php echo $this->lang->line('emergency_contact_number'); ?></label>
                                                <input id="emergency_contact_no" name="emergency_contact_no" placeholder="" type="text" class="form-control" value="<?php echo set_value('emergency_contact_no', $staff['emergency_contact_no'] ?? ''); ?>" />
                                                <span class="text-danger"><?php echo form_error('emergency_contact_no'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="marital_status"><?php echo $this->lang->line('marital_status'); ?></label>
                                                <select class="form-control" name="marital_status">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php foreach ($marital_status as $makey => $mavalue) { ?>
                                                        <option value="<?php echo $mavalue ?>" <?php echo set_select('marital_status', $mavalue, ($staff['marital_status'] == $mavalue)); ?>><?php echo $mavalue; ?></option>
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
                                                    <option value="Hinduism" <?php echo set_select('religion', 'Hinduism', ($staff['religion'] == 'Hinduism')); ?>>Hinduism</option>
                                                    <option value="Buddhism" <?php echo set_select('religion', 'Buddhism', ($staff['religion'] == 'Buddhism')); ?>>Buddhism</option>
                                                    <option value="Islam" <?php echo set_select('religion', 'Islam', ($staff['religion'] == 'Islam')); ?>>Islam</option>
                                                    <option value="Christianity" <?php echo set_select('religion', 'Christianity', ($staff['religion'] == 'Christianity')); ?>>Christianity</option>
                                                    <option value="Kirat" <?php echo set_select('religion', 'Kirat', ($staff['religion'] == 'Kirat')); ?>>Kirat</option>
                                                    <option value="Jainism" <?php echo set_select('religion', 'Jainism', ($staff['religion'] == 'Jainism')); ?>>Jainism</option>
                                                    <option value="Sikhism" <?php echo set_select('religion', 'Sikhism', ($staff['religion'] == 'Sikhism')); ?>>Sikhism</option>
                                                    <option value="Others" <?php echo set_select('religion', 'Others', ($staff['religion'] == 'Others')); ?>>Others</option>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('religion'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="caste"><?php echo $this->lang->line('caste'); ?></label>
                                                <select name="caste" class="form-control">
                                                    <option value="Brahmin" <?php echo set_select('caste', 'Brahmin', ($staff['caste'] == 'Brahmin')); ?>>Brahmin</option>
                                                    <option value="Chhetri" <?php echo set_select('caste', 'Chhetri', ($staff['caste'] == 'Chhetri')); ?>>Chhetri</option>
                                                    <option value="Dalit" <?php echo set_select('caste', 'Dalit', ($staff['caste'] == 'Dalit')); ?>>Dalit</option>
                                                    <option value="Janajati" <?php echo set_select('caste', 'Janajati', ($staff['caste'] == 'Janajati')); ?>>Janajati</option>
                                                    <option value="Kami" <?php echo set_select('caste', 'Kami', ($staff['caste'] == 'Kami')); ?>>Kami</option>
                                                    <option value="Limbu" <?php echo set_select('caste', 'Limbu', ($staff['caste'] == 'Limbu')); ?>>Limbu</option>
                                                    <option value="Madhesi" <?php echo set_select('caste', 'Madhesi', ($staff['caste'] == 'Madhesi')); ?>>Madhesi</option>
                                                    <option value="Magar" <?php echo set_select('caste', 'Magar', ($staff['caste'] == 'Magar')); ?>>Magar</option>
                                                    <option value="Muslim" <?php echo set_select('caste', 'Muslim', ($staff['caste'] == 'Muslim')); ?>>Muslim</option>
                                                    <option value="Newar" <?php echo set_select('caste', 'Newar', ($staff['caste'] == 'Newar')); ?>>Newar</option>
                                                    <option value="Rai" <?php echo set_select('caste', 'Rai', ($staff['caste'] == 'Rai')); ?>>Rai</option>
                                                    <option value="Sarki" <?php echo set_select('caste', 'Sarki', ($staff['caste'] == 'Sarki')); ?>>Sarki</option>
                                                    <option value="Tamang" <?php echo set_select('caste', 'Tamang', ($staff['caste'] == 'Tamang')); ?>>Tamang</option>
                                                    <option value="Tharu" <?php echo set_select('caste', 'Tharu', ($staff['caste'] == 'Tharu')); ?>>Tharu</option>
                                                    <option value="Others" <?php echo set_select('caste', 'Others', ($staff['caste'] == 'Others')); ?>>Others</option>
                                                </select>
                                                <span class="help-block">Syncs to HEMIS as <code>ethnicity</code>.</span>
                                                <span class="text-danger"><?php echo form_error('caste'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="file"><?php echo $this->lang->line('photo'); ?></label>
                                                <div>
                                                    <?php if (!empty($staff['image'])) { ?>
                                                    <img src="<?php echo base_url() . "uploads/staff_images/" . $staff['image']; ?>" class="img-thumbnail" width="100" height="100">
                                                    <?php } ?>
                                                    <input class="filestyle form-control" type='file' name='file' id="file" size='20' />
                                                </div>
                                                <span class="text-danger"><?php echo form_error('file'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="father_name"><?php echo $this->lang->line('father_name'); ?></label>
                                                <input id="father_name" name="father_name" type="text" class="form-control" value="<?php echo set_value('father_name', $staff['father_name'] ?? ''); ?>" />
                                                <span class="text-danger"><?php echo form_error('father_name'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="mother_name"><?php echo $this->lang->line('mother_name'); ?></label>
                                                <input id="mother_name" name="mother_name" type="text" class="form-control" value="<?php echo set_value('mother_name', $staff['mother_name'] ?? ''); ?>" />
                                                <span class="text-danger"><?php echo form_error('mother_name'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="spouse_name">Spouse name</label>
                                                <input id="spouse_name" name="spouse_name" type="text" class="form-control" maxlength="255" value="<?php echo htmlspecialchars(isset($staff['spouse_name']) ? $staff['spouse_name'] : '', ENT_QUOTES, 'UTF-8'); ?>" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="dependent_info">Dependent info</label>
                                                <textarea id="dependent_info" name="dependent_info" class="form-control" rows="2"><?php echo htmlspecialchars(isset($staff['dependent_info']) ? $staff['dependent_info'] : '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <?php $this->load->view('admin/staff/_staff_hemis_basic_fields', array(
                                        'staff' => $staff,
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
                                        <?php $this->load->view('admin/staff/_staff_ugc_address_row', array('prefix' => 'ugc_p', 'staff' => $staff)); ?>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="permanent_ward_no"><?php echo $this->lang->line('ward'); ?></label><small class="req"> *</small>
                                                <select id="permanent_ward_no" name="permanent_ward_no" class="form-control">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php for ($i = 1; $i <= 32; $i++) { ?>
                                                        <option value="<?php echo $i; ?>" <?php echo set_select('permanent_ward_no', $i, ($staff['permanent_ward_no'] == $i)); ?>><?php echo $i; ?></option>
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
                                                <input id="permanent_house_no" name="permanent_house_no" type="text" class="form-control" value="<?php echo set_value('permanent_house_no', $staff['permanent_house_no'] ?? ''); ?>" />
                                                <span class="text-danger"><?php echo form_error('permanent_house_no'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="permanent_address"><?php echo $this->lang->line('permanent_address'); ?></label>
                                                <input id="permanent_address" name="permanent_address" type="text" class="form-control" value="<?php echo set_value('permanent_address', $staff['permanent_address'] ?? ''); ?>" />
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
                                                <input type="checkbox" class="form-check-input" id="same_as_permanent" name="same_as_permanent" value="1" <?php echo (isset($staff['is_same_as_permanent']) && $staff['is_same_as_permanent'] == 1) ? 'checked' : ''; ?> onclick="copyPermanentAddress()">
                                                <label class="form-check-label" for="same_as_permanent"><?php echo $this->lang->line('if_permanent_address_is_current_address'); ?></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <?php $this->load->view('admin/staff/_staff_ugc_address_row', array('prefix' => 'ugc_t', 'staff' => $staff)); ?>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="temporary_ward_no"><?php echo $this->lang->line('ward'); ?></label>
                                                <select id="temporary_ward_no" name="temporary_ward_no" class="form-control">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php for ($i = 1; $i <= 32; $i++) { ?>
                                                        <option value="<?php echo $i; ?>" <?php echo set_select('temporary_ward_no', $i, ($staff['temporary_ward_no'] == $i)); ?>><?php echo $i; ?></option>
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
                                                <input id="local_address" name="local_address" type="text" class="form-control" value="<?php echo set_value('local_address', $staff['local_address'] ?? ''); ?>" />
                                                <span class="text-danger"><?php echo form_error('local_address'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Qualification -->
                            <div class="tshadow mb25 bozero">
                                <h3 class="pagetitleh2">Qualification</h3>
                                <div class="around10">
                                    <?php $this->load->view('admin/staff/_staff_hemis_qualification_section', array('staff' => $staff)); ?>

                                    <!-- Overall Qualification and Work Experience -->
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="qualification">Overall Qualification</label>
                                                <textarea id="qualification" name="qualification" class="form-control" rows="4" placeholder="Overall qualification summary"><?php echo set_value('qualification', $staff['qualification']); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="work_exp">Work Experience</label>
                                                <textarea id="work_exp" name="work_exp" class="form-control" rows="4" placeholder="Work experience details"><?php echo set_value('work_exp', $staff['work_exp']); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
<!-- Documents: Citizenship and National ID -->
<div class="tshadow mb25 bozero">
    <h3 class="pagetitleh2"><?php echo $this->lang->line('additional_document'); ?></h3>
    <div class="around10">
        <div class="row">
            <!-- Citizenship -->
            <div class="col-md-6">
                <div class="form-group">
                    <label for="citizenship"><?php echo $this->lang->line('citizenship'); ?></label>
                    <?php
                    if (isset($staff_doc['citizenship']) && !empty($staff_doc['citizenship'])) {
                        $citizenship_files = is_array(json_decode($staff_doc['citizenship'])) ? json_decode($staff_doc['citizenship']) : [$staff_doc['citizenship']];
                        foreach ($citizenship_files as $index => $file) {
                            $file = trim($file);
                            if (!empty($file)) {
                                $staff_dir_path = FCPATH . 'uploads/staff_documents/' . $staff['id'] . '/' . $file;
                                $fallback_url = base_url('uploads/staff_documents/' . $file);
                                $primary_url = base_url('uploads/staff_documents/' . $staff['id'] . '/' . $file);
                                $final_url = file_exists($staff_dir_path) ? $primary_url : $fallback_url;
                    ?>
                                <div class="existing-file mt-2 mb-2">
                                    <span class="text-muted small">File <?php echo $index + 1; ?>:</span>
                                    <a href="<?php echo $final_url; ?>" target="_blank" class="text-primary">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <span class="text-muted small"><?php echo htmlspecialchars($file); ?></span>
                                </div>
                    <?php
                            }
                        }
                    ?>
                        <input type="hidden" name="old_citizenship" value='<?php echo htmlspecialchars($staff_doc['citizenship']); ?>'>
                    <?php } ?>
                    <input class="filestyle form-control" type="file" name="citizenship[]" id="citizenship" multiple />
                    <span class="text-danger"><?php echo form_error('citizenship'); ?></span>
                </div>
            </div>

        <!-- National ID -->
<div class="col-md-6">
    <div class="form-group">
        <label for="nationalId"><?php echo $this->lang->line('national_id'); ?></label>
        <?php
        if (isset($staff_doc['national_id']) && !empty($staff_doc['national_id'])) {
            $nationalId_files = is_array(json_decode($staff_doc['national_id'])) ? json_decode($staff_doc['national_id']) : [$staff_doc['national_id']];
            foreach ($nationalId_files as $index => $file) {
                $file = trim($file);
                if (!empty($file)) {
                    $staff_dir_path = FCPATH . 'uploads/staff_documents/' . $staff['id'] . '/' . $file;
                    $fallback_url = base_url('uploads/staff_documents/' . $file);
                    $primary_url = base_url('uploads/staff_documents/' . $staff['id'] . '/' . $file);
                    $final_url = file_exists($staff_dir_path) ? $primary_url : $fallback_url;
        ?>
                    <div class="existing-file mt-2 mb-2">
                        <span class="text-muted small">File <?php echo $index + 1; ?>:</span>
                        <a href="<?php echo $final_url; ?>" target="_blank" class="text-primary">
                            <i class="fa fa-eye"></i>
                        </a>
                        <span class="text-muted small"><?php echo htmlspecialchars($file); ?></span>
                    </div>
        <?php
                }
            }
        ?>
            <input type="hidden" name="old_nationalId" value='<?php echo htmlspecialchars($staff_doc['national_id']); ?>'>
        <?php } ?>
        <input class="filestyle form-control" type="file" name="nationalId[]" id="nationalId" multiple />
        <span class="text-danger"><?php echo form_error('nationalId'); ?></span>
    </div>
</div>

<div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="ctz_issue_district_code"><?php echo $this->lang->line('ctz_issue_district_code'); ?></label>
                                                        <input id="ctz_issue_district_code" name="ctz_issue_district_code" type="text" class="form-control" 
                                                            value="<?php echo isset($staff['ctz_issue_district_code']) ? html_escape($staff['ctz_issue_district_code']) : ''; ?>" />
                                                        <span class="text-danger"><?php echo form_error('ctz_issue_district_code'); ?></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="nid_no"><?php echo $this->lang->line('nid_no'); ?></label>
                                                        <input id="nid_no" name="nid_no" type="text" class="form-control" 
                                                            value="<?php echo isset($staff['nid_no']) ? html_escape($staff['nid_no']) : ''; ?>" />
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
                                                        <input id="epf_no" name="epf_no" placeholder="" type="text" class="form-control" value="<?php echo set_value('epf_no', $staff['epf_no']); ?>" />
                                                        <span class="text-danger"><?php echo form_error('epf_no'); ?></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="basic_salary"><?php echo $this->lang->line('basic_salary'); ?></label>
                                                        <input type="text" class="form-control" name="basic_salary" value="<?php echo set_value('basic_salary', $staff['basic_salary']); ?>">
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
                                                        <input id="facebook" name="facebook" placeholder="" type="text" class="form-control" value="<?php echo set_value('facebook', $staff['facebook']); ?>" />
                                                        <span class="text-danger"><?php echo form_error('facebook'); ?></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="twitter"><?php echo $this->lang->line('twitter_url'); ?></label>
                                                        <input id="twitter" name="twitter" placeholder="" type="text" class="form-control" value="<?php echo set_value('twitter', $staff['twitter']); ?>" />
                                                        <span class="text-danger"><?php echo form_error('twitter'); ?></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="linkedin"><?php echo $this->lang->line('linkedin_url'); ?></label>
                                                        <input id="linkedin" name="linkedin" placeholder="" type="text" class="form-control" value="<?php echo set_value('linkedin', $staff['linkedin']); ?>" />
                                                        <span class="text-danger"><?php echo form_error('linkedin'); ?></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="instagram"><?php echo $this->lang->line('instagram_url'); ?></label>
                                                        <input id="instagram" name="instagram" placeholder="" type="text" class="form-control" value="<?php echo set_value('instagram', $staff['instagram']); ?>" />
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
                                                        <input id="work_phone" name="work_phone" placeholder="" type="text" class="form-control" 
                                                            value="<?php echo isset($staff['work_phone']) ? html_escape($staff['work_phone']) : ''; ?>" />
                                                        <span class="text-danger"><?php echo form_error('work_phone'); ?></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="work_email"><?php echo $this->lang->line('work_email'); ?></label>
                                                        <input id="work_email" name="work_email" placeholder="" type="email" class="form-control" 
                                                            value="<?php echo isset($staff['work_email']) ? html_escape($staff['work_email']) : ''; ?>" />
                                                        <span class="text-danger"><?php echo form_error('work_email'); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if ($sch_setting->staff_upload_documents) { ?>
                                        <div id='upload_documents_hide_show'>
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
                        <?php if(isset($staff_doc[0]['document']) && !empty($staff_doc[0]['document'])){ ?>
                        <div class="existing-file mt-2">
                            <a href="<?php echo base_url('uploads/staff_documents/' . htmlspecialchars($staff_doc[0]['document'])); ?>" 
                               target="_blank" title="View current file" class="text-primary">
                               <i class="fa fa-eye"></i> View current file
                            </a>
                            <input type="hidden" name="old_first_doc" value="<?php echo htmlspecialchars($staff_doc[0]['document']); ?>">
                        </div>
                        <?php } ?>
                        <span class="text-danger"><?php echo form_error('first_doc'); ?></span>
                    </td>
                </tr>
                <tr>
                    <td>3.</td>
                    <td><?php echo $this->lang->line('resignation_letter'); ?></td>
                    <td>
                        <input class="filestyle form-control" type='file' name='third_doc' id="doc3">
                        <?php if(isset($staff_doc[2]['document']) && !empty($staff_doc[2]['document'])){ ?>
                        <div class="existing-file mt-2">
                            <a href="<?php echo base_url('uploads/staff_documents/' . htmlspecialchars($staff_doc[2]['document'])); ?>" 
                               target="_blank" title="View current file" class="text-primary">
                               <i class="fa fa-eye"></i> View current file
                            </a>
                            <input type="hidden" name="old_third_doc" value="<?php echo htmlspecialchars($staff_doc[2]['document']); ?>">
                        </div>
                        <?php } ?>
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
                        <?php if(isset($staff_doc[1]['document']) && !empty($staff_doc[1]['document'])){ ?>
                        <div class="existing-file mt-2">
                            <a href="<?php echo base_url('uploads/staff_documents/' . htmlspecialchars($staff_doc[1]['document'])); ?>" 
                               target="_blank" title="View current file" class="text-primary">
                               <i class="fa fa-eye"></i> View current file
                            </a>
                            <input type="hidden" name="old_second_doc" value="<?php echo htmlspecialchars($staff_doc[1]['document']); ?>">
                        </div>
                        <?php } ?>
                        <span class="text-danger"><?php echo form_error('second_doc'); ?></span>
                    </td>
                </tr>

                <?php
                $dynamicDocCounter = 0;
                if (isset($staff_doc[3]['document_details']) && is_array($staff_doc[3]['document_details'])) {
                    foreach ($staff_doc[3]['document_details'] as $index => $doc_detail) {
                        $dynamicDocCounter = max($dynamicDocCounter, $index + 1);
                ?>
                <tr class="fetched-document-row">
                    <td><?php echo 4 + $index; ?>.</td>
                    <td>
                        <div style="display: flex; align-items: center;">
                            <select name="other_document_name[]" class="form-control" style="margin-right: 5px;">
                                <option value="">Select Document Type</option>
                                <option value="certificate copy" <?php echo ($doc_detail['name'] == 'certificate copy') ? 'selected' : ''; ?>>Certificate Copy</option>
                                <option value="transcript copy" <?php echo ($doc_detail['name'] == 'transcript copy') ? 'selected' : ''; ?>>Transcript Copy</option>
                                <option value="marksheet copy" <?php echo ($doc_detail['name'] == 'marksheet copy') ? 'selected' : ''; ?>>Marksheet Copy</option>
                                <option value="reference letter" <?php echo ($doc_detail['name'] == 'reference letter') ? 'selected' : ''; ?>>Reference Letter</option>
                            </select>
                            <button type="button" class="remove_stored_document_btn" data-index="<?php echo $index; ?>" style="margin-left: 5px; padding: 6px 12px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">-</button>
                        </div>
                    </td>
                    <td>
                        <input class="filestyle form-control" type="file" name="other_document_file[]" id="doc<?php echo 4 + $index; ?>">
                        <?php if(!empty($doc_detail['file'])){ ?>
                        <div class="existing-file mt-2">
                            <a href="<?php echo base_url('uploads/staff_documents/' . htmlspecialchars($doc_detail['file'])); ?>" 
                               target="_blank" title="View current file" class="text-primary">
                               <i class="fa fa-eye"></i> View current file
                            </a>
                            <input type="hidden" name="old_other_doc_file[]" value="<?php echo htmlspecialchars($doc_detail['file']); ?>">
                            <input type="hidden" name="old_other_doc_name[<?php echo $index; ?>]" value="<?php echo htmlspecialchars($doc_detail['name']); ?>">
                        </div>
                        <?php } ?>
                        <span class="text-danger"><?php echo form_error('other_document_file['.$index.']'); ?></span>
                        <!-- Hidden field to mark for deletion -->
                        <input type="hidden" name="delete_document[<?php echo $index; ?>]" value="0" class="delete_marker">
                    </td>
                </tr>
                <?php }} ?>

                <!-- Add Document Button Row - appears right after fetched documents -->
                <tr id="add_document_button_row">
                    <td></td>
                    <td>
                        <button type="button" id="add_document_btn_main" data-clicked="false" style="padding: 10px 20px; background: linear-gradient(45deg, #007bff, #0056b3); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 14px; box-shadow: 0 2px 4px rgba(0,123,255,0.3); transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 1px;">
                            ✚ ADD DOCUMENT
                        </button>
                    </td>
                    <td></td>
                </tr>
            </tbody>
            <tbody id="additional_documents_container">
                <!-- Dynamically added document rows will go here -->
            </tbody>
        </table>
    </div>
</div>
                                            </div>
                                        </div>
                                        <?php } ?>

                                        <div class="tshadow mb25 bozero">
                                            <h4 class="pagetitleh2"><?php echo $this->lang->line('leave_allocation'); ?></h4>
                                            <p class="text-muted small"><?php echo $this->lang->line('leave_allocation_hint'); ?></p>
                                            <div class="row around10">
                                                <?php
                                                if (!empty($leave_types_list)) {
                                                    foreach ($leave_types_list as $lt_row) {
                                                        $lt_id = (int) $lt_row['id'];
                                                        $val   = isset($leave_alloc_by_type[$lt_id]) ? $leave_alloc_by_type[$lt_id] : '';
                                                        if ($val !== '' && $val !== null && is_numeric($val)) {
                                                            $val = 0 + $val;
                                                        }
                                                ?>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label><?php echo htmlspecialchars($lt_row['type'], ENT_QUOTES, 'UTF-8'); ?></label>
                                                        <input type="number" name="leave_alloc[<?php echo $lt_id; ?>]" class="form-control" min="0" step="0.5" placeholder="<?php echo $this->lang->line('days'); ?>"
                                                            value="<?php echo $val === '' || $val === null ? '' : htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8'); ?>" />
                                                    </div>
                                                </div>
                                                <?php
                                                    }
                                                } else {
                                                    echo '<div class="col-md-12"><p class="text-warning">' . $this->lang->line('leave_allocation_no_types') . '</p></div>';
                                                }
                                                ?>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <input type="hidden" name="staff_id" value="<?php echo $staff['id']; ?>">
                            <button type="submit" id="submitbtn" class="btn btn-info pull-right"><?php echo $this->lang->line('update'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
// Use a namespace to avoid conflicts
window.DocumentManager = window.DocumentManager || {};

if (!window.DocumentManager.initialized) {
    window.DocumentManager.initialized = true;
    
    $(document).ready(function() {
        let documentCounter = <?php echo 4 + $dynamicDocCounter; ?>;

        // Remove ALL existing handlers first
        $(document).off('click', '#add_document_btn_main');
        $(document).off('click', '.remove_stored_document_btn');
        $(document).off('click', '.remove_document_btn');

        // Add document functionality with strict single execution
        $('#add_document_btn_main').on('click', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            
            // Check if button is already processing
            if ($(this).attr('data-clicked') === 'true') {
                return false;
            }
            
            // Mark button as processing
            $(this).attr('data-clicked', 'true');
            $(this).prop('disabled', true);
            
            console.log('Adding document row #' + documentCounter);
            
            let newRow = `
                <tr class="dynamic-document-row">
                    <td>${documentCounter}.</td>
                    <td>
                        <div style="display: flex; align-items: center;">
                            <select name="other_document_name[]" class="form-control" style="margin-right: 5px;">
                                <option value="">Select Document Type</option>
                                <option value="certificate copy">Certificate Copy</option>
                                <option value="transcript copy">Transcript Copy</option>
                                <option value="marksheet copy">Marksheet Copy</option>
                                <option value="reference letter">Reference Letter</option>
                            </select>
                            <button type="button" class="remove_document_btn" style="margin-left: 5px; padding: 6px 12px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">-</button>
                        </div>
                    </td>
                    <td>
                        <input class="filestyle form-control" type="file" name="other_document_file[]" id="doc${documentCounter}">
                        <span class="text-danger"></span>
                    </td>
                </tr>
            `;
            
            $('#additional_documents_container').append(newRow);
            documentCounter++;
            
            // Reset button state after short delay
            setTimeout(() => {
                $(this).attr('data-clicked', 'false');
                $(this).prop('disabled', false);
            }, 500);
            
            return false;
        });

        // Remove stored document functionality
        $(document).on('click', '.remove_stored_document_btn', function() {
            let row = $(this).closest('tr');
            let index = $(this).data('index');
            
            row.find('.delete_marker').val('1');
            row.hide();
            renumberAllRows();
        });

        // Remove dynamic document functionality
        $(document).on('click', '.remove_document_btn', function() {
            $(this).closest('tr').remove();
        });

        function renumberAllRows() {
            let counter = 4;
            
            $('.fetched-document-row:visible').each(function() {
                $(this).find('td:first').text(counter + '.');
                counter++;
            });
            
            $('.dynamic-document-row').each(function() {
                $(this).find('td:first').text(counter + '.');
                counter++;
            });
            
            documentCounter = counter;
        }
    });
}

$(function () {
    $('#form1').submit(function () {
        $("#submitbtn").button('loading');
    });
});
</script>

<script type="text/javascript" src="<?php echo base_url(); ?>backend/dist/js/savemode.js"></script>

<script>
    $(document).ready(function () {
        // Initialize dynamicDocCounter based on existing documents
        let dynamicDocCounter = <?php echo isset($staff_doc[3]['document_details']) ? count($staff_doc[3]['document_details']) : 0; ?>;

        // Handler for the main '+' button (initial dynamic row)
        $(document).on('click', '#add_document_btn_main', function() {
            const container = $('#additional_documents_container');
            const newRowHtml = `
                <tr>
                    <td>${4 + dynamicDocCounter}.</td>
                    <td>
                        <div style="display: flex; align-items: center;">
                            <select name="other_document_name[]" class="form-control" style="margin-right: 5px;">
                                <option value="">Select Document Type</option>
                                <option value="certificate copy">Certificate Copy</option>
                                <option value="transcript copy">Transcript Copy</option>
                                <option value="marksheet copy">Marksheet Copy</option>
                                <option value="reference letter">Reference Letter</option>
                            </select>
                            <button type="button" class="remove_document_btn" style="margin-left: 5px; padding: 6px 12px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">-</button>
                        </div>
                    </td>
                    <td>
                        <input class="filestyle form-control" type='file' name='other_document_file[]' id="doc${4 + dynamicDocCounter}">
                        <span class="text-danger"></span>
                    </td>
                </tr>
            `;
            const newRow = $(newRowHtml);
            container.append(newRow);
            dynamicDocCounter++;

            // Re-initialize filestyle for the newly added input
            newRow.find('.filestyle').filestyle({
                'input': false,
                'buttonText': 'Choose File'
            });

            // Update row numbers after adding
            updateDocumentRowNumbers();
        });

        // Handle document removal (delegated event for '-' button on dynamic rows)
        $(document).on('click', '.remove_document_btn', function() {
            const rowToRemove = $(this).closest('tr');
            rowToRemove.remove();
            updateDocumentRowNumbers();

            // If the last dynamic row was removed, and there are no other dynamic rows,
            // ensure the initial row (item 4) has the '+' button
            if ($('#additional_documents_container tr').length === 0) {
                // Find the main hardcoded row for item 4 (which might now be the last visible dynamic row)
                const lastVisibleRow = $('table.table tbody tr').last();
                if (lastVisibleRow.find('#add_document_btn_main').length === 0) {
                    lastVisibleRow.find('.remove_document_btn').replaceWith('<button type="button" id="add_document_btn_main" style="margin-left: 5px; padding: 6px 12px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">+</button>');
                }
            }
        });

        function updateDocumentRowNumbers() {
            let currentNumber = 4;
            // Update existing dynamic rows (if any)
            $('#additional_documents_container tr').each(function(index) {
                $(this).find('td:first').text(`${currentNumber + index}.`);
                $(this).find('input[type="file"]').attr('id', `doc${currentNumber + index}`);
                $(this).find('select').attr('name', 'other_document_name[]'); // Re-index names
                $(this).find('input[type="file"]').attr('name', 'other_document_file[]'); // Re-index names
            });

            // Update the hardcoded row #4's number if it exists and there are no dynamic rows initially
            // This part is crucial: we need to find the specific row that contains the initial + button.
            // If the initial row has dynamic docs, it will be the one with id="add_document_btn_main"
            // If there are no dynamic docs, it's just the initial row.
            const initialAddButtonRow = $('button#add_document_btn_main').closest('tr');
            if (initialAddButtonRow.length > 0) {
                initialAddButtonRow.find('td:first').text(`${currentNumber + $('#additional_documents_container tr').length}.`);
                initialAddButtonRow.find('select').attr('name', `other_document_name[${$('#additional_documents_container tr').length}]`);
                initialAddButtonRow.find('input[type="file"]').attr('name', `other_document_file[${$('#additional_documents_container tr').length}]`);
            }
            
            dynamicDocCounter = $('#additional_documents_container tr').length; // Correctly set counter for new additions based on current dynamic rows
        }

        

        // Initial setup of dynamic document rows
        updateDocumentRowNumbers();

        // Ensure initial filestyle is applied
        $('.filestyle').filestyle({
            'input': false,
            'buttonText': 'Choose File'
        });
    });
</script>

<script type="text/javascript">
$(document).ready(function() {
    // Legacy province/district dropdowns removed — UGC/HEMIS cascade handles addresses.
    $('#same_as_permanent').on('change', function() {
        if (typeof copyPermanentAddress === 'function') {
            copyPermanentAddress();
        }
    });
});
</script>

<script type="text/javascript">
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
}
</script>
<!-- NEPALI DATEPICKER - Handled by Global Initializer -->
<?php $staff_bs_ad_convert_on_load = false; ?>
<?php $this->load->view('admin/staff/_staff_bs_ad_date_script'); ?>
<?php $this->load->view('admin/staff/_staff_more_details_script'); ?>
<?php $this->load->view('admin/staff/_hemis_employee_position_script'); ?>
<?php $this->load->view('admin/staff/_staff_ugc_address_script'); ?>
<?php $this->load->view('admin/staff/_staff_hemis_org_unit_script'); ?>
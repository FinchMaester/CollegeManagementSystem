<div class="content-wrapper">  
    <section class="content">
        <div class="row">
       
            <?php $this->load->view('setting/_settingmenu'); ?>
           
            <!-- left column -->
            <div class="col-md-10">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><i class="fa fa-gear"></i> <?php echo $this->lang->line('general_setting'); ?></h3>
                        <div class="box-tools pull-right">
                        </div><!-- /.box-tools -->
                    </div><!-- /.box-header -->
                    <div class="">
                        <form role="form" id="schsetting_form" action="<?php //echo site_url('schsettings/ajax_schedit_new'); ?>" class="" method="post" enctype="multipart/form-data">
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4"><?php echo $this->lang->line('school_name'); ?><small class="req"> *</small> </label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="name" name="sch_name" value="<?php echo $result->name; ?>">
                                                <span class="text-danger"><?php echo form_error('name'); ?></span> <input type="hidden" name="sch_id" value="<?php echo $result->id; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4 text-lg-end"><?php echo $this->lang->line('school_code'); ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="dise_code" name="sch_dise_code" value="<?php echo $result->dise_code; ?>">
                                                <span class="text-danger"><?php echo form_error('dise_code'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                               
                               
                                 
                                <!--./row-->
                                <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-4"><?php echo $this->lang->line('province'); ?><small class="req"> *</small></label>
                                                <div class="col-sm-8">
                                                    <select class="form-control" id="province" name="province">
                                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                        <?php foreach ($provinces as $province) { ?>
                                                            <option value="<?php echo $province['id']; ?>" <?php echo (isset($result->province) && $result->province == $province['id']) ? 'selected' : ''; ?>>
                                                                <?php echo $province['name']; ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                    <span class="text-danger"><?php echo form_error('province'); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-4 text-lg-end"><?php echo $this->lang->line('district'); ?><small class="req"> *</small></label>
                                                <div class="col-sm-8">
                                                    <select class="form-control" id="district" name="district">
                                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                        <?php foreach ($districts as $district) { ?>
                                                            <option value="<?php echo $district['id']; ?>" 
                                                                <?php echo (isset($result->district) && $result->district == $district['id']) ? 'selected' : ''; ?>
                                                                data-province="<?php echo $district['province_id']; ?>">
                                                                <?php echo $district['name']; ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                    <span class="text-danger"><?php echo form_error('district'); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-4"><?php echo $this->lang->line('localLevel'); ?><small class="req"> *</small></label>
                                                <div class="col-sm-8">
                                                    <select class="form-control" id="local_level" name="local_level">
                                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                        <?php foreach ($municipalities as $municipality) { ?>
                                                            <option value="<?php echo $municipality['id']; ?>" 
                                                                <?php echo (isset($result->local_level) && $result->local_level == $municipality['id']) ? 'selected' : ''; ?>
                                                                data-district="<?php echo $municipality['district_id']; ?>">
                                                                <?php echo $municipality['name']; ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                    <span class="text-danger"><?php echo form_error('local_level'); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4 text-lg-end">
                                                <?php echo $this->lang->line('ward'); ?><small class="req"> *</small>
                                            </label>
                                            <div class="col-sm-8">
                                                <select class="form-control" id="ward" name="ward">
                                                    <option value="">Select</option>
                                                    <?php
                                                    for ($i = 1; $i <= 32; $i++) {
                                                        $selected = set_value('ward', isset($result->ward) ? $result->ward : '') == $i ? 'selected' : '';
                                                        echo "<option value=\"$i\" $selected>$i</option>";
                                                    }
                                                    ?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('ward'); ?></span>
                                            </div>
                                    </div>

                                    </div>
                                </div>
                                
                                
                                
                                
                                
                                <!--./row-->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4"><?php echo $this->lang->line('address'); ?><small class="req"> *</small></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="address" name="sch_address" value="<?php echo $result->address; ?>"> <span class="text-danger"><?php echo form_error('address'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>






                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4"><?php echo $this->lang->line('phone'); ?><small class="req"> *</small></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="phone" name="sch_phone" value="<?php echo $result->phone; ?>"><span class="text-danger"><?php echo form_error('phone'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4 text-lg-end"><?php echo $this->lang->line('email'); ?><small class="req"> *</small></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control"  id="email" name="sch_email" value="<?php echo $result->email; ?>">
                                                <span class="text-danger"><?php echo form_error('email'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                               
                               
               
                               
                             


                               
                               


                                <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4">
                                            <?php echo $this->lang->line('affiliating_university'); ?>
                                            <small class="req"> *</small>
                                        </label>
                                        <div class="col-sm-8">
                                            <select class="form-control" name="affiliated_university" required>
                                                <option value="">Select University</option>
                                                <option value="TU" <?php if($result->affiliated_university == 'TU') echo 'selected'; ?>>Tribhuvan University (TU)</option>
                                                <option value="PU" <?php if($result->affiliated_university == 'PU') echo 'selected'; ?>>Purbanchal University (PU)</option>
                                                <option value="KU" <?php if($result->affiliated_university == 'KU') echo 'selected'; ?>>Kathmandu University (KU)</option>
                                                <option value="PokharaPU" <?php if($result->affiliated_university == 'PokharaPU') echo 'selected'; ?>>Pokhara University (PU)</option>
                                                <option value="LBU" <?php if($result->affiliated_university == 'LBU') echo 'selected'; ?>>Lumbini Buddhist University (LBU)</option>
                                                <option value="NSU" <?php if($result->affiliated_university == 'NSU') echo 'selected'; ?>>Nepal Sanskrit University (NSU)</option>
                                            </select>
                                            <span class="text-danger"><?php echo form_error('affiliated_university'); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 text-lg-end">
                                            <?php echo $this->lang->line('level_of_study'); ?>
                                            <small class="req"> *</small>
                                        </label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="level_of_study" name="level_of_study">
                                                <option value="">Select</option>
                                                <option value="Bachelors" <?php echo ($result->level_of_study == 'Bachelors') ? 'selected' : ''; ?>>Bachelors</option>
                                                <option value="Masters" <?php echo ($result->level_of_study == 'Masters') ? 'selected' : ''; ?>>Masters</option>
                                                <option value="Bachelors & Masters" <?php echo ($result->level_of_study == 'Bachelors & Masters') ? 'selected' : ''; ?>>Bachelors & Masters</option>
                                            </select>
                                            <span class="text-danger"><?php echo form_error('level_of_study'); ?></span>
                                        </div>
                                    </div>
                                </div>

                                </div>




                                <div class="row">
                                   
                                </div>


                                <!-- Geographic Location  -->


                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="settinghr"></div>
                                        <h4 class="session-head"><?php echo $this->lang->line('geographic_location'); ?></h4>
                                    </div><!--./col-md-12-->
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4"><?php echo $this->lang->line('latitude'); ?><small class="req"> *</small></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="latitude" name="latitude" value="<?php echo $result->latitude; ?>">
                                                <span class="text-danger"><?php echo form_error('latitude'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4 text-lg-end"><?php echo $this->lang->line('longitude'); ?><small class="req"> *</small></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="longitude" name="longitude" value="<?php echo $result->longitude; ?>">
                                                <span class="text-danger"><?php echo form_error('longitude'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>




                                 <!-- chief details -->
                               
                                <!--./row-->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="settinghr"></div>
                                        <h4 class="session-head"><?php echo $this->lang->line('campus_chief_details'); ?></h4>
                                    </div><!--./col-md-12-->
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4"><?php echo $this->lang->line('campus_chief_name'); ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="campus_chief_name" name="campus_chief_name" value="<?php echo $result->campus_chief_name; ?>">
                                                <span class="text-danger"><?php echo form_error('campus_chief_name'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4"><?php echo $this->lang->line('campus_chief_contact'); ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="campus_chief_contact" name="campus_chief_contact" value="<?php echo $result->campus_chief_contact; ?>">
                                                <span class="text-danger"><?php echo form_error('campus_chief_contact'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                               
                                <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4"><?php echo $this->lang->line('campus_chief_email'); ?></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="campus_chief_email" name="campus_chief_email" value="<?php echo $result->campus_chief_email; ?>">
                                            <span class="text-danger"><?php echo form_error('campus_chief_email'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 text-lg-end"><?php echo $this->lang->line('campus_it_name'); ?></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="campus_it_name" name="campus_it_name" value="<?php echo $result->campus_it_name; ?>">
                                            <span class="text-danger"><?php echo form_error('campus_it_name'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                </div>
                               
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4"><?php echo $this->lang->line('campus_it_contact'); ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="campus_it_contact" name="campus_it_contact" value="<?php echo $result->campus_it_contact; ?>">
                                                <span class="text-danger"><?php echo form_error('campus_it_contact'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4 text-lg-end"><?php echo $this->lang->line('campus_it_email'); ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="campus_it_email" name="campus_it_email" value="<?php echo $result->campus_it_email; ?>">
                                                <span class="text-danger"><?php echo form_error('campus_it_email'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                               
                           
               
                               
                               
                               
           
                               
                               
                                <!--./row-->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="settinghr"></div>
                                        <h4 class="session-head"><?php echo $this->lang->line('academic_session'); ?></h4>
                                    </div><!--./col-md-12-->
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4"><?php echo $this->lang->line('session'); ?><small class="req"> *</small> </label>
                                            <div class="col-sm-8">
                                                <select  id="session_id" name="sch_session_id" class="form-control" >
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php foreach ($sessionlist as $session) {
                                                        ?>
                                                        <option value="<?php echo $session['id'] ?>" <?php
                                                        if ($session['id'] == $result->session_id) {
                                                            echo "selected";
                                                        }
                                                        ?>><?php echo $session['session'] ?></option>
                                                            <?php } ?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('session_id'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4 text-lg-end"><?php echo $this->lang->line('session_start_month'); ?><small class="req"> *</small></label>
                                            <div class="col-sm-8">
                                                <select  id="start_month" name="sch_start_month" class="form-control">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php foreach ($monthList as $key => $month) {
                                                        ?>
                                                        <option value="<?php echo $key ?>" <?php
                                                        if ($key == $result->start_month) {
                                                            echo "selected";
                                                        }
                                                        ?> ><?php echo $month ?></option>
                                                            <?php } ?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('start_month'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div><!--./row-->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="settinghr"></div>
                                        <h4 class="session-head"><?php echo $this->lang->line('date_time'); ?></h4>
                                    </div><!--./col-md-12-->
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-sm-4"><?php echo $this->lang->line('date_format'); ?><small class="req"> *</small></label>
                                            <div class="col-sm-8">
                                                <select  id="date_format" name="sch_date_format" class="form-control">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php foreach ($dateFormatList as $key => $dateformat) {
                                                        ?>
                                                        <option value="<?php echo $key ?>" <?php
                                                        if ($key == $result->date_format) {
                                                            echo "selected";
                                                        }
                                                        ?>><?php echo $dateformat; ?></option>
                                                            <?php } ?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('date_format'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-sm-4 text-lg-end"><?php echo $this->lang->line('timezone'); ?><small class="req"> *</small></label>
                                            <div class="col-sm-8">
                                                <select  id="language_id" name="sch_timezone" class="form-control" >
                                                    <option value="">--<?php echo $this->lang->line('select') ?>--</option>
                                                    <?php foreach ($timezoneList as $key => $timezone) {
                                                        ?>
                                                        <option value="<?php echo $key ?>" <?php
                                                        if ($key == $result->timezone) {
                                                            echo "selected";
                                                        }
                                                        ?> ><?php echo $timezone ?></option>
                                                            <?php } ?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('timezone'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-sm-5 text-lg-end"><?php echo $this->lang->line('start_day_of_week') ?><small class="req"> *</small></label>
                                            <div class="col-sm-7">
                                                <select  id="start_week" name="sch_start_week" class="form-control" >
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php foreach ($daysList as $day_key => $day_value) {
                                                        ?>
                                                        <option value="<?php echo $day_key ?>" <?php
                                                        if ($day_key == $result->start_week) {
                                                            echo "selected";
                                                        }
                                                        ?> ><?php echo $day_value ?></option>
                                                            <?php } ?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('sch_start_week'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div><!--./row-->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="settinghr"></div>
                                        <h4 class="session-head"><?php echo $this->lang->line('currency') ?></h4>
                                    </div><!--./col-md-12-->                                    
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4"><?php echo $this->lang->line('currency_format'); ?><small class="req"> *</small></label>
                                            <div class="col-sm-8">
                                                    <select  id="currency_format" name="currency_format" class="form-control" >
                                                    <option value="">
                                                    <?php echo $this->lang->line('select'); ?></option>
                                                    <?php foreach ($currency_formats as $cur_format_key => $cur_format) {
                                                        ?>
                                                        <option value="<?php echo $cur_format_key ?>" <?php
                                                        if ($cur_format_key == $result->currency_format) {
                                                            echo "selected";
                                                        }
                                                        ?> ><?php echo $cur_format; ?></option>
                                                            <?php } ?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('currency_format'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 hidden">
                                        <div class="form-group row">
                                            <label class="col-sm-3"><?php echo $this->lang->line('currency_symbol_place'); ?><small class="req"> *</small></label>
                                            <div class="col-sm-9">
                                                <?php foreach ($currencyPlace as $currency_place_k => $currency_place_v) {
                                                    ?>
                                                    <label class="radio-inline hidden">
                                                        <input type="hidden" name="currency_place" value="<?php echo $currency_place_k; ?>" <?php
                                                        if ($result->currency_place == $currency_place_k) {
                                                            echo "checked";
                                                        }
                                                        ?>  ><?php echo $currency_place_v; ?>
                                                    </label>


                                                <?php } ?>
                                            </div>
                                            <span class="text-danger"><?php echo form_error('currency_symbol'); ?></span>
                                        </div>
                                    </div>
                                </div><!--./row-->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="settinghr"></div>
                                        <h4 class="session-head"><?php echo $this->lang->line('file_upload_path'); ?></h4>
                                    </div><!--./col-md-12-->
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4"><?php echo $this->lang->line('base_url'); ?><small class="req"> *</small></label>
                                      <div class="col-sm-8">
                                                <input type="text" class="form-control" id="base_url" name="base_url" value="<?php echo $result->base_url; ?>">
                                                <span class="text-danger"><?php echo form_error('base_url'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4 text-lg-end"><?php echo $this->lang->line('file_upload_path'); ?><small class="req"> *</small></label>
                                       <div class="col-sm-8">
                                                <input type="text" class="form-control"  id="folder_path" name="folder_path" value="<?php echo $result->folder_path; ?>">
                                                <span class="text-danger"><?php echo form_error('folder_path'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div><!--./row-->                              
                            </div><!-- /.box-body -->
                            <div class="box-footer">
                                <?php
                                if ($this->rbac->hasPrivilege('general_setting', 'can_edit')) {
                                    ?>
                                    <button type="button" class="btn btn-primary submit_schsetting pull-right edit_setting" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> <?php echo $this->lang->line('processing'); ?>"> <?php echo $this->lang->line('save'); ?></button>
                                    <?php
                                }
                                ?>
                            </div>
                        </form>
                    </div><!-- /.box-body -->
                </div>
            </div><!--/.col (left) -->
            <!-- right column -->
        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->


<!-- new END -->


</div><!-- /.content-wrapper -->


<script type="text/javascript">


    var base_url = '<?php echo base_url(); ?>';


    $(".edit_setting").on('click', function (e) {
        var $this = $(this);
        $this.button('loading');
        $.ajax({
            url: '<?php echo site_url("schsettings/generalsetting") ?>',
            type: 'POST',
            data: $('#schsetting_form').serialize(),
            dataType: 'json',


            success: function (data) {


                if (data.status == "fail") {
                    var message = "";
                    $.each(data.error, function (index, value) {


                        message += value;
                    });
                    errorMsg(message);
                } else {
                    successMsg(data.message);
                }


                $this.button('reset');
            }
        });
    });
</script>
<script>
$(document).ready(function() {
    $('#province').change(function() {
        var provinceId = $(this).val();
        $('#district').html('<option value=""><?php echo $this->lang->line('select'); ?></option>').prop('disabled', provinceId === '');
        $('#local_level').html('<option value=""><?php echo $this->lang->line('select'); ?></option>').prop('disabled', true);
        
        if(provinceId) {
            $.ajax({
                url: '<?php echo site_url("Schsettings/getDistrictsByProvince"); ?>',
                type: 'POST',
                data: {province_id: provinceId},
                dataType: 'json',
                success: function(data) {
                    $('#district').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                    $.each(data, function(key, value) {
                        $('#district').append('<option value="'+value.id+'">'+value.name+'</option>');
                    });
                    $('#district').prop('disabled', false);

                    var selectedDistrict = "<?php echo isset($result->district) ? $result->district : ''; ?>";
                    if(selectedDistrict) {
                        $('#district').val(selectedDistrict).trigger('change');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching districts: ", error);
                }
            });
        }
    });
    
    // District change event
    $('#district').change(function() {
        var districtId = $(this).val();
        $('#local_level').html('<option value=""><?php echo $this->lang->line('select'); ?></option>').prop('disabled', districtId === '');
        
        if(districtId) {
            $.ajax({
                url: '<?php echo site_url("Schsettings/getMunicipalitiesByDistrict"); ?>',
                type: 'POST',
                data: {district_id: districtId},
                dataType: 'json',
                success: function(data) {
                    $('#local_level').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                    $.each(data, function(key, value) {
                        $('#local_level').append('<option value="'+value.id+'">'+value.name+'</option>');
                    });
                    $('#local_level').prop('disabled', false);

                    var selectedMunicipality = "<?php echo isset($result->local_level) ? $result->local_level : ''; ?>";
                    if(selectedMunicipality) {
                        $('#local_level').val(selectedMunicipality);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching municipalities: ", error);
                }
            });
        }
    });

    var selectedProvince = "<?php echo isset($result->province) ? $result->province : ''; ?>";
    if(selectedProvince) {
        $('#province').val(selectedProvince).trigger('change');
    } else {
        $('#district').prop('disabled', true);
        $('#local_level').prop('disabled', true);
    }
});
</script>
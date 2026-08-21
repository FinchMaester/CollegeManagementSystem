<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-sitemap"></i> <?php echo $this->lang->line('human_resource'); ?>
            <?php if ($this->rbac->hasPrivilege('staff', 'can_add')) { ?>
                <small class="pull-right">
                    <a href="<?php echo base_url(); ?>admin/staff/create" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> <?php echo $this->lang->line('add_staff'); ?>
                    </a>
                </small>
            <?php } ?>
        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                        <div class="box-tools pull-right">
                            <small class="pull-right">
                                <?php if ($this->rbac->hasPrivilege('staff', 'can_add')) { ?>
                                    <a href="<?php echo base_url(); ?>admin/staff/create" class="btn btn-primary btn-sm">
                                        <i class="fa fa-plus"></i> <?php echo $this->lang->line('add_staff'); ?>
                                    </a>
                                <?php } ?>
                            </small>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if ($this->session->flashdata('msg')) {
                            echo $this->session->flashdata('msg');
                            $this->session->unset_userdata('msg');
                        } ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <form role="form" action="<?php echo site_url('admin/staff') ?>" method="post" class="">
                                        <?php echo $this->customlib->getCSRF(); ?>
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label><?php echo $this->lang->line("role"); ?></label><small class="req"> *</small>
                                                <select name="role" class="form-control">
                                                    <option value=""><?php echo $this->lang->line("select"); ?></option>
                                                    <?php foreach ($role as $key => $role_value) { ?>
                                                        <option <?php if ($role_id == $role_value["id"]) { echo "selected"; } ?> value="<?php echo $role_value['id'] ?>"><?php echo $role_value['type'] ?></option>
                                                    <?php } ?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('role'); ?></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-sm pull-right checkbox-toggle"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <form role="form" action="<?php echo site_url('admin/staff') ?>" method="post" class="">
                                        <?php echo $this->customlib->getCSRF(); ?>
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label><?php echo $this->lang->line('search_by_keyword'); ?></label>
                                                <input type="text" name="search_text" class="form-control" value="<?php echo set_value('search_text'); ?>"  placeholder="<?php echo $this->lang->line('search_by_staff'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <button type="submit" name="search" value="search_full" class="btn btn-primary pull-right btn-sm checkbox-toggle"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($resultlist)) { ?>
                        <div class="box-header ptbnull"></div>
                        <div class="nav-tabs-custom border0">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="false"><i class="fa fa-newspaper-o"></i> <?php echo $this->lang->line('card_view'); ?></a></li>
                                <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="true"><i class="fa fa-list"></i> <?php echo $this->lang->line('list_view'); ?></a></li>
                            </ul>
                            <div class="tab-content">
                                <div class="download_label"><?php echo $title; ?></div>
                                <div class="tab-pane table-responsive no-padding" id="tab_2">
                                    <table class="table table-striped table-bordered table-hover example" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th><?php echo $this->lang->line('staff_id'); ?></th>
                                                <th><?php echo $this->lang->line('name'); ?></th>
                                                <th><?php echo $this->lang->line('role'); ?></th>
                                                <th><?php echo $this->lang->line('department'); ?></th>
                                                <th><?php echo $this->lang->line('designation'); ?></th>
                                                <th><?php echo $this->lang->line('mobile_number'); ?></th>
                                                <?php if ($this->rbac->hasPrivilege('superadmin', 'can_edit')) { ?>
                                                    <th>UGC Sync</th>
                                                    <th>HEMIS Employee ID</th>
                                                <?php } ?>
                                                <?php if (!empty($fields)) {
                                                    foreach ($fields as $fields_key => $fields_value) { ?>
                                                        <th><?php echo $fields_value->name; ?></th>
                                                    <?php }
                                                } ?>
                                                <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $displayed_staff = array();
                                            if (!empty($resultlist)) {
                                                $sync_map = isset($ugc_employee_sync_map) && is_array($ugc_employee_sync_map) ? $ugc_employee_sync_map : array();
                                                foreach ($resultlist as $staff) {
                                                    // Skip if this staff has already been displayed
                                                    if (in_array($staff['id'], $displayed_staff)) {
                                                        continue;
                                                    }
                                                    $displayed_staff[] = $staff['id'];
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $staff['employee_id']; ?></td>
                                                        <td>
                                                            <a <?php if ($this->rbac->hasPrivilege('staff', 'can_edit')) { ?> href="<?php echo base_url(); ?>admin/staff/profile/<?php echo $staff['id']; ?>" <?php } ?>><?php echo $staff['name'] . " " . $staff['surname']; ?></a>
                                                        </td>
                                                        <td><?php echo $staff['user_type']; ?></td>
                                                        <td><?php echo $staff['department']; ?></td>
                                                        <td><?php echo $staff['designation']; ?></td>
                                                        <td><?php echo $staff['contact_no']; ?></td>
                                                        <?php $this->load->view('admin/staff/_staff_ugc_sync_list_cells', array('staff' => $staff, 'sync_map' => $sync_map)); ?>
                                                        <?php if (!empty($fields)) {
                                                            foreach ($fields as $fields_key => $fields_value) {
                                                                $display_field = $staff[$fields_value->name];
                                                                if ($fields_value->type == "link") {
                                                                    $display_field = "<a href='" . $staff[$fields_value->name] . "' target='_blank'>" . $staff[$fields_value->name] . "</a>";
                                                                } ?>
                                                                <td><?php echo $display_field; ?></td>
                                                            <?php }
                                                        } ?>
                                                        <td class="pull-right">
                                                            <?php
                                                            $userdata = $this->customlib->getUserData();
                                                            $a = 0;
                                                            $sessionData = $this->session->userdata('admin');
                                                            if (($this->rbac->hasPrivilege('staff', 'can_edit')) && ($this->rbac->hasPrivilege('can_see_other_users_profile', 'can_view'))) {
                                                                $a = 1;
                                                            }
                                                            if ($a == 1) { ?>
                                                                <a href="<?php echo base_url(); ?>admin/staff/edit/<?php echo $staff['id'] ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                                    <i class="fa fa-pencil"></i>
                                                                </a>
                                                            <?php } ?>
                                                            <?php if ($this->rbac->hasPrivilege('superadmin', 'can_edit')) { ?>
                                                                <button type="button" class="btn btn-default btn-xs ugc-sync-employee" data-staff-id="<?php echo (int) $staff['id']; ?>" data-toggle="tooltip" title="Sync staff to UGC">
                                                                    <i class="fa fa-cloud-upload"></i>
                                                                </button>
                                                            <?php } ?>
                                                        </td>
                                                    </tr>
                                                <?php }
                                            } else { ?>
                                                <tr>
                                                    <td colspan="8" class="text-center"><?php echo $this->lang->line('no_record_found'); ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tab-pane active" id="tab_1">
                                    <div class="mediarow">
                                        <div class="row">
                                            <?php 
                                            $displayed_staff = array();
                                            if (!empty($resultlist)) {
                                                $sync_map = isset($ugc_employee_sync_map) && is_array($ugc_employee_sync_map) ? $ugc_employee_sync_map : array();
                                                foreach ($resultlist as $staff) {
                                                    // Skip if this staff has already been displayed
                                                    if (in_array($staff['id'], $displayed_staff)) {
                                                        continue;
                                                    }
                                                    $displayed_staff[] = $staff['id'];
                                                    ?>
                                                    <div class="col-lg-3 col-md-4 col-sm-6 img_div_modal">
                                                        <div class="staffinfo-box">
                                                            <div class="staffleft-box">
                                                                <?php
                                                                if (!empty($staff["image"])) {
                                                                    $image = $staff["image"];
                                                                } else {
                                                                    $image = ($staff['gender'] == 'Male') ? "default_male.jpg" : "default_female.jpg";
                                                                }
                                                                ?>
                                                                <img src="<?php echo base_url('uploads/staff_images/' . $image); ?>" class="staff-image" alt="<?php echo $staff['name']; ?>"/>
                                                            </div>
                                                            <div class="staffleft-content">
                                                                <h5><span data-toggle="tooltip" title="<?php echo $this->lang->line('name'); ?>" ><?php echo $staff["name"] . " " . $staff["surname"]; ?></span></h5>
                                                                <?php $this->load->view('admin/staff/_staff_ugc_sync_card_badge', array('staff' => $staff, 'sync_map' => $sync_map)); ?>
                                                                <p><font data-toggle="tooltip" title="Employee Id"><?php echo $staff["employee_id"] ?></font></p>
                                                                <p><font data-toggle="tooltip" title="Contact Number"><?php echo $staff["contact_no"] ?></font></p>
                                                                <p><font data-toggle="tooltip" title="Location"><?php echo $staff["location"]; ?></font></p>
                                                                <p class="staffsub"><span data-toggle="tooltip" title="<?php echo $this->lang->line('role'); ?>"><?php echo $staff["user_type"] ?></span></p>
                                                            </div>
                                                            <div class="overlay3">
                                                                <div class="stafficons">
                                                                    <a title="<?php echo $this->lang->line('edit'); ?>" href="<?php echo base_url() . "admin/staff/edit/" . $staff["id"] ?>"><i class="fa fa-pencil"></i></a>
                                                                    <a title="<?php echo $this->lang->line('view'); ?>" href="<?php echo base_url() . "admin/staff/profile/" . $staff["id"] ?>"><i class="fa fa-eye"></i></a>
                                                                    <?php if ($this->rbac->hasPrivilege('superadmin', 'can_edit')) { ?>
                                                                        <a href="javascript:void(0);" class="ugc-sync-employee" data-staff-id="<?php echo (int) $staff['id']; ?>" title="Sync staff to UGC"><i class="fa fa-cloud-upload"></i></a>
                                                                    <?php } ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php }
                                            } else { ?>
                                                <div class="col-md-12">
                                                    <div class="alert alert-info"><?php echo $this->lang->line('no_record_found'); ?></div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    function getSectionByClass(class_id, section_id) {
        if (class_id != "" && section_id != "") {
            $('#section_id').html("");
            var base_url = '<?php echo base_url() ?>';
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.ajax({
                type: "GET",
                url: base_url + "sections/getByClass",
                data: {'class_id': class_id},
                dataType: "json",
                success: function (data) {
                    $.each(data, function (i, obj) {
                        var sel = "";
                        if (section_id == obj.section_id) {
                            sel = "selected";
                        }
                        div_data += "<option value=" + obj.section_id + " " + sel + ">" + obj.section + "</option>";
                    });
                    $('#section_id').append(div_data);
                }
            });
        }
    }

    $(document).ready(function () {
        var class_id = $('#class_id').val();
        var section_id = '<?php echo set_value('section_id') ?>';
        getSectionByClass(class_id, section_id);
        
        $(document).on('change', '#class_id', function (e) {
            $('#section_id').html("");
            var class_id = $(this).val();
            var base_url = '<?php echo base_url() ?>';
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.ajax({
                type: "GET",
                url: base_url + "sections/getByClass",
                data: {'class_id': class_id},
                dataType: "json",
                success: function (data) {
                    $.each(data, function (i, obj) {
                        div_data += "<option value=" + obj.section_id + ">" + obj.section + "</option>";
                    });
                    $('#section_id').append(div_data);
                }
            });
        });
    });

    // Manual UGC sync button (Super Admin tooling)
    function ugcBadgeApply(id, resp) {
        var el = document.getElementById('ugc-emp-badge-' + id);
        if (!el) return;
        var status = 'pending';
        if (resp && (resp.synced || resp.hemis_employee_id)) status = 'synced';
        else if (resp && resp.sync_pending === 'UGC_DEV_BLOCKER') status = 'blocked';
        else if (resp && resp.error) status = 'error';

        el.classList.remove('label-default', 'label-success', 'label-warning', 'label-danger');
        if (status === 'synced') { el.classList.add('label-success'); el.textContent = 'Synced'; }
        else if (status === 'blocked') { el.classList.add('label-warning'); el.textContent = 'Blocked (UGC)'; }
        else if (status === 'error') { el.classList.add('label-danger'); el.textContent = 'Error'; }
        else { el.classList.add('label-default'); el.textContent = 'Pending'; }

        if (resp && resp.error) {
            el.setAttribute('title', resp.error);
        } else {
            el.removeAttribute('title');
        }

        var hid = resp && resp.hemis_employee_id ? parseInt(resp.hemis_employee_id, 10) : 0;
        var idCell = document.getElementById('hemis-emp-id-' + id);
        if (idCell && hid > 0) {
            idCell.textContent = String(hid);
        }
        var idWrap = document.getElementById('hemis-emp-id-wrap-' + id);
        if (idWrap && hid > 0) {
            idWrap.style.display = '';
            var inner = document.getElementById('hemis-emp-id-' + id);
            if (inner) inner.textContent = String(hid);
        }
    }

    $(document).on('click', '.ugc-sync-employee', function (e) {
        if (e && e.preventDefault) e.preventDefault();
        var id = $(this).data('staff-id');
        var base = (window.baseurl || '<?php echo base_url(); ?>');
        var data = {};
        data['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
        $.ajax({
            type: 'POST',
            url: base + 'admin/ugc_sync/employee/' + id,
            data: data,
            dataType: 'json'
        }).done(function (resp) {
            ugcBadgeApply(id, resp || {});
            if (resp && (resp.synced || resp.hemis_employee_id)) {
                alert('UGC staff sync: OK' + (resp.hemis_employee_id ? ' (HEMIS ID ' + resp.hemis_employee_id + ')' : ''));
            } else if (resp && resp.sync_pending === 'UGC_DEV_BLOCKER') {
                alert('UGC staff sync: blocked (DEV)');
            } else {
                alert('UGC staff sync: error');
            }
        }).fail(function (xhr) {
            var msg = 'UGC staff sync failed.';
            if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            alert(msg);
        });
    });
</script>
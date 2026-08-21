<style type="text/css"> 
    .checkbox-inline+.checkbox-inline, .radio-inline+.radio-inline {
    margin-left: 8px;}
    #myFeesModal .modal-dialog {
    width: 600px;
    max-width: 90vw;
}

#myFeesModal .modal-body {
    max-height: 70vh;
    overflow-y: auto;
}

#myFeesModal .form-group {
    margin-bottom: 15px;
}

#myFeesModal .modal-footer {
    padding: 15px;
    border-top: 1px solid #e5e5e5;
}

/* Fix modal backdrop and layout issues - PROPER FIX */
.modal-backdrop {
    z-index: 1040 !important;
    background-color: rgba(0, 0, 0, 0.5) !important;
    pointer-events: auto !important;
}

#myFeesModal,
#listCollectionModal {
    z-index: 1050 !important;
}

#myFeesModal.modal,
#listCollectionModal.modal {
    overflow: visible !important;
    display: none !important;
    pointer-events: none !important;
}

#myFeesModal.modal.in,
#myFeesModal.modal.show,
#listCollectionModal.modal.in,
#listCollectionModal.modal.show {
    display: block !important;
    z-index: 1050 !important;
    pointer-events: none !important;
}

#myFeesModal .modal-dialog,
#listCollectionModal .modal-dialog {
    margin: 30px auto !important;
    position: relative !important;
    z-index: 1051 !important;
    pointer-events: auto !important;
}

#myFeesModal .modal-content,
#listCollectionModal .modal-content {
    position: relative !important;
    z-index: 1052 !important;
    pointer-events: auto !important;
}

#myFeesModal .modal-header,
#listCollectionModal .modal-header {
    pointer-events: auto !important;
}

#myFeesModal .modal-body,
#listCollectionModal .modal-body {
    pointer-events: auto !important;
    max-height: 70vh;
    overflow-y: auto;
    overflow-x: hidden;
}

#myFeesModal .modal-footer,
#listCollectionModal .modal-footer {
    pointer-events: auto !important;
    z-index: 1053 !important;
}

#myFeesModal .modal-footer button,
#listCollectionModal .modal-footer button,
#myFeesModal .modal-header button,
#listCollectionModal .modal-header button,
#myFeesModal input,
#listCollectionModal input,
#myFeesModal select,
#listCollectionModal select,
#myFeesModal textarea,
#listCollectionModal textarea {
    pointer-events: auto !important;
    cursor: pointer !important;
}

/* Fix Nepali calendar positioning in modals */
#myFeesModal .nepali-datepicker-container,
#listCollectionModal .nepali-datepicker-container {
    position: absolute !important;
    z-index: 200000 !important;
}

/* Nepali calendar positioning - FIXED */
#ndp-nepali-box {
    z-index: 999999 !important;
    position: fixed !important;
    display: block !important;
}

/* Ensure calendar appears inside modal, not outside */
#myFeesModal #ndp-nepali-box,
#listCollectionModal #ndp-nepali-box {
    position: fixed !important;
    z-index: 999999 !important;
}

/* Remove any calendar that appears at top-left (outside modal) */
body > #ndp-nepali-box {
    position: fixed !important;
}

/* Hide calendar if it's positioned at top-left (0,0) - means it's misplaced */
#ndp-nepali-box[style*="top: 0px"],
#ndp-nepali-box[style*="top:0px"],
#ndp-nepali-box[style*="left: 0px"],
#ndp-nepali-box[style*="left:0px"] {
    display: none !important;
}

/* Position Nepali calendar relative to input in modals */
#myFeesModal input.nepali-datepicker + .nepali-datepicker-container,
#listCollectionModal input.nepali-datepicker + .nepali-datepicker-container,
#myFeesModal .nepali-datepicker-container,
#listCollectionModal .nepali-datepicker-container {
    position: absolute !important;
    top: 100% !important;
    left: 0 !important;
    margin-top: 2px !important;
    z-index: 200000 !important;
}

/* Remove any stray lines after modal */
body.modal-open {
    overflow: hidden !important;
}

body.modal-open .modal-backdrop {
    display: block !important;
}

/* Prevent English calendar from appearing */
#listCollectionModal .date_fee,
#listCollectionModal input[name*="date"],
#listCollectionModal .nepali-datepicker {
    pointer-events: auto !important;
}

/* Block English calendar widgets */
.bootstrap-datetimepicker-widget,
.ui-datepicker,
body > .datepicker-dropdown,
body > .datepicker-inline {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    pointer-events: none !important;
}

/* Prevent Nepali calendar from auto-closing */
#ndp-nepali-box {
    z-index: 999999 !important;
    position: fixed !important;
    pointer-events: auto !important;
}

#listCollectionModal #ndp-nepali-box {
    z-index: 999999 !important;
    position: fixed !important;
    pointer-events: auto !important;
}
</style>
<style>
#customModal {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    background: rgba(0,0,0,0.7) !important;
    z-index: 99999 !important;
    display: none !important;
}

#customModal .modal-content {
    position: absolute !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    background: white !important;
    border-radius: 5px !important;
    box-shadow: 0 0 20px rgba(0,0,0,0.8) !important;
    max-width: 800px !important;
    max-height: 90vh !important;
    overflow: auto !important;
    padding: 0 !important;
}

#customModal .modal-header {
    padding: 15px 20px !important;
    border-bottom: 1px solid #ccc !important;
    background: #f8f9fa !important;
    font-weight: bold !important;
}

#customModal .modal-body {
    padding: 20px !important;
    max-height: 60vh !important;
    overflow: auto !important;
}

#customModal .modal-footer {
    padding: 15px 20px !important;
    border-top: 1px solid #ccc !important;
    background: #f8f9fa !important;
    text-align: right !important;
}

#customModal button {
    margin-left: 10px !important;
    padding: 8px 16px !important;
    border: none !important;
    border-radius: 3px !important;
    cursor: pointer !important;
}

.btn-primary-custom { background: #007bff !important; color: white !important; }
.btn-secondary-custom { background: #6c757d !important; color: white !important; }
.btn-danger-custom { background: #dc3545 !important; color: white !important; }
</style>
<style>
  #listCollectionModal { 
    z-index: 1050 !important; 
    pointer-events: none !important;
  }
  #listCollectionModal.modal.show {
    z-index: 1050 !important;
    pointer-events: none !important;
  }
  #listCollectionModal .modal-dialog {
    z-index: 1051 !important;
    position: relative;
    pointer-events: auto !important;
  }
  #listCollectionModal .modal-content {
    z-index: 1052 !important;
    position: relative;
    pointer-events: auto !important;
  }
  #listCollectionModal .modal-header,
  #listCollectionModal .modal-body,
  #listCollectionModal .modal-footer {
    pointer-events: auto !important;
  }
  #listCollectionModal input,
  #listCollectionModal select,
  #listCollectionModal textarea,
  #listCollectionModal button {
    pointer-events: auto !important;
    cursor: pointer !important;
  }
  .modal-backdrop { 
    z-index: 1040 !important; 
    pointer-events: auto !important;
  }
  /* Remove any duplicate backdrops that might be blocking */
  .modal-backdrop.show ~ .modal-backdrop.show {
    display: none !important;
  }
  /* Ensure modal is clickable */
  #listCollectionModal.modal.show .modal-content,
  #listCollectionModal.modal.show .modal-dialog {
    pointer-events: auto !important;
  }
  /* Remove shadow overlay if present */
  body.modal-open::before {
    display: none !important;
  }
</style>
<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
$language        = $this->customlib->getLanguage();
$language_name   = $language["short_code"];

/* ---- FIX: helper so strtotime never receives NULL/invalid ---- */
if (!function_exists('_safe_strtotime')) {
    function _safe_strtotime($d) {
        if ($d === null || $d === '' || $d === '0000-00-00' || $d === '0000-00-00 00:00:00') {
            return false;
        }
        $ts = strtotime($d);
        return $ts === false ? false : $ts;
    }
}
?>
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12">
            <section class="content-header">

            </section>
        </div>
        <div>
            <a id="sidebarCollapse" class="studentsideopen"><i class="fa fa-navicon"></i></a>
            <aside class="studentsidebar">
                <div class="stutop" id="">
                    <!-- Create the tabs -->
                    <div class="studentsidetopfixed">
                        <p class="classtap"><?php echo $student["class"]; ?> <a href="#" data-toggle="control-sidebar" class="studentsideclose"><i class="fa fa-times"></i></a></p>
                        <ul class="nav nav-justified studenttaps">
                            <?php foreach ($class_section as $skey => $svalue) { ?>
                                <li <?php if ($student["section_id"] == $svalue["section_id"]) { echo "class='active'"; } ?> >
                                    <a href="#section<?php echo $svalue["section_id"] ?>" data-toggle="tab"><?php print_r($svalue["section"]);?></a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <?php foreach ($class_section as $skey => $snvalue) { ?>
                            <div class="tab-pane <?php if ($student["section_id"] == $snvalue["section_id"]) { echo "active"; } ?>" id="section<?php echo $snvalue["section_id"]; ?>">
                                <?php foreach ($studentlistbysection as $stkey => $stvalue) {
                                    if ($stvalue['section_id'] == $snvalue["section_id"]) { ?>
                                        <div class="studentname">
                                            <a class="" href="<?php echo base_url() . "studentfee/addfee/" . $stvalue["student_session_id"] ?>">
                                                <div class="icon"><img src="<?php echo base_url() . $stvalue["image"] . img_time(); ?>" alt="User Image"></div>
                                                <div class="student-tittle"><?php echo $stvalue["firstname"] . " " . $stvalue["lastname"]; ?></div>
                                            </a>
                                        </div>
                                <?php } } ?>
                            </div>
                        <?php } ?>
                        <div class="tab-pane" id="sectionB">
                            <h3 class="control-sidebar-heading">Recent Activity 2</h3>
                        </div>
                        <div class="tab-pane" id="sectionC">
                            <h3 class="control-sidebar-heading">Recent Activity 3</h3>
                        </div>
                        <div class="tab-pane" id="sectionD">
                            <h3 class="control-sidebar-heading">Recent Activity 3</h3>
                        </div>
                        <!-- /.tab-pane -->
                    </div>
                </div>
            </aside>
        </div>
    </div>
    <!-- /.control-sidebar -->
    <section class="content">
        <div class="row">
            <!-- left column -->
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-md-4">
                                <h3 class="box-title"><?php echo $this->lang->line('student_fees'); ?></h3>
                            </div>
                            <div class="col-md-8">
                                <div class="btn-group pull-right">
                                    <a href="<?php echo base_url() ?>studentfee" type="button" class="btn btn-primary btn-xs">
                                        <i class="fa fa-arrow-left"></i> <?php echo $this->lang->line('back'); ?></a>
                                </div>
                            </div>
                        </div>
                    </div><!--./box-header-->
                    <div class="box-body" style="padding-top:0;">
                        <?php
                        $student_fee_disabled = !empty($student_fee_disabled);
                        if ($student_fee_disabled) {
                            ?>
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i>
                            This student is <strong>disabled</strong>.
                            New fee group assignment is blocked.
                            You can still collect payment on existing assigned fees and print receipts below.
                            <?php if (!empty($reason_data['reason'])) { ?>
                                <br><strong><?php echo $this->lang->line('disable_reason'); ?>:</strong> <?php echo html_escape($reason_data['reason']); ?>
                            <?php } ?>
                            <?php if (!empty($student['dis_note'])) { ?>
                                <br><strong><?php echo $this->lang->line('disable_note'); ?>:</strong> <?php echo html_escape($student['dis_note']); ?>
                            <?php } ?>
                        </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="sfborder-top-border">
                                    <div class="col-md-2">
                                        <?php
                                        // FIX #1: Always use base_url() for student image, never media_storage
                                        if (!empty($student["image"])) {
                                            $student_img = base_url($student["image"]);
                                        } else {
                                            if ($student['gender'] == 'Female') {
                                                $student_img = base_url("uploads/student_images/default_female.jpg");
                                            } elseif ($student['gender'] == 'Male') {
                                                $student_img = base_url("uploads/student_images/default_male.jpg");
                                            } else {
                                                $student_img = base_url("uploads/student_images/default_male.jpg");
                                            }
                                        }
                                        ?>
                                        <img width="115" height="115" class="mt5 mb10 sfborder-img-shadow img-responsive img-rounded" src="<?php echo $student_img; ?>" alt="No Image">
                                    </div>
                                    <div class="col-md-10">
                                        <div class="row">
                                            <table class="table table-striped mb0 font13">
                                                <tbody>
                                                    <tr>
                                                        <th class="bozero"><?php echo $this->lang->line('name'); ?></th>
                                                        <td class="bozero"><?php echo $this->customlib->getFullName($student['firstname'], $student['middlename'], $student['lastname'], $sch_setting->middlename, $sch_setting->lastname); ?></td>
                                                        <th class="bozero">Academic Level (Faculty-Program)</th>
                                                        <td class="bozero">
                                                            <?php 
                                                                echo $student['class'] . " (" . $student['section'] . ")";
                                                                if (!empty($student['program_title'])) {
                                                                    echo " - " . $student['program_title'];
                                                                }
                                                            ?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th><?php echo $this->lang->line('father_name'); ?></th>
                                                        <td><?php echo $student['father_name']; ?></td>
                                                        <th><?php echo $this->lang->line('admission_no'); ?></th>
                                                        <td><?php echo $student['admission_no']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th><?php echo $this->lang->line('mobile_number'); ?></th>
                                                        <td><?php echo $student['mobileno']; ?></td>
                                                        <th><?php echo $this->lang->line('roll_number'); ?></th>
                                                        <td><?php echo $student['roll_no']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th><?php echo $this->lang->line('category'); ?></th>
                                                        <td>
                                                            <?php
                                                            foreach ($categorylist as $value) {
                                                                if ($student['category_id'] == $value['id']) {
                                                                    echo $value['category'];
                                                                }
                                                            }
                                                            ?>
                                                        </td>
                                                        <?php if ($sch_setting->rte) { ?>
                                                            <th><?php echo $this->lang->line('rte'); ?></th>
                                                            <td><b class="text-danger"> <?php echo $student['rte']; ?> </b></td>
                                                        <?php } ?>
                                                    </tr>

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div style="background: #dadada; height: 1px; width: 100%; clear: both; margin-bottom: 10px;"></div>
                            </div>
                        </div>
                        <div class="row no-print mb10">
                            <div class="col-md-12 mDMb10">
                                <div class="float-rtl-right float-left">
                                    <button type="button" class="btn btn-sm btn-info printSelected" id="load" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('please_wait') ?>"><i class="fa fa-print"></i> <?php echo $this->lang->line('print_selected'); ?></button>
                                    <?php if ($this->rbac->hasPrivilege('collect_fees', 'can_add')) { ?>
                                        <button type="button" class="btn btn-sm btn-warning collectSelected" id="load" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('please_wait') ?>"><i class="fa fa-money"></i> <?php echo $this->lang->line('collect_selected'); ?></button>
                                    <?php } ?>
                                    <?php if ($student_processing_fee) { ?>
                                        <a href="javascript:void(0)" class="btn btn-sm btn-info getProcessingfees" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('please_wait') ?>"><i class="fa fa-money"></i> <?php echo $this->lang->line('processing_fees') ?></a>
                                    <?php } ?>
                                </div>
                                <span class="pull-right pt5" id="current-date-display"><?php echo $this->lang->line('date'); ?>: <?php echo date($this->customlib->getSchoolDateFormat()); ?></span>
                            </div>
                        </div>
                        <?php
                        $student_admission_no = '';
                        if ($student['admission_no'] != '') {
                            $student_admission_no = ' (' . $student['admission_no'] . ')';
                        }
                        ?>
                        <div class="table-responsive">
                            <div class="download_label "><?php echo $this->lang->line('student_fees') . ": " . $student['firstname'] . " " . $student['lastname'] . $student_admission_no; ?> </div>
                            <table class="table table-striped table-bordered table-hover example table-fixed-header">
                                <thead class="header">
                                    <tr>
                                        <th style="width: 10px"><input type="checkbox" id="select_all"/></th>
                                        <th align="left"><?php echo $this->lang->line('fees_group'); ?></th>
                                        <th align="left"><?php echo $this->lang->line('fees_code'); ?></th>
                                        <th align="left" class="text text-left"><?php echo $this->lang->line('due_date'); ?></th>
                                        <th align="left" class="text text-left"><?php echo $this->lang->line('status'); ?></th>
                                        <th class="text text-right"><?php echo $this->lang->line('amount') ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-left"><?php echo $this->lang->line('payment_id'); ?></th>
                                        <th class="text text-left"><?php echo $this->lang->line('mode'); ?></th>
                                        <th class="text text-left"><?php echo $this->lang->line('date'); ?></th>
                                        <th class="text text-right"><?php echo $this->lang->line('discount'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-right"><?php echo $this->lang->line('fine'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-right"><?php echo $this->lang->line('paid'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-right"><?php echo $this->lang->line('balance'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total_amount           = 0;
                                    $total_deposite_amount  = 0;
                                    $total_fine_amount      = 0;
                                    $total_fees_fine_amount = 0;

                                    $total_discount_amount = 0;
                                    $total_balance_amount  = 0;
                                    $alot_fee_discount     = 0;

                                    foreach ($student_due_fee as $key => $fee) {
                                        if (!empty($fee->fees)) {
                                            foreach ($fee->fees as $fee_key => $fee_value) {
                                                $fee_paid         = 0;
                                                $fee_discount     = 0;
                                                $fee_fine         = 0;
                                                $fees_fine_amount = 0;
                                                $feetype_balance  = 0;
                                                if (!empty($fee_value->amount_detail) && $fee_value->amount_detail !== '0') {
                                                    $fee_deposits = json_decode($fee_value->amount_detail);
                                                    if (is_object($fee_deposits) || is_array($fee_deposits)) {
                                                        foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                                            $row_amt = is_object($fee_deposits_value) ? (float) ($fee_deposits_value->amount ?? 0) : (float) ($fee_deposits_value['amount'] ?? 0);
                                                            $row_dis = is_object($fee_deposits_value) ? (float) ($fee_deposits_value->amount_discount ?? 0) : (float) ($fee_deposits_value['amount_discount'] ?? 0);
                                                            $row_fin = is_object($fee_deposits_value) ? (float) ($fee_deposits_value->amount_fine ?? 0) : (float) ($fee_deposits_value['amount_fine'] ?? 0);
                                                            $fee_paid     = $fee_paid + $row_amt;
                                                            $fee_discount = $fee_discount + $row_dis;
                                                            $fee_fine     = $fee_fine + $row_fin;
                                                        }
                                                    }
                                                }
                                                if (($fee_value->due_date != "0000-00-00" && $fee_value->due_date != null)) {
                                                    $__due_ts = _safe_strtotime($fee_value->due_date); /* FIX */
                                                    if ($__due_ts !== false && ($__due_ts < strtotime(date('Y-m-d')))) {
                                                        $fees_fine_amount       = $fee_value->fine_amount;
                                                        $total_fees_fine_amount = $total_fees_fine_amount + $fee_value->fine_amount;
                                                    }
                                                }

                                                $total_amount += $fee_value->amount;
                                                $total_discount_amount += $fee_discount;
                                                $total_deposite_amount += $fee_paid;
                                                $total_fine_amount += $fee_fine;
                                                $feetype_balance = $fee_value->amount - ($fee_paid + $fee_discount);
                                                $total_balance_amount += $feetype_balance;
                                                ?>
                                                <?php
                                                $__row_due = _safe_strtotime($fee_value->due_date);
                                                if ($feetype_balance > 0 && $__row_due !== false && $__row_due < strtotime(date('Y-m-d'))) { ?>
                                                    <tr class="danger font12">
                                                <?php } else { ?>
                                                    <tr class="dark-gray">
                                                <?php } ?>
                                                    <td>
                                                        <input class="checkbox" type="checkbox" name="fee_checkbox" data-fee_master_id="<?php echo $fee_value->id ?>" data-fee_session_group_id="<?php echo $fee_value->fee_session_group_id ?>" data-fee_groups_feetype_id="<?php echo $fee_value->fee_groups_feetype_id ?>" data-fee_category="fees"  data-trans_fee_id="0">
                                                    </td>
                                                    <td align="left" class="text-rtl-right">
                                                        <?php
                                                        if ($fee_value->is_system) {
                                                            echo $this->lang->line($fee_value->name) . " (" . $this->lang->line($fee_value->type) . ")";
                                                        } else {
                                                            echo $fee_value->name . " (" . $fee_value->type . ")";
                                                        }
                                                        ?>
                                                    </td>
                                                    <td align="left" class="text-rtl-right">
                                                        <?php
                                                        if ($fee_value->is_system) {
                                                            echo $this->lang->line($fee_value->code);
                                                        } else {
                                                            echo $fee_value->code;
                                                        }
                                                        ?>
                                                    </td>
                                                    <td align="left" class="text text-left">
                                                        <?php
                                                        if ($fee_value->due_date == "0000-00-00") {
                                                            // blank
                                                        } else {
                                                            if ($fee_value->due_date) {
                                                                echo $this->customlib->dateformat($fee_value->due_date);
                                                            }
                                                        }
                                                        ?>
                                                    </td>
                                                    <td align="left" class="text text-left width85">
                                                        <?php
                                                        if ($feetype_balance == 0) {
                                                            ?><span class="label label-success"><?php echo $this->lang->line('paid'); ?></span><?php
                                                        } else if (!empty($fee_value->amount_detail)) {
                                                            ?><span class="label label-warning"><?php echo $this->lang->line('partial'); ?></span><?php
                                                        } else {
                                                            ?><span class="label label-danger"><?php echo $this->lang->line('unpaid'); ?></span><?php
                                                        }
                                                        ?>
                                                    </td>
                                                    <td class="text text-right">
                                                        <?php echo amountFormat($fee_value->amount);
                                                        if (($fee_value->due_date != "0000-00-00" && $fee_value->due_date != null)) {
                                                            $__fine_due = _safe_strtotime($fee_value->due_date);
                                                            if ($__fine_due !== false && $__fine_due < strtotime(date('Y-m-d'))) { ?>
                                                                <span data-toggle="popover" class="text text-danger detail_popover"><?php echo " + " . amountFormat($fee_value->fine_amount); ?></span>
                                                                <div class="fee_detail_popover" style="display: none">
                                                                    <?php if ($fee_value->fine_amount != "") { ?>
                                                                        <p class="text text-danger"><?php echo $this->lang->line('fine'); ?></p>
                                                                    <?php } ?>
                                                                </div>
                                                            <?php }
                                                        } ?>
                                                    </td>
                                                    <td class="text text-left"></td>
                                                    <td class="text text-left"></td>
                                                    <td class="text text-left"></td>
                                                    <td class="text text-right"><?php echo amountFormat($fee_discount); ?></td>
                                                    <td class="text text-right"><?php echo amountFormat($fee_fine); ?></td>
                                                    <td class="text text-right"><?php echo amountFormat($fee_paid); ?></td>
                                                    <td class="text text-right">
                                                        <?php
                                                        $display_none = "ss-none";
                                                        if ($feetype_balance > 0) {
                                                            $display_none = "";
                                                            echo amountFormat($feetype_balance);
                                                        }
                                                        ?> 
                                                    </td>
                                                    <td width="100">
                                                        <div class="btn-group">
                                                            <div class="pull-right">
                                                                <?php if ($this->rbac->hasPrivilege('collect_fees', 'can_add')) { ?>
                                                                    <button type="button"
                                                                        data-student_session_id="<?php echo $fee->student_session_id; ?>" 
                                                                        data-student_fees_master_id="<?php echo $fee->id; ?>"
                                                                        data-fee_groups_feetype_id="<?php echo $fee_value->fee_groups_feetype_id; ?>"
                                                                        data-fee-balance="<?php echo amountFormat($feetype_balance); ?>"
                                                                        data-group="<?php echo ($fee_value->is_system) ? $this->lang->line($fee_value->name). " (" . $this->lang->line($fee_value->type) . ")" : $fee_value->name. " (" . $fee_value->type . ")"; ?>"
                                                                        data-type="<?php echo ($fee_value->is_system) ? $this->lang->line($fee_value->type) : $fee_value->code; ?>"
                                                                        class="btn btn-xs btn-default myCollectFeeBtn <?php echo $display_none; ?>"
                                                                        title="<?php echo $this->lang->line('add_fees'); ?>"
                                                                        data-fee-category="fees" data-trans_fee_id="0">
                                                                        <i class="fa fa-plus"></i>
                                                                    </button>
                                                                <?php } ?>

                                                                <button class="btn btn-xs btn-default printInv"
                                                                    data-student_session_id="<?php echo $fee->student_session_id; ?>"
                                                                    data-fee_master_id="<?php echo $fee_value->id ?>"
                                                                    data-fee_session_group_id="<?php echo $fee_value->fee_session_group_id ?>"
                                                                    data-fee_groups_feetype_id="<?php echo $fee_value->fee_groups_feetype_id ?>"
                                                                    data-fee-category="fees" data-trans_fee_id="0"
                                                                    title="<?php echo $this->lang->line('print'); ?>"
                                                                    data-loading-text="<i class='fa fa-spinner fa-spin '></i>">
                                                                    <i class="fa fa-print"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php
                                                if (!empty($fee_value->amount_detail) && $fee_value->amount_detail !== '0') {
                                                    $fee_deposits = json_decode($fee_value->amount_detail);
                                                    foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                                        if (is_array($fee_deposits_value)) {
                                                            $fee_deposits_value = (object) $fee_deposits_value;
                                                        }
                                                        ?>
                                                        <tr class="white-td">
                                                            <td align="left"></td>
                                                            <td align="left"></td>
                                                            <td align="left"></td>
                                                            <td align="left"></td>
                                                            <td align="left"></td>
                                                            <td class="text-right"><img src="<?php echo base_url('backend/images/table-arrow.png'); ?>" alt="" /></td>
                                                            <td class="text text-left">
                                                                <a href="#" data-toggle="popover" class="detail_popover"><?php echo $fee_value->student_fees_deposite_id . "/" . $fee_deposits_value->inv_no; ?></a>
                                                                <div class="fee_detail_popover" style="display: none">
                                                                    <?php if ($fee_deposits_value->description == "") { ?>
                                                                        <p class="text text-danger"><?php echo $this->lang->line('no_description'); ?></p>
                                                                    <?php } else { ?>
                                                                        <p class="text text-info"><?php echo $fee_deposits_value->description; ?></p>
                                                                    <?php } ?>
                                                                </div>
                                                            </td>
                                                            <td class="text text-left"><?php echo $this->lang->line(strtolower($fee_deposits_value->payment_mode)); ?></td>
                                                            <td class="text text-left">
                                                                <?php if ($fee_deposits_value->date) { echo $this->customlib->dateformat($fee_deposits_value->date); } ?>
                                                            </td>
                                                            <td class="text text-right"><?php echo amountFormat($fee_deposits_value->amount_discount); ?></td>
                                                            <td class="text text-right"><?php echo amountFormat($fee_deposits_value->amount_fine); ?></td>
                                                            <td class="text text-right"><?php echo amountFormat($fee_deposits_value->amount); ?></td>
                                                            <td></td>
                                                            <td class="text text-right">
                                                                <div class="btn-group">
                                                                    <div class="pull-right">
                                                                        <?php if ($this->rbac->hasPrivilege('collect_fees', 'can_delete')) { ?>
                                                                            <button type="button" class="btn btn-default btn-xs js-open-invoice-revert-modal" data-invoiceno="<?php echo $fee_value->student_fees_deposite_id . "/" . $fee_deposits_value->inv_no; ?>" data-main_invoice="<?php echo $fee_value->student_fees_deposite_id ?>" data-sub_invoice="<?php echo $fee_deposits_value->inv_no ?>" title="<?php echo $this->lang->line('revert'); ?>">
                                                                                <i class="fa fa-undo"> </i>
                                                                            </button>
                                                                        <?php } ?>
                                                                        <button class="btn btn-xs btn-default printDoc" data-main_invoice="<?php echo $fee_value->student_fees_deposite_id ?>" data-sub_invoice="<?php echo $fee_deposits_value->inv_no ?>"  title="<?php echo $this->lang->line('print'); ?>"><i class="fa fa-print"></i> </button>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php }
                                                } ?>
                                                <?php
                                            }
                                        } else {
                                            // Display fee group even when it has no fee types configured
                                            ?>
                                            <tr class="dark-gray">
                                                <td>
                                                    <input class="checkbox" type="checkbox" name="fee_checkbox" disabled>
                                                </td>
                                                <td align="left" class="text-rtl-right">
                                                    <?php
                                                    if (isset($fee->is_system) && $fee->is_system) {
                                                        echo $this->lang->line($fee->name);
                                                    } else {
                                                        echo isset($fee->name) ? $fee->name : '';
                                                    }
                                                    ?>
                                                </td>
                                                <td align="left" class="text-rtl-right">-</td>
                                                <td align="left" class="text text-left">-</td>
                                                <td align="left" class="text text-left width85">
                                                    <span class="label label-info">No Fee Types</span>
                                                </td>
                                                <td class="text text-right">-</td>
                                                <td class="text text-left">-</td>
                                                <td class="text text-left">-</td>
                                                <td class="text text-left">-</td>
                                                <td class="text text-right">-</td>
                                                <td class="text text-right">-</td>
                                                <td class="text text-right">-</td>
                                                <td class="text text-right">-</td>
                                                <td width="100">
                                                    <div class="btn-group">
                                                        <div class="pull-right">
                                                            <span class="text-muted" style="font-size: 11px;">Configure Fee Types</span>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>

                                    <?php
                                    if (!empty($transport_fees)) {
                                        foreach ($transport_fees as $transport_fee_key => $transport_fee_value) {
                                            $fee_paid         = 0;
                                            $fee_discount     = 0;
                                            $fee_fine         = 0;
                                            $fees_fine_amount = 0;
                                            $feetype_balance  = 0;

                                            if (!empty($transport_fee_value->amount_detail) && $transport_fee_value->amount_detail !== '0') {
                                                $fee_deposits = json_decode($transport_fee_value->amount_detail);
                                                if (is_object($fee_deposits) || is_array($fee_deposits)) {
                                                    foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                                        $row_amt = is_object($fee_deposits_value) ? (float) ($fee_deposits_value->amount ?? 0) : (float) ($fee_deposits_value['amount'] ?? 0);
                                                        $row_dis = is_object($fee_deposits_value) ? (float) ($fee_deposits_value->amount_discount ?? 0) : (float) ($fee_deposits_value['amount_discount'] ?? 0);
                                                        $row_fin = is_object($fee_deposits_value) ? (float) ($fee_deposits_value->amount_fine ?? 0) : (float) ($fee_deposits_value['amount_fine'] ?? 0);
                                                        $fee_paid     = $fee_paid + $row_amt;
                                                        $fee_discount = $fee_discount + $row_dis;
                                                        $fee_fine     = $fee_fine + $row_fin;
                                                    }
                                                }
                                            }

                                            $feetype_balance = $transport_fee_value->fees - ($fee_paid + $fee_discount);

                                            if (($transport_fee_value->due_date != "0000-00-00" && $transport_fee_value->due_date != null)) {
                                                $__tr_due = _safe_strtotime($transport_fee_value->due_date);
                                                if ($__tr_due !== false && ($__tr_due < strtotime(date('Y-m-d')))) {
                                                    $fees_fine_amount       = is_null($transport_fee_value->fine_percentage) ? $transport_fee_value->fine_amount : percentageAmount($transport_fee_value->fees, $transport_fee_value->fine_percentage);
                                                    $total_fees_fine_amount = $total_fees_fine_amount + $fees_fine_amount;
                                                }
                                            }

                                            $total_amount += $transport_fee_value->fees;
                                            $total_discount_amount += $fee_discount;
                                            $total_deposite_amount += $fee_paid;
                                            $total_fine_amount += $fee_fine;
                                            $total_balance_amount += $feetype_balance;

                                            $__tr_row_due = _safe_strtotime($transport_fee_value->due_date);
                                            if ($__tr_row_due !== false && $__tr_row_due < strtotime(date('Y-m-d'))) { ?>
                                                <tr class="danger font12">
                                            <?php } else { ?>
                                                <tr class="dark-gray">
                                            <?php } ?>
                                                    <td>
                                                        <input class="checkbox" type="checkbox" name="fee_checkbox" data-fee_master_id="0" data-fee_session_group_id="0" data-fee_groups_feetype_id="0" data-fee_category="transport"  data-trans_fee_id="<?php echo $transport_fee_value->id; ?>">
                                                    </td>
                                                    <td align="left" class="text-rtl-right"><?php echo $this->lang->line('transport_fees'); ?></td>
                                                    <td align="left" class="text-rtl-right"><?php echo $transport_fee_value->month; ?></td>
                                                    <td align="left" class="text text-left"><?php echo $this->customlib->dateformat($transport_fee_value->due_date); ?></td>
                                                    <td align="left" class="text text-left width85">
                                                        <?php
                                                        if ($feetype_balance == 0) {
                                                            ?><span class="label label-success"><?php echo $this->lang->line('paid'); ?></span><?php
                                                        } else if (!empty($transport_fee_value->amount_detail)) {
                                                            ?><span class="label label-warning"><?php echo $this->lang->line('partial'); ?></span><?php
                                                        } else {
                                                            ?><span class="label label-danger"><?php echo $this->lang->line('unpaid'); ?></span><?php
                                                        }
                                                        ?>
                                                    </td>
                                                    <td class="text text-right">
                                                        <?php
                                                        echo amountFormat($transport_fee_value->fees);

                                                        if (($transport_fee_value->due_date != "0000-00-00" && $transport_fee_value->due_date != null)) {
                                                            $tr_fine_amount = $transport_fee_value->fine_amount;
                                                            if ($transport_fee_value->fine_type != "" && $transport_fee_value->fine_type == "percentage") {
                                                                $tr_fine_amount = percentageAmount($transport_fee_value->fees, $transport_fee_value->fine_percentage);
                                                            }
                                                            $__tr_due_fine = _safe_strtotime($transport_fee_value->due_date);
                                                            if ($__tr_due_fine !== false && $__tr_due_fine < strtotime(date('Y-m-d'))) { ?>
                                                                <span data-toggle="popover" class="text text-danger detail_popover"><?php echo " + " . amountFormat($tr_fine_amount); ?></span>
                                                                <div class="fee_detail_popover" style="display: none">
                                                                    <?php if ($tr_fine_amount != "") { ?>
                                                                        <p class="text text-danger"><?php echo $this->lang->line('fine'); ?></p>
                                                                    <?php } ?>
                                                                </div>
                                                            <?php }
                                                        } ?>
                                                    </td>
                                                    <td class="text text-left"></td>
                                                    <td class="text text-left"></td>
                                                    <td class="text text-left"></td>
                                                    <td class="text text-right"><?php echo amountFormat($fee_discount); ?></td>
                                                    <td class="text text-right"><?php echo amountFormat($fee_fine); ?></td>
                                                    <td class="text text-right"><?php echo amountFormat($fee_paid); ?></td>
                                                    <td class="text text-right">
                                                        <?php
                                                        $display_none = "ss-none";
                                                        if ($feetype_balance > 0) {
                                                            $display_none = "";
                                                            echo amountFormat($feetype_balance);
                                                        }
                                                        ?>
                                                    </td>
                                                    <td width="100">
                                                        <?php if ($this->rbac->hasPrivilege('collect_fees', 'can_add')) { ?>
                                                            <button type="button"
                                                                data-student_session_id="<?php echo $transport_fee_value->student_session_id; ?>"
                                                                data-student_fees_master_id="0" data-fee_groups_feetype_id="0"
                                                                data-group="<?php echo $this->lang->line('transport_fees'); ?>"
                                                                data-type="<?php echo $transport_fee_value->month; ?>"
                                                                class="btn btn-xs btn-default myCollectFeeBtn <?php echo $display_none; ?>"
                                                                title="<?php echo $this->lang->line('add_fees'); ?>"
                                                                data-fee-category="transport" data-trans_fee_id="<?php echo $transport_fee_value->id; ?>">
                                                                <i class="fa fa-plus"></i>
                                                            </button>
                                                        <?php } ?>
                                                        <button class="btn btn-xs btn-default printInv"
                                                            data-student_session_id="<?php echo $transport_fee_value->student_session_id; ?>"
                                                            data-fee_master_id="0" data-fee_session_group_id="0"
                                                            data-fee_groups_feetype_id="0" data-fee-category="transport"
                                                            data-trans_fee_id="<?php echo $transport_fee_value->id; ?>"
                                                            title="<?php echo $this->lang->line('print'); ?>"
                                                            data-loading-text="<i class='fa fa-spinner fa-spin '></i>">
                                                            <i class="fa fa-print"></i>
                                                        </button>
                                                    </td>
                                                </tr>

                                                <?php
                                                if (!empty($transport_fee_value->amount_detail) && $transport_fee_value->amount_detail !== '0') {
                                                    $fee_deposits = json_decode($transport_fee_value->amount_detail);
                                                    foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                                        if (is_array($fee_deposits_value)) {
                                                            $fee_deposits_value = (object) $fee_deposits_value;
                                                        }
                                                        ?>
                                                        <tr class="white-td">
                                                            <td align="left"></td>
                                                            <td align="left"></td>
                                                            <td align="left"></td>
                                                            <td align="left"></td>
                                                            <td align="left"></td>
                                                            <td class="text-right"><img src="<?php echo base_url('backend/images/table-arrow.png'); ?>" alt="" /></td>
                                                            <td class="text text-left">
                                                                <a href="#" data-toggle="popover" class="detail_popover"><?php echo $transport_fee_value->student_fees_deposite_id . "/" . $fee_deposits_value->inv_no; ?></a>
                                                                <div class="fee_detail_popover" style="display: none">
                                                                    <?php if ($fee_deposits_value->description == "") { ?>
                                                                        <p class="text text-danger"><?php echo $this->lang->line('no_description'); ?></p>
                                                                    <?php } else { ?>
                                                                        <p class="text text-info"><?php echo $fee_deposits_value->description; ?></p>
                                                                    <?php } ?>
                                                                </div>
                                                            </td>
                                                            <td class="text text-left"><?php echo $this->lang->line(strtolower($fee_deposits_value->payment_mode)); ?></td>
                                                            <td class="text text-left"><?php echo $this->customlib->dateformat($fee_deposits_value->date); ?></td>
                                                            <td class="text text-right"><?php echo amountFormat($fee_deposits_value->amount_discount); ?></td>
                                                            <td class="text text-right"><?php echo amountFormat($fee_deposits_value->amount_fine); ?></td>
                                                            <td class="text text-right"><?php echo amountFormat($fee_deposits_value->amount); ?></td>
                                                            <td></td>
                                                            <td class="text text-right">
                                                                <div class="btn-group ">
                                                                    <div class="pull-right">
                                                                        <?php if ($this->rbac->hasPrivilege('collect_fees', 'can_delete')) { ?>
                                                                            <button type="button" class="btn btn-default btn-xs js-open-invoice-revert-modal" data-invoiceno="<?php echo $transport_fee_value->student_fees_deposite_id . "/" . $fee_deposits_value->inv_no; ?>" data-main_invoice="<?php echo $transport_fee_value->student_fees_deposite_id ?>" data-sub_invoice="<?php echo $fee_deposits_value->inv_no ?>" title="<?php echo $this->lang->line('revert'); ?>">
                                                                                <i class="fa fa-undo"> </i>
                                                                            </button>
                                                                        <?php } ?>
                                                                        <button class="btn btn-xs btn-default printDoc" data-fee-category="transport" data-main_invoice="<?php echo $transport_fee_value->student_fees_deposite_id ?>" data-sub_invoice="<?php echo $fee_deposits_value->inv_no ?>"  title="<?php echo $this->lang->line('print'); ?>"><i class="fa fa-print"></i> </button>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php }
                                                } ?>
                                            <?php
                                        }
                                    }
                                    ?>

                                    <?php
                                    if (!empty($student_discount_fee)) {
                                        foreach ($student_discount_fee as $discount_key => $discount_value) {
                                            $disc_row_amount_base = isset($discount_value['amount']) ? (float) $discount_value['amount'] : 0;
                                            $disc_is_pct          = isset($discount_value['type']) && $discount_value['type'] === 'percentage';
                                            if (!$disc_is_pct && isset($discount_value['status']) && $discount_value['status'] !== 'applied' && $disc_row_amount_base > 0) {
                                                $alot_fee_discount += $disc_row_amount_base;
                                            }
                                            ?>
                                            <tr class="dark-light">
                                                <td></td>
                                                <td align="left" class="text-rtl-right"><?php echo $this->lang->line('discount'); ?></td>
                                                <td align="left" class="text-rtl-right"><?php echo $discount_value['code']; ?></td>
                                                <td align="left"></td>
                                                <td align="left" class="text text-left">
                                                    <?php if ($discount_value['status'] == "applied") { ?>
                                                        <span class="label label-success"><?php echo $this->lang->line($discount_value['status']); ?></span>
                                                        <?php if (!empty($discount_value['payment_id'])) { ?>
                                                            <small class="text-muted"> <?php echo $discount_value['payment_id']; ?></small>
                                                        <?php } ?>
                                                        <?php if ($discount_value['student_fees_discount_description'] != "") { ?>
                                                            <a href="#" data-toggle="popover" class="detail_popover" style="margin-left:4px;"><?php echo $this->lang->line('note'); ?></a>
                                                            <div class="fee_detail_popover" style="display: none">
                                                                <p class="text text-info"><?php echo $discount_value['student_fees_discount_description'] ?></p>
                                                            </div>
                                                        <?php } ?>
                                                    <?php } else { ?>
                                                        <span class="label label-warning"><?php echo $this->lang->line($discount_value['status']); ?></span>
                                                    <?php } ?>
                                                </td>
                                                <td></td>
                                                <td class="text text-left"></td>
                                                <td class="text text-left"></td>
                                                <td class="text text-left"></td>
                                                <td class="text text-right"><?php
                                                    if ($disc_is_pct) {
                                                        echo isset($discount_value['percentage']) && $discount_value['percentage'] !== '' && $discount_value['percentage'] !== null
                                                            ? number_format((float) $discount_value['percentage'], 2, '.', '') . '%'
                                                            : '&mdash;';
                                                    } else {
                                                        echo $currency_symbol . amountFormat($disc_row_amount_base);
                                                    }
                                                ?></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td>
                                                    <div class="btn-group ">
                                                        <div class="pull-right">
                                                            <?php if ($discount_value['status'] == "applied") { ?>
                                                                <?php if ($this->rbac->hasPrivilege('collect_fees', 'can_delete')) { ?>
                                                                    <button type="button" class="btn btn-default btn-xs js-open-discount-revert-modal" data-discounttitle="<?php echo htmlspecialchars($discount_value['code'], ENT_QUOTES, 'UTF-8'); ?>" data-discountid="<?php echo (int) $discount_value['id']; ?>" title="<?php echo $this->lang->line('revert'); ?>">
                                                                        <i class="fa fa-undo"> </i>
                                                                    </button>
                                                                <?php } ?>
                                                            <?php } ?>
                                                            <button type="button" data-modal_title="<?php echo $this->lang->line('discount') . " : " . $discount_value['code']; ?>" data-student_fees_discount_id="<?php echo $discount_value['id']; ?>"
                                                                class="btn btn-xs btn-default applydiscount"
                                                                title="<?php echo $this->lang->line('apply_discount'); ?>">
                                                                <i class="fa fa-check"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php }
                                    } ?>
                                    <tr class="box box-solid total-bg">
                                        <td align="left"></td>
                                        <td align="left"></td>
                                        <td align="left"></td>
                                        <td align="left"></td>
                                        <td align="left" class="text text-left"><?php echo $this->lang->line('grand_total'); ?></td>
                                        <td class="text text-right">
                                            <?php echo $currency_symbol . amountFormat($total_amount); ?>
                                            <span data-toggle="popover" class="text text-danger detail_popover"><?php echo " + " . amountFormat($total_fees_fine_amount); ?></span>
                                            <div class="fee_detail_popover" style="display: none">
                                                <p class="text text-danger"><?php echo $this->lang->line('fine'); ?></p>
                                            </div>
                                        </td>
                                        <td class="text text-left"></td>
                                        <td class="text text-left"></td>
                                        <td class="text text-left"></td>
                                        <td class="text text-right"><?php echo $currency_symbol . amountFormat(($total_discount_amount + $alot_fee_discount)); ?></td>
                                        <td class="text text-right"><?php echo $currency_symbol . amountFormat($total_fine_amount); ?></td>
                                        <td class="text text-right"><?php echo $currency_symbol . amountFormat($total_deposite_amount); ?></td>
                                        <td class="text text-right"><?php echo $currency_symbol . amountFormat(($total_balance_amount - $alot_fee_discount)); ?></td>
                                        <td class="text text-right"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- /.box-body -->
                </div>
            </div>
            <!--/.col (left) -->
        </div>
    </section>
</div>

<div class="modal fade" id="myFeesModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title title text-center fees_title"></h4>
            </div>
            <div class="modal-body pb0">
                <div class="form-horizontal balanceformpopup">
                    <div class="box-body">
                        <input type="hidden" class="form-control" id="std_id" value="<?php echo $student["student_session_id"]; ?>" readonly="readonly"/>
                        <input type="hidden" class="form-control" id="parent_app_key" value="<?php echo $student['parent_app_key'] ?>" readonly="readonly"/>
                        <input type="hidden" class="form-control" id="guardian_phone" value="<?php echo $student['guardian_phone'] ?>" readonly="readonly"/>
                        <input type="hidden" class="form-control" id="guardian_email" value="<?php echo $student['guardian_email'] ?>" readonly="readonly"/>
                        <input type="hidden" class="form-control" id="student_fees_master_id" value="0" readonly="readonly"/>
                        <input type="hidden" class="form-control" id="fee_groups_feetype_id" value="0" readonly="readonly"/>
                        <input type="hidden" class="form-control" id="transport_fees_id" value="0" readonly="readonly"/>
                        <input type="hidden" class="form-control" id="fee_category" value="" readonly="readonly"/>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-3 control-label">
                                <?php echo $this->lang->line('date'); ?><small class="req"> *</small>
                            </label>
                            <div class="col-sm-9">
                                <!-- FIX: force Nepali calendar -->
                                <input id="date" 
                                       name="admission_date" 
                                       placeholder="" 
                                       type="text" 
                                       class="form-control nepali-datepicker" 
                                       value="" 
                                      />
                                <span class="text-danger" id="date_error"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label"><?php echo $this->lang->line('amount'); ?> (<?php echo $currency_symbol; ?>)<small class="req"> *</small></label>
                            <div class="col-sm-9">
                                <input type="text" autofocus="" class="form-control modal_amount" id="amount" value="0">
                                <span class="text-danger" id="amount_error"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="discount_group" class="col-sm-3 control-label"><?php echo $this->lang->line('fees_discount'); ?></label>
                            <div class="col-sm-9">
                                <select class="form-control modal_discount_group" id="discount_group" title="<?php echo htmlspecialchars($this->lang->line('fees_discount_list')); ?>">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="text-danger" id="discount_group_error"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label"><?php echo $this->lang->line('discount'); ?> (<?php echo $currency_symbol; ?>)<small class="req"> *</small></label>
                            <div class="col-sm-9">
                                <div class="row">
                                    <div class="col-md-5 col-sm-5">
                                        <div class="">
                                            <input type="text" class="form-control" id="amount_discount" value="0">
                                            <span class="text-danger" id="amount_discount_error"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-2 ltextright">
                                        <label for="inputPassword3" class="control-label"><?php echo $this->lang->line('fine'); ?> (<?php echo $currency_symbol; ?>)<small class="req">*</small></label>
                                    </div>
                                    <div class="col-md-5 col-sm-5">
                                        <div class="">
                                            <input type="text" class="form-control" id="amount_fine" value="0">
                                            <span class="text-danger" id="amount_fine_error"></span>
                                        </div>
                                    </div>
                                </div>
                            </div><!--./col-sm-9-->
                        </div>
                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label"><?php echo $this->lang->line('payment_mode'); ?></label>
                            <div class="col-sm-9">
                                <label class="radio-inline"><input type="radio" name="payment_mode_fee" value="Cash" checked="checked"><?php echo $this->lang->line('cash'); ?></label>
                                <label class="radio-inline"><input type="radio" name="payment_mode_fee" value="Cheque"><?php echo $this->lang->line('cheque'); ?></label>
                                <label class="radio-inline"><input type="radio" name="payment_mode_fee" value="DD"><?php echo $this->lang->line('dd'); ?></label>
                                <label class="radio-inline"><input type="radio" name="payment_mode_fee" value="bank_transfer"><?php echo $this->lang->line('bank_transfer'); ?></label>
                                <label class="radio-inline"><input type="radio" name="payment_mode_fee" value="upi"><?php echo $this->lang->line('upi'); ?></label>
                                <label class="radio-inline"><input type="radio" name="payment_mode_fee" value="card"><?php echo $this->lang->line('card'); ?></label>
                                <span class="text-danger" id="payment_mode_error"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label"><?php echo $this->lang->line('note'); ?></label>
                            <div class="col-sm-9">
                                <textarea class="form-control" rows="3" id="description" placeholder=""></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?php echo $this->lang->line('cancel'); ?></button>
                <button type="button" class="btn cfees save_button" id="load" data-action="collect" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> <?php echo $this->lang->line('processing'); ?>"> <?php echo $currency_symbol; ?> <?php echo $this->lang->line('collect_fees'); ?> </button>
                <button type="button" class="btn cfees save_button" id="load" data-action="print" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> <?php echo $this->lang->line('processing'); ?>"> <?php echo $currency_symbol; ?> <?php echo $this->lang->line('collect_print'); ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="myDisApplyModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title title text-center discount_title"></h4>
            </div>
            <div class="modal-body pb0">
                <div class="form-horizontal">
                    <div class="box-body">
                        <input type="hidden" class="form-control" id="student_fees_discount_id" value=""/>
                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label"><?php echo $this->lang->line('payment_id'); ?> <small class="req">*</small></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="discount_payment_id">
                                <span class="text-danger" id="discount_payment_id_error"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label"><?php echo $this->lang->line('description'); ?></label>
                            <div class="col-sm-9">
                                <textarea class="form-control" rows="3" id="dis_description" placeholder=""></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?php echo $this->lang->line('cancel'); ?></button>
                <button type="button" class="btn cfees dis_apply_button" id="load" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> <?php echo $this->lang->line('processing'); ?>"> <?php echo $this->lang->line('apply_discount'); ?></button>
            </div>
        </div>
    </div>
</div>

<div class="delmodal modal fade" id="confirm-discountdelete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel"><?php echo $this->lang->line('confirmation'); ?></h4>
            </div>
            <div class="modal-body">
                <p><?php echo $this->lang->line('are_you_sure_want_to_revert'); ?> <b class="discount_title"></b> <?php echo $this->lang->line('discount_this_action_is_irreversible'); ?></p>
                <p><?php echo $this->lang->line('do_you_want_to_proceed') ?></p>
                <p class="debug-url"></p>
                <input type="hidden" name="discount_id"  id="discount_id" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('cancel'); ?></button>
                <a class="btn btn-danger btn-discountdel"><?php echo $this->lang->line('revert'); ?></a>
            </div>
        </div>
    </div>
</div>

<div class="delmodal modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel"><?php echo $this->lang->line('confirmation'); ?></h4>
            </div>
            <div class="modal-body">
                <p><?php echo $this->lang->line('are_you_sure_want_to_delete'); ?> <b class="invoice_no"></b> <?php echo $this->lang->line('invoice_this_action_is_irreversible') ?></p>
                <p><?php echo $this->lang->line('do_you_want_to_proceed') ?></p>
                <p class="debug-url"></p>
                <input type="hidden" name="main_invoice"  id="main_invoice" value="">
                <input type="hidden" name="sub_invoice" id="sub_invoice"  value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('cancel'); ?></button>
                <a class="btn btn-danger btn-ok"><?php echo $this->lang->line('revert'); ?></a>
            </div>
        </div>
    </div>
</div>

<div class="norecord modal fade" id="confirm-norecord" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <p><?php echo $this->lang->line('no_record_found'); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('cancel'); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="listCollectionModal" class="modal fade">
    <div class="modal-dialog">
        <form action="<?php echo site_url('studentfee/addfeegroup'); ?>" method="POST" id="collect_fee_group">
            <div class="modal-content">
                <!-- //================ -->
                <input type="hidden" class="form-control" id="group_std_id" name="student_session_id" value="<?php echo $student["student_session_id"]; ?>" readonly="readonly"/>
                <input type="hidden" class="form-control" id="group_parent_app_key" name="parent_app_key" value="<?php echo $student['parent_app_key'] ?>" readonly="readonly"/>
                <input type="hidden" class="form-control" id="group_guardian_phone" name="guardian_phone" value="<?php echo $student['guardian_phone'] ?>" readonly="readonly"/>
                <input type="hidden" class="form-control" id="group_guardian_email" name="guardian_email" value="<?php echo $student['guardian_email'] ?>" readonly="readonly"/>
                <!-- //================ -->
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><?php echo $this->lang->line('collect_fees'); ?></h4>
                </div>
                <div class="modal-body">
                </div>
            </div>
        </form>
    </div>
</div>

<div id="processing_fess_modal" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line('processing_fees'); ?></h4>
            </div>
            <div class="modal-body">
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
/* =========================================================
   0) FIXED HEADER PLUGIN (define before use) + safe init
   ========================================================= */
(function($){
  if (!$.fn.fixedHeader) {
    $.fn.fixedHeader = function (options) {
      var config = { topOffset: 50 };
      if (options) { $.extend(config, options); }
      return this.each(function () {
        var o = $(this), $win = $(window), $head = $('thead.header', o);
        var isFixed = 0, headTop = $head.length && $head.offset().top - config.topOffset;
        function processScroll() {
          if (!o.is(':visible')) return;
          if ($('thead.header-copy', o).length) {
            $('thead.header-copy', o).width($('thead.header', o).width());
          }
          var scrollTop = $win.scrollTop();
          var t = $head.length && $head.offset().top - config.topOffset;
          if (!isFixed && headTop !== t) headTop = t;
          if (scrollTop >= headTop && !isFixed) isFixed = 1;
          else if (scrollTop <= headTop && isFixed) isFixed = 0;
          isFixed
            ? $('thead.header-copy', o).offset({ left: $head.offset().left }).removeClass('hide')
            : $('thead.header-copy', o).addClass('hide');
        }
        $win.on('scroll', processScroll);
        $head.on('click', function(){ if (!isFixed) setTimeout(function(){ $win.scrollTop($win.scrollTop()-47); }, 10); });
        $head.clone().removeClass('header').addClass('header-copy header-fixed').appendTo(o);
        var header_width = $head.width();
        o.find('thead.header-copy').width(header_width);
        o.find('thead.header > tr:first > th').each(function (i, h) {
          var w = $(h).width();
          o.find('thead.header-copy> tr > th:eq(' + i + ')').width(w);
        });
        $head.css({ margin:'0 auto', width:o.width(), 'background-color': config.bgColor });
        processScroll();
      });
    };
  }
})(jQuery);

function initFixedHeaderSafely(){
  if (window.jQuery && jQuery.fn && jQuery.fn.fixedHeader) {
    try { jQuery('.table-fixed-header').fixedHeader(); }
    catch(e){ console.warn('fixedHeader init failed:', e); }
  } else {
    console.warn('$.fn.fixedHeader not found, skipping init');
  }
}

/* =========================================================
   1) GLOBALS
   ========================================================= */
var base_url = '<?php echo base_url() ?>';
var fee_amount = 0;
var fee_type_amount = 0;

/* ====== 1.1) CSRF handling (cookie-based, rotates safely) ====== */
var CSRF_NAME = '<?php echo $this->security->get_csrf_token_name(); ?>';
var CSRF_COOKIE = (function () {
  return CSRF_NAME;
})();

function getCookie(name){
  var m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([$?*|{}\[\]\\\/\+^])/g,'\\$1') + '=([^;]*)'));
  return m ? decodeURIComponent(m[1]) : '';
}

function currentCsrfValue(){
  return getCookie(CSRF_COOKIE) || getCookie(CSRF_NAME);
}

function appendCsrfToData(dataObjOrString){
  try {
    var key = encodeURIComponent(CSRF_NAME);
    var val = encodeURIComponent(currentCsrfValue() || '');
    
    if (typeof dataObjOrString === 'string'){
      return dataObjOrString + (dataObjOrString ? '&' : '') + key + '=' + val;
    } else if (dataObjOrString && typeof dataObjOrString === 'object') {
      var o = $.extend({}, dataObjOrString || {});
      o[CSRF_NAME] = currentCsrfValue() || '';
      return o;
    } else {
      var o = {};
      o[CSRF_NAME] = currentCsrfValue() || '';
      return o;
    }
  } catch (e) {
    console.log('PAYMENT DEBUG: appendCsrfToData error:', e);
    return dataObjOrString;
  }
}

function ensureFormHasCsrf($form){
  if (!$form || !$form.length) return;
  var $hidden = $form.find('input[name="'+CSRF_NAME+'"]');
  if (!$hidden.length){
    $('<input type="hidden">').attr('name', CSRF_NAME).val(currentCsrfValue() || '').appendTo($form);
  } else {
    $hidden.val(currentCsrfValue() || '');
  }
}

// FIXED: Safer CSRF attachment
$.ajaxPrefilter(function (options, originalOptions) {
  if ((options.type || '').toUpperCase() === 'POST') {
    try {
      options.data = appendCsrfToData(options.data);
    } catch (e) {
      console.log('PAYMENT DEBUG: CSRF attachment error:', e);
    }
  }
});

// FIXED: Enhanced AJAX complete handler
$(document).ajaxSuccess(function (_evt, xhr, settings) {
}).ajaxComplete(function (_evt, xhr, settings) {
  try {
    var text = xhr.responseText || '';
    console.log('PAYMENT DEBUG: AJAX Complete - URL:', settings.url, 'Status:', xhr.status);
    
    if (text.indexOf('/site/userlogin') !== -1 && text.indexOf('<form') !== -1 && text.indexOf('name="username"') !== -1){
      console.error('PAYMENT DEBUG: Login page detected in AJAX response');
      console.error('URL:', settings.url);
      console.error('Method:', settings.type);
      console.error('Data sent:', settings.data);
      console.error('Response preview:', text.substring(0, 500));
      alert('Login page returned. Check console for details. URL: ' + settings.url);
    }
  } catch(e){
    console.error('PAYMENT DEBUG: Error in ajaxComplete:', e);
  }
});

/* =========================================================
   2) HELPERS - FIXED NEPALI DATE FUNCTION
   ========================================================= */
function enforceNepaliDateOnly($root) {
  var $dateFields = $root.find('input[name*="date"], input.nepali-datepicker, input[id*="date"], input[name="date"]');
  
  $dateFields.each(function() {
    var $date = $(this);
    console.log('DEBUG: Processing date field:', $date.attr('name'), $date.attr('id'));

    try { 
      if ($date.data('datepicker')) { console.log('DEBUG: Destroying bootstrap datepicker'); $date.datepicker('destroy'); }
      if ($date.data('DateTimePicker')) { console.log('DEBUG: Destroying datetime picker'); $date.data('DateTimePicker').destroy(); }
      if ($date.hasClass('hasDatepicker')) { console.log('DEBUG: Destroying jquery datepicker'); $date.datepicker('destroy'); }
    } catch(e){ console.log('DEBUG: Calendar destroy error:', e); }

    try {
      if ($.fn.nepaliDatePicker && ($date.data('ndp-initialized') || $date.data('nepaliDatePicker'))) {
        try { $date.nepaliDatePicker('destroy'); } catch (eN1) { /* plugin may not implement destroy */ }
      }
    } catch (e) { /* ignore */ }
    $date.removeData('ndp-initialized').removeData('nepaliDatePicker');

    $date
      .removeAttr('data-provide data-date data-date-format readonly data-toggle data-datepicker data-date-autoclose')
      .removeClass('datepicker hasDatepicker form_datetime bootstrap-datepicker')
      .off('.datepicker .datetimepicker .bootstrap-datepicker')
      .addClass('nepali-datepicker form-control')
      .attr('type','text')
      .prop('readonly', false)
      .css('background-color', 'white');

    setTimeout(function() {
      if ($.fn.nepaliDatePicker) {
        // Get today's BS date before initializing
        var todayBsStr = '';
        if (window.NepaliFunctions && typeof NepaliFunctions.GetCurrentBsDate === 'function') {
          try {
            var bs = NepaliFunctions.GetCurrentBsDate();
            if (bs && bs.year && bs.month && bs.day) {
              var month = String(bs.month).padStart(2, '0');
              var day = String(bs.day).padStart(2, '0');
              todayBsStr = bs.year + '-' + month + '-' + day;
            }
          } catch(e) {}
        }
        if (!todayBsStr && window.NepaliFunctions && typeof NepaliFunctions.AD2BS === 'function') {
          try {
            var today = new Date();
            var bs = NepaliFunctions.AD2BS({
              year: today.getFullYear(),
              month: today.getMonth() + 1,
              day: today.getDate()
            });
            if (bs && bs.year && bs.month && bs.day) {
              var month = String(bs.month).padStart(2, '0');
              var day = String(bs.day).padStart(2, '0');
              todayBsStr = bs.year + '-' + month + '-' + day;
            }
          } catch(e) {}
        }
        
        // Set value before initializing if empty
        if (todayBsStr && (!$date.val() || $date.val() === '')) {
          $date.val(todayBsStr);
        }
        
        $date.nepaliDatePicker({
          language: 'np',
          ndpYear: true,
          ndpMonth: true,
          readOnlyInput: false,
          closeOnDateSelect: false, // Don't auto-close - let user select date
          dateFormat: 'YYYY-MM-DD'
        });
        
        // Ensure value is set after initialization
        if (todayBsStr && (!$date.val() || $date.val() === '')) {
          setTimeout(function() {
            $date.val(todayBsStr);
            $date.trigger('change');
          }, 100);
        }
        
        console.log('DEBUG: Initialized Nepali datepicker for', $date.attr('name') || $date.attr('id'), 'with closeOnDateSelect: false');
      } else if (typeof window.initNepaliDatepickers === 'function') {
        window.initNepaliDatepickers($root);
      }
      
      setTimeout(function() {
        $date.prop('readonly', false);
      }, 200);
    }, 100);
  });
}

function bindNepaliDateEvents($root) {
  $root.find('.nepali-datepicker, input[name*="date"], input.date_fee')
    .off('nepali.datepicker.dateChanged.ndp change.ndp input.ndp')
    .on('nepali.datepicker.dateChanged.ndp', function (e) {
      var $this = $(this);
      var selectedDate = '';
      
      if (e && e.datePickerData) {
        var d = e.datePickerData;
        var month = (d.month || d.monthNumber || 0).toString().padStart(2, '0');
        var day = (d.day || d.dayNumber || 0).toString().padStart(2, '0');
        var year = d.year || 0;
        selectedDate = year + '-' + month + '-' + day;
      }
      
      if (selectedDate) {
        $this.val(selectedDate);
      } else {
        var v = $this.val();
        if ((!v || v === '') && e && e.datePickerData) {
          var fromPlugin = e.datePickerData.formattedDate || e.datePickerData.bs || '';
          if (fromPlugin) $this.val(fromPlugin);
        }
      }
      
      $this.trigger('change');
    })
    .on('change.ndp input.ndp', function () {
      // Ensure value is set
      if (!$(this).val() && $(this).data('nepaliDatePicker')) {
        var currentVal = $(this).data('nepaliDatePicker').getValue();
        if (currentVal) {
          $(this).val(currentVal);
        }
      }
    });
}

/** English/Bootstrap pickers only — never $('.datepicker') globally (Nepali UI uses .datepicker inside #ndp-nepali-box). */
function removeBootstrapEnglishDatepickerWidgets() {
  var n = 0;
  n += $('.bootstrap-datetimepicker-widget, .ui-datepicker').remove().length;
  n += $('body > .datepicker-dropdown, body > .datepicker-inline').remove().length;
  n += $('body').children('.datepicker').not('#ndp-nepali-box').remove().length;
  return n;
}

function applyAddFeesDateLogicToGroupModal() {
  var $m = $('#listCollectionModal');
  if (!$m.length) return;

  setTimeout(function () {
    removeBootstrapEnglishDatepickerWidgets();
    $m.find('input[data-provide="datepicker"]').removeAttr('data-provide');

    // Force Nepali calendar setup using shared helpers
    enforceNepaliDateOnly($m);
    bindNepaliDateEvents($m);

    // Pre-fill with today's BS date if empty
    var todayBsStr = '';
    try {
      if (window.NepaliFunctions && typeof NepaliFunctions.GetCurrentBsDate === 'function') {
        var bsToday = NepaliFunctions.GetCurrentBsDate();
        if (bsToday && bsToday.year && bsToday.month && bsToday.day) {
          todayBsStr = bsToday.year + '-' + String(bsToday.month).padStart(2, '0') + '-' +
            String(bsToday.day).padStart(2, '0');
        }
      }
      if (!todayBsStr && window.NepaliFunctions && typeof NepaliFunctions.AD2BS === 'function') {
        var ad = new Date();
        var bs = NepaliFunctions.AD2BS({ year: ad.getFullYear(), month: ad.getMonth() + 1, day: ad.getDate() });
        if (bs && bs.year && bs.month && bs.day) {
          todayBsStr = bs.year + '-' + String(bs.month).padStart(2, '0') + '-' + String(bs.day).padStart(2, '0');
        }
      }
    } catch (err) {
      console.warn('DEBUG: Unable to compute today BS date', err);
    }

    if (todayBsStr) {
      $m.find('input.nepali-datepicker, input.nepali-date-picker, input.date_fee, input[name="collected_date"]').each(function () {
        if (!$(this).val()) {
          $(this).val(todayBsStr);
        }
      });
    }
  }, 0);

  // Cleanup when modal is hidden
  $m.off('hidden.bs.modal.removeEnglish').on('hidden.bs.modal.removeEnglish', function () {
    $('#ndp-nepali-box').remove();
  });
}

function Popup(data, winload) {
  var frame1 = $('<iframe />').attr("id", "printDiv");
  frame1[0].name = "frame1";
  frame1.css({position:"absolute", top:"-1000000px"});
  $("body").append(frame1);
  var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow.document :
                 frame1[0].contentDocument.document || frame1[0].contentDocument;
  frameDoc.open();
  frameDoc.write('<html><head>');
  frameDoc.write('<link rel="stylesheet" href="' + base_url + 'backend/bootstrap/css/bootstrap.min.css">');
  frameDoc.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/font-awesome.min.css">');
  frameDoc.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/AdminLTE.min.css">');
  frameDoc.write('</head><body>' + data + '</body></html>');
  frameDoc.close();
  setTimeout(function () {
    document.getElementById('printDiv').contentWindow.focus();
    document.getElementById('printDiv').contentWindow.print();
    $("#printDiv", top.document).remove();
    if (winload) window.location.reload(true);
  }, 500);
}

function removeEnglishCalendars() {
  removeBootstrapEnglishDatepickerWidgets();
  $('input.datepicker, input.hasDatepicker').removeClass('datepicker hasDatepicker').off('.datepicker');
}

/* =========================================================
   2.5) GLOBAL ENGLISH CALENDAR BLOCKER - MUST RUN FIRST
   ========================================================= */
(function() {
  console.log('DEBUG: Setting up global English calendar blocker');
  
  // Block English calendar initialization at the lowest level
  var originalDatepicker = $.fn.datepicker;
  if (originalDatepicker) {
    $.fn.datepicker = function(options) {
      // Check if this is being called on a field in our modals
      var $this = $(this);
      var isInModal = $this.closest('#listCollectionModal, #myFeesModal').length > 0;
      var isDateField = $this.hasClass('date_fee') || 
                        $this.hasClass('nepali-datepicker') ||
                        ($this.attr('name') && $this.attr('name').indexOf('date') !== -1);
      
      if (isInModal && isDateField) {
        console.log('DEBUG: Blocked English datepicker initialization on:', $this.attr('name') || $this.attr('id'));
        return $this; // Return jQuery object without initializing
      }
      
      // Allow normal initialization for other fields
      return originalDatepicker.apply(this, arguments);
    };
    console.log('DEBUG: English datepicker blocker installed');
  }
  
  // Block Bootstrap datetimepicker
  if ($.fn.datetimepicker) {
    var originalDatetimepicker = $.fn.datetimepicker;
    $.fn.datetimepicker = function(options) {
      var $this = $(this);
      var isInModal = $this.closest('#listCollectionModal, #myFeesModal').length > 0;
      if (isInModal) {
        console.log('DEBUG: Blocked Bootstrap datetimepicker initialization');
        return $this;
      }
      return originalDatetimepicker.apply(this, arguments);
    };
    console.log('DEBUG: Bootstrap datetimepicker blocker installed');
  }
})();

/* =========================================================
   3) DOM READY
   ========================================================= */
$(function () {
  console.log('DEBUG: DOM ready, scripts loaded successfully');

  if (!document.getElementById('ndp-zfix')) {
    $('head').append('<style id="ndp-zfix">#ndp-nepali-box{z-index:200000 !important; position:absolute !important;}</style>');
    console.log('DEBUG: Added Nepali calendar z-index fix');
  }

  $('#listCollectionModal,#processing_fess_modal,#confirm-norecord,#myFeesModal,#confirm-delete,#confirm-discountdelete')
    .modal({ backdrop:'static', keyboard:false, show:false });

  if ($.fn.dataTable) {
    $.extend($.fn.dataTable.defaults, {
      searching:false, ordering:false, paging:false, bSort:false, info:false
    });
  }

  initFixedHeaderSafely();

  $('.detail_popover').popover({
    placement:'right', title:'', trigger:'hover', container:'body', html:true,
    content:function(){ return $(this).closest('td').find('.fee_detail_popover').html(); }
  });

  // Convert date display to BS (Nepali) date
  try {
    var $dateDisplay = $('#current-date-display');
    if ($dateDisplay.length && window.NepaliFunctions) {
      var today = new Date();
      if (window.NepaliFunctions.GetCurrentBsDate) {
        var bs = window.NepaliFunctions.GetCurrentBsDate();
        if (bs && bs.year && bs.month && bs.day) {
          var month = String(bs.month).padStart(2, '0');
          var day = String(bs.day).padStart(2, '0');
          var bsDateStr = month + '-' + day + '-' + bs.year;
          var dateLabel = $dateDisplay.text().split(':')[0] + ': ';
          $dateDisplay.text(dateLabel + bsDateStr);
        }
      } else if (window.NepaliFunctions.AD2BS) {
        var bs = window.NepaliFunctions.AD2BS({
          year: today.getFullYear(),
          month: today.getMonth() + 1,
          day: today.getDate()
        });
        if (bs && bs.year && bs.month && bs.day) {
          var month = String(bs.month).padStart(2, '0');
          var day = String(bs.day).padStart(2, '0');
          var bsDateStr = month + '-' + day + '-' + bs.year;
          var dateLabel = $dateDisplay.text().split(':')[0] + ': ';
          $dateDisplay.text(dateLabel + bsDateStr);
        }
      }
    }
  } catch(e) {
    console.warn('Could not convert date display to BS:', e);
  }
});

/* =========================================================
   3b) COLLECT SELECTED — per-row discount
   ========================================================= */
var bulkCollectCurrency = <?php echo json_encode($currency_symbol); ?>;

function parseBulkMoney(v) {
  if (v === undefined || v === null || v === '') return 0;
  return parseFloat(String(v).replace(/,/g, '')) || 0;
}

function recalcBulkCollectTotal($modal) {
  var totalNet = 0, totalDisc = 0, totalFine = 0;
  $modal.find('.bulk-fee-item').each(function () {
    var $item = $(this);
    totalNet += parseBulkMoney($item.find('.bulk-fee-amount-net').val());
    totalDisc += parseBulkMoney($item.find('.bulk-amount-discount').val());
    totalFine += parseBulkMoney($item.find('.bulk-fee-fine').val());
  });
  var grand = totalNet + totalFine;
  $modal.find('.bulk-total-pay').text(bulkCollectCurrency + grand.toFixed(2));
  if (totalDisc > 0) {
    $modal.find('.bulk-total-discount-label, .bulk-total-discount-amount').show();
    $modal.find('.bulk-total-discount-amount').text('-' + bulkCollectCurrency + totalDisc.toFixed(2));
  } else {
    $modal.find('.bulk-total-discount-label, .bulk-total-discount-amount').hide();
  }
}

function applyBulkDiscountToRow($item) {
  var balance = parseBulkMoney($item.data('balance'));
  var $sel = $item.find('.bulk-fee-discount');
  var $opt = $sel.find('option:selected');
  var disc = 0;

  if ($opt.val()) {
    var typeRaw = ($opt.attr('data-type') || 'fix').toString().toLowerCase();
    var pct = parseBulkMoney($opt.attr('data-percentage'));
    var fixAmt = parseBulkMoney($opt.attr('data-disamount'));
    if (typeRaw === 'percentage') {
      disc = (balance * pct) / 100;
    } else {
      disc = fixAmt;
    }
    if (disc > balance) disc = balance;
  }

  var net = Math.max(0, balance - disc);
  $item.find('.bulk-amount-discount').val(disc.toFixed(2));
  $item.find('.bulk-fee-amount-net').val(net.toFixed(2));
  $item.find('.bulk-line-pay').text(bulkCollectCurrency + net.toFixed(2));
  $item.find('.bulk-discount-display').val(disc.toFixed(2));
}

function refreshBulkDiscountOptions($modal) {
  // Same discount may be used on multiple fee lines in one bulk payment (one use per fee line).
  $modal.find('.bulk-fee-discount option').prop('disabled', false);
}

function initBulkCollectDiscounts($modal) {
  $modal.find('.bulk-fee-item').each(function () {
    applyBulkDiscountToRow($(this));
  });
  recalcBulkCollectTotal($modal);
  refreshBulkDiscountOptions($modal);
}

$(document).off('change.bulkDisc', '#listCollectionModal .bulk-fee-discount');
$(document).on('change.bulkDisc', '#listCollectionModal .bulk-fee-discount', function () {
  var $modal = $('#listCollectionModal');
  var $item = $(this).closest('.bulk-fee-item');
  applyBulkDiscountToRow($item);
  recalcBulkCollectTotal($modal);
  refreshBulkDiscountOptions($modal);
});

/* =========================================================
   4) COLLECT SELECTED → Bootstrap modal path - FIXED
   ========================================================= */
$(document).off('click', '.collectSelected');
$(document).on('click', '.collectSelected', function (e) {
  e.preventDefault();
  e.stopImmediatePropagation();

  $('#customCollectionModal, #customModal').remove();

  var $btn = $(this);
  var items = [];
  $("input[name='fee_checkbox']:checked").each(function () {
    items.push({
      fee_category: $(this).data('fee_category'),
      trans_fee_id: $(this).data('trans_fee_id'),
      fee_session_group_id: $(this).data('fee_session_group_id'),
      fee_master_id: $(this).data('fee_master_id'),
      fee_groups_feetype_id: $(this).data('fee_groups_feetype_id')
    });
  });

  if (!items.length) { alert("<?php echo $this->lang->line('no_record_selected'); ?>"); return; }

  $.ajax({
    type:'POST',
    url: base_url + "studentfee/getcollectfee",
    data: { data: JSON.stringify(items) },
    dataType:'json',
    beforeSend:function(){ if ($btn.button) $btn.button('loading'); },
    success:function(resp){
      if (resp && resp.status == 1 && resp.view) {
        var $m = $("#listCollectionModal");
        
        // Remove any existing backdrops first
        $(".modal-backdrop").remove();
        
        $m.find(".modal-body").html(resp.view);
        $m.appendTo("body");
        
        // Show modal properly
        $m.modal({
          backdrop: 'static',
          keyboard: false,
          show: true
        });
        
        // Force modal to be visible and clickable
        setTimeout(function() {
          // Remove ALL duplicate backdrops
          var backdropCount = $('.modal-backdrop').length;
          while (backdropCount > 1) {
            $('.modal-backdrop').last().remove();
            backdropCount = $('.modal-backdrop').length;
          }
          
          // Remove any shadow overlays
          $('body.modal-open::before').remove();
          $('body > div[style*="position: fixed"][style*="z-index"]').not('.modal').not('.modal-backdrop').each(function() {
            if ($(this).css('z-index') > 1050 && $(this).css('z-index') < 1065) {
              $(this).remove();
            }
          });
          
          $m.css({
            'display': 'block',
            'z-index': '1065',
            'pointer-events': 'auto'
          });
          $('.modal-backdrop').css({
            'z-index': '1060',
            'background-color': 'rgba(0, 0, 0, 0.5)',
            'pointer-events': 'auto'
          });
          $m.find('.modal-dialog').css({
            'z-index': '1066',
            'pointer-events': 'auto'
          });
          $m.find('.modal-content').css({
            'pointer-events': 'auto',
            'z-index': '1067'
          });
          $m.find('.modal-footer').css({
            'pointer-events': 'auto',
            'z-index': '1068'
          });
          $m.find('.modal-footer button, .modal-body input, .modal-body select, .modal-body textarea, .modal-body button').css({
            'pointer-events': 'auto',
            'cursor': 'pointer'
          });
        }, 50);

        setTimeout(function() {
          console.log('DEBUG: Collect Selected modal content loaded, initializing date fields');

          removeBootstrapEnglishDatepickerWidgets();
          $m.find('input[data-provide="datepicker"]').removeAttr('data-provide');

          // Initialize Nepali picker via shared helper
          applyAddFeesDateLogicToGroupModal();

          console.log('Collect Selected modal date fields initialized with Nepali datepicker with popup enabled');
        }, 100);

        initBulkCollectDiscounts($m);

        setTimeout(function(){
          var off = $m.offset() || {top:null,left:null};
          console.log("[listCollectionModal] visible:", $m.is(":visible"), "z", $m.css("z-index"), "offset", off);
        }, 0);
      } else {
        alert((resp && resp.message) ? resp.message : "<?php echo $this->lang->line('no_record_found'); ?>");
      }
    },
    error:function(xhr){
      console.error('getcollectfee failed', xhr.status, xhr.responseText);
      alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
    },
    complete:function(){ if ($btn.button) $btn.button('reset'); }
  });
});

window.submitCustomForm = function () {
  var $form = $('#listCollectionModal').find('#collect_fee_group');
  if ($form.length) $form.trigger('submit');
  else alert('Form not found in modal');
};

// FIXED: Enhanced form submission handling for both collect and pay actions
$(document).off('submit.ajaxCollect', '#collect_fee_group');
$(document).on('submit.ajaxCollect', '#collect_fee_group', function(e){
  var $form = $(this);
  var paymentMethod = $form.find('input[name="payment_method"]').val();
  
  if (paymentMethod === 'pay') {
    console.log('Pay action detected, allowing normal form submission');
    return true;
  }
  
  e.preventDefault();
  
  var url = $form.attr('action') || '<?php echo site_url("studentfee/addfeegroup"); ?>';
  var $btn = $form.find('button[type=submit], .btn-collect-fees, .collect-fees-submit').first();

  $.ajax({
    type:"POST",
    url:url,
    dataType:'JSON',
    data:$form.serialize(),
    beforeSend:function(){ if ($btn.length && $btn.button) $btn.button('loading'); },
    success:function(response){
      if (response && (response.status == 1 || response.status == '1')) {
        var successMsg = '<?php echo $this->lang->line("payment_successful") ? $this->lang->line("payment_successful") : "Payment successful! / भुक्तानी सफलतापूर्वक भयो!"; ?>';
        var $toast = $('<div class="payment-success-toast" style="position: fixed; top: 20px; right: 20px; background: #5cb85c; color: white; padding: 15px 25px; border-radius: 5px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); z-index: 999999; font-size: 14px; font-weight: bold; min-width: 250px; text-align: center;"><i class="fa fa-check-circle" style="margin-right: 8px;"></i>' + successMsg + '</div>');
        $('body').append($toast);
        $('#listCollectionModal').modal('hide');
        $('.modal-backdrop').remove();
        if ($form.length && $form[0]) {
          $form[0].reset();
        }
        $form.find('.text-danger').html('');
        if ($btn.length && $btn.button) $btn.button('reset');
        var nextUrl = (response.redirect && String(response.redirect).length) ? response.redirect : (window.location.href.split('#')[0]);
        setTimeout(function () {
          window.location.href = nextUrl;
        }, 500);
      } else if (response && (response.status == 0 || response.status == '0') && response.error) {
        $.each(response.error, function (i, v) { 
          var $errorField = $('#form_collection_' + i + '_error');
          if ($errorField.length) {
            $errorField.html(v);
          } else {
            // Try alternative error field names
            $('#' + i.replace('_', '') + '_error').html(v);
          }
        });
      } else {
        console.error('Unexpected response:', response);
        alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
      }
    },
    error:function(xhr, status, error){
      var responseText = xhr.responseText || '';
      
      console.error('PAYMENT DEBUG: AJAX Error Details:');
      console.error('Status:', xhr.status);
      console.error('Error:', error);
      console.error('Response Text:', responseText);
      console.error('Headers:', xhr.getAllResponseHeaders());
      
      if (responseText.indexOf('/site/userlogin') !== -1 || xhr.status === 401) {
        alert('Session expired or authentication failed. Response: ' + responseText.substring(0, 200));
        return;
      }
      
      console.error('Form submission failed:', xhr.status, error, responseText);
      alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
    },
    complete:function(){ if ($btn.length && $btn.button) $btn.button('reset'); }
  });
});

// FIXED: Pay button handler that works with existing environment
$(document).off('click', '#listCollectionModal .btn-pay, #listCollectionModal .pay, #listCollectionModal .payment_collect, #listCollectionModal [data-action="pay"], #listCollectionModal [data-submit="pay"], #listCollectionModal button:contains("Pay")');

$(document).on('click', '#listCollectionModal .btn-pay, #listCollectionModal .pay, #listCollectionModal .payment_collect, #listCollectionModal [data-action="pay"], #listCollectionModal [data-submit="pay"], #listCollectionModal button:contains("Pay")', function(e){
    console.log('PAYMENT DEBUG: Pay button clicked');
    e.preventDefault();
    e.stopPropagation();
    
    var $btn = $(this);
    var $form = $('#listCollectionModal').find('form').first();
    
    if (!$form.length) {
        $form = $('#collect_fee_group');
    }
    
    if (!$form.length) { 
        alert('Payment form not found'); 
        return false; 
    }

    // Date validation only
    var dateError = false;
    var $dateInputs = $form.find('input[name*="date"], .nepali-datepicker');
    
    $dateInputs.each(function() {
        var val = $(this).val();
        if (!val || val.trim() === '') {
            alert('Please select date for all fees');
            $(this).focus();
            dateError = true;
            return false;
        }
    });
    
    if (dateError) return false;

    // Skip session check - proceed directly with payment
    proceedWithPaymentFixed($form, $btn);
    return false;
});

function proceedWithPaymentFixed($form, $btn) {
    try {
        console.log('PAYMENT DEBUG: Starting payment processing');
        
        var currentCsrf = '';
        if (typeof currentCsrfValue === 'function') {
            currentCsrf = currentCsrfValue();
        } else if (typeof getCookie === 'function') {
            currentCsrf = getCookie('ci_csrf_token') || getCookie(CSRF_NAME || 'ci_csrf_token');
        }
        
        if (currentCsrf) {
            var csrfName = typeof CSRF_NAME !== 'undefined' ? CSRF_NAME : 'ci_csrf_token';
            var $existingCsrf = $form.find('input[name="' + csrfName + '"]');
            
            if ($existingCsrf.length) {
                $existingCsrf.val(currentCsrf);
            } else {
                $('<input type="hidden">').attr('name', csrfName).val(currentCsrf).appendTo($form);
            }
        }
        
        var $payMethod = $form.find('input[name="payment_method"]');
        if ($payMethod.length) {
            $payMethod.val('pay');
        } else {
            $('<input type="hidden" name="payment_method" value="pay">').appendTo($form);
        }
        
        var originalText = $btn.text();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing Payment...');
        
        // Use AJAX instead of form submission to handle the JSON response
        $.ajax({
            type: 'POST',
            url: $form.attr('action') || (base_url + 'studentfee/addfeegroup'),
            data: $form.serialize(),
            dataType: 'json',
            success: function(response) {
                console.log('PAYMENT DEBUG: Payment response:', response);
                
                if (response && (response.status == 1 || response.status === 1)) {
                    var successMsg = '<?php echo $this->lang->line("payment_successful") ? $this->lang->line("payment_successful") : "Payment successful! / भुक्तानी सफलतापूर्वक भयो!"; ?>';
                    var $toast = $('<div class="payment-success-toast" style="position: fixed; top: 20px; right: 20px; background: #5cb85c; color: white; padding: 15px 25px; border-radius: 5px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); z-index: 999999; font-size: 14px; font-weight: bold; min-width: 250px; text-align: center;"><i class="fa fa-check-circle" style="margin-right: 8px;"></i>' + successMsg + '</div>');
                    $('body').append($toast);
                    $('#listCollectionModal').modal('hide');
                    $('.modal-backdrop').remove();
                    if ($form.length && $form[0]) {
                      $form[0].reset();
                    }
                    $form.find('.text-danger').html('');
                    $btn.prop('disabled', false).text(originalText);
                    var nextUrl = (response.redirect && String(response.redirect).length) ? response.redirect : (window.location.href.split('#')[0]);
                    setTimeout(function () {
                      window.location.href = nextUrl;
                    }, 500);
                } else {
                    console.error('Payment failed:', response);
                    var errorMsg = response && response.message ? response.message : '<?php echo $this->lang->line("payment_failed") ? $this->lang->line("payment_failed") : "Payment failed. Please try again. / भुक्तानी असफल भयो। कृपया पुनः प्रयास गर्नुहोस्।"; ?>';
                    alert(errorMsg);
                    $btn.prop('disabled', false).text(originalText);
                }
            },
            error: function(xhr, status, error) {
                console.error('Payment AJAX error:', xhr.status, error, xhr.responseText);
                alert('Payment processing error. Please try again. / भुक्तानी प्रक्रियामा त्रुटि। कृपया पुनः प्रयास गर्नुहोस्।');
                $btn.prop('disabled', false).text(originalText);
            }
        });
        
    } catch (error) {
        console.error('PAYMENT DEBUG: Payment processing error:', error);
        $btn.prop('disabled', false).text(originalText);
        alert('Error processing payment: ' + error.message);
    }
}

function proceedWithPayment($form, $btn) {
    var currentCsrf = currentCsrfValue();
    if (currentCsrf) {
        var $csrfInput = $form.find('input[name="' + CSRF_NAME + '"]');
        if (!$csrfInput.length) {
            $('<input type="hidden">').attr('name', CSRF_NAME).val(currentCsrf).appendTo($form);
        } else {
            $csrfInput.val(currentCsrf);
        }
    }
    
    var $paymentMethod = $form.find('input[name="payment_method"]');
    if (!$paymentMethod.length) {
        $('<input type="hidden" name="payment_method" value="pay">').appendTo($form);
    } else {
        $paymentMethod.val('pay');
    }
    
    $form.off();
    $(document).off('submit.ajaxCollect');
    
    $form.attr({
        'action': '<?php echo site_url("studentfee/addfeegrp"); ?>',
        'method': 'POST',
        'target': '_self'
    });
    
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
    
    $form.get(0).submit();
}

/* =========================================================
   5) PRINT SELECTED
   ========================================================= */
$(document).on('click', '.printSelected', function () {
  var $btn = $(this), items = [];
  $("input[name='fee_checkbox']:checked").each(function () {
    items.push({
      fee_category: $(this).data('fee_category'),
      trans_fee_id: $(this).data('trans_fee_id'),
      fee_session_group_id: $(this).data('fee_session_group_id'),
      fee_master_id: $(this).data('fee_master_id'),
      fee_groups_feetype_id: $(this).data('fee_groups_feetype_id')
    });
  });
  if (!items.length) { alert("<?php echo $this->lang->line('no_record_selected'); ?>"); return; }
  $.ajax({
    url:'<?php echo site_url("studentfee/printFeesByGroupArray") ?>',
    type:'post',
    data:{ data: JSON.stringify(items) },
    beforeSend:function(){ $btn.button('loading'); },
    success:function(html){ Popup(html); },
    error:function(){ alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>"); },
    complete:function(){ $btn.button('reset'); }
  });
});

/* =========================================================
   6) OTHER EVENT HANDLERS
   ========================================================= */
$(document).on('change','#select_all', function(){ $('input:checkbox').not(this).prop('checked', this.checked); });

$(document).on('click', '.printDoc', function () {
  $.ajax({
    url:'<?php echo site_url("studentfee/printFeesByName") ?>',
    type:'post',
    dataType:"JSON",
    data:{
      fee_category: $(this).data('fee-category'),
      student_session_id: '<?php echo $student['student_session_id'] ?>',
      main_invoice: $(this).data('main_invoice'),
      sub_invoice: $(this).data('sub_invoice')
    },
    success:function (resp) { Popup(resp.page); }
  });
});

$(document).on('click', '.printInv', function () {
  var $b = $(this);
  $.ajax({
    url:'<?php echo site_url("studentfee/printFeesByGroup") ?>',
    type:'post', dataType:"JSON",
    data:{
      trans_fee_id: $b.data('trans_fee_id'),
      fee_category: $b.data('fee_category'),
      fee_groups_feetype_id: $b.data('fee_groups_feetype_id'),
      fee_master_id: $b.data('fee_master_id'),
      fee_session_group_id: $b.data('fee_session_group_id'),
      student_session_id: $b.data('student_session_id')
    },
    beforeSend:function(){ $b.button('loading'); },
    success:function (resp) { Popup(resp.page); },
    error:function(){ alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>"); },
    complete:function(){ $b.button('reset'); }
  });
});

$(document).on('click', '.getProcessingfees', function () {
  var $b = $(this), sid = '<?php echo $student["student_session_id"]; ?>';
  $.ajax({
    type:'POST', url: base_url + "studentfee/getProcessingfees/" + sid, dataType:"JSON",
    beforeSend:function(){ $b.button('loading'); },
    success:function (data) {
      $("#processing_fess_modal .modal-body").html(data.view);
      $("#processing_fess_modal").modal('show');
    },
    complete:function(){ $b.button('reset'); }
  });
});

/* =========================================================
   7) INDIVIDUAL FEE COLLECTION MODAL - FIXED
   ========================================================= */

$(document).on('click', '.myCollectFeeBtn', function (e) {
  e.preventDefault();
  e.stopImmediatePropagation();
  
  var $btn = $(this);
  var $modal = $('#myFeesModal');
  
  // Remove any existing backdrops first
  $('.modal-backdrop').remove();
  
  $('#myFeesModal .text-danger').html('');
  
  $('#student_fees_master_id').val($btn.data('student_fees_master_id') || 0);
  $('#fee_groups_feetype_id').val($btn.data('fee_groups_feetype_id') || 0);
  $('#transport_fees_id').val($btn.data('trans_fee_id') || 0);
  $('#fee_category').val($btn.data('fee-category') || 'fees');
  
  var group = $btn.data('group') || '';
  var type = $btn.data('type') || '';
  $('.fees_title').text('Collect Fees: ' + group + ' - ' + type);
  
  var _rowAmt = $btn.data('feeBalance');
  if (_rowAmt === undefined || _rowAmt === null || _rowAmt === '') {
    _rowAmt = $btn.closest('tr').find('.text-right').first().text().replace(/,/g, '').replace(/[^\d.-]/g, '');
  } else {
    _rowAmt = String(_rowAmt).replace(/,/g, '').replace(/[^\d.-]/g, '');
  }
  window.fee_amount = parseFloat(_rowAmt) || 0;
  window.fee_type_amount = window.fee_amount;
  fee_amount = window.fee_amount;
  fee_type_amount = window.fee_type_amount;

  $('#amount').val(window.fee_amount);
  $('#amount_discount').val(0).prop('readonly', false);
  $('#discount_group').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
  
  // Load discount groups for this student + fee line (filtered by applicable fee type)
  var student_session_id = $btn.data('student_session_id') || $('#std_id').val();
  var fee_groups_feetype_id = $btn.data('fee_groups_feetype_id') || $('#fee_groups_feetype_id').val();
  var fee_category = $btn.data('fee-category') || $('#fee_category').val() || 'fees';
  if (student_session_id) {
    $('#std_id').val(student_session_id);
    $.ajax({
      url: base_url + 'studentfee/getDiscountGroups',
      type: 'POST',
      dataType: 'json',
      data: {
        student_session_id: student_session_id,
        fee_groups_feetype_id: fee_groups_feetype_id,
        fee_category: fee_category
      },
      success: function(response) {
        if (response && response.status === 'success' && response.discount_not_applied) {
          var $discountSelect = $('#discount_group');
          $discountSelect.html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
          
          if (response.discount_not_applied && response.discount_not_applied.length > 0) {
            $.each(response.discount_not_applied, function(i, discount) {
              var optionText = discount.code || '';
              if (discount.type === 'percentage') {
                optionText += ' (' + (discount.percentage || 0) + '%)';
              } else {
                optionText += ' (' + (discount.amount || 0) + ')';
              }
              $discountSelect.append(
                $('<option></option>')
                  .attr('value', discount.student_fees_discount_id || discount.id)
                  .attr('data-disamount', discount.amount || 0)
                  .attr('data-type', discount.type || 'fix')
                  .attr('data-percentage', discount.percentage || 0)
                  .text(optionText)
              );
            });
          }
        }
      },
      error: function() {
        console.error('Failed to load discount groups');
      }
    });
  }
  
  // Show modal properly
  $modal.modal({
    backdrop: true,
    keyboard: true,
    show: true
  });
  
  // Force modal to be visible and clickable
  setTimeout(function() {
    $modal.css({
      'display': 'block',
      'z-index': '1050'
    });
    $('.modal-backdrop').css('z-index', '1040');
    $modal.find('.modal-content, .modal-footer').css('pointer-events', 'auto');
  }, 50);
});

$('#myFeesModal').off('shown.bs.modal').on('shown.bs.modal', function () { 
  var $modal = $(this);
  
  // FIX: Ensure modal is visible and clickable - PROPER FIX
  $modal.css({
    'display': 'block',
    'z-index': '1050'
  });
  
  // Remove ALL duplicate backdrops
  var backdropCount = $('.modal-backdrop').length;
  while (backdropCount > 1) {
    $('.modal-backdrop').last().remove();
    backdropCount = $('.modal-backdrop').length;
  }
  
  // Ensure backdrop doesn't block modal
  $('.modal-backdrop').css({
    'z-index': '1040',
    'background-color': 'rgba(0, 0, 0, 0.5)'
  });
  
  // Ensure modal content is clickable
  $modal.find('.modal-content').css({
    'pointer-events': 'auto',
    'z-index': '1052'
  });
  $modal.find('.modal-footer').css({
    'pointer-events': 'auto',
    'z-index': '1053'
  });
  $modal.find('.modal-footer button').css({
    'pointer-events': 'auto',
    'cursor': 'pointer'
  });
  
  removeBootstrapEnglishDatepickerWidgets();
  $modal.find('input[data-provide="datepicker"]').removeAttr('data-provide');
  
  // Enforce Nepali datepicker only
  enforceNepaliDateOnly($modal);
  bindNepaliDateEvents($modal);
  
  // Pre-fill today's BS date (Nepali date like 2082-08-03) - FORCE IT
  try {
    var todayBsStr = '';
    
    // Method 1: Use NepaliFunctions if available
    if (window.NepaliFunctions && typeof NepaliFunctions.GetCurrentBsDate === 'function') {
      try {
        var bs = NepaliFunctions.GetCurrentBsDate();
        if (bs && bs.year && bs.month && bs.day) {
          var month = String(bs.month).padStart(2, '0');
          var day = String(bs.day).padStart(2, '0');
          todayBsStr = bs.year + '-' + month + '-' + day;
          console.log('DEBUG: Got BS date from NepaliFunctions.GetCurrentBsDate:', todayBsStr);
        }
      } catch(e) {
        console.warn('DEBUG: GetCurrentBsDate failed:', e);
      }
    }
    
    // Method 2: Convert current AD date to BS
    if (!todayBsStr && window.NepaliFunctions && typeof NepaliFunctions.AD2BS === 'function') {
      try {
        var today = new Date();
        var bs = NepaliFunctions.AD2BS({
          year: today.getFullYear(),
          month: today.getMonth() + 1,
          day: today.getDate()
        });
        if (bs && bs.year && bs.month && bs.day) {
          var month = String(bs.month).padStart(2, '0');
          var day = String(bs.day).padStart(2, '0');
          todayBsStr = bs.year + '-' + month + '-' + day;
          console.log('DEBUG: Converted AD to BS date:', todayBsStr);
        }
      } catch(e) {
        console.warn('DEBUG: AD2BS conversion failed:', e);
      }
    }
    
    // Method 3: Try using nepaliDatePicker if available
    if (!todayBsStr && $.fn.nepaliDatePicker) {
      try {
        var $tempInput = $('<input type="text">');
        $tempInput.nepaliDatePicker({
          dateFormat: 'YYYY-MM-DD',
          ndpYear: true,
          ndpMonth: true
        });
        // Get today's date
        var today = new Date();
        var todayStr = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
        $tempInput.val(todayStr);
        var nepaliDate = $tempInput.nepaliDatePicker('getNepaliDate');
        if (nepaliDate && nepaliDate.year && nepaliDate.month && nepaliDate.day) {
          var month = String(nepaliDate.month).padStart(2, '0');
          var day = String(nepaliDate.day).padStart(2, '0');
          todayBsStr = nepaliDate.year + '-' + month + '-' + day;
          console.log('DEBUG: Got BS date from nepaliDatePicker:', todayBsStr);
        }
        $tempInput.remove();
      } catch(e) {
        console.warn('DEBUG: nepaliDatePicker method failed:', e);
      }
    }
    
    // Set the date field value - FORCE IT
    if (todayBsStr) {
      var $dateField = $modal.find('#date');
      if ($dateField.length) {
        $dateField.val(todayBsStr);
        // Trigger change to ensure datepicker recognizes it
        setTimeout(function() {
          $dateField.trigger('change');
        }, 100);
        console.log('DEBUG: Pre-filled date field with BS date:', todayBsStr);
      }
    } else {
      console.warn('DEBUG: Could not get today\'s BS date - all methods failed');
    }
  } catch(e) {
    console.warn('DEBUG: Could not pre-fill BS date:', e);
  }
  
  // Fix Nepali calendar positioning - ensure it appears inside modal
  setTimeout(function() {
    var $calendar = $('#ndp-nepali-box');
    if ($calendar.length) {
      var $dateField = $modal.find('#date');
      if ($dateField.length && $dateField.is(':visible')) {
        var inputRect = $dateField[0].getBoundingClientRect();
        var topPos = inputRect.top + inputRect.height + 2;
        var leftPos = inputRect.left;
        var calendarWidth = $calendar.outerWidth() || 280;
        var windowWidth = $(window).width();
        
        if (leftPos + calendarWidth > windowWidth) {
          leftPos = windowWidth - calendarWidth - 10;
        }
        
        $calendar.css({
          'position': 'fixed',
          'top': topPos + 'px',
          'left': leftPos + 'px',
          'z-index': '999999'
        });
      }
    }
  }, 200);
  
  // Focus on amount field
  setTimeout(function() {
    $('#amount').focus();
  }, 100);
});

// Fix modal cleanup on close
$('#myFeesModal').off('hidden.bs.modal').on('hidden.bs.modal', function () {
  removeBootstrapEnglishDatepickerWidgets();
  $('#ndp-nepali-box').remove();
  // Remove backdrop if stuck
  $('.modal-backdrop').remove();
  // Reset form (safely check if form exists)
  var $form = $(this).find('form');
  if ($form.length && $form[0]) {
    $form[0].reset();
  }
  $(this).find('.text-danger').html('');
});

$(document).on('click', '.save_button', function () {
  var $b = $(this), action = $b.data('action');
  var originalText = $b.html();
  var $modal = $('#myFeesModal');
  
  // Disable all buttons to prevent double submission
  $modal.find('.save_button').prop('disabled', true);
  $b.html('<i class="fa fa-spinner fa-spin"></i> <?php echo $this->lang->line("processing"); ?>');
  
  // Add processing overlay message
  var $processingOverlay = $('<div class="payment-processing-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 999999; display: flex; align-items: center; justify-content: center; flex-direction: column;"><div style="background: white; padding: 30px 50px; border-radius: 8px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.3);"><i class="fa fa-spinner fa-spin" style="font-size: 40px; color: #007bff; margin-bottom: 15px;"></i><h4 style="margin: 0; color: #333;"><?php echo $this->lang->line("payment_processing") ? $this->lang->line("payment_processing") : "Payment is processing... / भुक्तानी प्रक्रियामा छ..."; ?></h4><p style="margin: 10px 0 0 0; color: #666; font-size: 14px;"><?php echo $this->lang->line("please_wait") ? $this->lang->line("please_wait") : "Please wait... / कृपया प्रतीक्षा गर्नुहोस्..."; ?></p></div></div>');
  $('body').append($processingOverlay);
  
  // Clear previous errors
  $modal.find('.text-danger').html('');
  
  $.ajax({
    url:'<?php echo site_url("studentfee/addstudentfee") ?>',
    type:'post', 
    dataType:'json',
    timeout: 30000, // Reduced to 30 seconds (should be fast now)
    data:{
      action: action,
      student_session_id: $('#std_id').val(),
      date: $('#date').val(),
      type: $('#feetype_').val(),
      amount: $('#amount').val(),
      amount_discount: $('#amount_discount').val(),
      amount_fine: $('#amount_fine').val(),
      description: $('#description').val(),
      student_fees_master_id: $('#student_fees_master_id').val(),
      fee_groups_feetype_id: $('#fee_groups_feetype_id').val(),
      fee_category: $('#fee_category').val(),
      transport_fees_id: $('#transport_fees_id').val(),
      payment_mode: $('input[name="payment_mode_fee"]:checked').val(),
      guardian_phone: $('#guardian_phone').val(),
      guardian_email: $('#guardian_email').val(),
      student_fees_discount_id: $('#discount_group').val(),
      parent_app_key: $('#parent_app_key').val()
    },
    success:function (resp) {
      // Remove processing overlay immediately
      $('.payment-processing-overlay').remove();
      
      // Debug: Log the response to see what we're getting
      console.log('Payment response received:', resp);
      console.log('Response type:', typeof resp);
      console.log('Response status:', resp && resp.status);
      
      // Robust status check - handle various response formats
      var status = resp && resp.status;
      var isSuccess = false;
      
      // Check for success in multiple formats
      if (status === "success" || status === 1 || status === "1" || 
          status === "Success" || status === "SUCCESS" ||
          (typeof status === 'string' && status.toLowerCase() === 'success')) {
        isSuccess = true;
      }
      
      // Also check if response itself indicates success (for backwards compatibility)
      if (!isSuccess && resp && (resp.success === true || resp.success === "true" || resp.success === 1)) {
        isSuccess = true;
      }
      
      // Debug: Log success determination
      console.log('Is success?', isSuccess);
      
      if (isSuccess) {
        // Show success message (non-blocking toast notification)
        var successMsg = resp.message || '<?php echo $this->lang->line("payment_successful") ? $this->lang->line("payment_successful") : "Payment successful! / भुक्तानी सफलतापूर्वक भयो!"; ?>';
        
        // If processing in background, show that message
        if (resp.processing_background) {
          successMsg = '<?php echo $this->lang->line("payment_successful") ? $this->lang->line("payment_successful") : "Payment successful! / भुक्तानी सफलतापूर्वक भयो!"; ?>';
          successMsg += '<br><small style="opacity: 0.9;">Email/SMS sending in background...</small>';
        }
        
        // Create and show success toast notification (auto-dismisses)
        var $toast = $('<div class="payment-success-toast" style="position: fixed; top: 20px; right: 20px; background: #5cb85c; color: white; padding: 15px 25px; border-radius: 5px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); z-index: 999999; font-size: 14px; font-weight: bold; min-width: 250px; text-align: center;"><i class="fa fa-check-circle" style="margin-right: 8px;"></i>' + successMsg + '</div>');
        $('body').append($toast);
        
        // Close modal immediately (no manual OK click needed)
        $modal.modal('hide');
        $('.modal-backdrop').remove();
        
        // Reset form (safely check if form exists)
        var $modalForm = $modal.find('form');
        if ($modalForm.length && $modalForm[0]) {
          $modalForm[0].reset();
        }
        $modal.find('.text-danger').html('');
        
        // Re-enable buttons
        $modal.find('.save_button').prop('disabled', false);
        $b.html(originalText);

        if (action === "print" && resp.print && resp.print.trim() !== '') {
          Popup(resp.print, true);
        }

        setTimeout(function() {
          window.location.reload();
        }, 900);

        return;
      } else if (status === "fail" || status === 0 || status === "0" || status === "Fail" || status === "FAIL") {
        $modal.find('.save_button').prop('disabled', false);
        $b.html(originalText);
        if (resp.error) {
          $.each(resp.error, function (i, v) { 
            var $errorField = $('#' + i + '_error');
            if ($errorField.length) {
              $errorField.html(v);
            } else {
              $('#' + i.replace('_', '') + '_error').html(v);
            }
          });
        } else {
          // Show error toast instead of alert
          var errorMsg = "<?php echo $this->lang->line('error_occurred_please_try_again'); ?>";
          var $errorToast = $('<div class="payment-error-toast" style="position: fixed; top: 20px; right: 20px; background: #d9534f; color: white; padding: 15px 25px; border-radius: 5px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); z-index: 999999; font-size: 14px; font-weight: bold; min-width: 250px; text-align: center;"><i class="fa fa-exclamation-circle" style="margin-right: 8px;"></i>' + errorMsg + '</div>');
          $('body').append($errorToast);
          setTimeout(function() {
            $errorToast.fadeOut(300, function() {
              $(this).remove();
            });
          }, 3000);
        }
      } else {
        $modal.find('.save_button').prop('disabled', false);
        $b.html(originalText);
        console.error('Unexpected response:', resp);
        // Show error toast instead of alert
        var errorMsg = "<?php echo $this->lang->line('error_occurred_please_try_again'); ?>";
        var $errorToast = $('<div class="payment-error-toast" style="position: fixed; top: 20px; right: 20px; background: #d9534f; color: white; padding: 15px 25px; border-radius: 5px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); z-index: 999999; font-size: 14px; font-weight: bold; min-width: 250px; text-align: center;"><i class="fa fa-exclamation-circle" style="margin-right: 8px;"></i>' + errorMsg + '</div>');
        $('body').append($errorToast);
        setTimeout(function() {
          $errorToast.fadeOut(300, function() {
            $(this).remove();
          });
        }, 3000);
      }
    },
    error:function(xhr, status, error){ 
      // Remove processing overlay
      $('.payment-processing-overlay').remove();
      
      console.error('Payment error:', xhr, status, error, xhr.responseText);
      
      // If timeout, try to parse response anyway (might have succeeded)
      if (status === 'timeout' || xhr.status === 0) {
        // Try to parse response - payment might have succeeded
        try {
          var timeoutResp = JSON.parse(xhr.responseText || '{}');
          if (timeoutResp && (timeoutResp.status === "success" || timeoutResp.status === 1 || timeoutResp.status === "1")) {
            // Payment actually succeeded - show toast and close modal
            var successMsg = '<?php echo $this->lang->line("payment_successful") ? $this->lang->line("payment_successful") : "Payment successful! / भुक्तानी सफलतापूर्वक भयो!"; ?>';
            var $toast = $('<div class="payment-success-toast" style="position: fixed; top: 20px; right: 20px; background: #5cb85c; color: white; padding: 15px 25px; border-radius: 5px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); z-index: 999999; font-size: 14px; font-weight: bold; min-width: 250px; text-align: center;"><i class="fa fa-check-circle" style="margin-right: 8px;"></i>' + successMsg + '</div>');
            $('body').append($toast);
            setTimeout(function() {
              $toast.fadeOut(300, function() {
                $(this).remove();
              });
            }, 3000);
            $modal.modal('hide');
            $('.modal-backdrop').remove();
            // Reset form (safely check if form exists)
            var $timeoutForm = $modal.find('form');
            if ($timeoutForm.length && $timeoutForm[0]) {
              $timeoutForm[0].reset();
            }
            $modal.find('.text-danger').html('');
            window.location.reload();
            return;
          }
        } catch(e) {
          // Could not parse response
        }
        // If we get here, payment likely failed - show error toast instead of alert
        var errorMsg = "<?php echo $this->lang->line('error_occurred_please_try_again'); ?>";
        var $errorToast = $('<div class="payment-error-toast" style="position: fixed; top: 20px; right: 20px; background: #d9534f; color: white; padding: 15px 25px; border-radius: 5px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); z-index: 999999; font-size: 14px; font-weight: bold; min-width: 250px; text-align: center;"><i class="fa fa-exclamation-circle" style="margin-right: 8px;"></i>' + errorMsg + '</div>');
        $('body').append($errorToast);
        setTimeout(function() {
          $errorToast.fadeOut(300, function() {
            $(this).remove();
          });
        }, 3000);
        $modal.find('.save_button').prop('disabled', false);
        $b.html(originalText);
        return;
      }
      
      $modal.find('.save_button').prop('disabled', false);
      $b.html(originalText);
      
      var errorMsg = "<?php echo $this->lang->line('error_occurred_please_try_again'); ?>";
      try {
        var resp = JSON.parse(xhr.responseText);
        if (resp && resp.error) {
          if (typeof resp.error === 'object') {
            $.each(resp.error, function (i, v) { 
              var $errorField = $('#' + i + '_error');
              if ($errorField.length) {
                $errorField.html(v);
              }
            });
            return;
          } else {
            errorMsg = resp.error;
          }
        }
      } catch(e) {
        // Not JSON, use default message
      }
      
      alert(errorMsg);
    }
  });
});

/* =========================================================
   8) REVERT / DISCOUNT DELETE
   ========================================================= */
// Paid fee (invoice) revert — manual open only (no data-toggle: BS toggle + backdrop strip + show() early-return left modal broken)
$(document).on('click', '.js-open-invoice-revert-modal', function (e) {
  e.preventDefault();
  e.stopPropagation();
  var $b = $(e.target).closest('.js-open-invoice-revert-modal');
  var $m = $('#confirm-delete');
  $('.invoice_no', $m).text($b.attr('data-invoiceno') || '');
  $('#main_invoice', $m).val($b.attr('data-main_invoice') || '');
  $('#sub_invoice', $m).val($b.attr('data-sub_invoice') || '');
  $m.removeClass('fade').appendTo('body').css({display:'block'});
  $('.modal-backdrop').remove();
  $m.modal({backdrop:'static', keyboard:false, show:true});
});

// Discount revert: do NOT use data-toggle (pre-init + BS data-api "toggle" can flash open/close). Same manual path as invoice.
$(document).on('click', '.js-open-discount-revert-modal', function (e) {
  e.preventDefault();
  e.stopPropagation();
  var $b = $(e.target).closest('.js-open-discount-revert-modal');
  var $m = $('#confirm-discountdelete');
  $('.discount_title', $m).text($b.data('discounttitle') || '');
  $('#discount_id', $m).val($b.data('discountid') || '');
  $m.removeClass('fade').appendTo('body').css({display:'block'});
  $('.modal-backdrop').remove();
  $m.modal({backdrop:'static', keyboard:false, show:true});
});

$('#confirm-delete').on('show.bs.modal', function (e) {
  if (e.relatedTarget) {
    $('.invoice_no', this).text($(e.relatedTarget).data('invoiceno'));
    $('#main_invoice', this).val($(e.relatedTarget).data('main_invoice'));
    $('#sub_invoice', this).val($(e.relatedTarget).data('sub_invoice'));
  }
});

$('#confirm-discountdelete').on('show.bs.modal', function (e) {
  if (e.relatedTarget) {
    $('.discount_title', this).text($(e.relatedTarget).data('discounttitle'));
    $('#discount_id', this).val($(e.relatedTarget).data('discountid'));
  }
});

$('#confirm-delete').on('click', '.btn-ok', function () {
  $.post('<?php echo site_url("studentfee/deleteFee") ?>',
    { main_invoice: $('#main_invoice').val(), sub_invoice: $('#sub_invoice').val() },
    function(){ $('#confirm-delete').modal('hide'); location.reload(true); }, 'json'
  );
});

$('#confirm-discountdelete').on('click', '.btn-discountdel', function () {
  $.post('<?php echo site_url("studentfee/deleteStudentDiscount") ?>',
    { discount_id: $('#discount_id').val() },
    function(){ $('#confirm-discountdelete').modal('hide'); location.reload(true); }, 'json'
  );
});

/* =========================================================
   9) DISCOUNT APPLY + DROPDOWN CALC
   ========================================================= */
$(".applydiscount").click(function () {
  $("span[id$='_error']").html("");
  $('#student_fees_discount_id').val($(this).data("student_fees_discount_id"));
  $('.discount_title').html("<b>" + $(this).data("modal_title") + "</b>");
  $('#myDisApplyModal').modal({ backdrop:'static', keyboard:false, show:true });
});

$(document).on('click', '.dis_apply_button', function () {
  var $b = $(this); $b.button('loading');
  $.post('<?php echo site_url("admin/feediscount/applydiscount") ?>', {
    discount_payment_id: $('#discount_payment_id').val(),
    student_fees_discount_id: $('#student_fees_discount_id').val(),
    dis_description: $('#dis_description').val()
  }, function(resp){
    $b.button('reset');
    if (resp.status === "success") location.reload(true);
    else if (resp.status === "fail") $.each(resp.error, function (i,v){ $('#' + i + '_error').html(v); });
  }, 'json');
});

function parseMoneyInput(v) {
  if (v === undefined || v === null || v === '') return 0;
  return parseFloat(String(v).replace(/,/g, '')) || 0;
}

$(document).on('change', "#discount_group", function () {
  var $opt = $('option:selected', this);
  var $modal = $('div#myFeesModal');
  var $amt = $modal.find('#amount');
  var $dis = $modal.find('#amount_discount');
  var fee = parseFloat(window.fee_amount) || 0;
  var base = parseFloat(window.fee_type_amount) || fee;

  if (!$opt.val()) {
    $amt.val(fee);
    $dis.val(0).prop('readonly', false);
    return;
  }

  var typeRaw = ($opt.attr('data-type') || 'fix').toString().toLowerCase().trim();
  var disAmt = parseMoneyInput($opt.attr('data-disamount'));
  var pct = parseMoneyInput($opt.attr('data-percentage'));

  if (typeRaw === 'percentage') {
    var per_amount = (base * pct) / 100;
    if (per_amount > fee) per_amount = fee;
    $dis.val(per_amount.toFixed(2)).prop('readonly', true);
    $amt.val(Math.max(0, fee - per_amount).toFixed(2));
  } else {
    var fixDisc = disAmt;
    if (fixDisc > fee) fixDisc = fee;
    $dis.val(fixDisc.toFixed(2)).prop('readonly', true);
    $amt.val(Math.max(0, fee - fixDisc).toFixed(2));
  }
});

/* =========================================================
   10) ADDITIONAL SAFEGUARDS - ADDED
   ========================================================= */

// English calendar cleanup only (do not strip Nepali #ndp-nepali-box / inner .datepicker)
setInterval(function() {
  var removed = removeBootstrapEnglishDatepickerWidgets();
  if (removed > 0) {
    console.log('DEBUG: Global cleanup removed ' + removed + ' English calendar widgets');
  }
}, 1000);

console.log('Fixed student fees JavaScript loaded successfully');

$('#listCollectionModal').off('shown.bs.modal').on('shown.bs.modal', function() {
  var $modal = $(this);
  
  // FIX: Ensure modal is visible and clickable - SAME AS myFeesModal
  $modal.css({
    'display': 'block',
    'z-index': '1050'
  });
  
  // Remove ALL duplicate backdrops
  var backdropCount = $('.modal-backdrop').length;
  while (backdropCount > 1) {
    $('.modal-backdrop').last().remove();
    backdropCount = $('.modal-backdrop').length;
  }
  
  // Ensure backdrop doesn't block modal
  $('.modal-backdrop').css({
    'z-index': '1040',
    'background-color': 'rgba(0, 0, 0, 0.5)'
  });
  
  // Ensure modal content is clickable
  $modal.find('.modal-content').css({
    'pointer-events': 'auto',
    'z-index': '1052'
  });
  $modal.find('.modal-footer').css({
    'pointer-events': 'auto',
    'z-index': '1053'
  });
  $modal.find('.modal-footer button').css({
    'pointer-events': 'auto',
    'cursor': 'pointer'
  });
  
  removeBootstrapEnglishDatepickerWidgets();
  $modal.find('input[data-provide="datepicker"]').removeAttr('data-provide');
  
  // Always (re)initialize Nepali date logic after content is ready
  setTimeout(function() {
    applyAddFeesDateLogicToGroupModal();
  }, 200);
});

// Minimal handler: just remove English calendars when focusing Nepali inputs
$(document).off('focus.removeEnglish click.removeEnglish', '#listCollectionModal input.nepali-datepicker, #listCollectionModal input.nepali-date-picker, #listCollectionModal input.date_fee, #listCollectionModal input[name="collected_date"]');
$(document).on('focus.removeEnglish click.removeEnglish', '#listCollectionModal input.nepali-datepicker, #listCollectionModal input.nepali-date-picker, #listCollectionModal input.date_fee, #listCollectionModal input[name="collected_date"]', function () {
  removeBootstrapEnglishDatepickerWidgets();
});

</script>
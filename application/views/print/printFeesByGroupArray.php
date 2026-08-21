<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat(); ?>
<style type="text/css">
    .page-break { display: block; page-break-before: always; }
    table { border-collapse: collapse !important; }
    table th, table td { border: 1px solid #000 !important; }
    @media print {
        .page-break { display: block; page-break-before: always; }
        table { border-collapse: collapse !important; }
        table th, table td { border: 1px solid #000 !important; }
        .col-sm-1, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-sm-10, .col-sm-11, .col-sm-12 {
            float: left;
        }
        .col-sm-12 {
            width: 100%;
        }
        .col-sm-11 {
            width: 91.66666667%;
        }
        .col-sm-10 {
            width: 83.33333333%;
        }
        .col-sm-9 {
            width: 75%;
        }
        .col-sm-8 {
            width: 66.66666667%;
        }
        .col-sm-7 {
            width: 58.33333333%;
        }
        .col-sm-6 {
            width: 50%;
        }
        .col-sm-5 {
            width: 41.66666667%;
        }
        .col-sm-4 {
            width: 33.33333333%;
        }
        .col-sm-3 {
            width: 25%;
        }
        .col-sm-2 {
            width: 16.66666667%;
        }
        .col-sm-1 {
            width: 8.33333333%;
        }
        .col-sm-pull-12 {
            right: 100%;
        }
        .col-sm-pull-11 {
            right: 91.66666667%;
        }
        .col-sm-pull-10 {
            right: 83.33333333%;
        }
        .col-sm-pull-9 {
            right: 75%;
        }
        .col-sm-pull-8 {
            right: 66.66666667%;
        }
        .col-sm-pull-7 {
            right: 58.33333333%;
        }
        .col-sm-pull-6 {
            right: 50%;
        }
        .col-sm-pull-5 {
            right: 41.66666667%;
        }
        .col-sm-pull-4 {
            right: 33.33333333%;
        }
        .col-sm-pull-3 {
            right: 25%;
        }
        .col-sm-pull-2 {
            right: 16.66666667%;
        }
        .col-sm-pull-1 {
            right: 8.33333333%;
        }
        .col-sm-pull-0 {
            right: auto;
        }
        .col-sm-push-12 {
            left: 100%;
        }
        .col-sm-push-11 {
            left: 91.66666667%;
        }
        .col-sm-push-10 {
            left: 83.33333333%;
        }
        .col-sm-push-9 {
            left: 75%;
        }
        .col-sm-push-8 {
            left: 66.66666667%;
        }
        .col-sm-push-7 {
            left: 58.33333333%;
        }
        .col-sm-push-6 {
            left: 50%;
        }
        .col-sm-push-5 {
            left: 41.66666667%;
        }
        .col-sm-push-4 {
            left: 33.33333333%;
        }
        .col-sm-push-3 {
            left: 25%;
        }
        .col-sm-push-2 {
            left: 16.66666667%;
        }
        .col-sm-push-1 {
            left: 8.33333333%;
        }
        .col-sm-push-0 {
            left: auto;
        }
        .col-sm-offset-12 {
            margin-left: 100%;
        }
        .col-sm-offset-11 {
            margin-left: 91.66666667%;
        }
        .col-sm-offset-10 {
            margin-left: 83.33333333%;
        }
        .col-sm-offset-9 {
            margin-left: 75%;
        }
        .col-sm-offset-8 {
            margin-left: 66.66666667%;
        }
        .col-sm-offset-7 {
            margin-left: 58.33333333%;
        }
        .col-sm-offset-6 {
            margin-left: 50%;
        }
        .col-sm-offset-5 {
            margin-left: 41.66666667%;
        }
        .col-sm-offset-4 {
            margin-left: 33.33333333%;
        }
        .col-sm-offset-3 {
            margin-left: 25%;
        }
        .col-sm-offset-2 {
            margin-left: 16.66666667%;
        }
        .col-sm-offset-1 {
            margin-left: 8.33333333%;
        }
        .col-sm-offset-0 {
            margin-left: 0%;
        }
        .visible-xs {
            display: none !important;
        }
        .hidden-xs {
            display: block !important;
        }
        table.hidden-xs {
            display: table;
        }
        tr.hidden-xs {
            display: table-row !important;
        }
        th.hidden-xs,
        td.hidden-xs {
            display: table-cell !important;
        }
        .hidden-xs.hidden-print {
            display: none !important;
        }
        .hidden-sm {
            display: none !important;
        }
        .visible-sm {
            display: block !important;
        }
        table.visible-sm {
            display: table;
        }
        tr.visible-sm {
            display: table-row !important;
        }
        th.visible-sm,
        td.visible-sm {
            display: table-cell !important;
        }
    }
</style>

<html lang="en">
    <head>
        <title><?php echo $this->lang->line('fees_receipt'); ?></title>
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/bootstrap/css/bootstrap.min.css"> 
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/AdminLTE.min.css">
        <script src="https://unpkg.com/@sajanm/nepali-date-picker@4.0.8/dist/nepali.datepicker.v4.0.8.min.js"></script>
        <script src="<?php echo base_url(); ?>backend/js/nepali-datepicker-global.js"></script>
    </head>
    <body>     
         <?php 
            $print_copy=explode(',', $sch_setting->is_duplicate_fees_invoice);
// Always include office copy (0) and student copy (1) for printing
if (!in_array('0', $print_copy)) {
    $print_copy[] = '0';
}
if (!in_array('1', $print_copy)) {
    $print_copy[] = '1';
}
         ?>

        <div class="container"> 
             <?php 
if(in_array('0', $print_copy)){
    ?>
    <div class="row">
                <div id="content" class="col-lg-12 col-sm-12 ">
                    <div class="invoice">
                        <div class="row header ">
                            <div class="col-sm-12">
                                <?php
                                if (!empty($print_logo_base64)) {
                                    ?>
                                    <img src="<?php echo $print_logo_base64; ?>" style="height: 100px;max-width: 200px;object-fit: contain;">
                                    <?php
                                } elseif (!empty($print_logo_url)) {
                                    ?>
                                    <img src="<?php echo $print_logo_url; ?>" style="height: 100px;max-width: 200px;object-fit: contain;">
                                    <?php
                                } else {
                                    ?>
                                    <img  src="<?php echo $this->media_storage->getImageURL('/uploads/print_headerfooter/student_receipt/'.$this->setting_model->get_receiptheader()); ?>" style="height: 100px;width: 100%;">
                                    <?php
                                }
                                ?>
                            </div>

                        </div> 
                        
                            <div class="row">
                                <div class="col-md-12 text text-center">
                                    <?php echo $this->lang->line('office_copy'); ?>
                                </div>
                            </div>
                        
                        <div class="row">                           
                            <div class="col-xs-6 text-left">
                                <br/>
                                   <address>
                                        <strong>
                                            <?php  echo $this->customlib->getFullName($feearray[0]->firstname, $feearray[0]->middlename,$feearray[0]->lastname,$sch_setting->middlename,$sch_setting->lastname);  ?></strong><?php echo " (".$feearray[0]->admission_no.")"; ?> <br>

                                        <?php echo $this->lang->line('father_name'); ?>: <?php echo $feearray[0]->father_name; ?><br>
                                        <?php echo $this->lang->line('class'); ?>: <?php echo $feearray[0]->class . " (" . $feearray[0]->section . ")"; ?>
                                    </address>
                            </div>
                            <div class="col-xs-6 text-right">
                                <br/>
                                <address>
                                    <strong>Date :<span class="print-date-nepali"><?php
                                        // Get today's date and convert to Nepali format
                                        $today = date('Y-m-d');
                                        $ad_year = (int)date('Y', strtotime($today));
                                        $ad_month = (int)date('m', strtotime($today));
                                        $ad_day = (int)date('d', strtotime($today));
                                        
                                        // Approximate conversion: AD year + 57 = BS year
                                        // For more accurate conversion, a proper Nepali date library would be needed
                                        $bs_year = $ad_year + 57;
                                        $bs_month = $ad_month;
                                        $bs_day = $ad_day;
                                        
                                        // Format as DD-MM-YYYY (Nepali format)
                                        echo sprintf('%02d-%02d-%04d', $bs_day, $bs_month, $bs_year);
                                        ?></span></strong><br/>

                                </address>                               
                            </div>
                        </div>
                        <hr style="margin-top: 0px;margin-bottom: 0px;" />
                        <div class="row">
                         <?php
                                if (!empty($feearray)) {
                                    ?>

                                    <table class="table table-striped table-responsive" style="font-size: 8pt; border-collapse: collapse; border: 1px solid #000;">
                                        <thead>
                                        <th style="border: 1px solid #000;"><?php echo $this->lang->line('fees_group'); ?></th>
                                        <th class="text text-right" style="border: 1px solid #000;"><?php echo $this->lang->line('amount'); ?></th>
                                        <th style="border: 1px solid #000;"><?php echo $this->lang->line('date'); ?></th>
                                        <th class="text text-right" style="border: 1px solid #000;"><?php echo $this->lang->line('paid'); ?></th>
                                        <th class="text text-right" style="border: 1px solid #000;"><?php echo $this->lang->line('fine'); ?></th>
                                        <th class="text text-right" style="border: 1px solid #000;"><?php echo $this->lang->line('discount'); ?></th>
                                        <th class="text text-right" style="border: 1px solid #000;"><?php echo $this->lang->line('balance'); ?></th>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $total_amount = 0;
                                            $total_deposite_amount = 0;
                                            $total_fine_amount = 0;
                                            $total_discount_amount = 0;
                                            $total_balance_amount = 0;
                                            $alot_fee_discount = 0;
                                            if (empty($feearray)) {
                                                ?>
                                                <tr>
                                                    <td colspan="7" class="text-danger text-center" style="border: 1px solid #000;">
                                                        <?php echo $this->lang->line('no_transaction_found'); ?>
                                                    </td>
                                                </tr>
                                                <?php
                                            } else {
                                                foreach ($feearray as $fee_key => $feeList) {
                                                   if($feeList->fee_category == "fees"){

                                                    if ($feeList->is_system) {
                                                        $feeList->amount = $feeList->student_fees_master_amount;
                                                    }

                                                    $fee_discount = 0;
                                                    $fee_paid = 0;
                                                    $fee_fine = 0;
                                                    if (!empty($feeList->amount_detail)) {
                                                        $fee_deposits = json_decode(($feeList->amount_detail));

                                                        foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                                            $fee_paid = $fee_paid + $fee_deposits_value->amount;
                                                            $fee_discount = $fee_discount + $fee_deposits_value->amount_discount;
                                                            $fee_fine = $fee_fine + $fee_deposits_value->amount_fine;
                                                        }
                                                    }
                                                    $feetype_balance = $feeList->amount - ($fee_paid + $fee_discount);
                                                    $total_amount = $total_amount + $feeList->amount;
                                                    $total_discount_amount = $total_discount_amount + $fee_discount;
                                                    $total_fine_amount = $total_fine_amount + $fee_fine;
                                                    $total_deposite_amount = $total_deposite_amount + $fee_paid;
                                                    $total_balance_amount = $total_balance_amount + $feetype_balance;
                                                    ?>
                                                    <tr  class="dark-gray">

                                                        <td style="border: 1px solid #000;"> 
                                                            <?php
                                                            if ($feeList->is_system) {
                                                                echo $this->lang->line($feeList->name) . " (" . $this->lang->line($feeList->type) . ")";
                                                            } else {
                                                                echo $feeList->name . " (" . $feeList->type . ")";
                                                            }
                                                            ?>
                                                        </td>
                                                        <td class="text text-right" style="border: 1px solid #000;"><?php echo $currency_symbol . amountFormat($feeList->amount); 

      if (($feeList->due_date != "0000-00-00" && $feeList->due_date != null) && (strtotime($feeList->due_date) < strtotime(date('Y-m-d')))) {
            ?>
<span data-toggle="popover" class="text text-danger detail_popover"><?php echo " + " . $currency_symbol .amountFormat($feeList->fine_amount); ?></span>

<div class="fee_detail_popover" style="display: none">
    <?php
if ($feeList->fine_amount != "") {
                ?>
        <p class="text text-danger"><?php echo $this->lang->line('fine'); ?></p>
        <?php
}
            ?>
</div>
    <?php
}

                                                        ?></td>

                                                        <td colspan="1" style="border: 1px solid #000;"></td>
                                                        <td class="text text-right" style="border: 1px solid #000;"><?php
                                                            echo $currency_symbol . amountFormat($fee_paid);
                                                            ?></td>
                                                        <td class="text text-right" style="border: 1px solid #000;"><?php
                                                            echo $currency_symbol . amountFormat($fee_fine);
                                                            ?></td>
                                                        <td class="text text-right" style="border: 1px solid #000;"><?php
                                                            echo $currency_symbol . amountFormat($fee_discount);
                                                            ?></td>
                                                        <td class="text text-right" style="border: 1px solid #000;"><?php
                                                            $display_none = "ss-none";
                                                            if ($feetype_balance > 0) {
                                                                $display_none = "";


                                                                echo $currency_symbol . amountFormat($feetype_balance);
                                                            }
                                                            ?>

                                                        </td>
                                                    </tr>

                                                    <?php
                                                    $fee_deposits = json_decode(($feeList->amount_detail));
                                                    if (is_object($fee_deposits)) {
                                                        foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                                            ?>
                                                            <tr class="white-td">
                                                                <td colspan="1" class="text-right" style="border: 1px solid #000;"><img src="<?php echo $this->media_storage->getImageURL('backend/images/table-arrow.png');?>" alt="" /></td>
                                                                <td class="text text-center" style="border: 1px solid #000;">
                                                                    <?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($fee_deposits_value->date)); ?>
                                                                </td>
                                                                <td class="text text-right" style="border: 1px solid #000;"><?php echo $currency_symbol . amountFormat($fee_deposits_value->amount); ?></td>
                                                                <td class="text text-right" style="border: 1px solid #000;"><?php echo $currency_symbol . amountFormat($fee_deposits_value->amount_fine); ?></td>
                                                                <td class="text text-right" style="border: 1px solid #000;"><?php echo $currency_symbol . amountFormat($fee_deposits_value->amount_discount); ?></td>
                                                                <td style="border: 1px solid #000;"></td>

                                                            </tr>
                                                            <?php
                                                        }
                                                    }
                                                }elseif($feeList->fee_category == "transport"){
                                                
                                                    $fee_discount = 0;
                                                    $fee_paid = 0;
                                                    $fee_fine = 0;
                                                    if (!empty($feeList->amount_detail)) {
                                                        $fee_deposits = json_decode(($feeList->amount_detail));

                                                        foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                                            $fee_paid = $fee_paid + $fee_deposits_value->amount;
                                                            $fee_discount = $fee_discount + $fee_deposits_value->amount_discount;
                                                            $fee_fine = $fee_fine + $fee_deposits_value->amount_fine;
                                                        }
                                                    }
                                                    $feetype_balance = $feeList->fees - ($fee_paid + $fee_discount);
                                                    $total_amount = $total_amount + $feeList->fees;
                                                    $total_discount_amount = $total_discount_amount + $fee_discount;
                                                    $total_fine_amount = $total_fine_amount + $fee_fine;
                                                    $total_deposite_amount = $total_deposite_amount + $fee_paid;
                                                    $total_balance_amount = $total_balance_amount + $feetype_balance;
                                                    ?>
                                                    <tr  class="dark-gray">

                                                        <td style="border: 1px solid #000;"><?php
                                                            echo $this->lang->line("transport_fees");
                                                            ?></td>
                                                                  <td class="text text-right" style="border: 1px solid #000;"><?php 

                                                echo $currency_symbol . $feeList->fees;


    if (($feeList->due_date != "0000-00-00" && $feeList->due_date != null) && (strtotime($feeList->due_date) < strtotime(date('Y-m-d')))) {
            $tr_fine_amount = $feeList->fine_amount;
            if ($feeList->fine_type != "" && $feeList->fine_type == "percentage") {

                $tr_fine_amount = percentageAmount($feeList->fees, $feeList->fine_percentage);
            }
            ?>

<span  class="text text-danger"><?php echo " + " . $currency_symbol.amountFormat($tr_fine_amount); ?></span>

    <?php
}

                                                ?>
                                            </td>

                                                        <td colspan="1" style="border: 1px solid #000;"></td>
                                                        <td class="text text-right" style="border: 1px solid #000;"><?php
                                                            echo $currency_symbol . amountFormat($fee_paid);
                                                            ?></td>
                                                        <td class="text text-right" style="border: 1px solid #000;"><?php
                                                            echo $currency_symbol . amountFormat($fee_fine);
                                                            ?></td>
                                                        <td class="text text-right" style="border: 1px solid #000;"><?php
                                                            echo $currency_symbol . amountFormat($fee_discount);
                                                            ?></td>
                                                        <td class="text text-right" style="border: 1px solid #000;"><?php
                                                            $display_none = "ss-none";
                                                            if ($feetype_balance > 0) {
                                                                $display_none = "";

                                                                echo $currency_symbol . amountFormat($feetype_balance);
                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>

                                                    <?php
                                                    $fee_deposits = json_decode(($feeList->amount_detail));
                                                    if (is_object($fee_deposits)) {
                                                        foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                                            ?>
                                                            <tr class="white-td">
                                                                <td colspan="1" class="text-right" style="border: 1px solid #000;"><img src="<?php echo $this->media_storage->getImageURL('backend/images/table-arrow.png');?>" alt="" /></td>
                                                                <td class="text text-center" style="border: 1px solid #000;">
                                                                    <?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($fee_deposits_value->date)); ?>
                                                                </td>
                                                                <td class="text text-right" style="border: 1px solid #000;"><?php echo $currency_symbol . amountFormat($fee_deposits_value->amount); ?></td>
                                                                <td class="text text-right" style="border: 1px solid #000;"><?php echo $currency_symbol . amountFormat($fee_deposits_value->amount_fine); ?></td>
                                                                <td class="text text-right" style="border: 1px solid #000;"><?php echo $currency_symbol . amountFormat($fee_deposits_value->amount_discount); ?></td>
                                                                <td style="border: 1px solid #000;"></td>

                                                            </tr>
                                                            <?php
                                                        }
                                                    }
                                                }
                                                   }
                                            }
                                            ?>
                                            <tr class="success">
                                                <td align="left" class="text text-left" style="border: 1px solid #000;">
                                                    <b>    <?php echo $this->lang->line('grand_total'); ?></b>
                                                </td>
                                                <td class="text text-right" style="border: 1px solid #000;">
                                                    <b>    <?php
                                                        echo $currency_symbol . amountFormat($total_amount);
                                                        ?></b>
                                                </td>
                                                <td class="text text-left" style="border: 1px solid #000;"></td>

                                                <td class="text text-right" style="border: 1px solid #000;"> <b>  <?php
                                                        echo $currency_symbol . amountFormat($total_deposite_amount);
                                                        ?></b></td>
                                                <td class="text text-right" style="border: 1px solid #000;"> <b>  <?php
                                                        echo $currency_symbol . amountFormat($total_fine_amount);
                                                        ?></b></td>
                                                <td class="text text-right" style="border: 1px solid #000;"> <b>  <?php
                                                        echo $currency_symbol . amountFormat($total_discount_amount);
                                                        ?></b></td>
                                                <td class="text text-right" style="border: 1px solid #000;"> <b>  <?php
                                                        echo $currency_symbol . amountFormat($total_balance_amount);
                                                        ?></b></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <?php
                                }
                                ?>


                        </div>
                    </div>
                </div>
                <div class="row header ">
                    <div class="col-sm-12">   
                        <?php $this->setting_model->get_receiptfooter(); ?>                       
                    </div>
                </div>  
            </div>

    <?php
}
?>
            
            <?php
            if (in_array('1', $print_copy)) {
                ?>
                <div class="page-break"></div>
                <div class="row">
                    <div id="content" class="col-lg-12 col-sm-12 ">
                        <div class="invoice">
                            <div class="row header ">
                                <div class="col-sm-12">
                                    <?php
                                    if (!empty($print_logo_base64)) {
                                        ?>
                                        <img src="<?php echo $print_logo_base64; ?>" style="height: 100px;max-width: 200px;object-fit: contain;">
                                        <?php
                                    } elseif (!empty($print_logo_url)) {
                                        ?>
                                        <img src="<?php echo $print_logo_url; ?>" style="height: 100px;max-width: 200px;object-fit: contain;">
                                        <?php
                                    } else {
                                        ?>
                                        <img  src="<?php echo $this->media_storage->getImageURL('/uploads/print_headerfooter/student_receipt/'.$this->setting_model->get_receiptheader()); ?>" style="height: 100px;width: 100%;">
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>                            
                                <div class="row">
                                    <div class="col-md-12 text text-center">
                                        <?php echo $this->lang->line('student_copy'); ?>
                                    </div>
                                </div>                            
                            <div class="row">                           
                                <div class="col-xs-6">
                                    <br/>
                                       <address>
                                            <strong>                                            
                                            <?php  echo $this->customlib->getFullName($feearray[0]->firstname, $feearray[0]->middlename,$feearray[0]->lastname,$sch_setting->middlename,$sch_setting->lastname);  ?></strong><?php echo " (".$feearray[0]->admission_no.")"; ?>
                                            </strong><br>
                                            <?php echo $this->lang->line('father_name'); ?>: <?php echo $feearray[0]->father_name; ?><br>
                                            <?php echo $this->lang->line('class'); ?>: <?php echo $feearray[0]->class . " (" . $feearray[0]->section . ")"; ?>
                                        </address>
                                </div>
                                <div class="col-xs-6 text-right">
                                    <br/>
                                       <address>
                                            <strong>Date: <span class="print-date-nepali"><?php
                                                // Get today's date and convert to Nepali format
                                                $today = date('Y-m-d');
                                                $ad_year = (int)date('Y', strtotime($today));
                                                $ad_month = (int)date('m', strtotime($today));
                                                $ad_day = (int)date('d', strtotime($today));
                                                
                                                // Approximate conversion: AD year + 57 = BS year
                                                $bs_year = $ad_year + 57;
                                                $bs_month = $ad_month;
                                                $bs_day = $ad_day;
                                                
                                                // Format as DD-MM-YYYY (Nepali format)
                                                echo sprintf('%02d-%02d-%04d', $bs_day, $bs_month, $bs_year);
                                                ?></span></strong><br/>

                                        </address>                              
                                </div>
                            </div>
                            <hr style="margin-top: 0px;margin-bottom: 0px;" />
                            <div class="row">
                                     <?php
                                    if (!empty($feearray)) {
                                        ?>

                                        <table class="table table-striped table-responsive" style="font-size: 8pt; border-collapse: collapse; border: 1px solid #000;">
                                            <thead>
                                            <th style="border: 1px solid #000;"><?php echo $this->lang->line('fees_group'); ?></th>
                                            <th class="text text-right" style="border: 1px solid #000;"><?php echo $this->lang->line('amount'); ?></th>
                                            <th style="border: 1px solid #000;"><?php echo $this->lang->line('date'); ?></th>
                                            <th class="text text-right" style="border: 1px solid #000;"><?php echo $this->lang->line('paid'); ?></th>
                                            <th class="text text-right" style="border: 1px solid #000;"><?php echo $this->lang->line('fine'); ?></th>
                                            <th class="text text-right" style="border: 1px solid #000;"><?php echo $this->lang->line('discount'); ?></th>
                                            <th class="text text-right" style="border: 1px solid #000;"><?php echo $this->lang->line('balance'); ?></th>
                                            </thead>
                                            <tbody>
                                            <?php
                                            $total_amount = 0;
                                            $total_deposite_amount = 0;
                                            $total_fine_amount = 0;
                                            $total_discount_amount = 0;
                                            $total_balance_amount = 0;
                                            $alot_fee_discount = 0;
                                            if (empty($feearray)) {
                                                ?>
                                                <tr>
                                                    <td colspan="7" class="text-danger text-center" style="border: 1px solid #000;">
                                                        <?php echo $this->lang->line('no_transaction_found'); ?>
                                                    </td>
                                                </tr>
                                                <?php
                                            } else {

                                                foreach ($feearray as $fee_key => $feeList) {
                                                   if($feeList->fee_category == "fees"){

                                                    if ($feeList->is_system) {
                                                        $feeList->amount = $feeList->student_fees_master_amount;
                                                    }

                                                    $fee_discount = 0;
                                                    $fee_paid = 0;
                                                    $fee_fine = 0;
                                                    if (!empty($feeList->amount_detail)) {
                                                        $fee_deposits = json_decode(($feeList->amount_detail));

                                                        foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                                            $fee_paid = $fee_paid + $fee_deposits_value->amount;
                                                            $fee_discount = $fee_discount + $fee_deposits_value->amount_discount;
                                                            $fee_fine = $fee_fine + $fee_deposits_value->amount_fine;
                                                        }
                                                    }
                                                    $feetype_balance = $feeList->amount - ($fee_paid + $fee_discount);
                                                    $total_amount = $total_amount + $feeList->amount;
                                                    $total_discount_amount = $total_discount_amount + $fee_discount;
                                                    $total_fine_amount = $total_fine_amount + $fee_fine;
                                                    $total_deposite_amount = $total_deposite_amount + $fee_paid;
                                                    $total_balance_amount = $total_balance_amount + $feetype_balance;
                                                    ?>
                                                    <tr  class="dark-gray">

                                                        <td style="border: 1px solid #000;">                                                            
                                                            <?php
                                                            if ($feeList->is_system) {
                                                                echo $this->lang->line($feeList->name) . " (" . $this->lang->line($feeList->type) . ")";
                                                            } else {
                                                                echo $feeList->name . " (" . $feeList->type . ")";
                                                            }
                                                            ?>
                                                        </td>
                                                        <td class="text text-right" style="border: 1px solid #000;"><?php 
                                                        echo $currency_symbol . amountFormat($feeList->amount);


      if (($feeList->due_date != "0000-00-00" && $feeList->due_date != null) && (strtotime($feeList->due_date) < strtotime(date('Y-m-d')))) {
            ?>
<span data-toggle="popover" class="text text-danger detail_popover"><?php echo " + " . $currency_symbol .amountFormat($feeList->fine_amount); ?></span>

<div class="fee_detail_popover" style="display: none">
    <?php
if ($feeList->fine_amount != "") {
                ?>
        <p class="text text-danger"><?php echo $this->lang->line('fine'); ?></p>
        <?php
}
            ?>
</div>
    <?php
}


                                                         ?></td>

                                                        <td colspan="1" style="border: 1px solid #000;"></td>
                                                        <td class="text text-right" style="border: 1px solid #000;"><?php
                                                            echo $currency_symbol . amountFormat($fee_paid);
                                                            ?></td>
                                                        <td class="text text-right" style="border: 1px solid #000;"><?php
                                                            echo $currency_symbol . amountFormat($fee_fine);
                                                            ?></td>
                                                        <td class="text text-right" style="border: 1px solid #000;"><?php
                                                            echo $currency_symbol . amountFormat($fee_discount);
                                                            ?></td>
                                                        <td class="text text-right" style="border: 1px solid #000;"><?php
                                                            $display_none = "ss-none";
                                                            if ($feetype_balance > 0) {
                                                                $display_none = "";
                                                                echo $currency_symbol . amountFormat($feetype_balance);
                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>

                                                    <?php
                                                    $fee_deposits = json_decode(($feeList->amount_detail));
                                                    if (is_object($fee_deposits)) {
                                                        foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                                            ?>
                                                            <tr class="white-td">
                                                                <td colspan="1" class="text-right" style="border: 1px solid #000;"><img src="<?php echo $this->media_storage->getImageURL('backend/images/table-arrow.png');?>" alt="" /></td>
                                                                <td class="text text-center" style="border: 1px solid #000;">
                                                                    <?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($fee_deposits_value->date)); ?>
                                                                </td>
                                                                <td class="text text-right" style="border: 1px solid #000;"><?php echo $currency_symbol . amountFormat($fee_deposits_value->amount); ?></td>
                                                                <td class="text text-right" style="border: 1px solid #000;"><?php echo $currency_symbol . amountFormat($fee_deposits_value->amount_fine); ?></td>
                                                                <td class="text text-right" style="border: 1px solid #000;"><?php echo $currency_symbol . amountFormat($fee_deposits_value->amount_discount); ?></td>
                                                                <td style="border: 1px solid #000;"></td>
                                                            </tr>
                                                            <?php
                                                        }
                                                    }
                                                }elseif($feeList->fee_category == "transport"){
                                                
                                                    $fee_discount = 0;
                                                    $fee_paid = 0;
                                                    $fee_fine = 0;
                                                    if (!empty($feeList->amount_detail)) {
                                                        $fee_deposits = json_decode(($feeList->amount_detail));

                                                        foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                                            $fee_paid = $fee_paid + $fee_deposits_value->amount;
                                                            $fee_discount = $fee_discount + $fee_deposits_value->amount_discount;
                                                            $fee_fine = $fee_fine + $fee_deposits_value->amount_fine;
                                                        }
                                                    }
                                                    $feetype_balance = $feeList->fees - ($fee_paid + $fee_discount);
                                                    $total_amount = $total_amount + $feeList->fees;
                                                    $total_discount_amount = $total_discount_amount + $fee_discount;
                                                    $total_fine_amount = $total_fine_amount + $fee_fine;
                                                    $total_deposite_amount = $total_deposite_amount + $fee_paid;
                                                    $total_balance_amount = $total_balance_amount + $feetype_balance;
                                                    ?>
                                                    <tr  class="dark-gray">

                                                        <td style="border: 1px solid #000;"><?php
                                                            echo $this->lang->line("transport_fees");
                                                            ?></td>
                                                                  <td class="text text-right" style="border: 1px solid #000;"><?php 

                                                echo $currency_symbol . $feeList->fees; 


    if (($feeList->due_date != "0000-00-00" && $feeList->due_date != null) && (strtotime($feeList->due_date) < strtotime(date('Y-m-d')))) {
            $tr_fine_amount = $feeList->fine_amount;
            if ($feeList->fine_type != "" && $feeList->fine_type == "percentage") {

                $tr_fine_amount = percentageAmount($feeList->fees, $feeList->fine_percentage);
            }
            ?>

<span  class="text text-danger"><?php echo " + " . $currency_symbol.amountFormat($tr_fine_amount); ?></span>

    <?php
}

                                                ?>
                                            </td>
                                                        <td colspan="1" style="border: 1px solid #000;"></td>
                                                        <td class="text text-right" style="border: 1px solid #000;"><?php
                                                            echo $currency_symbol . amountFormat($fee_paid);
                                                            ?></td>
                                                        <td class="text text-right" style="border: 1px solid #000;"><?php
                                                            echo $currency_symbol . amountFormat($fee_fine);
                                                            ?></td>
                                                        <td class="text text-right" style="border: 1px solid #000;"><?php
                                                            echo $currency_symbol . amountFormat($fee_discount);
                                                            ?></td>
                                                        <td class="text text-right" style="border: 1px solid #000;"><?php
                                                            $display_none = "ss-none";
                                                            if ($feetype_balance > 0) {
                                                                $display_none = "";

                                                                echo $currency_symbol . amountFormat($feetype_balance);
                                                            }
                                                            ?>

                                                        </td>
                                                    </tr>

                                                    <?php
                                                    $fee_deposits = json_decode(($feeList->amount_detail));
                                                    if (is_object($fee_deposits)) {
                                                        foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                                            ?>
                                                            <tr class="white-td">
                                                                <td colspan="1" class="text-right" style="border: 1px solid #000;"><img src="<?php echo $this->media_storage->getImageURL('backend/images/table-arrow.png');?>" alt="" /></td>
                                                                <td class="text text-center" style="border: 1px solid #000;">
                                                                    <?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($fee_deposits_value->date)); ?>
                                                                </td>
                                                                <td class="text text-right" style="border: 1px solid #000;"><?php echo $currency_symbol . amountFormat($fee_deposits_value->amount); ?></td>
                                                                <td class="text text-right" style="border: 1px solid #000;"><?php echo $currency_symbol . amountFormat($fee_deposits_value->amount_fine); ?></td>
                                                                <td class="text text-right" style="border: 1px solid #000;"><?php echo $currency_symbol . amountFormat($fee_deposits_value->amount_discount); ?></td>
                                                                <td style="border: 1px solid #000;"></td>

                                                            </tr>
                                                            <?php
                                                        }
                                                    }
                                                }
                                                   }
                                            }
                                            ?>
                                            <tr class="success">
                                                <td align="left" class="text text-left" style="border: 1px solid #000;">
                                                    <b>    <?php echo $this->lang->line('grand_total'); ?></b>
                                                </td>
                                                <td class="text text-right" style="border: 1px solid #000;">
                                                    <b>    <?php
                                                        echo $currency_symbol . amountFormat($total_amount);
                                                        ?></b>
                                                </td>
                                                <td class="text text-left" style="border: 1px solid #000;"></td>

                                                <td class="text text-right" style="border: 1px solid #000;"> <b>  <?php
                                                        echo $currency_symbol . amountFormat($total_deposite_amount);
                                                        ?></b></td>
                                                <td class="text text-right" style="border: 1px solid #000;"> <b>  <?php
                                                        echo $currency_symbol . amountFormat($total_fine_amount);
                                                        ?></b></td>
                                                <td class="text text-right" style="border: 1px solid #000;"> <b>  <?php
                                                        echo $currency_symbol . amountFormat($total_discount_amount);
                                                        ?></b></td>
                                                <td class="text text-right" style="border: 1px solid #000;"> <b>  <?php
                                                        echo $currency_symbol . amountFormat($total_balance_amount);
                                                        ?></b></td>
                                            </tr>
                                        </tbody>
                                        </table>
                                        <?php
                                    }
                                    ?>
                            </div>
                        </div>
                    </div>
                    <div class="row header ">
                        <div class="col-sm-12">
                            <?php $this->setting_model->get_receiptfooter(); ?>
                        </div>
                    </div>  
                </div>
                <?php
            }
            ?>
                  <?php
            if (in_array('2', $print_copy)) {
                ?>
                <div class="page-break"></div>
                <div class="row">
                    <div id="content" class="col-lg-12 col-sm-12 ">
                        <div class="invoice">
                            <div class="row header ">
                                <div class="col-sm-12">
                                    <?php
                                    if (!empty($print_logo_base64)) {
                                        ?>
                                        <img src="<?php echo $print_logo_base64; ?>" style="height: 100px;max-width: 200px;object-fit: contain;">
                                        <?php
                                    } elseif (!empty($print_logo_url)) {
                                        ?>
                                        <img src="<?php echo $print_logo_url; ?>" style="height: 100px;max-width: 200px;object-fit: contain;">
                                        <?php
                                    } else {
                                        ?>
                                        <img  src="<?php echo $this->media_storage->getImageURL('/uploads/print_headerfooter/student_receipt/'.$this->setting_model->get_receiptheader()); ?>" style="height: 100px;width: 100%;">
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>                            
                                <div class="row">
                                    <div class="col-md-12 text text-center">
                                        <?php echo $this->lang->line('bank_copy'); ?>
                                    </div>
                                </div>                            
                            <div class="row">                           
                                <div class="col-xs-6">
                                    <br/>
                                       <address>
                                            <strong><?php  echo $this->customlib->getFullName($feearray[0]->firstname, $feearray[0]->middlename,$feearray[0]->lastname,$sch_setting->middlename,$sch_setting->lastname);  ?></strong><?php echo " (".$feearray[0]->admission_no.")"; ?>
                                            </strong><br>

                                            <?php echo $this->lang->line('father_name'); ?>: <?php echo $feearray[0]->father_name; ?><br>
                                            <?php echo $this->lang->line('class'); ?>: <?php echo $feearray[0]->class . " (" . $feearray[0]->section . ")"; ?>
                                        </address>
                                </div>
                                <div class="col-xs-6 text-right">
                                    <br/>
                                       <address>
                                            <strong>Date: <span class="print-date-nepali"><?php
                                                // Get today's date and convert to Nepali format
                                                $today = date('Y-m-d');
                                                $ad_year = (int)date('Y', strtotime($today));
                                                $ad_month = (int)date('m', strtotime($today));
                                                $ad_day = (int)date('d', strtotime($today));
                                                
                                                // Approximate conversion: AD year + 57 = BS year
                                                $bs_year = $ad_year + 57;
                                                $bs_month = $ad_month;
                                                $bs_day = $ad_day;
                                                
                                                // Format as DD-MM-YYYY (Nepali format)
                                                echo sprintf('%02d-%02d-%04d', $bs_day, $bs_month, $bs_year);
                                                ?></span></strong><br/>

                                        </address>                              
                                </div>
                            </div>
                            <hr style="margin-top: 0px;margin-bottom: 0px;" />
                            <div class="row">
                                     <?php
                                    if (!empty($feearray)) {
                                        ?>

                                        <table class="table table-striped table-responsive" style="font-size: 8pt; border-collapse: collapse; border: 1px solid #000;">
                                            <thead>
                                            <th style="border: 1px solid #000;"><?php echo $this->lang->line('fees_group'); ?></th>
                                            <th class="text text-right" style="border: 1px solid #000;"><?php echo $this->lang->line('amount'); ?></th>
                                            <th style="border: 1px solid #000;"><?php echo $this->lang->line('date'); ?></th>
                                            <th class="text text-right" style="border: 1px solid #000;"><?php echo $this->lang->line('paid'); ?></th>
                                            <th class="text text-right" style="border: 1px solid #000;"><?php echo $this->lang->line('fine'); ?></th>
                                            <th class="text text-right" style="border: 1px solid #000;"><?php echo $this->lang->line('discount'); ?></th>
                                            <th class="text text-right" style="border: 1px solid #000;"><?php echo $this->lang->line('balance'); ?></th>
                                            </thead>
                                            <tbody>
                                            <?php
                                            $total_amount = 0;
                                            $total_deposite_amount = 0;
                                            $total_fine_amount = 0;
                                            $total_discount_amount = 0;
                                            $total_balance_amount = 0;
                                            $alot_fee_discount = 0;
                                            if (empty($feearray)) {
                                                ?>
                                                <tr>
                                                    <td colspan="7" class="text-danger text-center" style="border: 1px solid #000;">
                                                        <?php echo $this->lang->line('no_transaction_found'); ?>
                                                    </td>
                                                </tr>
                                                <?php
                                            } else {

                                                foreach ($feearray as $fee_key => $feeList) {
                                                   if($feeList->fee_category == "fees"){

                                                    if ($feeList->is_system) {
                                                        $feeList->amount = $feeList->student_fees_master_amount;
                                                    }

                                                    $fee_discount = 0;
                                                    $fee_paid = 0;
                                                    $fee_fine = 0;
                                                    if (!empty($feeList->amount_detail)) {
                                                        $fee_deposits = json_decode(($feeList->amount_detail));

                                                        foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                                            $fee_paid = $fee_paid + $fee_deposits_value->amount;
                                                            $fee_discount = $fee_discount + $fee_deposits_value->amount_discount;
                                                            $fee_fine = $fee_fine + $fee_deposits_value->amount_fine;
                                                        }
                                                    }
                                                    $feetype_balance = $feeList->amount - ($fee_paid + $fee_discount);
                                                    $total_amount = $total_amount + $feeList->amount;
                                                    $total_discount_amount = $total_discount_amount + $fee_discount;
                                                    $total_fine_amount = $total_fine_amount + $fee_fine;
                                                    $total_deposite_amount = $total_deposite_amount + $fee_paid;
                                                    $total_balance_amount = $total_balance_amount + $feetype_balance;
                                                    ?>
                                                    <tr  class="dark-gray">

                                                        <td> 
                                                            <?php
                                                            if ($feeList->is_system) {
                                                                echo $this->lang->line($feeList->name) . " (" . $this->lang->line($feeList->type) . ")";
                                                            } else {
                                                                echo $feeList->name . " (" . $feeList->type . ")";
                                                            }
                                                            ?> 
                                                        </td>
                                                        <td>
                                                            <?php
                                                                if ($feeList->is_system) {
                                                                    echo $this->lang->line($feeList->code) ;
                                                                } else {
                                                                    echo $feeList->code ;
                                                                }
                                                            ?>
                                                        </td>
                                                        <td class="">

                                                            <?php
                                                            if ($feeList->due_date) {
                                                                echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($feeList->due_date));
                                                            } else {
                                                                
                                                            }
                                                            ?>
                                                        </td>
                                                        <td class="">
                                                            <?php
                                                            if ($feetype_balance == 0) {
                                                                echo $this->lang->line('paid');
                                                            } else if (!empty($feeList->amount_detail)) {
                                                                ?><?php echo $this->lang->line('partial'); ?><?php
                                                            } else {
                                                                echo $this->lang->line('unpaid');
                                                            }
                                                            ?>

                                                        </td>
                                                        <td class="text text-right"><?php
                                                         echo $currency_symbol . amountFormat($feeList->amount); 
                                                         
      if (($feeList->due_date != "0000-00-00" && $feeList->due_date != null) && (strtotime($feeList->due_date) < strtotime(date('Y-m-d')))) {
            ?>
<span data-toggle="popover" class="text text-danger detail_popover"><?php echo " + " . $currency_symbol .amountFormat($feeList->fine_amount); ?></span>

<div class="fee_detail_popover" style="display: none">
    <?php
if ($feeList->fine_amount != "") {
                ?>
        <p class="text text-danger"><?php echo $this->lang->line('fine'); ?></p>
        <?php
}
            ?>
</div>
    <?php
}


                                                         ?></td>

                                                        <td colspan="1" style="border: 1px solid #000;"></td>
                                                        <td class="text text-right" style="border: 1px solid #000;"><?php
                                                            echo $currency_symbol . amountFormat($fee_paid);
                                                            ?></td>
                                                        <td class="text text-right"><?php
                                                            echo $currency_symbol . amountFormat($fee_fine);
                                                            ?></td>
                                                        <td class="text text-right"><?php
                                                            echo $currency_symbol . amountFormat($fee_discount);
                                                            ?></td>
                                                        <td class="text text-right"><?php
                                                            $display_none = "ss-none";
                                                            if ($feetype_balance > 0) {
                                                                $display_none = "";


                                                                echo $currency_symbol . amountFormat($feetype_balance);
                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>

                                                    <?php
                                                    $fee_deposits = json_decode(($feeList->amount_detail));
                                                    if (is_object($fee_deposits)) {
                                                        foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                                            ?>
                                                            <tr class="white-td">
                                                                <td colspan="1" class="text-right" style="border: 1px solid #000;"><img src="<?php echo $this->media_storage->getImageURL('backend/images/table-arrow.png');?>" alt="" /></td>
                                                                <td class="text text-center" style="border: 1px solid #000;">
                                                                    <?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($fee_deposits_value->date)); ?>
                                                                </td>
                                                                <td class="text text-right" style="border: 1px solid #000;"><?php echo $currency_symbol . amountFormat($fee_deposits_value->amount); ?></td>
                                                                <td class="text text-right" style="border: 1px solid #000;"><?php echo $currency_symbol . amountFormat($fee_deposits_value->amount_fine); ?></td>
                                                                <td class="text text-right" style="border: 1px solid #000;"><?php echo $currency_symbol . amountFormat($fee_deposits_value->amount_discount); ?></td>
                                                                <td style="border: 1px solid #000;"></td>

                                                            </tr>
                                                            <?php
                                                        }
                                                    }
                                                }elseif($feeList->fee_category == "transport"){
                                                
                                                    $fee_discount = 0;
                                                    $fee_paid = 0;
                                                    $fee_fine = 0;
                                                    if (!empty($feeList->amount_detail)) {
                                                        $fee_deposits = json_decode(($feeList->amount_detail));

                                                        foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                                            $fee_paid = $fee_paid + $fee_deposits_value->amount;
                                                            $fee_discount = $fee_discount + $fee_deposits_value->amount_discount;
                                                            $fee_fine = $fee_fine + $fee_deposits_value->amount_fine;
                                                        }
                                                    }
                                                    $feetype_balance = $feeList->fees - ($fee_paid + $fee_discount);
                                                    $total_amount = $total_amount + $feeList->fees;
                                                    $total_discount_amount = $total_discount_amount + $fee_discount;
                                                    $total_fine_amount = $total_fine_amount + $fee_fine;
                                                    $total_deposite_amount = $total_deposite_amount + $fee_paid;
                                                    $total_balance_amount = $total_balance_amount + $feetype_balance;
                                                    ?>
                                                    <tr  class="dark-gray">

                                                        <td><?php
                                                            echo $this->lang->line("transport_fees");
                                                            ?></td>
                                                        <td><?php echo $this->lang->line(strtolower($feeList->month)); ?></td>
                                                        <td class="">

                                                            <?php
                                                            if ($feeList->due_date == "0000-00-00") {
                                                                
                                                            } else {

                                                                echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($feeList->due_date));
                                                            }
                                                            ?>
                                                        </td>
                                                        <td class="">
                                                            <?php
                                                            if ($feetype_balance == 0) {
                                                                echo $this->lang->line('paid');
                                                            } else if (!empty($feeList->amount_detail)) {
                                                                ?><?php echo $this->lang->line('partial'); ?><?php
                                                            } else {
                                                                echo $this->lang->line('unpaid');
                                                            }
                                                            ?>

                                                        </td>
                                            <td class="text text-right">
                                                <?php 

                      echo $currency_symbol . $feeList->fees; 


    if (($feeList->due_date != "0000-00-00" && $feeList->due_date != null) && (strtotime($feeList->due_date) < strtotime(date('Y-m-d')))) {
            $tr_fine_amount = $feeList->fine_amount;
            if ($feeList->fine_type != "" && $feeList->fine_type == "percentage") {

                $tr_fine_amount = percentageAmount($feeList->fees, $feeList->fine_percentage);
            }
            ?>

<span  class="text text-danger"><?php echo " + " . $currency_symbol.amountFormat($tr_fine_amount); ?></span>

    <?php
}

                                                ?>
                                            </td>

                                                        <td colspan="1" style="border: 1px solid #000;"></td>
                                                        <td class="text text-right" style="border: 1px solid #000;"><?php
                                                            echo $currency_symbol . amountFormat($fee_paid);
                                                            ?></td>
                                                        <td class="text text-right" style="border: 1px solid #000;"><?php
                                                            echo $currency_symbol . amountFormat($fee_fine);
                                                            ?></td>
                                                        <td class="text text-right" style="border: 1px solid #000;"><?php
                                                            echo $currency_symbol . amountFormat($fee_discount);
                                                            ?></td>
                                                        <td class="text text-right" style="border: 1px solid #000;"><?php
                                                            $display_none = "ss-none";
                                                            if ($feetype_balance > 0) {
                                                                $display_none = "";

                                                                echo $currency_symbol . amountFormat($feetype_balance);
                                                            }
                                                            ?>

                                                        </td>



                                                    </tr>

                                                    <?php
                                                    $fee_deposits = json_decode(($feeList->amount_detail));
                                                    if (is_object($fee_deposits)) {
                                                        foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                                            ?>
                                                            <tr class="white-td">
                                                                <td colspan="1" class="text-right" style="border: 1px solid #000;"><img src="<?php echo $this->media_storage->getImageURL('backend/images/table-arrow.png');?>" alt="" /></td>
                                                                <td class="text text-center" style="border: 1px solid #000;">
                                                                    <?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($fee_deposits_value->date)); ?>
                                                                </td>
                                                                <td class="text text-right" style="border: 1px solid #000;"><?php echo $currency_symbol . amountFormat($fee_deposits_value->amount); ?></td>
                                                                <td class="text text-right" style="border: 1px solid #000;"><?php echo $currency_symbol . amountFormat($fee_deposits_value->amount_fine); ?></td>
                                                                <td class="text text-right" style="border: 1px solid #000;"><?php echo $currency_symbol . amountFormat($fee_deposits_value->amount_discount); ?></td>
                                                                <td style="border: 1px solid #000;"></td>

                                                            </tr>
                                                            <?php
                                                        }
                                                    }
                                                }
                                                   }


                                            }
                                            ?>
                                            <tr class="success">
                                                <td align="left" class="text text-left" style="border: 1px solid #000;">
                                                    <b>    <?php echo $this->lang->line('grand_total'); ?></b>
                                                </td>
                                                <td class="text text-right" style="border: 1px solid #000;">
                                                    <b>    <?php
                                                        echo $currency_symbol . amountFormat($total_amount);
                                                        ?></b>
                                                </td>
                                                <td class="text text-left" style="border: 1px solid #000;"></td>

                                                <td class="text text-right" style="border: 1px solid #000;"> <b>  <?php
                                                        echo $currency_symbol . amountFormat($total_deposite_amount);
                                                        ?></b></td>
                                                <td class="text text-right" style="border: 1px solid #000;"> <b>  <?php
                                                        echo $currency_symbol . amountFormat($total_fine_amount);
                                                        ?></b></td>
                                                <td class="text text-right" style="border: 1px solid #000;"> <b>  <?php
                                                        echo $currency_symbol . amountFormat($total_discount_amount);
                                                        ?></b></td>
                                                <td class="text text-right" style="border: 1px solid #000;"> <b>  <?php
                                                        echo $currency_symbol . amountFormat($total_balance_amount);
                                                        ?></b></td>
                                            </tr>
                                        </tbody>
                                        </table>
                                        <?php
                                    }
                                    ?>
                            </div>
                        </div>
                    </div>
                    <div class="row header ">
                        <div class="col-sm-12">
                            <?php $this->setting_model->get_receiptfooter(); ?>
                        </div>
                    </div>  
                </div>
                <?php
            }
            ?>
        </div>
        <div class="clearfix"></div>

        <footer>           
        </footer>
        
        <script type="text/javascript">
        // Convert dates to Nepali using the same logic as the modal
        (function() {
            function convertDatesToNepali() {
                // Wait for NepaliFunctions to be available
                if (typeof window.NepaliFunctions === 'undefined') {
                    // Try to access from parent window if in iframe
                    if (window.parent && window.parent.NepaliFunctions) {
                        window.NepaliFunctions = window.parent.NepaliFunctions;
                    } else {
                        // Retry after a short delay
                        setTimeout(convertDatesToNepali, 100);
                        return;
                    }
                }
                
                var todayBsStr = '';
                try {
                    // Method 1: Use GetCurrentBsDate if available (same logic as modal)
                    if (window.NepaliFunctions && typeof window.NepaliFunctions.GetCurrentBsDate === 'function') {
                        var bs = window.NepaliFunctions.GetCurrentBsDate();
                        if (bs && bs.year && bs.month && bs.day) {
                            var month = String(bs.month).padStart(2, '0');
                            var day = String(bs.day).padStart(2, '0');
                            // Use same format as modal: MM-DD-YYYY
                            todayBsStr = month + '-' + day + '-' + bs.year;
                        }
                    }
                    
                    // Method 2: Use AD2BS conversion if GetCurrentBsDate not available (same logic as modal)
                    if (!todayBsStr && window.NepaliFunctions && typeof window.NepaliFunctions.AD2BS === 'function') {
                        var today = new Date();
                        var bs = window.NepaliFunctions.AD2BS({
                            year: today.getFullYear(),
                            month: today.getMonth() + 1,
                            day: today.getDate()
                        });
                        if (bs && bs.year && bs.month && bs.day) {
                            var month = String(bs.month).padStart(2, '0');
                            var day = String(bs.day).padStart(2, '0');
                            // Use same format as modal: MM-DD-YYYY
                            todayBsStr = month + '-' + day + '-' + bs.year;
                        }
                    }
                } catch (err) {
                    console.warn('Could not convert date to Nepali:', err);
                }
                
                // Update all date spans with the correct Nepali date
                if (todayBsStr) {
                    var dateSpans = document.querySelectorAll('.print-date-nepali');
                    for (var i = 0; i < dateSpans.length; i++) {
                        dateSpans[i].textContent = todayBsStr;
                    }
                }
            }
            
            // Run conversion when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', convertDatesToNepali);
            } else {
                convertDatesToNepali();
            }
            
            // Also try after a delay in case NepaliFunctions loads later
            setTimeout(convertDatesToNepali, 500);
        })();
        </script>
    </body>
</html>

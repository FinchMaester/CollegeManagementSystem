<style type="text/css">
    @media print {
        .no-print, .no-print * {
            display: none !important;
        }
    }
</style>
<div class="content-wrapper" style="min-height: 946px;">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <i class="fa fa-calendar-check-o"></i> <?php //echo $this->lang->line('attendance'); ?> <small> <?php //echo $this->lang->line('by_date1'); ?></small>
        </h1>
    </section>
    <section class="content">
        <?php $this->load->view('attendencereports/_attendance'); ?>
        <div class="row">  
            <div class="col-md-12">
                <div class="box removeboxmius">
                    <div class="box-header ptbnull"></div>
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <form id='form1' action="<?php echo site_url('attendencereports/daily_attendance_report') ?>" method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="date"><?php echo $this->lang->line('date'); ?></label><span class="req"> *</span>
                                        <!-- Plain text input; JS will fill it once on load. No calendar UI. -->
                                        <input
                                            type="text"
                                            name="date"
                                            id="date"
                                            class="form-control date-bs"
                                            value="<?php echo set_value('date'); ?>">
                                        <span class="text-danger"><?php echo form_error('date'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <button type="submit" name="search" value="search" class="btn btn-primary btn-sm pull-right checkbox-toggle">
                                            <i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?>
                                        </button>
                                    </div>  
                                </div>
                            </div>
                        </div>  
                    </form>
                    <div class="">
                        <div class="box-header ptbnull"></div>
                        <div class="box-header ptbnull">
                            <h3 class="box-title titlefix"><i class="fa fa-money"></i>
                                <?php echo $this->lang->line('daily_attendance_report') ?>
                            </h3>
                        </div>
                        <div class="box-body table-responsive">
                            <div class="download_label"><?php echo $this->lang->line('daily_attendance_report'); ?></div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('class_section'); ?></th>
                                        <th><?php echo $this->lang->line('total_present'); ?></th>
                                        <th><?php echo $this->lang->line('total_absent'); ?></th>
                                        <th><?php echo $this->lang->line('present') . " %"; ?></th>
                                        <th><?php echo $this->lang->line('absent') . " %"; ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($result)) {
                                        foreach ($resultlist as $key => $value) {
                                            ?>
                                            <tr>
                                                <td><?php echo $value['class_section'] ?></td>
                                                <td><?php echo $value['total_present'] ?></td>
                                                <td><?php echo $value['total_absent'] ?></td>
                                                <td><?php echo $value['present_percent'] ?></td>
                                                <td><?php echo $value['absent_persent'] ?></td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                         <tr style="font-weight: bold;">
                                            <td></td>
                                            <td><?php echo $all_present ?></td>
                                            <td><?php echo $all_absent ?></td>
                                            <td><?php echo $all_present_percent ?></td>
                                            <td><?php echo $all_absent_percent ?></td>
                                        </tr>                                      
                                </tbody>                                
                                    <?php } ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
    </section>
</div>

<script type="text/javascript">
$(document).ready(function() {
    // Set today's Nepali date using global datepicker
    var $date = $('#date');
    if (!$date.val()) {
        // Wait for global datepicker to be available
        function setTodaysNepaliDate() {
            if (typeof $.fn.nepaliDatePicker !== 'undefined') {
                try {
                    // Get today's Nepali date using the global datepicker
                    var now = new Date();
                    var today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                    
                    // Use the datepicker's conversion method
                    var $temp = $('<input>');
                    $temp.nepaliDatePicker({
                        dateFormat: 'YYYY-MM-DD',
                        ndpYear: true,
                        ndpMonth: true,
                        ndpYearCount: 10,
                        readOnlyInput: false
                    });
                    
                    // Set today's date
                    $temp.val(today.getFullYear() + '-' + 
                            String(today.getMonth() + 1).padStart(2, '0') + '-' + 
                            String(today.getDate()).padStart(2, '0'));
                    
                    // Get the converted Nepali date
                    var nepaliDate = $temp.nepaliDatePicker('getNepaliDate');
                    if (nepaliDate && nepaliDate.year) {
                        var yy = nepaliDate.year;
                        var mm = String(nepaliDate.month).padStart(2, '0');
                        var dd = String(nepaliDate.day).padStart(2, '0');
                        $date.val(yy + '-' + mm + '-' + dd);
                    }
                    
                    $temp.remove();
                } catch (e) {
                    console.log('Error getting Nepali date:', e);
                }
            } else {
                // Retry in 500ms if datepicker not ready
                setTimeout(setTodaysNepaliDate, 500);
            }
        }
        
        setTodaysNepaliDate();
    }
});
</script>

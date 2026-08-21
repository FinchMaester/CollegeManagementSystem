<style type="text/css">
    @media print
    {
        .no-print, .no-print *
        {
            display: none !important;
        }
    }
</style>

<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-usd"></i> <?php echo $this->lang->line('income'); ?></h1>
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
                <form role="form" id="form1" action="<?php echo site_url('admin/income/checkvalidation') ?>" method="post" class="">
                                        <?php echo $this->customlib->getCSRF(); ?>
                                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">

                                        <div class="col-sm-6 col-md-6">
                                            <div class="form-group">
                                                <label><?php echo $this->lang->line('search_type'); ?></label><small class="req"> *</small>
                                                <select class="form-control" name="search_type" id="search_type" onchange="showdate(this.value)">

                                                    <?php foreach ($searchlist as $key => $search) {
    ?>
                                                        <option value="<?php echo $key ?>" <?php
if ((isset($search_type)) && ($search_type == $key)) {
        echo "selected";
    }
    ?>><?php echo $search ?></option>
                                                            <?php }?>
                                                </select>
                                                <span class="text-danger" id="error_search_type"></span>
                                            </div>
                                        </div>
                                        <div id='date_result'>

                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-sm checkbox-toggle pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                            </div>
                                        </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">

                                        <?php echo $this->customlib->getCSRF(); ?>
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label><?php echo $this->lang->line('search'); ?></label><small class="req"> *</small>
                                                <input autofocus="" type="text" value="<?php echo set_value('search_text', ""); ?>" name="search_text" id="search_text" class="form-control" placeholder="<?php echo $this->lang->line('search_by_income'); ?>">
                                               <span class="text-danger" id="error_search_text"></span>
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <button type="submit" name="search" value="search_full" class="btn btn-primary btn-sm checkbox-toggle pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                            </div>
                                        </div>
                                </div>
                            </div>
                        </div>
                        </form>
                    </div>
                <div class="" id="exp">
                            <div class="box-header ptbnull"></div>
                            <div class="box-header ptbnull">
                                <h3 class="box-title titlefix"><i class="fa fa-money"></i> <?php echo $this->lang->line('income_list'); ?></h3>
                            </div>
                            <div class="box-body table-responsive">
                                <div class="download_label"><?php echo $this->lang->line('income_result'); ?></div>
                                 <table class="table table-striped table-bordered table-hover income-list" data-export-title="<?php echo $this->lang->line('income_list'); ?>">
                                    <thead>
                                        <tr>
                                            <th><?php echo $this->lang->line('name'); ?></th>
                                            <th><?php echo $this->lang->line('invoice_number'); ?></th>
                                            <th class="white-space-nowrap"><?php echo $this->lang->line('income_head'); ?></th>
                                            <th class="white-space-nowrap"><?php echo $this->lang->line('date'); ?></th>
                                            <th class="text-right white-space-nowrap"><?php echo $this->lang->line('amount'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        </tr>
                                    </thead>
                                    <tbody> </tbody>
                                </table>
                            </div>
                        </div>
                  </div>
            </div>
        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<script type="text/javascript">

    var base_url = '<?php echo base_url() ?>';

    function printDiv(elem) {
        Popup(jQuery(elem).html());
    }

    function Popup(data)
    {
        
        var frame1 = $('<iframe />');
        frame1[0].name = "frame1";
        frame1.css({"position": "absolute", "top": "-1000000px"});
        $("body").append(frame1);
        var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow : frame1[0].contentDocument.document ? frame1[0].contentDocument.document : frame1[0].contentDocument;
        frameDoc.document.open();
        //Create a new HTML document.
        frameDoc.document.write('<html>');
        frameDoc.document.write('<head>');
        frameDoc.document.write('<title></title>');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/bootstrap/css/bootstrap.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/font-awesome.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/ionicons.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/AdminLTE.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/skins/_all-skins.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/iCheck/flat/blue.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/morris/morris.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/jvectormap/jquery-jvectormap-1.2.2.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/datepicker/datepicker3.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/daterangepicker/daterangepicker-bs3.css">');
        frameDoc.document.write('</head>');
        frameDoc.document.write('<body>');
        frameDoc.document.write(data);
        frameDoc.document.write('</body>');
        frameDoc.document.write('</html>');
        frameDoc.document.close();
        setTimeout(function () {
            window.frames["frame1"].focus();
            window.frames["frame1"].print();
            frame1.remove();
        }, 500);

        return true;
    }
</script>

<script>
$(document).ready(function() {
     emptyDatatable('income-list','data');

});
</script>

<script type="text/javascript">

$(document).ready(function(){
$(document).on('submit','#form1',function(e){
    e.preventDefault(); // avoid to execute the actual submit of the form.
    var $this = $(this).find("button[type=submit]:focus");
    var form = $(this);
    var url = form.attr('action');
    var form_data = form.serializeArray();
    form_data.push({name: 'button_type', value: $this.attr('value')});
    $.ajax({
           url: url,
           type: "POST",
           dataType:'JSON',
           data: form_data, // serializes the form's elements.
              beforeSend: function () {
                $('[id^=error]').html("");
                    $this.button('loading');
                    resetFields($this.attr('value'));
               },
              success: function(response) {
                if(!response.status){
                    $.each(response.error, function(key, value) {
                        $('#error_' + key).html(value);
                    });
                }else{
                    // Destroy existing DataTable if it exists
                    if ($.fn.DataTable.isDataTable('.income-list')) {
                        try {
                            $('.income-list').DataTable().destroy();
                        } catch(e) {
                            $('.income-list').empty();
                        }
                    }
                    
                    // Call original initDatatable function
                    initDatatable('income-list','admin/income/getincomesearchlist',response.params);
                }
              },
             error: function() { // your error handler
                 $this.button('reset');
             },
             complete: function() {
             $this.button('reset');
             }
         });
      });
    });

function resetFields(search_type){
        if(search_type == "search_full"){
            $('#search_type').val('');
        }else if (search_type == "search_filter") {
             $('#search_text').val("");
        }
    }

    // Custom showdate function for income search with Nepali date support
    function showdate(type) {
        if (type == 'period') {
            // Get today's BS date for default values
            var todayBsStr = '';
            try {
                if (window.NepaliFunctions && typeof window.NepaliFunctions.GetCurrentBsDate === 'function') {
                    var bs = window.NepaliFunctions.GetCurrentBsDate();
                    if (bs && bs.year && bs.month && bs.day) {
                        var month = String(bs.month).padStart(2, '0');
                        var day = String(bs.day).padStart(2, '0');
                        todayBsStr = bs.year + '-' + month + '-' + day;
                    }
                }
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
                        todayBsStr = bs.year + '-' + month + '-' + day;
                    }
                }
            } catch(e) {
                console.warn('Could not get BS date:', e);
            }
            
            // Use today's BS date as default if not available from existing fields
            var date_from = $('#date_from').val() || todayBsStr;
            var date_to = $('#date_to').val() || todayBsStr;
            
            $.ajax({
                url: base_url + 'admin/income/get_betweendate/period',
                type: 'POST',
                data: {date_from: date_from, date_to: date_to},
                dataType: 'html',
                success: function (res) {
                    if (!res || res.trim() === '') {
                        console.error('Empty response from server');
                        return;
                    }
                    
                    try {
                        // Safely insert HTML - use a temporary container to parse
                        var $container = $('#date_result');
                        
                        // Create a temporary div to safely parse the HTML
                        var $temp = $('<div>');
                        
                        // Remove any script tags from response before parsing
                        var cleanRes = res.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
                        
                        // Set HTML content
                        $temp.html(cleanRes);
                        
                        // Get the HTML content without scripts
                        var htmlContent = $temp.html();
                        
                        // Insert into container
                        $container.html(htmlContent);
                        
                        // Initialize Nepali datepickers after DOM insertion
                        setTimeout(function() {
                            if (typeof $.fn.nepaliDatePicker !== 'undefined') {
                                $('#date_from, #date_to').each(function() {
                                    var $field = $(this);
                                    
                                    // CRITICAL: Prevent English calendar from attaching
                                    // Remove ALL classes that trigger English datepicker
                                    $field.removeClass('date english-date date_fee datetime');
                                    
                                    // Ensure Nepali date attributes are set BEFORE any other initialization
                                    $field.attr('data-nepali-date', 'true');
                                    $field.attr('data-prevent-english-calendar', 'true');
                                    $field.addClass('nepali-datepicker date-bs');
                                    
                                    // Prevent focus events from triggering English calendar
                                    $field.off('focusin.footer-datepicker');
                                    
                                    // Destroy any English datepicker that might have attached
                                    if ($field.data('datepicker')) {
                                        try { 
                                            $field.datepicker('destroy'); 
                                            $field.removeData('datepicker');
                                        } catch(e) {
                                            console.warn('Error destroying English datepicker:', e);
                                        }
                                    }
                                    if ($field.data('datetimepicker')) {
                                        try { 
                                            $field.datetimepicker('destroy'); 
                                            $field.removeData('datetimepicker');
                                        } catch(e) {}
                                    }
                                    
                                    // Remove any existing Nepali datepicker to reinitialize
                                    if ($field.data('nepaliDatePicker')) {
                                        try {
                                            $field.nepaliDatePicker('destroy');
                                            $field.removeData('nepaliDatePicker');
                                            $field.removeData('ndp-initialized');
                                        } catch(e) {}
                                    }
                                    
                                    // Initialize Nepali datepicker
                                    if (!$field.data('ndp-initialized')) {
                                        try {
                                            $field.nepaliDatePicker({
                                                ndpTriggerButton: false,
                                                dateFormat: 'YYYY-MM-DD',
                                                ndpYear: true,
                                                ndpMonth: true,
                                                ndpYearCount: 10,
                                                readOnlyInput: false,
                                                ndpTrigger: 'click'
                                            });
                                            $field.data('ndp-initialized', true);
                                        } catch(e) {
                                            console.error('Error initializing Nepali datepicker:', e);
                                        }
                                    }
                                });
                            }
                        }, 200);
                        
                        // CRITICAL: Prevent footer.php delegate from attaching English calendar
                        // The footer.php has: $("body").delegate(".date", "focusin", function() {...})
                        // We need to prevent this from firing on our fields
                        setTimeout(function() {
                            $('#date_from, #date_to').each(function() {
                                var $field = $(this);
                                
                                // Method 1: Stop propagation on focusin BEFORE footer delegate fires
                                $field.on('focusin.income-search', function(e) {
                                    e.stopImmediatePropagation();
                                    e.preventDefault();
                                    return false;
                                });
                                
                                // Method 2: Ensure field never gets .date class
                                if ($field.hasClass('date')) {
                                    $field.removeClass('date');
                                }
                                
                                // Method 3: Monitor and destroy English calendar if it attaches
                                var checkInterval = setInterval(function() {
                                    if ($field.data('datepicker')) {
                                        try {
                                            $field.datepicker('destroy');
                                            $field.removeData('datepicker');
                                            console.log('Prevented English calendar on income search field');
                                        } catch(e) {}
                                    } else {
                                        clearInterval(checkInterval);
                                    }
                                }, 100);
                                
                                // Stop checking after 2 seconds
                                setTimeout(function() {
                                    clearInterval(checkInterval);
                                }, 2000);
                            });
                        }, 100);
                    } catch(e) {
                        console.error('Error processing AJAX response:', e);
                        $('#date_result').html('<div class="alert alert-danger">Error processing date fields. Please refresh the page.</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error loading date fields:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText ? xhr.responseText.substring(0, 200) : 'No response'
                    });
                    $('#date_result').html('<div class="alert alert-danger">Error loading date fields. Please refresh the page.</div>');
                }
            });
        } else {
            $('#date_result').html('');
        }
    }
</script>
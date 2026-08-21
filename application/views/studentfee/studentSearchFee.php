<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-money"></i> <?php echo $this->lang->line('fees_collection'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <form class="studentsearchfee" action="<?php echo site_url('studentfee/feesearch') ?>"  method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">

<?php echo validation_errors(); ?>

                                <div class="col-md-3">
                                    <div class="form-group relative">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('fees_group'); ?></label><small class="req"> *</small>
                                            <div id="checkbox-dropdown-container">
            <div class="">
               <div class="custom-select" id="custom-select"><?php echo $this->lang->line('select'); ?></div>
               
                <div id="custom-select-option-box">
                    <div class="custom-select-option checkbox">
                        <label class="vertical-middle line-h-18">
                            <input  class="custom-select-option-checkbox" type="checkbox" <?php if(isset($select_all) && $select_all == 'on'){echo "checked"; }  ?> name="select_all" id="select_all"> <?php echo $this->lang->line('select_all'); ?> 
                        </label> 
                    </div>
                
                     <?php
foreach ($feesessiongrouplist as $feecategory) {
    ?>
                                                  <div class="custom-select-option group_name">
                       <?php echo $feecategory->group_name //$feecategory->is_system; ?>
                    </div>
  <?php
if (!empty($feecategory->feetypes)) {
        foreach ($feecategory->feetypes as $fee_key => $fee_value) {

            ?>
                                                        <div class="custom-select-option checkbox">
                        <label class="vertical-middle line-h-18">
                            <input  value="<?php echo $feecategory->id . "-" . $fee_value->id; ?>"
                            class="custom-select-option-checkbox" type="checkbox"
                            name="feegroup[]" <?php echo set_checkbox("feegroup[]", $feecategory->id . "-" . $fee_value->id) ?>> 
                            <?php echo ($feecategory->is_system) ? $this->lang->line($fee_value->type) . " (" . $this->lang->line($fee_value->code) . ")" : $fee_value->type . " (" . $fee_value->code . ")"; ?>
                        </label>
                    </div>
                                                                <?php
}
    }
    ?>
                                                <?php
}
?>
                </div>
            </div>
    </div>
                                        <span class="text-danger"><?php echo form_error('feegroup[]'); ?></span>
                                    </div>
                                </div>

                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                        <select id="section_id" name="section_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                        <select id="program_id" name="program_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label> <small class="req"> *</small>
                                        <select id="class_id" name="class_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger" id="error_class_id"></span>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <button type="submit" id="search_filter" class="btn btn-sm btn-primary pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <?php
if (isset($student_remain_fees)) {
    ?>
                        <div class="" id="duefee">
                            <div class="box-header ptbnull"></div>
                            <div class="box-header ptbnull">
                                <h3 class="box-title titlefix"><i class="fa fa-users"></i> <?php echo $this->lang->line('student_list'); ?></h3>
                            </div>
                            <div class="box-body table-responsive">
                                <div class="download_label"><?php echo $this->lang->line('student_list'); ?></div>
                                <table class="table table-striped table-bordered table-hover example">
                                    <thead>
                                        <tr>
                                            <th><?php echo $this->lang->line('class'); ?></th>
                                            <th><?php echo $this->lang->line('admission_no'); ?></th>
                                            <th><?php echo $this->lang->line('student_name'); ?></th>
                                            <th><?php echo $this->lang->line('status'); ?></th>
                                            <th width="30%"><?php echo $this->lang->line('fees_group'); ?></th>
                                            <th class="text text-right"><?php echo $this->lang->line('amount'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                            <th class="text text-right"><?php echo $this->lang->line('deposit'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                            <th class="text text-right"><?php echo $this->lang->line('discount'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                            <th class="text text-right"><?php echo $this->lang->line('fine'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                            <th class="text text-right"><?php echo $this->lang->line('balance'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                            <th class="text text-right noExport"><?php echo $this->lang->line('action'); ?> </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($student_remain_fees)) {
        ?>

                                            <?php
} else {
        $count = 1;
        foreach ($student_remain_fees as $student) {
            $amount          = 0;
            $amount_deposite = 0;
            $amount_discount = 0;
            $amount_fine     = 0;
            if (!empty($student['fees'])) {
                foreach ($student['fees'] as $fee_key => $fee_value) {
                    $amount += $fee_value['amount'];
                    $amount_deposite += $fee_value['amount_deposite'];
                    $amount_discount += $fee_value['amount_discount'];
                    $amount_fine += $fee_value['amount_fine'];
                }
            }
            
            $balance = ($amount - ($amount_deposite + $amount_discount));
            if($balance > 0){
            $student_disabled = isset($student['is_active']) && strtolower(trim((string) $student['is_active'])) === 'no';
            $student_name = $this->customlib->getFullName($student['firstname'], $student['middlename'], $student['lastname'], $sch_setting->middlename, $sch_setting->lastname);
            if ($student_disabled) {
                $student_name .= ' <span class="label label-danger">Disabled</span>';
            }
            ?>
                                                <tr<?php echo $student_disabled ? ' class="warning"' : ''; ?>>
                                                    <td><?php echo $student['class'] . "-" . $student['section']; ?></td>
                                                    <td><?php echo $student['admission_no']; ?></td>
                                                    <td><?php echo $student_name; ?></td>
                                                    <td>
                                                        <?php if ($student_disabled) { ?>
                                                            <span class="label label-danger">Disabled</span>
                                                        <?php } else { ?>
                                                            <span class="label label-success"><?php echo $this->lang->line('active'); ?></span>
                                                        <?php } ?>
                                                    </td>
                                                    <td>
                                                        <?php
if (!empty($student['fees'])) {

                echo implode(', ', array_map(
                    function ($v) {
                       return ($v['is_system']) ? $this->lang->line($v['fee_group']) . ' (' . $this->lang->line($v['fee_type']) . ')' :$v['fee_group'] . ' (' . $v['fee_type'] . ' : ' . $v['fee_code'] . ')';
                   
                    },
                    $student['fees']));
            }

            ?>
                                                    </td>
                                                    <td class="text text-right"><?php echo amountFormat($amount); ?></td>
                                                    <td class="text text-right"><?php echo amountFormat($amount_deposite); ?></td>
                                                    <td class="text text-right"><?php echo amountFormat($amount_discount); ?></td>
                                                    <td class="text text-right"><?php echo amountFormat($amount_fine); ?></td>
                                                    <td class="text text-right"><?php
echo amountFormat(($amount - ($amount_deposite + $amount_discount)));
            ?></td>
                                                    <td class="text text-right">
                                                        <?php if ($this->rbac->hasPrivilege('collect_fees', 'can_add')) {?><a href="<?php echo base_url(); ?>studentfee/addfee/<?php echo $student['student_session_id'] ?>" class="btn btn-info btn-xs">
                                                            <?php echo $currency_symbol; ?> <?php echo $this->lang->line('add_fees'); ?>
                                                            </a>
                                                <?php }?>
                                                    </td>
                                                </tr>
        <?php }
}
        $count++;
    }
    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
} else {

}
?>

    </section>
</div>
<script type="text/javascript">
    $(document).on('submit','.studentsearchfee',function(e){
         document.getElementById("search_filter").disabled = true;
    });

    $(document).ready(function () {
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

// When section changes, fetch programs
$(document).on('change', '#section_id', function (e) {
    var section_id = $(this).val();
    
    // Clear program and class dropdowns
    $('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
    $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
    
    if (section_id != '') {
        // Show loading indicator
        $('#program_id').html('<option value="">Loading...</option>');
        
        // Fetch programs for this section
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
                
                // If there's a pre-selected program, trigger class load
                if(program_id) {
                    $('#program_id').trigger('change');
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
            
            var class_id = '<?php echo set_value("class_id") ?>'; 
            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
            
            if (response && response.length > 0) {
                $.each(response, function(index, classItem) {
                    // ADD SELECTED LOGIC HERE
                    var selected = (class_id == classItem.id) ? 'selected' : '';
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
});
</script>

<script type="text/javascript">
    var base_url = '<?php echo base_url() ?>';
    function printDiv(elem) {
        var fcat = $("#feecategory_id option:selected").text();
        var ftype = $("#feetype_id option:selected").text();
        var cls = $("#class_id option:selected").text();
        var sec = $("#section_id option:selected").text();
        $('.fcat').html(fcat);
        $('.ftype').html(ftype);
        $('.cls').html(cls + '(' + sec + ')');
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
    $("#custom-select").on("click",function(){
        $("#custom-select-option-box").toggle();
    });

    $(".custom-select-option").on("click", function(e) {
        var checkboxObj = $(this).children("input");
        if($(e.target).attr("class") != "custom-select-option-checkbox") {
                if($(checkboxObj).prop('checked') == true) {
                    $(checkboxObj).prop('checked',false)
                } else {
                    $(checkboxObj).prop("checked",true);
                }
        }
    });


$(document).on('click', function(event) {
  if (event.target.id != "custom-select" && !$(event.target).closest('div').hasClass("custom-select-option")  ) {
          $("#custom-select-option-box").hide();
     }
});

$(document).on('change','#select_all',function(){
   
        $('input:checkbox').not(this).prop('checked', this.checked);
});
</script>
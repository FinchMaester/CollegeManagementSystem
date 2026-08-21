<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-money"></i> <?php //echo $this->lang->line('fees_collection'); ?> <small> <?php //echo $this->lang->line('filter_by_name1'); ?></small></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <?php $this->load->view('financereports/_finance'); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box removeboxmius">
                    <div class="box-header ptbnull"></div>
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <form action="<?php echo site_url('financereports/reportduefees') ?>"  method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="section_id"><?php echo $this->lang->line('faculty'); ?></label>
                                        <select autofocus id="section_id" name="section_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
                                            foreach ($section_list as $section) {
                                                ?>
                                                <option value="<?php echo $section['section_id'] ?>" <?php if (set_value('section_id') == $section['section_id']) echo "selected=selected" ?>><?php echo $section['section'] ?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="program_id"><?php echo $this->lang->line('program'); ?></label>
                                        <select id="program_id" name="program_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="class_id"><?php echo $this->lang->line('academic_level'); ?></label>
                                        <select id="class_id" name="class_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <div class="resp">                                
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search') ?></button>   </div>
                    </form>
                    <div class="row">
                        <?php
                        if (isset($student_due_fee)) {
                            ?>
                            <div class="" id="transfee">
                                <div class="box-header ptbnull">
                                    <h3 class="box-title titlefix"><i class="fa fa-users"></i> <?php echo $this->lang->line('balance_fees_statement'); ?></h3>
                                </div>                              
                                <div class="box-body">
                                    <?php
                                    if (!empty($student_due_fee)) {
                                        ?>
<button type="button" class="btn btn-sm btn-info mb10 print" id="load" data-class-id="<?php echo $class_id;?>"  data-section-id="<?php echo $section_id;?>" data-loading-text="<i class='fa fa-spinner fa-spin '></i> Please wait"><i class="fa fa-print"></i> <?php echo $this->lang->line('print') ?> </button>
<div class="clearfix"></div>
                                        <?php
                                        foreach ($student_due_fee as $student_key => $student_value) {
                                            ?>
                                            <div class="row">  
                                                 <div class="col-md-3">
                                                    <label> <?php echo $this->lang->line('admission_no') ?>: </label>
                                               
                                                    <?php echo $student_value['admission_no']; ?>
                                                </div>
                                                <div class="col-md-3">
                                                    <label> <?php echo $this->lang->line('name') ?>: </label>                                                
                                                    <?php echo $this->customlib->getFullName($student_value['firstname'], $student_value['middlename'], $student_value['lastname'], $sch_setting->middlename, $sch_setting->lastname); ?>
                                                </div>
                                                 <div class="col-md-3">
                                                    <label> <?php echo $this->lang->line('father_name') ?>: </label>
                                                    <?php echo $student_value['father_name'] ?>
                                                </div>
                                                <div class="col-md-3">
                                                    <label> <?php echo $this->lang->line('class_section') ?>: </label>
                                               
                                                    <?php echo $student_value['class'] . " (" . $student_value['section'] . ")" ?>
                                                </div>                                                  
                                            </div>
                                            <hr class="mb10 mt10">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-bordered table-hover">
                                                    <thead class="header">
                                                        <tr>                
                                                            <th align="left"><?php echo $this->lang->line('fees_group'); ?></th>
                                                            <th align="left"><?php echo $this->lang->line('fees_code'); ?></th>
                                                            <th align="left" class="text text-left"><?php echo $this->lang->line('due_date'); ?></th>
                                                            <th align="left" class="text text-left"><?php echo $this->lang->line('status'); ?></th>
                                                            <th class="text text-right"><?php echo $this->lang->line('amount') ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>                                  
                                                            <th class="text text-left"><?php echo $this->lang->line('mode'); ?></th>
                                                            <th  class="text text-left"><?php echo $this->lang->line('date'); ?></th>
                                                            <th class="text text-right" ><?php echo $this->lang->line('discount'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                                            <th class="text text-right"><?php echo $this->lang->line('fine'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                                            <th class="text text-right"><?php echo $this->lang->line('paid'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                                            <th class="text text-right"><?php echo $this->lang->line('balance'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $total_amount = 0;
                                                        $total_deposite_amount = 0;
                                                        $total_discount_amount = 0;
                                                        $total_fine_amount = 0;
                                                        $total_fees_fine_amount = 0;
                                                        $total_balance_amount = 0;


                                                        foreach ($student_value['fees_list'] as $fee_key => $fee_value) {
                                                            if (($fee_value->due_date != "0000-00-00" && $fee_value->due_date != NULL) && (strtotime($fee_value->due_date) < strtotime(date('Y-m-d')))) {


                                                                $total_fees_fine_amount+=$fee_value->fine_amount;
                                                            }
                                                            //======================
                                                            $fee_paid = 0;
                                                            $fee_discount = 0;
                                                            $fee_fine = 0;
                                                            $fees_fine_amount = 0;
                                                            if (!empty($fee_value->amount_detail)) {
                                                                $fee_deposits = json_decode(($fee_value->amount_detail));


                                                                foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                                                    $fee_paid += $fee_deposits_value->amount;
                                                                    $fee_discount += $fee_deposits_value->amount_discount;
                                                                    $fee_fine += $fee_deposits_value->amount_fine;
                                                                }
                                                            }
                                                           
 $fee_type_amount=($fee_value->is_system)? $fee_value->previous_amount: $fee_value->amount;
                             $feetype_balance = $fee_type_amount - ($fee_paid + $fee_discount);
                             $total_amount+=$fee_type_amount;
                             $total_discount_amount +=$fee_discount;
                             $total_fine_amount +=$fee_fine;
                             $total_deposite_amount+=$fee_paid;
                             $total_balance_amount+=$feetype_balance;


                                                            //===============================
                                                            ?>
                                                            <tr class="dark-gray">


                                                                <td align="left">
                                                                    <?php
                                                                    if ($fee_value->is_system) {
                                                                        echo $this->lang->line($fee_value->fee_group_name) . " (" . $this->lang->line($fee_value->type) . ")";
                                                                    } else {
                                                                        echo $fee_value->fee_group_name . " (" . $fee_value->type . ")";
                                                                    }
                                                                    ?>
                                                                </td>
                                                                <td align="left">
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
                                                                       
                                                                    } else {


                                                                        echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($fee_value->due_date));
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
                                                                    <?php
                                                                    echo amountFormat($fee_type_amount);
                                                                    if (($fee_value->due_date != "0000-00-00" && $fee_value->due_date != NULL) && (strtotime($fee_value->due_date) < strtotime(date('Y-m-d')))) {
                                                                        ?>
<span data-toggle="popover" class="text text-danger detail_popover"><?php echo " + " . (amountFormat($fee_value->fine_amount)); ?></span>
<div class="fee_detail_popover" style="display: none">
    <?php
    if ($fee_value->fine_amount != "") {
        ?>
        <p class="text text-danger"><?php echo $this->lang->line('fine'); ?></p>
        <?php
    }
    ?>
</div>
                                                                        <?php
                                                                    }
                                                                    ?>
                                                                </td>                                                            
                                                                <td class="text text-left"></td>
                                                                <td class="text text-left"></td>
                                                                <td class="text text-right"><?php
                                                                    echo amountFormat($fee_discount);
                                                                    ?></td>
                                                                <td class="text text-right"><?php
                                                                    echo amountFormat($fee_fine);
                                                                    ?></td>
                                                                <td class="text text-right"><?php
                                                                    echo amountFormat($fee_paid);
                                                                    ?></td>
                                                                <td class="text text-right"><?php
                                                                    $display_none = "ss-none";
                                                                    if ($feetype_balance > 0) {
                                                                        $display_none = "";


                                                                        echo amountFormat($feetype_balance);
                                                                    }
                                                                    ?>
                                                                </td>
                                                            </tr>
                                                           
                                                            <?php
                                                        }
//=================================
                                                        ?>
                                                        <tr class="box box-solid total-bg">


                                                            <td align="left" ></td>
                                                            <td align="left" ></td>
                                                            <td align="left" ></td>
                                                            <td align="left" class="text text-left" ><?php echo $this->lang->line('grand_total'); ?></td>
                                                            <td class="text text-right">
                                                                <?php
                                                                echo $currency_symbol . amountFormat($total_amount);
                                                                ?>


<span data-toggle="popover" class="text text-danger detail_popover"><?php echo " + " . (amountFormat($total_fees_fine_amount)); ?></span>


<div class="fee_detail_popover" style="display: none">
    <?php
    if ($total_fees_fine_amount != "") {
        ?>
        <p class="text text-danger"><?php echo $this->lang->line('fine'); ?></p>
        <?php
    }
    ?>
</div>


                                                            </td>                                                            
                                                            <td class="text text-left"></td>
                                                            <td class="text text-left"></td>
                                                            <td class="text text-right"><?php
                                                                echo ($currency_symbol . amountFormat($total_discount_amount));
                                                                ?></td>
                                                            <td class="text text-right"><?php
                                                                echo ($currency_symbol . amountFormat($total_fine_amount));
                                                                ?></td>
                                                            <td class="text text-right"><?php
                                                                echo ($currency_symbol . amountFormat($total_deposite_amount));
                                                                ?></td>
                                                            <td class="text text-right"><?php
                                                                echo ($currency_symbol . amountFormat($total_balance_amount));
                                                                ?></td>
                                                                 
                                                        </tr>
                                                        <?php
//=================================
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <div class="alert alert-info">
                                           <?php echo $this->lang->line('no_record_found') ; ?>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>                            
                            </div>                
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
    </section>
</div>


<script type="text/javascript">
    $(document).ready(function () {
        var section_id = '<?php echo set_value('section_id') ?>';
        var program_id = '<?php echo set_value('program_id') ?>';
        var class_id = '<?php echo set_value('class_id') ?>';


        // Fetch all sections (faculty)
        $.ajax({
            url: '<?php echo base_url(); ?>sections/getAll',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                var html = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                $.each(response, function (index, section) {
                    var selected = (section_id == section.id) ? 'selected' : '';
                    html += '<option value="' + section.id + '" ' + selected + '>' + section.section + '</option>';
                });
                $('#section_id').html(html);
                if(section_id) {
                    getProgramsBySection(section_id, program_id);
                }
            },
            error: function(xhr, status, error) {
                $('#section_id').html('<option value="">Error loading sections</option>');
            }
        });


        if (class_id && section_id && program_id) {
            getClassesByProgram(program_id, class_id);
        }


        $(document).on('change', '#section_id', function (e) {
            $('#program_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            var section_id = $(this).val();
            if (section_id) {
                getProgramsBySection(section_id);
            }
        });


        $(document).on('change', '#program_id', function (e) {
            $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            var program_id = $(this).val();
            if (program_id) {
                getClassesByProgram(program_id);
            }
        });


        function getProgramsBySection(section_id, selected_program_id = null) {
            if (section_id != "") {
                var base_url = '<?php echo base_url() ?>';
                var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                $.ajax({
                    type: "POST",
                    url: base_url + "admin/timetable/getProgramsBySection",
                    data: {'section_id': section_id},
                    dataType: "json",
                    success: function (data) {
                        $.each(data, function (i, obj) {
                            var sel = "";
                            if (selected_program_id == obj.id) {
                                sel = "selected";
                            }
                            div_data += "<option value=" + obj.id + " " + sel + ">" + obj.title + (obj.code && obj.code !== obj.title ? " (" + obj.code + ")" : "") + "</option>";
                        });
                        $('#program_id').html(div_data);
                        if (selected_program_id) {
                            getClassesByProgram(selected_program_id, class_id);
                        }
                    },
                    error: function() {
                        $('#program_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                    }
                });
            }
        }


        function getClassesByProgram(program_id, selected_class_id = null) {
            if (program_id != "") {
                var base_url = '<?php echo base_url() ?>';
                var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                $.ajax({
                    type: "POST",
                    url: base_url + "admin/timetable/getClassesByProgram",
                    data: {'program_id': program_id},
                    dataType: "json",
                    success: function (data) {
                        $.each(data, function (i, obj) {
                            var sel = "";
                            if (selected_class_id == obj.id) {
                                sel = "selected";
                            }
                            div_data += "<option value=" + obj.id + " " + sel + ">" + obj.class + " - " + obj.degree + "</option>";
                        });
                        $('#class_id').html(div_data);
                    },
                    error: function() {
                        $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                    }
                });
            }
        }


    $('.detail_popover').popover({
        placement: 'right',
        title: '',
        trigger: 'hover',
        container: 'body',
        html: true,
        content: function () {
            return $(this).closest('td').find('.fee_detail_popover').html();
        }
    });


    });

   
   
   $(document).on('click', '.print', function (e) {
   
                var $this = $(this);          
                var class_id=$this.data('classId');
                var section_id=$this.data('sectionId');
  $.ajax({
            type: "POST",
            url: base_url+'financereports/printreportduefees',
            dataType: 'JSON',
            data: {'class_id':class_id,'section_id':section_id}, 
            beforeSend: function () {
                $this.button('loading');
            },
            success: function (response) {
                Popup(response.page);
            },
            error: function (xhr) { // if error occured


                alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");


            },
            complete: function () {
                $this.button('reset');
            }
        });


        e.preventDefault(); // avoid to execute the actual submit of the form.


        });
       
    function Popup(data, winload = false)
    {
        var frame1 = $('<iframe />').attr("id", "printDiv");
        frame1[0].name = "frame1";
        frame1.css({"position": "absolute", "top": "-1000000px"});
        $("body").append(frame1);
        var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow : frame1[0].contentDocument.document ? frame1[0].contentDocument.document : frame1[0].contentDocument;
        frameDoc.document.open();
        //Create a new HTML document.
        frameDoc.document.write('<html>');
        frameDoc.document.write('<head>');
        frameDoc.document.write('<title></title>');
        frameDoc.document.write('</head>');
        frameDoc.document.write('<body>');
        frameDoc.document.write(data);
        frameDoc.document.write('</body>');
        frameDoc.document.write('</html>');
        frameDoc.document.close();
        setTimeout(function () {
        document.getElementById('printDiv').contentWindow.focus();
        document.getElementById('printDiv').contentWindow.print();
        $("#printDiv", top.document).remove();
         
            if (winload) {
                window.location.reload(true);
            }
        }, 500);


        return true;
    }  


</script>


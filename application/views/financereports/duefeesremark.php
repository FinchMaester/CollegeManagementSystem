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
                    <form action="<?php echo site_url('financereports/duefeesremark') ?>"  method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-sm-4 col-lg-4 col-md-4">
                                    <div class="form-group">
                                        <label for="section_id"><?php echo $this->lang->line('faculty'); ?></label>
                                        <select autofocus id="section_id" name="section_id" class="form-control">
                                            <!-- Options will be populated by JS -->
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-4 col-lg-4 col-md-4">
                                    <div class="form-group">
                                        <label for="program_id"><?php echo $this->lang->line('program'); ?></label>
                                        <select id="program_id" name="program_id" class="form-control">
                                            <!-- Options will be populated by JS -->
                                        </select>
                                        <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-4 col-lg-4 col-md-4">
                                    <div class="form-group">
                                        <label for="class_id"><?php echo $this->lang->line('academic_level'); ?></label>
                                        <select id="class_id" name="class_id" class="form-control">
                                            <!-- Options will be populated by JS -->
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
                        if (isset($student_remain_fees)) {
                            ?>
                            <div class="" id="transfee">
                                <div class="box-header ptbnull">
                                    <h3 class="box-title titlefix"><i class="fa fa-users"></i> <?php echo $this->lang->line('balance_fees_report_with_remark'); ?> </h3>
                                </div>                              
                                <div class="box-body">
                                    <?php
                                    if (!empty($student_remain_fees)) {
                                        ?>
                                       
                                    <button type="button" class="btn btn-primary btn-sm pull-right print" id="load" data-class-id="<?php echo $class_id;?>"  data-section-id="<?php echo $section_id;?>" data-loading-text="<i class='fa fa-spinner fa-spin '></i> Please wait"><i class="fa fa-print"></i> <?php echo $this->lang->line('print') ?> </button>
                    <div class="clearfix"></div>


      <div class="table-responsive">
                                                   <table class="table table-striped table-bordered table-hover ">
                                    <thead>
                                        <tr>


                                         
                                            <th><?php echo $this->lang->line('student_name')."<br/>". "(".$this->lang->line('admission_no').")"; ?></th>
                                            <th><?php echo $this->lang->line('class'); ?></th>                                
                                            <th width="30%"><?php echo $this->lang->line('fees'); ?></th>                    
                                            <th class="text text-right"><?php echo $this->lang->line('amount'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                            <th class="text text-right"><?php echo $this->lang->line('deposit'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>        


                                            <th class="text text-right"><?php echo $this->lang->line('balance'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                            <th ><?php echo $this->lang->line('guardian_phone'); ?></th>
                                          <th class="text text-right"><?php echo $this->lang->line('remark'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($student_remain_fees)) {
                                            ?>


                                            <?php
                                        } else {
                                            $count = 1;
                                            foreach ($student_remain_fees as $student) {
                                               
                    $amount=0;
                    $amount_deposite=0;
                    $amount_discount=0;
                    $amount_fine=0;


                                                if(!empty($student['fees'])){
                                                           foreach ($student['fees'] as $fee_key => $fee_value) {
                                                         
                                                             $amount+=$fee_value['amount'];
                                                             $amount_deposite+=$fee_value['amount_deposite'];
                                                             $amount_discount+=$fee_value['amount_discount'];
                                                             $amount_fine+=$fee_value['amount_fine'];
                                                            }                                                        
                                                        }                                              
                                                ?>
                                                <tr>
                                                    <td><?php echo $this->customlib->getFullName($student['firstname'],$student['middlename'],$student['lastname'],$sch_setting->middlename,$sch_setting->lastname) ."<br/>"."(".$student['admission_no'].")";?></td>                                        
                                                    <td><?php echo $student['class']."-".$student['section']; ?></td>                            
                                                    <td>
                                                        <?php  
                                                        if(!empty($student['fees'])){




                                                        echo implode(', <br/>', array_map(
                                                         function ($v) {
                                                           
                                                           return ($v['is_system']) ? $this->lang->line($v['fee_group']) . ' (' . $this->lang->line($v['fee_type']) . ')' :$v['fee_group'] . ' (' . $v['fee_type'] . ' : ' . $v['fee_code'] . ')';
                                                                      },
                                                             $student['fees']));
                                                        }                                                      
                                                       
                                                    ?>
                                                    </td>
                                                    <td class="text text-right"><?php echo amountFormat($amount); ?></td>
                                                    <td class="text text-right"><?php echo amountFormat($amount_deposite+$amount_discount); ?></td>                                                    
                                                    <td class="text text-right"><?php
                                            echo amountFormat(($amount - ($amount_deposite + $amount_discount)));
                                                ?></td>
                                                  <td ><?php
                                            echo $student['guardian_phone'];
                                                ?></td>
                                                  <td class="text text-right">
                                                      <div style="height: 100px; overflow:hidden;">
   
  </div>
                                                  </td>
                                                </tr>
                                                <?php
                                            }
                                            $count++;
                                        }
                                        ?>
                                    </tbody>
                                </table>


                                            </div>
                                        <?php
                                     
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


        // Always repopulate dropdowns on page load
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
            url: base_url+'financereports/printduefeesremark',
            dataType: 'JSON',
            data: {'class_id':class_id,'section_id':section_id}, // serializes the form's elements.
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
            // frame1.remove();
            if (winload) {
                window.location.reload(true);
            }
        }, 500);


        return true;
    }  


</script>


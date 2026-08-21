<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-money"></i> <?php //echo $this->lang->line('fees_collection'); ?> </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <?php $this->load->view('reports/_online_examinations');?>
        <div class="row">
            <div class="col-md-12">
                <div class="box removeboxmius">
                    <div class="box-header ptbnull"></div>
                    <form id='feesforward' action="<?php echo site_url('admin/onlineexam/searchloginvalidation') ?>"  method="post"  accept-charset="utf-8">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                            <div class="box-tools pull-right">
                            </div>
                        </div>
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <?php if ($this->session->flashdata('msg')) {
    ?>
                                        <?php echo $this->session->flashdata('msg');
    $this->session->unset_userdata('msg'); ?>
                                    <?php }?>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('exam') ?><small class="req"> *</small></label>
                                        <select  id="exam_id" name="exam_id" class="form-control select2"  >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
foreach ($examList as $exam_key => $exam_value) {
    ?>
                                                <option value="<?php echo $exam_value->id; ?>"<?php
if (set_value('exam_id') == $exam_value->id) {
        echo "selected=selected";
    }
    ?>><?php echo $exam_value->exam; ?></option>
                                                        <?php
$count++;
}
?>
                                        </select>
                                       <span class="text-danger" id="error_exam_id"></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                        <select id="section_id" name="section_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                        <select id="program_id" name="program_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label> <small class="req"> *</small>
                                        <select id="class_id" name="class_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger" id="error_class_id"></span>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <button type="submit" name="action" value ="search" class="btn btn-primary pull-right btn-sm"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                            <div class="">
                                <div class="box-header ptbnull"></div>
                                <div class="box-header with-border">
                                    <h3 class="box-title titlefix"> <?php echo $this->lang->line('result_report'); ?></h3>
                                </div>
                                <div class="box-body">
                                    <div class="download_label"><?php
echo $this->lang->line('result_report'); ?></div>
                                    <div class="table-responsive">
                                          <table class="table table-striped table-bordered table-hover record-list" data-export-title="<?php echo $this->lang->line('result_report'); ?>">
                                            <thead>
                                                <tr>
                                                    <th><?php echo $this->lang->line('admission_no'); ?></th>
                                                    <th><?php echo $this->lang->line('student_name'); ?></th>
                                                    <th><?php echo $this->lang->line('class'); ?></th>
                                                    <th><?php echo $this->lang->line('total_attempt'); ?></th>
                                                    <th><?php echo $this->lang->line('remaining_attempt'); ?></th>
                                                    <th><?php echo $this->lang->line('exam_submitted'); ?></th>
                                                    <th class="noExport"><?php echo $this->lang->line('action') ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<div id="myModal" class="modal fade" role="dialog">
    <div class="modal-dialog pup100">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line('exam') ?></h4>
            </div>
            <div class="modal-body">
                <div class="result_exam" ></div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('.select2').select2();
        var class_id = $('#class_id').val();
        var section_id = '<?php echo set_value('section_id', 0) ?>';
        var hostel_id = $('#hostel_id').val();
        var hostel_room_id = '<?php echo set_value('hostel_room_id', 0) ?>';
    });

$(document).ready(function () {
    // Store selected values
    var selectedSection = '<?php echo set_value("section_id") ?>';
    var selectedProgram = '<?php echo set_value("program_id") ?>';
    var selectedClass = '<?php echo set_value("class_id") ?>';
    
    // Fetch all sections on page load
    loadSections();
    
    function loadSections() {
        $.ajax({
            url: '<?php echo base_url(); ?>sections/getAll',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                
                $.each(response, function (index, section) {
                    var selected = (selectedSection == section.id) ? 'selected' : '';
                    html += '<option value="' + section.id + '" ' + selected + '>' + section.section + '</option>';
                });
                
                $('#section_id').html(html);
                
                // If there's a pre-selected section, load programs
                if(selectedSection) {
                    loadPrograms(selectedSection);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error loading sections:", error);
            }
        });
    }
    
    function loadPrograms(sectionId) {
        if (!sectionId) return;
        
        $('#program_id').html('<option value="">Loading...</option>');
        $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        
        $.ajax({
            url: '<?php echo base_url(); ?>programs/getBySection', 
            method: 'POST',
            data: {
                section_id: sectionId,
                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function (response) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                
                if(response && response.length > 0) {
                    $.each(response, function (index, program) {
                        var selected = (selectedProgram == program.id) ? 'selected' : '';
                        html += '<option value="' + program.id + '" ' + selected + '>' + program.title + '</option>';
                    });
                } else {
                    html += '<option value="" disabled>No programs found</option>';
                }
                
                $('#program_id').html(html);
                
                // If there's a pre-selected program, load classes
                if(selectedProgram) {
                    loadClasses(sectionId, selectedProgram);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching programs:", error);
                $('#program_id').html('<option value="">Error loading programs</option>');
            }
        });
    }
    
    function loadClasses(sectionId, programId) {
        if (!sectionId || !programId) return;
        
        $('#class_id').html('<option value="">Loading...</option>');
        
        $.ajax({
            url: '<?php echo base_url(); ?>classes/getBySectionAndProgram',
            method: 'POST',
            data: {
                section_id: sectionId,
                program_id: programId,
                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function(response) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                
                if (response && response.length > 0) {
                    $.each(response, function(index, classItem) {
                        var selected = (selectedClass == classItem.id) ? 'selected' : '';
                        html += '<option value="' + classItem.id + '" ' + selected + '>' + 
                               (classItem.degree ? classItem.degree + ' - ' : '') + 
                               classItem.class + '</option>';
                    });
                } else {
                    html += '<option value="" disabled>No classes found for this combination</option>';
                }
                
                $('#class_id').html(html);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                $('#class_id').html('<option value="">Error loading classes</option>');
            }
        });
    }
    
    // Event handlers
    $('#section_id').on('change', function() {
        var sectionId = $(this).val();
        selectedSection = sectionId;
        selectedProgram = '';
        selectedClass = '';
        
        if (sectionId) {
            loadPrograms(sectionId);
        } else {
            $('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
            $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        }
    });
    
    $('#program_id').on('change', function() {
        var programId = $(this).val();
        var sectionId = $('#section_id').val();
        selectedProgram = programId;
        selectedClass = '';
        
        if (sectionId && programId) {
            loadClasses(sectionId, programId);
        } else {
            $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        }
    });
});
</script>

<script type="text/javascript">

    $(document).ready(function () {
        var date_format = '<?php echo $result = strtr($this->customlib->getSchoolDateFormat(), ['d' => 'dd', 'm' => 'mm', 'Y' => 'yyyy']) ?>';
    });

    $(document).on('click', '.student_result', function () {
        var $this = $(this);
        var recordid = $this.data('recordid');
        var examid = $this.data('examid');
        var student_session_id = $this.data('student_session_id');
        $('input[name=recordid]').val(recordid);
        $.ajax({
            type: 'POST',
            url: baseurl + "admin/onlineexam/getstudentresult",
            data: {'recordid': recordid, 'examid': examid,'student_session_id':student_session_id},
            dataType: 'JSON',
            beforeSend: function () {
                $this.button('loading');
            },
            success: function (data) {
                console.log(data.result);
                if (data.status) {
                    $('.result_exam').html(data.result);
                    $('#myModal').modal('show');
                }
                $this.button('reset');
            },
            error: function (xhr) { // if error occured
                alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                $this.button('reset');
            },
            complete: function () {
                $this.button('reset');
            }
        });
    });
</script>

<script type="text/javascript">
   var base_url = '<?php echo base_url() ?>';
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
       frameDoc.document.write('<link rel=\"stylesheet\" href=\"' + base_url + 'backend/dist/css/font-awesome.min.css\" type=\"text/css\" media=\"all\" > ' );
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

    $(document).on('click', '.print_div', function () {
        var $this = $(this);
        $this.button('loading');
        var recordid = $this.data('recordid');
        var examid = $this.data('examid');
        var student_session_id = $this.data('student_session_id');
        $.ajax(
                {
                    url: "<?php echo site_url('admin/onlineexam/getstudentresult') ?>",
                    type: "POST",
                    data: {'recordid': recordid,'examid': examid,'student_session_id':student_session_id,'print':'print'},
                    dataType: 'Json',
                    success: function (data, textStatus, jqXHR)
                    {
                        console.log(data.result);
                         Popup(data.result);
                        $this.button('reset');
                    },
                    error: function (jqXHR, textStatus, errorThrown)
                    {
                        $this.button('reset');
                    }
                });
    });
</script>

<script>
$(document).ready(function() {
     emptyDatatable('record-list','fees_data');

});
</script>

<script type="text/javascript">
$(document).ready(function(){
$(document).on('submit','#feesforward',function(e){
    e.preventDefault(); // avoid to execute the actual submit of the form.
    var $this = $(this).find("button[type=submit]:focus");
    var form = $(this);
    var url = form.attr('action');
    var form_data = form.serializeArray();
    $.ajax({
           url: url,
           type: "POST",
           dataType:'JSON',
           data: form_data, // serializes the form's elements.
              beforeSend: function () {
                $('[id^=error]').html("");
                $this.button('loading');

               },
              success: function(response) { // your success handler
                if(!response.status){
                    $.each(response.error, function(key, value) {
                    $('#error_' + key).html(value);
                    });
                }else{
                   initDatatable('record-list','admin/onlineexam/dtreportlist',response.params);
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
</script>
<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-newspaper-o"></i> <?php //echo $this->lang->line('certificate'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <?php if ($this->session->flashdata('msg')) {?>
            <?php
                echo $this->session->flashdata('msg');
                $this->session->unset_userdata('msg');
            ?>
        <?php }?>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <form role="form" action="<?php echo site_url('admin/generatecertificate/search') ?>" method="post" class="">
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                        <select id="section_id" name="section_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php if (isset($classlist)) {
                                                $sections = array();
                                                foreach ($classlist as $class) {
                                                    if (isset($class['sections'])) {
                                                        foreach ($class['sections'] as $section) {
                                                            $sections[$section['section_id']] = $section['section'];
                                                        }
                                                    }
                                                }
                                                foreach ($sections as $sid => $sname) {
                                                    $selected = (set_value('section_id', isset($section_id) ? $section_id : '') == $sid) ? 'selected' : '';
                                                    echo "<option value=\"$sid\" $selected>$sname</option>";
                                                }
                                            } ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                        <select id="program_id" name="program_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php if (isset($section_id) && $section_id) {
                                                $CI =& get_instance();
                                                $CI->load->model('Program_model');
                                                $programs = $CI->Program_model->get_programs_by_section($section_id);
                                                foreach ($programs as $program) {
                                                    $selected = (set_value('program_id', isset($program_id) ? $program_id : '') == $program['id']) ? 'selected' : '';
                                                    echo "<option value=\"{$program['id']}\" $selected>{$program['title']}</option>";
                                                }
                                            } ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label> <small class="req"> *</small>
                                        <select id="class_id" name="class_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php if (isset($section_id) && isset($program_id) && $section_id && $program_id) {
                                                $CI =& get_instance();
                                                $CI->load->model('Class_model');
                                                $classes = $CI->Class_model->getBySectionAndProgram($section_id, $program_id);
                                                foreach ($classes as $class) {
                                                    $selected = (set_value('class_id', isset($class_id) ? $class_id : '') == $class['id']) ? 'selected' : '';
                                                    $label = ($class['degree'] ? $class['degree'] . ' - ' : '') . $class['class'];
                                                    echo "<option value=\"{$class['id']}\" $selected>$label</option>";
                                                }
                                            } ?>
                                        </select>
                                        <span class="text-danger" id="error_class_id"></span>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('certificate'); ?></label><small class="req"> *</small>
                                        <select name="certificate_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
if (isset($certificateList)) {
    foreach ($certificateList as $list) {
        ?>
                                                    <option value="<?php echo $list->id ?>" <?php if (set_value('certificate_id') == $list->id) {
            echo "selected=selected";
        }
        ?>><?php echo $list->certificate_name ?></option>
                                                    <?php
    }
}
?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('certificate_id'); ?></span>
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

                    <?php
if (isset($resultlist)) {
    ?>
                        <form method="post" action="<?php echo base_url('admin/generatecertificate/generatemultiple') ?>">
                            <div  class="" id="duefee">
                                <div class="box-header ptbnull"></div>
                                <div class="box-header ptbnull">
                                    <h3 class="box-title titlefix"><i class="fa fa-users"></i> <?php echo $this->lang->line('student_list'); ?></h3>
                                    <button  class="btn btn-info btn-sm printSelected pull-right" type="button" name="generate" title="generate multiple certificate"><?php echo $this->lang->line('generate'); ?></button>
                                </div>
                                <div class="box-body table-responsive overflow-visible">
                                    <div class="download_label"><?php echo $this->lang->line('student_list'); ?></div>
                                    <div class="tab-pane active table-responsive no-padding" id="tab_1">
                                        <table class="table table-striped table-bordered table-hover example" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th><input type="checkbox" id="select_all" /></th>
                                                    <th><?php echo $this->lang->line('admission_no'); ?></th>
                                                    <th><?php echo $this->lang->line('student_name'); ?></th>
                                                    <th><?php echo $this->lang->line('class'); ?></th>
                                                    <th><?php echo $this->lang->line('program'); ?></th>
                                                    <th><?php echo $this->lang->line('father_name'); ?></th>
                                                    <th><?php echo $this->lang->line('date_of_birth'); ?></th>
                                                    <th><?php echo $this->lang->line('gender'); ?></th>
                                                    <th><?php echo $this->lang->line('category'); ?></th>
                                                    <th class=""><?php echo $this->lang->line('mobile_number'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
if (empty($resultlist)) {
        ?>
                                                    <?php
} else {
        $count = 1;
        foreach ($resultlist as $student) {
            ?>
<tr>
    <td class="text-center">
        <input type="checkbox" class="checkbox center-block" name="check" data-student_id="<?php echo $student['id'] ?>" value="<?php echo $student['id'] ?>">
        <input type="hidden" name="class_id" value="<?php echo $student['class_id'] ?>">
        <input type="hidden" name="program_id" value="<?php echo isset($student['program_id']) ? $student['program_id'] : ''; ?>" id="program_id">
        <input type="hidden" name="certificate_id" value="<?php echo $certificateResult[0]->id ?>" id="certificate_id">
    </td>
    <td><?php echo $student['admission_no']; ?></td>
    <td>
        <a href="<?php echo base_url(); ?>student/view/<?php echo $student['id']; ?>">
            <?php echo $this->customlib->getFullName($student['firstname'], $student['middlename'], $student['lastname'], $sch_setting->middlename, $sch_setting->lastname); ?>
        </a>
    </td>
    <td><?php echo $student['class'] . "(" . $student['section'] . ")" ?></td>
    <td><?php echo isset($student['program_title']) ? $student['program_title'] : ''; ?></td>
    <td><?php echo $student['father_name']; ?></td>
    <td>
        <?php
            $dob = $student['dob'] ?? '';
            echo ($dob && $dob !== '0000-00-00') ? $dob : '';
            // If you prefer normalized: echo ($dob && $dob!=='0000-00-00') ? date('Y-m-d', strtotime($dob)) : '';
        ?>
    </td>
    <td><?php echo $this->lang->line(strtolower($student['gender'])); ?></td>
    <td><?php echo $student['category']; ?></td>
    <td><?php echo $student['mobileno']; ?></td>
</tr>
                                                        <?php
$count++;
        }
    }
    ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <?php
}
?>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
$(document).ready(function () {
    // Fetch all sections
    $.ajax({
        url: '<?php echo base_url(); ?>sections/getAll',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            var section_id = '<?php echo set_value("section_id", isset($section_id) ? $section_id : "") ?>';
            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';

            $.each(response, function (index, section) {
                var selected = (section_id == section.id) ? 'selected' : '';
                html += '<option value="' + section.id + '" ' + selected + '>' + section.section + '</option>';
            });

            $('#section_id').html(html);

            if(section_id) {
                $('#section_id').trigger('change');
            }
        }
    });

    // Retain program and class values after search
    var program_id = '<?php echo set_value("program_id", isset($program_id) ? $program_id : "") ?>';
    var class_id   = '<?php echo set_value("class_id",   isset($class_id)   ? $class_id   : "") ?>';
    var section_id = '<?php echo set_value("section_id", isset($section_id) ? $section_id : "") ?>';

    if(section_id) {
        setTimeout(function() {
            if(program_id) {
                $('#program_id').val(program_id).trigger('change');
            }
        }, 500);
        setTimeout(function() {
            if(class_id) {
                $('#class_id').val(class_id);
            }
        }, 1000);
    }
});

// When section changes, fetch programs
$(document).on('change', '#section_id', function (e) {
    var section_id = $(this).val();
    $('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
    $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');

    if (section_id != '') {
        $('#program_id').html('<option value="">Loading...</option>');

        $.ajax({
            url: '<?php echo base_url(); ?>programs/getBySection',
            method: 'POST',
            data: {section_id: section_id},
            dataType: 'json',
            success: function (response) {
                var program_id = '<?php echo isset($program_id) ? $program_id : set_value("program_id") ?>';
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

                if(program_id) {
                    $('#program_id').val(program_id).trigger('change');
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

    $('#class_id').html('<option value="">Loading...</option>');

    $.ajax({
        url: '<?php echo base_url(); ?>classes/getBySectionAndProgram',
        method: 'POST',
        data: {
            section_id: section_id,
            program_id: program_id,
            '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
        },
        dataType: 'json',
        success: function(response) {
            var class_id = '<?php echo isset($class_id) ? $class_id : set_value("class_id") ?>';
            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';

            if (response && response.length > 0) {
                $.each(response, function(index, classItem) {
                    var selected = (class_id == classItem.id) ? 'selected' : '';
                    html += '<option value="' + classItem.id + '" ' + selected + '>' +
                           (classItem.degree ? classItem.degree + ' - ' : '') +
                           classItem.class + '</option>';
                });
            } else {
                html += '<option value="" disabled>No classes found for this combination</option>';
            }

            $('#class_id').html(html);
            if(class_id) {
                $('#class_id').val(class_id);
            }
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
// Select-all behavior for the student list
$(document).ready(function(){
	$(document).on('change', '#select_all', function(){
		var checked = $(this).is(':checked');
		$("input[name='check']").prop('checked', checked);
	});

	$(document).on('change', "input[name='check']", function(){
		var total = $("input[name='check']").length;
		var selected = $("input[name='check']:checked").length;
		$('#select_all').prop('checked', total > 0 && selected === total);
	});
});
</script>

<script type="text/javascript">
   $(document).ready(function () {
    $(document).on('click', '.printSelected', function () {
        var array_to_print = [];
        var classId       = $("input[name='class_id']").val();
        var certificateId = $("input[name='certificate_id']").val();
        var programId     = $("input[name='program_id']").val();
        // Pre-open a window in response to the user click to satisfy popup/print gesture policies
        var preOpenedPrintWindow = window.open('', 'cert_print_popup');

        $.each($("input[name='check']:checked"), function () {
            var studentId = $(this).data('student_id');
            if (studentId) {
                var item = {};
                item["student_id"] = studentId;
                array_to_print.push(item);
            }
        });

        console.log('Selected students:', array_to_print);

        if (array_to_print.length == 0) {
            alert("<?php echo $this->lang->line('no_record_selected'); ?>");
            return;
        }

        if (!certificateId) {
            alert("Certificate ID is missing. Please refresh the page and try again.");
            return;
        }

        $('.printSelected').prop('disabled', true).text('Generating...');

        $.ajax({
            url: '<?php echo site_url("admin/generatecertificate/generatemultiple") ?>',
            type: 'post',
            dataType: "html",
            data: {
                'data': JSON.stringify(array_to_print),
                'class_id': classId,
                'certificate_id': certificateId,
                'program_id': programId,
                '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            success: function (response) {
                console.log('Response received:', response);
                try {
                    var jsonResponse = JSON.parse(response);
                    if (jsonResponse.error) {
                        alert('Error: ' + jsonResponse.error);
                        return;
                    }
                } catch (e) {
                    if (response.trim().length > 0) {
                        // Light, targeted tweak: move body text up for Transfer Certificate only
                        var toPrint = tweakCertificateLayout(response);
                        if (preOpenedPrintWindow && !preOpenedPrintWindow.closed) {
                            printIntoWindow(preOpenedPrintWindow, toPrint);
                        } else {
                            Popup(toPrint);
                        }
                    } else {
                        alert('Empty response received from server');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response Text:', xhr.responseText);
                alert('Error generating certificate: ' + error + '\n\nPlease check the browser console for more details.');
            },
            complete: function() {
                $('.printSelected').prop('disabled', false).text('<?php echo $this->lang->line('generate'); ?>');
            }
        });
    });
});
</script>

<script type="text/javascript">
var base_url = '<?php echo base_url() ?>';

// Robust print helper (iframe + image load + fallback)
function Popup(htmlString){
  // Create hidden iframe
  var iframe = document.createElement('iframe');
  iframe.name = 'cert_print_iframe';
  iframe.style.position = 'fixed';
  iframe.style.right = '0';
  iframe.style.bottom = '0';
  iframe.style.width = '0';
  iframe.style.height = '0';
  iframe.style.border = '0';
  document.body.appendChild(iframe);

  var win = iframe.contentWindow || iframe;
  var doc = win.document;

  // Write content into iframe
  doc.open();
  doc.write('<!DOCTYPE html><html><head><meta charset="utf-8">');
  doc.write('<title></title>');
  doc.write('<style>@page{size:auto;margin:10mm}html,body{background:#fff}img{max-width:100%}</style>');
  doc.write('</head><body>');
  doc.write(htmlString);
  doc.write('</body></html>');
  doc.close();

  // Wait for images
  function whenImagesReady(cb){
    try{
      var imgs = doc.images || [];
      var total = imgs.length;
      if(total === 0){ cb(); return; }
      var done = 0;
      for(var i=0;i<total;i++){
        if(imgs[i].complete){
          if(++done===total) cb();
        }else{
          imgs[i].addEventListener('load',  function(){ if(++done===total) cb(); });
          imgs[i].addEventListener('error', function(){ if(++done===total) cb(); });
        }
      }
    }catch(e){ cb(); }
  }

  function doPrint(){
    try{
      win.focus();
      setTimeout(function(){
        win.print();
        setTimeout(function(){ try{ document.body.removeChild(iframe); }catch(_){} }, 500);
      }, 100);
    }catch(e){
      console.error('Iframe print failed, using popup fallback', e);
      fallbackWindowOpen(htmlString);
      try{ document.body.removeChild(iframe); }catch(_){}
    }
  }

  if (doc.readyState === 'complete' || doc.readyState === 'interactive') {
    whenImagesReady(doPrint);
  } else {
    iframe.onload = function(){ whenImagesReady(doPrint); };
  }
}

function fallbackWindowOpen(htmlString){
  var w = window.open('', 'cert_print_popup');
  if(!w){ alert('Please allow pop-ups to print the certificate.'); return; }
  w.document.open();
  w.document.write('<!DOCTYPE html><html><head><meta charset="utf-8">');
  w.document.write('<title></title>');
  w.document.write('<style>@page{size:auto;margin:10mm}html,body{background:#fff}img{max-width:100%}</style>');
  w.document.write('</head><body>');
  w.document.write(htmlString);
  w.document.write('</body></html>');
  w.document.close();
  w.focus();
  setTimeout(function(){ w.print(); w.close(); }, 200);
}

// Print into already opened window (opened from the click handler)
function printIntoWindow(w, htmlString){
  try{
    if(!w || w.closed){ fallbackWindowOpen(htmlString); return; }
    w.document.open();
    w.document.write('<!DOCTYPE html><html><head><meta charset="utf-8">');
    w.document.write('<title></title>');
    w.document.write('<style>@page{size:auto;margin:10mm}html,body{background:#fff}img{max-width:100%}</style>');
    w.document.write('</head><body>');
    w.document.write(htmlString);
    w.document.write('</body></html>');
    w.document.close();
    w.focus();
    setTimeout(function(){ w.print(); w.close(); }, 200);
  }catch(e){
    console.error('printIntoWindow failed, fallback', e);
    fallbackWindowOpen(htmlString);
  }
}
</script>

<script type="text/javascript">
// Inject a minimal CSS override for Transfer Certificate layout
function tweakCertificateLayout(html){
  try{
    if(typeof html !== 'string') return html;
    if(html.indexOf('Transfer Certificate') === -1) return html; // only for that template

    var style = '\n<style>\n  /* Nudge the main body closer to the title without altering template source */\n  .header-center, .header-left, .header-right{ margin-bottom: 6mm; }\n  .body-wrap{ margin-top: 6mm !important; }\n</style>\n';

    // Place override right after <head> or before </head>
    if(html.indexOf('</head>') !== -1){
      return html.replace('</head>', style + '</head>');
    }
    // Otherwise prepend to body
    if(html.indexOf('<body') !== -1){
      return html.replace(/<body[^>]*>/i, function(m){ return m + style; });
    }
    return style + html;
  }catch(_){
    return html;
  }
}
</script>

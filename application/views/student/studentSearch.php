<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<style>
.input-error{ border-color:#a94442 !important; background:#f2dede !important; }
.help-error{ color:#a94442; font-size:12px; margin-top:4px; }
#bulkIdsPanel{ display:none; margin:10px 0 0; }
#bulkIdsPanel .panel{ margin-bottom:10px; }

/* promoted/inactive UI */
.promoted-student { opacity: 0.6; background-color: #f9f9f9 !important; color: #888 !important; }
.promoted-student td { color: #888 !important; }
.promoted-student .btn { cursor: not-allowed !important; opacity: 0.5 !important; }
.promoted-student:hover { background-color: #f5f5f5 !important; }
.promoted-student-card { opacity: 0.6; background-color: #f9f9f9 !important; border: 1px dashed #ddd !important; }
.promoted-student-card .slide-content { color: #888 !important; }
.promoted-student-card .disabled-link { color: #888 !important; text-decoration: none !important; cursor: not-allowed !important; pointer-events: none; }
.promoted-student-card .btn.disabled { cursor: not-allowed !important; opacity: 0.5 !important; pointer-events: none; }
.status-promoted { background-color: #5bc0de; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px; font-weight: normal; }
.status-active { background-color: #5cb85c; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px; font-weight: normal; }
.promoted-student-card .alert-warning { margin-bottom: 10px; padding: 5px 10px; font-size: 11px; }

/* Modal sizing */
#bulkIdsModal .modal-dialog { width: 96%; max-width: 1200px; }
#bulkIdsModal .panel { margin-bottom: 10px; }
</style>

<div class="content-wrapper">
  <section class="content-header"></section>
  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
            <div class="box-tools pull-right">
              <small class="text-muted">
                <i class="fa fa-info-circle"></i>
                <?php echo $this->lang->line('search_includes_both_active_and_promoted_students'); ?>
              </small>
            </div>
          </div>
          <div class="box-body">

<?php if ($this->session->flashdata('msg')) { ?>
  <div class="alert alert-success">
    <?php echo $this->session->flashdata('msg'); $this->session->unset_userdata('msg'); ?>
  </div>
<?php } ?>

<div class="row">
  <form role="form" action="<?php echo site_url('student/searchvalidation') ?>" method="post" class="class_search_form">
    <?php echo $this->customlib->getCSRF(); ?>
    <div class="col-md-6">
      <div class="row">
        <div class="col-sm-6">
          <div class="form-group">
            <label><?php echo $this->lang->line('section'); ?></label> <small class="req"> *</small>
            <select id="section_id" name="section_id" class="form-control">
              <option value=""><?php echo $this->lang->line('select'); ?></option>
            </select>
            <span class="text-danger" id="error_section_id"><?php echo form_error('section_id'); ?></span>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
            <select id="program_id" name="program_id" class="form-control">
              <option value=""><?php echo $this->lang->line('select'); ?></option>
            </select>
            <span class="text-danger" id="error_program_id"><?php echo form_error('program_id'); ?></span>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            <label><?php echo $this->lang->line('class'); ?></label> <small class="req"> *</small>
            <select id="class_id" name="class_id" class="form-control">
              <option value=""><?php echo $this->lang->line('select'); ?></option>
            </select>
            <span class="text-danger" id="error_class_id"></span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="row">
        <div class="col-sm-12">
          <div class="form-group">
            <label><?php echo $this->lang->line('search_by_keyword'); ?></label>
            <input type="text" name="search_text" id="search_text" class="form-control" value="<?php echo set_value('search_text'); ?>" placeholder="<?php echo $this->lang->line('search_by_student_name'); ?>">
            <span class="help-block small"><?php echo $this->lang->line('search_by_student_name'); ?> (<?php echo $this->lang->line('optional'); ?>)</span>
          </div>
        </div>
        <div class="col-sm-12">
          <div class="form-group">
            <button type="submit" name="search" value="search_filter" class="btn btn-primary pull-right btn-sm"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

</div>

<div id="bulkToolbar" class="clearfix" style="margin:10px; display:none;">
  <div id="hemisBulkProgressWrap" style="display:none; margin-bottom:10px;">
    <div class="progress" style="height:22px; margin-bottom:6px;">
      <div id="hemisBulkProgressBar" class="progress-bar progress-bar-warning progress-bar-striped active" role="progressbar" style="width:0%; min-width:2em;">0%</div>
    </div>
    <p id="hemisBulkProgressText" class="text-muted small" style="margin:0;">HEMIS bulk sync idle.</p>
  </div>
  <div class="pull-right">
    <?php
      $hemis_is_mock = isset($hemis_mock_mode) ? (bool) $hemis_mock_mode : true;
    ?>
    <span id="hemisConnectivityBadge" class="label <?php echo $hemis_is_mock ? 'label-warning' : 'label-success'; ?>" style="margin-right:8px; font-weight:600;" title="Outbound HEMIS: <?php echo $hemis_is_mock ? 'no HTTP to UGC (mock IDs)' : 'calls configured UGC / HEMIS API'; ?>">
      <?php echo $hemis_is_mock ? 'Mode: MOCK' : 'Mode: LIVE'; ?>
    </span>
    <button id="btnBulkHemisSync" type="button" class="btn btn-warning btn-sm" style="margin-right:6px;">
      <i class="fa fa-cloud-upload"></i> Bulk Sync to HEMIS
    </button>
    <button id="btnToggleBulkIds" type="button" class="btn btn-success btn-sm">
      <i class="fa fa-hashtag"></i> Bulk Symbol, Reg. No. & Roll No.
    </button>
  </div>
</div>

<div class="nav-tabs-custom border0 navnoshadow">
  <div class="box-header ptbnull"></div>
  <ul class="nav nav-tabs">
    <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true"><i class="fa fa-list"></i> <?php echo $this->lang->line('list_view'); ?></a></li>
    <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false"><i class="fa fa-newspaper-o"></i> <?php echo $this->lang->line('details_view'); ?></a></li>
  </ul>
  <div class="tab-content">
    <div class="tab-pane active table-responsive no-padding overflow-visible" id="tab_1">
      <table class="table table-striped table-bordered table-hover student-list" data-export-title="<?php echo $this->lang->line('student_list'); ?>">
        <thead>
          <tr>
            <th><?php echo $this->lang->line('admission_no'); ?></th>
            <th><?php echo $this->lang->line('student_name'); ?></th>
            <th><?php echo $this->lang->line('section'); ?></th>
            <th><?php echo $this->lang->line('program'); ?></th>
            <th><?php echo $this->lang->line('class'); ?></th>
            <th><?php echo $this->lang->line('gender'); ?></th>
            <th><?php echo $this->lang->line('mobile_number'); ?></th>
            <th><?php echo $this->lang->line('religion'); ?></th>
            <th><?php echo $this->lang->line('caste'); ?></th>
            <th><?php echo $this->lang->line('category'); ?></th>
            <th>HEMIS Status</th>
            <th>HEMIS Id</th>
            <th><?php echo $this->lang->line('status'); ?></th>
            <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>

      <div id="bulkIdsPanel" class="well well-sm"></div>
    </div>

    <div class="tab-pane detail_view_tab" id="tab_2">
      <?php if (empty($resultlist)) { ?>
        <div class="alert alert-info"><?php echo $this->lang->line('no_record_found'); ?></div>
      <?php } else {
        $count = 1;
        foreach ($resultlist as $student) {
          $is_promoted = (isset($student['session_is_active']) && $student['session_is_active'] == 'no');
          $card_class = $is_promoted ? 'promoted-student-card' : '';
          if (empty($student["image"])) {
            if ($student['gender'] == 'Female') { $image = "uploads/student_images/default_female.jpg"; }
            else { $image = "uploads/student_images/default_male.jpg"; }
          } else { $image = $student['image']; }
      ?>
        <div class="carousel-row <?php echo $card_class; ?>" <?php echo $is_promoted ? 'title="This student has been promoted and is inactive in current session"' : ''; ?>>
          <div class="slide-row">
            <div id="carousel-2" class="carousel slide slide-carousel" data-ride="carousel">
              <div class="carousel-inner">
                <div class="item active">
                  <a href="<?php echo base_url(); ?>student/view/<?php echo $student['id'] ?>" <?php echo $is_promoted ? 'class="disabled-link"' : ''; ?>>
                    <?php if ($sch_setting->student_photo) {?><img class="img-responsive img-thumbnail width150" alt="<?php echo $student["firstname"] . " " . $student["lastname"] ?>" src="<?php echo $this->media_storage->getImageURL($image); ?>"><?php }?>
                  </a>
                </div>
              </div>
            </div>
            <div class="slide-content">
              <h4><a href="<?php echo base_url(); ?>student/view/<?php echo $student['id'] ?>" <?php echo $is_promoted ? 'class="disabled-link"' : ''; ?>> <?php echo $this->customlib->getFullName($student['firstname'], $student['middlename'], $student['lastname'], $sch_setting->middlename, $sch_setting->lastname); ?></a></h4>
              <?php if ($is_promoted): ?>
                <div class="alert alert-warning small">
                  <i class="fa fa-info-circle"></i> <?php echo $this->lang->line('student_promoted_read_only'); ?>
                </div>
              <?php endif; ?>
              <div class="row">
                <div class="col-xs-6 col-md-6">
                  <address>
                    <strong><b><?php echo $this->lang->line('program'); ?>: </b><?php echo $student['program_title'] ?></strong><br>
                    <b><?php echo $this->lang->line('admission_no'); ?>: </b><?php echo $student['admission_no'] ?><br/>
                    <b><?php echo $this->lang->line('class'); ?>: </b><?php echo (!empty($student['degree']) ? $student['degree'].' - ' : '').(!empty($student['class']) ? $student['class'] : ''); ?><br>
                    <b><?php echo $this->lang->line('gender'); ?>:&nbsp;</b><?php echo $this->lang->line(strtolower($student['gender'])) ?><br>
                  </address>
                </div>
                <div class="col-xs-6 col-md-6">
                  <b><?php echo $this->lang->line('mobile_number'); ?>:&nbsp;</b><?php echo $student['mobileno'] ?><br>
                  <b><?php echo $this->lang->line('religion'); ?>:&nbsp;</b><?php echo !empty($student['religion_name']) ? $student['religion_name'] : ''; ?><br>

                  <b><?php echo $this->lang->line('caste'); ?>:&nbsp;</b><?php echo !empty($student['caste_name']) ? $student['caste_name'] : ''; ?><br>

                  <b><?php echo $this->lang->line('category'); ?>:&nbsp;</b><?php echo $student['category'] ?><br>
                  <b><?php echo $this->lang->line('current_address'); ?>:&nbsp;</b><?php echo $student['current_address'] ?> <?php echo $student['city'] ?><br>
                </div>
              </div>
            </div>
            <div class="slide-footer">
              <span class="pull-right buttons">
                <?php if ($is_promoted): ?>
                  <span class="btn btn-default btn-xs disabled" data-toggle="tooltip" title="<?php echo $this->lang->line('view_read_only'); ?>"><i class="fa fa-eye"></i></span>
                  <span class="btn btn-default btn-xs disabled" data-toggle="tooltip" title="<?php echo $this->lang->line('edit_disabled_promoted'); ?>"><i class="fa fa-pencil"></i></span>
                  <span class="btn btn-default btn-xs disabled" data-toggle="tooltip" title="<?php echo $this->lang->line('fees_disabled_promoted'); ?>"><?php echo $currency_symbol; ?></span>
                  <span class="label label-info"><?php echo $this->lang->line('promoted'); ?></span>
                <?php else: ?>
                  <a href="<?php echo base_url(); ?>student/view/<?php echo $student['id'] ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('view'); ?>"><i class="fa fa-reorder"></i></a>
                  <?php if ($this->rbac->hasPrivilege('student', 'can_edit')) { ?>
                    <a href="<?php echo base_url(); ?>student/edit/<?php echo $student['id'] ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>"><i class="fa fa-pencil"></i></a>
                  <?php }
                  if ($this->module_lib->hasActive('fees_collection') && $this->rbac->hasPrivilege('collect_fees', 'can_add')) { ?>
                    <a href="<?php echo base_url(); ?>studentfee/addfee/<?php echo !empty($student['student_session_id']) ? (int)$student['student_session_id'] : (int)$student['id']; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="" data-original-title="<?php echo $this->lang->line('add_fees'); ?>"><?php echo $currency_symbol; ?></a>
                  <?php } ?>
                <?php endif; ?>
              </span>
            </div>
          </div>
        </div>
      <?php $count++; } } ?>
    </div>
  </div>
</div>

        </div><!-- ./box -->
      </div>
    </div>
  </section>
</div>

<!-- BULK IDS MODAL -->
<div id="bulkIdsModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="bulkIdsModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="bulkIdsModalLabel"><i class="fa fa-hashtag"></i> Bulk Symbol / Registration / Roll</h4>
      </div>
      <div class="modal-body">

        <div class="row" style="margin-bottom:10px;">
          <div class="col-sm-6">
            <div class="panel panel-default">
              <div class="panel-heading"><strong>Symbol No. Generator</strong></div>
              <div class="panel-body">
                <div class="row">
                  <div class="col-sm-4">
                    <input type="text" id="sym_prefix" class="form-control" placeholder="Prefix (optional)">
                    <p class="help-block" style="margin:4px 0 0;"><small>E.g. <code>SYM-</code> → <code>SYM-001</code></small></p>
                  </div>
                  <div class="col-sm-3"><input type="number" id="sym_start" class="form-control" value="1" min="0" placeholder="Start From"></div>
                  <div class="col-sm-3"><input type="number" id="sym_pad" class="form-control" value="3" min="0" placeholder="Pad Length"></div>
                  <div class="col-sm-2"><button id="btnGenSymbol" class="btn btn-info btn-block"><i class="fa fa-bolt"></i> Generate</button></div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-sm-6">
            <div class="panel panel-default">
              <div class="panel-heading"><strong>Registration No. Generator</strong></div>
              <div class="panel-body">
                <div class="row">
                  <div class="col-sm-4">
                    <input type="text" id="reg_prefix" class="form-control" placeholder="Prefix (optional)">
                    <p class="help-block" style="margin:4px 0 0;"><small>E.g. <code>REG-2025-</code> → <code>REG-2025-0001</code></small></p>
                  </div>
                  <div class="col-sm-3"><input type="number" id="reg_start" class="form-control" value="1" min="0" placeholder="Start From"></div>
                  <div class="col-sm-3"><input type="number" id="reg_pad" class="form-control" value="4" min="0" placeholder="Pad Length"></div>
                  <div class="col-sm-2"><button id="btnGenReg" class="btn btn-info btn-block"><i class="fa fa-bolt"></i> Generate</button></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row" style="margin-bottom:10px;">
          <div class="col-sm-12">
            <div class="panel panel-default">
              <div class="panel-heading"><strong>Roll No. Generator</strong></div>
              <div class="panel-body">
                <div class="row">
                  <div class="col-sm-4">
                    <input type="text" id="roll_prefix" class="form-control" placeholder="Prefix (optional)">
                    <p class="help-block" style="margin:4px 0 0;"><small>E.g. <code>R-</code> → <code>R-1</code></small></p>
                  </div>
                  <div class="col-sm-3"><input type="number" id="roll_start" class="form-control" value="1" min="1" placeholder="Start From"></div>
                  <div class="col-sm-3"><input type="number" id="roll_pad" class="form-control" value="0" min="0" placeholder="Pad Length"></div>
                  <div class="col-sm-2"><button id="btnGenRoll" class="btn btn-info btn-block"><i class="fa fa-bolt"></i> Generate</button></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="table-responsive" style="max-height:60vh; overflow:auto;">
          <table class="table table-striped table-bordered" id="bulkIdsTable">
            <thead>
              <tr>
                <th style="width:70px;">ID</th>
                <th style="width:120px;">Admission No</th>
                <th>Name</th>
                <th style="width:160px;">Class(Section)</th>
                <th style="width:220px;">Symbol No</th>
                <th style="width:220px;">Registration No</th>
                <th style="width:160px;">Roll No</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>

      </div>
      <div class="modal-footer">
        <span id="bulkIdsStatus" class="pull-left text-muted"></span>
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('close'); ?></button>
        <button type="button" id="btnSaveBulkIds" class="btn btn-primary"><i class="fa fa-save"></i> Save All</button>
      </div>
    </div>
  </div>
</div>

<script>
// baseurl
if (typeof window.baseurl === 'undefined' || !window.baseurl) {
  window.baseurl = '<?php echo base_url(); ?>';
}

// init dropdowns
$(function () {
  $.ajax({
    url: '<?php echo base_url(); ?>sections/getAll',
    method: 'GET',
    dataType: 'json',
    success: function (response) {
      var section_id = '<?php echo set_value("section_id") ?>';
      var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
      $.each(response || [], function (i, section) {
        var selected = (section_id == section.id) ? 'selected' : '';
        html += '<option value="' + section.id + '" ' + selected + '>' + section.section + '</option>';
      });
      $('#section_id').html(html);
      if (section_id) $('#section_id').trigger('change');
    }
  });
});

$(document).on('change', '#section_id', function () {
  var section_id = $(this).val();
  $('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
  $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
  if (!section_id) return;

  $('#program_id').html('<option value="">Loading...</option>');
  $.ajax({
    url: '<?php echo base_url(); ?>programs/getBySection',
    method: 'POST',
    data: {section_id: section_id, <?php echo $this->security->get_csrf_token_name(); ?>:'<?php echo $this->security->get_csrf_hash(); ?>'},
    dataType: 'json',
    success: function (response) {
      var program_id = '<?php echo set_value("program_id") ?>';
      var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
      if (response && response.length) {
        $.each(response, function (i, p) {
          var selected = (program_id == p.id) ? 'selected' : '';
          html += '<option value="' + p.id + '" ' + selected + '>' + p.title + '</option>';
        });
      } else {
        html += '<option value="" disabled>No programs found</option>';
      }
      $('#program_id').html(html);
      if (program_id) $('#program_id').trigger('change');
    }
  });
});

$(document).on('change', '#program_id', function () {
  var section_id = $('#section_id').val();
  var program_id = $(this).val();
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
    success: function (resp) {
      var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
      if (resp && resp.length) {
        $.each(resp, function (i, c) {
          html += '<option value="' + c.id + '">' + (c.degree ? c.degree + ' - ' : '') + c.class + '</option>';
        });
      } else {
        html += '<option value="" disabled>No classes found for this combination</option>';
      }
      $('#class_id').html(html);
    }
  });
});

$(function(){ emptyDatatable('student-list','data'); });

$(document).on('submit','.class_search_form',function(e){
  e.preventDefault();
  var $btn = $(".class_search_form button[type=submit]");
  var form = $(this);
  var url  = form.attr('action');
  var data = form.serializeArray();
  var keyword = ($('#search_text').val() || '').trim();
  var searchType = keyword.length > 0 ? 'search_full' : 'search_filter';
  data.push({name:'search_type', value:searchType});

  var hasError = false;
  $('[id^=error]').html("");
  if (keyword.length === 0) {
    if(!$('#section_id').val()){ $('#error_section_id').html('<?php echo $this->lang->line('this_field_is_required'); ?>'); hasError=true; }
    if(!$('#program_id').val()){ $('#error_program_id').html('<?php echo $this->lang->line('this_field_is_required'); ?>'); hasError=true; }
    if(!$('#class_id').val()){   $('#error_class_id').html('<?php echo $this->lang->line('this_field_is_required'); ?>'); hasError=true; }
    if(hasError) return;
  }

  $.ajax({
    url: url, type: "POST", dataType:'JSON', data: data,
    beforeSend: function(){ $btn.button('loading'); },
    success: function(response){
      if(!response.status){
        $.each(response.error, function(k, v){ $('#error_'+k).html(v); });
        $('#bulkToolbar').hide();
        return;
      }
      if ($.fn.DataTable.isDataTable('.student-list')) $('.student-list').DataTable().destroy();
      $('.student-list').DataTable({
        dom: 'Bfrtip',
        buttons: [
          { extend:'copy',  text:'<i class="fa fa-files-o"></i>',  className:"btn-copy",  title: $('.student-list').data("exportTitle"), exportOptions:{columns:[0,1,2,3,4,5,6,7,8,9,10,11]} },
          { extend:'excel', text:'<i class="fa fa-file-excel-o"></i>', className:"btn-excel", title: $('.student-list').data("exportTitle"), exportOptions:{columns:[0,1,2,3,4,5,6,7,8,9,10,11]} },
          { extend:'csv',   text:'<i class="fa fa-file-text-o"></i>', className:"btn-csv",   title: $('.student-list').data("exportTitle"), exportOptions:{columns:[0,1,2,3,4,5,6,7,8,9,10,11]} },
          { extend:'pdf',   text:'<i class="fa fa-file-pdf-o"></i>',  className:"btn-pdf",   title: $('.student-list').data("exportTitle"), exportOptions:{columns:[0,1,2,3,4,5,6,7,8,9,10,11]} },
          { extend:'print', text:'<i class="fa fa-print"></i>',      className:"btn-print", title: $('.student-list').data("exportTitle"),
            customize: function (win) {
              $(win.document.body).find('th').addClass('display').css('text-align','center');
              $(win.document.body).find('table').addClass('display').css('font-size','14px');
              $(win.document.body).find('h1').css('text-align','center');
            },
            exportOptions:{columns:[0,1,2,3,4,5,6,7,8,9,10,11]}
          }
        ],
        columnDefs: [ { targets:-1, orderable:false }, { targets:-2, orderable:false }, { targets:10, orderable:false }, { targets:11, orderable:false } ],
        language: { processing: '<i class="fa fa-spinner fa-spin fa-1x fa-fw"></i><span class="sr-only">Loading...</span> '},
        pageLength: 100, processing:true, serverSide:true,
        createdRow: function (row, data) {
          var last = data[data.length - 1];
          var s = (typeof last === 'string') ? last : '';
          var m = s.match(/student\/edit\/(\d+)/i);
          if (!m) {
            var nameCell = data[1];
            var ns = (typeof nameCell === 'string') ? nameCell : '';
            m = ns.match(/student\/view\/(\d+)/i);
          }
          if (m && m[1]) {
            $(row).attr('data-student-id', m[1]);
          }
        },
        ajax:{
          url: baseurl+"student/dtstudentlist",
          dataSrc: 'data',
          type: "POST",
          data: response.params
        },
        drawCallback: function (settings) {
          $('.detail_view_tab').html("").html(settings.json.student_detail_view);
          var hasRows = settings.json && settings.json.recordsTotal && settings.json.recordsTotal > 0;
          var allChosen = ($('#section_id').val() && $('#program_id').val() && $('#class_id').val());
          if (hasRows && allChosen) { $('#bulkToolbar').show(); } else { $('#bulkToolbar').hide(); }
        }
      });
    },
    complete: function(){ $btn.button('reset'); }
  });
});

// ===== BULK (modal) =====
(function(){
  function padNum(num, width) {
    num = num.toString(); width = parseInt(width,10);
    if (isNaN(width) || width <= 0) return num;
    return num.length >= width ? num : new Array(width - num.length + 1).join('0') + num;
  }
  function markFieldError($input, msg){
    $input.addClass('input-error');
    var $h = $input.siblings('.help-error'); if ($h.length) { $h.text(msg).show(); }
  }
  function clearFieldError($input){
    $input.removeClass('input-error');
    $input.siblings('.help-error').hide().text('');
  }
  function validateUniqueLocal(){
    var symMap={}, regMap={}, rollMap={}, ok=true;
    $('#bulkIdsTable tbody tr').each(function(){
      var $sym=$(this).find('input.sym'), $reg=$(this).find('input.reg'), $roll=$(this).find('input.roll');
      clearFieldError($sym); clearFieldError($reg); clearFieldError($roll);
      var s=$.trim($sym.val()), r=$.trim($reg.val()), rl=$.trim($roll.val());
      if (s!==''){ if (symMap[s]){ ok=false; markFieldError($sym,'Duplicate'); markFieldError(symMap[s],'Duplicate'); } else { symMap[s]=$sym; } }
      if (r!==''){ if (regMap[r]){ ok=false; markFieldError($reg,'Duplicate'); markFieldError(regMap[r],'Duplicate'); } else { regMap[r]=$reg; } }
      if (rl!==''){ if (rollMap[rl]){ ok=false; markFieldError($roll,'Duplicate'); markFieldError(rollMap[rl],'Duplicate'); } else { rollMap[rl]=$roll; } }
    });
    return ok;
  }
  function fillBulkTable(rows){
    var $tbody = $('#bulkIdsTable tbody').empty();
    if(!rows || !rows.length){
      $tbody.append('<tr><td colspan="7" class="text-center text-muted">No students found for current criteria.</td></tr>');
      $('#bulkIdsStatus').text('0 student(s) loaded.'); return;
    }
    $.each(rows, function(i, r){
      var fullName = r.firstname + (r.middlename ? (' ' + r.middlename) : '') + (r.lastname ? (' ' + r.lastname) : '');
      var clsSec = (r.class || '') + (r.section ? ('(' + r.section + ')') : '');
      var tr =
        '<tr data-id="'+r.id+'">'+
          '<td>'+r.id+'</td>'+
          '<td>'+(r.admission_no||'')+'</td>'+
          '<td>'+fullName+'</td>'+
          '<td>'+clsSec+'</td>'+
          '<td><input type="text" class="form-control input-sm sym" value="'+(r.symbol_no||'')+'"><div class="help-error" style="display:none;"></div></td>'+
          '<td><input type="text" class="form-control input-sm reg" value="'+(r.registration_no||'')+'"><div class="help-error" style="display:none;"></div></td>'+
          '<td><input type="text" class="form-control input-sm roll" value="'+(r.roll_no||'')+'"><div class="help-error" style="display:none;"></div></td>'+
        '</tr>';
      $tbody.append(tr);
    });
    $('#bulkIdsStatus').text(rows.length + ' student(s) loaded.');
    validateUniqueLocal();
  }

  function getCsrf() {
    var name = '<?php echo $this->security->get_csrf_token_name(); ?>';
    var hash = '<?php echo $this->security->get_csrf_hash(); ?>';
    var $hidden = $('input[name="<?php echo $this->security->get_csrf_token_name(); ?>"]');
    if ($hidden.length) { hash = $hidden.val() || hash; }
    var obj = {}; obj[name] = hash; return obj;
  }

  // --- helpers: toast, close modal, reload table ---
  function showSuccess(msg){
    var $alert = $(
      '<div class="alert alert-success alert-dismissable fade in" role="alert" style="margin:10px 0; display:none;">' +
        '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
        '<i class="fa fa-check-circle"></i> ' + msg +
      '</div>'
    );
    $('.box-body').first().prepend($alert);
    $alert.slideDown(120);
    setTimeout(function(){ try { $alert.alert('close'); } catch(e){} }, 5000);
  }

  function closeBulkUI(afterClose){
    if ($.fn.modal && $('#bulkIdsModal').is(':visible')) {
      if (afterClose) { $('#bulkIdsModal').one('hidden.bs.modal', afterClose); }
      $('#bulkIdsModal').modal('hide');
    } else {
      $('#bulkIdsPanel').slideUp(120, function(){
        if (typeof afterClose === 'function') afterClose();
      });
    }
  }

  function reloadStudentsTable(){
    if ($.fn.DataTable && $.fn.DataTable.isDataTable('.student-list')) {
      $('.student-list').DataTable().ajax.reload(null, false);
    }
  }
  // --- end helpers ---

  // global holder of DB-used values for the open cohort
  window.bulkUsed = {symbol_no:[], registration_no:[], roll_no:[]};

  $(document).on('click', '#btnToggleBulkIds', function(){
    if ($.fn.modal) { $('#bulkIdsModal').modal('show'); } else { $('#bulkIdsPanel').slideDown(150); }

    var sectionId = $('#section_id').val(),
        programId = $('#program_id').val(),
        classId   = $('#class_id').val();

    if(!sectionId || !programId || !classId){
      alert('Select Section, Program & Class first.');
      $('#bulkIdsStatus').text('Select filters first.');
      return;
    }
    $('#bulkIdsStatus').text('Loading...');

    var payload = Object.assign({
      search_type:'search_filter',
      section_id: sectionId,
      program_id: programId,
      class_id:   classId
    }, getCsrf());

    $.ajax({
      url: (window.baseurl) + 'student/list_for_bulk_ids',
      type: 'POST', dataType: 'json', data: payload,
      success: function(resp){
        if (!resp || !resp.status) {
          alert('Load failed: ' + (resp && resp.message ? resp.message : 'Unknown error'));
          $('#bulkIdsStatus').text('Load failed.');
          return;
        }
        window.bulkUsed = resp.used || {symbol_no:[], registration_no:[], roll_no:[]};
        fillBulkTable(resp.students || []);
      },
      error: function(){ $('#bulkIdsStatus').text('Load failed (network/server).'); alert('Load error.'); }
    });
  });

  $(document).on('input', '#bulkIdsTable input.sym, #bulkIdsTable input.reg, #bulkIdsTable input.roll', function(){ validateUniqueLocal(); });

  // Generators (skip DB-used within current cohort)
  $(document).on('click', '#btnGenSymbol', function(){
    var prefix=$('#sym_prefix').val()||'';
    var start =parseInt($('#sym_start').val(),10); if(isNaN(start)) start=1;
    var pad   =parseInt($('#sym_pad').val(),10);   if(isNaN(pad))   pad=0;
    var used = {};
    (window.bulkUsed.symbol_no||[]).forEach(function(v){ used[v]=true; });
    $('#bulkIdsTable tbody tr input.sym').each(function(){ var v=$.trim($(this).val()); if(v!=='') used[v]=true; });

    $('#bulkIdsTable tbody tr').each(function(idx, el){
      var $inp=$(el).find('input.sym'); var num=start+idx, cand=prefix+padNum(num,pad);
      while (used[cand]) { num++; cand=prefix+padNum(num,pad); }
      $inp.val(cand); used[cand]=true;
    });
    validateUniqueLocal();
  });

  $(document).on('click', '#btnGenReg', function(){
    var prefix=$('#reg_prefix').val()||'';
    var start =parseInt($('#reg_start').val(),10); if(isNaN(start)) start=1;
    var pad   =parseInt($('#reg_pad').val(),10);   if(isNaN(pad))   pad=0;
    var used = {};
    (window.bulkUsed.registration_no||[]).forEach(function(v){ used[v]=true; });
    $('#bulkIdsTable tbody tr input.reg').each(function(){ var v=$.trim($(this).val()); if(v!=='') used[v]=true; });

    $('#bulkIdsTable tbody tr').each(function(idx, el){
      var $inp=$(el).find('input.reg'); var num=start+idx, cand=prefix+padNum(num,pad);
      while (used[cand]) { num++; cand=prefix+padNum(num,pad); }
      $inp.val(cand); used[cand]=true;
    });
    validateUniqueLocal();
  });

  // Roll: sequential 1..N (or from "Start From") while SKIPPING DB-used & in-table used in this cohort
  $(document).on('click', '#btnGenRoll', function(){
    var prefix=$('#roll_prefix').val()||'';
    var start =parseInt($('#roll_start').val(),10); if(isNaN(start) || start<1) start=1;
    var pad   =parseInt($('#roll_pad').val(),10);   if(isNaN(pad))   pad=0;

    var used = {};
    (window.bulkUsed.roll_no||[]).forEach(function(v){ used[v]=true; });          // DB-used for this cohort
    $('#bulkIdsTable tbody tr input.roll').each(function(){                        // in-table current values
      var v=$.trim($(this).val()); if(v!=='') used[v]=true;
    });

    $('#bulkIdsTable tbody tr').each(function(idx, el){
      var $inp=$(el).find('input.roll');
      var num = start + idx;
      var cand = prefix + padNum(num, pad);
      while (used[cand]) { num++; cand = prefix + padNum(num, pad); }             // skip already-used
      $inp.val(cand);
      used[cand]=true;
    });

    validateUniqueLocal();
  });

  $(document).on('click', '#btnSaveBulkIds', function(){
    var $btn = $(this);
    if(!validateUniqueLocal()){ $('#bulkIdsStatus').text('Resolve duplicates highlighted in red.'); return; }

    var items=[];
    $('#bulkIdsTable tbody tr').each(function(){
      items.push({
        id: $(this).data('id'),
        symbol_no: $.trim($(this).find('input.sym').val()),
        registration_no: $.trim($(this).find('input.reg').val()),
        roll_no: $.trim($(this).find('input.roll').val())
      });
    });
    $('#bulkIdsStatus').text('Saving...'); $btn.prop('disabled', true);

    var payload = Object.assign({ items: JSON.stringify(items) }, getCsrf());

    $.ajax({
      url: (window.baseurl) + 'student/bulk_update_symbol_reg',
      type: 'POST', dataType: 'json', data: payload,
      success: function(resp){
        if(resp && resp.status){
          $('#bulkIdsStatus').text('Saved ' + (resp.updated||0) + ' row(s).');

          // Close modal/panel, toast, and refresh the table
          closeBulkUI(function(){
            showSuccess('Saved ' + (resp.updated||0) + ' row(s).');
            reloadStudentsTable();
          });

        } else {
          $('#bulkIdsStatus').text('Please resolve highlighted conflicts.');
          if (resp && resp.errors){
            $('#bulkIdsTable tbody tr input').each(function(){ clearFieldError($(this)); });
            var firstErr = null;
            $.each(resp.errors, function(id, err){
              var $tr = $('#bulkIdsTable tbody tr[data-id="'+id+'"]');
              if(err.symbol_no){ var $i=$tr.find('input.sym'); markFieldError($i, err.symbol_no); if(!firstErr) firstErr=$i; }
              if(err.registration_no){ var $i2=$tr.find('input.reg'); markFieldError($i2, err.registration_no); if(!firstErr) firstErr=$i2; }
              if(err.roll_no){ var $i3=$tr.find('input.roll'); markFieldError($i3, err.roll_no); if(!firstErr) firstErr=$i3; }
            });
            if(firstErr){ firstErr.focus(); }
          } else {
            alert('Save failed.');
          }
        }
      },
      error: function(){ $('#bulkIdsStatus').text('Save failed (network/server).'); },
      complete: function(){ $btn.prop('disabled', false); }
    });
  });
})();

// After edit/activate redirect: re-apply last search filters and reload the table.
(function () {
  var restore = <?php echo json_encode(!empty($restore_student_search) ? $restore_student_search : null); ?>;
  if (!restore) return;

  var kw = (restore.search_text != null ? String(restore.search_text) : '').trim();
  var hasSection = restore.section_id != null && String(restore.section_id) !== '';

  function waitUntil(pred, done, timeoutMs) {
    timeoutMs = timeoutMs || 10000;
    var start = Date.now();
    var t = setInterval(function () {
      if (pred()) {
        clearInterval(t);
        done();
        return;
      }
      if (Date.now() - start > timeoutMs) {
        clearInterval(t);
        done();
      }
    }, 40);
  }

  function submitSearch() {
    $('.class_search_form').trigger('submit');
  }

  $(function () {
    if (!hasSection && kw.length) {
      $('#search_text').val(restore.search_text || '');
      setTimeout(submitSearch, 150);
      return;
    }
    if (!hasSection) return;

    waitUntil(function () {
      return $('#section_id option[value="' + String(restore.section_id) + '"]').length > 0;
    }, function () {
      $('#section_id').val(String(restore.section_id)).trigger('change');
      waitUntil(function () {
        var pid = restore.program_id != null ? String(restore.program_id) : '';
        if ($('#program_id option').length <= 1) return false;
        var txt = ($('#program_id option').first().text() || '');
        if (txt.indexOf('Loading') !== -1) return false;
        if (!pid) return true;
        return $('#program_id option[value="' + pid + '"]').length > 0;
      }, function () {
        if (restore.program_id != null && String(restore.program_id) !== '') {
          $('#program_id').val(String(restore.program_id)).trigger('change');
        }
        waitUntil(function () {
          var cid = restore.class_id != null ? String(restore.class_id) : '';
          if ($('#class_id option').length <= 1) return false;
          var t0 = ($('#class_id option').first().text() || '');
          if (t0.indexOf('Loading') !== -1) return false;
          if (!cid) return true;
          return $('#class_id option[value="' + cid + '"]').length > 0;
        }, function () {
          if (restore.class_id != null && String(restore.class_id) !== '') {
            $('#class_id').val(String(restore.class_id));
          }
          $('#search_text').val(restore.search_text != null ? restore.search_text : '');
          submitSearch();
        });
      });
    });
  });
})();
</script>

<script>
(function () {
  function hemisCsrf() {
    var name = '<?php echo $this->security->get_csrf_token_name(); ?>';
    var hash = '<?php echo $this->security->get_csrf_hash(); ?>';
    var $hidden = $('input[name="' + name + '"]');
    if ($hidden.length) {
      hash = $hidden.val() || hash;
    }
    var o = {};
    o[name] = hash;
    return o;
  }

  var HEMIS_STATUS_COL = 10;

  function hemisBadgeHtml(err, hsid, studentId) {
    var e = (err || '').trim();
    if (e !== '') {
      var t = $('<div/>').text(e).html().replace(/\s+/g, ' ');
      return '<span class="label label-danger hemis-badge-cell" title="' + t + '">Error</span> '
        + '<button type="button" class="btn btn-xs btn-default hemis-retry-sync" data-student-id="' + studentId + '" title="Retry HEMIS sync"><i class="fa fa-refresh"></i></button>';
    }
    if (hsid && String(hsid) !== '' && String(hsid) !== '0') {
      return '<span class="label label-success">Synced</span>';
    }
    return '<span class="text-muted">&mdash;</span>';
  }
  window.hemisBadgeHtml = hemisBadgeHtml;

  var HEMIS_ID_COL = 11;
  function hemisIdCellHtml(hsid) {
    if (hsid && String(hsid) !== '' && String(hsid) !== '0') {
      return '<span class="hemis-id-cell">' + $('<div/>').text(String(hsid)).html() + '</span>';
    }
    return '<span class="text-muted">&mdash;</span>';
  }
  window.hemisIdCellHtml = hemisIdCellHtml;

  $(document).on('click', '.hemis-retry-sync', function (e) {
    e.preventDefault();
    var sid = $(this).data('student-id');
    if (!sid) {
      return;
    }
    var $btn = $(this);
    var $cell = $btn.closest('td');
    $btn.prop('disabled', true);
    $.ajax({
      url: (window.baseurl || '<?php echo base_url(); ?>') + 'student/manual_sync/' + sid,
      type: 'POST',
      dataType: 'json',
      data: hemisCsrf(),
      success: function (resp) {
        if (!resp) {
          return;
        }
        var err = resp.hemis_sync_error != null ? String(resp.hemis_sync_error) : '';
        var hsid = resp.hemis_student_id != null ? resp.hemis_student_id : '';
        $cell.html(hemisBadgeHtml(err, hsid, sid));
        $cell.closest('tr').find('td').eq(HEMIS_ID_COL).html(hemisIdCellHtml(hsid));
      },
      error: function () {
        alert('HEMIS sync request failed.');
      },
      complete: function () {
        $btn.prop('disabled', false);
      }
    });
  });

  function hemisUpdateTableRowHemisCell(studentId, resp) {
    if (!resp) {
      return;
    }
    var err = resp.hemis_sync_error != null ? String(resp.hemis_sync_error) : '';
    var hsid = resp.hemis_student_id != null ? resp.hemis_student_id : '';
    var $tr = $('.student-list tbody tr[data-student-id="' + studentId + '"]');
    if ($tr.length) {
      $tr.find('td').eq(HEMIS_STATUS_COL).html(window.hemisBadgeHtml(err, hsid, studentId));
      $tr.find('td').eq(HEMIS_ID_COL).html(window.hemisIdCellHtml(hsid));
    }
  }

  function hemisDelay(ms) {
    var d = $.Deferred();
    setTimeout(function () {
      d.resolve();
    }, ms);
    return d.promise();
  }

  $(document).on('click', '#btnBulkHemisSync', function () {
    var $b = $(this);
    if (!confirm('Queue HEMIS sync for all eligible students in the current session? Each student is synced one request at a time in the browser (avoids PHP timeouts). If UGC returns gateway errors three times in a row, the queue stops automatically.')) {
      return;
    }
    $b.prop('disabled', true);
    $('#hemisBulkProgressWrap').show();
    $('#hemisBulkProgressBar').addClass('active').css('width', '0%').text('0%');
    $('#hemisBulkProgressText').text('Fetching queue…');

    var base = (window.baseurl || '<?php echo base_url(); ?>');

    $.ajax({
      url: base + 'student/hemis_sync_queue_ids',
      type: 'POST',
      dataType: 'json',
      data: hemisCsrf()
    }).done(function (queue) {
      if (!queue || !queue.ids || !queue.ids.length) {
        $('#hemisBulkProgressText').text('No students in queue (or HEMIS columns missing).');
        $b.prop('disabled', false);
        return;
      }
      var ids = queue.ids;
      var total = ids.length;
      var processed = 0;
      var failed = 0;
      var consecutiveServerBusy = 0;
      var aborted = false;
      var pauseMs = 120;

      function setProgress() {
        var pct = total ? Math.round((processed / total) * 100) : 100;
        $('#hemisBulkProgressBar').css('width', pct + '%').text(pct + '%');
        $('#hemisBulkProgressText').text('Syncing ' + processed + '/' + total + '… (' + failed + ' failed)');
      }

      function isServerBusy(resp, jqXHR) {
        if (resp && resp.server_busy) {
          return true;
        }
        if (jqXHR && (jqXHR.status === 502 || jqXHR.status === 503 || jqXHR.status === 504)) {
          return true;
        }
        return false;
      }

      function finishBulk(msg) {
        $('#hemisBulkProgressText').text(msg || ('Finished: ' + processed + '/' + total + ' processed, ' + failed + ' failed.'));
        $('#hemisBulkProgressBar').removeClass('active');
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('.student-list')) {
          $('.student-list').DataTable().ajax.reload(null, false);
        }
        $b.prop('disabled', false);
      }

      function processNext(i) {
        if (aborted) {
          finishBulk('Stopped after repeated UGC gateway errors.');
          return;
        }
        if (i >= ids.length) {
          finishBulk();
          return;
        }
        var sid = ids[i];
        $('#hemisBulkProgressText').text('Syncing ' + (i + 1) + '/' + total + '… (' + failed + ' failed)');
        $.ajax({
          url: base + 'student/manual_sync/' + sid,
          type: 'POST',
          dataType: 'json',
          data: hemisCsrf()
        }).done(function (resp) {
          processed++;
          if (!resp || !resp.success) {
            failed++;
          }
          if (isServerBusy(resp, null)) {
            consecutiveServerBusy++;
            if (consecutiveServerBusy >= 3) {
              aborted = true;
              window.alert('UGC / HEMIS appears overloaded (3 consecutive server-busy responses). Bulk sync paused. Try again later.');
              hemisUpdateTableRowHemisCell(sid, resp || {});
              setProgress();
              finishBulk('Stopped: UGC gateway busy (3 consecutive).');
              return;
            }
          } else {
            consecutiveServerBusy = 0;
          }
          hemisUpdateTableRowHemisCell(sid, resp || {});
          setProgress();
          hemisDelay(pauseMs).then(function () {
            processNext(i + 1);
          });
        }).fail(function (jqXHR) {
          processed++;
          failed++;
          if (isServerBusy(null, jqXHR)) {
            consecutiveServerBusy++;
            if (consecutiveServerBusy >= 3) {
              aborted = true;
              window.alert('UGC / HEMIS appears overloaded (3 consecutive gateway HTTP errors). Bulk sync paused.');
              setProgress();
              finishBulk('Stopped: UGC gateway busy (3 consecutive).');
              return;
            }
          } else {
            consecutiveServerBusy = 0;
          }
          hemisUpdateTableRowHemisCell(sid, { hemis_sync_error: 'Request failed', success: false });
          setProgress();
          hemisDelay(pauseMs).then(function () {
            processNext(i + 1);
          });
        });
      }

      setProgress();
      processNext(0);
    }).fail(function () {
      $('#hemisBulkProgressText').text('Failed to load sync queue.');
      $b.prop('disabled', false);
    });
  });
})();
</script>

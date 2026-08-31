<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
$hemis_ck = (isset($import_hemis_checklist) && is_array($import_hemis_checklist)) ? $import_hemis_checklist : array();
$hemis_ready = !empty($hemis_ck['ready']);
$import_schema = (isset($import_schema) && is_array($import_schema)) ? $import_schema : array('groups' => array(), 'sample_rows' => array());
?>
<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-upload"></i> <?php echo $this->lang->line('import_student'); ?></h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('import_student'); ?></h3>
                        <div class="pull-right box-tools">
                            <a href="<?php echo site_url('student/exportformat'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-download"></i> <?php echo $this->lang->line('download_sample_import_file'); ?></a>
                        </div>
                    </div>
                    <div class="box-body">

                        <?php
                        $import_res = $this->session->flashdata('staff_import_result');
                        if ($import_res !== false && is_array($import_res)) {
                            $success_n = isset($import_res['success_count']) ? (int) $import_res['success_count'] : 0;
                            $total_n = isset($import_res['total_rows']) ? (int) $import_res['total_rows'] : 0;
                            $failures = isset($import_res['failures']) && is_array($import_res['failures']) ? $import_res['failures'] : array();
                            $fail_n = count($failures);
                            if ($total_n === 0) {
                                echo '<div class="alert alert-info">' . $this->lang->line('no_record_found') . '</div>';
                            } else {
                                $summary_class = ($fail_n === 0) ? 'alert-success' : (($success_n === 0) ? 'alert-danger' : 'alert-warning');
                                $summary_text = ($success_n === $total_n && $fail_n === 0)
                                    ? $this->lang->line('staff_import_all_success')
                                    : sprintf($this->lang->line('staff_import_summary'), $success_n, $total_n);
                                echo '<div class="alert ' . $summary_class . '">' . htmlspecialchars($summary_text, ENT_QUOTES, 'UTF-8') . '</div>';
                                if ($fail_n > 0) {
                                    echo '<div class="table-responsive"><table class="table table-bordered table-condensed table-striped"><thead><tr><th>#</th><th>' . $this->lang->line('admission_no') . '</th><th>' . $this->lang->line('name') . '</th><th>' . $this->lang->line('reason') . '</th></tr></thead><tbody>';
                                    foreach ($failures as $frow) {
                                        echo '<tr><td>' . (int) $frow['row'] . '</td><td>' . htmlspecialchars(isset($frow['admission_no']) ? $frow['admission_no'] : '', ENT_QUOTES, 'UTF-8') . '</td>';
                                        echo '<td>' . htmlspecialchars(isset($frow['name']) ? $frow['name'] : '', ENT_QUOTES, 'UTF-8') . '</td>';
                                        echo '<td>' . htmlspecialchars(isset($frow['reason']) ? $frow['reason'] : '', ENT_QUOTES, 'UTF-8') . '</td></tr>';
                                    }
                                    echo '</tbody></table></div>';
                                }
                            }
                        } elseif ($this->session->flashdata('msg')) {
                            echo '<div>' . $this->session->flashdata('msg') . '</div>';
                        }
                        ?>

                        <!-- HEMIS preflight (compact) -->
                        <div class="panel panel-default" id="student-import-hemis-panel">
                            <div class="panel-heading clearfix">
                                <a data-toggle="collapse" href="#import-hemis-body" class="collapsed" style="color:inherit;">
                                    <strong><i class="fa fa-check-square-o"></i> <?php echo $this->lang->line('student_import_hemis_checklist_title'); ?></strong>
                                </a>
                                <button type="button" id="btn-import-refresh-metadata" class="btn btn-info btn-xs pull-right">
                                    <i class="fa fa-refresh"></i> <?php echo $this->lang->line('student_import_refresh_metadata'); ?>
                                </button>
                            </div>
                            <div id="import-hemis-body" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <div id="import-hemis-status-banner" class="alert <?php echo $hemis_ready ? 'alert-success' : 'alert-warning'; ?>" style="margin-bottom:10px;padding:8px 12px;">
                                        <?php echo $this->lang->line($hemis_ready ? 'student_import_all_ready' : 'student_import_not_ready'); ?>
                                    </div>
                                    <div id="import-hemis-cache-badges" style="margin-bottom:12px;">
                                        <?php
                                        if (!empty($hemis_ck['items'])) {
                                            foreach ($hemis_ck['items'] as $item) {
                                                $cls = !empty($item['ok']) ? 'label-success' : 'label-danger';
                                                echo '<span class="label ' . $cls . '" style="margin-right:6px;margin-bottom:4px;display:inline-block;">';
                                                echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') . ' (' . (int) $item['count'] . ')';
                                                echo '</span>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <p class="text-muted small" style="margin-bottom:12px;"><?php echo $this->lang->line('student_import_no_local_address_cols'); ?></p>

                                    <ul class="nav nav-tabs" role="tablist">
                                        <li class="active"><a href="#import-tab-ids" data-toggle="tab"><?php echo $this->lang->line('student_import_lookup_tab_ids'); ?></a></li>
                                        <li><a href="#import-tab-admission" data-toggle="tab"><?php echo $this->lang->line('student_import_lookup_tab_admission'); ?></a></li>
                                    </ul>
                                    <p class="text-muted small" style="margin:8px 0 0;">
                                        <?php echo $this->lang->line('student_import_address_panel_hint'); ?>
                                        <a href="#import-address-panel" class="btn btn-success btn-xs" style="margin-left:6px;"><i class="fa fa-map-marker"></i> <?php echo $this->lang->line('student_import_lookup_tab_address'); ?></a>
                                    </p>
                                    <div class="tab-content" style="padding-top:12px;">
                                        <div class="tab-pane active" id="import-tab-ids">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <h5 class="mt0">ugc_program_id</h5>
                                                    <div class="table-responsive" style="max-height:180px;">
                                                        <table class="table table-condensed table-bordered" style="font-size:12px;">
                                                            <tbody id="import-ref-programs">
                                                            <?php if (!empty($hemis_ck['programs_ref'])) { foreach ($hemis_ck['programs_ref'] as $pr) { ?>
                                                                <tr class="import-ref-row" style="cursor:pointer" title="Click to copy ID">
                                                                    <td width="50"><code><?php echo (int) $pr['id']; ?></code></td>
                                                                    <td><?php echo htmlspecialchars($pr['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                                </tr>
                                                            <?php } } ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <h5 class="mt0">ugc_batch_id</h5>
                                                    <div class="table-responsive" style="max-height:180px;">
                                                        <table class="table table-condensed table-bordered" style="font-size:12px;">
                                                            <tbody id="import-ref-batches">
                                                            <?php if (!empty($hemis_ck['batches_ref'])) { foreach ($hemis_ck['batches_ref'] as $br) { ?>
                                                                <tr class="import-ref-row" style="cursor:pointer">
                                                                    <td width="50"><code><?php echo (int) $br['id']; ?></code></td>
                                                                    <td><?php echo htmlspecialchars($br['name'], ENT_QUOTES, 'UTF-8'); ?><?php if (!empty($br['batch_nepali'])) { echo ' <small>(' . htmlspecialchars($br['batch_nepali'], ENT_QUOTES, 'UTF-8') . ')</small>'; } ?></td>
                                                                </tr>
                                                            <?php } } ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <h5 class="mt0">ugc_fiscal_year_id</h5>
                                                    <div class="table-responsive" style="max-height:180px;">
                                                        <table class="table table-condensed table-bordered" style="font-size:12px;">
                                                            <tbody id="import-ref-fiscal">
                                                            <?php if (!empty($hemis_ck['fiscal_ref'])) { foreach ($hemis_ck['fiscal_ref'] as $fr) { ?>
                                                                <tr class="import-ref-row" style="cursor:pointer">
                                                                    <td width="50"><code><?php echo (int) $fr['id']; ?></code></td>
                                                                    <td><?php echo htmlspecialchars($fr['year_nepali'], ENT_QUOTES, 'UTF-8'); ?><?php if (!empty($fr['active_fiscal_year'])) { echo ' <span class="label label-success">active</span>'; } ?></td>
                                                                </tr>
                                                            <?php } } ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <?php if (!empty($hemis_ck['categories_ref'])) { ?>
                                                    <h5><?php echo $this->lang->line('student_import_category_id'); ?></h5>
                                                    <div class="table-responsive" style="max-height:120px;">
                                                        <table class="table table-condensed table-bordered" style="font-size:12px;">
                                                            <tbody id="import-ref-categories">
                                                            <?php foreach ($hemis_ck['categories_ref'] as $cat) { ?>
                                                                <tr class="import-ref-row" style="cursor:pointer">
                                                                    <td width="50"><code><?php echo (int) $cat['id']; ?></code></td>
                                                                    <td><?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                                </tr>
                                                            <?php } ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="import-tab-admission">
                                            <p class="text-muted small"><?php echo $this->lang->line('student_import_batch_admission_help'); ?></p>
                                            <?php if (!empty($hemis_ck['batches_ref'])) { ?>
                                            <table class="table table-condensed table-bordered" style="font-size:12px;max-width:520px;">
                                                <thead><tr><th>ugc_batch_id</th><th>Batch</th><th>B.S. year</th></tr></thead>
                                                <tbody>
                                                <?php foreach ($hemis_ck['batches_ref'] as $br) { ?>
                                                    <tr class="import-ref-row" style="cursor:pointer" data-copy="<?php echo (int) $br['id']; ?>">
                                                        <td><code><?php echo (int) $br['id']; ?></code></td>
                                                        <td><?php echo htmlspecialchars($br['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo !empty($br['batch_nepali']) ? htmlspecialchars($br['batch_nepali'], ENT_QUOTES, 'UTF-8') : '—'; ?></td>
                                                    </tr>
                                                <?php } ?>
                                                </tbody>
                                            </table>
                                            <?php } else { ?>
                                            <p class="text-warning small"><?php echo $this->lang->line('student_import_batch_admission_empty'); ?></p>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <p id="import-metadata-refresh-log" class="small text-muted" style="display:none;margin-top:10px;margin-bottom:0;"></p>
                                </div>
                            </div>
                        </div>

                        <!-- UGC address lookup (always visible) -->
                        <div class="panel panel-success" id="import-address-panel">
                            <div class="panel-heading">
                                <strong><i class="fa fa-map-marker"></i> <?php echo $this->lang->line('student_import_lookup_tab_address'); ?></strong>
                                <small class="text-muted"> — <?php echo $this->lang->line('student_import_address_lookup_help'); ?></small>
                            </div>
                            <div class="panel-body">
                                <div class="well well-sm" id="import-address-builder" style="margin-bottom:14px;background:#fafafa;">
                                    <h5 class="mt0"><?php echo $this->lang->line('student_import_address_builder_title'); ?></h5>
                                    <div id="import-address-load-status" class="text-muted small" style="margin-bottom:8px;"><?php echo $this->lang->line('student_import_address_loading'); ?></div>
                                    <div class="btn-group btn-group-sm" data-toggle="buttons" style="margin-bottom:10px;">
                                        <label class="btn btn-default active">
                                            <input type="radio" name="import_addr_prefix" value="ugc_p" checked> <?php echo $this->lang->line('student_import_address_builder_permanent'); ?>
                                        </label>
                                        <label class="btn btn-default">
                                            <input type="radio" name="import_addr_prefix" value="ugc_t"> <?php echo $this->lang->line('student_import_address_builder_temporary'); ?>
                                        </label>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label><?php echo $this->lang->line('province'); ?></label>
                                            <select id="import_addr_province" class="form-control input-sm" disabled>
                                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            </select>
                                        </div>
                                        <div class="col-sm-4">
                                            <label><?php echo $this->lang->line('district'); ?></label>
                                            <select id="import_addr_district" class="form-control input-sm" disabled>
                                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            </select>
                                        </div>
                                        <div class="col-sm-4">
                                            <label><?php echo $this->lang->line('permanent_local_level'); ?></label>
                                            <select id="import_addr_local_level" class="form-control input-sm" disabled>
                                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <p class="text-muted small" style="margin:8px 0 0;"><?php echo $this->lang->line('student_import_address_only_code_tip'); ?></p>
                                    <div id="import-address-csv-preview" class="alert alert-success" style="display:none;margin-top:10px;margin-bottom:0;padding:8px 12px;font-size:12px;">
                                        <strong><?php echo $this->lang->line('student_import_address_builder_result'); ?>:</strong>
                                        <table class="table table-condensed" style="margin:8px 0 0;background:transparent;">
                                            <tbody id="import-address-csv-fields"></tbody>
                                        </table>
                                        <button type="button" class="btn btn-primary btn-xs" id="btn-import-address-copy-all"><i class="fa fa-copy"></i> <?php echo $this->lang->line('student_import_address_copy_all'); ?></button>
                                    </div>
                                </div>

                                <h5 class="text-muted" style="margin-top:0;"><?php echo $this->lang->line('student_import_address_search_title'); ?></h5>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <label><?php echo $this->lang->line('student_import_address_district_filter'); ?></label>
                                        <select id="import_address_district" class="form-control input-sm">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php if (!empty($hemis_ck['districts_ref'])) { foreach ($hemis_ck['districts_ref'] as $dr) {
                                                $dn = isset($dr['district']) ? $dr['district'] : '';
                                                if ($dn === '') { continue; }
                                                echo '<option value="' . htmlspecialchars($dn, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($dn, ENT_QUOTES, 'UTF-8') . '</option>';
                                            } } ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-5">
                                        <label><?php echo $this->lang->line('student_import_address_search'); ?></label>
                                        <input type="text" id="import_address_q" class="form-control input-sm" placeholder="Municipality or combined code" />
                                    </div>
                                    <div class="col-sm-2">
                                        <label>&nbsp;</label>
                                        <button type="button" class="btn btn-default btn-sm btn-block" id="btn-import-address-search">Search</button>
                                    </div>
                                </div>
                                <p class="text-muted small" style="margin-top:6px;"><?php echo $this->lang->line('student_import_copy_hint'); ?></p>
                                <div class="table-responsive" style="max-height:180px;margin-top:8px;">
                                    <table class="table table-condensed table-bordered table-hover" style="font-size:12px;">
                                        <thead>
                                            <tr>
                                                <th>Code</th>
                                                <th>Province</th>
                                                <th>District</th>
                                                <th>Local level</th>
                                            </tr>
                                        </thead>
                                        <tbody id="import-address-results">
                                            <tr><td colspan="4" class="text-muted text-center"><?php echo $this->lang->line('select'); ?> district or search</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Quick guide -->
                        <div class="alert alert-warning" style="margin-bottom:8px;padding:8px 12px;font-size:13px;">
                            <strong>Nepali names:</strong> Use <strong>Save As → CSV UTF-8 (Comma delimited)</strong> in Excel (not plain CSV). Columns: <code>firstname_np</code> / <code>firstname_</code>, <code>lastname_np</code> / <code>lastname_</code>. If import is rejected, open the saved file in Notepad — you must see Nepali letters, not <code>????</code>.
                        </div>
                        <div class="alert alert-info" style="margin-bottom:12px;padding:10px 14px;">
                            <strong><?php echo $this->lang->line('student_import_guide_title'); ?>:</strong>
                            <?php echo $this->lang->line('student_import_guide_text'); ?>
                            <a href="#import-address-panel" class="btn btn-success btn-xs"><i class="fa fa-map-marker"></i> <?php echo $this->lang->line('student_import_lookup_tab_address'); ?></a>
                            <a href="<?php echo site_url('hemisintegration'); ?>">HEMIS settings</a>.
                        </div>

                        <!-- CSV columns (grouped, vertical) -->
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <a data-toggle="collapse" href="#import-columns-body" class="collapsed" style="color:inherit;">
                                    <strong><i class="fa fa-table"></i> <?php echo $this->lang->line('student_import_columns_title'); ?></strong>
                                    <small class="text-muted"> — matches downloaded sample</small>
                                </a>
                            </div>
                            <div id="import-columns-body" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <div class="row">
                                        <?php
                                        if (!empty($import_schema['groups'])) {
                                            foreach ($import_schema['groups'] as $grp) {
                                                echo '<div class="col-md-6 col-lg-4" style="margin-bottom:14px;">';
                                                echo '<h5 style="margin-top:0;border-bottom:1px solid #eee;padding-bottom:4px;">' . htmlspecialchars($grp['label'], ENT_QUOTES, 'UTF-8') . '</h5>';
                                                echo '<table class="table table-condensed table-bordered" style="font-size:11px;margin-bottom:0;">';
                                                echo '<thead><tr><th>Column</th><th></th><th>Example</th></tr></thead><tbody>';
                                                $s0 = !empty($import_schema['sample_rows'][0]) ? $import_schema['sample_rows'][0] : array();
                                                foreach ($grp['columns'] as $coldef) {
                                                    $req = !empty($coldef['req']) ? '<span class="text-red">*</span>' : '';
                                                    $ex = isset($s0[$coldef['col']]) ? $s0[$coldef['col']] : '';
                                                    if (strlen($ex) > 28) {
                                                        $ex = substr($ex, 0, 25) . '…';
                                                    }
                                                    echo '<tr><td><code>' . htmlspecialchars($coldef['col'], ENT_QUOTES, 'UTF-8') . '</code></td>';
                                                    echo '<td>' . $req . '</td>';
                                                    echo '<td class="text-muted">' . htmlspecialchars($ex, ENT_QUOTES, 'UTF-8') . '</td></tr>';
                                                }
                                                echo '</tbody></table></div>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr style="margin:15px 0;" />

                        <form action="<?php echo site_url('student/import'); ?>" id="employeeform" method="post" enctype="multipart/form-data">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                        <select id="section_id" name="section_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                        <select id="program_id" name="program_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label><small class="req"> *</small>
                                        <select id="class_id" name="class_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="import_csv_file"><?php echo $this->lang->line('select_csv_file'); ?></label><small class="req"> *</small>
                                        <div>
                                            <input class="filestyle form-control" type="file" name="file" id="import_csv_file" accept=".csv,text/csv" data-height="120" />
                                        </div>
                                        <p class="help-block text-muted" style="margin-top:6px;"><small><i class="fa fa-file-excel-o"></i> .csv, UTF-8</small></p>
                                        <span class="text-danger"><?php echo form_error('file'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4 pt20">
                                    <button type="submit" class="btn btn-info btn-block"><?php echo $this->lang->line('import_student'); ?></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
var baseUrl = '<?php echo base_url(); ?>';
var baseurl = baseUrl;
var importLang = {
    cacheOk: <?php echo json_encode(sprintf($this->lang->line('student_import_cache_ok'), '%d')); ?>,
    cacheMissing: <?php echo json_encode($this->lang->line('student_import_cache_missing')); ?>,
    allReady: <?php echo json_encode($this->lang->line('student_import_all_ready')); ?>,
    notReady: <?php echo json_encode($this->lang->line('student_import_not_ready')); ?>,
    refreshing: <?php echo json_encode($this->lang->line('student_import_metadata_refreshing')); ?>,
    refreshed: <?php echo json_encode($this->lang->line('student_import_metadata_refreshed')); ?>,
    refreshFailed: <?php echo json_encode($this->lang->line('student_import_metadata_refresh_failed')); ?>,
    addressEmpty: <?php echo json_encode('No matches. Refresh metadata or try another district.'); ?>,
    addressLoading: <?php echo json_encode($this->lang->line('student_import_address_loading')); ?>,
    addressLoadFailed: <?php echo json_encode($this->lang->line('student_import_address_load_failed')); ?>,
    addressCopyAll: <?php echo json_encode($this->lang->line('student_import_address_copy_all')); ?>,
    addressCopyField: <?php echo json_encode($this->lang->line('student_import_address_copy_field')); ?>,
    selectOpt: <?php echo json_encode($this->lang->line('select')); ?>
};

var importUgcAddressRows = [];
var importAddressRowsLoaded = false;

function importEscapeHtml(s) {
    return $('<div/>').text(s == null ? '' : String(s)).html();
}

function importRenderCacheBadges(items) {
    var $wrap = $('#import-hemis-cache-badges').empty();
    if (!items || !items.length) {
        return;
    }
    $.each(items, function (_, item) {
        var cls = item.ok ? 'label-success' : 'label-danger';
        $wrap.append('<span class="label ' + cls + '" style="margin-right:6px;margin-bottom:4px;display:inline-block;">'
            + importEscapeHtml(item.label) + ' (' + (parseInt(item.count, 10) || 0) + ')</span>');
    });
}

function importRenderHemisChecklist(checklist) {
    if (!checklist) {
        return;
    }
    $('#import-hemis-status-banner').removeClass('alert-success alert-warning')
        .addClass(checklist.ready ? 'alert-success' : 'alert-warning')
        .text(checklist.ready ? importLang.allReady : importLang.notReady);
    importRenderCacheBadges(checklist.items);

    function fillRef($tbody, rows, fmt) {
        $tbody.empty();
        if (!rows || !rows.length) {
            $tbody.append('<tr><td colspan="2" class="text-muted">—</td></tr>');
            return;
        }
        $.each(rows, function (_, row) {
            $tbody.append(fmt(row));
        });
    }
    fillRef($('#import-ref-programs'), checklist.programs_ref, function (r) {
        return '<tr class="import-ref-row" style="cursor:pointer"><td><code>' + parseInt(r.id, 10) + '</code></td><td>' + importEscapeHtml(r.name) + '</td></tr>';
    });
    fillRef($('#import-ref-batches'), checklist.batches_ref, function (r) {
        var t = importEscapeHtml(r.name) + (r.batch_nepali ? ' <small>(' + importEscapeHtml(r.batch_nepali) + ')</small>' : '');
        return '<tr class="import-ref-row" style="cursor:pointer"><td><code>' + parseInt(r.id, 10) + '</code></td><td>' + t + '</td></tr>';
    });
    fillRef($('#import-ref-fiscal'), checklist.fiscal_ref, function (r) {
        var t = importEscapeHtml(r.year_nepali || r.year_english || '');
        if (parseInt(r.active_fiscal_year, 10) === 1) {
            t += ' <span class="label label-success">active</span>';
        }
        return '<tr class="import-ref-row" style="cursor:pointer"><td><code>' + parseInt(r.id, 10) + '</code></td><td>' + t + '</td></tr>';
    });
    if ($('#import-ref-categories').length && checklist.categories_ref) {
        fillRef($('#import-ref-categories'), checklist.categories_ref, function (r) {
            return '<tr class="import-ref-row" style="cursor:pointer"><td><code>' + parseInt(r.id, 10) + '</code></td><td>' + importEscapeHtml(r.name) + '</td></tr>';
        });
    }
    if (checklist.districts_ref && checklist.districts_ref.length) {
        var $distSel = $('#import_address_district');
        var keep = $distSel.val();
        $distSel.find('option:not(:first)').remove();
        $.each(checklist.districts_ref, function (_, dr) {
            var dn = dr.district || '';
            if (dn) {
                $distSel.append($('<option></option>').val(dn).text(dn));
            }
        });
        if (keep) {
            $distSel.val(keep);
        }
    }
    importAddressRowsLoaded = false;
    importUgcAddressRows = [];
    importLoadUgcAddressesForImport();
}

$(document).on('click', '.import-ref-row code, .import-ref-row[data-copy]', function () {
    var t = $(this).is('code') ? $(this).text() : String($(this).closest('tr').data('copy') || '');
    if (window.prompt) {
        window.prompt('Copy to CSV:', t);
    }
});

function importAddrNormRow(row) {
    return {
        province: row.province || '',
        district: row.district || '',
        localLevel: row.localLevel || row.local_level || '',
        combinedCode: String(row.combinedCode || row.combined_code || '')
    };
}

function importAddrUniq(list, key) {
    var seen = {}, out = [];
    $.each(list, function (_, row) {
        var v = row[key] || '';
        if (!v || seen[v]) { return; }
        seen[v] = true;
        out.push(v);
    });
    out.sort();
    return out;
}

function importAddrFilter(list, province, district) {
    return $.grep(list, function (row) {
        if (province && row.province !== province) { return false; }
        if (district && row.district !== district) { return false; }
        return true;
    });
}

function importAddrFillSelect($el, values, selected) {
    var html = '<option value="">' + importEscapeHtml(importLang.selectOpt) + '</option>';
    $.each(values, function (_, v) {
        var sel = (String(selected) === String(v)) ? ' selected' : '';
        html += '<option value="' + importEscapeHtml(v) + '"' + sel + '>' + importEscapeHtml(v) + '</option>';
    });
    $el.html(html).prop('disabled', false);
}

function importAddrFillLocalLevel($el, rows, selectedCode) {
    var html = '<option value="">' + importEscapeHtml(importLang.selectOpt) + '</option>';
    var seen = {};
    $.each(rows, function (_, row) {
        var code = row.combinedCode || '';
        var name = row.localLevel || '';
        if (!code || !name || seen[code]) { return; }
        seen[code] = true;
        var sel = (String(selectedCode) === String(code)) ? ' selected' : '';
        html += '<option value="' + importEscapeHtml(code) + '"' + sel + '>' + importEscapeHtml(name) + '</option>';
    });
    $el.html(html).prop('disabled', false);
}

function importGetAddrPrefix() {
    return $('input[name="import_addr_prefix"]:checked').val() || 'ugc_p';
}

function importAddrGetSelection() {
    var $prov = $('#import_addr_province');
    var $dist = $('#import_addr_district');
    var $loc = $('#import_addr_local_level');
    var province = $.trim($prov.val() || '');
    var district = $.trim($dist.val() || '');
    var code = $.trim($loc.val() || '');
    var localLevel = '';
    if (code && $loc.find('option:selected').length) {
        localLevel = $.trim($loc.find('option:selected').text() || '');
        if (localLevel.toLowerCase().indexOf('select') === 0) {
            localLevel = '';
        }
    }
    return { province: province, district: district, localLevel: localLevel, combinedCode: code };
}

function importCopyText(text) {
    text = String(text || '');
    if (!text) { return; }
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).catch(function () {
            window.prompt('Copy:', text);
        });
        return;
    }
    window.prompt('Copy:', text);
}

function importAddrRenderCsvPreview() {
    var prefix = importGetAddrPrefix();
    var sel = importAddrGetSelection();
    var $preview = $('#import-address-csv-preview');
    var $fields = $('#import-address-csv-fields').empty();
    if (!sel.province || !sel.district || !sel.localLevel || !sel.combinedCode) {
        $preview.hide();
        return;
    }
    var cols = [
        { key: prefix + '_province', val: sel.province },
        { key: prefix + '_district', val: sel.district },
        { key: prefix + '_local_level', val: sel.localLevel },
        { key: prefix + '_combined_code', val: sel.combinedCode }
    ];
    $.each(cols, function (_, c) {
        var $tr = $('<tr></tr>');
        $tr.append('<td style="width:42%;"><code>' + importEscapeHtml(c.key) + '</code></td>');
        $tr.append('<td class="import-addr-val">' + importEscapeHtml(c.val) + '</td>');
        var $btn = $('<button type="button" class="btn btn-default btn-xs import-addr-copy-one"></button>')
            .text(importLang.addressCopyField).data('copy', c.val);
        $tr.append($('<td style="width:70px;text-align:right;"></td>').append($btn));
        $fields.append($tr);
    });
    $preview.show();
}

function importAddrRefreshDistricts() {
    var rows = importUgcAddressRows;
    var province = $('#import_addr_province').val();
    var drows = importAddrFilter(rows, province, null);
    importAddrFillSelect($('#import_addr_district'), importAddrUniq(drows, 'district'), '');
    importAddrFillLocalLevel($('#import_addr_local_level'), [], '');
    $('#import_addr_local_level').prop('disabled', true);
    importAddrRenderCsvPreview();
}

function importAddrRefreshLocals() {
    var rows = importUgcAddressRows;
    var province = $('#import_addr_province').val();
    var district = $('#import_addr_district').val();
    var lrows = importAddrFilter(rows, province, district);
    importAddrFillLocalLevel($('#import_addr_local_level'), lrows, '');
    importAddrRenderCsvPreview();
}

function importAddrApplyToBuilder(province, district, combinedCode) {
    if (!importAddressRowsLoaded || !importUgcAddressRows.length) {
        return;
    }
    importAddrFillSelect($('#import_addr_province'), importAddrUniq(importUgcAddressRows, 'province'), province);
    var drows = importAddrFilter(importUgcAddressRows, province, null);
    importAddrFillSelect($('#import_addr_district'), importAddrUniq(drows, 'district'), district);
    var lrows = importAddrFilter(importUgcAddressRows, province, district);
    importAddrFillLocalLevel($('#import_addr_local_level'), lrows, combinedCode);
    importAddrRenderCsvPreview();
}

function importLoadUgcAddressesForImport() {
    if (importAddressRowsLoaded) {
        return;
    }
    $('#import-address-load-status').text(importLang.addressLoading);
    $.getJSON(baseUrl + 'student/import_ugc_addresses', function (res) {
        if (!res || res.status !== 1 || !res.data || !res.data.length) {
            $('#import-address-load-status').html('<span class="text-warning">' + importEscapeHtml(importLang.addressLoadFailed) + '</span>');
            return;
        }
        importUgcAddressRows = $.map(res.data, importAddrNormRow);
        importAddressRowsLoaded = true;
        importAddrFillSelect($('#import_addr_province'), importAddrUniq(importUgcAddressRows, 'province'), '');
        $('#import_addr_district, #import_addr_local_level').html('<option value="">' + importEscapeHtml(importLang.selectOpt) + '</option>').prop('disabled', true);
        $('#import-address-load-status').html('<span class="text-success">' + importUgcAddressRows.length + ' UGC localities loaded. Pick province → district → municipality.</span>');
    }).fail(function () {
        $('#import-address-load-status').html('<span class="text-danger">' + importEscapeHtml(importLang.addressLoadFailed) + '</span>');
    });
}

function importSearchAddresses() {
    var district = $.trim($('#import_address_district').val() || '');
    var q = $.trim($('#import_address_q').val() || '');
    var $body = $('#import-address-results').html('<tr><td colspan="4" class="text-center">Loading…</td></tr>');
    $.getJSON(baseUrl + 'student/import_lookup_address', { district: district, q: q, limit: 50 }, function (res) {
        $body.empty();
        if (!res || res.status !== 1 || !res.data || !res.data.length) {
            $body.append('<tr><td colspan="4" class="text-muted text-center">' + importEscapeHtml(importLang.addressEmpty) + '</td></tr>');
            return;
        }
        $.each(res.data, function (i, row) {
            var norm = importAddrNormRow(row);
            var $tr = $('<tr class="import-address-row" style="cursor:pointer"></tr>');
            $tr.data('row', norm);
            $tr.append('<td><code>' + importEscapeHtml(norm.combinedCode) + '</code></td>');
            $tr.append('<td>' + importEscapeHtml(norm.province) + '</td>');
            $tr.append('<td>' + importEscapeHtml(norm.district) + '</td>');
            $tr.append('<td>' + importEscapeHtml(norm.localLevel) + '</td>');
            $body.append($tr);
        });
    }).fail(function () {
        $body.html('<tr><td colspan="4" class="text-danger text-center">Search failed</td></tr>');
    });
}

$(document).on('click', '.import-address-row', function () {
    var row = $(this).data('row');
    if (!row) { return; }
    $('input[name="import_addr_prefix"][value="ugc_p"]').prop('checked', true).closest('label').addClass('active').siblings().removeClass('active');
    importAddrApplyToBuilder(row.province, row.district, row.combinedCode);
    var $panel = $('#import-address-panel');
    if ($panel.length) {
        $('html, body').animate({ scrollTop: $panel.offset().top - 70 }, 300);
    }
});

$(document).on('click', '.import-addr-copy-one', function (e) {
    e.preventDefault();
    importCopyText($(this).data('copy'));
});

$('#btn-import-address-copy-all').on('click', function () {
    var prefix = importGetAddrPrefix();
    var sel = importAddrGetSelection();
    if (!sel.combinedCode) { return; }
    var lines = [
        prefix + '_province\t' + sel.province,
        prefix + '_district\t' + sel.district,
        prefix + '_local_level\t' + sel.localLevel,
        prefix + '_combined_code\t' + sel.combinedCode
    ];
    importCopyText(lines.join('\n'));
});

$('#import_addr_province').on('change', importAddrRefreshDistricts);
$('#import_addr_district').on('change', importAddrRefreshLocals);
$('#import_addr_local_level').on('change', importAddrRenderCsvPreview);
$('input[name="import_addr_prefix"]').on('change', function () {
    $(this).closest('label').addClass('active').parent().find('label').not($(this).closest('label')).removeClass('active');
    importAddrRenderCsvPreview();
});

$('#btn-import-address-search').on('click', importSearchAddresses);
$('#import_address_q').on('keypress', function (e) {
    if (e.which === 13) { e.preventDefault(); importSearchAddresses(); }
});

$('#btn-import-refresh-metadata').on('click', function () {
    var $btn = $(this);
    var $log = $('#import-metadata-refresh-log').show().text(importLang.refreshing);
    $btn.prop('disabled', true);
    $.ajax({
        url: baseUrl + 'student/import_refresh_metadata',
        method: 'POST',
        dataType: 'json',
        data: { <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>' },
        success: function (res) {
            if (res && res.status === 1 && res.checklist) {
                importRenderHemisChecklist(res.checklist);
                $log.text(importLang.refreshed);
            } else {
                $log.text(importLang.refreshFailed);
            }
        },
        error: function () { $log.text(importLang.refreshFailed); },
        complete: function () { $btn.prop('disabled', false); }
    });
});

$(document).ready(function () {
    <?php if (!$hemis_ready) { ?>
    $('#import-hemis-body').collapse('show');
    <?php } ?>
    importLoadUgcAddressesForImport();

    if ($.fn.dropify) {
        var $csvFile = $('#import_csv_file');
        if ($csvFile.length && !$csvFile.data('dropify')) {
            $csvFile.dropify({
                messages: {
                    default: <?php echo json_encode($this->lang->line('choose_a_file_or_drag_it_here')); ?>,
                    replace: <?php echo json_encode($this->lang->line('choose_a_file_or_drag_it_here')); ?>
                }
            });
        }
    }

    $.getJSON(baseUrl + 'sections/getAll', function (response) {
        var section_id = '<?php echo set_value('section_id'); ?>';
        var html = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
        $.each(response, function (i, section) {
            html += '<option value="' + section.id + '"' + (section_id == section.id ? ' selected' : '') + '>' + section.section + '</option>';
        });
        $('#section_id').html(html);
        if (section_id) {
            $('#section_id').trigger('change');
        }
    });
});

$(document).on('change', '#section_id', function () {
    var section_id = $(this).val();
    $('#program_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
    $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
    if (!section_id) {
        return;
    }
    $.post(baseUrl + 'programs/getBySection', { section_id: section_id }, function (response) {
        var program_id = '<?php echo set_value('program_id'); ?>';
        var html = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
        $.each(response, function (i, program) {
            html += '<option value="' + program.id + '"' + (program_id == program.id ? ' selected' : '') + '>' + program.title + '</option>';
        });
        $('#program_id').html(html);
        if (program_id) {
            $('#program_id').trigger('change');
        }
    }, 'json');
});

$(document).on('change', '#program_id', function () {
    var section_id = $('#section_id').val();
    var program_id = $(this).val();
    $('#class_id').html('<option value="">Loading...</option>');
    $.post(baseUrl + 'classes/getBySectionAndProgram', {
        section_id: section_id,
        program_id: program_id,
        <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
    }, function (response) {
        var html = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
        if (response && response.length) {
            $.each(response, function (i, c) {
                html += '<option value="' + c.id + '">' + (c.degree ? c.degree + ' - ' : '') + c.class + '</option>';
            });
        }
        $('#class_id').html(html);
    }, 'json');
});
</script>

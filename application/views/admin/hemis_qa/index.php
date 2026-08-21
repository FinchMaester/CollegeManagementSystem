<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-building"></i> <?php echo htmlspecialchars($title ?? 'HEMIS QA', ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="text-muted">Buildings, Library, and Furniture/Equipment/Vehicle — local DB + immediate <code>Hemis_client</code> sync (same as REST APIs).</p>
    </section>
    <section class="content">
        <?php if (!empty($flash_ok)) { ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($flash_ok, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php } ?>
        <?php if (!empty($flash_err)) { ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($flash_err, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php } ?>
        <?php if (!empty($last_hemis)) { ?>
            <div class="alert alert-info small"><strong>Last HEMIS sync result:</strong> <pre style="white-space:pre-wrap;margin:8px 0 0;"><?php echo htmlspecialchars(json_encode($last_hemis, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?></pre></div>
        <?php } ?>
        <?php if (isset($last_api_http) || !empty($last_api_body)) { ?>
            <div class="alert alert-default small"><strong>Last campus API response:</strong> HTTP <?php echo isset($last_api_http) ? (int) $last_api_http : 0; ?>
                <pre style="white-space:pre-wrap;margin:8px 0 0;max-height:240px;overflow:auto;"><?php echo htmlspecialchars(substr((string) ($last_api_body ?? ''), 0, 8000), ENT_QUOTES, 'UTF-8'); ?></pre>
            </div>
        <?php } ?>

        <div class="alert alert-info small">
            Manual sync buttons are Super Admin tooling. Status badges are stored in <code>ugc_sync_states</code>.
            Review attempts in <a href="<?php echo site_url('admin/ugc_error_log'); ?>">UGC Error Log</a>.
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header"><h3 class="box-title">Buildings — manual sync</h3></div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-condensed">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>houseName</th>
                                    <th>noOfClassrooms</th>
                                    <th>UGC Sync</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $sync_map = isset($ugc_building_sync_map) && is_array($ugc_building_sync_map) ? $ugc_building_sync_map : array();
                                foreach (($buildings ?? array()) as $b) {
                                    $bid = (int) ($b['id'] ?? 0);
                                    if ($bid < 1) continue;
                                    $st = isset($sync_map[$bid]) ? $sync_map[$bid] : null;
                                    $status = $st['status'] ?? 'pending';
                                    $badge = 'label-default';
                                    $txt = 'Pending';
                                    if ($status === 'synced') { $badge = 'label-success'; $txt = 'Synced'; }
                                    elseif ($status === 'blocked') { $badge = 'label-warning'; $txt = 'Blocked'; }
                                    elseif ($status === 'error') { $badge = 'label-danger'; $txt = 'Error'; }
                                    ?>
                                    <tr id="ugc-building-row-<?php echo $bid; ?>">
                                        <td><?php echo $bid; ?></td>
                                        <td><?php echo htmlspecialchars((string) ($b['houseName'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo (int) ($b['noOfClassrooms'] ?? 0); ?></td>
                                        <td><span class="label <?php echo $badge; ?> ugc-building-badge"><?php echo $txt; ?></span></td>
                                        <td>
                                            <button type="button" class="btn btn-xs btn-primary ugc-sync-building" data-id="<?php echo $bid; ?>">
                                                <i class="fa fa-cloud-upload"></i> Sync
                                            </button>
                                        </td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header"><h3 class="box-title">Building (§5.1.1)</h3></div>
                    <form method="post" class="box-body">
                        <?php echo $this->customlib->getCSRF(); ?>
                        <input type="hidden" name="hemis_qa_action" value="building" />
                        <div class="row">
                            <div class="col-md-4"><div class="form-group"><label>houseName</label><input class="form-control" name="houseName" required value="QA Block A" /></div></div>
                            <div class="col-md-4"><div class="form-group"><label>areaCoveredByBuilding</label><input class="form-control" name="areaCoveredByBuilding" type="number" step="0.01" value="1200.5" /></div></div>
                            <div class="col-md-4"><div class="form-group"><label>noOfClassrooms</label><input class="form-control" name="noOfClassrooms" type="number" value="12" /></div></div>
                            <div class="col-md-4"><div class="form-group"><label>areaCoveredByAllRooms</label><input class="form-control" name="areaCoveredByAllRooms" type="number" step="0.01" value="900" /></div></div>
                            <div class="col-md-4"><div class="form-group"><label>ownershipOfBuilding</label><select class="form-control" name="ownershipOfBuilding"><option value="1">Owned</option><option value="0">Rented</option></select></div></div>
                            <div class="col-md-4"><div class="form-group"><label>hasInternetConnection</label><select class="form-control" name="hasInternetConnection"><option value="1">Yes</option><option value="0">No</option></select></div></div>
                            <div class="col-md-12"><div class="form-group"><label>remarks</label><input class="form-control" name="remarks" value="QA automation" /></div></div>
                        </div>
                        <button type="submit" class="btn btn-primary">Save building + sync</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-success">
                    <div class="box-header"><h3 class="box-title">Library (§5.1.6) — verify noOfBooks &amp; areaOfReadingHall</h3></div>
                    <form method="post" class="box-body">
                        <?php echo $this->customlib->getCSRF(); ?>
                        <input type="hidden" name="hemis_qa_action" value="library" />
                        <div class="row">
                            <div class="col-md-4"><div class="form-group"><label>libraryName</label><input class="form-control" name="libraryName" required value="Central QA Library" /></div></div>
                            <div class="col-md-4"><div class="form-group"><label>buildingId</label>
                                <select class="form-control" name="buildingId" required>
                                    <?php foreach ($buildings as $b) {
                                        $bid = isset($b['id']) ? (int) $b['id'] : 0;
                                        if ($bid < 1) {
                                            continue;
                                        }
                                        echo '<option value="' . $bid . '">' . htmlspecialchars($b['houseName'] ?? ('#' . $bid), ENT_QUOTES, 'UTF-8') . '</option>';
                                    } ?>
                                </select>
                                <?php if (empty($buildings)) { ?>
                                    <p class="text-danger small">Create a building first (dropdown empty).</p>
                                <?php } ?>
                            </div></div>
                            <div class="col-md-4"><div class="form-group"><label>campusId</label><input class="form-control" name="campusId" type="number" required value="<?php echo (int) ($default_campus_id ?? 1); ?>" /></div></div>
                            <div class="col-md-3"><div class="form-group"><label>noOfLibraryRooms</label><input class="form-control" name="noOfLibraryRooms" type="number" value="3" /></div></div>
                            <div class="col-md-3"><div class="form-group"><label>totalAreaOfLibraryRoom</label><input class="form-control" name="totalAreaOfLibraryRoom" type="number" step="0.01" value="250.75" /></div></div>
                            <div class="col-md-3"><div class="form-group"><label>noOfReadingHalls</label><input class="form-control" name="noOfReadingHalls" type="number" value="1" /></div></div>
                            <div class="col-md-3"><div class="form-group"><label>areaOfReadingHall (e.g. sq ft)</label><input class="form-control" name="areaOfReadingHall" type="number" step="0.01" value="150.5" /></div></div>
                            <div class="col-md-3"><div class="form-group"><label>noOfBooks</label><input class="form-control" name="noOfBooks" type="number" value="5000" /></div></div>
                            <div class="col-md-3"><div class="form-group"><label>noOfDailyJournals</label><input class="form-control" name="noOfDailyJournals" type="number" value="2" /></div></div>
                            <div class="col-md-3"><div class="form-group"><label>noOfWeeklyJournals</label><input class="form-control" name="noOfWeeklyJournals" type="number" value="5" /></div></div>
                            <div class="col-md-3"><div class="form-group"><label>noOfMonthlyJournals</label><input class="form-control" name="noOfMonthlyJournals" type="number" value="40" /></div></div>
                            <div class="col-md-3"><div class="form-group"><label>noOfAnnualJournals</label><input class="form-control" name="noOfAnnualJournals" type="number" value="12" /></div></div>
                            <div class="col-md-3"><div class="form-group"><label>hasELibraryFacility</label><select class="form-control" name="hasELibraryFacility"><option value="1">Yes</option><option value="0">No</option></select></div></div>
                            <div class="col-md-12"><div class="form-group"><label>remarks</label><input class="form-control" name="remarks" value="QA" /></div></div>
                        </div>
                        <button type="submit" class="btn btn-success" <?php echo empty($buildings) ? 'disabled' : ''; ?>>Save library + sync</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-warning">
                    <div class="box-header"><h3 class="box-title">Furniture / Equipment / Vehicle</h3></div>
                    <form method="post" class="box-body">
                        <?php echo $this->customlib->getCSRF(); ?>
                        <input type="hidden" name="hemis_qa_action" value="fev" />
                        <div class="row">
                            <div class="col-md-3"><div class="form-group"><label>itemType</label><select class="form-control" name="itemType"><option>Furniture</option><option>Equipment</option><option>Vehicle</option></select></div></div>
                            <div class="col-md-4"><div class="form-group"><label>itemName</label><input class="form-control" name="itemName" required value="Student chairs (QA)" /></div></div>
                            <div class="col-md-2"><div class="form-group"><label>noOfQty</label><input class="form-control" name="noOfQty" type="number" value="120" /></div></div>
                            <div class="col-md-12"><div class="form-group"><label>itemDescription</label><input class="form-control" name="itemDescription" value="Stackable" /></div></div>
                            <div class="col-md-12"><div class="form-group"><label>remarks</label><input class="form-control" name="remarks" value="QA" /></div></div>
                        </div>
                        <button type="submit" class="btn btn-warning">Save FEV + sync</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header"><h3 class="box-title">Student lifecycle (browser QA → same REST as UGC)</h3></div>
                    <div class="box-body">
                        <p class="text-muted small">Uses a valid <code>tokens</code> row as Bearer. Check <code>application/logs</code> for <code>HEMIS Library outbound</code> / <code>HEMIS DropOut outbound</code> after actions.</p>
                        <div class="row" style="margin-bottom:15px;">
                            <div class="col-md-12">
                                <form method="post" class="form-inline">
                                    <?php echo $this->customlib->getCSRF(); ?>
                                    <input type="hidden" name="hemis_qa_action" value="reactivate_1097" />
                                    <button type="submit" class="btn btn-default">1) Reactivate student 1097 (undo dropout for current session)</button>
                                </form>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <h4>2) POST /api/StudentUpgrade</h4>
                                <form method="post" class="form-horizontal">
                                    <?php echo $this->customlib->getCSRF(); ?>
                                    <input type="hidden" name="hemis_qa_action" value="student_upgrade_api" />
                                    <div class="form-group col-md-2"><label>studentId</label><input class="form-control" name="su_studentId" type="number" value="1097" /></div>
                                    <div class="form-group col-md-2"><label>programId</label><input class="form-control" name="su_programId" type="number" value="5" /></div>
                                    <div class="form-group col-md-2"><label>year</label><input class="form-control" name="su_year" value="3" /></div>
                                    <div class="form-group col-md-2"><label>semester</label><input class="form-control" name="su_semester" value="Semester 2" /></div>
                                    <div class="form-group col-md-2"><label>batchId</label><input class="form-control" name="su_batchId" type="number" value="1" /></div>
                                    <div class="form-group col-md-12"><button type="submit" class="btn btn-primary">Run StudentUpgrade</button></div>
                                </form>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <h4>3) POST /api/DropOut (text reason → DB + HEMIS log)</h4>
                                <form method="post" class="form-horizontal">
                                    <?php echo $this->customlib->getCSRF(); ?>
                                    <input type="hidden" name="hemis_qa_action" value="dropout_test_api" />
                                    <div class="form-group col-md-2"><label>studentId</label><input class="form-control" name="do_studentId" type="number" value="1095" /></div>
                                    <div class="form-group col-md-4"><label>reason (text)</label><input class="form-control" name="do_reason" value="UGC compliance test — financial hardship" required /></div>
                                    <div class="form-group col-md-2"><label>dateOfEntry</label><input class="form-control" name="do_date" value="<?php echo date('Y-m-d'); ?>" required /></div>
                                    <div class="form-group col-md-4"><label>remarks</label><input class="form-control" name="do_remarks" value="QA dropout retest after VARCHAR migration" /></div>
                                    <div class="form-group col-md-12"><button type="submit" class="btn btn-danger">Run DropOut</button> <span class="text-muted small">Change studentId if already dropped out.</span></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
  (function () {
    function csrfData() {
      var obj = {};
      obj['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
      return obj;
    }

    function updateBadge($row, out) {
      var $badge = $row.find('.ugc-building-badge');
      var status = 'pending';
      if (out && out.synced) status = 'synced';
      else if (out && out.sync_pending === 'UGC_DEV_BLOCKER') status = 'blocked';
      else if (out && out.error) status = 'error';
      var cls = 'label-default', txt = 'Pending';
      if (status === 'synced') { cls = 'label-success'; txt = 'Synced'; }
      else if (status === 'blocked') { cls = 'label-warning'; txt = 'Blocked'; }
      else if (status === 'error') { cls = 'label-danger'; txt = 'Error'; }
      $badge.removeClass('label-default label-success label-warning label-danger').addClass(cls).text(txt);
    }

    $(document).on('click', '.ugc-sync-building', function (e) {
      if (e && e.preventDefault) e.preventDefault();
      var id = $(this).data('id');
      var $row = $('#ugc-building-row-' + id);
      var base = (window.baseurl || '<?php echo base_url(); ?>');
      $.ajax({
        type: 'POST',
        url: base + 'admin/ugc_sync/building/' + id,
        data: csrfData(),
        dataType: 'json'
      }).done(function (resp) {
        updateBadge($row, resp || {});
      }).fail(function () {
        updateBadge($row, { error: 'request failed' });
        alert('UGC sync request failed.');
      });
    });
  })();
</script>

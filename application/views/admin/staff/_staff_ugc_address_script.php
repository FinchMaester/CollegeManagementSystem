<script type="text/javascript">
(function ($) {
    var ugcUrl = '<?php echo base_url(); ?>admin/staff/ugc_addresses';

    function normalizeUgcAddressRows(rows) {
        var out = [];
        $.each(rows || [], function (_, row) {
            if (!row || typeof row !== 'object') {
                return;
            }
            out.push({
                province: $.trim(String(row.province || row.Province || '')),
                district: $.trim(String(row.district || row.District || '')),
                localLevel: $.trim(String(row.localLevel || row.LocalLevel || '')),
                combinedCode: $.trim(String(row.combinedCode || row.CombinedCode || ''))
            });
        });
        return out;
    }

    function ugcAddressUniq(rows, key) {
        var out = [], seen = {};
        $.each(rows || [], function (_, row) {
            var v = $.trim(String(row[key] || ''));
            if (v && !seen[v]) {
                seen[v] = true;
                out.push(v);
            }
        });
        out.sort();
        return out;
    }

    function ugcAddressFilter(rows, province, district) {
        return $.grep(rows || [], function (row) {
            if (province && $.trim(String(row.province || '')) !== $.trim(String(province))) {
                return false;
            }
            if (district && $.trim(String(row.district || '')) !== $.trim(String(district))) {
                return false;
            }
            return true;
        });
    }

    function fillSelect($el, values, selected) {
        var html = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
        $.each(values || [], function (_, v) {
            var sel = (String(selected) === String(v)) ? ' selected' : '';
            html += '<option value="' + $('<div>').text(v).html() + '"' + sel + '>' + $('<div>').text(v).html() + '</option>';
        });
        $el.html(html);
    }

    function fillSelectLocalLevel($el, rows, selectedCombinedCode) {
        var html = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
        var seen = {};
        $.each(rows || [], function (_, row) {
            var code = row.combinedCode || '';
            var name = row.localLevel || '';
            if (!code || !name || seen[code]) {
                return;
            }
            seen[code] = true;
            var sel = (String(selectedCombinedCode) === String(code)) ? ' selected' : '';
            html += '<option value="' + $('<div>').text(code).html() + '"' + sel + '>' + $('<div>').text(name).html() + '</option>';
        });
        $el.html(html);
    }

    window.syncUgcHiddenFieldsFromCascade = function (prefix) {
        var $prov = $('#' + prefix + '_province');
        var $dist = $('#' + prefix + '_district');
        var $loc = $('#' + prefix + '_local_level');
        if (!$prov.length || !$dist.length || !$loc.length) {
            return;
        }
        var pv = $.trim($prov.val() || '');
        var dv = $.trim($dist.val() || '');
        var code = $.trim($loc.val() || '');
        var locTxt = $.trim($loc.find('option:selected').text() || '');
        if (locTxt.toLowerCase().indexOf('select') === 0) {
            locTxt = '';
        }
        $('input[name="' + prefix + '_province_name"]').val(pv);
        $('input[name="' + prefix + '_district_name"]').val(dv);
        $('input[name="' + prefix + '_local_level_name"]').val(locTxt);
        $('input[name="' + prefix + '_combined_code"]').val(code);
    };

    window.syncAllUgcAddressHiddensForSubmit = function () {
        syncUgcHiddenFieldsFromCascade('ugc_p');
        syncUgcHiddenFieldsFromCascade('ugc_t');
    };

    function initUgcAddressCascade(prefix, initial) {
        initial = initial || {};
        var $prov = $('#' + prefix + '_province');
        var $dist = $('#' + prefix + '_district');
        var $loc = $('#' + prefix + '_local_level');
        if (!$prov.length) {
            return;
        }

        $prov.html('<option value="">Loading...</option>');
        $dist.html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
        $loc.html('<option value=""><?php echo $this->lang->line('select'); ?></option>');

        $.ajax({
            url: ugcUrl,
            method: 'GET',
            dataType: 'json',
            cache: false,
            success: function (resp) {
                var rows = normalizeUgcAddressRows((resp && resp.data) ? resp.data : []);
                if (!rows.length) {
                    var msg = (resp && resp.message) ? resp.message : 'No UGC addresses in cache';
                    $prov.html('<option value="">' + msg + '</option>');
                    return;
                }
                window.__ugcAddressRows = rows;
                fillSelect($prov, ugcAddressUniq(rows, 'province'), initial.province || '');

                function refreshDistricts() {
                    fillSelect($dist, ugcAddressUniq(ugcAddressFilter(rows, $prov.val(), null), 'district'), initial.district || '');
                    refreshLocals();
                }

                function refreshLocals() {
                    fillSelectLocalLevel($loc, ugcAddressFilter(rows, $prov.val(), $dist.val()), initial.combinedCode || '');
                    syncUgcHiddenFieldsFromCascade(prefix);
                }

                $prov.off('change.staffugc').on('change.staffugc', function () {
                    initial.district = '';
                    initial.combinedCode = '';
                    refreshDistricts();
                });
                $dist.off('change.staffugc').on('change.staffugc', function () {
                    initial.combinedCode = '';
                    refreshLocals();
                });
                $loc.off('change.staffugc').on('change.staffugc', function () {
                    syncUgcHiddenFieldsFromCascade(prefix);
                });

                refreshDistricts();
            },
            error: function (xhr) {
                var msg = 'Error loading UGC addresses';
                if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                $prov.html('<option value="">' + msg + '</option>');
            }
        });
    }

    $(function () {
        var initial = window.__staffUgcAddressInitial || {};
        initUgcAddressCascade('ugc_p', initial.ugc_p || {});
        initUgcAddressCascade('ugc_t', initial.ugc_t || {});

        $('form#form1, form#employeeform, form[name="employeeform"]').on('submit', function () {
            syncAllUgcAddressHiddensForSubmit();
        });

        $('#same_as_permanent').off('change.staffugc').on('change.staffugc', function () {
            if (typeof copyPermanentAddress === 'function') {
                copyPermanentAddress();
            }
        });
    });
})(jQuery);
</script>

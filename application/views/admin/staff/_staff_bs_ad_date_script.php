<script type="text/javascript">
(function ($) {
    'use strict';

    /**
     * Normalize BS date string to YYYY-MM-DD for NepaliFunctions.
     */
    function staffNormalizeBsDate(bsDateStr) {
        if (!bsDateStr || typeof bsDateStr !== 'string') {
            return '';
        }
        var s = bsDateStr.trim().replace(/\//g, '-');
        if (!/^\d{4}-\d{2}-\d{2}$/.test(s)) {
            return '';
        }
        return s;
    }

    /**
     * Convert B.S. date to A.D. (YYYY-MM-DD) — same approach as student create/edit.
     */
    window.staffConvertBsToAd = function (bsDateStr, targetAdFieldId) {
        var normalized = staffNormalizeBsDate(bsDateStr);
        var $ad = $('#' + targetAdFieldId);
        if (!normalized) {
            if ($ad.length && !bsDateStr) {
                $ad.val('');
            }
            return;
        }

        try {
            if (window.NepaliFunctions && typeof window.NepaliFunctions.ConvertToEnglish === 'function') {
                var eng = window.NepaliFunctions.ConvertToEnglish(normalized);
                if (eng && eng.year && eng.month && eng.day) {
                    $ad.val(
                        eng.year + '-' +
                        String(eng.month).padStart(2, '0') + '-' +
                        String(eng.day).padStart(2, '0')
                    );
                    return;
                }
            }

            var parts = normalized.split('-');
            if (parts.length === 3 && window.NepaliFunctions && typeof window.NepaliFunctions.BS2AD === 'function') {
                var ad = window.NepaliFunctions.BS2AD({
                    year: parseInt(parts[0], 10),
                    month: parseInt(parts[1], 10),
                    day: parseInt(parts[2], 10)
                });
                if (ad && ad.year && ad.month && ad.day) {
                    $ad.val(
                        ad.year + '-' +
                        String(ad.month).padStart(2, '0') + '-' +
                        String(ad.day).padStart(2, '0')
                    );
                }
            }
        } catch (e) {
            console.error('Staff BS to AD conversion failed:', e);
        }
    };

    /**
     * Wire a BS field to auto-fill an AD field when the Nepali datepicker changes.
     *
     * @param {string} bsFieldId
     * @param {string} adFieldId
     * @param {object} opts
     */
    window.staffBindBsToAd = function (bsFieldId, adFieldId, opts) {
        opts = opts || {};
        var $bs = $('#' + bsFieldId);
        if (!$bs.length) {
            return;
        }

        var runConvert = function () {
            var v = $bs.val();
            if (staffNormalizeBsDate(v)) {
                staffConvertBsToAd(v, adFieldId);
            }
        };

        $bs.data('last-converted-value', staffNormalizeBsDate($bs.val()) || '');

        $bs.off('.staffBsAd');
        $bs.on('nepali.datepicker.dateChanged.staffBsAd dateChange.staffBsAd', function () {
            setTimeout(runConvert, 50);
        });
        $bs.on('change.staffBsAd blur.staffBsAd input.staffBsAd', function () {
            runConvert();
        });

        setInterval(function () {
            var v = $bs.val();
            var normalized = staffNormalizeBsDate(v);
            if (!normalized) {
                return;
            }
            var last = $bs.data('last-converted-value') || '';
            if (normalized !== last) {
                $bs.data('last-converted-value', normalized);
                staffConvertBsToAd(v, adFieldId);
            }
        }, 250);

        if (opts.convertOnLoad && staffNormalizeBsDate($bs.val())) {
            setTimeout(runConvert, opts.convertOnLoadDelay || 400);
        }
    };

    $(document).ready(function () {
        staffBindBsToAd('dob_bs', 'dob', {
            convertOnLoad: <?php echo !empty($staff_bs_ad_convert_on_load) ? 'true' : 'false'; ?>
        });
    });
})(jQuery);
</script>

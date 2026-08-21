<style type="text/css">
    .qty_error{
        display: none;
    }
</style>
<style type="text/css">

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 22px !important; border-radius: 0 !important; padding-left: 0 !important;}
    .input-group-addon .glyphicon{font-size: 12px;}

    .show{
        display : block;
        z-index: 100;
        background-image : url('../../backend/images/timeloader.gif');
        opacity : 0.6;
        background-repeat : no-repeat;
        background-position : center;
    }
    /* .tab-pane{min-height: 200px;}*/
    .commentForm .input-group {position: relative;display: block;border-collapse: separate;}
    .commentForm .input-group-addon{
        position: absolute;
        right: 26px;
        top: 0px;
        z-index: 3;
    }
    .relative{position: relative;}
    .commentForm .input-group-addon i,
    .commentForm .input-group-addon span{padding-left: 13px;}
    .commentForm .relative label.text-danger{position: absolute; bottom: 5px;}
    .addbtnright{ position: absolute;right: 0;top: -46px;}

    @media(max-width:767px){
        .timeresponsive{overflow-x: auto;     overflow-y: hidden;}
        .timeresponsive .dropdown-menu{z-index: 1060;    bottom: 0 !important; height: 250px; padding: 20px;}
        .tablewidthRS{width: 690px;}
    }
    
    .book-search-box {
        margin-bottom: 15px;
        padding: 15px;
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .label-overdue { background-color: #dd4b39; }
    .label-active { background-color: #00a65a; }
</style>
<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
$library_settings = isset($library_settings) ? $library_settings : array();
$lib_fine_on = !empty($library_settings['fine_enabled']);
$lib_renew_on = !empty($library_settings['renewal_enabled']);
$lib_renewal_days = isset($library_settings['renewal_days']) ? (int) $library_settings['renewal_days'] : 14;
$lib_max_renewals = isset($library_settings['max_renewals']) ? (int) $library_settings['max_renewals'] : 2;
$student_member_disabled = !empty($student_member_disabled);
?>

<div class="content-wrapper">

    <section class="content">
        <div class="row">
            <div class="col-md-3">
                <?php
if ($memberList->member_type == "student") {
    $this->load->view('admin/librarian/_student');
} else {
    $this->load->view('admin/librarian/_teacher');
}
?>

            </div>
            <div class="col-md-9">
                <?php if ($student_member_disabled) { ?>
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i>
                    This student is <strong>disabled</strong>. New book issues and renewals are blocked. You can still return outstanding books below.
                </div>
                <?php } ?>
                <?php if (!$student_member_disabled) { ?>
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('issue_book'); ?></h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/member'); ?>" class="btn btn-default btn-sm" title="<?php echo $this->lang->line('back'); ?>">
                                <i class="fa fa-arrow-left"></i> <?php echo $this->lang->line('back'); ?>
                            </a>
                            <?php if ($this->rbac->hasPrivilege('library_settings', 'can_view')) { ?>
                            <a href="<?php echo site_url('admin/librarysettings'); ?>" class="btn btn-default btn-sm" title="<?php echo $this->lang->line('library_settings'); ?>">
                                <i class="fa fa-cog"></i> <?php echo $this->lang->line('library_settings'); ?>
                            </a>
                            <?php } ?>
                        </div>
                    </div><!-- /.box-header -->
                    <!-- form start -->

                    <form id="form1" action="<?php echo site_url('admin/member/issue/' . $memberList->lib_member_id) ?>"  id="employeeform" name="employeeform" method="post" accept-charset="utf-8">

                        <div class="box-body">
                            <?php
if ($this->session->flashdata('msg')) {
    echo $this->session->flashdata('msg');
    $this->session->unset_userdata('msg');
}
?> 

                            <?php echo $this->customlib->getCSRF(); ?>

                            <input id="member_id" name="member_id"  type="hidden" class="form-control date"  value="<?php echo $memberList->lib_member_id; ?>" />

                            <!-- Book Search Box -->
                            <div class="book-search-box">
                                <div class="row">
                                    <div class="col-md-10">
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <label for="book_search"><?php echo $this->lang->line('search'); ?> <?php echo $this->lang->line('book'); ?></label>
                                            <input type="text" id="book_search" class="form-control" placeholder="Search by Book Title, Book Number, Author or Subject...">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label>&nbsp;</label>
                                        <button type="button" id="clear_search" class="btn btn-default btn-block" style="margin-top: 0;">
                                            <i class="fa fa-refresh"></i> Clear
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('books'); ?>  <small class="req"> *</small></label>
                                <select autofocus="" id="book_id" name="book_id" class="form-control select2">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <?php     
foreach ($bookList as $book) {  
    // Store book number separately for exact matching
    $bookNo = isset($book['book_no']) ? trim($book['book_no']) : '';
    
    // Create a search string with delimiters for exact matching
    $searchString = strtolower(
        '|||title:' . $book['book_title'] . 
        '|||bookno:' . $bookNo . 
        '|||author:' . (isset($book['author']) ? $book['author'] : '') . 
       
        '|||subject:' . (isset($book['subject']) ? $book['subject'] : '') . '|||'
    );
    ?>
                                        <option value="<?php echo $book['id'] ?>" 
                                                data-search-text="<?php echo htmlspecialchars($searchString); ?>"
                                                data-book-no="<?php echo htmlspecialchars($bookNo); ?>"
                                                <?php if (set_value('book_id') == $book['id']) { echo "selected=selected"; } ?>>
                                            <?php echo $book['book_title']; ?>
                                        </option>
                                        <?php
}
?>
                                </select>
                                <span class="text-danger" id="book_already_issued"><?php echo form_error('book_id'); ?></span>
                                <span class="text text-danger qty_error"><b><?php echo $this->lang->line('available_quantity'); ?></b>: <span class="ava_quantity">0</span></span>
                            </div>
                            <div class="form-group">
                                <label><?php echo $this->lang->line('issue_date'); ?> <small class="req"> *</small></label>
                                <input id="issue_date" name="issue_date"  type="text" class="form-control nepali-datepicker"  value="<?php echo set_value('issue_date'); ?>"/>
                                <span class="text-danger"><?php echo form_error('issue_date'); ?></span>
                            </div>
                            <div class="form-group">
                                <label><?php echo $this->lang->line('due_return_date'); ?> <small class="req"> *</small></label>
                                <input id="dateto" name="return_date"  type="text" class="form-control nepali-datepicker"  value="<?php echo set_value('return_date'); ?>"/>
                                <span class="text-danger"><?php echo form_error('return_date'); ?></span>
                            </div>
                        </div><!-- /.box-body -->
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                        </div>
                    </form>
                </div>
                <?php } ?>
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('book_issued'); ?></h3>
                    </div><!-- /.box-header -->
                    <!-- form start -->
                    <div class="box-body">
                        <div class="table-responsive mailbox-messages">
                            <div class="download_label"><?php echo $this->lang->line('book_issued'); ?></div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('book_title'); ?></th>
                                        <th><?php echo $this->lang->line('book_number'); ?></th>
                                        <th><?php echo $this->lang->line('issue_date'); ?></th>
                                        <th><?php echo $this->lang->line('due_return_date'); ?></th>
                                        <?php if ($lib_fine_on || $lib_renew_on) { ?>
                                        <th><?php echo $this->lang->line('status'); ?></th>
                                        <?php } ?>
                                        <?php if ($lib_fine_on) { ?>
                                        <th><?php echo $this->lang->line('library_fine'); ?></th>
                                        <?php } ?>
                                        <?php if ($lib_renew_on) { ?>
                                        <th><?php echo $this->lang->line('renewals_remaining'); ?></th>
                                        <?php } ?>
                                        <th><?php echo $this->lang->line('return_date'); ?></th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
if (empty($issued_books)) {
    ?>
                                        <?php
} else {
    $count = 1;
    foreach ($issued_books as $book) {
        ?>
                                            <tr>
                                                <td class="mailbox-name">
                                                    <?php echo $book['book_title'] ?>
                                                </td>
                                                <td class="mailbox-name">
                                                    <?php echo $book['book_no'] ?>
                                                </td>
                                                <td class="mailbox-name">
                                                    <?php
                                                    $id_bs = $this->customlib->formatLibraryDateBsForDisplay(isset($book['issue_date']) ? $book['issue_date'] : '');
                                                    echo $id_bs !== '' ? htmlspecialchars($id_bs) : '';
                                                    ?></td>
                                                <td class="mailbox-name">
                                                    <?php
                                                    $dd_bs = $this->customlib->formatLibraryDateBsForDisplay(isset($book['duereturn_date']) ? $book['duereturn_date'] : '');
                                                    echo $dd_bs !== '' ? htmlspecialchars($dd_bs) : '';
                                                    ?></td>
                                                <?php if ($lib_fine_on || $lib_renew_on) { ?>
                                                <td class="mailbox-name">
                                                    <?php
                                                    if (!empty($book['is_returned'])) {
                                                        echo '<span class="label label-default">' . $this->lang->line('return') . '</span>';
                                                    } elseif (!empty($book['is_overdue'])) {
                                                        echo '<span class="label label-overdue">' . $this->lang->line('overdue');
                                                        if (!empty($book['overdue_days'])) {
                                                            echo ' (' . (int) $book['overdue_days'] . 'd)';
                                                        }
                                                        echo '</span>';
                                                    } else {
                                                        echo '<span class="label label-active">' . $this->lang->line('active') . '</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <?php } ?>
                                                <?php if ($lib_fine_on) { ?>
                                                <td class="mailbox-name">
                                                    <?php
                                                    if (!empty($book['is_returned'])) {
                                                        $stored = isset($book['stored_fine']) ? (float) $book['stored_fine'] : 0;
                                                        if ($stored > 0) {
                                                            echo htmlspecialchars($currency_symbol) . number_format($stored, 2);
                                                            $paid = isset($book['fine_paid']) ? (float) $book['fine_paid'] : 0;
                                                            if ($paid > 0) {
                                                                echo '<br><small class="text-muted">' . $this->lang->line('fine_paid') . ': ' . htmlspecialchars($currency_symbol) . number_format($paid, 2) . '</small>';
                                                            }
                                                        } else {
                                                            echo '<span class="text-muted">—</span>';
                                                        }
                                                    } elseif (!empty($book['calculated_fine']) && (float) $book['calculated_fine'] > 0) {
                                                        echo '<span class="text-danger"><strong>' . htmlspecialchars($currency_symbol) . number_format((float) $book['calculated_fine'], 2) . '</strong></span>';
                                                    } else {
                                                        echo '<span class="text-muted">' . $this->lang->line('no_fine_due') . '</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <?php } ?>
                                                <?php if ($lib_renew_on) { ?>
                                                <td class="mailbox-name">
                                                    <?php
                                                    $rc = isset($book['renew_count']) ? (int) $book['renew_count'] : 0;
                                                    $left = isset($book['renewals_left']) ? (int) $book['renewals_left'] : 0;
                                                    if (!empty($book['is_returned'])) {
                                                        echo (int) $rc . ' / ' . (int) $lib_max_renewals;
                                                    } else {
                                                        echo $left > 0
                                                            ? '<span class="text-success">' . $left . '</span> / ' . (int) $lib_max_renewals
                                                            : '<span class="text-danger">' . $this->lang->line('max_renewals_reached') . '</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <?php } ?>
                                                <td class="mailbox-name">
                                                    <?php
                                                    $rd_bs = $this->customlib->formatLibraryDateBsForDisplay(isset($book['return_date']) ? $book['return_date'] : '');
                                                    echo $rd_bs !== '' ? htmlspecialchars($rd_bs) : '';
        ?></td>
                                                <td class="mailbox-date pull-right">
                                                    <?php if ($book['is_returned'] == 0) {
            ?>
                                                        <?php if ($lib_renew_on && !empty($book['can_renew']) && !$student_member_disabled && $this->rbac->hasPrivilege('issue_return', 'can_edit')) { ?>
                                                        <button type="button" class="btn btn-warning btn-xs renew-book-btn"
                                                            data-record-id="<?php echo (int) $book['id']; ?>"
                                                            data-record-member_id="<?php echo (int) $memberList->lib_member_id; ?>"
                                                            data-book-title="<?php echo htmlspecialchars($book['book_title'], ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-renewal-days="<?php echo (int) $lib_renewal_days; ?>"
                                                            title="<?php echo $this->lang->line('renew_book'); ?>">
                                                            <i class="fa fa-refresh"></i>
                                                        </button>
                                                        <?php } ?>
                                                        <button type="button" class="btn btn-default btn-xs return-book-btn"
                                                            data-record-id="<?php echo $book['id']; ?>"
                                                            data-record-member_id="<?php echo $memberList->lib_member_id; ?>"
                                                            data-fine-amount="<?php echo isset($book['calculated_fine']) ? (float) $book['calculated_fine'] : 0; ?>"
                                                            title="<?php echo $this->lang->line('return') . ' ' . $book['book_title']; ?>">
                                                            <i class="fa fa-mail-reply"></i>
                                                        </button>
                                                        <?php
}
        ?>
                                                </td>
                                            </tr>
                                            <?php
$count++;
    }
}
?>
                                </tbody>
                            </table><!-- /.table -->
                        </div><!-- /.mail-box-messages -->
                    </div><!-- /.box-body -->
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="confirm-return" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel"><?php echo $this->lang->line('confirm_return'); ?></h4>
            </div>
            <form action="<?php echo site_url('admin/member/bookreturn') ?>" method="POST" id="return_book">
                <div class="modal-body issue_retrun_modal-body">
                    <input type="hidden" name="id" id="return_model_id" value="0">
                    <input type="hidden" name="member_id" id="return_model_member_id" value="0">
                    <div class="form-group">
                        <label for="exampleInputEmail1"><?php echo $this->lang->line('return_date'); ?> <small class="req"> *</small></label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </div>
                            <input type="text" class="form-control no-nepali-datepicker" id="date" name="date" placeholder="<?php echo $this->lang->line('date'); ?> (YYYY-MM-DD)" value="" data-ndp-skip="true" data-ndp-custom-handler="true" style="background-color: white;"/>
                        </div>
                        <div id="error" class="text text-danger"></div>
                    </div>
                    <?php if ($lib_fine_on) { ?>
                    <div class="form-group" id="return-fine-wrap" style="display:none;">
                        <label><?php echo $this->lang->line('library_fine'); ?></label>
                        <p id="return-fine-summary" class="help-block text-danger" style="margin-bottom:8px;"></p>
                        <label for="fine_paid"><?php echo $this->lang->line('fine_paid'); ?></label>
                        <div class="input-group">
                            <span class="input-group-addon"><?php echo htmlspecialchars($currency_symbol); ?></span>
                            <input type="number" step="0.01" min="0" class="form-control" id="fine_paid" name="fine_paid" value="0">
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('cancel'); ?></button>
                    <button type="submit" class="btn btn-success" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('saving'); ?>"><?php echo $this->lang->line('save'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    // ✅ Safe local loader for Nepali DatePicker (no global impact)
    if (typeof NepaliFunctions === "undefined" || typeof $.fn.nepaliDatePicker === "undefined") {
        console.warn("⚠️ Nepali datepicker not loaded globally — loading locally...");
        var script = document.createElement("script");
        script.src = "https://cdn.jsdelivr.net/gh/sajanm/nepali-date-picker@v4.0.4/dist/nepali.datepicker.v4.0.4.min.js";
        script.onload = function () {
            console.log("✅ Nepali DatePicker script loaded successfully.");
        };
        document.head.appendChild(script);
    }

    // Global variable to store all book options
    var allBookOptions = null;

    $(document).ready(function () {
        $('.js-example-basic-single').select2();

        // Wait for DataTable initialization before custom search
        setTimeout(function () {
            $.fn.dataTable.ext.search.push(function (settings, searchData, index, rowData, counter) {
                var searchValue = $('.dataTables_filter input').val().toLowerCase().trim();
                if (searchValue === '') return true;

                var bookNumber = searchData[1] ? searchData[1].toLowerCase().trim() : '';

                if (!isNaN(searchValue) && searchValue !== '') {
                    return bookNumber === searchValue;
                } else {
                    for (var i = 0; i < searchData.length; i++) {
                        var columnData = searchData[i] ? searchData[i].toLowerCase() : '';
                        if (columnData.indexOf(searchValue) !== -1) return true;
                    }
                    return false;
                }
            });

            $(document).on('keyup change', '.dataTables_filter input', function () {
                if ($.fn.DataTable.isDataTable('.example')) {
                    $('.example').DataTable().draw();
                }
            });
        }, 500);

        // Initialize select2
        $('#book_id').select2();

        // Store book options
        setTimeout(function () {
            allBookOptions = $('#book_id option').clone();
            console.log('Stored ' + allBookOptions.length + ' book options');
        }, 100);

        // Book search functionality
        $('#book_search').on('keyup', function () {
            var searchTerm = $(this).val().toLowerCase().trim();
            console.log('Searching for: "' + searchTerm + '"');
            if (!allBookOptions) return;

            $('#book_id').select2('destroy');

            if (searchTerm === '') {
                $('#book_id').html(allBookOptions.clone());
            } else {
                var filteredOptions = $('<select>');
                var matchCount = 0;

                allBookOptions.each(function () {
                    var $option = $(this);
                    var optionValue = $option.val();
                    if (optionValue === '') {
                        filteredOptions.append($option.clone());
                        return;
                    }

                    var bookNo = ($option.attr('data-book-no') || '').toLowerCase();
                    var searchText = $option.attr('data-search-text') || '';
                    var isMatch = false;

                    if (!isNaN(searchTerm) && searchTerm !== '') {
                        if (bookNo === searchTerm) {
                            isMatch = true;
                            console.log('Exact book number match: ' + bookNo);
                        }
                    } else {
                        var titleMatch = searchText.match(/\|\|\|title:(.*?)\|\|\|/);
                        var authorMatch = searchText.match(/\|\|\|author:(.*?)\|\|\|/);
                        var subjectMatch = searchText.match(/\|\|\|subject:(.*?)\|\|\|/);

                        if (
                            (titleMatch && titleMatch[1].toLowerCase().indexOf(searchTerm) !== -1) ||
                            (authorMatch && authorMatch[1].toLowerCase().indexOf(searchTerm) !== -1) ||
                            (subjectMatch && subjectMatch[1].toLowerCase().indexOf(searchTerm) !== -1)
                        ) {
                            isMatch = true;
                        }
                    }

                    if (isMatch) {
                        filteredOptions.append($option.clone());
                        matchCount++;
                    }
                });

                console.log('Found ' + matchCount + ' matching books');
                $('#book_id').html(filteredOptions.html());
            }

            $('#book_id').select2();
        });

        // Clear search
        $('#clear_search').on('click', function () {
            console.log('Clearing search');
            $('#book_search').val('');
            if (allBookOptions) {
                $('#book_id').select2('destroy');
                $('#book_id').html(allBookOptions.clone());
                $('#book_id').select2();
            }
        });

        function libNormalizeYmd(val) {
            if (!val || typeof val !== 'string') {
                return '';
            }
            val = val.trim();
            var m = val.match(/^(\d{4})[-\/\.](\d{1,2})[-\/\.](\d{1,2})$/);
            if (!m) {
                return '';
            }
            return m[1] + '-' + ('0' + m[2]).slice(-2) + '-' + ('0' + m[3]).slice(-2);
        }

        $('#form1').on('submit', function (e) {
            var issue = libNormalizeYmd($('#issue_date').val());
            var due = libNormalizeYmd($('#dateto').val());
            if (issue && due && due < issue) {
                var msg = <?php echo json_encode($this->lang->line('due_date_must_be_after_issue_date')); ?>;
                if (!msg) {
                    msg = 'Due return date cannot be earlier than the issue date.';
                }
                alert(msg);
                e.preventDefault();
                return false;
            }
        });
    });

    $(document).on('change', '#book_id', function () {
        var book_id = $(this).val();
        availableQuantity(book_id);
    });

    function availableQuantity(book_id) {
        $('#book_already_issued').html('');
        $('.ava_quantity').html(0);
        if (book_id != "") {
            $.ajax({
                type: "POST",
                url: base_url + "admin/book/getAvailQuantity",
                data: { 'book_id': book_id },
                dataType: "json",
                beforeSend: function () {
                    $('.qty_error').show();
                    $('.ava_quantity').html("<?php echo $this->lang->line('loading'); ?>");
                },
                success: function (data) {
                    $('.ava_quantity').html(data.qty);
                }
            });
        } else {
            $('.qty_error').hide();
        }
    }

    /**
     * ✅ Confirm Return Modal – Plain editable field (no popup)
     *  - Prevents global Nepali datepicker from attaching
     *  - Always sets today's BS date
     *  - Leaves the input fully editable (manual typing)
     */
    function computeTodayBs() {
        var bsFormatted = '';
        if (window.NepaliFunctions) {
            try {
                if (typeof NepaliFunctions.GetCurrentBsDate === 'function') {
                    var today = NepaliFunctions.GetCurrentBsDate();
                    if (today && today.year && today.month && today.day) {
                        bsFormatted = today.year + '-' + String(today.month).padStart(2, '0') + '-' + String(today.day).padStart(2, '0');
                    }
                }
                if (!bsFormatted && typeof NepaliFunctions.AD2BS === 'function') {
                    var todayAd = new Date();
                    var bs = NepaliFunctions.AD2BS({
                        year: todayAd.getFullYear(),
                        month: todayAd.getMonth() + 1,
                        day: todayAd.getDate()
                    });
                    if (bs && bs.year && bs.month && bs.day) {
                        bsFormatted = bs.year + '-' + String(bs.month).padStart(2, '0') + '-' + String(bs.day).padStart(2, '0');
                    }
                }
            } catch (error) {
                console.warn('Unable to compute BS date', error);
            }
        }
        return bsFormatted;
    }

    // Prevent global initializer from attaching to return modal date field
    function preventReturnModalDatepicker() {
        var $input = $('#confirm-return #date');
        if (!$input.length) return;

        // Mark as already initialized to prevent global init
        $input.data('ndp-initialized', true);
        $input.data('ndp-skip', true);
        
        // Destroy any existing Nepali datepicker instance
        try {
            if ($input.data('nepaliDatePicker')) {
                var picker = $input.data('nepaliDatePicker');
                if (picker && typeof picker.destroy === 'function') {
                    picker.destroy();
                }
            }
        } catch (error) {
            // Ignore errors
        }
        
        // Remove all datepicker-related data and classes
        $input.removeData('nepaliDatePicker')
              .removeClass('nepali-datepicker date-bs')
              .addClass('no-nepali-datepicker')
              .off('.ndp')
              .off('click.ndp');
        
        // Remove any datepicker UI elements
        $input.next('.nepali-date-picker').remove();
        $input.siblings('.nepali-date-picker').remove();
        $input.parent().find('.nepali-date-picker').remove();

        // Keep the field fully editable
        $input.prop('readonly', false).css('background-color', 'white');
        
        // Always set today's date
        var todayBs = computeTodayBs();
        if (todayBs) {
            $input.val(todayBs);
        }
    }

    // Intercept global Nepali datepicker initialization for return modal date field
    // This runs immediately to catch the datepicker before global init attaches it
    (function() {
        var checkAndIntercept = function() {
            if (typeof $ !== 'undefined' && typeof $.fn !== 'undefined' && typeof $.fn.nepaliDatePicker !== 'undefined') {
                var originalNepaliDP = $.fn.nepaliDatePicker;
                if (originalNepaliDP._intercepted) return; // Already intercepted
                originalNepaliDP._intercepted = true;
                
                $.fn.nepaliDatePicker = function(options) {
                    // Block initialization for return modal date field
                    var $self = this;
                    var isReturnModalDate = false;
                    
                    $self.each(function() {
                        var $el = $(this);
                        if ($el.attr('id') === 'date' && $el.closest('#confirm-return').length > 0) {
                            isReturnModalDate = true;
                            return false; // break
                        }
                    });
                    
                    if (isReturnModalDate) {
                        return $self;
                    }
                    
                    return originalNepaliDP.apply(this, arguments);
                };
            } else {
                // Retry if jQuery or datepicker not ready yet
                setTimeout(checkAndIntercept, 100);
            }
        };
        checkAndIntercept();
    })();

    var libFineEnabled = <?php echo $lib_fine_on ? 'true' : 'false'; ?>;
    var libRenewEnabled = <?php echo $lib_renew_on ? 'true' : 'false'; ?>;
    var libRenewalDays = <?php echo (int) $lib_renewal_days; ?>;
    var libCurrencySymbol = <?php echo json_encode($currency_symbol); ?>;
    var renewConfirmMsg = <?php echo json_encode($this->lang->line('renew_book_confirm') . ' (+' . (int) $lib_renewal_days . ' days)'); ?>;

    function refreshReturnFinePreview() {
        if (!libFineEnabled) {
            return;
        }
        var issueId = $('#return_model_id').val();
        var returnDate = $('#confirm-return #date').val();
        if (!issueId || !returnDate) {
            $('#return-fine-wrap').hide();
            return;
        }
        $.ajax({
            type: 'POST',
            url: base_url + 'admin/member/bookfine_preview',
            data: {
                id: issueId,
                date: returnDate
            },
            dataType: 'json',
            success: function (res) {
                if (res.status !== 'success') {
                    $('#return-fine-wrap').hide();
                    return;
                }
                if (parseFloat(res.amount) > 0) {
                    var summary = res.amount_display;
                    if (res.overdue_days) {
                        summary += ' (' + res.overdue_days + ' <?php echo addslashes($this->lang->line('overdue_days')); ?>)';
                    }
                    $('#return-fine-summary').text(summary);
                    $('#fine_paid').val(parseFloat(res.amount).toFixed(2));
                    $('#return-fine-wrap').show();
                } else {
                    $('#return-fine-summary').text('<?php echo addslashes($this->lang->line('no_fine_due')); ?>');
                    $('#fine_paid').val('0');
                    $('#return-fine-wrap').show();
                }
            }
        });
    }

    // Renew outstanding issue
    $(document).on('click', '.renew-book-btn', function (e) {
        e.preventDefault();
        if (!libRenewEnabled) {
            return;
        }
        var $btn = $(this);
        var title = $btn.data('book-title') || '';
        var msg = renewConfirmMsg;
        if (title) {
            msg = title + '\n\n' + msg;
        }
        if (!confirm(msg)) {
            return;
        }
        $.ajax({
            type: 'POST',
            url: base_url + 'admin/member/bookrenew',
            data: {
                id: $btn.data('record-id'),
                member_id: $btn.data('record-member_id')
            },
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    successMsg(res.message || '<?php echo addslashes($this->lang->line('success_message')); ?>');
                    setTimeout(function () { location.reload(); }, 500);
                } else {
                    var err = res.message || '<?php echo addslashes($this->lang->line('error')); ?>';
                    if (res.error) {
                        err = typeof res.error === 'string' ? res.error : JSON.stringify(res.error);
                    }
                    alert(err);
                }
            },
            error: function () {
                alert('<?php echo addslashes($this->lang->line('error')); ?>');
            }
        });
    });

    // Return modal open handler
    $(document).on('click', '.return-book-btn', function (e) {
        e.preventDefault();
        var recordId = $(this).data('record-id');
        var memberId = $(this).data('record-member_id');
        $('#return_model_id').val(recordId);
        $('#return_model_member_id').val(memberId);
        $('#error').html('');
        $('#return-fine-wrap').hide();
        $('#fine_paid').val('0');
        $('#confirm-return').modal('show');
    });

    $(document).on('change keyup', '#confirm-return #date', function () {
        refreshReturnFinePreview();
    });

    $(document).on('shown.bs.modal', '#confirm-return', function () {
        preventReturnModalDatepicker();
        refreshReturnFinePreview();
        
        // Continuously monitor and prevent datepicker attachment (MutationObserver might try to attach it)
        var preventInterval = setInterval(function() {
            var $input = $('#confirm-return #date');
            if (!$input.length || !$('#confirm-return').is(':visible')) {
                clearInterval(preventInterval);
                return;
            }
            
            // Check if datepicker was attached and destroy it
            if ($input.data('nepaliDatePicker') || $input.hasClass('nepali-datepicker')) {
                preventReturnModalDatepicker();
            }
        }, 100);
        
        // Clear interval when modal is hidden
        $(this).on('hidden.bs.modal', function() {
            clearInterval(preventInterval);
        });
    });

    // Also prevent on modal show (before shown)
    $(document).on('show.bs.modal', '#confirm-return', function () {
        preventReturnModalDatepicker();
    });

    // 🟢 Form submit (Return Book)
    $("form#return_book").submit(function (e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var $btn = form.find("button[type=submit]");
        $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(),
            dataType: 'JSON',
            beforeSend: function () {
                $btn.button('loading');
            },
            success: function (response) {
                if (response.status == 'fail') {
                    $.each(response.error, function (key, value) {
                        $('#error').html(value);
                    });
                    $btn.button('reset');
                }
                if (response.status == 'success') {
                    successMsg(response.message);
                    $('#confirm-return').modal('hide');
                    setTimeout(function () { location.reload(); }, 500);
                }
            },
            error: function () {
                $btn.button('reset');
            }
        });
    });
</script>

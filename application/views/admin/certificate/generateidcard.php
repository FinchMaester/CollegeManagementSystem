<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">  
    <section class="content-header">
        <h1><i class="fa fa-newspaper-o"></i> <?php //echo $this->lang->line('certificate'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <?php if ($this->session->flashdata('msg')) { ?>
            <?php 
                echo $this->session->flashdata('msg');
                $this->session->unset_userdata('msg');
            ?>
        <?php } ?>  
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <form role="form" action="<?php echo site_url('admin/generateidcard/search') ?>" method="post" class="">
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                        <select id="section_id" name="section_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                        <select id="program_id" name="program_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label> <small class="req"> *</small>
                                        <select id="class_id" name="class_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger" id="error_class_id"></span>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('id_card_template'); ?></label><small class="req"> *</small>
                                        <select id="id_card" name="id_card" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
                                            if (isset($idcardlist)) {
                                                foreach ($idcardlist as $list) {
                                                    ?>
                                                    <option value="<?php echo $list->id ?>" <?php if (set_value('id_card') == $list->id) echo "selected=selected" ?>><?php echo $list->title ?></option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('id_card'); ?></span>
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
                        <form method="post" action="<?php echo base_url('admin/generateidcard/generatemultiple') ?>">
                            <div class="" id="duefee">
                                <div class="box-header ptbnull"></div>   
                                <div class="box-header ptbnull">
                                    <h3 class="box-title titlefix"><i class="fa fa-users"></i> <?php echo $this->lang->line('student_list'); ?></h3>
                                    <button class="btn btn-info btn-sm printSelected pull-right" type="button" name="generate" title="<?php echo $this->lang->line('generate_certificate'); ?>"><?php echo $this->lang->line('generate'); ?></button>
                                </div>
                                <div class="box-body table-responsive overflow-visible">
                                    <div class="download_label"><?php echo $this->lang->line('student_list'); ?></div>
                                    <div class="tab-pane active table-responsive no-padding" id="tab_1">
                                        <table class="table table-striped table-bordered table-hover example" cellspacing="0" width="100%">
                                            <thead>
                                                <tr> 
                                                    <th><input type="checkbox" id="select_all" /></th>
                                                    <?php if (!$adm_auto_insert) { ?>
                                                        <th><?php echo $this->lang->line('admission_no'); ?></th>
                                                    <?php } ?>
                                                    <th><?php echo $this->lang->line('student_name'); ?></th>
                                                    <th><?php echo $this->lang->line('class'); ?></th>
                                                    <th><?php echo $this->lang->line('program'); ?></th>
                                                    <?php if ($sch_setting->father_name) { ?>
                                                        <th><?php echo $this->lang->line('father_name'); ?></th>
                                                    <?php } ?>
                                                    <th><?php echo $this->lang->line('date_of_birth'); ?></th>
                                                    <th><?php echo $this->lang->line('gender'); ?></th>
                                                    <?php if ($sch_setting->category) { ?>
                                                        <th><?php echo $this->lang->line('category'); ?></th>
                                                    <?php } if ($sch_setting->mobile_no) { ?>
                                                        <th><?php echo $this->lang->line('mobile_number'); ?></th>
                                                    <?php } ?>
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
                                                            <td class="text-center"><input type="checkbox" class="checkbox center-block" data-student_id="<?php echo $student['id'] ?>"  name="check" id="check" value="<?php echo $student['id'] ?>">
                                                                <input type="hidden" name="class_id" id="class_id" value="<?php echo $student['class_id'] ?>">
                                                                <input type="hidden" name="id_card_id" id="id_card_id" value="<?php echo $idcardResult[0]->id ?>">
                                                            </td>
                                                            <?php if (!$adm_auto_insert) { ?>
                                                                <td><?php echo $student['admission_no']; ?></td>
                                                            <?php } ?>
                                                            <td>
                                                                <a href="<?php echo base_url(); ?>student/view/<?php echo $student['id']; ?>"><?php echo $this->customlib->getFullName($student['firstname'],$student['middlename'],$student['lastname'],$sch_setting->middlename,$sch_setting->lastname); ?>
                                                                </a>
                                                            </td>
                                                            <td><?php echo $student['class'] . "(" . $student['section'] . ")" ?></td>
                                                            <?php if (!empty($student['program_name'])): ?>
                                                                <td><?php echo $student['program_name']; ?></td>
                                                                <?php endif; ?>
                                                            <?php if ($sch_setting->father_name) { ?>
                                                                <td><?php echo $student['father_name']; ?></td>
                                                            <?php } ?>
                                                            <td>
                                                            <?php echo $student['dob']; ?>
                                                            </td>
                                                            <td><?php echo $this->lang->line(strtolower($student['gender'])); ?></td>
                                                            <?php if ($sch_setting->category) { ?>
                                                                <td><?php echo $student['category']; ?></td>
                                                            <?php } if ($sch_setting->mobile_no) { ?>
                                                                <td><?php echo $student['mobileno']; ?></td>
                                                            <?php } ?>
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
<div class="response"> 
</div>

<!-- ======== IMPROVED CRASH GUARDS & UTILITIES ======== -->
<script>
// Comprehensive crash guards for missing libraries
(function() {
    'use strict';
    
    // Ensure jQuery is available
    if (typeof jQuery === 'undefined') {
        console.warn('jQuery not loaded');
        return;
    }
    
    var $ = jQuery;
    
    // Fix jQuery UI issues
    if (!$.ui || !$.widget || !$.widget.bridge) {
        $.widget = $.widget || {};
        $.widget.bridge = $.widget.bridge || function(name, object){
            $.fn[name] = $.fn[name] || function(){ return this; };
        };
        $.ui = $.ui || {};
        $.ui.button = $.ui.button || function(){};
    }
    
    // Fix mCustomScrollbar
    if (!$.fn.mCustomScrollbar) {
        $.fn.mCustomScrollbar = function(options){ 
            console.warn('mCustomScrollbar not available, using fallback');
            return this; 
        };
    }
    
    // Fix other potential missing plugins
    if (!$.fn.DataTable && !$.fn.dataTable) {
        $.fn.DataTable = $.fn.dataTable = function(options) {
            console.warn('DataTables not available');
            return this;
        };
    }
    
})();
</script>

<script type="text/javascript">
(function($) {
    'use strict';
    
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
                timeout: 10000,
                success: function (response) {
                    var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                    
                    if (response && $.isArray(response)) {
                        $.each(response, function (index, section) {
                            var selected = (selectedSection == section.id) ? 'selected' : '';
                            html += '<option value="' + section.id + '" ' + selected + '>' + section.section + '</option>';
                        });
                    }
                    
                    $('#section_id').html(html);
                    
                    if(selectedSection) {
                        loadPrograms(selectedSection);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error loading sections:", error);
                    $('#section_id').html('<option value="">Error loading sections</option>');
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
                timeout: 10000,
                success: function (response) {
                    var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                    
                    if(response && $.isArray(response) && response.length > 0) {
                        $.each(response, function (index, program) {
                            var selected = (selectedProgram == program.id) ? 'selected' : '';
                            html += '<option value="' + program.id + '" ' + selected + '>' + program.title + '</option>';
                        });
                    } else {
                        html += '<option value="" disabled>No programs found</option>';
                    }
                    
                    $('#program_id').html(html);
                    
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
                timeout: 10000,
                success: function(response) {
                    var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                    
                    if (response && $.isArray(response) && response.length > 0) {
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
})(jQuery);
</script>

<script type="text/javascript">
(function($) {
    'use strict';
    
    $(document).ready(function () {
        $('#select_all').on('click', function () {
            var isChecked = this.checked;
            $('.checkbox').each(function () {
                this.checked = isChecked;
            });
        });

        $(document).on('click', '.checkbox', function () {
            var totalCheckboxes = $('.checkbox').length;
            var checkedCheckboxes = $('.checkbox:checked').length;
            $('#select_all').prop('checked', totalCheckboxes === checkedCheckboxes);
        });
    });
})(jQuery);
</script>

<script type="text/javascript">
(function($) {
    'use strict';
    
    $(document).ready(function () {
        $(document).on('click', '.printSelected', function (e) {
            e.preventDefault();
            
            var array_to_print = [];
            var idCard = $("#id_card").val(); 
            var classId = null;
            var checkedBoxes = $("input[name='check']:checked");
            
            if (checkedBoxes.length > 0) {
                classId = checkedBoxes.first().closest('tr').find("input[name='class_id']").val();
            }
            
            // Collect selected student IDs
            checkedBoxes.each(function () {
                var studentId = $(this).data('student_id');
                if (studentId) {
                    array_to_print.push({ "student_id": studentId });
                }
            });
            
            // Validation
            if (array_to_print.length === 0) {
                alert("<?php echo $this->lang->line('no_record_selected'); ?>");
                return false;
            }
            if (!idCard) {
                alert("ID Card template is required. Please select an ID card template from the search form.");
                return false;
            }
            if (!classId) {
                alert("Class ID is missing. Please try searching again.");
                return false;
            }
            
            var $button = $(this);
            var originalText = $button.html();
            $button.html('<i class="fa fa-spinner fa-spin"></i> Generating...').prop('disabled', true);

            // Prepare form data
            var postData = {
                'data': JSON.stringify(array_to_print),
                'class_id': classId,
                'id_card': idCard,
                '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
            };

            console.log('Sending data:', postData);

            $.ajax({
                url: '<?php echo site_url("admin/generateidcard/generatemultiple") ?>',
                type: 'POST',
                dataType: 'json',
                data: postData,
                timeout: 120000,
                success: function (response) {
                    console.log('Response received:', response);
                    $button.html(originalText).prop('disabled', false);
                    
                    if (response && response.status === 1 && response.page) {
                        openPrintWindow(response.page);
                    } else if (response && response.status === 'success' && response.html) {
                        // Alternative response format
                        openPrintWindow(response.html);
                    } else {
                        var errorMsg = (response && response.message) ? response.message : "Error generating ID cards. Please try again.";
                        alert(errorMsg);
                        console.error("Invalid response:", response);
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    console.error("Response Text:", xhr.responseText);
                    console.error("Status Code:", xhr.status);
                    
                    $button.html(originalText).prop('disabled', false);
                    
                    var errorMessage = "Error generating ID cards. ";
                    if (xhr.status === 0) {
                        errorMessage += "Network error or request timeout.";
                    } else if (xhr.status === 500) {
                        errorMessage += "Server error. Please check server logs.";
                    } else if (xhr.status === 404) {
                        errorMessage += "Controller method not found.";
                    } else {
                        errorMessage += "HTTP " + xhr.status + " error.";
                    }
                    
                    alert(errorMessage);
                }
            });
        });
    });
    
    // Improved popup function with better error handling
    function openPrintWindow(htmlContent) {
        try {
            // Method 1: Try iframe approach
            var $iframe = $('<iframe>', {
                id: 'printFrame',
                name: 'printFrame',
                style: 'position:fixed;top:-1000px;left:-1000px;width:1px;height:1px;border:0;'
            });

            $("body").append($iframe);
            
            var frameWindow = $iframe[0].contentWindow;
            var frameDocument = frameWindow.document;
            
            frameDocument.open();
            frameDocument.write(htmlContent);
            frameDocument.close();
            
            // Wait a bit for content to load, then print
            setTimeout(function() {
                try {
                    frameWindow.focus();
                    frameWindow.print();
                } catch(printError) {
                    console.warn('Print failed:', printError);
                    // Fallback: open in new window
                    fallbackToNewWindow(htmlContent);
                }
                
                // Clean up
                setTimeout(function() {
                    $iframe.remove();
                }, 1000);
            }, 500);
            
        } catch (iframeError) {
            console.warn('Iframe method failed:', iframeError);
            fallbackToNewWindow(htmlContent);
        }
    }
    
    function fallbackToNewWindow(htmlContent) {
        try {
            var printWindow = window.open('', '_blank', 'width=800,height=600');
            if (printWindow) {
                printWindow.document.open();
                printWindow.document.write(htmlContent);
                printWindow.document.close();
                
                setTimeout(function() {
                    try {
                        printWindow.print();
                    } catch(e) {
                        console.warn('New window print failed:', e);
                    }
                }, 500);
            } else {
                // Popup blocked or failed - show in current page
                showInCurrentPage(htmlContent);
            }
        } catch (windowError) {
            console.error('New window method failed:', windowError);
            showInCurrentPage(htmlContent);
        }
    }
    
    function showInCurrentPage(htmlContent) {
        // Last resort: show content in a modal or new div
        var $preview = $('#idCardsPreview');
        if ($preview.length === 0) {
            $preview = $('<div id="idCardsPreview" style="margin-top:20px;"></div>').appendTo('.content-wrapper');
        }
        $preview.html('<h3>Generated ID Cards (Print manually)</h3>' + htmlContent);
        
        // Scroll to the preview
        $('html, body').animate({
            scrollTop: $preview.offset().top
        }, 500);
    }
    
    // Make functions available globally
    window.openPrintWindow = openPrintWindow;
    window.Popup = openPrintWindow; // Backward compatibility
    
})(jQuery);
</script>
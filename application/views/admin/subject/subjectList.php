<style type="text/css">
    @media print
    {
        .no-print, .no-print *
        {
            display: none !important;
        }
    }
</style>
<div class="content-wrapper" style="min-height: 946px;">  
    <section class="content-header">
        <h1>
            <i class="fa fa-mortar-board"></i> <?php echo $this->lang->line('academics'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <?php
            if ($this->rbac->hasPrivilege('subject', 'can_add')) {
                ?>        
                <div class="col-md-4">          
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo $this->lang->line('add_subject'); ?></h3>
                            <!-- Add Non-Academic Button -->
                            <button type="button" class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#nonAcademicModal" id="openNonAcademicModal">
                                <i class="fa fa-plus"></i> Add Non-Academic Subject
                            </button>
                        </div>
                        <form id="form1" action="<?php echo site_url('admin/subject') ?>"  id="employeeform" name="employeeform" method="post" accept-charset="utf-8">
                            <div class="box-body">
                                <?php if ($this->session->flashdata('msg')) { ?>
                                    <?php echo $this->session->flashdata('msg');
                                    $this->session->unset_userdata('msg'); ?>
                                <?php } ?>     
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('subject_name'); ?></label><small class="req"> *</small>
                                    <input autofocus="" id="category" name="name" placeholder="" type="text" class="form-control"  value="<?php echo set_value('name'); ?>" />
                                    <span class="text-danger"><?php echo form_error('name'); ?></span>
                                </div>
                                <?php
                                foreach ($subject_types as $subject_type_key => $subject_type_value) {
                                    ?>

                                    <label class="radio-inline">
                                        <input type="radio" value="<?php echo $subject_type_key ?>" name="type" <?php echo set_radio('type', $subject_type_key, set_value('type')); ?> ><?php echo $subject_type_value; ?> 
                                    </label>
                                    <?php
                                }
                                ?>
                                <span class="text-danger"><?php echo form_error('type'); ?></span>

                                <div class="form-group"><br>
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('subject_code'); ?></label>
                                    <input id="category" name="code" placeholder="" type="text" class="form-control"  value="<?php echo set_value('code'); ?>" />
                                    <span class="text-danger"><?php echo form_error('code'); ?></span>
                                </div>
                            </div>
                            <div class="box-footer">
                                <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php } ?>
            <div class="col-md-<?php
            if ($this->rbac->hasPrivilege('subject', 'can_add')) {
                echo "8";
            } else {
                echo "12";
            }
            ?>">            
                <div class="box box-primary" id="sublist">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('subject_list'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive mailbox-messages">
                            <div class="download_label"><?php echo $this->lang->line('subject_list'); ?></div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('subject'); ?></th>
                                        <th><?php echo $this->lang->line('subject_code'); ?></th>
                                        <th><?php echo $this->lang->line('subject_type'); ?></th>
                                        <th>Category</th>
                                        <th class="text-right no-print noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $count = 1;
                                    foreach ($subjectlist as $subject) {
                                        ?>
                                        <tr>
                                            <td class="mailbox-name"> <?php echo $subject['name'] ?></td>
                                            <td class="mailbox-name"><?php echo $subject['code'] ?></td>
                                            <td class="mailbox-name">
                                                <?php 
                                                    if (isset($subject['is_academic']) && $subject['is_academic'] == 0) {
                                                        echo "<span class='label label-info'>Non-Academic</span>";
                                                    } else {
                                                        echo ucfirst($subject['type']);
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <?php echo (isset($subject['is_academic']) && $subject['is_academic'] == 1) ? 'Academic' : 'Non-Academic'; ?>
                                            </td>
                                            <td class="mailbox-date pull-right no-print">
                                                <?php
                                                if ($this->rbac->hasPrivilege('subject', 'can_edit')) {
                                                    ?>
                                                    <a href="<?php echo base_url(); ?>admin/subject/edit/<?php echo $subject['id'] ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                    <?php
                                                }
                                                if ($this->rbac->hasPrivilege('subject', 'can_delete')) {
                                                    ?>
                                                    <a href="<?php echo base_url(); ?>admin/subject/delete/<?php echo $subject['id'] ?>"class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
                                                        <i class="fa fa-remove"></i>
                                                    </a>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    $count++;
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> 
        </div> 
    </section>
</div>

<!-- Modal for Non-Academic Subject (moved outside form container) -->
<div class="modal fade" id="nonAcademicModal" tabindex="-1" role="dialog" aria-labelledby="nonAcademicModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form id="nonAcademicForm" action="javascript:void(0);" method="post" onsubmit="return false;">
        <div class="modal-header">
          <h4 class="modal-title" id="nonAcademicModalLabel">Add Non-Academic Subject</h4>
          <button type="button" class="close" id="closeNonAcademicModal" aria-label="Close">
            <span>&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <?php echo $this->customlib->getCSRF(); ?>
          <div class="form-group">
            <label>Subject Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="nonAcademicName" class="form-control">
          </div>
          <div class="form-group">
            <label>Subject Code</label>
            <input type="text" name="code" id="nonAcademicCode" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save</button>
          <button type="button" class="btn btn-default" id="cancelNonAcademicModal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        console.log('=== SUBJECT LIST PAGE LOADED ===');
        
        $("#btnreset").click(function () {
            $("#form1")[0].reset();
        });
        
        // Flag to track if we're intentionally closing the modal
        var modalClosingIntentionally = false;
        
        // Prevent modal from closing on backdrop click or escape key when opened
        $('#nonAcademicModal').on('show.bs.modal', function(e) {
            console.log('Modal show event triggered');
            modalClosingIntentionally = false;
            var modal = $(this);
            try {
                modal.data('bs.modal').options.backdrop = 'static';
                modal.data('bs.modal').options.keyboard = false;
                console.log('Modal options set: backdrop=static, keyboard=false');
            } catch(e) {
                console.log('Error setting modal options:', e);
            }
        });
        
        $('#nonAcademicModal').on('shown.bs.modal', function() {
            console.log('Modal is now shown');
        });
        
        // CRITICAL: Prevent modal from hiding unless we explicitly want it to
        $('#nonAcademicModal').on('hide.bs.modal', function(e) {
            console.log('=== MODAL HIDE EVENT TRIGGERED ===');
            console.log('Event:', e);
            console.log('Event type:', e.type);
            console.log('Event target:', e.target);
            console.log('modalClosingIntentionally:', modalClosingIntentionally);
            console.log('Stack trace:', new Error().stack);
            
            // If we're not intentionally closing, prevent it
            if (!modalClosingIntentionally) {
                console.log('PREVENTING MODAL CLOSE - not intentional');
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                return false;
            }
            console.log('Allowing modal to close - intentional');
        });
        
        // CRITICAL: Prevent form submission completely
        $('#nonAcademicForm').on('submit', function(e) {
            console.log('=== FORM SUBMIT EVENT TRIGGERED ===');
            console.log('Event object:', e);
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            console.log('Form submit prevented');
            return false;
        });
        
        // Handle button click - prevent default and handle manually
        $('#nonAcademicForm button[type="submit"]').on('click', function(e) {
            console.log('=== SUBMIT BUTTON CLICKED ===');
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            console.log('Button click prevented');
            
            var form = $('#nonAcademicForm');
            var nameField = $('#nonAcademicName');
            var codeField = $('#nonAcademicCode');
            
            console.log('Form element:', form);
            console.log('Name field value:', nameField.val());
            console.log('Code field value:', codeField.val());
            
            // Basic validation
            if (!nameField.val() || nameField.val().trim() === '') {
                console.log('Validation failed: Name is required');
                alert('Subject Name is required');
                nameField.focus();
                return false;
            }
            
            var formData = form.serialize();
            console.log('Form data:', formData);
            var submitBtn = $(this);
            var originalBtnText = submitBtn.html();
            
            // Disable submit button to prevent double submission
            submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
            console.log('Submit button disabled');
            
            var ajaxUrl = '<?php echo site_url('admin/subject/create_nonacademic'); ?>';
            console.log('Making AJAX request to:', ajaxUrl);
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: formData,
                dataType: 'json',
                beforeSend: function() {
                    console.log('AJAX beforeSend called');
                },
                success: function(response) {
                    console.log('AJAX Success - Response:', response);
                    if (response && response.status === 'success') {
                        console.log('Success - closing modal and reloading');
                        // Set flag to allow intentional close
                        modalClosingIntentionally = true;
                        // Close modal
                        $('#nonAcademicModal').modal('hide');
                        // Reset form
                        form[0].reset();
                        // Reload page to show new subject
                        setTimeout(function() {
                            console.log('Reloading page...');
                            location.reload();
                        }, 300);
                    } else {
                        console.log('Success but status not success:', response);
                        // Show error message
                        var errorMsg = (response && response.message) ? response.message : 'Failed to save non-academic subject. Please try again.';
                        alert(errorMsg);
                        submitBtn.prop('disabled', false).html(originalBtnText);
                    }
                },
                error: function(xhr, status, error) {
                    console.log('=== AJAX ERROR ===');
                    console.log('Status:', status);
                    console.log('Error:', error);
                    console.log('Response Text:', xhr.responseText);
                    console.log('Response Status:', xhr.status);
                    
                    // Try to parse as JSON first
                    try {
                        var jsonResponse = JSON.parse(xhr.responseText);
                        console.log('Parsed JSON response:', jsonResponse);
                        if (jsonResponse.status === 'success') {
                            console.log('JSON success - closing modal');
                            modalClosingIntentionally = true;
                            $('#nonAcademicModal').modal('hide');
                            form[0].reset();
                            setTimeout(function() {
                                location.reload();
                            }, 300);
                            return;
                        }
                        alert(jsonResponse.message || 'An error occurred. Please try again.');
                    } catch (e) {
                        console.log('Could not parse as JSON:', e);
                        // If response is HTML (validation errors), reload page
                        if (xhr.responseText && xhr.responseText.indexOf('<!DOCTYPE') !== -1) {
                            console.log('HTML response detected - reloading');
                            modalClosingIntentionally = true;
                            $('#nonAcademicModal').modal('hide');
                            setTimeout(function() {
                                location.reload();
                            }, 300);
                        } else {
                            console.log('Unknown error format');
                            alert('An error occurred. Please try again.');
                        }
                    }
                    submitBtn.prop('disabled', false).html(originalBtnText);
                },
                complete: function() {
                    console.log('AJAX complete');
                }
            });
            
            console.log('Returning false from button click handler');
            return false;
        });
        
        // Also prevent any other form submission attempts
        $('#nonAcademicForm input').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                console.log('Enter key pressed in form input');
                e.preventDefault();
                $('#nonAcademicForm button[type="submit"]').click();
                return false;
            }
        });
        
        // Handle cancel button click
        $('#cancelNonAcademicModal, #closeNonAcademicModal').on('click', function() {
            console.log('Cancel/Close button clicked');
            modalClosingIntentionally = true;
            $('#nonAcademicModal').modal('hide');
        });
        
        // Reset form when modal is closed
        $('#nonAcademicModal').on('hidden.bs.modal', function () {
            console.log('Modal hidden - resetting form');
            $('#nonAcademicForm')[0].reset();
            $('#nonAcademicForm').find('button[type="submit"]').prop('disabled', false).html('Save');
            modalClosingIntentionally = false;
        });
        
        console.log('=== ALL EVENT HANDLERS ATTACHED ===');
    });
</script>

<script type="text/javascript">
    var base_url = '<?php echo base_url() ?>';
    function printDiv(elem) {
        Popup(jQuery(elem).html());
    }

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
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/bootstrap/css/bootstrap.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/font-awesome.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/ionicons.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/AdminLTE.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/skins/_all-skins.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/iCheck/flat/blue.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/morris/morris.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/jvectormap/jquery-jvectormap-1.2.2.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/datepicker/datepicker3.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/daterangepicker/daterangepicker-bs3.css">');
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
</script>
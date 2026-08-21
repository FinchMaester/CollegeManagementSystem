
<div class="content-wrapper" style="min-height: 946px;">  
    <section class="content-header">
        <h1>
            <i class="fa fa-warning"></i> <?php echo $this->lang->line('access_denied'); ?></h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- general form elements -->
                <div class="box box-primary">

                    <div class="box-body">
                        <div class="alert alert-warning"><?php if ($this->session->flashdata('msg')) { 
                        echo $this->session->flashdata('msg'); 
                        }else{
                                    echo $this->lang->line('not_authoried');
                                } ?></div>

                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
(function() {
    console.group('🔒 UNAUTHORIZED ACCESS DEBUG');
    console.log('%c=== UNAUTHORIZED ACCESS DETAILED DEBUG ===', 'color: red; font-weight: bold; font-size: 14px;');
    
    // Get current URL info
    var currentUrl = window.location.href;
    var currentPath = window.location.pathname;
    console.log('%cCurrent URL:', 'color: orange; font-weight: bold;', currentUrl);
    console.log('%cCurrent Path:', 'color: orange; font-weight: bold;', currentPath);
    
    // Get referrer
    console.log('%cReferrer:', 'color: orange; font-weight: bold;', document.referrer || 'Direct access');
    
    // Check session data via AJAX
    $.ajax({
        url: '<?php echo base_url("admin/admin/getDebugInfo"); ?>',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            // CRITICAL: Check for superadmin first and show summary
            console.log('%c╔══════════════════════════════════════════════════════════════╗', 'color: red; font-weight: bold;');
            console.log('%c║  🔴 UNAUTHORIZED ACCESS - ROOT CAUSE ANALYSIS 🔴            ║', 'color: red; font-weight: bold;');
            console.log('%c╚══════════════════════════════════════════════════════════════╝', 'color: red; font-weight: bold;');
            
            if (response && response.status === 'success') {
                // Check if superadmin is missing from permissions
                var hasSuperadmin = false;
                if (response.database_checks && response.database_checks.all_role_permissions) {
                    hasSuperadmin = response.database_checks.all_role_permissions.some(function(perm) {
                        return perm.short_code && perm.short_code.toLowerCase() === 'superadmin';
                    });
                }
                
                if (!hasSuperadmin) {
                    console.error('%c❌ CRITICAL ISSUE FOUND: "superadmin" permission is MISSING from your role!', 'color: red; font-weight: bold; font-size: 16px; background: yellow; padding: 10px;');
                    console.error('%cYour role (ID: ' + (response.role_id || 'Unknown') + ') does NOT have "superadmin" permission in the roles_permissions table.', 'color: red; font-weight: bold;');
                    console.error('%cThis is why you are getting "Unauthorized" errors.', 'color: red; font-weight: bold;');
                    console.log('%cSOLUTION: Add "superadmin" permission to role_id=' + (response.role_id || '1') + ' in the roles_permissions table.', 'color: green; font-weight: bold; font-size: 14px;');
                    console.log('%cSQL to fix: INSERT INTO roles_permissions (role_id, perm_cat_id, can_view, can_add, can_edit, can_delete) SELECT ' + (response.role_id || '1') + ', id, 1, 1, 1, 1 FROM permission_category WHERE short_code = "superadmin";', 'color: cyan; font-weight: bold; font-size: 12px;');
                } else {
                    console.log('%c✓ "superadmin" permission found in role permissions', 'color: green; font-weight: bold;');
                }
                console.log('');
            }
            
            console.log('%c=== SESSION & ROLE DEBUG ===', 'color: blue; font-weight: bold;');
            
            if (response && response.status === 'success') {
                console.log('%cRole Name:', 'color: green; font-weight: bold;', response.role_name || 'Unknown');
                console.log('%cRole ID:', 'color: green; font-weight: bold;', response.role_id || 'Unknown');
                console.log('%cIs Super Admin:', 'color: green; font-weight: bold;', response.is_super_admin ? 'YES' : 'NO');
                console.log('%cAdmin Session:', 'color: green; font-weight: bold;', response.admin_session || 'Missing');
                console.log('%cController:', 'color: green; font-weight: bold;', response.controller || 'Unknown');
                console.log('%cMethod:', 'color: green; font-weight: bold;', response.method || 'Unknown');
                console.log('%cPermission Category:', 'color: green; font-weight: bold;', response.permission_category || 'Unknown');
                console.log('%cPermission Required:', 'color: green; font-weight: bold;', response.permission_required || 'Unknown');
                
                if (response.role_permissions) {
                    console.log('%c=== ROLE PERMISSIONS ===', 'color: purple; font-weight: bold;');
                    console.table(response.role_permissions);
                }
                
                if (response.permission_check_result) {
                    console.log('%c=== PERMISSION CHECK RESULT ===', 'color: purple; font-weight: bold;');
                    console.log('Permission Found:', response.permission_check_result.found ? 'YES' : 'NO');
                    console.log('Permission Value:', response.permission_check_result.value);
                    console.log('Available Keys:', response.permission_check_result.available_keys);
                }
                
                if (response.debug_messages && response.debug_messages.length > 0) {
                    console.log('%c=== DEBUG MESSAGES ===', 'color: purple; font-weight: bold;');
                    response.debug_messages.forEach(function(msg) {
                        console.log(msg);
                    });
                }
                
                // Always check for superadmin first (most important)
                if (response.superadmin_manual_check || response.superadmin_check_found) {
                    console.log('%c=== 🔍 SUPERADMIN PERMISSION INVESTIGATION ===', 'color: red; font-weight: bold; font-size: 16px; background: yellow; padding: 5px;');
                    
                    if (response.superadmin_check_details) {
                        console.log('%cSuperadmin check found in history:', 'color: orange; font-weight: bold;');
                        console.log('  Category:', response.superadmin_check_details.category);
                        console.log('  Permission:', response.superadmin_check_details.permission);
                        console.log('  Found:', response.superadmin_check_details.permission_found ? 'YES ✓' : 'NO ✗');
                        if (response.superadmin_check_details.error) {
                            console.error('  ERROR:', response.superadmin_check_details.error);
                        }
                    }
                    
                    if (response.superadmin_manual_check) {
                        console.log('%cManual superadmin check performed:', 'color: cyan; font-weight: bold;');
                        console.log('  Permission Category Exists:', response.superadmin_category_exists ? 'YES ✓' : 'NO ✗');
                        if (response.superadmin_category_data) {
                            console.log('  Category Data:');
                            console.table(response.superadmin_category_data);
                        } else {
                            console.warn('  ⚠️ "superadmin" category NOT FOUND in permission_category table!');
                        }
                        console.log('  Role Permission Exists:', response.superadmin_permission_exists ? 'YES ✓' : 'NO ✗');
                        if (response.superadmin_permission_data) {
                            console.log('  Permission Data:');
                            console.table(response.superadmin_permission_data);
                        } else {
                            console.warn('  ⚠️ Role ' + response.role_id + ' does NOT have "superadmin" permission in roles_permissions table!');
                        }
                    }
                    console.log('---');
                }
                
                if (response.all_recent_rbac_checks && response.all_recent_rbac_checks.length > 0) {
                    console.log('%c=== ALL RECENT RBAC CHECKS (Last ' + response.all_recent_rbac_checks.length + ') ===', 'color: magenta; font-weight: bold; font-size: 14px;');
                    response.all_recent_rbac_checks.forEach(function(check, index) {
                        var isFailed = !check.permission_found || check.error;
                        var isSuperadmin = check.category && check.category.toLowerCase() === 'superadmin';
                        var color = isFailed ? 'red' : 'green';
                        if (isSuperadmin) {
                            console.log('%c⚠️ Check #' + (index + 1) + ' - SUPERADMIN (FAILED):', 'color: red; font-weight: bold; font-size: 14px; background: yellow;');
                        } else {
                            console.log('%cCheck #' + (index + 1) + ':', 'color: ' + color + '; font-weight: bold;');
                        }
                        console.log('  Category:', check.category);
                        console.log('  Permission:', check.permission);
                        console.log('  Role:', check.role_name + ' (ID: ' + (check.role_id || 'Unknown') + ')');
                        console.log('  Found:', check.permission_found ? 'YES ✓' : 'NO ✗');
                        if (check.error) {
                            console.error('  ERROR:', check.error);
                        }
                        if (check.available_permissions) {
                            console.log('  Available Keys:', check.available_permissions);
                        }
                        if (check.permission_value !== undefined) {
                            console.log('  Value:', check.permission_value);
                        }
                        console.log('  Time:', check.check_time || 'Unknown');
                        console.log('---');
                    });
                } else {
                    console.log('%cNo RBAC check history found in session', 'color: gray; font-style: italic;');
                }
                
                if (response.rbac_debug) {
                    console.log('%c=== MOST RECENT RBAC CHECK ===', 'color: magenta; font-weight: bold;');
                    console.log('Category Checked:', response.rbac_debug.category);
                    console.log('Permission Required:', response.rbac_debug.permission);
                    console.log('Role Name:', response.rbac_debug.role_name);
                    console.log('Role ID:', response.rbac_debug.role_id || 'Unknown');
                    console.log('Permission Found:', response.rbac_debug.permission_found ? 'YES' : 'NO');
                    if (response.rbac_debug.available_permissions) {
                        console.log('Available Permissions:', response.rbac_debug.available_permissions);
                    }
                    if (response.rbac_debug.error) {
                        console.error('RBAC Error:', response.rbac_debug.error);
                    }
                    if (response.rbac_debug.permission_value !== undefined) {
                        console.log('Permission Value:', response.rbac_debug.permission_value);
                    }
                }
                
                if (response.database_checks) {
                    var isSuperadmin = response.permission_category && response.permission_category.toLowerCase() === 'superadmin';
                    if (isSuperadmin) {
                        console.log('%c=== ⚠️ SUPERADMIN PERMISSION CHECK - DATABASE VERIFICATION ===', 'color: red; font-weight: bold; font-size: 16px; background: yellow; padding: 5px;');
                    } else {
                        console.log('%c=== DATABASE VERIFICATION ===', 'color: red; font-weight: bold; font-size: 14px;');
                    }
                    console.log('%cPermission Category Exists:', 'color: orange; font-weight: bold;', response.database_checks.permission_category_exists ? 'YES ✓' : 'NO ✗');
                    console.log('%cSearched Category:', 'color: orange; font-weight: bold;', response.database_checks.searched_category || response.permission_category);
                    
                    if (response.database_checks.permission_category_data) {
                        console.log('%cPermission Category Data:', 'color: green; font-weight: bold;');
                        console.table(response.database_checks.permission_category_data);
                    }
                    
                    if (response.database_checks.case_insensitive_match) {
                        console.warn('%c⚠️ CASE SENSITIVITY ISSUE DETECTED!', 'color: red; font-weight: bold; font-size: 14px;');
                        console.log('Searched for:', response.database_checks.searched_category);
                        console.log('Found (different case):', response.database_checks.permission_category_data.short_code);
                        console.log('%cThis is likely the issue! The database has a different case than what the code is searching for.', 'color: orange; font-weight: bold;');
                    }
                    
                    if (!response.database_checks.permission_category_exists && response.database_checks.all_permission_categories) {
                        console.warn('%c⚠️ Permission category "' + response.permission_category + '" NOT FOUND in database!', 'color: red; font-weight: bold;');
                        console.log('%cSearched for:', 'color: yellow; font-weight: bold;', response.database_checks.searched_category);
                        console.log('%cAvailable Permission Categories:', 'color: yellow; font-weight: bold;');
                        console.table(response.database_checks.all_permission_categories);
                        
                        if (response.database_checks.similar_categories && response.database_checks.similar_categories.length > 0) {
                            console.log('%cSimilar Categories (possible typo?):', 'color: orange; font-weight: bold;');
                            console.table(response.database_checks.similar_categories);
                        }
                    }
                    
                    console.log('%cRole Permission Exists:', 'color: orange; font-weight: bold;', response.database_checks.role_permission_exists ? 'YES ✓' : 'NO ✗');
                    
                    if (response.database_checks.last_query) {
                        console.log('%cLast SQL Query:', 'color: cyan; font-weight: bold;', response.database_checks.last_query);
                    }
                    
                    if (!response.database_checks.role_permission_exists && response.database_checks.all_role_permissions) {
                        console.warn('%c⚠️ Role permission NOT FOUND! Showing all permissions for this role:', 'color: red; font-weight: bold;');
                        console.table(response.database_checks.all_role_permissions);
                    }
                }
            } else {
                console.error('%cFailed to get debug info:', 'color: red; font-weight: bold;', response);
            }
        },
        error: function(xhr, status, error) {
            console.error('%cAJAX Error getting debug info:', 'color: red; font-weight: bold;', status, error);
            console.log('Response:', xhr.responseText);
        },
        complete: function() {
            console.log('%c=== END DEBUG ===', 'color: red; font-weight: bold;');
            console.groupEnd();
        }
    });
    
    // Also log any stored debug info from session
    <?php if (isset($debug_info) && !empty($debug_info)): ?>
    console.log('%c=== PHP DEBUG INFO ===', 'color: cyan; font-weight: bold;');
    console.log(<?php echo json_encode($debug_info); ?>);
    <?php endif; ?>
    
    // Check for RBAC debug info from session
    <?php 
    $rbac_debug = $this->session->userdata('rbac_debug');
    if ($rbac_debug): 
    ?>
    console.log('%c=== RBAC PERMISSION CHECK DEBUG ===', 'color: magenta; font-weight: bold;');
    console.log('Category Checked:', <?php echo json_encode($rbac_debug['category']); ?>);
    console.log('Permission Required:', <?php echo json_encode($rbac_debug['permission']); ?>);
    console.log('Role Name:', <?php echo json_encode($rbac_debug['role_name']); ?>);
    console.log('Role ID:', <?php echo json_encode(isset($rbac_debug['role_id']) ? $rbac_debug['role_id'] : 'Unknown'); ?>);
    console.log('Permission Found:', <?php echo json_encode(isset($rbac_debug['permission_found']) ? $rbac_debug['permission_found'] : false); ?>);
    <?php if (isset($rbac_debug['available_permissions'])): ?>
    console.log('Available Permissions:', <?php echo json_encode($rbac_debug['available_permissions']); ?>);
    <?php endif; ?>
    <?php if (isset($rbac_debug['error'])): ?>
    console.error('Error:', <?php echo json_encode($rbac_debug['error']); ?>);
    <?php endif; ?>
    <?php if (isset($rbac_debug['permission_value'])): ?>
    console.log('Permission Value:', <?php echo json_encode($rbac_debug['permission_value']); ?>);
    <?php endif; ?>
    console.log('Check Time:', <?php echo json_encode(isset($rbac_debug['check_time']) ? $rbac_debug['check_time'] : 'Unknown'); ?>);
    <?php 
    // Clear the debug session after displaying
    $this->session->unset_userdata('rbac_debug');
    endif; 
    ?>
    
})();
</script>
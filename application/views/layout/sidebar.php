<aside class="main-sidebar" id="alert2">
    <?php if ($this->rbac->hasPrivilege('student', 'can_view')) {?>
        <form class="navbar-form navbar-left search-form2" role="search"  action="<?php echo site_url('admin/admin/search'); ?>" method="POST">
            <?php echo $this->customlib->getCSRF(); ?>
            <div class="input-group ">

                <input type="text"  name="search_text" class="form-control search-form" placeholder="<?php echo $this->lang->line('search_by_student_name'); ?>">
                <span class="input-group-btn">
                    <button type="submit" name="search" id="search-btn" style="padding: 3px 12px !important;border-radius: 0px 30px 30px 0px; background: #fff;" class="btn btn-flat"><i class="fa fa-search"></i></button>
                </span>
            </div>
        </form>
    <?php }?>
    <section class="sidebar" id="sibe-box">
        <?php $this->load->view('layout/top_sidemenu');?>

        <ul class="sidebar-menu verttop">

        <!-- //==================sidebar dynamic======================= -->
        <?php
$side_list = side_menu_list(1);
$current_uri = $this->uri->uri_string();
$can_view_library_card = $this->rbac->hasPrivilege('library_card', 'can_view')
    || $this->rbac->hasPrivilege('issue_return', 'can_view');
$is_library_card_active = (strpos($current_uri, 'admin/librarycard') === 0 || strpos($current_uri, 'admin/generatelibrarycard') === 0);
$library_card_rendered = false;

if (!empty($side_list)) {
    foreach ($side_list as $side_list_key => $side_list_value) {

        $module_permission = access_permission_sidebar_remove_pipe($side_list_value->access_permissions);
        $module_access     = false;
        if (!empty($module_permission)) {
            foreach ($module_permission as $m_permission_key => $m_permission_value) {
                $cat_permission = access_permission_remove_comma($m_permission_value);

                if ($this->rbac->hasPrivilege($cat_permission[0], $cat_permission[1])) {
                    $module_access = true;
                    break;
                }
            }
        }
        if ($module_access) {
            if ($this->module_lib->hasModule($side_list_value->short_code) && $this->module_lib->hasActive($side_list_value->short_code)) {

                ?>

                    <li class="treeview <?php echo activate_main_menu($side_list_value->activate_menu); ?>">

                        <a href="#">
                            <?php if (strtolower($side_list_value->lang_key) == 'income'): ?>
                                <span style="font-weight: bold; margin-right: 5px;">NPR</span>
                            <?php else: ?>
                                <i class="<?php echo $side_list_value->icon; ?>"></i>
                            <?php endif; ?>
                            <span><?php echo $this->lang->line($side_list_value->lang_key); ?></span> <i class="fa fa-angle-left pull-right"></i>
                        </a>

                                                    <?php
if (!empty($side_list_value->submenus)) {
                    ?>
                        <ul class="treeview-menu">
                            <?php
foreach ($side_list_value->submenus as $submenu_key => $submenu_value) {

                        $sidebar_permission = access_permission_sidebar_remove_pipe($submenu_value->access_permissions);
                        $sidebar_access     = false;

                        if (!empty($sidebar_permission)) {
                            foreach ($sidebar_permission as $sidebar_permission_key => $sidebar_permission_value) {
                                $sidebar_cat_permission = access_permission_remove_comma($sidebar_permission_value);

                                if ($submenu_value->addon_permission != "") {
                                    if ($this->rbac->hasPrivilege($sidebar_cat_permission[0], $sidebar_cat_permission[1])
                                        && $this->auth->addonchk($submenu_value->addon_permission, false)) {
                                        $sidebar_access = true;
                                        break;
                                    }
                                } else {
                                    if ($this->rbac->hasPrivilege($sidebar_cat_permission[0], $sidebar_cat_permission[1])) {
                                        $sidebar_access = true;
                                        break;
                                    }
                                }
                            }
                        }

                        if ($sidebar_access) {
                            if (!empty($submenu_value->permission_group_id)) {
                                if (!$this->module_lib->hasActive($submenu_value->short_code)) {
                                    continue;
                                }
                            }

                            ?>

                        <li class="<?php echo activate_submenu($submenu_value->activate_controller, explode(',', $submenu_value->activate_methods)); ?>"><a href="<?php echo site_url($submenu_value->url); ?>"><i class="fa fa-angle-double-right"></i><?php echo $this->lang->line($submenu_value->lang_key); ?></a></li>


                          <?php
}

                    }

                    ?>
                        </ul>

                            <?php

                }
                ?>
                                </li>
                            <?php
            }
        }
    }
}
?>
        <!-- //==================sidebar dynamic======================= -->

        <?php if ($can_view_library_card && !$library_card_rendered) { ?>
            <li class="treeview library-card-menu <?php echo $is_library_card_active ? 'active menu-open' : ''; ?>">
                <a href="#">
                    <i class="fa fa-id-card"></i>
                    <span>Library Card</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu" <?php echo $is_library_card_active ? 'style="display:block;"' : ''; ?>>
                    <li class="<?php echo (strpos($current_uri, 'admin/librarycard') === 0) ? 'active' : ''; ?>">
                        <a href="<?php echo site_url('admin/librarycard'); ?>">
                            <i class="fa fa-angle-double-right"></i>Manage Templates
                        </a>
                    </li>
                    <li class="<?php echo (strpos($current_uri, 'admin/generatelibrarycard') === 0) ? 'active' : ''; ?>">
                        <a href="<?php echo site_url('admin/generatelibrarycard'); ?>">
                            <i class="fa fa-angle-double-right"></i>Generate Cards
                        </a>
                    </li>
                </ul>
            </li>
        <?php } ?>

        </ul>
    </section>
</aside>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var sidebarMenu = document.querySelector('.sidebar-menu');
    var libraryCardMenu = document.querySelector('.sidebar-menu .library-card-menu');
    if (!sidebarMenu || !libraryCardMenu) {
        return;
    }

    var libraryMenuLi = null;
    var menuLabels = sidebarMenu.querySelectorAll('li.treeview > a > span');
    for (var i = 0; i < menuLabels.length; i++) {
        var text = (menuLabels[i].textContent || '').trim().toLowerCase();
        if (text === 'library') {
            libraryMenuLi = menuLabels[i].closest('li.treeview');
            break;
        }
    }

    if (libraryMenuLi && libraryMenuLi.parentNode === sidebarMenu) {
        sidebarMenu.insertBefore(libraryCardMenu, libraryMenuLi.nextSibling);
    }
});
</script>
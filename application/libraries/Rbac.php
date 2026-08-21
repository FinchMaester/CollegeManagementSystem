<?php

defined('BASEPATH') or exit('No direct script access allowed');
#[AllowDynamicProperties]
class Rbac
{

    private $userRoles = array();
    protected $permissions;
    public $perm_category;
    /** @var array<string, array<string,bool>> */
    protected $mergedPermissions = array();
    /** @var string */
    protected $mergedPermissionsCacheKey = '';

    public function __construct()
    {

        $this->CI          = &get_instance();
        $this->permissions = array();
        $this->CI->config->load('mailsms');
        $this->perm_category = $this->CI->config->item('perm_category');
      
    }
 
    /**
     * @return array{name_to_id:array<string,int>,role_ids:int[],cache_key:string}
     */
    protected function _current_session_roles()
    {
        $admin = $this->CI->session->userdata('admin');
        $name_to_id = array();
        $ids = array();
        if (is_array($admin) && isset($admin['roles']) && is_array($admin['roles'])) {
            foreach ($admin['roles'] as $name => $id) {
                $rid = (int) $id;
                if ($rid > 0) {
                    $name_to_id[(string) $name] = $rid;
                    $ids[$rid] = $rid;
                }
            }
        }
        $role_ids = array_values($ids);
        sort($role_ids);
        $cache_key = 'roles:' . implode(',', $role_ids);
        return array('name_to_id' => $name_to_id, 'role_ids' => $role_ids, 'cache_key' => $cache_key);
    }

    /**
     * Build union (OR) permission map across all assigned roles.
     *
     * Shape:
     *   merged[permission_category_code][can_view|can_add|can_edit|can_delete] = bool
     */
    protected function _ensure_merged_permissions_loaded()
    {
        $roles = $this->_current_session_roles();
        $role_ids = $roles['role_ids'];
        $cache_key = $roles['cache_key'];

        if ($this->mergedPermissionsCacheKey === $cache_key && !empty($this->mergedPermissions)) {
            return;
        }

        // Session cache (per login session)
        $cache = $this->CI->session->userdata('rbac_perm_cache');
        if (is_array($cache)
            && isset($cache['cache_key'], $cache['merged'])
            && $cache['cache_key'] === $cache_key
            && is_array($cache['merged'])
        ) {
            $this->mergedPermissions = $cache['merged'];
            $this->mergedPermissionsCacheKey = $cache_key;
            return;
        }

        $this->mergedPermissions = array();
        $this->mergedPermissionsCacheKey = $cache_key;

        if (empty($role_ids)) {
            $this->CI->session->set_userdata('rbac_perm_cache', array(
                'cache_key' => $cache_key,
                'merged'    => $this->mergedPermissions,
                'merged_categories' => 0,
                'merged_rows' => 0,
                'built_at'  => date('Y-m-d H:i:s'),
            ));
            return;
        }

        $this->CI->load->model('rolepermission_model');
        $rows = $this->CI->rolepermission_model->getPermissionsByRoles($role_ids);

        $rowCount = 0;
        foreach ($rows as $r) {
            if (!is_array($r) || empty($r['permission_category_code'])) {
                continue;
            }
            $cat = trim((string) $r['permission_category_code']);
            if ($cat === '') {
                continue;
            }
            if (!isset($this->mergedPermissions[$cat])) {
                $this->mergedPermissions[$cat] = array(
                    'can_view'   => false,
                    'can_add'    => false,
                    'can_edit'   => false,
                    'can_delete' => false,
                );
            }

            foreach (array('can_view', 'can_add', 'can_edit', 'can_delete') as $p) {
                if (isset($r[$p])) {
                    $this->mergedPermissions[$cat][$p] = $this->mergedPermissions[$cat][$p] || (bool) $r[$p];
                }
            }
            $rowCount++;
        }

        $this->CI->session->set_userdata('rbac_perm_cache', array(
            'cache_key' => $cache_key,
            'merged'    => $this->mergedPermissions,
            'merged_categories' => count($this->mergedPermissions),
            'merged_rows' => $rowCount,
            'built_at'  => date('Y-m-d H:i:s'),
        ));

        log_message(
            'debug',
            'RBAC merged permissions built: ' . $cache_key
            . ' categories=' . count($this->mergedPermissions)
            . ' rows=' . $rowCount
        );
    }

    public function hasPrivilege($category = null, $permission = null)
    {    
        $category = trim((string) $category);
        $permission = trim((string) $permission);
        if ($category === '' || $permission === '') {
            return false;
        }

        $this->_ensure_merged_permissions_loaded();

        $admin = $this->CI->session->userdata('admin');
        $roles = $this->_current_session_roles();
        $role_ids = $roles['role_ids'];

        $debug_info = array(
            'category'   => $category,
            'permission' => $permission,
            'role_ids'   => $role_ids,
            'check_time' => date('Y-m-d H:i:s'),
            'cache_key'  => $roles['cache_key'],
        );

        $catKey = strtolower($category);
        if (!isset($this->mergedPermissions[$catKey])) {
            // Some installs store short_code in mixed case; try exact too.
            if (!isset($this->mergedPermissions[$category])) {
                $debug_info['permission_found'] = false;
                $debug_info['error'] = 'Permission category not present in merged set';
                $this->CI->session->set_userdata('rbac_debug', $debug_info);
                return false;
            }
            $catKey = $category;
        }

        $permRow = $this->mergedPermissions[$catKey];
        $debug_info['permission_found'] = true;
        $debug_info['available_permissions'] = array_keys($permRow);
        $val = (isset($permRow[$permission])) ? (bool) $permRow[$permission] : false;
        $debug_info['permission_value'] = $val ? 1 : 0;
        if (!isset($permRow[$permission])) {
            $debug_info['error'] = 'Permission "' . $permission . '" not found in merged permissions';
        }

        $this->CI->session->set_userdata('rbac_debug', $debug_info);
        $history = $this->CI->session->userdata('rbac_debug_history');
        if (!$history) {
            $history = array();
        }
        $history[] = $debug_info;
        if (count($history) > 10) {
            $history = array_slice($history, -10);
        }
        $this->CI->session->set_userdata('rbac_debug_history', $history);

        return $val;
    }

  
    public function module_permission($module_name)
    {
        $module_perm = $this->CI->Module_model->getPermissionByModulename($module_name);
        return $module_perm;
    }

    public function unautherized()
    {
        $this->CI->load->view('layout/header');
        $this->CI->load->view('unauthorized');
        $this->CI->load->view('layout/footer');
    }

}

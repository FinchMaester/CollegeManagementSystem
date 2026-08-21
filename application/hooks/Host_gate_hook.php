<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Host gate: serve the public CMS website on the main domain and the HEMIS ERP/admin
 * on the `hemis.` subdomain from a SINGLE codebase. Configured per deployment via .env:
 *
 *   APP_SITE_MODE   = split | hemis_only      (empty/unset => gate disabled, legacy behavior)
 *   APP_HEMIS_HOST  = hemis.websitename.edu.np
 *   APP_PUBLIC_HOST = websitename.edu.np      (optional; only used for redirects)
 *
 * Modes:
 *  - split:      main domain shows the CMS; `/admin` and `/user` on the main domain
 *                bounce to the hemis host. On the hemis host the public CMS pages
 *                bounce to the admin login (so the subdomain is "ERP only").
 *  - hemis_only: this install is the ERP only (the college keeps its own website on the
 *                apex). Any request that is not on the hemis host is redirected there.
 *
 * Runs as a pre_system hook (env is already loaded in index.php). Static assets are
 * served by .htaccess before PHP, so this never touches CSS/JS/images.
 */
class Host_gate_hook
{
    public function enforce()
    {
        if (!function_exists('app_env')) {
            return;
        }

        // ERP forms use jQuery XHR calls (e.g. programs/getBySection). In split mode those
        // endpoints can share the same first segment as a public CMS page; don't redirect
        // background AJAX requests away from the current host.
        if ($this->_is_ajax_request()) {
            return;
        }

        $mode = strtolower(trim((string) app_env('APP_SITE_MODE', '')));
        if ($mode !== 'split' && $mode !== 'hemis_only') {
            return; // disabled => behave exactly as before
        }

        $hemisHost = $this->_normalize_host((string) app_env('APP_HEMIS_HOST', ''));
        if ($hemisHost === '') {
            return; // misconfigured => fail open (do nothing)
        }

        $host = $this->_normalize_host(isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : '');
        if ($host === '') {
            return;
        }

        $seg = $this->_first_segment();

        // Never gate machine/utility endpoints.
        $neverGate = array('api', 'cron', 'install');
        if (in_array($seg, $neverGate, true)) {
            return;
        }

        $publicCms = array(
            '', 'welcome', 'page', 'read', 'online_admission', 'online_course',
            'course', 'programs', 'checkout', 'examresult', 'frontend',
        );
        $erpEntry = array('admin', 'user');

        $scheme = $this->_scheme();
        $onHemis = ($host === $hemisHost);

        if ($onHemis) {
            // ERP host: do not serve the marketing/CMS site here -> send to admin login.
            if (in_array($seg, $publicCms, true)) {
                $this->_redirect($scheme . '://' . $hemisHost . '/site/login');
            }
            return;
        }

        // Not the hemis host (apex / main domain or anything else pointed here).
        if ($mode === 'hemis_only') {
            // This deployment is ERP-only; the public site lives elsewhere.
            $this->_redirect($scheme . '://' . $hemisHost . $this->_request_uri());
        }

        // split mode on the main domain: keep ERP entry points on the hemis host.
        if (in_array($seg, $erpEntry, true)) {
            $this->_redirect($scheme . '://' . $hemisHost . $this->_request_uri());
        }
    }

    /**
     * @param string $host
     * @return string lowercased host without port
     */
    protected function _normalize_host($host)
    {
        $host = strtolower(trim($host));
        return preg_replace('/:\d+$/', '', $host);
    }

    /**
     * @return string first URI path segment (lowercased), '' for site root
     */
    protected function _first_segment()
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
        $path = (string) parse_url($uri, PHP_URL_PATH);
        $path = ltrim($path, '/');
        if (stripos($path, 'index.php') === 0) {
            $path = ltrim(substr($path, strlen('index.php')), '/');
        }
        $seg = strtok($path, '/');
        return strtolower($seg === false ? '' : $seg);
    }

    /**
     * @return string
     */
    protected function _scheme()
    {
        $https = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
                && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
        return $https ? 'https' : 'http';
    }

    /**
     * @return string original request URI (path + query), always starting with '/'
     */
    protected function _request_uri()
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
        if ($uri === '' || $uri[0] !== '/') {
            $uri = '/' . $uri;
        }
        return $uri;
    }

    /**
     * @return bool
     */
    protected function _is_ajax_request()
    {
        $requestedWith = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? (string) $_SERVER['HTTP_X_REQUESTED_WITH'] : '';
        return strtolower($requestedWith) === 'xmlhttprequest';
    }

    /**
     * @param string $url
     */
    protected function _redirect($url)
    {
        header('Location: ' . $url, true, 302);
        exit;
    }
}

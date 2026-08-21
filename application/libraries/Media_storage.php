<?php


if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}


class Media_storage
{


    private $_CI;


    public function __construct()
    {
        $this->_CI = &get_instance();
        $this->_CI->load->library('customlib');


    }


public function fileupload($file_input_name, $upload_path) {
    if(isset($_FILES[$file_input_name]) && !empty($_FILES[$file_input_name]['name'])) {
        $temp = explode(".", $_FILES[$file_input_name]["name"]);
        $newfilename = round(microtime(true)) . '-' . rand() . time() . "!" . $_FILES[$file_input_name]["name"];


        if (!is_dir($upload_path) && !mkdir($upload_path, 0755, true)) {
            log_message('error', 'Failed to create directory: ' . $upload_path);
            return false;
        }


        $destination = rtrim($upload_path, '/\\') . DIRECTORY_SEPARATOR . $newfilename;


        if(move_uploaded_file($_FILES[$file_input_name]["tmp_name"], $destination)) {
            return $newfilename;
        } else {
            log_message('error', 'Failed to move uploaded file to: ' . $destination);
            return false;
        }
    }
    return false;
}


    public function filedownload($file_name, $download_path = "")
    {
        // Determine the base path (same logic as ImageResize)
        $folder_path = $this->_CI->customlib->getFolderPath();
        $base_path = null;
        
        // Try folder_path first
        if (!empty($folder_path)) {
            $folder_path_clean = rtrim($folder_path, '/\\');
            if (!empty($folder_path_clean) && $folder_path_clean !== '/' && $folder_path_clean !== '\\' && is_dir($folder_path_clean)) {
                $base_path = $folder_path_clean;
            }
        }
        
        // Fallback to FCPATH
        if (empty($base_path)) {
            $fcpath_clean = rtrim(FCPATH, '/\\');
            if (!empty($fcpath_clean) && $fcpath_clean !== '/' && $fcpath_clean !== '\\') {
                $base_path = $fcpath_clean;
            }
        }
        
        // Final fallback to DOCUMENT_ROOT
        if (empty($base_path) || $base_path === '/' || $base_path === '\\') {
            if (isset($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['DOCUMENT_ROOT'])) {
                $doc_root = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
                if (!empty($doc_root) && $doc_root !== '/' && $doc_root !== '\\') {
                    $base_path = $doc_root;
                }
            }
        }
        
        // Last resort
        if (empty($base_path)) {
            $base_path = rtrim(FCPATH, '/\\');
        }
        
        // Clean download path
        $download_path_clean = ltrim($download_path, '/\\');
        if (substr($download_path_clean, 0, 1) === '/') {
            $download_path_clean = ltrim($download_path_clean, '/');
        }
        
        // Remove 'uploads' prefix if base_path already ends with 'uploads'
        if (substr($base_path, -7) === 'uploads' && substr($download_path_clean, 0, 7) === 'uploads') {
            $download_path_clean = ltrim(substr($download_path_clean, 7), '/\\');
        }
        
        // Construct file path
        $file_url = $base_path . DIRECTORY_SEPARATOR . $download_path_clean . DIRECTORY_SEPARATOR . $file_name;
        $file_url = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file_url);
        
        // Check if file exists
        if (!file_exists($file_url)) {
            // Try alternative path construction
            $alt_path = $base_path . DIRECTORY_SEPARATOR . rtrim($download_path_clean, '/\\') . DIRECTORY_SEPARATOR . ltrim($file_name, '/\\');
            $alt_path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $alt_path);
            
            if (file_exists($alt_path)) {
                $file_url = $alt_path;
            } else {
                // Try with original download_path
                $original_path = $base_path . DIRECTORY_SEPARATOR . ltrim($download_path, '/\\') . DIRECTORY_SEPARATOR . $file_name;
                $original_path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $original_path);
                
                if (file_exists($original_path)) {
                    $file_url = $original_path;
                } else {
                    log_message('error', 'Download file not found: ' . $file_url . ' | base_path: ' . $base_path . ' | download_path: ' . $download_path);
                    show_404();
                    return;
                }
            }
        }
        
        $download_file_name = substr($file_name, (strpos($file_name, '!') + 1));
        $this->_CI->load->helper('download');
        
        // Read file contents
        $data = @file_get_contents($file_url);
        if ($data === false) {
            log_message('error', 'Failed to read file for download: ' . $file_url);
            show_404();
            return;
        }
        
        force_download($download_file_name, $data);
    }


    public function fileview($file_name)
    {
        if (!IsNullOrEmptyString($file_name)) {


            $download_file_name = substr($file_name, (strpos($file_name, '!') + 1));
            return $download_file_name;
        }
        return null;


    }


    public function getImageURL($file_name)
    {
        if (!IsNullOrEmptyString($file_name)) {
            // Get base URL and ensure proper slash handling
            $base_url = $this->_CI->customlib->getBaseUrl();
            
            // Remove trailing slash from base_url if present
            $base_url = rtrim($base_url, '/');
            
            // Ensure file_name starts with / if it doesn't already
            $file_name = '/' . ltrim($file_name, '/');
            
            // Construct URL with proper slash handling
            $download_file_name = $base_url . $file_name . img_time();
            return $download_file_name;
        }
        return null;
    }


    public function filedelete($file_name, $path = "")
    {
        if (!IsNullOrEmptyString($file_name)) {


            $url = $this->_CI->customlib->getFolderPath() . $path . "/" . $file_name;


            if (file_exists($url)) {


                if (unlink($url)) {
                    return true;
                }


            }
        }


        return false;
    }


}




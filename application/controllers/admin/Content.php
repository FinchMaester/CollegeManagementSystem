<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Content extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('Enc_lib');
        $this->load->library('media_storage'); 
        $this->load->model(array('contenttype_model', 'uploadcontent_model', 'sharecontent_model'));
    }

    function list() {

        if (!$this->rbac->hasPrivilege('content_share_list', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'download_center');
        $this->session->set_userdata('sub_menu', 'admin/contentlist');

        $data                           = array();
        $staff_id                       = $this->customlib->getStaffID();
        $data['count']                  = $this->uploadcontent_model->total_record($staff_id);
        $data['sch_setting']            = $this->setting_model->getSetting();
        $data['roles']                  = $this->role_model->get();
        $content_types                  = $this->contenttype_model->get();
        $data['content_types']          = $content_types;
        $data['superadmin_restriction'] = $this->customlib->superadmin_visible();
        $this->load->view('layout/header');
        $this->load->view('admin/content/list', $data);
        $this->load->view('layout/footer');

    }

    public function upload()
    {
        if (!$this->rbac->hasPrivilege('upload_content', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'download_center');
        $this->session->set_userdata('sub_menu', 'admin/upload_content');

        $data                           = array();
        $class                          = $this->class_model->get();
        $data['classlist']              = $class;
        $staff_id                       = $this->customlib->getStaffID();
        $data['count']                  = $this->uploadcontent_model->total_record($staff_id);
        $data['sch_setting']            = $this->setting_model->getSetting();
        $data['roles']                  = $this->role_model->get();
        $content_types                  = $this->contenttype_model->get();
        $data['content_types']          = $content_types;
        $data['superadmin_restriction'] = $this->customlib->superadmin_visible();
        $data['branch_url']=$this->customlib->getBaseUrl();
        $this->load->view('layout/header');
        $this->load->view('admin/content/upload', $data);
        $this->load->view('layout/footer');

    }

    public function download_content($id)
    {
        $this->load->helper('file'); // Load file helper
        $content = $this->uploadcontent_model->get($id);
        $this->media_storage->filedownload($content->img_name, $content->dir_path);
    }
    
    /**
     * Serve image files for display
     * This method serves images through the controller to ensure proper path resolution
     */
    public function view_image($id, $type = 'thumb')
    {
        $content = $this->uploadcontent_model->get($id);
        if (!$content) {
            show_404();
            return;
        }
        
        $file_path = '';
        $file_name = '';
        
        if ($type === 'thumb' && !empty($content->thumb_path) && !empty($content->thumb_name)) {
            $file_path = $content->thumb_path;
            $file_name = $content->thumb_name;
        } elseif (!empty($content->dir_path) && !empty($content->img_name)) {
            // Fallback to main image
            $file_path = $content->dir_path;
            $file_name = $content->img_name;
        } else {
            // Final fallback to docsicon
            $fallback_path = FCPATH . 'backend/images/docsicon.png';
            if (file_exists($fallback_path)) {
                header('Content-Type: image/png');
                readfile($fallback_path);
                exit;
            }
            show_404();
            return;
        }
        
        // Construct full file path - try multiple formats to support both old and new uploads
        $folder_path = $this->customlib->getFolderPath();
        if (empty($folder_path)) {
            $folder_path = FCPATH;
        }
        
        // Normalize paths
        $folder_path = rtrim($folder_path, '/\\');
        $file_path_original = $file_path;
        $file_path_clean = ltrim($file_path, '/\\');
        
        // Build list of possible paths to try (for backward compatibility)
        $possible_paths = array();
        
        // Method 1: New format - ImageResize style (with uploads prefix removal)
        $file_path_new = $file_path_clean;
        if (substr($folder_path, -7) === 'uploads' && substr($file_path_new, 0, 7) === 'uploads') {
            $file_path_new = ltrim(substr($file_path_new, 7), '/\\');
        }
        // Try direct concatenation (ImageResize style - path + filename without separator)
        $possible_paths[] = $folder_path . '/' . $file_path_new . $file_name;
        // Try with explicit separator
        $possible_paths[] = $folder_path . '/' . rtrim($file_path_new, '/\\') . '/' . ltrim($file_name, '/\\');
        
        // Method 2: Old format - Direct path from FCPATH (for older uploads)
        $possible_paths[] = FCPATH . ltrim($file_path_original, '/\\') . ltrim($file_name, '/\\');
        $possible_paths[] = FCPATH . ltrim($file_path_original, '/\\') . '/' . ltrim($file_name, '/\\');
        $possible_paths[] = FCPATH . rtrim($file_path_original, '/\\') . '/' . ltrim($file_name, '/\\');
        
        // Method 3: Old format - Direct path from folder_path (without uploads prefix removal)
        $possible_paths[] = $folder_path . '/' . $file_path_clean . $file_name;
        $possible_paths[] = $folder_path . '/' . rtrim($file_path_clean, '/\\') . '/' . ltrim($file_name, '/\\');
        
        // Method 4: Try with folder_path but without any prefix removal
        if ($file_path_original !== $file_path_clean) {
            $possible_paths[] = $folder_path . '/' . $file_path_original . $file_name;
            $possible_paths[] = $folder_path . '/' . rtrim($file_path_original, '/\\') . '/' . ltrim($file_name, '/\\');
        }
        
        // Normalize all paths for Windows
        foreach ($possible_paths as $key => $path) {
            $possible_paths[$key] = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        }
        
        // Try each possible path
        $full_path = null;
        $debug_log = array(); // For debugging - remove after fixing
        foreach ($possible_paths as $index => $test_path) {
            $debug_log[] = "Path " . ($index + 1) . ": " . $test_path . " - " . (file_exists($test_path) ? "EXISTS" : "NOT FOUND");
            if (file_exists($test_path)) {
                $full_path = $test_path;
                break;
            }
        }
        
        // Log debug info if file not found (temporary - remove after fixing)
        if (!$full_path && isset($_GET['debug'])) {
            log_message('debug', 'view_image Debug - Content ID: ' . $id . ', Type: ' . $type);
            log_message('debug', 'view_image Debug - thumb_path: ' . (isset($content->thumb_path) ? $content->thumb_path : 'empty'));
            log_message('debug', 'view_image Debug - thumb_name: ' . (isset($content->thumb_name) ? $content->thumb_name : 'empty'));
            log_message('debug', 'view_image Debug - dir_path: ' . (isset($content->dir_path) ? $content->dir_path : 'empty'));
            log_message('debug', 'view_image Debug - img_name: ' . (isset($content->img_name) ? $content->img_name : 'empty'));
            log_message('debug', 'view_image Debug - folder_path: ' . $folder_path);
            log_message('debug', 'view_image Debug - Tried paths: ' . implode(' | ', $debug_log));
        }
        
        // If thumbnail not found, try fallback to main image
        if (!$full_path && $type === 'thumb' && !empty($content->dir_path) && !empty($content->img_name)) {
            $dir_path_original = $content->dir_path;
            $dir_path_clean = ltrim($dir_path_original, '/\\');
            
            // Build possible paths for main image
            $main_image_paths = array();
            
            // New format
            $dir_path_new = $dir_path_clean;
            if (substr($folder_path, -7) === 'uploads' && substr($dir_path_new, 0, 7) === 'uploads') {
                $dir_path_new = ltrim(substr($dir_path_new, 7), '/\\');
            }
            $main_image_paths[] = $folder_path . '/' . $dir_path_new . $content->img_name;
            $main_image_paths[] = $folder_path . '/' . rtrim($dir_path_new, '/\\') . '/' . ltrim($content->img_name, '/\\');
            
            // Old format - FCPATH
            $main_image_paths[] = FCPATH . ltrim($dir_path_original, '/\\') . ltrim($content->img_name, '/\\');
            $main_image_paths[] = FCPATH . ltrim($dir_path_original, '/\\') . '/' . ltrim($content->img_name, '/\\');
            $main_image_paths[] = FCPATH . rtrim($dir_path_original, '/\\') . '/' . ltrim($content->img_name, '/\\');
            
            // Old format - folder_path
            $main_image_paths[] = $folder_path . '/' . $dir_path_clean . $content->img_name;
            $main_image_paths[] = $folder_path . '/' . rtrim($dir_path_clean, '/\\') . '/' . ltrim($content->img_name, '/\\');
            
            // Normalize paths
            foreach ($main_image_paths as $key => $path) {
                $main_image_paths[$key] = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
            }
            
            // Try each path
            foreach ($main_image_paths as $test_path) {
                if (file_exists($test_path)) {
                    $full_path = $test_path;
                    break;
                }
            }
        }
        
        // If still not found, use docsicon fallback
        if (!$full_path) {
            $fallback_path = FCPATH . 'backend/images/docsicon.png';
            if (file_exists($fallback_path)) {
                header('Content-Type: image/png');
                readfile($fallback_path);
                exit;
            }
            show_404();
            return;
        }
        
        // Determine MIME type
        $mime_type = mime_content_type($full_path);
        if (!$mime_type) {
            $ext = strtolower(pathinfo($full_path, PATHINFO_EXTENSION));
            $mime_types = array(
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
            );
            $mime_type = isset($mime_types[$ext]) ? $mime_types[$ext] : 'image/jpeg';
        }
        
        // Output image
        header('Content-Type: ' . $mime_type);
        header('Content-Length: ' . filesize($full_path));
        header('Cache-Control: public, max-age=3600');
        readfile($full_path);
        exit;
    }

    public function getuploaddata()
    {
        $staff_id       = $this->customlib->getStaffID();
        $pag_content    = '';
        $pag_navigation = '';

        if (isset($_POST['data']['page'])) {

            $page = $_POST['data']['page']; /* The page we are currently at */

            $cur_page = $page;
            $page -= 1;
            $per_page     = 12;
            $previous_btn = true;
            $next_btn     = true;
            $first_btn    = true;
            $last_btn     = true;
            $start        = $page * $per_page;

            $where_search = array();

            /* Check if there is a string inputted on the search box */
            if (!empty($_POST['data']['search'])) {
                $where_search['search'] = $_POST['data']['search'];
            }
            $data['grid_view'] = $_POST['data']['grid'];
            /* Retrieve all the posts */ 
            $contents = $this->uploadcontent_model->getlimitwithsearch($staff_id, $per_page, $start, $where_search);

            $data['all_contents'] = $contents['total_rows'];

            $data['selected_content'] = $this->input->post('selected_content');

            $count       = $contents['count'];
            $pag_content = $this->load->view('admin/content/_getuploaddata', $data, true);

            $no_of_paginations = ceil($count / $per_page);
 
            if ($cur_page >= 7) {
                $start_loop = $cur_page - 3;
                if ($no_of_paginations > $cur_page + 3) {
                    $end_loop = $cur_page + 3;
                } else if ($cur_page <= $no_of_paginations && $cur_page > $no_of_paginations - 6) {
                    $start_loop = $no_of_paginations - 6;
                    $end_loop   = $no_of_paginations;
                } else {
                    $end_loop = $no_of_paginations;
                }
            } else {
                $start_loop = 1;
                if ($no_of_paginations > 7) {
                    $end_loop = 7;
                } else {
                    $end_loop = $no_of_paginations;
                }

            }
            $pag_navigation .= "<ul class='pagination'>";

            if ($first_btn && $cur_page > 1) {
                $pag_navigation .= "<li p='1' class='page-item unactive'><a class='page-link' href='javascript:void(0);'><i class='fa fa-angle-double-left'></i></a></li>";
            } else if ($first_btn) {
                $pag_navigation .= "<li p='1' class='page-item disabled'><a class='page-link' href='javascript:void(0);'><i class='fa fa-angle-double-left'></i></a></li>";
            }

            if ($previous_btn && $cur_page > 1) {
                $pre = $cur_page - 1;
                $pag_navigation .= "<li p='$pre' class='page-item unactive'><a class='page-link' href='javascript:void(0);'>  ". $this->lang->line('previous') ."</a></li>";
            } else if ($previous_btn) {
                $pag_navigation .= "<li class='page-item disabled'><a class='page-link' href='javascript:void(0);'>". $this->lang->line('previous') ."</a></li>";
            }
            for ($i = $start_loop; $i <= $end_loop; $i++) {

                if ($cur_page == $i) {
                    $pag_navigation .= "<li p='$i' class = 'page-item active' ><a class='page-link' href='javascript:void(0);'>{$i}</a></li>";
                } else {
                    $pag_navigation .= "<li p='$i' class='page-item unactive'><a class='page-link' href='javascript:void(0);'>{$i}</a></li>";
                }
            }

            if ($next_btn && $cur_page < $no_of_paginations) {
                $nex = $cur_page + 1;
                $pag_navigation .= "<li p='$nex' class='page-item unactive'><a class='page-link' href='javascript:void(0);'>". $this->lang->line('next') ." </a></li>";
            } else if ($next_btn) {
                $pag_navigation .= "<li class='page-item disabled'><a class='page-link' href='javascript:void(0);'>". $this->lang->line('next') ."</a></li>";
            }

            if ($last_btn && $cur_page < $no_of_paginations) {
                $pag_navigation .= "<li p='$no_of_paginations' class='page-item unactive'><a class='page-link' href='javascript:void(0);'><i class='fa fa-angle-double-right'></i></a></li>";
            } else if ($last_btn) {
                $pag_navigation .= "<li p='$no_of_paginations' class='page-item disabled'><a class='page-link' href='javascript:void(0);'><i class='fa fa-angle-double-right'></i></a></li>";
            }

            $pag_navigation = $pag_navigation . "</ul>";
        }

        $response = array(
            'content'    => $pag_content,
            'navigation' => $pag_navigation,
        );

        echo json_encode($response);
    }

    public function ajaxupload()
    {
        // Suppress error output to prevent breaking JSON response
        $old_error_reporting = error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING);
        $old_display_errors = ini_set('display_errors', 0);
        
        // Set JSON response header first
        header('Content-Type: application/json');
        
        // Start output buffering to catch any accidental output
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start();
        
        // Debug logging
        $debug_info = array();
        $debug_info['timestamp'] = date('Y-m-d H:i:s');
        $debug_info['post_data'] = $this->input->post();
        $debug_info['files_data'] = isset($_FILES) ? $_FILES : array();
        
        try {
            $this->form_validation->set_rules('content_type', $this->lang->line('content_type'), 'required|trim|xss_clean');
            $url = $this->input->post('url');
            if (isset($_FILES['upload']) && $url == "") {
                $this->form_validation->set_rules('file', $this->lang->line('file'), "callback_handle_upload_file[upload]|trim|xss_clean");
            } else {
                $this->form_validation->set_rules('url', $this->lang->line('url'), 'required|trim|xss_clean');
            }

            $data = array();

            if ($this->form_validation->run() == false) {
                $data = array(
                    'title'        => form_error('title'),
                    'content_type' => form_error('content_type'),
                    'file'         => form_error('file'),
                    'url'          => form_error('url'),
                );
                $debug_info['validation_errors'] = $data;
                $array = array('status' => 0, 'error' => $data, 'debug' => $debug_info);
                
                // Clean buffer and output JSON
                ob_end_clean();
                echo json_encode($array);
                exit;
            }

            if (isset($_FILES['upload']) && !empty($_FILES['upload']) && $_FILES['upload']['error'][0] == UPLOAD_ERR_OK) {

                $dir_path   = "uploads/school_content/material/media/";
                $thumb_path = "uploads/school_content/material/media/thumb/";

                $config['thumb_path']   = $thumb_path;
                $config['dir_path']     = $dir_path;
                $config['thumb_width']  = 100;
                $config['thumb_height'] = 100;
                
                try {
                    $this->load->library('imageResize', $config);
                    $responses = $this->imageresize->resize($_FILES["upload"]);
                } catch (Exception $e) {
                    ob_end_clean();
                    $debug_info['library_error'] = $e->getMessage();
                    echo json_encode(array('status' => 0, 'error' => array('file' => 'Upload failed: ' . $e->getMessage()), 'debug' => $debug_info));
                    exit;
                }

                $response_array = array();
                if ($responses) {
                    $img_array  = array();
                    $validation = 1;

                    if ($validation == 1) {
                        foreach ($responses['images'] as $key => $value) {
                            $data = array(
                                'real_name'       => $value['name'],
                                'img_name'        => $value['store_name'],
                                'mime_type'       => $value['file_type'],
                                'file_type'       => find_file_type($value['file_type']),
                                'file_size'       => $value['file_size'],
                                'thumb_name'      => $value['thumb_name'],
                                'thumb_path'      => $value['thumb_path'],
                                'dir_path'        => $value['dir_path'],
                                'content_type_id' => $this->input->post('content_type'),
                                'upload_by'       => $this->customlib->getStaffID(),
                                'created_at'      => date('Y-m-d H:i:s'),
                            );

                            $insert_id         = $this->uploadcontent_model->add($data);
                            $data['record_id'] = $insert_id;
                            $img_array[]       = $data;
                        }
                        $staff_id = $this->customlib->getStaffID();
                        $count    = $this->uploadcontent_model->total_record($staff_id);

                        $response_array['status']     = 1;
                        $response_array['file_count'] = $count->number;
                        $response_array['file_size']  = format_file_size($count->file_size);
                        $response_array['msg']        = $this->lang->line('success_message');
                        $response_array['debug']      = $debug_info;
                        
                        // Clean buffer and output JSON
                        ob_end_clean();
                        echo json_encode($response_array);
                        exit; // Exit early on success

                    } else {
                        $response_array['status'] = 0;
                        $response_array['error']  = array('file' => $this->lang->line('extension_not_allowed'));
                        $response_array['debug']  = $debug_info;
                        
                        ob_end_clean();
                        echo json_encode($response_array);
                        exit;
                    }

                } else {
                    $response_array['status'] = 0;
                    $response_array['error']  = array('file' => $this->lang->line('something_went_wrong'));
                    $debug_info['error'] = 'ImageResize library returned false';
                    $response_array['debug'] = $debug_info;
                    
                    ob_end_clean();
                    echo json_encode($response_array);
                    exit;
                }

            } else {
                //====================
                $url     = $this->input->post('url');
                $youtube = "https://www.youtube.com/oembed?url=" . $url . "&format=json";
                $curl    = curl_init($youtube);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                $return   = curl_exec($curl);
                $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);
                if ($httpcode == 200) {
                    $img_array = array();

                    $dir_path   = "uploads/school_content/material/media/";
                    $thumb_path = "uploads/school_content/material/media/thumb/";

                    $config['thumb_path']   = $thumb_path;
                    $config['dir_path']     = $dir_path;
                    $config['thumb_width']  = 100;
                    $config['thumb_height'] = 100;
                    $this->load->library('imageResize', $config);

                    $upload_response = $this->imageresize->resizeVideoImg($return);

                    if ($upload_response) {
                        $upload_response = json_decode($upload_response);
                        $data            = array(
                            'real_name'       => $upload_response->vid_title,
                            'vid_url'         => $url,
                            'vid_title'       => $upload_response->vid_title,
                            'img_name'        => $upload_response->store_name,
                            'file_type'       => $upload_response->file_type,
                            'file_size'       => $upload_response->file_size,
                            'thumb_name'      => $upload_response->thumb_name,
                            'thumb_path'      => $upload_response->thumb_path,
                            'dir_path'        => $upload_response->dir_path,
                            'content_type_id' => $this->input->post('content_type'),
                            'upload_by'       => $this->customlib->getStaffID(),
                            'created_at'      => date('Y-m-d H:i:s'),
                        );

                        $insert_id = $this->uploadcontent_model->add($data);
                        $staff_id  = $this->customlib->getStaffID();
                        $count     = $this->uploadcontent_model->total_record($staff_id);

                        ob_end_clean();
                        echo json_encode(array('status' => 1, 'msg' => $this->lang->line('file_upload_successfully'), 'file_count' => $count->number,
                            'file_size' => format_file_size($count->file_size), 'error' => '', 'debug' => $debug_info));
                        exit;
                    } else {
                        $debug_info['error'] = 'resizeVideoImg returned false';
                        ob_end_clean();
                        echo json_encode(array('status' => 0, 'error' => array('file' => $this->lang->line('something_went_wrong')), 'debug' => $debug_info));
                        exit;
                    }
                } else {
                    $debug_info['error'] = 'YouTube API returned HTTP code: ' . $httpcode;
                    ob_end_clean();
                    echo json_encode(array('status' => 0, 'error' => array('file' => $this->lang->line('invalid_url_or_try_again')), 'debug' => $debug_info));
                    exit;
                }

                //===================
            }

        } catch (Exception $e) {
            $debug_info['exception'] = array(
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            );
            
            ob_end_clean();
            echo json_encode(array('status' => 0, 'error' => array('file' => 'Server Error: ' . $e->getMessage()), 'debug' => $debug_info));
            exit;
        }
        
        // Restore error settings (only reached if no exit was called)
        error_reporting($old_error_reporting);
        ini_set('display_errors', $old_display_errors);
    }

    public function handle_upload_file($field, $var)
    {

        $result = $this->filetype_model->get();

        if (isset($_FILES[$var])
            && !empty($_FILES[$var]['name'][0])
            && !empty($_FILES[$var]["type"][0])
            && $_FILES[$var]["size"][0] != 0 && file_exists($_FILES[$var]["tmp_name"][0])) {

            $file_type = $_FILES[$var]['type'][0];
            $file_size = $_FILES[$var]["size"][0];
            $file_name = $_FILES[$var]["name"][0];

            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', ($result->image_extension . "," . $result->file_extension))));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', ($result->image_mime . "," . $result->file_mime))));

            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (!in_array($file_type, $allowed_mime_type)) {
                $this->form_validation->set_message('handle_upload_file', $this->lang->line('file_type_not_allowed'));
                return false;
            } elseif (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                $this->form_validation->set_message('handle_upload_file', $this->lang->line('file_type_not_allowed'));
                return false;
            } elseif ($file_size > $result->file_size) {
                $this->form_validation->set_message('handle_upload_file', $this->lang->line('file_size_shoud_be_less_than') .
                    number_format($result->file_size / 1048576, 2) . ' MB');
                return false;
            }

            return true;
        }
        $this->form_validation->set_message('handle_upload_file', $this->lang->line('please_choose_a_file_or_enter_youtube_video_link'));
        return false;
    }

    public function share()
    {
        $this->form_validation->set_rules('title', $this->lang->line('title'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('share_date', $this->lang->line('share_date'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('send_to', $this->lang->line('send_to'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('selected_contents[]', $this->lang->line('contents'), 'required|trim|xss_clean');

        $data    = array();
        $send_to = $this->input->post('send_to');

        if ($send_to == "group") {
            $groups = $this->input->post('user');
            if (!isset($groups)) {
                $this->form_validation->set_rules('groups', $this->lang->line('group'), 'required|trim|xss_clean');
            }

        } elseif ($send_to == "individual") {

            $users_array = $this->input->post('user_list');
            if (!isset($users_array)) {
                $this->form_validation->set_rules('users_array', $this->lang->line('users'), 'required|trim|xss_clean');
            } else {
                $individual_array_validate = json_decode($users_array);
                if (empty($individual_array_validate)) {
                    $this->form_validation->set_rules('users_array', $this->lang->line('users'), 'required|trim|xss_clean');
                }
            }

        } elseif ($send_to == "class") {

            $class_sections = $this->input->post('class_section_id');
            if (!isset($class_sections)) {
                $this->form_validation->set_rules('class_sections', $this->lang->line('section'), 'required|trim|xss_clean');
            }
        }

        if ($this->form_validation->run() == false) {
            $data = array(
                'title'               => form_error('title'),
                'share_date'          => form_error('share_date'),
                'send_to'             => form_error('send_to'),
                'groups'              => form_error('groups'),
                'class_sections'      => form_error('class_sections'),
                'users_array'         => form_error('users_array'),
                'selected_contents[]' => form_error('selected_contents[]'),
            );
            $array = array('status' => 0, 'error' => $data);
            echo json_encode($array);
        } else {
            $upload_content    = array();
            $selected_contents = $this->input->post('selected_contents');

            foreach ($selected_contents as $selected_content_key => $selected_content_value) {
                $upload_content[] = array(
                    'upload_content_id' => $selected_content_value,
                    'share_content_id'  => 0,
                );
            }

            $insert_data                = array();
            $insert_data['title']       = $this->input->post('title');
            $insert_data['share_date']  = $this->customlib->dateFormatToYYYYMMDD($this->input->post('share_date'));
            $insert_data['valid_upto']  = $this->customlib->dateFormatToYYYYMMDD($this->input->post('valid_upto'));
            $insert_data['description'] = $this->input->post('description');
            $insert_data['created_by'] = $this->customlib->getStaffID();
            $insert_data['send_to']    = $this->input->post('send_to');
            $insert_content_for        = array();

            if ($insert_data['send_to'] == "group") {
                $groups = $this->input->post('user');
                foreach ($groups as $group_key => $group_value) {
                    $insert_content_for[] = array(
                        'group_id'         => $group_value,
                        'share_content_id' => 0,
                    );
                }
            } elseif ($insert_data['send_to'] == "individual") {
                $individual_arr = json_decode($this->input->post('user_list'));
                foreach ($individual_arr as $individual_key => $individual_value) {
                    $inv = array(
                        'share_content_id' => 0,
                        'staff_id'         => null,
                        'student_id'       => null,
                    );

                    if ($individual_value[0]->{"category"} == "staff") {

                        $inv['staff_id']       = $individual_value[0]->{"record_id"};
                        $inv['student_id']     = null;
                        $inv['user_parent_id'] = null;

                    } elseif ($individual_value[0]->{"category"} == "student") {

                        $inv['staff_id']       = null;
                        $inv['student_id']     = $individual_value[0]->{"record_id"};
                        $inv['user_parent_id'] = null;

                    } elseif ($individual_value[0]->{"category"} == "parent") {
                        $inv['staff_id']       = null;
                        $inv['student_id']     = null;
                        $inv['user_parent_id'] = $individual_value[0]->{"parent_id"};

                    } elseif ($individual_value[0]->{"category"} == "student_guardian") {
                        $inv['staff_id']       = null;
                        $inv['student_id']     = $individual_value[0]->{"record_id"};
                        $inv['user_parent_id'] = null;

                        $inv_parent = array(
                            'share_content_id' => 0,
                            'staff_id'         => null,
                            'student_id'       => null,
                            'user_parent_id'   => $individual_value[0]->{"parent_id"},
                        );

                        $insert_content_for[] = $inv_parent;

                    }

                    $insert_content_for[] = $inv;
                }
            } elseif ($insert_data['send_to'] == "class") {
                $class_sections = $this->input->post('class_section_id');
                foreach ($class_sections as $class_section_key => $class_section_value) {
                    $insert_content_for[] = array(
                        'class_section_id' => $class_section_value,
                        'share_content_id' => 0,
                    );
                }
            }

            $this->sharecontent_model->add($insert_data, $insert_content_for, $upload_content);
            echo json_encode(array('status' => 1, 'msg' => $this->lang->line('record_shared_successfully')));
        }
    }

    public function generate_url()
    {

        $this->form_validation->set_rules('title', $this->lang->line('title'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('share_date', $this->lang->line('share_date'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('selected_contents[]', $this->lang->line('contents'), 'required|trim|xss_clean');

        $data = array();

        if ($this->form_validation->run() == false) {

            $data = array(
                'title'               => form_error('title'),
                'share_date'          => form_error('share_date'),
                'selected_contents[]' => form_error('selected_contents[]'),
            );
            $array = array('status' => 0, 'error' => $data);
            echo json_encode($array);
        } else {
            $upload_content    = array();
            $selected_contents = $this->input->post('selected_contents');

            foreach ($selected_contents as $selected_content_key => $selected_content_value) {
                $upload_content[] = array(
                    'upload_content_id' => $selected_content_value,
                    'share_content_id'  => 0,
                );
            }

            $insert_data                = array();
            $insert_data['title']       = $this->input->post('title');
            $insert_data['send_to']     = 'public';
            $insert_data['share_date']  = $this->customlib->dateFormatToYYYYMMDD($this->input->post('share_date'));
            $insert_data['valid_upto']  = $this->customlib->dateFormatToYYYYMMDD($this->input->post('valid_upto'));
            $insert_data['description'] = $this->input->post('description');
            $insert_data['created_by']  = $this->customlib->getStaffID();
            $insert_content_for         = array();

            $insert_id = $this->sharecontent_model->add($insert_data, $insert_content_for, $upload_content);
            $url_key   = $this->enc_lib->encrypt($insert_id);
            echo json_encode(array('status' => 1, 'shared_url' => ($this->customlib->getBaseUrl().'site/share/' . $url_key), 'msg' => $this->lang->line('success_message')));
        }
    }

    public function delete_content($id)
    {
        $is_removed = $this->sharecontent_model->remove($id);
        redirect('admin/content/list');
    }

    public function delete()
    {
        $id                  = $this->input->post('id');
        $upload_content_data = $this->uploadcontent_model->get($id);
     
        $is_removed = $this->uploadcontent_model->remove($id);
        if ($is_removed) {
            $this->media_storage->filedelete($upload_content_data->img_name, $upload_content_data->dir_path);
            $this->media_storage->filedelete($upload_content_data->thumb_name, $upload_content_data->thumb_path);

            $staff_id = $this->customlib->getStaffID();
            $count     = $this->uploadcontent_model->total_record($staff_id);  
                     
            echo json_encode(array('status' => 1, 'file_count' => $count->number, 'file_size'=> format_file_size($count->file_size), 'msg' => $this->lang->line('success_message')));
        } else {
            echo json_encode(array('status' => 2, 'msg' => $this->lang->line('something_went_wrong')));
        }
    }

    public function delete_array()
    {
        $id_array     = $this->input->post('id');
        $removed_data = $this->uploadcontent_model->getByIdArray($id_array);

        $is_removed = $this->uploadcontent_model->remove_array($id_array);
        if ($is_removed) {
            if (!empty($removed_data)) {
                foreach ($removed_data as $remove_data_key => $remove_data_value) {
                    $this->media_storage->filedelete($remove_data_value->img_name, $remove_data_value->dir_path);
                    $this->media_storage->filedelete($remove_data_value->thumb_name, $remove_data_value->thumb_path);
                }
            }

            echo json_encode(array('status' => 1, 'msg' => $this->lang->line('success_message')));
        } else {
            echo json_encode(array('status' => 2, 'msg' => $this->lang->line('something_went_wrong')));
        }
    }

    public function ajaxupdate()
    {


        $this->form_validation->set_rules('content_type', $this->lang->line('content_type'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('name', $this->lang->line('file_name'), 'required|trim|xss_clean');
    

        $data = array();

        if ($this->form_validation->run() == false) {

            $data = array(
                'content_type'               => form_error('content_type'),
                'name'          => form_error('name')
            );
            $array = array('status' => 0, 'error' => $data);
            echo json_encode($array);
        } else {
        $update = array(
            'id'              => $this->input->post('id'),
            'real_name'       => $this->input->post('name'),
            'content_type_id' => $this->input->post('content_type'),
        );
        $this->uploadcontent_model->add($update);
        echo json_encode(array('status' => 1, 'msg' => $this->lang->line('success_message')));
        }     
    }

    public function getsharelist()
    {
        $role_array = json_decode($this->customlib->getStaffRole());
        $role       = $role_array->id;
        if ($role == 7) {

            $m = $this->sharecontent_model->getsharelist();
        } else {

            $m = $this->sharecontent_model->getOtherStaffsharelist($role, $this->customlib->getStaffID());
        }
        $m = json_decode($m);

        $dt_data = array();
        if (!empty($m->data)) {
            foreach ($m->data as $key => $value) {

                $editbtn    = '';
                $deletebtn  = '';
                $share_link = '';

                $title = $value->title;

                $row   = array();
                $row[] = $title;

                if ($value->send_to == "public") {
                    $url_key         = $this->enc_lib->encrypt($value->id);
                    $shared_url_link = $this->customlib->getBaseUrl().'site/share/' . $url_key;

                    $share_link = "<button type='button' class='btn btn-default btn-xs' data-recordid=" . $value->id . " data-link=" . $shared_url_link . " data-toggle='modal' data-target='#linkModal' title=" . $this->lang->line('link') . " ><i class='fa fa-link'></i></button>";
                }

                $editbtn = "<button type='button' class='btn btn-default btn-xs' data-recordid=" . $value->id . " data-toggle='modal' data-target='#viewShareModal' title=" . $this->lang->line('view') . " ><i class='fa fa-eye'></i></button>";
                
                if ($this->rbac->hasPrivilege('content_share_list', 'can_delete')) {
                    $deletebtn = "<a onclick='return confirm(" . '"' . $this->lang->line('delete_confirm') . '"' . "  )' href='" . base_url() . "admin/content/delete_content/" . $value->id . "' class='btn btn-default btn-xs' title='" . $this->lang->line('delete') . "' data-toggle='tooltip'><i class='fa fa-trash'></i></a>";
                }else{
                    $deletebtn = "";
                }
                
                $row[] = $this->lang->line($value->send_to);
                $row[] = $this->customlib->dateformat($value->share_date);
                $row[] = $this->customlib->dateformat($value->valid_upto);
                $row[] = $this->customlib->getStaffFullName($value->name, $value->surname, $value->employee_id);
                if ($value->description == "") {
                    $row[] = $this->lang->line('no_description');
                } else {
                    $row[] = $value->description;
                }
                $row[]     = $share_link . ' ' . $editbtn . ' ' . $deletebtn;
                $dt_data[] = $row;
            }
        }

        $json_data = array(
            "draw"            => intval($m->draw),
            "recordsTotal"    => intval($m->recordsTotal),
            "recordsFiltered" => intval($m->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }

    public function index()
    {

        $this->session->set_userdata('top_menu', 'Download Center');
        $this->session->set_userdata('sub_menu', 'admin/content');
        $user_role                 = $this->customlib->getStaffRole();
        $data['title']             = 'Upload Content';
        $data['title_list']        = 'Upload Content List';
        $data['content_available'] = $this->customlib->contentAvailabelFor();
        $ght                       = $this->customlib->getcontenttype();
        $role                      = json_decode($user_role);

        $list = $this->content_model->getContentByRole($this->customlib->getStaffID(), $role->name);
        $class = $this->class_model->get();

        $data['list']      = $list;
        $data['classlist'] = $class;
        $userdata          = $this->customlib->getUserData();
        $carray            = array();
        $data['ght']       = $ght;
        $this->form_validation->set_rules('content_title', $this->lang->line('content_title'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('content_type', $this->lang->line('content_type'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('content_available[]', $this->lang->line('available_for'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('upload_date', $this->lang->line('date'), 'trim|required|xss_clean');
        $post_data = $this->input->post();

        if (isset($post_data['content_available']) and !isset($post_data['visibility']) and (in_array("student", $post_data['content_available']))) {
            $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        }

        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload');
        if ($this->form_validation->run() == false) {

            $this->load->view('layout/header');
            $this->load->view('admin/content/createcontent', $data);
            $this->load->view('layout/footer');
        } else {

            $vs                = $this->input->post('visibility');
            $content_available = $this->input->post('content_available');
            $visibility        = "No";
            $classes           = "";
            $section_id        = "";
            if (in_array('student', $content_available) && isset($vs)) {
                $visibility = $this->input->post('visibility');
            } elseif (in_array('student', $content_available) && !isset($vs)) {
                $section_id = $this->input->post('section_id');
                $classes    = $this->input->post('class_id');
            } else {

            }

            $content_for = array();
            foreach ($content_available as $cont_avail_key => $cont_avail_value) {
                $content_for[] = array('role' => $cont_avail_value);
            }

            $img_name = $this->media_storage->fileupload("file", "./uploads/school_content/material/");

            $data = array(
                'title'      => $this->input->post('content_title'),
                'type'       => $this->input->post('content_type'),
                'note'       => $this->input->post('note'),
                'class_id'   => $classes,
                'cls_sec_id' => $section_id,
                'created_by' => $this->customlib->getStaffID(),
                'is_public'  => $visibility,
                'file'       => $img_name,
            );

            if (isset($_POST['upload_date']) && $_POST['upload_date'] != '') {

                $data['date'] = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('upload_date')));
            }

            $insert_id = $this->content_model->add($data, $content_for);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/content');
        }
    }

    public function getsharedcontents()
    {
        $response                    = array();
        $share_content_id            = $this->input->post('share_content_id');
        $response['shared_contents'] = $this->sharecontent_model->getShareContentWithDocuments($share_content_id);
        $response['sch_setting']     = $this->setting_model->getSetting();
        $response['result_array']    = $this->sharecontent_model->getSharedUserBySharedID($share_content_id);
        $response_page               = $this->load->view('admin/content/_getsharedcontents', $response, true);
        $array                       = array('status' => '1', 'error' => '', 'page' => $response_page);
        echo json_encode($array);
    }

    public function index1()
    {
        $this->customlib->getStaffRole();
        $data['title']             = 'Upload Content';
        $data['title_list']        = 'Upload Content List';
        $data['content_available'] = $this->customlib->contentAvailabelFor();
        $ght                       = $this->customlib->getcontenttype();
        $list                      = $this->content_model->get();
        $class                     = $this->class_model->get();
        $data['list']              = $list;
        $data['classlist']         = $class;
        $data['ght']               = $ght;
        $this->form_validation->set_rules('content_title', $this->lang->line('content_title'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('content_type', $this->lang->line('content_type'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('content_available[]', $this->lang->line('available_for'), 'trim|required|xss_clean');
        $post_data = $this->input->post();

        if (isset($post_data['content_available']) and !isset($post_data['visibility']) and (in_array("student", $post_data['content_available']))) {
            $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        }

        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload');
        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header');
            $this->load->view('admin/content/createcontent', $data);
            $this->load->view('layout/footer');
        } else {

            $vs                = $this->input->post('visibility');
            $content_available = $this->input->post('content_available');
            $visibility        = "No";
            $classes           = "";
            $section_id        = "";
            if (in_array('student', $content_available) && isset($vs)) {
                $visibility = $this->input->post('visibility');
            } elseif (in_array('student', $content_available) && !isset($vs)) {
                $section_id = $this->input->post('section_id');
                $classes    = $this->input->post('class_id');
            } else {

            }

            $content_for = array();
            foreach ($content_available as $cont_avail_key => $cont_avail_value) {
                $content_for[] = array('role' => $cont_avail_value);
            }

            $data = array(
                'title'      => $this->input->post('content_title'),
                'type'       => $this->input->post('content_type'),
                'note'       => $this->input->post('note'),
                'class_id'   => $classes,
                'cls_sec_id' => $section_id,
                'date'       => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('upload_date'))),
                'file'       => $this->input->post('file'),
                'is_public'  => $visibility,
            );

            $insert_id = $this->content_model->add($data, $content_for);
            if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                $fileInfo = pathinfo($_FILES["file"]["name"]);
                $img_name = $insert_id . '.' . $fileInfo['extension'];
                move_uploaded_file($_FILES["file"]["tmp_name"], "./uploads/school_content/material/" . $img_name);
                $data_img = array('id' => $insert_id, 'file' => 'uploads/school_content/material/' . $img_name);
                $this->content_model->add($data_img);
            }
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/content');
        }
    }

    public function handle_upload()
    {
        $image_validate = $this->config->item('file_validate');
        $result         = $this->filetype_model->get();
        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {

            $file_type         = $_FILES["file"]['type'];
            $file_size         = $_FILES["file"]["size"];
            $file_name         = $_FILES["file"]["name"];
            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->file_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->file_mime)));
            $ext               = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($files = filesize($_FILES['file']['tmp_name'])) {

                if (!in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_type_not_allowed'));
                    return false;
                }
                if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_type_not_allowed'));
                    return false;
                }
                if ($file_size > $result->file_size) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_size_shoud_be_less_than') .
                        number_format($result->file_size / 1048576, 2) . ' MB');
                    return false;
                }
            } else {
                $this->form_validation->set_message('handle_upload', $this->lang->line('file_size_is_too_small'));
                return false;
            }

            return true;
        } else {
            $this->form_validation->set_message('handle_upload', $this->lang->line('the_file_field_is_required'));
            return false;
        }
    }

    public function download($file)
    {
        $this->media_storage->filedownload($this->uri->segment(4), "./uploads/school_content/material");

    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('upload_content', 'can_edit')) {
            access_denied();
        }
        $data['title']     = 'Add Content';
        $data['id']        = $id;
        $editpost          = $this->content_model->get($id);
        $data['editpost']  = $editpost;
        $ght               = $this->customlib->getcontenttype();
        $data['ght']       = $ght;
        $class             = $this->class_model->get();
        $data['classlist'] = $class;
        $this->form_validation->set_rules('content_title', $this->lang->line('content_title'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $listpost         = $this->content_model->get();
            $data['listpost'] = $listpost;
            $this->load->view('layout/header');
            $this->load->view('admin/content/editpost', $data);
            $this->load->view('layout/footer');
        } else {
            $data = array(
                'id'            => $this->input->post('id'),
                'content_title' => $this->input->post('content_title'),
                'content_type'  => $this->input->post('content_type'),
                'class_id'      => $this->input->post('class_id'),
                'date'          => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('upload_date'))),
                'file_uploaded' => $this->input->file['file']['name'],
            );
            $this->content_model->addcontentpost($data);
            if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                $fileInfo = pathinfo($_FILES["file"]["name"]);
                $img_name = $id . '.' . $fileInfo['extension'];
                move_uploaded_file($_FILES["file"]["tmp_name"], "./uploads/student_images/" . $img_name);
                $data_img = array('id' => $id, 'file_uploaded' => 'uploads/student_images/' . $img_name);
                $this->content_model->addcontentpost($data_img);
            }
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-center">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/content/createcontent/index');
        }
    }

}

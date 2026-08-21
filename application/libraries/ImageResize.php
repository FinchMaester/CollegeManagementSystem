<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class ImageResize
{

    protected $dir_path         = "";
    protected $thumb_path       = "";
    protected $maintain_ratio   = true;
    protected $create_thumb     = true;
    protected $thumb_marker     = 'thumb_';
    protected $thumb_width      = 150;
    protected $thumb_height     = 150;
    protected $random_file_name = false;
    protected $files            = array();
    private $response;
    protected $CI; // Fix PHP 8.2 deprecation warning
    protected $file_count; // Fix PHP 8.2 deprecation warning
    protected $curr_tmp_name; // Fix PHP 8.2 deprecation warning
    protected $new_file_name; // Fix PHP 8.2 deprecation warning
    protected $image_size_info; // Fix PHP 8.2 deprecation warning

    public function __construct($props = array())
    {
        //set local vars
        $this->CI = &get_instance();
        if (count($props) > 0) {
            $this->initialize($props);
        }
    }

    public function initialize($props = array())
    {
        // Convert array elements into class variables
        if (count($props) > 0) {
            foreach ($props as $key => $val) {

                $this->$key = $val;

            }
        }

        return true;
    }

    //resize function
    public function resize($files)
    {      

        $this->files      = $files;
        $this->file_count = count($this->files['name']);
        $this->response   = array();
        //================
        if ($this->file_count > 0) {
            if (!is_array($this->files['name'])) {
                throw new Exception('HTML file input field must be in array format!');
            }
            for ($x = 0; $x < $this->file_count; $x++) {
                //========
                $file_mime_type = mime_content_type($this->files['tmp_name'][$x]);

                if ($file_mime_type != 'inode/x-empty' || $this->files['size'][$x] > 0) {

                    $this->curr_tmp_name = $this->files['tmp_name'][$x];
                    $name                = $this->files['name'][$x];
                    $unique_name         = time() . "-" . uniqid(rand()) . "!";
                    
                    $this->new_file_name = $unique_name . $name;

                    $fileName = $this->new_file_name;
                    //upload image path - fix double uploads issue
                    $folder_path = $this->CI->customlib->getFolderPath();
                    
                    // Determine the base path for uploads
                    // Priority: folder_path (if valid) > FCPATH (if not root) > DOCUMENT_ROOT
                    $base_path = null;
                    
                    // Try folder_path first
                    if (!empty($folder_path)) {
                        $folder_path_clean = rtrim($folder_path, '/\\');
                        if (!empty($folder_path_clean) && $folder_path_clean !== '/' && $folder_path_clean !== '\\' && is_dir($folder_path_clean)) {
                            $base_path = $folder_path_clean;
                        }
                    }
                    
                    // Fallback to FCPATH if folder_path is not valid
                    if (empty($base_path)) {
                        $fcpath_clean = rtrim(FCPATH, '/\\');
                        if (!empty($fcpath_clean) && $fcpath_clean !== '/' && $fcpath_clean !== '\\') {
                            $base_path = $fcpath_clean;
                        }
                    }
                    
                    // Final fallback to DOCUMENT_ROOT if FCPATH is root
                    if (empty($base_path) || $base_path === '/' || $base_path === '\\') {
                        if (isset($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['DOCUMENT_ROOT'])) {
                            $doc_root = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
                            if (!empty($doc_root) && $doc_root !== '/' && $doc_root !== '\\') {
                                $base_path = $doc_root;
                            }
                        }
                    }
                    
                    // If still empty, use FCPATH as last resort (even if it's root - will show better error)
                    if (empty($base_path)) {
                        $base_path = rtrim(FCPATH, '/\\');
                    }
                    
                    // Final check: If base_path is still root, we have a configuration problem
                    if (empty($base_path) || $base_path === '/' || $base_path === '\\') {
                        $error_details = 'Configuration Error: Unable to determine base path. ';
                        $error_details .= 'folder_path: ' . ($folder_path ? $folder_path : 'empty') . ', ';
                        $error_details .= 'FCPATH: ' . FCPATH . ', ';
                        $error_details .= 'DOCUMENT_ROOT: ' . (isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : 'not set');
                        log_message('error', $error_details);
                        throw new Exception('Server configuration error: Unable to determine upload directory. Please configure folder_path in admin settings or check FCPATH in index.php. ' . $error_details);
                    }
                    
                    // Clean the directory path
                    $dir_path_clean = ltrim($this->dir_path, '/\\');
                    
                    // Remove 'uploads' prefix if base_path already ends with 'uploads'
                    if (substr($base_path, -7) === 'uploads' && substr($dir_path_clean, 0, 7) === 'uploads') {
                        $dir_path_clean = ltrim(substr($dir_path_clean, 7), '/\\');
                    }
                    
                    // Ensure dir_path_clean doesn't start with '/'
                    if (substr($dir_path_clean, 0, 1) === '/') {
                        $dir_path_clean = ltrim($dir_path_clean, '/');
                    }
                    
                    // Build upload path - always relative to base_path
                    $upload_image = $base_path . DIRECTORY_SEPARATOR . $dir_path_clean . basename($fileName);
                    
                    // Ensure directory exists
                    $upload_dir = dirname($upload_image);
                    
                    // Normalize path separators
                    $upload_dir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $upload_dir);
                    $upload_image = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $upload_image);
                    
                    // Final security check: Ensure upload_dir is within base_path
                    $base_path_normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $base_path);
                    if (strpos($upload_dir, $base_path_normalized) !== 0 && $base_path !== '/') {
                        // Path is outside base_path - reconstruct using base_path
                        $dir_path_final = ltrim($this->dir_path, '/\\');
                        if (substr($dir_path_final, 0, 1) === '/') {
                            $dir_path_final = ltrim($dir_path_final, '/');
                        }
                        $upload_dir = $base_path_normalized . DIRECTORY_SEPARATOR . $dir_path_final;
                        $upload_image = $base_path_normalized . DIRECTORY_SEPARATOR . $dir_path_final . basename($fileName);
                    }
                    
                    if (!is_dir($upload_dir)) {
                        // Try to create parent directories first
                        $parent_dir = dirname($upload_dir);
                        if (!is_dir($parent_dir) && !empty($parent_dir) && $parent_dir !== $upload_dir) {
                            if (!mkdir($parent_dir, 0755, true)) {
                                log_message('error', 'Failed to create parent directory: ' . $parent_dir);
                            }
                        }
                        
                        if (!mkdir($upload_dir, 0755, true)) {
                            // Get more detailed error information
                            $parent_dir = dirname($upload_dir);
                            $error_msg = 'Failed to create upload directory: ' . $upload_dir;
                            $error_msg .= ' | base_path: ' . $base_path;
                            $error_msg .= ' | folder_path: ' . ($folder_path ? $folder_path : 'empty');
                            $error_msg .= ' | FCPATH: ' . FCPATH;
                            $error_msg .= ' | DOCUMENT_ROOT: ' . (isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : 'not set');
                            $error_msg .= ' | parent_dir: ' . $parent_dir;
                            $error_msg .= ' | parent_exists: ' . (is_dir($parent_dir) ? 'yes' : 'no');
                            $error_msg .= ' | parent_writable: ' . (is_writable($parent_dir) ? 'yes' : 'no');
                            log_message('error', $error_msg);
                            throw new Exception('Failed to create upload directory: ' . $upload_dir . '. Please check directory permissions. Base path: ' . $base_path);
                        }
                    }
                    
                    // Ensure directory is writable
                    if (!is_writable($upload_dir)) {
                        throw new Exception('Upload directory is not writable: ' . $upload_dir . '. Please check directory permissions.');
                    }
                    
                    //upload image
                    // Capture the file size BEFORE moving, because move_uploaded_file()
                    // removes the temp file and filesize() on it would fail afterwards.
                    $uploaded_file_size = $this->files['size'][$x];
                    if (empty($uploaded_file_size) && is_readable($this->curr_tmp_name)) {
                        $uploaded_file_size = filesize($this->curr_tmp_name);
                    }
                    if (move_uploaded_file($this->curr_tmp_name, $upload_image)) {
                        //thumbnail creation
                        // Use the size captured before the move; fall back to the
                        // moved destination file if needed.
                        $this->image_size_info = !empty($uploaded_file_size)
                            ? $uploaded_file_size
                            : (is_readable($upload_image) ? filesize($upload_image) : 0);
                        $thumb_name            ="";
                        if ($this->create_thumb) {
                            if ($file_mime_type == 'image/jpeg' || $file_mime_type == 'image/png' || $file_mime_type == 'image/gif') {
                                $thumb_name            = $unique_name . $this->thumb_marker . $name;

                                $arr_image_details = getimagesize($upload_image); // pass id to thumb name

                                $original_width  = $arr_image_details[0];
                                $original_height = $arr_image_details[1];
                                if ($original_width > $original_height) {
                                    $new_width  = $this->thumb_width;
                                    $new_height = intval($original_height * $new_width / $original_width);
                                } else {
                                    $new_height = $this->thumb_height;
                                    $new_width  = intval($original_width * $new_height / $original_height);
                                }
                                $dest_x = intval(($this->thumb_width - $new_width) / 2);
                                $dest_y = intval(($this->thumb_height - $new_height) / 2);
                                if ($arr_image_details[2] == IMAGETYPE_GIF) {
                                    $imgt          = "ImageGIF";
                                    $imgcreatefrom = "ImageCreateFromGIF";
                                }
                                if ($arr_image_details[2] == IMAGETYPE_JPEG) {
                                    $imgt          = "ImageJPEG";
                                    $imgcreatefrom = "ImageCreateFromJPEG";
                                }
                                if ($arr_image_details[2] == IMAGETYPE_PNG) {
                                    $imgt          = "ImagePNG";
                                    $imgcreatefrom = "ImageCreateFromPNG";
                                }
                                if ($imgt) {
                                    $old_image = $imgcreatefrom($upload_image);
                                    $new_image = imagecreatetruecolor($this->thumb_width, $this->thumb_height);
                                    imagecopyresized($new_image, $old_image, $dest_x, $dest_y, 0, 0, $new_width, $new_height, $original_width, $original_height);
                                    
                                    // Fix thumb path construction - use same base_path as main path
                                    $thumb_path_clean = ltrim($this->thumb_path, '/\\');
                                    
                                    // Remove 'uploads' prefix if base_path already ends with 'uploads'
                                    if (substr($base_path, -7) === 'uploads' && substr($thumb_path_clean, 0, 7) === 'uploads') {
                                        $thumb_path_clean = ltrim(substr($thumb_path_clean, 7), '/\\');
                                    }
                                    
                                    // Ensure thumb_path_clean doesn't start with '/'
                                    if (substr($thumb_path_clean, 0, 1) === '/') {
                                        $thumb_path_clean = ltrim($thumb_path_clean, '/');
                                    }
                                    
                                    $thumb_full_path = $base_path . DIRECTORY_SEPARATOR . $thumb_path_clean . $thumb_name;
                                    
                                    // Normalize path separators
                                    $thumb_full_path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $thumb_full_path);
                                    
                                    // Ensure thumb directory exists
                                    $thumb_dir = dirname($thumb_full_path);
                                    
                                    // Security check: Ensure thumb_dir is within base_path
                                    $base_path_normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $base_path);
                                    if (strpos($thumb_dir, $base_path_normalized) !== 0 && $base_path !== '/') {
                                        // Path is outside base_path - reconstruct using base_path
                                        $thumb_path_final = ltrim($this->thumb_path, '/\\');
                                        if (substr($thumb_path_final, 0, 1) === '/') {
                                            $thumb_path_final = ltrim($thumb_path_final, '/');
                                        }
                                        $thumb_dir = $base_path_normalized . DIRECTORY_SEPARATOR . $thumb_path_final;
                                        $thumb_full_path = $base_path_normalized . DIRECTORY_SEPARATOR . $thumb_path_final . $thumb_name;
                                    }
                                    
                                    if (!is_dir($thumb_dir)) {
                                        // Try to create parent directories first
                                        $parent_dir = dirname($thumb_dir);
                                        if (!is_dir($parent_dir) && !empty($parent_dir) && $parent_dir !== $thumb_dir) {
                                            if (!mkdir($parent_dir, 0755, true)) {
                                                log_message('error', 'Failed to create thumb parent directory: ' . $parent_dir);
                                            }
                                        }
                                        
                                        if (!mkdir($thumb_dir, 0755, true)) {
                                            $error_msg = 'Failed to create thumb directory: ' . $thumb_dir;
                                            $error_msg .= ' | folder_path: ' . $folder_path;
                                            $error_msg .= ' | thumb_path_clean: ' . $thumb_path_clean;
                                            log_message('error', $error_msg);
                                            throw new Exception('Failed to create thumb directory: ' . $thumb_dir . '. Please check directory permissions.');
                                        }
                                    }
                                    
                                    // Ensure thumb directory is writable
                                    if (!is_writable($thumb_dir)) {
                                        throw new Exception('Thumb directory is not writable: ' . $thumb_dir . '. Please check directory permissions.');
                                    }
                                    
                                    $imgt($new_image, $thumb_full_path);
                                }

                            }
                            //to create thumbnail of image

                        }
                        $img_array = array(
                            'name'       => $name,
                            'store_name' => $this->new_file_name,
                            'file_type'  => $file_mime_type,
                            'file_size'  => $this->image_size_info,
                            'thumb_name' => $thumb_name,
                            'thumb_path' => $this->thumb_path,
                            'dir_path'   => $this->dir_path,
                            'height'     => 0,
                            'width'      => 0,
                        );

                        $this->response["images"][] = $img_array;
                    }
                }

                //=========
            }
        }
        return $this->response;
        //=================
    }

    public function resizeVideoImg($image_array)
    {
     
        $img_data        = json_decode($image_array);
        $image           = $img_data->thumbnail_url;
        $title           = $img_data->title;
        $path_info       = pathinfo($image);
        $file_extenstion = '.' . $path_info['extension']; // "bill

        $contextOptions = array(
            "ssl" => array(
                "verify_peer"      => false,
                "verify_peer_name" => false,
            ),
        );
        $unique_name = time() . "-" . uniqid(rand()) . "!";
        $filename    = time() . $file_extenstion;

        $this->new_file_name = $unique_name . $filename;

        $thumb_name = $unique_name . $this->thumb_marker . $filename;

        if (copy($image, $this->dir_path . '/' . $this->new_file_name, stream_context_create($contextOptions))) {
            $this->videoThumbnail($this->dir_path . '/' . $this->new_file_name, $this->CI->customlib->getFolderPath().$this->thumb_path . '/' . $thumb_name);
            return json_encode(array('vid_title' => $title, 'store_name' => $this->new_file_name, 'file_type' => 'video', 'file_size' => 0, 'thumb_name' => $thumb_name, 'thumb_path' => $this->thumb_path, 'dir_path' => $this->dir_path));
        }
        return false;
    }

    public function videoThumbnail($src, $dest)
    {
        $arr_image_details = getimagesize($src); // pass id to thumb name
        $original_width    = $arr_image_details[0];
        $original_height   = $arr_image_details[1];
        if ($original_width > $original_height) {
            $new_width  = $this->thumb_width;
            $new_height = intval($original_height * $new_width / $original_width);
        } else {
            $new_height = $this->thumb_height;
            $new_width  = intval($original_width * $new_height / $original_height);
        }
        $dest_x = intval(($this->thumb_width - $new_width) / 2);
        $dest_y = intval(($this->thumb_height - $new_height) / 2);
        if ($arr_image_details[2] == IMAGETYPE_GIF) {
            $imgt          = "ImageGIF";
            $imgcreatefrom = "ImageCreateFromGIF";
        }
        if ($arr_image_details[2] == IMAGETYPE_JPEG) {
            $imgt          = "ImageJPEG";
            $imgcreatefrom = "ImageCreateFromJPEG";
        }
        if ($arr_image_details[2] == IMAGETYPE_PNG) {
            $imgt          = "ImagePNG";
            $imgcreatefrom = "ImageCreateFromPNG";
        }
        if ($imgt) {
            $old_image = $imgcreatefrom($src);
            $new_image = imagecreatetruecolor($this->thumb_width, $this->thumb_height);
            imagecopyresized($new_image, $old_image, $dest_x, $dest_y, 0, 0, $new_width, $new_height, $original_width, $original_height);
            $imgt($new_image, $dest);
        }

    }

}

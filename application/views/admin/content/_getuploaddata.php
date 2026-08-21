<div class="row flex-column-sm grid_div <?php echo ($grid_view) ? "displayblock" : "displaynone" ?>">
    <div class="col-lg-12 col-md-12 col-sm-12  order-2 order-lg-1">
        <div class="row">

        <?php
        if (!empty($all_contents)) {
            foreach ($all_contents as $content_key => $content_value) {      
        ?>
        
            <div class="col-lg-4 col-sm-12 col-md-6 top_list_div" data-record-id="<?php echo $content_value->id; ?>" data-real_name="<?php echo $content_value->real_name; ?>" data-short_name="<?php echo $this->media_storage->fileview($content_value->img_name); ?>" data-type-id="<?php echo $content_value->content_type_id; ?>"  data-file-type="<?php echo $content_value->file_type; ?>"  data-name="<?php echo ($content_value->file_type == "video") ? $content_value->vid_url: $content_value->img_name; ?>"  data-path="<?php echo $content_value->dir_path; ?>">
                <article class="card card-product-list">
                    <div class="">
                        <aside class="img-wrap-fix-mobile div_image">
                        <a href="javascript:void(0);" class="img-wrap image_content">
                        <?php 
                            // Get base URL for all images - use current server URL if stored URL doesn't match
                            $stored_base_url = $this->customlib->getBaseUrl();
                            $current_base_url = base_url();
                            
                            // Extract host from both URLs
                            $stored_host = !empty($stored_base_url) ? parse_url($stored_base_url, PHP_URL_HOST) : '';
                            $current_host = parse_url($current_base_url, PHP_URL_HOST);
                            
                            // Use stored URL only if it matches current host, otherwise use current base_url
                            if (!empty($stored_base_url) && $stored_host === $current_host) {
                                $base_url = rtrim($stored_base_url, '/');
                                $base_url_source = 'stored';
                            } else {
                                // Running locally or on different server - use current base_url
                                $base_url = rtrim($current_base_url, '/');
                                $base_url_source = 'current';
                            }
                            
                            // Debug info for console
                            $base_url_debug = array(
                                'stored_base_url' => $stored_base_url,
                                'current_base_url' => $current_base_url,
                                'stored_host' => $stored_host,
                                'current_host' => $current_host,
                                'selected_base_url' => $base_url,
                                'source' => $base_url_source
                            );
                        ?>
                        <?php if ($content_value->file_type == 'xls' || $content_value->file_type == 'xlsx') {  ?>                 
                            <img class='p-2' src="<?php echo $base_url . '/backend/images/excelicon.png'; ?>" loading="lazy" style="display:block;">
                            
                        <?php } elseif ($content_value->file_type == 'ppt' || $content_value->file_type == 'pptx') {   ?>
                        
                            <img class='p-2' src="<?php echo $base_url . '/backend/images/pptxicon.png'; ?>" loading="lazy" style="display:block;">
                        
                        <?php } elseif ($content_value->file_type == 'doc' || $content_value->file_type == 'docx') {  ?>
                        
                            <img class='p-2' src="<?php echo $base_url . '/backend/images/wordicon.png'; ?>" loading="lazy" style="display:block;">
                        
                        <?php } elseif ($content_value->file_type == "csv") {   ?>
                        
                            <img class='p-2' src="<?php echo $base_url . '/backend/images/txticon.png'; ?>" loading="lazy" style="display:block;">
                            
                        <?php } elseif ($content_value->file_type == "pdf") {   ?>
                        
                            <img class='p-2' src="<?php echo $base_url . '/backend/images/pdficon.png'; ?>" loading="lazy" style="display:block;">
                            
                        <?php } elseif ($content_value->file_type == "text/plain") {  ?>
                        
                            <img class='p-2' src="<?php echo $base_url . '/backend/images/txticon.png'; ?>" loading="lazy" style="display:block;">
                            
                        <?php } elseif ($content_value->file_type == "zip" || $content_value->file_type == "rar") {   ?>
                        
                            <img class='p-2' src="<?php echo $base_url . '/backend/images/zipicon.png'; ?>" loading="lazy" style="display:block;">
                            
                        <?php } elseif ($content_value->file_type == 'video' || $content_value->file_type == 'gif' || $content_value->file_type == 'jpeg' || $content_value->file_type == 'jpg' || $content_value->file_type == 'jpe' || $content_value->file_type == 'jp2' || $content_value->file_type == 'j2k' || $content_value->file_type == 'jpf' || $content_value->file_type == 'jpg2' || $content_value->file_type == 'jpx' || $content_value->file_type == 'jpm' || $content_value->file_type == 'mj2' || $content_value->file_type == 'mjp2' || $content_value->file_type == 'png' || $content_value->file_type == 'tiff' || $content_value->file_type == 'tif' ) { 
                            // Use controller method to serve images - ensures proper path resolution
                            $primary_url = site_url('admin/content/view_image/' . $content_value->id . '/thumb');
                            $fallback_url = site_url('admin/content/view_image/' . $content_value->id . '/main');
                            $final_fallback_url = $base_url . '/backend/images/docsicon.png';
                            
                            // Debug data attributes
                            $debug_data = array(
                                'base_url' => $base_url,
                                'base_url_source' => $base_url_source,
                                'base_url_debug' => $base_url_debug,
                                'content_id' => $content_value->id,
                                'thumb_path' => isset($content_value->thumb_path) ? $content_value->thumb_path : '',
                                'thumb_name' => isset($content_value->thumb_name) ? $content_value->thumb_name : '',
                                'dir_path' => isset($content_value->dir_path) ? $content_value->dir_path : '',
                                'img_name' => isset($content_value->img_name) ? $content_value->img_name : '',
                                'primary_url' => $primary_url,
                                'fallback_url' => $fallback_url,
                                'final_fallback_url' => $final_fallback_url,
                                'method' => 'controller'
                            );
                        ?>
                            <img src="<?php echo $primary_url; ?>" 
                                 data-fallback="<?php echo htmlspecialchars($fallback_url); ?>"
                                 data-final-fallback="<?php echo htmlspecialchars($final_fallback_url); ?>"
                                 onerror="handleImageError(this);" 
                                 onload="this.classList.add('loaded');"
                                 data-debug="<?php echo htmlspecialchars(json_encode($debug_data)); ?>"
                                 loading="lazy" 
                                 alt="<?php echo htmlspecialchars($content_value->real_name); ?>"
                                 class="content-image">
     
                        <?php } elseif ( $content_value->file_type == '3g2' || $content_value->file_type == '3gp' || $content_value->file_type == 'mp4'  || $content_value->file_type == 'm4a' || $content_value->file_type == 'f4v' || $content_value->file_type == 'flv' || $content_value->file_type == 'webm') {  ?>
                        
                            <img class='p-2' src="<?php echo $base_url . '/backend/images/video-icon.png'; ?>" loading="lazy" style="display:block;">
                            
                        <?php }else { ?>
                        
                            <img class='p-2' src="<?php echo $base_url . '/backend/images/docsicon.png'; ?>" loading="lazy" style="display:block;">
                            
                        <?php } ?>                        
                        </a> 
                        </aside>
                        <!-- col.// -->
                        <div class="img-wrap-fix-right content_list" >
                            <div class="content-card-body relative flex-column">
                                <div class="radio-title">
                                    <input type="checkbox" name="share_checkbox[]" data-real_name="<?php echo $content_value->real_name; ?>" value="<?php echo $content_value->id; ?>" data-name="<?php echo $content_value->img_name; ?> " class="float-end share_checkbox relative z-index-1" <?php echo make_selected($content_value->id, $selected_content) ? "checked" : "";?>>
                                    <span><?php echo ($content_value->file_type == "video") ? $content_value->vid_title : $content_value->real_name; ?></span>
                                </div>                                  <!-- price-dewrap // -->
                                <div class="price-wrap me-3">
                                    <a href="#"><?php echo $this->customlib->getStaffFullName($content_value->staff_name,$content_value->surname,$content_value->employee_id);   ?></a>
                                </div>
                                <!-- price-dewrap // -->                                
                            </div>                            
                            <div class="d-flex justify-content-between content-footer-bottom">
                                <div>
                                    <span class="price h6"> <?php echo $this->customlib->dateyyyymmddToDateTimeformat($content_value->created_at); ?> </span>
                                </div>
                                <div class="inline-anchor">
                                      <a href="<?php echo site_url('admin/content/download_content/'.$content_value->id) ?>" class="text-default download_file pr-05" data-toggle="tooltip" title="<?php echo $this->lang->line('download'); ?>"><i class="fa fa-download"></i></a>
                                      <?php if($this->rbac->hasPrivilege('upload_content', 'can_delete')){ ?>
                                    <a href="#" class="text-danger delete_file" data-record-id="<?php echo $content_value->id; ?>" data-name="<?php echo ($content_value->file_type == "video") ? $content_value->vid_title : $content_value->real_name; ?>"><span class="display-inline-block" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>"><i class="fa fa-trash-o"></i></span></a>
                                <?php } ?>
                                </div>
                            </div>
                            <!-- card-body .// -->
                        </div>
                        <!-- col.// -->
                    </div>
                    <!-- row.// -->
                </article>
            </div>
            <?php
            }
        } else {
        ?>
            <div class="col-12 col-sm-6 col-md-12">
                <div class="alert alert-info">
                    <?php echo $this->lang->line('no_record_found'); ?>
                </div>
            </div>
        <?php }
        ?>
        </div>
    </div>
</div>

<!-- //================list view============ -->
<div class="row list_div <?php echo (!$grid_view) ? "displayblock" : "displaynone" ?>">
<?php
    if (!empty($all_contents)) {
?>

    <div class="col-lg-12">
      <div class="table-responsive">
         <table class="table table-bordered table_contents">
            <thead>
                <tr>
                    <th width="30">#</th>
                    <th width="30"><?php echo $this->lang->line('document'); ?></th>
                    <th width="30"><?php echo $this->lang->line('content_type'); ?></th>
                    <th width="30"><?php echo $this->lang->line('size'); ?></th>
                    <th width="30"><?php echo $this->lang->line('upload_by'); ?></th>
                    <th width="30" class="pull-right"><?php echo $this->lang->line('created_on'); ?></th>    
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach ($all_contents as $content_key => $content_value) {
                ?>                
                <tr data-record-id="<?php echo $content_value->id; ?>" data-real_name="<?php echo $content_value->real_name; ?>" data-short_name="<?php echo $this->media_storage->fileview($content_value->img_name); ?>" data-type-id="<?php echo $content_value->content_type_id; ?>"  data-file-type="<?php echo $content_value->file_type; ?>"  data-name="<?php echo ($content_value->file_type == "video") ? $content_value->vid_url: $content_value->img_name; ?>"  data-path="<?php echo $content_value->dir_path; ?>">
                    <td><input type="checkbox" name="share_checkbox[]" data-real_name="<?php echo $content_value->real_name; ?>" value="<?php echo $content_value->id; ?>" data-name="<?php echo $content_value->img_name; ?> " class="share_checkbox_list" <?php echo make_selected($content_value->id, $selected_content) ? "checked" : "";?>>
                    <?php
                       // Get base URL for list view images - use current server URL if stored URL doesn't match
                       $stored_list_base_url = $this->customlib->getBaseUrl();
                       $current_list_base_url = base_url();
                       
                       // Extract host from both URLs
                       $stored_list_host = !empty($stored_list_base_url) ? parse_url($stored_list_base_url, PHP_URL_HOST) : '';
                       $current_list_host = parse_url($current_list_base_url, PHP_URL_HOST);
                       
                       // Use stored URL only if it matches current host, otherwise use current base_url
                       if (!empty($stored_list_base_url) && $stored_list_host === $current_list_host) {
                           $list_base_url = rtrim($stored_list_base_url, '/');
                       } else {
                           // Running locally or on different server - use current base_url
                           $list_base_url = rtrim($current_list_base_url, '/');
                       }
                       
                       // Note: Using same base_url logic as grid view for consistency
                       
                       $image="";
                    if ($content_value->file_type == 'xls' || $content_value->file_type == 'xlsx') {
                            
                        $image= $list_base_url . '/backend/images/excelicon.png';
                    
                    } elseif ($content_value->file_type == 'ppt' || $content_value->file_type == 'pptx') {
                            
                        $image= $list_base_url . '/backend/images/pptxicon.png';
                    
                    } elseif ($content_value->file_type == 'doc' || $content_value->file_type == 'docx') {
                            
                        $image= $list_base_url . '/backend/images/wordicon.png';
                    
                    } elseif ($content_value->file_type == "csv") {
           
                        $image= $list_base_url . '/backend/images/txticon.png';
                    
                    } elseif ($content_value->file_type == "pdf") {
                            
                        $image= $list_base_url . '/backend/images/pdficon.png';
                    
                    } elseif ($content_value->file_type == "text/plain") {
                            
                        $image= $list_base_url . '/backend/images/txticon.png';
                    
                    } elseif ($content_value->file_type == "zip" || $content_value->file_type == "rar") {
                            
                        $image= $list_base_url . '/backend/images/zipicon.png';
                    
                    } elseif ($content_value->file_type == 'video' || $content_value->file_type == 'gif' || $content_value->file_type == 'jpeg' || $content_value->file_type == 'jpg' || $content_value->file_type == 'jpe' || $content_value->file_type == 'jp2' || $content_value->file_type == 'j2k' || $content_value->file_type == 'jpf' || $content_value->file_type == 'jpg2' || $content_value->file_type == 'jpx' || $content_value->file_type == 'jpm' || $content_value->file_type == 'mj2' || $content_value->file_type == 'mjp2' || $content_value->file_type == 'png' || $content_value->file_type == 'tiff' || $content_value->file_type == 'tif' ) {
                        // Use controller method to serve images - ensures proper path resolution
                        $image = site_url('admin/content/view_image/' . $content_value->id . '/thumb');

                    } elseif ( $content_value->file_type == '3g2'  || $content_value->file_type == '3gp'  || $content_value->file_type == 'mp4'  || $content_value->file_type == 'm4a'  || $content_value->file_type == 'f4v'  || $content_value->file_type == 'flv'  || $content_value->file_type == 'webm'  ) {
           
                        $image= $list_base_url . '/backend/images/video-icon.png';

                    }else {

                }
            ?>
                    <input type="hidden" name="image_display" value="<?php echo $image; ?>">
                    </td>
                    <td>
                        <?php echo ($content_value->file_type == "video") ? "<a href=" . $content_value->vid_url . " target='_blank'>" . $content_value->vid_title . "</a>" : "<a href='javascript:void(0);'>" . $content_value->real_name . "</a>" ?>
                    </td>
                    <td><?php echo $content_value->content_type; ?></td>
                    <td><?php echo ($content_value->file_type == "video") ? $this->lang->line('n_a') : format_file_size($content_value->file_size); ?></td>
                    <td><?php echo $this->customlib->getStaffFullName($content_value->staff_name,$content_value->surname,$content_value->employee_id);  ?></td>                
                    <td  class="pull-right"><?php echo $this->customlib->dateyyyymmddToDateTimeformat($content_value->created_at); ?></td>
                </tr>
                <?php }   ?>
            </tbody>
         </table>
      </div>
   </div>
<?php
} else {
    ?>
    <div class="col-12 col-sm-6 col-md-12">
        <div class="alert alert-info">
            <?php echo $this->lang->line('no_record_found'); ?>
        </div>
    </div>
<?php
}
?>
</div>

<?php
function make_selected($find, $selected_content)
{
    if (!empty($selected_content)) {
        if (in_array($find, $selected_content)) {
            return true;
        }
    }
    return false;
}

?>

<script type="text/javascript">
// Handle image error with proper fallback chain: thumbnail -> main image -> docsicon
function handleImageError(img) {
    var $img = $(img);
    var currentSrc = $img.attr('src');
    var fallbackUrl = $img.data('fallback');
    var finalFallbackUrl = $img.data('final-fallback');
    
    console.error('Image failed to load:', currentSrc);
    
    // If we haven't tried the fallback yet, try it
    if (fallbackUrl && currentSrc !== fallbackUrl && currentSrc !== finalFallbackUrl) {
        console.log('Trying fallback image:', fallbackUrl);
        $img.attr('src', fallbackUrl);
        // Set a flag to prevent infinite loop
        $img.data('tried-fallback', true);
    } 
    // If fallback also failed, use final fallback (docsicon)
    else if (finalFallbackUrl && currentSrc !== finalFallbackUrl) {
        console.log('Using final fallback (docsicon):', finalFallbackUrl);
        $img.attr('src', finalFallbackUrl);
        $img.data('tried-final-fallback', true);
        // Prevent further error handling
        $img.off('error');
    } else {
        // Already tried all fallbacks, prevent infinite loop
        $img.off('error');
    }
}

// Debug image loading on page load
$(document).ready(function() {
    console.log('=== CONTENT IMAGE DEBUG START ===');
    console.log('Total images found:', $('.content-image').length);
    
    $('.content-image').each(function(index) {
        var $img = $(this);
        var debugData = {};
        try {
            debugData = JSON.parse($img.attr('data-debug') || '{}');
        } catch(e) {
            console.warn('Could not parse debug data for image', index);
        }
        
        console.log('Image ' + (index + 1) + ':', {
            src: $img.attr('src'),
            alt: $img.attr('alt'),
            fallback: $img.data('fallback') || 'N/A',
            final_fallback: $img.data('final-fallback') || 'N/A',
            base_url: debugData.base_url || 'N/A',
            thumb_path: debugData.thumb_path || 'N/A',
            thumb_name: debugData.thumb_name || 'N/A',
            dir_path: debugData.dir_path || 'N/A',
            img_name: debugData.img_name || 'N/A',
            primary_url: debugData.primary_url || 'N/A',
            fallback_url: debugData.fallback_url || 'N/A',
            complete: this.complete,
            naturalWidth: this.naturalWidth,
            naturalHeight: this.naturalHeight
        });
        
        // Test if image URL is accessible
        var testImg = new Image();
        testImg.onload = function() {
            console.log('✓ Image verified:', $img.attr('src'));
        };
        testImg.onerror = function() {
            console.error('✗ Image failed verification:', $img.attr('src'));
            console.error('  Will try fallback:', $img.data('fallback') || 'N/A');
        };
        testImg.src = $img.attr('src');
    });
    
    console.log('=== CONTENT IMAGE DEBUG END ===');
});
</script>
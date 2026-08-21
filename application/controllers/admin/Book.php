<?php




if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}




class Book extends Admin_Controller
{




    public function __construct()
    {
        parent::__construct();
        $this->load->library('encoding_lib');
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }




    public function index()
    {
        if (!$this->rbac->hasPrivilege('books', 'can_view')) {
            access_denied();
        }




        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'book/index');




        $data['title']      = 'Add Book';
        $data['title_list'] = 'Book Details';
        $listbook           = $this->book_model->listbook();
        $data['listbook']   = $listbook;
        $this->load->model('program_model');
        $this->load->model('class_model');
        $data['programlist'] = $this->program_model->getAll();
        $data['classlist']  = $this->class_model->get();
        $this->load->view('layout/header');
        $this->load->view('admin/book/createbook', $data);
        $this->load->view('layout/footer');
    }




    public function getall()
    {
        if (!$this->rbac->hasPrivilege('books', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'book/getall');
        $this->load->view('layout/header');
        $this->load->view('admin/book/getall');
        $this->load->view('layout/footer');
    }



    public function create()
    {
        if (!$this->rbac->hasPrivilege('books', 'can_add')) {
            access_denied();
        }
        $data['title']      = 'Add Book';
        $data['title_list'] = 'Book Details';
        $this->form_validation->set_rules('book_title', $this->lang->line('book_title'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('perunitcost', $this->lang->line('book_price'), 'numeric');
        $this->form_validation->set_rules('qty', $this->lang->line('qty'), 'numeric|greater_than[0]');
       
        if ($this->form_validation->run() == false) {
            $listbook         = $this->book_model->listbook();
            $data['listbook'] = $listbook;
            $this->load->model('program_model');
            $this->load->model('class_model');
            $data['programlist'] = $this->program_model->getAll();
            $data['classlist']  = $this->class_model->get();
            $this->load->view('layout/header');
            $this->load->view('admin/book/createbook', $data);
            $this->load->view('layout/footer');
        } else {
           
            if($this->input->post('perunitcost')){
                $perunitcost = convertCurrencyFormatToBaseAmount($this->input->post('perunitcost'));
            }else{
                $perunitcost = '';
            }
           
            // Get postdate
            $postdate = null;
            if (isset($_POST['postdate']) && $_POST['postdate'] != '') {
                $ad_date = $this->customlib->dateFormatToYYYYMMDD($this->input->post('postdate'));
                $postdate = $ad_date ? $ad_date : null;
            }
            
            // Optional: Faculty (section), program, level (class), lock for current session
            $section_id = $this->input->post('section_id') ? (int)$this->input->post('section_id') : null;
            $program_id = $this->input->post('program_id') ? (int)$this->input->post('program_id') : null;
            $class_id   = $this->input->post('class_id') ? (int)$this->input->post('class_id') : null;
            $lock_for_session = $this->input->post('lock_for_session') ? true : false;
            $lock_session_id  = $lock_for_session ? $this->setting_model->getCurrentSession() : null;
            
            // Get quantity and starting book number
            $qty = (int)$this->input->post('qty');
            $book_no_start = $this->input->post('book_no');
            
            // Validate quantity
            if ($qty <= 0) {
                $qty = 1; // Default to 1 if not provided or invalid
            }
            
            // Digit-only sequences preserve leading zeros (e.g. 001); is_numeric/int breaks that.
            $trimmed_start = trim((string) $book_no_start);
            $is_digit_sequence = ($trimmed_start !== '' && ctype_digit($trimmed_start));
            
            // Variables to track created books
            $books_created = array();
            $total_books_added = 0;
            
            // Variables to track skipped books
            $books_skipped = array();
            $skip_reasons = array();
            
            $numeric_block = null;
            if ($is_digit_sequence) {
                $numeric_block = $this->_find_first_free_numeric_block($trimmed_start, $qty);
                if ($numeric_block === null) {
                    $msg = '<div class="alert alert-danger text-left">Could not find ' . (int) $qty .
                        ' consecutive free book numbers starting near ' . htmlspecialchars($trimmed_start, ENT_QUOTES, 'UTF-8') .
                        '. Try a different starting number.</div>';
                    $this->session->set_flashdata('msg', $msg);
                    redirect('admin/book/getall');
                    return;
                }
            }
            
            // Create multiple book entries based on quantity
            for ($i = 0; $i < $qty; $i++) {
                // Generate book number (unique per copy when qty > 1)
                if ($is_digit_sequence && $numeric_block !== null) {
                    $current_book_no = $this->_format_numeric_book_no(
                        $numeric_block['base'] + $i,
                        $numeric_block['width']
                    );
                } elseif ($trimmed_start === '') {
                    $current_book_no = 'AUTO-' . str_replace('.', '', uniqid('', true)) . '-' . $i;
                } elseif ($qty > 1) {
                    $current_book_no = $trimmed_start . '-' . ($i + 1);
                } else {
                    $current_book_no = $trimmed_start;
                }
                
                // Check if book number already exists
                if ($this->book_model->bookNumberExists($current_book_no)) {
                    $books_skipped[] = $current_book_no;
                    $skip_reasons[$current_book_no] = 'Book number already exists';
                    continue; // Skip this book and continue with next
                }
                
                // Prepare data for this book entry
                $book_data = array(
                    'book_title'  => $this->input->post('book_title'),
                    'book_no'     => $current_book_no,
                    'isbn_no'     => $this->input->post('isbn_no'),
                    'subject'     => $this->input->post('subject'),
                    'rack_no'     => $this->input->post('rack_no'),
                    'publish'     => $this->input->post('publish'),
                    'author'      => $this->input->post('author'),
                    'edition'     => $this->input->post('edition'),
                    'qty'         => 1, // Each entry represents 1 physical book
                    'perunitcost' => $perunitcost,
                    'description' => $this->input->post('description'),
                    'postdate'    => $postdate
                );
                if ($section_id !== null) $book_data['section_id'] = $section_id;
                if ($program_id !== null) $book_data['program_id'] = $program_id;
                if ($class_id !== null) $book_data['class_id'] = $class_id;
                if ($lock_session_id !== null) $book_data['lock_session_id'] = $lock_session_id;
                
                // Insert into database using existing model method
                try {
                    $insert_id = $this->book_model->addbooks($book_data);
                    
                    if ($insert_id && $insert_id > 0) {
                        $total_books_added++;
                        $books_created[] = $current_book_no;
                    } else {
                        // If insert failed
                        $books_skipped[] = $current_book_no;
                        $skip_reasons[$current_book_no] = 'Database insert failed';
                    }
                } catch (Exception $e) {
                    // Catch any database errors
                    $books_skipped[] = $current_book_no;
                    $skip_reasons[$current_book_no] = 'Error: ' . $e->getMessage();
                }
            }
            
            // Success message based on how many books were created
            $msg = '';
            if ($total_books_added > 0) {
                if ($total_books_added > 1 && $is_digit_sequence) {
                    $book_numbers_range = $books_created[0] . ' - ' . $books_created[count($books_created) - 1];
                    $msg = '<div class="alert alert-success text-left">' . 
                           $total_books_added . ' books added successfully with book numbers: ' . 
                           $book_numbers_range . '</div>';
                } else if ($total_books_added > 1) {
                    $msg = '<div class="alert alert-success text-left">' . 
                           $total_books_added . ' books added successfully</div>';
                } else {
                    $msg = '<div class="alert alert-success text-left">' . 
                           $this->lang->line('success_message') . '</div>';
                }
                
                // Add warning if some books were skipped
                if (!empty($books_skipped)) {
                    $skipped_list = implode(', ', $books_skipped);
                    $msg .= '<div class="alert alert-warning text-left">' . 
                           count($books_skipped) . ' book(s) skipped (duplicate book numbers): ' . 
                           $skipped_list . '</div>';
                }
                $this->session->set_flashdata('msg', $msg);
            } else {
                // No books were added
                if (!empty($books_skipped)) {
                    $skipped_list = implode(', ', $books_skipped);
                    $skip_details = array();
                    foreach ($skip_reasons as $book_no => $reason) {
                        $skip_details[] = $book_no . ' (' . $reason . ')';
                    }
                    $skip_details_str = implode(', ', $skip_details);
                    $msg = '<div class="alert alert-danger text-left">' . 
                           'No books were added. Issues: ' . $skip_details_str . '</div>';
                } else {
                    // Check if quantity was 0 or invalid
                    if ($qty <= 0) {
                        $msg = '<div class="alert alert-danger text-left">' . 
                               'Please enter a valid quantity (greater than 0).</div>';
                    } else {
                        $msg = '<div class="alert alert-danger text-left">' . 
                               'Failed to add books. Please check all required fields and try again.</div>';
                    }
                }
                $this->session->set_flashdata('msg', $msg);
            }
            
            redirect('admin/book/getall');
        }
    }

    /**
     * Find the first starting value such that qty consecutive numeric book numbers are all unused.
     * When $exclude_book_id is set, the first number in the block may match that row (edit flow).
     *
     * @param string   $startDigits Digits only (after trim), may include leading zeros
     * @param int      $qty
     * @param int|null $exclude_book_id
     * @return array|null [ 'base' => int, 'width' => int ]
     */
    private function _find_first_free_numeric_block($startDigits, $qty, $exclude_book_id = null)
    {
        $base = (int) $startDigits;
        $width = strlen($startDigits);
        $maxOffset = 50000;
        for ($offset = 0; $offset < $maxOffset; $offset++) {
            $blockStart = $base + $offset;
            $ok = true;
            for ($j = 0; $j < $qty; $j++) {
                $bn = $this->_format_numeric_book_no($blockStart + $j, $width);
                $ex = ($j === 0 && $exclude_book_id !== null) ? $exclude_book_id : null;
                if ($this->book_model->bookNumberExists($bn, $ex)) {
                    $ok = false;
                    break;
                }
            }
            if ($ok) {
                return array('base' => $blockStart, 'width' => $width);
            }
        }
        return null;
    }

    /**
     * @param int $num
     * @param int $width Minimum width for leading zeros; expands when value exceeds width
     * @return string
     */
    private function _format_numeric_book_no($num, $width)
    {
        $s = (string) (int) $num;
        if (strlen($s) < $width) {
            return str_pad($s, $width, '0', STR_PAD_LEFT);
        }
        return $s;
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('books', 'can_edit')) {
            access_denied();
        }
    
        $data['title']      = 'Edit Book';
        $data['title_list'] = 'Book Details';
        $data['id']         = $id;
        $editbook           = $this->book_model->get($id);
        $data['editbook']   = $editbook;
        $this->form_validation->set_rules('book_title', $this->lang->line('book_title'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('perunitcost', $this->lang->line('book_price'), 'numeric');
        $this->form_validation->set_rules('qty', $this->lang->line('qty'), 'numeric');
       
        if ($this->form_validation->run() == false) {
            $listbook         = $this->book_model->listbook();
            $data['listbook'] = $listbook;
            $this->load->model('program_model');
            $this->load->model('class_model');
            $data['programlist'] = $this->program_model->getAll();
            $data['classlist']  = $this->class_model->get();
            $this->load->view('layout/header');
            $this->load->view('admin/book/editbook', $data);
            $this->load->view('layout/footer');
        } else {
           
            if($this->input->post('perunitcost')){
                $perunitcost    =   convertCurrencyFormatToBaseAmount($this->input->post('perunitcost'));
            }else{
                $perunitcost    = '';
            }
           
            // Get postdate
            $postdate = null;
            if (isset($_POST['postdate']) && $_POST['postdate'] != '') {
                $ad_date = $this->customlib->dateFormatToYYYYMMDD($this->input->post('postdate'));
                $postdate = $ad_date ? $ad_date : null;
            }
            
            // Optional: Faculty (section), program, level (class), lock for current session
            $section_id = $this->input->post('section_id') ? (int)$this->input->post('section_id') : null;
            $program_id = $this->input->post('program_id') ? (int)$this->input->post('program_id') : null;
            $class_id   = $this->input->post('class_id') ? (int)$this->input->post('class_id') : null;
            $lock_for_session = $this->input->post('lock_for_session') ? true : false;
            $lock_session_id  = $lock_for_session ? $this->setting_model->getCurrentSession() : null;
            
            // Get quantity and starting book number
            $qty = (int)$this->input->post('qty');
            $book_no_start = $this->input->post('book_no');
            $book_id = $this->input->post('id');
            
            if ($qty <= 0) {
                $qty = 1;
            }
            
            $trimmed_start = trim((string) $book_no_start);
            $is_digit_sequence = ($trimmed_start !== '' && ctype_digit($trimmed_start));
            
            // Planned book numbers for each copy (aligned with create())
            $book_numbers = array();
            if ($is_digit_sequence) {
                if ($qty > 1) {
                    $numeric_block = $this->_find_first_free_numeric_block($trimmed_start, $qty, $book_id);
                    if ($numeric_block === null) {
                        $msg = '<div class="alert alert-danger text-left">Could not find ' . (int) $qty .
                            ' consecutive free book numbers starting near ' . htmlspecialchars($trimmed_start, ENT_QUOTES, 'UTF-8') .
                            '. Try a different starting number.</div>';
                        $this->session->set_flashdata('msg', $msg);
                        redirect('admin/book/getall');
                        return;
                    }
                    for ($i = 0; $i < $qty; $i++) {
                        $book_numbers[] = $this->_format_numeric_book_no(
                            $numeric_block['base'] + $i,
                            $numeric_block['width']
                        );
                    }
                } else {
                    if ($this->book_model->bookNumberExists($trimmed_start, $book_id)) {
                        $msg = '<div class="alert alert-danger text-left">Book number already exists.</div>';
                        $this->session->set_flashdata('msg', $msg);
                        redirect('admin/book/getall');
                        return;
                    }
                    $book_numbers[] = $trimmed_start;
                }
            } elseif ($trimmed_start === '') {
                for ($i = 0; $i < $qty; $i++) {
                    $book_numbers[] = 'AUTO-' . str_replace('.', '', uniqid('', true)) . '-' . $i;
                }
            } elseif ($qty > 1) {
                for ($i = 0; $i < $qty; $i++) {
                    $book_numbers[] = $trimmed_start . '-' . ($i + 1);
                }
            } else {
                if ($trimmed_start !== '' && $this->book_model->bookNumberExists($trimmed_start, $book_id)) {
                    $msg = '<div class="alert alert-danger text-left">Book number already exists.</div>';
                    $this->session->set_flashdata('msg', $msg);
                    redirect('admin/book/getall');
                    return;
                }
                $book_numbers[] = $trimmed_start;
            }
            
            // Variables to track created books
            $books_created = array();
            $total_books_added = 0;
            
            // Variables to track skipped books
            $books_skipped = array();
            
            // Update the current book entry (first copy)
            $update_data = array(
                'id'          => $book_id,
                'book_title'  => $this->input->post('book_title'),
                'book_no'     => $book_numbers[0],
                'isbn_no'     => $this->input->post('isbn_no'),
                'subject'     => $this->input->post('subject'),
                'rack_no'     => $this->input->post('rack_no'),
                'publish'     => $this->input->post('publish'),
                'author'      => $this->input->post('author'),
                'edition'     => $this->input->post('edition'),
                'perunitcost' => $perunitcost,
                'description' => $this->input->post('description'),
                'postdate'    => $postdate
            );
            if ($section_id !== null) $update_data['section_id'] = $section_id; else $update_data['section_id'] = null;
            if ($program_id !== null) $update_data['program_id'] = $program_id; else $update_data['program_id'] = null;
            if ($class_id !== null) $update_data['class_id'] = $class_id; else $update_data['class_id'] = null;
            $update_data['lock_session_id'] = $lock_session_id;
            
            if ($qty > 1) {
                $update_data['qty'] = 1;
                try {
                    $update_result = $this->book_model->addbooks($update_data);
                    if ($update_result && $update_result > 0) {
                        $total_books_added++;
                        $books_created[] = $book_numbers[0];
                    } else {
                        $books_skipped[] = $book_numbers[0];
                    }
                } catch (Exception $e) {
                    $books_skipped[] = $book_numbers[0];
                }
                
                for ($i = 1; $i < $qty; $i++) {
                    $current_book_no = $book_numbers[$i];
                    if ($this->book_model->bookNumberExists($current_book_no)) {
                        $books_skipped[] = $current_book_no;
                        continue;
                    }
                    $book_data = array(
                        'book_title'  => $this->input->post('book_title'),
                        'book_no'     => $current_book_no,
                        'isbn_no'     => $this->input->post('isbn_no'),
                        'subject'     => $this->input->post('subject'),
                        'rack_no'     => $this->input->post('rack_no'),
                        'publish'     => $this->input->post('publish'),
                        'author'      => $this->input->post('author'),
                        'edition'     => $this->input->post('edition'),
                        'qty'         => 1,
                        'perunitcost' => $perunitcost,
                        'description' => $this->input->post('description'),
                        'postdate'    => $postdate
                    );
                    if ($section_id !== null) $book_data['section_id'] = $section_id;
                    if ($program_id !== null) $book_data['program_id'] = $program_id;
                    if ($class_id !== null) $book_data['class_id'] = $class_id;
                    if ($lock_session_id !== null) $book_data['lock_session_id'] = $lock_session_id;
                    
                    $insert_id = $this->book_model->addbooks($book_data);
                    if ($insert_id) {
                        $total_books_added++;
                        $books_created[] = $current_book_no;
                    } else {
                        $books_skipped[] = $current_book_no;
                    }
                }
            } else {
                $update_data['qty'] = 1;
                try {
                    $update_result = $this->book_model->addbooks($update_data);
                    if ($update_result && $update_result > 0) {
                        $total_books_added++;
                        $books_created[] = $book_numbers[0];
                    } else {
                        if (!empty($trimmed_start) && $this->book_model->bookNumberExists($trimmed_start, $book_id)) {
                            $books_skipped[] = $trimmed_start . ' (duplicate book number)';
                        } else {
                            $books_skipped[] = $trimmed_start . ' (update failed)';
                        }
                    }
                } catch (Exception $e) {
                    $books_skipped[] = $trimmed_start . ' (error: ' . $e->getMessage() . ')';
                }
            }
            
            // Success message based on how many books were created/updated
            $msg = '';
            if ($total_books_added > 0) {
                if ($total_books_added > 1 && $is_digit_sequence) {
                    $book_numbers_range = $books_created[0] . ' - ' . $books_created[count($books_created) - 1];
                    $msg = '<div class="alert alert-success text-left">' . 
                           'Book updated successfully. ' . 
                           ($total_books_added - 1) . ' additional book(s) created with book numbers: ' . 
                           $book_numbers_range . '</div>';
                } else {
                    $msg = '<div class="alert alert-success text-left">' . 
                           $this->lang->line('update_message') . '</div>';
                }
                
                // Add warning if some books were skipped
                if (!empty($books_skipped)) {
                    $skipped_list = implode(', ', $books_skipped);
                    $msg .= '<div class="alert alert-warning text-left">' . 
                           count($books_skipped) . ' book(s) skipped (duplicate book numbers): ' . 
                           $skipped_list . '</div>';
                }
                $this->session->set_flashdata('msg', $msg);
            } else {
                // No books were updated/created
                if (!empty($books_skipped)) {
                    $skipped_list = implode(', ', $books_skipped);
                    $msg = '<div class="alert alert-danger text-left">' . 
                           'Update failed. All book numbers already exist: ' . 
                           $skipped_list . '</div>';
                } else {
                    $msg = '<div class="alert alert-danger text-left">' . 
                           'Failed to update book. Please try again.</div>';
                }
                $this->session->set_flashdata('msg', $msg);
            }
            
            redirect('admin/book/getall');
        }
    }




    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('books', 'can_delete')) {
            access_denied();
        }
        $data['title'] = 'Fees Master List';
        $this->book_model->remove($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('delete_message') . '</div>');
        redirect('admin/book/getall');
    }




    public function getAvailQuantity()
    {




        $book_id   = $this->input->post('book_id');
        $available = 0;
        if ($book_id != "") {
            $result    = $this->bookissue_model->getAvailQuantity($book_id);
            $available = $result->qty - $result->total_issue;
        }
        $result_final = array('status' => '1', 'qty' => $available);
        echo json_encode($result_final);
    }




    public function import()
    {
        $data['fields'] = array('book_title', 'book_no', 'isbn_no', 'subject', 'rack_no', 'publish', 'author', 'edition', 'qty', 'perunitcost', 'postdate', 'description', 'available');
        $this->form_validation->set_rules('file', $this->lang->line('images'), 'callback_handle_csv_upload');
        if ($this->form_validation->run() == false) {
   
            $this->load->view('layout/header');
            $this->load->view('admin/book/import', $data);
            $this->load->view('layout/footer');
        } else {
            $rowcount = 0;
            $total_books_added = 0;
            $books_skipped = array();
            $result = array();
            // Optional: apply Faculty / Program / Level / Lock to all imported books
            $section_id = $this->input->post('section_id') ? (int)$this->input->post('section_id') : null;
            $program_id = $this->input->post('program_id') ? (int)$this->input->post('program_id') : null;
            $class_id = $this->input->post('class_id') ? (int)$this->input->post('class_id') : null;
            $lock_for_session = $this->input->post('lock_for_session') ? true : false;
            $lock_session_id = $lock_for_session ? $this->setting_model->getCurrentSession() : null;

            if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                if ($ext == 'csv') {
                    $file = $_FILES['file']['tmp_name'];
                    $this->load->library('CSVReader');
                    $result = $this->csvreader->parse_file($file);
   
                    $books_to_insert = array();
                    
                    if (!empty($result)) {
                        foreach ($result as $r_key => $r_value) {
                            // Clean and prepare data
                            $book_title  = $this->encoding_lib->toUTF8($result[$r_key]['book_title']);
                            $book_no     = $this->encoding_lib->toUTF8($result[$r_key]['book_no']);
                            $isbn_no     = $this->encoding_lib->toUTF8($result[$r_key]['isbn_no']);
                           
                            // Make subject optional - check if it exists in the CSV
                            $subject     = isset($result[$r_key]['subject']) ?
                                $this->encoding_lib->toUTF8($result[$r_key]['subject']) : '';
                           
                            $rack_no     = $this->encoding_lib->toUTF8($result[$r_key]['rack_no']);
                            $publish     = $this->encoding_lib->toUTF8($result[$r_key]['publish']);
                            $author      = $this->encoding_lib->toUTF8($result[$r_key]['author']);
                            $edition     = isset($result[$r_key]['edition']) ? $this->encoding_lib->toUTF8($result[$r_key]['edition']) : '';
                            $qty         = (int)$this->encoding_lib->toUTF8($result[$r_key]['qty']);
                           
                            // Sanitize perunitcost by removing non-numeric characters except the decimal point
                            $sanitized_perunitcost = preg_replace('/[^0-9.]/', '', $this->encoding_lib->toUTF8($result[$r_key]['perunitcost']));
                            $perunitcost = convertCurrencyFormatToBaseAmount($sanitized_perunitcost);
                           
                            $postdate_raw = $this->encoding_lib->toUTF8($result[$r_key]['postdate']);
                            $description = $this->encoding_lib->toUTF8($result[$r_key]['description']);
                            
                            // Convert postdate format if needed
                            $postdate = null;
                            if (!empty($postdate_raw)) {
                                $ad_date = $this->customlib->dateFormatToYYYYMMDD($postdate_raw);
                                $postdate = $ad_date ? $ad_date : null;
                            }
                            
                            $trimmed_bn = trim((string) $book_no);
                            $is_digit_sequence = ($trimmed_bn !== '' && ctype_digit($trimmed_bn));
                            if ($qty <= 0) {
                                $qty = 1;
                            }
                            
                            $append_book_row = function ($current_book_no) use (
                                &$books_to_insert,
                                &$total_books_added,
                                $book_title,
                                $isbn_no,
                                $subject,
                                $rack_no,
                                $publish,
                                $author,
                                $edition,
                                $perunitcost,
                                $description,
                                $postdate,
                                $section_id,
                                $program_id,
                                $class_id,
                                $lock_session_id
                            ) {
                                $book_data = array(
                                    'book_title'  => $book_title,
                                    'book_no'     => $current_book_no,
                                    'isbn_no'     => $isbn_no,
                                    'subject'     => $subject,
                                    'rack_no'     => $rack_no,
                                    'publish'     => $publish,
                                    'author'      => $author,
                                    'edition'     => $edition,
                                    'qty'         => 1,
                                    'perunitcost' => $perunitcost,
                                    'description' => $description,
                                    'postdate'    => $postdate
                                );
                                if ($section_id !== null) $book_data['section_id'] = $section_id;
                                if ($program_id !== null) $book_data['program_id'] = $program_id;
                                if ($class_id !== null) $book_data['class_id'] = $class_id;
                                if ($lock_session_id !== null) $book_data['lock_session_id'] = $lock_session_id;
                                $books_to_insert[] = $book_data;
                                $total_books_added++;
                            };
                            
                            if ($is_digit_sequence && $qty > 1) {
                                $numeric_block = $this->_find_first_free_numeric_block($trimmed_bn, $qty);
                                if ($numeric_block === null) {
                                    $books_skipped[] = $trimmed_bn . ' (no free block of ' . $qty . ')';
                                    $rowcount++;
                                    continue;
                                }
                                for ($i = 0; $i < $qty; $i++) {
                                    $current_book_no = $this->_format_numeric_book_no(
                                        $numeric_block['base'] + $i,
                                        $numeric_block['width']
                                    );
                                    $append_book_row($current_book_no);
                                }
                            } elseif ($is_digit_sequence && $qty === 1) {
                                if ($this->book_model->bookNumberExists($trimmed_bn)) {
                                    $books_skipped[] = $trimmed_bn;
                                    $rowcount++;
                                    continue;
                                }
                                $append_book_row($trimmed_bn);
                            } elseif ($trimmed_bn === '') {
                                for ($i = 0; $i < $qty; $i++) {
                                    $append_book_row('AUTO-' . str_replace('.', '', uniqid('', true)) . '-' . $i);
                                }
                            } elseif ($qty > 1) {
                                for ($i = 0; $i < $qty; $i++) {
                                    $current_book_no = $trimmed_bn . '-' . ($i + 1);
                                    if ($this->book_model->bookNumberExists($current_book_no)) {
                                        $books_skipped[] = $current_book_no;
                                        continue;
                                    }
                                    $append_book_row($current_book_no);
                                }
                            } else {
                                if ($this->book_model->bookNumberExists($trimmed_bn)) {
                                    $books_skipped[] = $trimmed_bn;
                                    $rowcount++;
                                    continue;
                                }
                                $append_book_row($trimmed_bn);
                            }
                            
                            $rowcount++;
                        }
   
                        // Insert all books in batch
                        if (!empty($books_to_insert)) {
                            $this->db->insert_batch('books', $books_to_insert);
                        }
                    }
                    // Prepare success message
                    $message = $this->lang->line('records_found_in_csv_file_total') . ' ' . $rowcount . ' ' . $this->lang->line('records_imported_successfully');
                    if ($total_books_added > $rowcount) {
                        $message = $this->lang->line('records_found_in_csv_file_total') . ' ' . $rowcount . ' rows processed, ' . $total_books_added . ' books imported successfully';
                    }
                    
                    // Add warning if some books were skipped
                    if (!empty($books_skipped)) {
                        $skipped_list = implode(', ', array_slice($books_skipped, 0, 10)); // Show first 10
                        if (count($books_skipped) > 10) {
                            $skipped_list .= ' and ' . (count($books_skipped) - 10) . ' more';
                        }
                        $message .= '. ' . count($books_skipped) . ' book(s) skipped (duplicate book numbers): ' . $skipped_list;
                    }
                    
                    $array = array('status' => 'success', 'error' => '', 'message' => $message);
                }
            } else {
                $msg = array(
                    'e' => $this->lang->line('the_file_field_is_required'),
                );
                $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
            }
   
            // Prepare flash message
            $flash_msg = '<div class="alert alert-success text-center">' . $this->lang->line('total') . ' ' . $rowcount . " " . $this->lang->line('records_found_in_csv_file_total') . " " . $total_books_added . ' ' . $this->lang->line('records_imported_successfully');
            if (!empty($books_skipped)) {
                $flash_msg .= '<br><span class="text-warning">' . count($books_skipped) . ' book(s) skipped due to duplicate book numbers</span>';
            }
            $flash_msg .= '</div>';
            
            $this->session->set_flashdata('msg', $flash_msg);
            redirect('admin/book/import');
        }
    }




    public function import_new()
    {




        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
            $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            if ($ext == 'csv') {
                $file = $_FILES['file']['tmp_name'];
                $this->load->library('CSVReader');
                $result = $this->csvreader->parse_file($file);




                $rowcount = 0;
                if (!empty($result)) {
                    foreach ($result as $r_key => $r_value) {
                        $result[$r_key]['book_title']  = $this->encoding_lib->toUTF8($result[$r_key]['book_title']);
                        $result[$r_key]['book_no']     = $this->encoding_lib->toUTF8($result[$r_key]['book_no']);
                        $result[$r_key]['isbn_no']     = $this->encoding_lib->toUTF8($result[$r_key]['isbn_no']);
                        $result[$r_key]['subject']     = $this->encoding_lib->toUTF8($result[$r_key]['subject']);
                        $result[$r_key]['rack_no']     = $this->encoding_lib->toUTF8($result[$r_key]['rack_no']);
                        $result[$r_key]['publish']     = $this->encoding_lib->toUTF8($result[$r_key]['publish']);
                        $result[$r_key]['author']      = $this->encoding_lib->toUTF8($result[$r_key]['author']);
                        $result[$r_key]['edition']     = isset($result[$r_key]['edition']) ? $this->encoding_lib->toUTF8($result[$r_key]['edition']) : '';
                        $result[$r_key]['qty']         = $this->encoding_lib->toUTF8($result[$r_key]['qty']);
                        $result[$r_key]['perunitcost'] = convertCurrencyFormatToBaseAmount($this->encoding_lib->toUTF8($result[$r_key]['perunitcost']));
                        $result[$r_key]['postdate']    = $this->encoding_lib->toUTF8($result[$r_key]['postdate']);
                        $result[$r_key]['description'] = $this->encoding_lib->toUTF8($result[$r_key]['description']);
                        $rowcount++;
                    }




                    $this->db->insert_batch('books', $result);
                }
                $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('records_found_in_csv_file_total') . $rowcount . $this->lang->line('records_imported_successfully'));
            }
        } else {
            $msg = array(
                'e' => $this->lang->line('the_file_field_is_required'),
            );
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        }




        echo json_encode($array);
    }




    public function handle_csv_upload()
    {
        $error = "";
        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
            $allowedExts = array('csv');
            $mimes       = array('text/csv',
                'text/plain',
                'application/csv',
                'text/comma-separated-values',
                'application/excel',
                'application/vnd.ms-excel',
                'application/vnd.msexcel',
                'text/anytext',
                'application/octet-stream',
                'application/txt');
            $temp      = explode(".", $_FILES["file"]["name"]);
            $extension = end($temp);
            if ($_FILES["file"]["error"] > 0) {
                $error .= "Error opening the file<br />";
            }
            if (!in_array($_FILES['file']['type'], $mimes)) {
                $error .= "Error opening the file<br />";
                $this->form_validation->set_message('handle_csv_upload', $this->lang->line('file_type_not_allowed'));
                return false;
            }
            if (!in_array($extension, $allowedExts)) {
                $error .= "Error opening the file<br />";
                $this->form_validation->set_message('handle_csv_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }
            if ($error == "") {
                return true;
            }
        } else {
            $this->form_validation->set_message('handle_csv_upload', $this->lang->line('please_select_file'));
            return false;
        }
    }




    public function exportformat()
    {
        $this->load->helper('download');
        $filepath = "./backend/import/import_book_sample_file.csv";
        $data     = file_get_contents($filepath);
        $name     = 'import_book_sample_file.csv';
        force_download($name, $data);
    }




    public function issue_report()
    {
        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'Library/book/issue_report');
        $data['title']        = 'Add Teacher';
        $teacher_result       = $this->teacher_model->getLibraryTeacher();
        $data['teacherlist']  = $teacher_result;
        $genderList           = $this->customlib->getGender();
        $data['genderList']   = $genderList;
        $issued_books         = $this->bookissue_model->getissueMemberBooks();
        $data['issued_books'] = $issued_books;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/book/issuereport', $data);
        $this->load->view('layout/footer', $data);
    }




    public function issue_returnreport()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/library');
        $this->session->set_userdata('subsub_menu', 'Reports/library/issue_returnreport');
        $data['title']        = 'Add Teacher';
        $teacher_result       = $this->teacher_model->getLibraryTeacher();
        $data['searchlist']   = $this->customlib->get_searchtype();
        // $issued_books         = $this->bookissue_model->getissuereturnMemberBooks();
        // $data['issued_books'] = $issued_books;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/book/issue_returnreport', $data);
        $this->load->view('layout/footer', $data);
    }

    public function getbooklist()
    {
        $listbook        = $this->book_model->getbooklist();
        $m               = json_decode($listbook);

        // Keep datatable JSON untouched; filtering handled in model
        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        $dt_data         = array();
        if (!empty($m->data)) {
            foreach ($m->data as $key => $value) {
                $editbtn   = '';
                $deletebtn = '';




                if ($this->rbac->hasPrivilege('books', 'can_edit')) {
                    $editbtn = "<a href='" . base_url() . "admin/book/edit/" . $value->id . "'   class='btn btn-default btn-xs'  data-toggle='tooltip' title='" . $this->lang->line('edit') . "'><i class='fa fa-pencil'></i></a>";
                }




                if ($this->rbac->hasPrivilege('books', 'can_delete')) {
                    $deletebtn = "<a onclick='return confirm(" . '"' . $this->lang->line('delete_confirm') . '"' . "  )' href='" . base_url() . "admin/book/delete/" . $value->id . "' class='btn btn-default btn-xs' title='" . $this->lang->line('delete') . "' data-toggle='tooltip'><i class='fa fa-trash'></i></a>";
                }




                $row   = array();
                $row[] = $value->book_title;
                if ($value->description == "") {
                    $row[] = $this->lang->line('no_description');
                } else {
                    $row[] = $value->description;
                }
                $row[]     = $value->book_no;
                $row[]     = $value->isbn_no;
                $row[]     = $value->publish;
                $row[]     = $value->author;
                $row[]     = isset($value->edition) ? $value->edition : '';
                $row[]     = $value->subject;
                $row[]     = $value->rack_no;
                $row[]     = $value->qty;
                $row[]     = $value->qty - $value->total_issue;
                $row[]     = $currency_symbol . amountFormat($value->perunitcost);
                $row[]     = $this->customlib->dateformat($value->postdate);
                $row[]     = $editbtn . ' ' . $deletebtn;
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




    /* function to get book inventory report by using datatable */
    public function dtbookissuereturnreportlist()
    {
        /* search code start from here */
        $search_type = $this->input->post('search_type');
        $date_from   = $this->input->post('date_from');
        $date_to     = $this->input->post('date_to');
        if (isset($_POST['search_type']) && $_POST['search_type'] != '') {
            $dates               = $this->customlib->get_betweendate($_POST['search_type']);
            $data['search_type'] = $_POST['search_type'];
        } else {
            $dates               = $this->customlib->get_betweendate('this_year');
            $data['search_type'] = '';
        }
        $sch_setting = $this->sch_setting_detail;
        $start_date    = date('Y-m-d', strtotime($dates['from_date']));
        $end_date      = date('Y-m-d', strtotime($dates['to_date']));
        $data['label'] = date($this->customlib->getSchoolDateFormat(), strtotime($start_date)) . " " . $this->lang->line('to') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($end_date));




        /* search code ends here */
        $issued_books = $this->bookissue_model->getissuereturnMemberBooks(' ', $start_date, $end_date);




        $resultlist = json_decode($issued_books);
        $dt_data    = array();




        if (!empty($resultlist->data)) {




            $editbtn   = "";
            $deletebtn = "";




            $getStaffRole = $this->customlib->getStaffRole();
            $staffrole    = json_decode($getStaffRole);




            foreach ($resultlist->data as $resultlist_key => $value) {




                $row = array();




                $row[] = $value->book_title;
                $row[] = $value->book_no;
                $row[] = $this->customlib->formatLibraryDateBsForDisplay($value->issue_date);
                $row[] = $this->customlib->formatLibraryDateBsForDisplay($value->return_date);
                $row[] = $value->members_id;
                $row[] = $value->library_card_no;




               
                if ($value->admission) {
                    $admission = ' (' . $value->admission . ')';
                   
                } else {
                    $admission = '';
                   
                }




             
               
                $row[] = $this->customlib->getFullName($value->fname, $value->mname, $value->lname, $sch_setting->middlename, $sch_setting->lastname) . $admission;
               
               
                $row[] = $this->lang->line($value->member_type);




                $dt_data[] = $row;




            }




        }
        $json_data = array(
            "draw"            => intval($resultlist->draw),
            "recordsTotal"    => intval($resultlist->recordsTotal),
            "recordsFiltered" => intval($resultlist->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }




}










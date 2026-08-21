<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Book_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
    }

    /**
     * This funtion takes id as a parameter and will fetch the record.
     * If id is not provided, then it will fetch all the records form the table.
     * @param int $id
     * @return mixed
     */
    public function get($id = null)
    {
        $this->db->select()->from('books');
        if ($id != null) {
            $this->db->where('books.id', $id);
        } else {
            $this->db->order_by('books.id');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

    /**
     * Get total count of all books in the library
     * @return int
     */
    public function getTotalBooksCount()
    {
        $this->db->select('COUNT(*) as total');
        $this->db->from('books');
        $query = $this->db->get();
        $result = $query->row();
        return isset($result->total) ? (int)$result->total : 0;
    }

    public function getbooklist()
    {
        $this->datatables
            ->select('books.*,IFNULL(total_issue, "0") as `total_issue` ')
            // Keep these lists to real column names only; placeholder entries can break SQL search/order.
            ->searchable('books.book_title,books.description,books.book_no,books.isbn_no,books.publish,books.author,books.edition,books.subject,books.rack_no,books.qty,books.perunitcost,books.postdate')
            ->orderable('books.book_title,books.description,books.book_no,books.isbn_no,books.publish,books.author,books.edition,books.subject,books.rack_no,books.qty,books.perunitcost,books.postdate')
            ->join(" (SELECT COUNT(*) as `total_issue`, book_id from book_issues  where is_returned= 0  GROUP by book_id) as `book_count`", "books.id=book_count.book_id", "left")
            ->sort('books.id','desc')
            ->from('books');

        // Add an exact match filter for numeric global search terms on book_no
        $search = $this->input->post('search');
        if (is_array($search) && isset($search['value'])) {
            $keyword = trim($search['value']);
            if ($keyword !== '' && preg_match('/^\d+$/', $keyword)) {
                // `book_no` can contain leading zeros; treat it as a string.
                $this->datatables->where('books.book_no', $keyword);
            }
        }
        return $this->datatables->generate('json');
    }

    public function getBookwithQty()
    {
        $sql = "SELECT books.*,IFNULL(total_issue, '0') as `total_issue` FROM books LEFT JOIN (SELECT COUNT(*) as `total_issue`, book_id from book_issues  where is_returned= 0 GROUP by book_id) as `book_count` on books.id=book_count.book_id";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }

    /**
     * This function will delete the record based on the id
     * @param $id
     */
    public function remove($id)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where('id', $id);
        $this->db->delete('books');
        $this->db->where('book_id', $id);
        $this->db->delete('book_issues');
        $message   = DELETE_RECORD_CONSTANT . " On books id " . $id;
        $action    = "Delete";
        $record_id = $id;
        $this->log($message, $record_id, $action);
        //======================Code End==============================
        $this->db->trans_complete(); # Completing transaction
        /* Optional */
        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            //return $return_value;
        }
    }

    /**
     * This function will take the post data passed from the controller
     * If id is present, then it will do an update
     * else an insert. One function doing both add and edit.
     * @param $data
     */

     public function getAvailableQty($book_id, $book_no = null)
{
    // If book_no is provided, check for that specific book number
    if ($book_no !== null) {
        // Count how many times this specific book number is issued and not returned
        $this->db->select('COUNT(*) as issued_count');
        $this->db->from('book_issues');
        $this->db->join('books', 'books.id = book_issues.book_id');
        $this->db->where('book_issues.book_id', $book_id);
        $this->db->where('books.book_no', $book_no);
        $this->db->where('book_issues.is_returned', 0);
        $query = $this->db->get();
        $issued = $query->row()->issued_count;
        
        // For individual book numbers, available is either 0 or 1
        return (1 - $issued) >= 0 ? (1 - $issued) : 0;
    } else {
        // Original logic for books without specific book numbers
        $this->db->select('qty');
        $this->db->from('books');
        $this->db->where('id', $book_id);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $total_qty = $query->row()->qty;
            
            // Count issued books that are not returned
            $this->db->select('COUNT(*) as issued_count');
            $this->db->from('book_issues');
            $this->db->where('book_id', $book_id);
            $this->db->where('is_returned', 0);
            $query2 = $this->db->get();
            $issued = $query2->row()->issued_count;
            
            return ($total_qty - $issued) >= 0 ? ($total_qty - $issued) : 0;
        }
        
        return 0;
    }
    }

    /**
     * Check if a book number already exists
     * @param string $book_no
     * @param int $exclude_id Optional ID to exclude from check (for updates)
     * @return bool
     */
    public function bookNumberExists($book_no, $exclude_id = null)
    {
        $book_no = trim((string) $book_no);
        if ($book_no === '') {
            return false;
        }
        $this->db->select('id');
        $this->db->from('books');
        $this->db->where('book_no', $book_no);
        if ($exclude_id !== null) {
            $this->db->where('id !=', $exclude_id);
        }
        $query = $this->db->get();
        return $query->num_rows() > 0;
    }

    /**
     * Get book details by book number
     * @param string $book_no
     * @return array|null
     */
    public function getBookByNumber($book_no)
    {
        $this->db->select('*');
        $this->db->from('books');
        $this->db->where('book_no', $book_no);
        $this->db->limit(1);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->row_array();
        }
        return null;
    }

    public function addbooks($data)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        if (isset($data['id'])) {
            // Check for duplicate book number when updating (only if book_no is provided and not empty)
            if (isset($data['book_no']) && !empty($data['book_no']) && $this->bookNumberExists($data['book_no'], $data['id'])) {
                $this->db->trans_rollback();
                return false;
            }
            
            // Remove id from data array before update (don't update the id field)
            $book_id = $data['id'];
            unset($data['id']);
            
            // Remove only empty string so nullable columns (e.g. program_id, class_id) can be set to NULL on update
            $data = array_filter($data, function($value) {
                return $value !== '';
            });
            
            $this->db->where('id', $book_id);
            $update_result = $this->db->update('books', $data);
            
            if (!$update_result) {
                $this->db->trans_rollback();
                return false;
            }
            
            $message   = UPDATE_RECORD_CONSTANT . " On books id " . $book_id;
            $action    = "Update";
            $record_id = $book_id;
            $this->log($message, $record_id, $action);
            //======================Code End==============================

            $this->db->trans_complete(); # Completing transaction
            /* Optional */

            if ($this->db->trans_status() === false) {
                # Something went wrong.
                $this->db->trans_rollback();
                return false;
            } else {
                return $book_id; // Return the book ID on successful update
            }
        } else {
            // Check for duplicate book number before inserting
            if (isset($data['book_no']) && !empty($data['book_no']) && $this->bookNumberExists($data['book_no'])) {
                $this->db->trans_rollback();
                return false;
            }
            
            // Remove empty values to avoid database errors
            $data = array_filter($data, function($value) {
                return $value !== '' && $value !== null;
            });
            
            $this->db->insert('books', $data);
            $insert_id = $this->db->insert_id();
            
            if (!$insert_id) {
                // If insert failed, rollback and return false
                $this->db->trans_rollback();
                return false;
            }
            
            $message   = INSERT_RECORD_CONSTANT . " On books id " . $insert_id;
            $action    = "Insert";
            $record_id = $insert_id;
            $this->log($message, $record_id, $action);
            //======================Code End==============================

            $this->db->trans_complete(); # Completing transaction
            /* Optional */

            if ($this->db->trans_status() === false) {
                # Something went wrong.
                $this->db->trans_rollback();
                return false;
            } else {
                return $insert_id;
            }
        }
    }

    public function listbook()
    {
        $this->db->select()->from('books');
        $this->db->order_by("id", "desc");
        $listbook = $this->db->get();
        return $listbook->result_array();
    }

    public function check_Exits_group($data)
    {
        $this->db->select('*');
        $this->db->from('feemasters');
        $this->db->where('session_id', $this->current_session);
        $this->db->where('feetype_id', $data['feetype_id']);
        $this->db->where('class_id', $data['class_id']);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            return false;
        } else {
            return true;
        }
    }

    public function getTypeByFeecategory($type, $class_id)
    {
        $this->db->select('feemasters.id,feemasters.session_id,feemasters.amount,feemasters.description,classes.class,feetype.type')->from('feemasters');
        $this->db->join('classes', 'feemasters.class_id = classes.id');
        $this->db->join('feetype', 'feemasters.feetype_id = feetype.id');
        $this->db->where('feemasters.class_id', $class_id);
        $this->db->where('feemasters.feetype_id', $type);
        $this->db->where('feemasters.session_id', $this->current_session);
        $this->db->order_by('feemasters.id');
        $query = $this->db->get();
        return $query->row_array();
    }

    public function bookinventory($start_date, $end_date)
    {
        $condition = $this->_library_bs_date_sql("`books`.`postdate`", $start_date, $end_date);
        $condition .= $this->_library_book_academic_sql();
        $sql       = "SELECT books.*,IFNULL(total_issue, '0') as `total_issue` FROM books LEFT JOIN (SELECT COUNT(*) as `total_issue`, book_id from book_issues  where is_returned= 0  GROUP by book_id) as `book_count` on books.id=book_count.book_id where 0=0 " . $condition . " ";

        $this->datatables->query($sql)
            ->orderable('book_title,book_no,isbn_no,publish,author,subject,rack_no,qty,null,null,perunitcost,postdate')
            ->searchable('book_title,book_no,isbn_no,publish,author,subject,rack_no,qty,null,null,perunitcost,postdate')
            ->query_where_enable(true);
        return $this->datatables->generate('json');
    }

    /**
     * Compare VARCHAR library dates stored as BS YYYY-MM-DD.
     * Do not use MySQL DATE()/DATE_FORMAT — BS months can have day 31/32.
     */
    private function _library_bs_date_sql($column, $start_date, $end_date)
    {
        if ($start_date === null || $start_date === '' || $end_date === null || $end_date === '') {
            return '';
        }
        return " AND " . $column . " IS NOT NULL AND TRIM(" . $column . ") <> '' AND TRIM(" . $column . ") <> '0000-00-00'"
            . " AND LEFT(TRIM(" . $column . "), 10) BETWEEN " . $this->db->escape($start_date)
            . " AND " . $this->db->escape($end_date);
    }

    /**
     * Optional Faculty / Program / Class filters on books.* (all nullable).
     */
    private function _library_book_academic_sql()
    {
        $sql = '';
        $section_id = $this->input->post('section_id');
        $program_id = $this->input->post('program_id');
        $class_id   = $this->input->post('class_id');
        if ($section_id !== null && $section_id !== '' && $section_id !== '0') {
            $sql .= " AND books.section_id = " . (int) $section_id;
        }
        if ($program_id !== null && $program_id !== '' && $program_id !== '0') {
            $sql .= " AND books.program_id = " . (int) $program_id;
        }
        if ($class_id !== null && $class_id !== '' && $class_id !== '0') {
            $sql .= " AND books.class_id = " . (int) $class_id;
        }
        return $sql;
    }

    public function bookoverview($start_date, $end_date)
    {
        $condition = " and date_format(`books`.`postdate`,'%Y-%m-%d') between '" . $start_date . "' and '" . $end_date . "'";
        $sql       = "SELECT sum(books.qty) as qty,sum(IFNULL(total_issue, '0')) as `total_issue` FROM books LEFT JOIN (SELECT COUNT(*) as `total_issue`, book_id from book_issues  where is_returned= 0  GROUP by book_id) as `book_count` on books.id=book_count.book_id where 0=0 " . $condition . " ";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }

}

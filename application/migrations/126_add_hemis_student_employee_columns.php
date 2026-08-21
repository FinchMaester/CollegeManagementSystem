<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * HEMIS integration schema hardening (Student + Employee).
 *
 * Notes:
 * - This migration is additive and tries to be safe to run on existing campus DBs.
 * - It does not drop/rename existing columns (to avoid breaking legacy UI).
 */
class Migration_Add_hemis_student_employee_columns extends CI_Migration
{
    public function up()
    {
        $this->load->dbforge();

        // ---- Students -------------------------------------------------------
        if ($this->db->table_exists('students')) {
            // Dual date: store BS explicitly (your schema currently stores a mixed/ambiguous `dob` varchar).
            if (!$this->db->field_exists('dob_bs', 'students')) {
                $this->dbforge->add_column('students', array(
                    'dob_bs' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 20,
                        'null' => true,
                    ),
                ));
            }

            // Address: keep HEMIS combinedCode so you can deterministically map Province/District/LocalLevel.
            if (!$this->db->field_exists('ugc_p_combined_code', 'students')) {
                $this->dbforge->add_column('students', array(
                    'ugc_p_combined_code' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 20,
                        'null' => true,
                    ),
                ));
            }
            if (!$this->db->field_exists('ugc_t_combined_code', 'students')) {
                $this->dbforge->add_column('students', array(
                    'ugc_t_combined_code' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 20,
                        'null' => true,
                    ),
                ));
            }

            // Ensure UGC lookup ids exist (some installs add these lazily via Ugc_metadata_model).
            $ensureStudentIntCols = array('ugc_program_id', 'ugc_batch_id', 'ugc_fiscal_year_id');
            foreach ($ensureStudentIntCols as $col) {
                if (!$this->db->field_exists($col, 'students')) {
                    $this->dbforge->add_column('students', array(
                        $col => array(
                            'type' => 'INT',
                            'null' => true,
                        ),
                    ));
                }
            }

            // Sync metadata already exists on some DBs via application/sql/*.sql; keep idempotent.
            if (!$this->db->field_exists('hemis_student_id', 'students')) {
                $this->dbforge->add_column('students', array(
                    'hemis_student_id' => array(
                        'type' => 'INT',
                        'null' => true,
                    ),
                ));
            }
            if (!$this->db->field_exists('hemis_sync_at', 'students')) {
                $this->dbforge->add_column('students', array(
                    'hemis_sync_at' => array(
                        'type' => 'DATETIME',
                        'null' => true,
                    ),
                ));
            }
            if (!$this->db->field_exists('hemis_sync_error', 'students')) {
                $this->dbforge->add_column('students', array(
                    'hemis_sync_error' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 500,
                        'null' => true,
                    ),
                ));
            }
            if (!$this->db->field_exists('hemis_sync_error_at', 'students')) {
                $this->dbforge->add_column('students', array(
                    'hemis_sync_error_at' => array(
                        'type' => 'DATETIME',
                        'null' => true,
                    ),
                ));
            }
        }

        // ---- Staff / Employees ---------------------------------------------
        if ($this->db->table_exists('staff')) {
            // Add sync metadata for employee as well (student has it, staff doesn't).
            if (!$this->db->field_exists('hemis_employee_id', 'staff')) {
                $this->dbforge->add_column('staff', array(
                    'hemis_employee_id' => array(
                        'type' => 'INT',
                        'null' => true,
                    ),
                ));
            }
            if (!$this->db->field_exists('hemis_sync_at', 'staff')) {
                $this->dbforge->add_column('staff', array(
                    'hemis_sync_at' => array(
                        'type' => 'DATETIME',
                        'null' => true,
                    ),
                ));
            }
            if (!$this->db->field_exists('hemis_sync_error', 'staff')) {
                $this->dbforge->add_column('staff', array(
                    'hemis_sync_error' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 500,
                        'null' => true,
                    ),
                ));
            }
            if (!$this->db->field_exists('hemis_sync_error_at', 'staff')) {
                $this->dbforge->add_column('staff', array(
                    'hemis_sync_error_at' => array(
                        'type' => 'DATETIME',
                        'null' => true,
                    ),
                ));
            }

            // Ensure UGC lookup ids exist (some installs add lazily via Ugc_metadata_model).
            $ensureStaffIntCols = array('ugc_department_id', 'ugc_section_id');
            foreach ($ensureStaffIntCols as $col) {
                if (!$this->db->field_exists($col, 'staff')) {
                    $this->dbforge->add_column('staff', array(
                        $col => array(
                            'type' => 'INT',
                            'null' => true,
                        ),
                    ));
                }
            }

            // Employee document: digital signature is required by many campuses (parity with student).
            if (!$this->db->field_exists('digital_signature', 'staff')) {
                $this->dbforge->add_column('staff', array(
                    'digital_signature' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 255,
                        'null' => true,
                    ),
                ));
            }
        }
    }

    public function down()
    {
        // Intentionally no down migration (safe/append-only migration).
    }
}

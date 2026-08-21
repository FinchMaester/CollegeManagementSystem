<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Minimal local endpoint that mimics UGC HEMIS success JSON for integration tests.
 * Enable with $config['hemis']['mock_http_enabled'] = TRUE in application/config/hemis.php
 */
class Mockhemis extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('hemis_integration_model');
        $cfg = $this->hemis_integration_model->get_effective_config();
        if (empty($cfg['mock_http_enabled'])) {
            show_404();
        }
    }

    /**
     * POST multipart — returns a UGC-like payload with a fake central id.
     */
    public function student_create()
    {
        if (strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status' => 'error',
                'message' => 'Method not allowed',
            )));
            return;
        }

        $id = 800000000 + random_int(1, 999999);
        $payload = array(
            'status'  => 'Success',
            'message' => 'Student Registered Successfully (LOCAL MOCK).',
            'id'      => $id,
            'data'    => array(
                'id'        => $id,
                'firstName' => $this->input->post('firstName'),
                'lastName'  => $this->input->post('lastName'),
            ),
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($payload));
    }

    /**
     * PATCH multipart — mock central student update.
     */
    public function student_update($hemis_id = 0)
    {
        if (strtoupper($this->input->method(true)) !== 'PATCH' && strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status' => 'error',
                'message' => 'Use PATCH or POST for local mock',
            )));
            return;
        }

        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 'Success',
            'message' => 'Student record updated successfully (LOCAL MOCK).',
            'id'      => (int) $hemis_id,
        )));
    }

    /**
     * POST JSON — mock POST /api/StudentUpgrade.
     */
    public function student_upgrade()
    {
        if (strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            return;
        }

        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 'Success',
            'message' => 'Student upgrade recorded (LOCAL MOCK).',
        )));
    }

    /** POST multipart — mock UGC graduation application forward. */
    public function graduation_generate()
    {
        if (strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            return;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 'Success',
            'message' => 'Graduation application received (LOCAL MOCK).',
        )));
    }

    /** POST multipart — mock GenerateGraduationApplicationForStudent. */
    public function graduation_for_student()
    {
        if (strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            return;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 'Success',
            'message' => 'External graduation application received (LOCAL MOCK).',
        )));
    }

    /** PUT JSON — mock Graduation update (UGC often exposes PUT by student/record id). */
    public function graduation_put($id = 0)
    {
        if (strtoupper($this->input->method(true)) !== 'PUT' && strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            return;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 'Success',
            'message' => 'Graduation record updated (LOCAL MOCK).',
            'id'      => (int) $id,
        )));
    }

    /** POST JSON — mock DropOut create. */
    public function dropout_post()
    {
        if (strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            return;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 'Success',
            'message' => 'Dropout recorded (LOCAL MOCK).',
        )));
    }

    /** PUT JSON — mock DropOut update. */
    public function dropout_put($id = 0)
    {
        if (strtoupper($this->input->method(true)) !== 'PUT' && strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            return;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 'Success',
            'message' => 'Dropout updated (LOCAL MOCK).',
            'id'      => (int) $id,
        )));
    }

    /** POST JSON — mock Library create. */
    public function library_post()
    {
        if (strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            return;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 'Success',
            'message' => 'Library created (LOCAL MOCK).',
        )));
    }

    /** PUT JSON — mock Library update. */
    public function library_put($id = 0)
    {
        if (strtoupper($this->input->method(true)) !== 'PUT' && strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            return;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 'Success',
            'message' => 'Library updated (LOCAL MOCK).',
            'id'      => (int) $id,
        )));
    }

    /** POST JSON — mock Employee create. */
    public function employee_post()
    {
        if (strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            return;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 'Success',
            'message' => 'Employee created (LOCAL MOCK).',
        )));
    }

    /** PATCH JSON — mock Employee update. */
    public function employee_patch($id = 0)
    {
        if (strtoupper($this->input->method(true)) !== 'PATCH' && strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            return;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 'Success',
            'message' => 'Employee updated (LOCAL MOCK).',
            'id'      => (int) $id,
        )));
    }

    /** POST JSON — mock Labs create. */
    public function labs_post()
    {
        if (strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            return;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 'Success',
            'message' => 'Lab created (LOCAL MOCK).',
        )));
    }

    /** PUT JSON — mock Labs update. */
    public function labs_put($id = 0)
    {
        if (strtoupper($this->input->method(true)) !== 'PUT' && strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            return;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 'Success',
            'message' => 'Lab updated (LOCAL MOCK).',
            'id'      => (int) $id,
        )));
    }

    /** GET — mock GET /api/ProgramMgmt/GetCollegePrograms (PDF §5.4). */
    public function programmgmt_get()
    {
        if (strtoupper($this->input->method(true)) !== 'GET') {
            $this->output->set_status_header(405);
            return;
        }
        $sample = array(
            array(
                'id'           => 30,
                'universityId' => 1,
                'campusId'     => null,
                'programId'    => 30,
                'programName'  => 'Master in Business Management (LOCAL MOCK)',
                'shortName'    => 'MBM',
                'code'         => '11',
                'duration'     => '2',
                'programType'  => 'annual',
                'status'       => true,
            ),
        );
        $this->output->set_content_type('application/json')->set_output(json_encode($sample));
    }

    public function buildings_post()
    {
        if (strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            return;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 'Success',
            'message' => 'Building created (LOCAL MOCK).',
        )));
    }

    public function buildings_put($id = 0)
    {
        if (strtoupper($this->input->method(true)) !== 'PUT' && strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            return;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 'Success',
            'message' => 'Building updated (LOCAL MOCK).',
            'id'      => (int) $id,
        )));
    }

    public function fev_post()
    {
        if (strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            return;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 'Success',
            'message' => 'FEV item created (LOCAL MOCK).',
        )));
    }

    public function fev_put($id = 0)
    {
        if (strtoupper($this->input->method(true)) !== 'PUT' && strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            return;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 'Success',
            'message' => 'FEV item updated (LOCAL MOCK).',
            'id'      => (int) $id,
        )));
    }

    public function hostels_post()
    {
        if (strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            return;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 'Success',
            'message' => 'Hostel created (LOCAL MOCK).',
        )));
    }

    public function hostels_put($id = 0)
    {
        if (strtoupper($this->input->method(true)) !== 'PUT' && strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            return;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 'Success',
            'message' => 'Hostel updated (LOCAL MOCK).',
            'id'      => (int) $id,
        )));
    }

    public function lands_post()
    {
        if (strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            return;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 'Success',
            'message' => 'Land created (LOCAL MOCK).',
        )));
    }

    public function lands_put($id = 0)
    {
        if (strtoupper($this->input->method(true)) !== 'PUT' && strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            return;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 'Success',
            'message' => 'Land updated (LOCAL MOCK).',
            'id'      => (int) $id,
        )));
    }

    public function management_add_department()
    {
        if (strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            return;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 'Success',
            'message' => 'Department added (LOCAL MOCK).',
        )));
    }

    public function management_put_department($id = 0)
    {
        if (strtoupper($this->input->method(true)) !== 'PUT' && strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            return;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 'Success',
            'message' => 'Department updated (LOCAL MOCK).',
            'id'      => (int) $id,
        )));
    }

    public function management_add_section()
    {
        if (strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            return;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 'Success',
            'message' => 'Section added (LOCAL MOCK).',
        )));
    }

    public function management_put_section($id = 0)
    {
        if (strtoupper($this->input->method(true)) !== 'PUT' && strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            return;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 'Success',
            'message' => 'Section updated (LOCAL MOCK).',
            'id'      => (int) $id,
        )));
    }

    public function batch_get()
    {
        if (strtoupper($this->input->method(true)) !== 'GET') {
            $this->output->set_status_header(405);
            return;
        }
        $sample = array(
            array('id' => 21, 'name' => '2035', 'batchNepali' => '2090', 'index' => -4, 'isActive' => true),
        );
        $this->output->set_content_type('application/json')->set_output(json_encode($sample));
    }

    public function fiscalyear_get()
    {
        if (strtoupper($this->input->method(true)) !== 'GET') {
            $this->output->set_status_header(405);
            return;
        }
        $sample = array(
            array('id' => 19, 'yearNepali' => '2084/085', 'yearEnglish' => '2026/027', 'activeFiscalYear' => false, 'index' => 0),
        );
        $this->output->set_content_type('application/json')->set_output(json_encode($sample));
    }

    public function address_getall()
    {
        if (strtoupper($this->input->method(true)) !== 'GET') {
            $this->output->set_status_header(405);
            return;
        }
        $sample = array(
            array(
                'id'               => 1,
                'combinedCode'     => '10101',
                'provinceCode'     => '1',
                'province'         => 'Koshi',
                'provinceNepali'   => '',
                'districtCode'     => '01',
                'district'         => 'Taplejung',
                'districtNepali'   => '',
                'localLevelCode'   => '01',
                'localLevel'       => 'Phaktanlung Rural Municipality',
                'localLevelNepali' => '',
                'ecologicalBelt'   => 'Mountain',
                'noOfWard'         => 7,
            ),
        );
        $this->output->set_content_type('application/json')->set_output(json_encode($sample));
    }

    public function employeepositions_get($id = null)
    {
        if (strtoupper($this->input->method(true)) !== 'GET') {
            $this->output->set_status_header(405);
            return;
        }
        if ($id !== null && $id !== '' && (string) $id !== '0') {
            $one = array(
                'id'       => (int) $id,
                'category' => 'Nonteaching',
                'type'     => 'Administrative',
                'postName' => 'Ass. Accountant',
                'index'    => 1,
                'status'   => true,
                'remarks'  => '',
            );
            $this->output->set_content_type('application/json')->set_output(json_encode($one));
            return;
        }
        $list = array(
            array(
                'id'       => 1,
                'category' => 'Nonteaching',
                'type'     => 'Administrative',
                'postName' => 'Ass. Accountant',
                'index'    => 1,
                'status'   => true,
                'remarks'  => '',
            ),
        );
        $this->output->set_content_type('application/json')->set_output(json_encode($list));
    }
}

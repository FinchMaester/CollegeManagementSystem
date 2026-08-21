<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

/**
 * Department + Section (PDF §5.6). Outbound paths use Managent typo for Section endpoints per PDF.
 */
class Management extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('hemis_rest_auth');
        $this->hemis_rest_auth->guard_controller($this);
        $this->load->model('management_department_model');
        $this->load->model('management_section_model');
        $this->load->library('hemis_client');
    }

    public function department_list_get()
    {
        return $this->response($this->management_department_model->get_all(), self::HTTP_OK);
    }

    public function add_department_post()
    {
        if (!$this->post('departmentName') && $this->post('departmentName') !== '0') {
            return $this->response(array(
                'status'  => false,
                'message' => 'departmentName is required',
            ), self::HTTP_BAD_REQUEST);
        }
        $data = array(
            'departmentName' => $this->post('departmentName'),
            'status'         => $this->post('status'),
            'remarks'        => $this->post('remarks'),
        );
        $newId = $this->management_department_model->create($data);
        if (!$newId) {
            return $this->response(array(
                'status'  => false,
                'message' => 'Failed to create department',
            ), self::HTTP_INTERNAL_SERVER_ERROR);
        }
        $row = $this->management_department_model->get($newId);
        $payload = array(
            'departmentName' => $row['departmentName'],
            'status'         => $row['status'],
            'remarks'        => $row['remarks'],
        );
        $hemisSync = $this->hemis_client->sync_management_add_department($payload);
        return $this->response(array_merge($row, array('hemisSync' => $hemisSync)), self::HTTP_CREATED);
    }

    public function update_department_put($id = null)
    {
        if ($id === null || $id === '') {
            return $this->response(array(
                'status'  => false,
                'message' => 'Department id is required',
            ), self::HTTP_BAD_REQUEST);
        }
        if (!$this->management_department_model->get($id)) {
            return $this->response(array(
                'status'  => false,
                'message' => 'Department not found',
            ), self::HTTP_NOT_FOUND);
        }
        $patch = array();
        foreach (array('departmentName', 'status', 'remarks') as $f) {
            $v = $this->put($f);
            if ($v !== null) {
                $patch[$f] = $v;
            }
        }
        if (empty($patch)) {
            return $this->response(array(
                'status'  => false,
                'message' => 'No data provided for update',
            ), self::HTTP_BAD_REQUEST);
        }
        if (!$this->management_department_model->update($id, $patch)) {
            return $this->response(array(
                'status'  => false,
                'message' => 'Update failed',
            ), self::HTTP_INTERNAL_SERVER_ERROR);
        }
        $row = $this->management_department_model->get($id);
        $syncBody = array(
            'id'             => (int) $row['id'],
            'departmentName' => $row['departmentName'],
            'status'         => $row['status'],
            'remarks'        => $row['remarks'],
        );
        $hemisSync = $this->hemis_client->sync_management_update_department((int) $id, $syncBody);
        return $this->response(array_merge($row, array('hemisSync' => $hemisSync)), self::HTTP_OK);
    }

    public function section_list_get()
    {
        return $this->response($this->management_section_model->get_all(), self::HTTP_OK);
    }

    public function add_section_post()
    {
        $name = $this->post('sectionName');
        if ($name === null && $this->post('SectionName') !== null) {
            $name = $this->post('SectionName');
        }
        if (!$name && $name !== '0') {
            return $this->response(array(
                'status'  => false,
                'message' => 'sectionName is required',
            ), self::HTTP_BAD_REQUEST);
        }
        $data = array(
            'sectionName' => $name,
            'status'      => $this->post('status'),
            'remarks'     => $this->post('remarks'),
        );
        $newId = $this->management_section_model->create($data);
        if (!$newId) {
            return $this->response(array(
                'status'  => false,
                'message' => 'Failed to create section',
            ), self::HTTP_INTERNAL_SERVER_ERROR);
        }
        $row = $this->management_section_model->get($newId);
        $payload = array(
            'sectionName' => $row['sectionName'],
            'status'      => $row['status'],
            'remarks'     => $row['remarks'],
        );
        $hemisSync = $this->hemis_client->sync_management_add_section($payload);
        return $this->response(array_merge($row, array('hemisSync' => $hemisSync)), self::HTTP_CREATED);
    }

    public function update_section_put($id = null)
    {
        if ($id === null || $id === '') {
            return $this->response(array(
                'status'  => false,
                'message' => 'Section id is required',
            ), self::HTTP_BAD_REQUEST);
        }
        if (!$this->management_section_model->get($id)) {
            return $this->response(array(
                'status'  => false,
                'message' => 'Section not found',
            ), self::HTTP_NOT_FOUND);
        }
        $patch = array();
        foreach (array('sectionName', 'status', 'remarks') as $f) {
            $v = $this->put($f);
            if ($v !== null) {
                $patch[$f] = $v;
            }
        }
        if (empty($patch)) {
            return $this->response(array(
                'status'  => false,
                'message' => 'No data provided for update',
            ), self::HTTP_BAD_REQUEST);
        }
        if (!$this->management_section_model->update($id, $patch)) {
            return $this->response(array(
                'status'  => false,
                'message' => 'Update failed',
            ), self::HTTP_INTERNAL_SERVER_ERROR);
        }
        $row = $this->management_section_model->get($id);
        $syncBody = array(
            'id'          => (int) $row['id'],
            'sectionName' => $row['sectionName'],
            'status'      => $row['status'],
            'remarks'     => $row['remarks'],
        );
        $hemisSync = $this->hemis_client->sync_management_update_section((int) $id, $syncBody);
        return $this->response(array_merge($row, array('hemisSync' => $hemisSync)), self::HTTP_OK);
    }
}

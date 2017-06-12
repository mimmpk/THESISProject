<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Database Schema Version Management
* Create Date: 2017-06-11
*/
class VersionManagement_Schema extends CI_Controller{
	
	function __construct(){
		parent::__construct();

		$this->load->model('Project_model', 'mProj');
		$this->load->model('DatabaseSchema_model', 'mDB');
		$this->load->model('VersionManagement_model', 'mVerMng');

		$this->load->library('form_validation', null, 'FValidate');
	}

	public function index(){
		$data['projectCombo'] = $this->mProj->searchStartProjectCombobox();

		$data['projectId'] = '';
		$data['tableName'] = '';
		$data['columnName'] = '';
		$data['schemaVersionId'] = '';

		$data['resultList'] = null;
		$this->openView($data);
	}

	private function openView($data){
		$data['html']  = 'VersionManagement/databaseSchemaVersionSearch_view';
		
		$data['active_title'] = 'versionManagement';
		$data['active_page'] = 'trns005';
		
		$this->load->view('template/header');
		$this->load->view('VersionManagement/bodyDatabaseSchemaVersion_view', $data);
		$this->load->view('template/footer');
	}
}
?>
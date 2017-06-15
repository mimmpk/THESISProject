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

	public function getRelatedTableName(){
		$output = '';
		if(!empty($_POST)){
			$projectId = $this->input->post('projectId');

			$criteria = (object) array('projectId' => $projectId);
			$tableList = $this->mVerMng->searchRelatedTableName($criteria);

			$output .= "<option value=''>".PLEASE_SELECT."</option>";
			foreach($tableList as $value){
				$output .= "<option value='".$value['tableName']."'>".$value['tableName']."</option>";
			}
		}
		echo $output;
	}

	public function getRelatedColumnName(){
		$output = '';
		if(!empty($_POST)){
			$projectId = $this->input->post('projectId');
			$tableName = $this->input->post('tableName');

			$criteria = (object) array('projectId' => $projectId, 'tableName' => $tableName);
			$columnList = $this->mVerMng->searchRelatedColumnName($criteria);

			$output .= "<option value=''>".PLEASE_SELECT."</option>";
			foreach($columnList as $value){
				$output .= "<option value='".$value['columnName']."'>".$value['columnName']."</option>";
			}
		}
		echo $output;
	}

	public function getRelatedColumnVersion(){
		$output = '';
		if(!empty($_POST)){
			$projectId = $this->input->post('projectId');
			$tableName = $this->input->post('tableName');
			$columnName = $this->input->post('columnName');
			
			$criteria = (object) array(
				'projectId'  => $projectId, 
				'tableName'  => $tableName,
				'columnName' => $columnName);

			$versionList = $this->mVerMng->searchRelatedColumnVersion($criteria);

			$output .= "<option value=''>".PLEASE_SELECT."</option>";
			foreach($versionList as $value){
				$output .= "<option value='".$value['schemaVersionId']."'>"."Version ".$value['schemaVersionNumber']."</option>";
			}
		}
		echo $output;
	}

	public function search(){
		$resultList = array();

		$projectId = $this->input->post('inputProjectName');
		$tableName = $this->input->post('inputTable');
		$columnName = $this->input->post('inputColumn');
		$schemaVersionId = $this->input->post('inputVersion');

		$this->FValidate->set_rules('inputProjectName', null, 'required');
		$this->FValidate->set_rules('inputTable', null, 'required');
		if($this->FValidate->run()){
			$criteria = (object) array(
				'projectId' => $projectId, 'tableName' => $tableName,
				'columnName' => $columnName, 'schemaVersionId' => $schemaVersionId);
			$resultList = $this->mVerMng->searchDatabaseSchemaDetailByVersion($criteria);
		}

		$data['projectId'] = $projectId;
		$data['tableName'] = $tableName;
		$data['columnName'] = $columnName;
		$data['schemaVersionId'] = $schemaVersionId;

		$this->initialComboBox($projectId, $tableName, $columnName, $data);
		$data['resultList'] = $resultList;
		$this->openView($data);
	}

	public function reset(){
		$this->index();
	}

	private function initialComboBox($projectId, $tableName, $columnName, &$data){
		$data['projectCombo'] = $this->mProj->searchStartProjectCombobox();
		if(null != $projectId && !empty($projectId)){
			$criteria = (object) array('projectId' => $projectId);
			$tableList = $this->mVerMng->searchRelatedTableName($criteria);
			$data['tableCombo'] = $tableList;
		}

		if(null != $tableName && !empty($tableName)){
			$criteria = (object) array('projectId' => $projectId, 'tableName' => $tableName);
			$columnList = $this->mVerMng->searchRelatedColumnName($criteria);
			$data['columnCombo'] = $columnList;
		}

		if(null != $columnName && !empty($columnName)){
			$criteria = (object) array('projectId'  => $projectId, 'tableName'  => $tableName, 'columnName' => $columnName);
			$versionList = $this->mVerMng->searchRelatedColumnVersion($criteria);
			$data['schemaVersionCombo'] = $versionList;
		}
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
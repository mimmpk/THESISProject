<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

/**
* Database Schema Controller
*/
class DatabaseSchema extends CI_Controller{
	
	function __construct(){
		parent::__construct();
		$this->load->library('form_validation', null, 'FValidate');
		$this->load->library('session');
		$this->load->model('Project_model', 'mProject');
		$this->load->model('DatabaseSchema_model', 'mDbSchema');
	}

	public function index(){
		$formObj = (object) array('projectId' => '', 'dbSchemaStatus' => '');
		$data['error_message'] = '';
		$data['resultList'] = null;
		$this->openView($data, 'search');
	}

	public function reset(){
		$this->index();
	}

	public function search(){
		$error_message = '';
		$resultList = null;

		$projectId = trim($this->input->post('inputProjectName'));
		$status = trim($this->input->post('inputStatus'));

		$this->FValidate->set_rules('inputProjectName', null, 'required');
		if($this->FValidate->run()){
			$resultList = $this->mDbSchema->searchDatabaseSchemaByCriteria($projectId, $status);

			$data['selectedProjectId'] = $projectId;
			$data['searchFlag'] = 'Y';
		}

		$formObj = (object) array('projectId' => $projectId, 'dbSchemaStatus' => $status);
		$data['formData'] = $formObj;
		$data['error_message'] = $error_message;
		$data['resultList'] = $resultList;
		$this->openView($data, 'search');
	}

	public function addMore($projectId = ''){
		$screenMode = '1';  //normal screen
		$errorMessage = '';
		$projectName = '';
		$projectNameAlias = '';

		if(!empty($projectId)){
			//search project information
			$result = $this->mProject->searchProjectDetail($projectId);
			if(!empty($result)){
				$projectName = $result->projectName;
				$projectNameAlias = $result->projectNameAlias;
			}else{
				$screenMode = '0';
				$errorMessage = ER_MSG_007;
			}
		}else{
			$screenMode = '0'; //error screen
			$projectId = '';
			$errorMessage = ER_MSG_007;
		}

		$hfield = array('screenMode' => $screenMode, 'projectId' => $projectId, 'projectName' => $projectName, 'projectNameAlias' => $projectNameAlias);
		$data['uploadResult'] = null;
		$data['hfield'] = $hfield;
		$data['error_message'] = $errorMessage;
		$this->openView($data, 'upload');
	}

	public function doUpload(){
		$fileName = "DB_".date("YmdHis")."_".$this->session->session_id;
		$config['upload_path'] = './uploads/';
	    $config['allowed_types'] = 'csv';
	    $config['file_name'] = $fileName;
	    $config['max_size']  = '5000';

	    $errorMessage = '';
	    $successMessage = '';
	    $uploadResult = array();

	    $projectId = $this->input->post('projectId');
	    $projectName = $this->input->post('projectName');
		$projectNameAlias = $this->input->post('projectNameAlias');
		$screenMode = $this->input->post('screenMode');

		$this->load->library('upload', $config);

		if($this->upload->do_upload('fileName') == FALSE){
			$errorMessage = "Import process failed.";
		}else{
			$data = array('upload_data' => $this->upload->data());
			$fullPath = $data['upload_data']['full_path'];
	    	$this->load->library('csvreader');
       		$result =  $this->csvreader->parse_file($fullPath);//path to csv file

       		//Validate data in File
       		
       		$totalRecord = count($result);
       		$databaseSchemaList = array();
       		$correctRecord = 0;
       		$incorrectRecord = 0;

       		$user = (null != $this->session->userdata('username'))? $this->session->userdata('username'): 'userDefault';
       		
       		

		}
	}

	private function ValidateCSVFile($data, &$uploadResult, &$incorrectRecord){
		$lineNo = 0;
		$checkTableName = '';
		$checkColumnName = '';

		foreach($data as $value){
   			++$lineNo;
   			
   			$hasError = FALSE;	
   			$hasDataLength = FALSE;
   			$hasScalePoint = FALSE;

   			$scalePoint = 0;

   			if(NUMBER_OF_UPLOADED_COLUMN_DB != count($value)){
   				$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_004', $lineNo);
   				continue;
   			}

   			/**************************[TABLE NAME]**************************/
   			if($this->checkNullOrEmpty($value[KEY_DB_TABLE_NAME])){
   				$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_026', $lineNo);
   				$hasError = TRUE;
   			}else{
   				//Check length
   				if(MAX_TABLE_NAME < $value[KEY_DB_TABLE_NAME]){
   					$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_027', $lineNo);
   					$hasError = TRUE;
   				}

   				//Check whether be the same table name
   				if(!empty($checkTableName) && $checkTableName != $value[KEY_DB_TABLE_NAME]){
   					$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_031', $lineNo);
   					$hasError = TRUE;
   				}else{
   					$checkTableName = $value[KEY_DB_TABLE_NAME];
   				}
   			}

   			/**************************[COLUMN NAME]**************************/
   			if($this->checkNullOrEmpty($value[KEY_DB_COLUMN_NAME])){
   				$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_028', $lineNo);
   				$hasError = TRUE;
   			}else{
   				//Check length
   				if(MAX_FIELD_NAME < $value[KEY_DB_COLUMN_NAME]){
   					$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_029', $lineNo);
   					$hasError = TRUE;
   				}

   				//Check duplicate column name
   				if(!empty($checkColumnName) && $checkColumnName == $value[KEY_DB_COLUMN_NAME]){
   					$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_032', $lineNo);
   					$hasError = TRUE;
   				}else{
   					$checkColumnName = $value[KEY_DB_COLUMN_NAME];
   				}
   			}

   			/**************************[PRIMARY KEY]**************************/
   		}
	}

	private function openView($data, $page){
		if('search' == $page){
			$data['html'] = 'DatabaseSchemaManagement/databaseSchemaSearch_view';
			$data['projectCombo'] = $this->mProject->searchActiveProjectCombobox();
		}else{
			$data['html'] = 'DatabaseSchemaManagement/databaseSchemaUpload_view';
		}
		$data['active_title'] = 'master';
		$data['active_page'] = 'mats005';
		$this->load->view('template/header');
		$this->load->view('template/body', $data);
		$this->load->view('template/footer');
	}

	private function checkNullOrEmpty($varInput){
		return (!isset($varInput) || empty($varInput));
	}

	/*
	$key = errorCode;
	$value = lineNo;

	map[string,mixed] 	-- The key is undefined
	$key(string) 		-- The key is defined, but isn't yet set to an array
	$value(string) 		-- The key is defined, and the element is an array.
	*/
	private function appendThings($array, $key, $value) {
		if(empty($array[$key]) && !isset($array[$key])){
			$array[$key] = array(0 => $value);
		}else{ //(is_array($array[$key]))
			if(array_key_exists($key, $array)){
				$array[$key][] = $value;
			}
		}
		return $array;
	}
}
?>
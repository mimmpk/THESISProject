<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class FunctionalRequirement extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->model('Project_model', 'Project');
		$this->load->model('FunctionalRequirement_model', 'FR');
		$this->load->library('form_validation', null, 'FValidate');
		$this->load->library('session');
	}

	public function index(){
		$formObj = (object) array('projectName' => '', 'fnStatus' => '');
		$data['error_message'] = '';
		$data['searchFlag'] = '';
		$data['formData'] = $formObj;
		$data['result'] = null;
		$this->openView($data, 'search');
	}

	public function search(){
		$error_message = '';
		$result = null;
		$projectId = trim($this->input->post('inputProjectName'));
		$status = trim($this->input->post('inputStatus'));

		//var_dump("project_id=".$projectId." status=".$status);
		$this->FValidate->set_rules('inputProjectName', null, 'required');
		if($this->FValidate->run()){
			$param = (object) array('projectId' => $projectId, 'status' => $status);
			$result = $this->FR->searchFunctionalRequirementHeaderInfo($param);
			
			$data['selectedProjectId'] = $projectId;
			$data['searchFlag'] = 'Y';
		}

		$formObj = (object) array('projectName' => $projectId, 'fnStatus' => $status);
		$data['formData'] = $formObj;
		$data['error_message'] = $error_message;
		$data['result'] = $result;
		$this->openView($data, 'search');
	}

	public function addMore($projectId = ''){
		$screenMode = '1';  
		$errorMessage = '';
		$projectName = '';
		$projectNameAlias = '';

		if(!empty($projectId)){
			//search project information
			$result = $this->Project->searchProjectDetail($projectId);
			if(!empty($result)){
				$projectName = $result->projectName;
				$projectNameAlias = $result->projectNameAlias;
			}else{
				$errorMessage = ER_MSG_007;
			}
		}else{
			$screenMode = '0'; 
			$projectId = '';
			$errorMessage = ER_MSG_007;
		}

		$hfield = array('screenMode' => $screenMode, 'projectId' => $projectId, 'projectName' => $projectName, 'projectNameAlias' => $projectNameAlias);
		$data['result'] = null;
		$data['hfield'] = $hfield;
		$data['error_message'] = $errorMessage;
		$this->openView($data, 'upload');
	}

	public function doUpload(){
		//$fileName = $_FILES['fileName']['name']."_".$this->session->session_id;
	
		$fileName = "FN_".date("YmdHis")."_".$this->session->session_id;
	  	$config['upload_path'] = './uploads/';
	    $config['allowed_types'] = 'csv';
	    $config['file_name'] = $fileName;
	    $config['max_size']  = '5000';
	    
	    $error = '';
	    $resultUpload = array();

	    $projectId = $this->input->post('projectId');
	    $projectName = $this->input->post('projectName');
		$projectNameAlias = $this->input->post('projectNameAlias');
		$screenMode = $this->input->post('screenMode');

	    $this->load->library('upload', $config);

	    if($this->upload->do_upload('fileName') == FALSE){
	    	$error = "Import process failed.";
	    }else{
	    	$data = array('upload_data' => $this->upload->data());
	    	$this->load->library('csvreader');
       		$result =  $this->csvreader->parse_file($data['upload_data']['full_path']);//path to csv file
       	
       		//Validate data in File
       		$lineNo = 0;
       		$totalRecord = count($result);
       		$correctRecord = 0;
       		$incorrectRecord = 0;
       		
       		$dataHeader = '';
       		$inputData = '';
       		$inputNameisNULL = FALSE;
       		$tableNameisNULL = FALSE;
       		$fieldNameisNULL = FALSE;

			
       		foreach($result as $value){
       			++$lineNo;
       			$errorFlag = FALSE;	

       			if(13 != count($value)){
       				$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_004', $lineNo);
       				continue;
       			}

				/**************************[FUNCTIONAL REQUIREMENT ID]**************************/
				if(checkNullOrEmpty($value[KEY_FR_NO])){
					//Check FR No. not null
					$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_005', $lineNo);
				}else{
					//Check same Functional Requirement ID
					if(!empty($dataHeader) && $dataHeader != $value[KEY_FR_NO]){
	       				$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_001', $lineNo);
	       				$errorFlag = TRUE;
	       			}else{
	       				$dataHeader = $value[KEY_FR_NO];
	       			}
				}

       			//Check length Functional Requirement ID
       			if(LENGTH_FR_NO < strlen($value[KEY_FR_NO])){
       				$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_002', $lineNo);
       				$errorFlag = TRUE;
       			}

       			//Check Exist Functional Requirement ID
       			if(!checkNullOrEmpty($value[KEY_FR_NO])){
       				$recordCount = $this->FR->searchExistFunctionalRequirement($value[KEY_FR_NO], $projectId);
       				if(0 < $recordCount){
       					$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_006', $lineNo);
       					$errorFlag = TRUE;
       				}
       			}

       			/**************************[FUNCTIONAL REQUIREMENT DESCRIPTION]*************************/
       			//Check FRs'Description not null
       			if(checkNullOrEmpty($value[KEY_FR_DESC])){
       				$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_007', $lineNo);
       				$errorFlag = TRUE;
       			}else{
       				//Check Length of FRs'Description
	       			if(LENGTH_FR_DESC < strlen($value[KEY_FR_DESC])){
	       				$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_002', $lineNo);
	       				$errorFlag = TRUE;
	       			}
       			}

       			/**************************[INPUT NAME]*************************/
       			//Check Input Name not null
       			if(checkNullOrEmpty($value[KEY_FR_INPUT_NAME])){
       				$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_008', $lineNo);
       				$errorFlag = TRUE;
       				$inputNameisNULL = TRUE;
       			}else{
       				//Check length of Input Name
	       			if(LENGTH_INPUT_NAME < strlen($value[KEY_FR_INPUT_NAME])){
	       				$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_009', $lineNo);
	       				$errorFlag = TRUE;
	       			}
       			}

       			/**************************[INPUT DATA TYPE]*************************/ 
       			//Check Input Data Type not null
       			if(checkNullOrEmpty($value[KEY_FR_INPUT_TYPE])){
       				$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_011', $lineNo);
       				$errorFlag = TRUE;
       			}else{
       				//Check Length of Input Data Type
       				if(LENGTH_INPUT_DATA_TYPE < strlen($value[KEY_FR_INPUT_TYPE])){
       					$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_012', $lineNo);
       					$errorFlag = TRUE;
       				}

       				//Check format
       				
       			}













       			//Check unique Input Name with Table and Field of Target Database
       			if(!$inputNameisNULL && !$tableNameisNULL && !$fieldNameisNULL){
       				//Check unique in CSV File
       				$detailKey = $value[KEY_FR_INPUT_NAME]."_".$value[KEY_FR_INPUT_TABLE_NAME]."_".$value[KEY_FR_INPUT_FIELD_NAME];
       				if(!empty($inputData) && $inputData != $detailKey){
	       				$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_010', $lineNo);
	       				$errorFlag = TRUE;
	       			}else{
	       				$dataHeader = $detailKey;
	       			}

	       			//Check Exist in Database
       			}
				


       			




       			
       			
       			if($errorFlag == FALSE){
       				$correctRecord++;
       			}
       		}
       		$incorrectRecord = $totalRecord - $correctRecord;
	    }
	    
	    //var_dump($resultUpload);
	    $hfield = array('screenMode' => $screenMode, 'projectId' => $projectId, 'projectName' => $projectName, 'projectNameAlias' => $projectNameAlias);
	    $data['hfield'] = $hfield;
	    $data['totalRecords'] = (string)$totalRecord;
	    $data['correctRecords'] = $correctRecord;
		$data['incorrectRecords'] = $incorrectRecord;
	    $data['error_message'] = $error;
	    $data['result'] = $resultUpload;
	    $this->openView($data, 'upload');
	}

	private function openView($data, $page){
		if('search' == $page){
			$data['html'] = 'FunctionalRequirementManagement/RequirementSearch_View';
			$data['projectCombo'] = $this->Project->searchActiveProjectCombobox();
		}else{
			$data['html'] = 'FunctionalRequirementManagement/RequirementUpload_View';
		}
		$data['active_title'] = 'master';
		$data['active_page'] = 'mats002';
		$this->load->view('template/header');
		$this->load->view('template/body', $data);
		$this->load->view('template/footer');
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

	private function checkNullOrEmpty($varInput){
		return (!isset($varInput) || empty($varInput));
	}
}
?>
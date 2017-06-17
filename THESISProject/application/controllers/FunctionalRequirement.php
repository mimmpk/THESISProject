<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class FunctionalRequirement extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->model('Project_model', 'Project');
		$this->load->model('FunctionalRequirement_model', 'FR');
		$this->load->model('Miscellaneous_model', 'MISC');
		$this->load->model('DatabaseSchema_model', 'mDbSchema');

		$this->load->library('form_validation', null, 'FValidate');
		$this->load->library('session');
	}

	public function index(){
		$formObj = (object) array('projectName' => '');
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

		//var_dump("project_id=".$projectId." status=".$status);
		$this->FValidate->set_rules('inputProjectName', null, 'required');
		if($this->FValidate->run()){
			$param = (object) array('projectId' => $projectId, 'status' => ACTIVE_CODE);
			$result = $this->FR->searchFunctionalRequirementHeaderInfo($param);
			
			$data['selectedProjectId'] = $projectId;
			$data['searchFlag'] = 'Y';
		}

		$formObj = (object) array('projectName' => $projectId);
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
				$screenMode = '0';
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
	    $successMsg = '';
	    $isCorrectCSV = FALSE;
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
	    	$fullPath = $data['upload_data']['full_path'];
	    	$this->load->library('csvreader');
       		$result =  $this->csvreader->parse_file($fullPath);//path to csv file
       	
       		//Validate data in File
       		$lineNo = 0;
       		$totalRecord = count($result);
       		$funtionalRequirementsList = array();
       		$correctRecord = 0;
       		$incorrectRecord = 0;
       		
       		$user = (null != $this->session->userdata('username'))? $this->session->userdata('username'): 'userDefault';
       		$dataHeader = '';
       		$inputNameKey = '';
			
       		foreach($result as $value){
       			++$lineNo;
       			$inputId = '';
       			$errorFlag = FALSE;	
       			$hasDataLength = FALSE;
       			$hasScalePoint = FALSE;
       			$correctFRInput = FALSE;

       			$scalePoint = 0;

       			if(NUMBER_OF_UPLOADED_COLUMN_FR != count($value)){
       				$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_004', $lineNo);
       				continue;
       			}

       			/*------------------------------------[HEADER]---------------------------------*/
				/**************************[FUNCTIONAL REQUIREMENT ID]**************************/
				if($this->checkNullOrEmpty($value[KEY_FR_NO])){
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
       			if(!$this->checkNullOrEmpty($value[KEY_FR_NO])){
       				$recordCount = $this->FR->searchExistFunctionalRequirement($value[KEY_FR_NO], $projectId);
       				if(0 < count($recordCount)){
       					$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_006', $lineNo);
       					$errorFlag = TRUE;
       				}
       			}

       			/**************************[FUNCTIONAL REQUIREMENT DESCRIPTION]***********************/
       			//Check FRs'Description not null
       			if($this->checkNullOrEmpty($value[KEY_FR_DESC])){
       				$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_007', $lineNo);
       				$errorFlag = TRUE;
       			}else{
       				//Check Length of FRs'Description
	       			if(LENGTH_FR_DESC < strlen($value[KEY_FR_DESC])){
	       				$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_002', $lineNo);
	       				$errorFlag = TRUE;
	       			}
       			}

       			/*--------------------------[DETAIL]---------------------------*/
       			/**************************[INPUT NAME]*************************/
       			//Check Input Name not null
       			if($this->checkNullOrEmpty($value[KEY_FR_INPUT_NAME])){
       				$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_008', $lineNo);
       				$errorFlag = TRUE;
       			}else{

       				//Check unique in CSV File
       				if(!empty($inputNameKey) && $inputNameKey == $value[KEY_FR_INPUT_NAME]){
	       				$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_010', $lineNo);
	       				$errorFlag = TRUE;
	       			}else{
	       				$inputNameKey = $value[KEY_FR_INPUT_NAME];
	       			}


       				$resultInputInfo = $this->FR->searchFRInputInformation($projectId, $value[KEY_FR_INPUT_NAME], ACTIVE_CODE);
       				if(0 < count($resultInputInfo)){
       					//Validate with exist data
       					$referTableName = $resultInputInfo->refTableName;
       					$referColumnName = $resultInputInfo->refColumnName;
       					
       					if($referTableName != strtoupper($value[KEY_FR_INPUT_TABLE_NAME])
       						|| $referColumnName != strtoupper($value[KEY_FR_INPUT_FIELD_NAME])){
       						$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_030', $lineNo);
		       				$errorFlag = TRUE;
       					}else{
       						$inputId = $resultInputInfo->inputId;
       						$correctFRInput = TRUE;
       					}
       				}else{
       					//Validate with new data
		       			/*************************[TABLE NAME of DB TARGET]************************/
			       		$hasTableName = FALSE;
			       		$hasColumnName = FALSE;
		       			if(!$this->checkNullOrEmpty($value[KEY_FR_INPUT_TABLE_NAME])){
		       				if(MAX_TABLE_NAME < strlen($value[KEY_FR_INPUT_TABLE_NAME])){
		       					$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_027', $lineNo);
								$errorFlag = TRUE;
		       				}else{
		       					$hasTableName = TRUE;
		       				}
		       			}else{
		       				$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_026', $lineNo);
							$errorFlag = TRUE;
		       			}

		       			/*************************[FIELD NAME of DB TARGET]************************/
						if(!$this->checkNullOrEmpty($value[KEY_FR_INPUT_FIELD_NAME])){
		       				if(MAX_FIELD_NAME < strlen($value[KEY_FR_INPUT_FIELD_NAME])){
		       					$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_029', $lineNo);
								$errorFlag = TRUE;
		       				}else{
		       					$hasColumnName = TRUE;
		       				}
		       			}else{
		       				$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_028', $lineNo);
							$errorFlag = TRUE;
		       			}

		       			//Check exist Table and Column Name in Database
		       			if($hasTableName && $hasColumnName){
		       				$resultSchemaInfo = $this->FR->searchExistFRInputsByTableAndColumnName($value[KEY_FR_INPUT_TABLE_NAME], $value[KEY_FR_INPUT_FIELD_NAME], $projectId, ACTIVE_CODE);
		       				if(0 < count($resultSchemaInfo)){
		       					$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_037', $lineNo);
		       					$errorFlag = TRUE;
		       				}

		       				$resultSchemaInfo = $this->mDbSchema->searchExistDatabaseSchemaInfo($value[KEY_FR_INPUT_TABLE_NAME], $value[KEY_FR_INPUT_FIELD_NAME], $projectId);
		       				if(null == $resultSchemaInfo || empty($resultSchemaInfo)){
		       					$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_038', $lineNo);
		       					$errorFlag = TRUE;
		       				}
		       			}

       				}// end validate input
       			}// end check detail

       			if($errorFlag == FALSE){
       				$correctRecord++;

       				$funtionalRequirementsList[] = (object) array(
       					'projectId' => $projectId, 
	    				'functionNo' => $value[KEY_FR_NO], 
	    				'functionDescription' => $value[KEY_FR_DESC], 
	    				'functionVersionNo' => INITIAL_VERSION,
	    				'inputId' => $inputId,
	    				'inputName' => $value[KEY_FR_INPUT_NAME],
	    				'referTableName' => strtoupper($value[KEY_FR_INPUT_TABLE_NAME]),
	    				'referColumnName' => strtoupper($value[KEY_FR_INPUT_FIELD_NAME]),
	    				'schemaVersionId' => '',
	    				'effectiveStartDate' => '',
	    				'effectiveEndDate' => '',
	    				'activeFlag' => ACTIVE_CODE,
	    				'user' => $user);
       			}

       		} //end foreach
       		
       		unlink($fullPath); //delete uploaded file
       		$incorrectRecord = $totalRecord - $correctRecord;
       		if(0 < $incorrectRecord){
       			$error = ER_MSG_008;
       		}else{
       			$isCorrectCSV = TRUE;
       		}

       		$data['totalRecords'] = $totalRecord;
		    $data['correctRecords'] = $correctRecord;
			$data['incorrectRecords'] = $incorrectRecord;
	    } //end if

	    //save data in database
	    if($isCorrectCSV){
	    	$isSaveSuccess = $this->FR->uploadFR($funtionalRequirementsList);

	    	if($isSaveSuccess){
	    		$successMsg =  str_replace("{0}", $funtionalRequirementsList[0]->functionNo, IF_MSG_004);
	    	}else{
	    		$error = ER_MSG_008;
	    	}
		}


	    $hfield = array('screenMode' => $screenMode, 'projectId' => $projectId, 'projectName' => $projectName, 'projectNameAlias' => $projectNameAlias);
	    $data['hfield'] = $hfield;
	    $data['error_message'] = $error;
	    $data['success_message'] = $successMsg;
	    $data['result'] = $resultUpload;
	    $this->openView($data, 'upload');
	}

	private function openView($data, $page){
		if('search' == $page){
			$data['html'] = 'FunctionalRequirementManagement/requirementSearch_view';
			$data['projectCombo'] = $this->Project->searchActiveProjectCombobox();
		}else{
			$data['html'] = 'FunctionalRequirementManagement/requirementUpload_view';
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

	private function nullToEmpty($value){
		if(NULL == $value)
			return "";
		return $value; 
	}
}
?>
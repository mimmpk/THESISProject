<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class FunctionalRequirement extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->model('Project_model', 'Project');
		$this->load->model('FunctionalRequirement_model', 'FR');
		$this->load->model('Miscellaneous_model', 'MISC');
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
       		$inputNameisNULL = FALSE;
       		$tableNameisNULL = FALSE;
       		$fieldNameisNULL = FALSE;

			
       		foreach($result as $value){
       			++$lineNo;
       			$errorFlag = FALSE;	
       			$hasDataLength = FALSE;
       			$hasScalePoint = FALSE;

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
       				if(0 < $recordCount){
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
       				$inputNameisNULL = TRUE;
       			}else{
       				$resultInputInfo = $this->FR->searchFRInputInformation($projectId, $value[KEY_FR_INPUT_NAME]);
       				if(0 < count($resultInputInfo)){
       					//var_dump($resultInputInfo);
       					//var_dump("<br/>");
       					//var_dump($value);
       					//Validate Type 1
       					$cInputType = $this->nullToEmpty($resultInputInfo[0]['inputType']);
       					$cInputSize = $this->nullToEmpty($resultInputInfo[0]['inputSize']);
       					$cScalePoint = $this->nullToEmpty($resultInputInfo[0]['decimalPoint']);
       					$cUnique = $this->nullToEmpty($resultInputInfo[0]['constraintUnique']);
       					$cDefaultValue = $this->nullToEmpty($resultInputInfo[0]['constraintDefault']);
       					$cNullable = $this->nullToEmpty($resultInputInfo[0]['constraintNull']);
       					$cMinValue = $this->nullToEmpty($resultInputInfo[0]['constraintMinValue']);
       					$cMaxValue = $this->nullToEmpty($resultInputInfo[0]['constraintMaxValue']);
       					$cTableName = $this->nullToEmpty($resultInputInfo[0]['relationTableName']);
       					$cColumnName = $this->nullToEmpty($resultInputInfo[0]['relationColumnName']);
       					
       					if($cInputType != strtoupper($value[KEY_FR_INPUT_TYPE])
       						|| $cInputSize != $value[KEY_FR_INPUT_LENGTH]
       						|| $cScalePoint != $value[KEY_FR_DECIMAL_POINT]
       						|| $cUnique != strtoupper($value[KEY_FR_INPUT_UNIQUE])
       						|| $cDefaultValue != $value[KEY_FR_INPUT_DEFAULT]
       						|| $cNullable != strtoupper($value[KEY_FR_INPUT_NULL])
       						|| $cMinValue != $value[KEY_FR_INPUT_MIN_VALUE]
       						|| $cMaxValue != $value[KEY_FR_INPUT_MAX_VALUE]
       						|| $cTableName != $value[KEY_FR_INPUT_TABLE_NAME]
       						|| $cColumnName != $value[KEY_FR_INPUT_FIELD_NAME]){
       						$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_030', $lineNo);
		       				$errorFlag = TRUE;
       					}
       				}else{
       					//Validate Type 2
       					//Check length of Input Name
		       			if(LENGTH_INPUT_NAME < strlen($value[KEY_FR_INPUT_NAME])){
		       				$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_009', $lineNo);
		       				$errorFlag = TRUE;
		       			}

		       			$typeIsMatch = FALSE;
		       			/**************************[INPUT DATA TYPE]*************************/ 
		       			$dataType = '';
		       			//Check Input Data Type not null
		       			if($this->checkNullOrEmpty($value[KEY_FR_INPUT_TYPE])){
		       				$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_011', $lineNo);
		       				$errorFlag = TRUE;
		       			}else{
		       				//Check Length of Input Data Type
		       				if(LENGTH_INPUT_DATA_TYPE < strlen($value[KEY_FR_INPUT_TYPE])){
		       					$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_012', $lineNo);
		       					$errorFlag = TRUE;
		       				}

		       				//Check format
		       				$miscValue = strtolower($value[KEY_FR_INPUT_TYPE]);
		       				$result = $this->MISC->searchMiscellaneous(MISC_DATA_INPUT_DATA_TYPE, $miscValue);
		       				if(null == $result || 0 == count($result)){
		       					$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_013', $lineNo);
		       					$errorFlag = TRUE;
		       				}else{
		       					$typeIsMatch = TRUE;
		       					$dataType = $result[0]['miscValue2'];
		       				}
		       			}

		       			/**************************[INPUT DATA LENGTH]*************************/ 
		       			//Check Size
		       			$exceptInputSize = array("date", "datetime", "int", "float", "real");
		       			$inputLength = $value[KEY_FR_INPUT_LENGTH];
		       			$lengthIsMatch = TRUE;
		       			if($typeIsMatch && !in_array($miscValue, $exceptInputSize)){
		       				$hasDataLength = TRUE;
		       				if($this->checkNullOrEmpty($inputLength)){
			       				$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_014', $lineNo);
			       				$errorFlag = TRUE;
			       				$lengthIsMatch = FALSE;
			       			}else{
			       				if("char" == $miscValue || "varchar" == $miscValue){
			       					if($inputLength < 1 || $inputLength > 8000 ){
			       						$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_015', $lineNo);
			       						$errorFlag = TRUE;
			       						$lengthIsMatch = FALSE;
			       					}
			       				}else if("nchar" == $miscValue || "nvarchar" == $miscValue){
			       					if($inputLength < 1 || $inputLength > 4000 ){
			       						$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_016', $lineNo);
			       						$errorFlag = TRUE;
			       						$lengthIsMatch = FALSE;
			       					}
			       				}else{
			       					if($inputLength < 1 || $inputLength > 38 ){
			       						$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_017', $lineNo);
			       						$errorFlag = TRUE;
			       						$lengthIsMatch = FALSE;
			       					}else{
			       						//Check Decimal Scale. If NULL, default value will be '0'.
			       						$decimalScale = $value[KEY_FR_DECIMAL_POINT];
			       						if(!$this->checkNullOrEmpty($decimalScale)){
			       							if($decimalScale < 0 || $decimalScale > $inputLength){
			       								$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_018', $lineNo);
			       								$errorFlag = TRUE;
			       								$lengthIsMatch = FALSE;
			       							}else{
			       								$hasScalePoint = TRUE;
			       								$scalePoint = $value[KEY_FR_DECIMAL_POINT];
			       							}
			       						}else{
			       							$hasScalePoint = TRUE;
			       						}
			       					}
			       				}
			       			}
		       			}
		       			
		       			/**************************[CONSTRAINT-UNIQUE]*************************/ 
		       			if($this->checkNullOrEmpty($value[KEY_FR_INPUT_UNIQUE])){
		   					$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_019', $lineNo);
							$errorFlag = TRUE;
		       			}else{
		       				$uniqueContraint = strtoupper($value[KEY_FR_INPUT_UNIQUE]);
		       				if("Y" != $uniqueContraint && "N" != $uniqueContraint){
		       					$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_020', $lineNo);
								$errorFlag = TRUE;
		       				}
		       			}

		       			/**************************[CONSTRAINT-DEFAULT]*************************/
		       			if(!$this->checkNullOrEmpty($value[KEY_FR_INPUT_DEFAULT]) && $typeIsMatch && $lengthIsMatch){
		       				$defaultValue = $value[KEY_FR_INPUT_DEFAULT];
		       				if("Numerics" == $dataType){
		       					if(!is_numeric($defaultValue)){
		       						$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_021', $lineNo);
									$errorFlag = TRUE;
		       					}else{
		       						if('decimal' == $miscValue && is_float((float)$defaultValue)){
		       							$decimalFotmat = explode(".", $defaultValue);
		       							if($decimalFotmat[0] > $inputLength){
		       								$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_021', $lineNo);
											$errorFlag = TRUE;
		       							}
		       						}
		       					}
		       				}else if("Strings" == $dataType && strlen($defaultValue) > $inputLength){
		       					$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_021', $lineNo);
								$errorFlag = TRUE;
		       				}else{ //date
		       					if("getdate()" != strtolower($defaultValue)){
		       						$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_021', $lineNo);
									$errorFlag = TRUE;
		       					}
		       				}
		       			}

		       			/****************************[CONSTRAINT-NULL]***************************/
		       			if($this->checkNullOrEmpty($value[KEY_FR_INPUT_NULL])){
		   					$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_022', $lineNo);
							$errorFlag = TRUE;
		       			}else{
		       				$notNullConstaint = strtoupper($value[KEY_FR_INPUT_NULL]);
		       				if("Y" != $notNullConstaint && "N" != $notNullConstaint){
		       					$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_023', $lineNo);
								$errorFlag = TRUE;
		       				}
		       			}

		       			/*************************[CONSTRAINT-CHECK(MIN)]************************/
		       			if($typeIsMatch && !$this->checkNullOrEmpty($value[KEY_FR_INPUT_MIN_VALUE])){
		       				if("Numerics" == $dataType){
		       					if(!is_numeric($value[KEY_FR_INPUT_MIN_VALUE])){
		       						$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_024', $lineNo);
									$errorFlag = TRUE;
		       					}
		       				}else{
		       					$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_024', $lineNo);
								$errorFlag = TRUE;
		       				}
		       			}

		       			/*************************[CONSTRAINT-CHECK(MAX)]************************/
		       			if($typeIsMatch && !$this->checkNullOrEmpty($value[KEY_FR_INPUT_MAX_VALUE])){
		       				if("Numerics" == $dataType){
		       					if(!is_numeric($value[KEY_FR_INPUT_MAX_VALUE])){
		       						$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_025', $lineNo);
									$errorFlag = TRUE;
		       					}
		       				}else{
		       					$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_025', $lineNo);
								$errorFlag = TRUE;
		       				}
		       			}

		       			/*************************[TABLE NAME of DB TARGET]************************/
		       			if(!$this->checkNullOrEmpty($value[KEY_FR_INPUT_TABLE_NAME])){
		       				if(MAX_TABLE_NAME < strlen($value[KEY_FR_INPUT_TABLE_NAME])){
		       					$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_027', $lineNo);
								$errorFlag = TRUE;
		       				}else{
		       					$tableNameisNULL = TRUE;
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
		       					$fieldNameisNULL = TRUE;
		       				}
		       			}else{
		       				$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_028', $lineNo);
							$errorFlag = TRUE;
		       			}


		       			/********************************[MORE]*********************************/ 
		       			//Check unique Input Name with Table and Field of Target Database
		       			if(!$inputNameisNULL){ //&& !$tableNameisNULL && !$fieldNameisNULL
		       				//Check unique in CSV File
		       				if(!empty($inputNameKey) && $inputNameKey == $value[KEY_FR_INPUT_NAME]){
			       				$resultUpload = $this->appendThings($resultUpload, 'ER_IMP_010', $lineNo);
			       				$errorFlag = TRUE;
			       			}else{
			       				$inputNameKey = $value[KEY_FR_INPUT_NAME];
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
	    				'inputName' => $value[KEY_FR_INPUT_NAME], 
	    				'dataType' => $value[KEY_FR_INPUT_TYPE], 
	    				'dataLength' => (($hasDataLength)? $value[KEY_FR_INPUT_LENGTH] : NULL), 
	    				'scale' => (($hasScalePoint)? $scalePoint : NULL), 
	    				'unique' => strtoupper($value[KEY_FR_INPUT_UNIQUE]), 
	    				'defaultValue' => $value[KEY_FR_INPUT_DEFAULT], 
	    				'notNull' => strtoupper($value[KEY_FR_INPUT_NULL]), 
	    				'minValue' => $value[KEY_FR_INPUT_MIN_VALUE],
	    				'maxValue' => $value[KEY_FR_INPUT_MAX_VALUE],
	    				'tableName' => $value[KEY_FR_INPUT_TABLE_NAME],
	    				'fieldName' => $value[KEY_FR_INPUT_FIELD_NAME],
	    				'version' => INITIAL_VERSION,
	    				'functionStatus' => ACTIVE_CODE,
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
	    }

	    //save data in database
	    if($isCorrectCSV){
	    	//upload
	    	//var_dump($funtionalRequirementsList);
	    	$isSaveSuccess = $this->FR->uploadFR($funtionalRequirementsList);

	    	if($isSaveSuccess){
	    		$successMsg = ER_MSG_009;
	    	}else{
	    		$error = ER_MSG_008;
	    	}
		}


	    $hfield = array('screenMode' => $screenMode, 'projectId' => $projectId, 'projectName' => $projectName, 'projectNameAlias' => $projectNameAlias);
	    $data['hfield'] = $hfield;
	    $data['totalRecords'] = $totalRecord;
	    $data['correctRecords'] = $correctRecord;
		$data['incorrectRecords'] = $incorrectRecord;
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
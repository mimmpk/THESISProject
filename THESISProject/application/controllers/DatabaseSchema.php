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
		$this->load->model('Miscellaneous_model', 'mMisc');
	}

	public function index(){
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

		$this->FValidate->set_rules('inputProjectName', null, 'required');
		if($this->FValidate->run()){
			$resultList = $this->mDbSchema->searchDatabaseSchemaByCriteria($projectId, ACTIVE_CODE);

			$data['selectedProjectId'] = $projectId;
			$data['searchFlag'] = 'Y';
		}

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
			$errorMessage = ER_MSG_008;
		}else{
			$data = array('upload_data' => $this->upload->data());
			$fullPath = $data['upload_data']['full_path'];
	    	$this->load->library('csvreader');
       	    $result =  $this->csvreader->parse_file($fullPath);//path to csv file

    		//Validate data in File
    		if(0 < count($result)){
    			$totalRecord = count($result);
    			$correctRecord = 0;
       		    $incorrectRecord = 0;
       		    $databaseSchemaList = array();


           		if($this->validate($result, $uploadResult, $correctRecord, $projectId)){
           			$user = (null != $this->session->userdata('username'))? $this->session->userdata('username'): 'userDefault';
           			//Prepare data for uploading
           			foreach ($result as $value) {
           				$databaseSchemaList[] = (object) array(
    	       				'tableName' => strtoupper($value[KEY_DB_TABLE_NAME]),
    	       				'columnName' => strtoupper($value[KEY_DB_COLUMN_NAME]),
    	       				'primaryKey' => strtoupper($value[KEY_DB_ISPRIMARY_KEY]),
    	       				'dataType' => strtolower($value[KEY_FR_INPUT_TYPE]),
    	       				'dataLength' => $value[KEY_FR_INPUT_LENGTH],
    	       				'scale' => $value[KEY_FR_DECIMAL_POINT],
    	       				'unique' => strtoupper($value[KEY_FR_INPUT_UNIQUE]),
    	       				'defaultValue' => $value[KEY_FR_INPUT_DEFAULT],
    	       				'null' => strtoupper($value[KEY_FR_INPUT_NULL]),
    	       				'minValue' => $value[KEY_FR_INPUT_MIN_VALUE],
    	       				'maxValue' => $value[KEY_FR_INPUT_MAX_VALUE],
    	       				'schemaVersionId' => '',
    	       				'schemaVersionNo' => INITIAL_VERSION,
    	       				'status' => ACTIVE_CODE
           				);
           			}

           			//Saving data
           			$resultUpload = $this->mDbSchema->uploadDatabaseSchema($databaseSchemaList, $user, $projectId);
           			if($resultUpload){
           				//$successMessage = ER_MSG_009;
                        $successMessage =  str_replace("{0}", $databaseSchemaList[0]->tableName, IF_MSG_003); 
           			}else{
           				$errorMessage = ER_MSG_008;
           			}

           		}else{
           			$errorMessage = ER_MSG_008;
           		}
                
           		$incorrectRecord = $totalRecord - $correctRecord;
           		$data['totalRecords'] = $totalRecord;
    		    $data['correctRecords'] = $correctRecord;
    			$data['incorrectRecords'] = $incorrectRecord;
    		}else{
    			$errorMessage = ER_MSG_010;
    		}
    		unlink($fullPath); //delete uploaded file
		}

		$hfield = array('screenMode' => $screenMode, 'projectId' => $projectId, 'projectName' => $projectName, 'projectNameAlias' => $projectNameAlias);
		$data['hfield'] = $hfield;
	    $data['error_message'] = $errorMessage;
	    $data['success_message'] = $successMessage;
	    $data['uploadResult'] = $uploadResult;
		$this->openView($data, 'upload');
	}

	private function validate($data, &$uploadResult, &$correctRecord, $projectId){
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

   				//Check duplicate Table and Column in Database
   				if(!empty($value[KEY_DB_TABLE_NAME] && !empty($value[KEY_DB_COLUMN_NAME]))){
   					$result = $this->mDbSchema->searchExistDatabaseSchemaInfo($value[KEY_DB_TABLE_NAME], $value[KEY_DB_COLUMN_NAME], $projectId);
   					if(0 < count($result)){
   						$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_036', $lineNo);
   						$hasError = TRUE;
   					}
   				}
   			}

   			/**************************[PRIMARY KEY]**************************/
   			if($this->checkNullOrEmpty($value[KEY_DB_ISPRIMARY_KEY])){
   				$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_033', $lineNo);
   				$hasError = TRUE;
   			}else{
   				//Check Format ('Y' or 'N')
   				$primaryKeyValue = strtoupper($value[KEY_DB_ISPRIMARY_KEY]);
   				if("Y" != $primaryKeyValue && "N" != $primaryKeyValue){
					$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_034', $lineNo);
   					$hasError = TRUE;
   				}
   			}

   			/**************************[DATA TYPE]**************************/
   			$typeIsMatch = FALSE;
   			$dataType = '';
   			if($this->checkNullOrEmpty($value[KEY_FR_INPUT_TYPE])){
   				$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_011', $lineNo);
   				$hasError = TRUE;
   			}else{
   				//Check Length of Data Type
   				if(LENGTH_INPUT_DATA_TYPE < strlen($value[KEY_FR_INPUT_TYPE])){
   					$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_012', $lineNo);
   					$hasError = TRUE;
   				}

   				//Check format
   				$miscValue = strtolower($value[KEY_FR_INPUT_TYPE]);
   				$result = $this->mMisc->searchMiscellaneous(MISC_DATA_INPUT_DATA_TYPE, $miscValue);
   				if(null == $result || empty($result)){
   					$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_013', $lineNo);
   					$hasError = TRUE;
   				}else{
   					$typeIsMatch = TRUE;
   					$dataType = $result[0]['miscValue2'];
   				}

   			}

   			/**************************[DATA LENGTH]**************************/
   			$exceptInputSize = array("date", "datetime", "int", "float", "real");
   			$inputLength = $value[KEY_FR_INPUT_LENGTH];
   			$lengthIsMatch = TRUE;

   			if($typeIsMatch && !in_array($miscValue, $exceptInputSize)){
   				if($this->checkNullOrEmpty($inputLength)){
   					$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_014', $lineNo);
   					$hasError = TRUE;
   					$lengthIsMatch = FALSE;
   				}else{
   					if("char" == $miscValue || "varchar" == $miscValue){
   						if($inputLength < 1 || $inputLength > 8000 ){
   							$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_015', $lineNo);
   							$hasError = TRUE;
   							$lengthIsMatch = FALSE;
   						}
   					}else if("nchar" == $miscValue || "nvarchar" == $miscValue){
						if($inputLength < 1 || $inputLength > 4000 ){
							$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_016', $lineNo);
							$hasError = TRUE;
							$lengthIsMatch = FALSE;
						}
   					}else{
   						if($inputLength < 1 || $inputLength > 38){
							$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_017', $lineNo);
							$hasError = TRUE;
							$lengthIsMatch = FALSE;
						}else{
							//Check Decimal Scale. If NULL, default value will be '0'.
							$hasScalePoint = TRUE;
							$decimalScale = $value[KEY_FR_DECIMAL_POINT];
							if(!$this->checkNullOrEmpty($decimalScale)){
								if($decimalScale < 0 || $decimalScale > $inputLength){
									$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_018', $lineNo);
									$hasError = TRUE;
									$lengthIsMatch = FALSE;
									$hasScalePoint = FALSE;
								}else{
									$scalePoint = $value[KEY_FR_DECIMAL_POINT];
								}
							}
						}
   					}
   				}
   			}

   			/**************************[CONSTRAINT-UNIQUE]**************************/
   			if($this->checkNullOrEmpty($value[KEY_FR_INPUT_UNIQUE])){
   				$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_019', $lineNo);
   				$hasError = TRUE;
   			}else{
   				$uniqueContraint = strtoupper($value[KEY_FR_INPUT_UNIQUE]);
   				if("Y" != $uniqueContraint && "N" != $uniqueContraint){
   					$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_020', $lineNo);
   					$hasError = TRUE;
   				}
   			}

   			/**************************[CONSTRAINT-DEFAULT]**************************/
   			if(!$this->checkNullOrEmpty($value[KEY_FR_INPUT_DEFAULT]) && $typeIsMatch && $lengthIsMatch){
   				$defaultValue = $value[KEY_FR_INPUT_DEFAULT];
   				if(DATA_TYPE_CATEGORY_NUMERICS == $dataType){
   					if(!is_numeric($defaultValue)){
   						$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_021', $lineNo);
   						$hasError = TRUE;
   					}else if('decimal' == $miscValue && is_float((float)$defaultValue)){
   						$decimalFotmat = explode(".", $defaultValue);
   						if(strlen($decimalFotmat[0]) > $inputLength){
   							$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_021', $lineNo);
   							$hasError = TRUE;
   						}
   					}
   				}else if(DATA_TYPE_CATEGORY_STRINGS == $dataType && strlen($defaultValue) > $inputLength){
   					$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_021', $lineNo);
   					$hasError = TRUE;
   				}else{ // Date Type
   					if("getdate()" != strtolower($defaultValue)){
   						$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_021', $lineNo);
   						$hasError = TRUE;
   					}
   				}
   			}

   			/****************************[CONSTRAINT-NULL]***************************/
   			if($this->checkNullOrEmpty($value[KEY_FR_INPUT_NULL])){
   				$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_022', $lineNo);
   				$hasError = TRUE;
   			}else{
   				$notNullConstaint = strtoupper($value[KEY_FR_INPUT_NULL]);
   				if("Y" != $notNullConstaint && "N" != $notNullConstaint){
   					$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_023', $lineNo);
   					$hasError = TRUE;
   				}
   			}

   			/************************[CONSTRAINT-CHECK(MIN)]************************/
   			$hasMinValue = FALSE;
   			$hasMaxValue = FALSE;
   			if($typeIsMatch && !$this->checkNullOrEmpty($value[KEY_FR_INPUT_MIN_VALUE])){
   				if("Numerics" == $dataType){
   					if(!is_numeric($value[KEY_FR_INPUT_MIN_VALUE])){
   						$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_024', $lineNo);
   						$hasError = TRUE;
   					}else{
   						$hasMinValue = TRUE;
   					}
   				}else{
				   	$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_024', $lineNo);
					$hasError = TRUE;
   				}
   			}

   			/************************[CONSTRAINT-CHECK(MAX)]************************/
   			if($typeIsMatch && !$this->checkNullOrEmpty($value[KEY_FR_INPUT_MAX_VALUE])){
   				if("Numerics" == $dataType){
   					if(!is_numeric($value[KEY_FR_INPUT_MAX_VALUE])){
   						$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_025', $lineNo);
   						$hasError = TRUE;
   					}else{
   						$hasMaxValue = TRUE;
   					}
   				}else{
   					$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_025', $lineNo);
					$hasError = TRUE;
   				}
   			}

   			if($hasMinValue && $hasMaxValue){
   				if((float)$value[KEY_FR_INPUT_MIN_VALUE] > (float)$value[KEY_FR_INPUT_MAX_VALUE]){
   					$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_035', $lineNo);
					$hasError = TRUE;
   				}
   			}


   			if(!$hasError){
   				$correctRecord++;
   			}
   		} // end foreach

   		if($correctRecord == count($data)){
   			return TRUE;
   		}else{
   			return FALSE;
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
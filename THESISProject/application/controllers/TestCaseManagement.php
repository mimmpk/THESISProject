<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

/**
* Test Case Management Controller
*/
class TestCaseManagement extends CI_Controller{
	
	function __construct(){
		parent::__construct();
		$this->load->library('form_validation', null, 'FValidate');
		$this->load->library('session');
		$this->load->model('Project_model', 'mProject');
		$this->load->model('DatabaseSchema_model', 'mDbSchema');
		$this->load->model('TestCase_model', 'mTestCase');
		$this->load->model('FunctionalRequirement_model', 'mRequirement');
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

		$projectId = $this->input->post('inputProjectName');

		$this->FValidate->set_rules('inputProjectName', null, 'required');
		if($this->FValidate->run()){
			$resultList = $this->mTestCase->searchTestCaseInfoByCriteria($projectId, ACTIVE_CODE);

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

		if("undefined" != $projectId && !empty($projectId)){
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

		$data['hfield'] = $hfield;
		$data['uploadResult'] = null;
		$data['error_message'] = $errorMessage;

		$this->openView($data, 'upload');
	}

	public function doUpload(){
		$fileName = "TC_".date("YmdHis")."_".$this->session->session_id;
		
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
       		    $testCaseInfoList = array();

       		    $resultValidate = $this->validate($result, $projectId, $uploadResult, $correctRecord, $testCaseInfoList);
       		    if($resultValidate){
       		    	$user = (null != $this->session->userdata('username'))? $this->session->userdata('username'): 'userDefault';

       		    	//Saving data
       		    	$saveResult = $this->mTestCase->uploadTestCaseInfo($testCaseInfoList, $user);
       		    	if($saveResult){
       		    		$successMessage = str_replace("{0}", $testCaseInfoList[0]->testCaseNo, IF_MSG_005);
       		    	}else{
       		    		$errorMessage = ER_MSG_008;
       		    	}
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
		$data['uploadResult'] = $uploadResult;
		$data['error_message'] = $errorMessage;
 		$data['success_message'] = $successMessage;
		$this->openView($data, 'upload');
	}

	private function validate($data, $projectId, &$uploadResult, &$correctRecord, &$testCaseInfoList){
		$lineNo = 0;
		$checkTestCaseId = '';
		$checkTestCaseDesc = '';
		$checkExpectedResult = '';
		$checkInputName = '';
		//var_dump($data);
		foreach ($data as $value) {
			++$lineNo;

			$hasError = FALSE;
			$correctFnReqNo = FALSE;
			$refInputId = '';

			if(NUMBER_OF_UPLOADED_COLUMN_TC != count($value)){
   				$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_004', $lineNo);
   				continue;
   			}

   			/**************************[TEST CASE ID]**************************/
   			if($this->checkNullOrEmpty($value[KEY_TC_TESTCASE_NO])){
   				$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_039', $lineNo);
   				$hasError = TRUE;
   			}else{
   				if(!empty($checkTestCaseId) && $checkTestCaseId != $value[KEY_TC_TESTCASE_NO]){
   					$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_040', $lineNo);
   					$hasError = TRUE;
   				}else{
   					$checkTestCaseId = $value[KEY_TC_TESTCASE_NO];
   				}

   				if(LENGTH_TC_NO < $value[KEY_TC_TESTCASE_NO]){
   					$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_041', $lineNo);
   					$hasError = TRUE;
   				}
   			}
   			
   			/****************************[Test Case Description]**************************/
   			if(!$this->checkNullOrEmpty($value[KEY_TC_TESTCASE_DESC])){
   				if(!empty($checkTestCaseDesc) && ($checkTestCaseDesc != $value[KEY_TC_TESTCASE_DESC])){
   					$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_042', $lineNo);
   					$hasError = TRUE;
   				}else{
   					$checkTestCaseDesc = $value[KEY_TC_TESTCASE_DESC];
   				}

   				if(LENGTH_TC_DESC < strlen($value[KEY_TC_TESTCASE_DESC])){
   					$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_043', $lineNo);
   					$hasError = TRUE;
   				}
   			}

   			/*************************[Test Case Expected Result]************************/
   			if($this->checkNullOrEmpty($value[KEY_TC_EXPECTED_RESULT])){
   				$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_044', $lineNo);
				$hasError = TRUE;
   			}else{
   				if("valid" != strtolower($value[KEY_TC_EXPECTED_RESULT]) 
   					&& "invalid" != strtolower($value[KEY_TC_EXPECTED_RESULT])){
   					$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_045', $lineNo);
					$hasError = TRUE;
   				}
   			}

   			/**********************************[Input Name]*****************************/
   			if($this->checkNullOrEmpty($value[KEY_TC_INPUT_NAME])){
				$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_046', $lineNo);
				$hasError = TRUE;
   			}else{
   				if(!empty($checkInputName) && $checkInputName == $value[KEY_TC_INPUT_NAME]){
   					$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_047', $lineNo);
					$hasError = TRUE;
   				}else{
   					$checkInputName = $value[KEY_TC_INPUT_NAME];
   				}

   				$result = $this->mRequirement->searchFRInputInformation($projectId, $value[KEY_TC_INPUT_NAME], ACTIVE_CODE);
   				if(null == $result || empty($result)){
   					$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_048', $lineNo);
					$hasError = TRUE;
   				}else{
   					$refInputId = $result->inputId;
   				}
   			}

   			/**********************************[Test Data]*****************************/
   			if($this->checkNullOrEmpty($value[KEY_TC_TEST_DATA])){
   				$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_049', $lineNo);
				$hasError = TRUE;
   			}

   			/*****************[Check Duplicate Test Case Input Data]*******************/
   			if(!$this->checkNullOrEmpty($value[KEY_TC_TESTCASE_NO]) && !empty($refInputId)){
   				$result = $this->mTestCase->searchExistTestCaseDetail($projectId, $value[KEY_TC_TESTCASE_NO], $refInputId);
   				if(0 < count($result)){
   					$uploadResult = $this->appendThings($uploadResult, 'ER_IMP_050', $lineNo);
					$hasError = TRUE;
   				}
   			}

   			if(!$hasError){
   				$correctRecord++;
   				$testCaseInfoList[] = (object) array(
   					'testCaseId' => '',
   					'testCaseNo' => $value[KEY_TC_TESTCASE_NO],
   					'testCaseDescription' => $value[KEY_TC_TESTCASE_DESC],
   					'expectedResult' => $value[KEY_TC_EXPECTED_RESULT],
   					'projectId' => $projectId,
   					'refInputId' => $refInputId,
   					'refInputName' => $value[KEY_TC_INPUT_NAME],
   					'testData' => $value[KEY_TC_TEST_DATA],
   					'effectiveStartDate' => '',
   					'initialVersionNo' => INITIAL_VERSION,
    	       		'activeStatus' => ACTIVE_CODE
   				);
   			}
		}//endforeach;
		if($correctRecord == count($data)){
   			return TRUE;
   		}else{
   			return FALSE;
   		}
	}

	private function openView($data, $page){
		if('search' == $page){
			$data['html'] = 'TestCaseManagement/testCaseSearch_view';
			$data['projectCombo'] = $this->mProject->searchActiveProjectCombobox();
		}else{
			$data['html'] = 'TestCaseManagement/testCaseUpload_view';
		}
		$data['active_title'] = 'master';
		$data['active_page'] = 'mats003';
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
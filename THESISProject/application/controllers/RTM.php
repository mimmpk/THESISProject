<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

/**
* Requirements Traceability Matrix Controller
*/
class RTM extends CI_Controller{
	
	function __construct(){
		parent::__construct();
		$this->load->library('form_validation', null, 'FValidate');
		$this->load->library('session');
		$this->load->model('Project_model', 'mProject');
		$this->load->model('FunctionalRequirement_model', 'mFR');
		$this->load->model('RTM_model', 'mRTM');
		$this->load->model('TestCase_model', 'mTestCase');
	}

	public function index(){
		$data['error_message'] = '';
		$data['searchResultList'] = null;
		$this->openView($data, 'search');
	}

	public function reset(){
		$this->index();
	}

	public function search(){
		$error_message = '';
		$searchResultList = null;
		
		$projectId = $this->input->post('inputProjectName');
		$this->FValidate->set_rules('inputProjectName', null, 'required');
		if($this->FValidate->run()){
			$searchResultList = $this->mRTM->searchRTMInfoByCriteria($projectId);

			$data['selectedProjectId'] = $projectId;
			$data['searchFlag'] = 'Y';
		}
		$data['error_message'] = $error_message;
		$data['searchResultList'] = $searchResultList;
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
		$fileName = "RTM_".date("YmdHis")."_".$this->session->session_id;

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
			$uploadData = array('upload_data' => $this->upload->data());
			$fullPath = $uploadData['upload_data']['full_path'];
			$this->load->library('csvreader');
			$result =  $this->csvreader->parse_file($fullPath);//path to csv file

			//Validate data in File
			if(0 < count($result)){
				$totalRecord = count($result);
    			$correctRecord = 0;
       		    $incorrectRecord = 0;
       		    $rtmList = array();

       		    $resultValidate = $this->validate($result, $projectId, $uploadResult, $correctRecord, $rtmList);
       		    if($resultValidate){
       		    	$user = (null != $this->session->userdata('username'))? $this->session->userdata('username'): 'userDefault';
       		    	
       		    	//Saving data
       		    	$saveResult = $this->mRTM->uploadRTM($rtmList, $user);
       		    	if($saveResult){
       		    		$successMessage = ER_MSG_009;
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

	private function validate($data, $projectId, &$uploadResult, &$correctRecord, &$rtmList){
		$lineNo = 0;

		$this->load->library('common');

		foreach ($data as $value) {
			++$lineNo;

			$hasError = FALSE;
			$functionId = '';
			$testCaseId = '';

			if(NUMBER_OF_UPLOADED_COLUMN_RTM != count($value)){
   				$uploadResult = $this->common->appendThings($uploadResult, 'ER_IMP_004', $lineNo);
   				continue;
   			}

   			/**************************[FUNCTIONAL REQUIREMENT ID]**************************/
   			if($this->common->checkNullOrEmpty($value[KEY_FR_ID])){
   				$uploadResult = $this->common->appendThings($uploadResult, 'ER_IMP_005', $lineNo);
   				$hasError = TRUE;
   			}else{
   				//Check Exist in Database
   				$resultFR = $this->mFR->searchExistFunctionalRequirement($value[KEY_FR_ID], $projectId);
   				if(0 == count($resultFR)){
   					$uploadResult = $this->common->appendThings($uploadResult, 'ER_IMP_051', $lineNo);
   					$hasError = TRUE;
   				}else{
   					$functionId = $resultFR[0]['functionId'];
   				}
   			}

   			/**************************[TEST CASE ID]**************************/
   			if($this->common->checkNullOrEmpty($value[KEY_TC_TESTCASE_NO])){
   				$uploadResult = $this->common->appendThings($uploadResult, 'ER_IMP_039', $lineNo);
   				$hasError = TRUE;
   			}else{
   				//Check Exist in Database
   				$resultTC = $this->mTestCase->searchExistTestCaseHeader($projectId, $value[KEY_TC_TESTCASE_NO]);
   				if(0 == count($resultTC)){
   					$uploadResult = $this->common->appendThings($uploadResult, 'ER_IMP_052', $lineNo);
   					$hasError = TRUE;
   				}else{
   					$testCaseId = $resultTC->testCaseId;
   				}
   			}

   			//Check Exist in RTM Database
   			if(!empty($testCaseId)){
				$result = $this->mRTM->searchExistRTMInfoByTestCaseId($projectId, $testCaseId);
   				if(0 < count($result)){
	   				$uploadResult = $this->common->appendThings($uploadResult, 'ER_IMP_053', $lineNo);
					$hasError = TRUE;
	   			}
   			}
   			

   			if(!$hasError){
   				$correctRecord++;

   				$rtmList[] = (object) array(
   					'projectId' => $projectId, 
   					'functionId' => $functionId,
   					'testCaseId' => $testCaseId,
   					'versionNo' => INITIAL_VERSION,
   					'effectiveStartDate' => '',
   					'activeFlag' => ACTIVE_CODE );
   			}
		}
		if($correctRecord == count($data)){
   			return TRUE;
   		}else{
   			return FALSE;
   		}
	}

	private function openView($data, $page){
		if('search' == $page){
			$data['html'] = 'RequirementsTraceabilityMatrixManagement/rtmSearch_view';
			$data['projectCombo'] = $this->mProject->searchActiveProjectCombobox();
		}else{
			$data['html'] = 'RequirementsTraceabilityMatrixManagement/rtmUpload_view';
		}
		$data['active_title'] = 'master';
		$data['active_page'] = 'mats004';
		$this->load->view('template/header');
		$this->load->view('template/body', $data);
		$this->load->view('template/footer');
	}

}

?>
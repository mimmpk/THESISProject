<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

/**
* Test Case Version Management
* Create Date: 2017-06-08
*/
class VersionManagement_TestCase extends CI_Controller{
	
	function __construct(){
		parent::__construct();

		$this->load->model('Project_model', 'mProj');
		$this->load->model('TestCase_model', 'mTC');
		$this->load->model('VersionManagement_model', 'mVerMng');

		$this->load->library('form_validation', null, 'FValidate');
	}

	public function index(){
		$data['projectCombo'] = $this->mProj->searchStartProjectCombobox();

		$data['projectId'] = '';
		$data['testCaseId'] = '';
		$data['testCaseVersionId'] = '';

		$data['resultList'] = null;
		$this->openView($data);
	}

	public function getRelatedTestCase(){
		$output = '';
		if(!empty($_POST)){
			$projectId = $this->input->post('projectId');

			$criteria = (object) array('projectId' => $projectId);
			$testCaseList = $this->mVerMng->searchRelatedTestCases($criteria);

			$output .= "<option value=''>".PLEASE_SELECT."</option>";
			foreach($testCaseList as $value){
				$output .= "<option value='".$value['testCaseId']."'>".$value['testCaseNo'].": ".$value['testCaseDescription']."</option>";
			}
		}
		echo $output;
	}

	public function getRelatedTestCaseVersion(){
		$output = '';
		if(!empty($_POST)){
			$testCaseId = $this->input->post('testCaseId');

			$criteria = (object) array('testCaseId' => $testCaseId);
			$testCaseVersionList = $this->mVerMng->searchRelatedTestCaseVersion($criteria);

			$output .= "<option value=''>".PLEASE_SELECT."</option>";
			foreach($testCaseVersionList as $value){
				$output .= "<option value='".$value['testCaseVersionId']."'>"."Version ".$value['testCaseVersionNumber']."</option>";
			}
		}
		echo $output;
	}

	public function search(){
		$resultList = array();

		$projectId = $this->input->post('inputProjectName');
		$testCaseId = $this->input->post('inputTestCase');
		$testCaseVersionId = $this->input->post('inputVersion');

		$this->FValidate->set_rules('inputProjectName', null, 'required');
		$this->FValidate->set_rules('inputTestCase', null, 'required');
		$this->FValidate->set_rules('inputVersion', null, 'required');

		if($this->FValidate->run()){
			$criteria = (object) array(
				'testCaseId' => $testCaseId, 'testCaseVersionId' => $testCaseVersionId);
			$versionInfo = $this->mTC->searchTestCaseVersionInformationByCriteria($criteria);

			if(null != $versionInfo && 0 < count($versionInfo)){
				$param = (object) array(
					'testCaseId' 	=> $testCaseId,
					'targetDate' 	=> $versionInfo->effectiveStartDate);
				
				$resultList = $this->mVerMng->searchTestCaseDetailByVersion($param);
				$data['resultVersionInfo'] = $versionInfo;
				//var_dump($resultList);
			}
		}

		$data['projectId'] = $projectId;
		$data['testCaseId'] = $testCaseId;
		$data['testCaseVersionId'] = $testCaseVersionId;

		//var_dump($testCaseVersionId);

		$this->initialComboBox($projectId, $testCaseId, $data);

		$data['resultList'] = $resultList;
		$this->openView($data);
	}

	public function reset(){
		$this->index();
	}

	private function initialComboBox($projectId, $testCaseId, &$data){
		$data['projectCombo'] = $this->mProj->searchStartProjectCombobox();
		if(null != $projectId && !empty($projectId)){
			$criteria = (object) array('projectId' => $projectId);
			$testCaseList = $this->mVerMng->searchRelatedTestCases($criteria);
			$data['testCaseCombo'] = $testCaseList;
		}
		if(null != $testCaseId && !empty($testCaseId)){
			$criteria = (object) array('testCaseId' => $testCaseId);
			$testCaseVersionList = $this->mVerMng->searchRelatedTestCaseVersion($criteria);
			$data['testCaseVersionCombo'] = $testCaseVersionList;
		}
	}

	private function openView($data){
		$data['html']  = 'VersionManagement/testCaseVersionSearch_view';
		
		$data['active_title'] = 'versionManagement';
		$data['active_page'] = 'trns004';
		
		$this->load->view('template/header');
		$this->load->view('VersionManagement/bodyTestCaseVersion_view', $data);
		$this->load->view('template/footer');
	}

}

?>
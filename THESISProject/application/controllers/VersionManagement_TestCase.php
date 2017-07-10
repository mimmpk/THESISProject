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

	public function diffWithPreviousVersion(){
		$output = "";
		$testCaseDetailList = array();

		$testCaseId = $this->input->post('testCaseId');
		$testCaseVersionId = $this->input->post('testCaseVersionId');

		$criteria = (object) array(
			'testCaseId' => $testCaseId, 'testCaseVersionId' => $testCaseVersionId);
		$versionInfo = $this->mTC->searchTestCaseVersionInformationByCriteria($criteria);

		if(null != $versionInfo && !empty($versionInfo->previousVersionId)){

			$criteria->testCaseVersionId = $versionInfo->previousVersionId;
			$pVersionInfo = $this->mTC->searchTestCaseVersionInformationByCriteria($criteria);

			$param = (object) array(
				'testCaseId' 	=> $testCaseId,
				'cTargetDate' 	=> $versionInfo->effectiveStartDate,	//Newer 
				'pTargetDate'   => $pVersionInfo->effectiveStartDate);	//Older
			$testCaseDetailList = $this->mVerMng->searchDiffPreviousVersion_TestCase($param);

			$output .= '
			<div class="col-md-3">
				<b>Test Case ID:</b>
			</div>
			<div class="col-md-3">
				<span>'.$versionInfo->testCaseNo.'</span>
			</div>	
			<div class="col-md-3">
				<b>Previous Version:</b>
			</div>
			<div class="col-md-3">
				<span>'.$pVersionInfo->testCaseVersionNumber.'</span>
			</div>		
			<table class="table table-condensed">
				<tbody>
					<tr>
						<th></th>
						<th>Input Name</th>
						<th>Test Data</th>
					</tr>';
			foreach($testCaseDetailList as $value){
				$colorBg = "";
				if('newer' == $value['version'] && 2 == $value['_count']){
					$action = '&nbsp';
				}else if('newer' == $value['version'] && 1 == $value['_count']){
					$action = '<span class="label label-success">
					<i class="fa fa-plus"></i></span>';
					$colorBg = BG_COLOR_ADD;
				}else if('older' == $value['version'] && 1 == $value['_count']){
					$action = '<span class="label label-danger">
					<i class="fa fa-minus"></i></span>';
					$colorBg = BG_COLOR_DELETE;
				}
				$output .= '
				<tr bgcolor="'.$colorBg.'">
					<td>'.$action.'</td>
					<td>'.$value['refInputName'].'</td>
					<td>'.$value['testData'].'</td>
				</tr>';
			}
			$output .= '</tbody></table>';
		}
		echo $output;
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
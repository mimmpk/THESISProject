<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

/**
* Functional Requirement Version Management
*/
class VersionManagement_FnReq extends CI_Controller{
	
	function __construct(){
		parent::__construct();

		$this->load->model('Project_model', 'Project');
		$this->load->model('VersionManagement_model', 'mVerMng');
		$this->load->model('FunctionalRequirement_model', 'mFR');

		$this->load->library('form_validation', null, 'FValidate');
		$this->load->library('session');
	}

	public function index(){
		$data['projectCombo'] = $this->Project->searchStartProjectCombobox();

		$data['projectId'] = '';
		$data['fnReqId'] = '';
		$data['fnReqVersionId'] = '';

		$data['resultList'] = null;
		$this->openView($data, 'search');
	}

	public function getRelatedFnReq(){
		$output = '';
		$error_message = '';

		if(!empty($_POST)){
			$projectId = $this->input->post('projectId');

			$criteria = (object) array('projectId' => $projectId);
			$fnReqList = $this->mVerMng->searchRelatedFunctionalRequirements($criteria);

			$output .= "<option value=''>".PLEASE_SELECT."</option>";
			foreach($fnReqList as $value){
				$output .= "<option value='".$value['functionId']."'>".$value['functionNo'].": ".$value['functionDescription']."</option>";
			}
		}
		echo $output;
	}

	public function getRelatedFnReqVersion(){
		$output = '';
		if(!empty($_POST)){
			$projectId = $this->input->post('projectId');
			$fnReqId = $this->input->post('functionId');

			$criteria = (object) array('projectId' => $projectId, 'functionId' => $fnReqId);
			$fnReqVersionList = $this->mVerMng->searchRelatedFunctionalRequirementVersion($criteria);
			$output .= "<option value=''>".PLEASE_SELECT."</option>";
			foreach($fnReqVersionList as $value){
				$output .= "<option value='".$value['functionVersionId']."'>"."Version ".$value['functionVersionNumber']."</option>";
			}
		}
		echo $output;
	}

	public function search(){
		$resultList = array();
		$projectId = $this->input->post('inputProjectName');
		$fnReqId = $this->input->post('inputFnReq');
		$fnReqVersionId = $this->input->post('inputVersion');

		$this->FValidate->set_rules('inputProjectName', null, 'required');
		$this->FValidate->set_rules('inputFnReq', null, 'required');
		$this->FValidate->set_rules('inputVersion', null, 'required');

		if($this->FValidate->run()){
			$criteria = (object) array(
				'functionId' => $fnReqId, 'functionVersionId' => $fnReqVersionId);
			$versionInfo = $this->mFR->searchFunctionalRequirementVersionByCriteria($criteria);

			if(null != $versionInfo && 0 < count($versionInfo)){
				$param = (object) array(
					'projectId' 	=> $projectId, 
					'functionId' 	=> $fnReqId,
					'targetDate' 	=> $versionInfo->effectiveStartDate);
				$resultList = $this->mVerMng->searchFunctionalRequirementDetailsByVersion($param);
				$data['resultVersionInfo'] = $versionInfo;
			}	
		}

		$data['projectId'] = $projectId;
		$data['fnReqId'] = $fnReqId;
		$data['fnReqVersionId'] = $fnReqVersionId;

		$this->initialComboBox($projectId, $fnReqId, $data);

		$data['resultList'] = $resultList;
		$this->openView($data, 'search');
	}

	public function reset(){
		$this->index();
	}

	private function initialComboBox($projectId, $fnReqId, &$data){
		$data['projectCombo'] = $this->Project->searchStartProjectCombobox();
		if(null != $projectId && !empty($projectId)){
			$criteria = (object) array('projectId' => $projectId);
			$fnReqList = $this->mVerMng->searchRelatedFunctionalRequirements($criteria);
			$data['fnReqCombo'] = $fnReqList;
		}
		if(null != $fnReqId && !empty($fnReqId)){
			$criteria = (object) array('projectId' => $projectId, 'functionId' => $fnReqId);
			$fnReqVersionList = $this->mVerMng->searchRelatedFunctionalRequirementVersion($criteria);
			$data['fnReqVersionCombo'] = $fnReqVersionList;
		}
	}

	private function openView($data, $page){
		if('search' == $page){
			$data['html']  = 'VersionManagement/functionalRequirementsVersionSearch_view';
		}
		$data['active_title'] = 'versionManagement';
		$data['active_page'] = 'trns003';
		
		$this->load->view('template/header');
		$this->load->view('VersionManagement/functionalRequirementsVersion_view', $data);
		$this->load->view('template/footer');

		//$this->load->view('template/body', $data);
	}

}

?>
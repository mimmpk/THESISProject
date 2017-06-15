<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Version Management RTM
* Create Date: 2017-06-14
*/
class VersionManagement_RTM extends CI_Controller{
	
	function __construct(){
		parent::__construct();

		$this->load->model('Project_model', 'mProj');
		$this->load->model('RTM_model', 'mRTM');
		$this->load->model('VersionManagement_model', 'mVerMng');

		$this->load->library('form_validation', null, 'FValidate');
	}

	public function index(){
		$data['projectCombo'] = $this->mProj->searchStartProjectCombobox();

		$data['projectId'] = '';
		$data['rtmVersionId'] = '';

		$data['resultList'] = null;
		$this->openView($data);
	}

	public function getRelatedRTMVersion(){
		$output = '';
		if(!empty($_POST)){
			$versionList = $this->mVerMng->searchRelatedRTMVersion($this->input->post('projectId'));

			$output .= "<option value=''>".PLEASE_SELECT."</option>";
			foreach($versionList as $value){
				$output .= "<option value='".$value['rtmVersionId']."'>"."Version ".$value['rtmVersionNumber']."</option>";
			}
		}
		echo $output;
	}

	public function search(){
		$resultList = array();

		$projectId = $this->input->post('inputProjectName');
		$rtmVersionId = $this->input->post('inputVersion');

		$this->FValidate->set_rules('inputProjectName', null, 'required');
		$this->FValidate->set_rules('inputVersion', null, 'required');

		if($this->FValidate->run()){
			$criteria = (object) array('projectId' => $projectId, 'rtmVersionId' => $rtmVersionId);
			$versionInfo = $this->mRTM->searchRTMVersionInfo($criteria);

			if(null != $versionInfo && 0 < count($versionInfo)){
				$param = (object) array(
					'projectId' => $projectId, 
					'targetDate' => $versionInfo->effectiveStartDate);
				
				$resultList = $this->mVerMng->searchRTMDetailByVersion($param);
				//$data['resultVersionInfo'] = $versionInfo;
			}
		}
		$data['projectId'] = $projectId;
		$data['rtmVersionId'] = $rtmVersionId;

		$this->initialComboBox($projectId, $data);

		$data['resultList'] = $resultList;
		$this->openView($data);
	}

	public function reset(){
		$this->index();
	}

	private function initialComboBox($projectId, &$data){
		$data['projectCombo'] = $this->mProj->searchStartProjectCombobox();
		if(null != $projectId && !empty($projectId)){
			$versionList = $this->mVerMng->searchRelatedRTMVersion($projectId);
			$data['rtmVersionCombo'] = $versionList;
		}
	}

	private function openView($data){
		$data['html']  = 'VersionManagement/rtmVersionSearch_view';
		$data['active_title'] = 'versionManagement';
		$data['active_page'] = 'trns006';
		
		$this->load->view('template/header');
		$this->load->view('VersionManagement/bodyRTMVersion_view', $data);
		$this->load->view('template/footer');
	}
}
?>
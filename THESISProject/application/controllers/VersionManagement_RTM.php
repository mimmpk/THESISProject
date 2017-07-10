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

	public function diffWithPreviousVersion(){
		$output = "";
		$rtmDetailList = array();

		$projectId = $this->input->post('projectId');
		$rtmVersionId = $this->input->post('rtmVersionId');

		$criteria = (object) array('projectId' => $projectId, 'rtmVersionId' => $rtmVersionId);
		$versionInfo = $this->mRTM->searchRTMVersionInfo($criteria);
		
		if(null != $versionInfo && !empty($versionInfo->previousVersionId)){
			
			$criteria->rtmVersionId = $versionInfo->previousVersionId;
			$pVersionInfo = $this->mRTM->searchRTMVersionInfo($criteria);

			$param = (object) array(
				'projectId' => $projectId,
				'cTargetDate' => $versionInfo->effectiveStartDate,
				'pTargetDate' => $pVersionInfo->effectiveStartDate);
			$rtmDetailList = $this->mVerMng->searchDiffPreviousVersion_RTM($param);

			$output .= '
			<div class="col-md-3">
				<b>RTM Previous Version:</b>
			</div>
			<div class="col-md-9">
				<span class="label label-info" style="text-align:left;">'.$pVersionInfo->rtmVersionNumber.'</span>
			</div>
			<table class="table table-condensed">
				<tbody>
					<tr>
						<th style="width:10px;"></th>
						<th>Functional Requirements ID</th>
						<th>Test Case ID</th>
					</tr>';
			foreach($rtmDetailList as $value){
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
					<td>'.$value['functionNo'].'</td>
					<td>'.$value['testCaseNo'].'</td>
				</tr>';
			}
			$output .= '</tbody></table>';
		}
		echo $output;
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
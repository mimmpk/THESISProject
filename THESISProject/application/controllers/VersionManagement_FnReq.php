<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

/**
* Functional Requirement Version Management
* Create Date: 2017-06-08
*/
class VersionManagement_FnReq extends CI_Controller{
	
	function __construct(){
		parent::__construct();

		$this->load->model('Project_model', 'Project');
		$this->load->model('VersionManagement_model', 'mVerMng');
		$this->load->model('FunctionalRequirement_model', 'mFR');

		$this->load->library('form_validation', null, 'FValidate');
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

	public function diffWithPreviousVersion(){
		$output = "";
		$fnDetailList = array();

		$projectId = $this->input->post('projectId');
		$fnReqId = $this->input->post('functionId');
		$fnReqVersionId = $this->input->post('functionVersion');

		$criteria = (object) array(
				'functionId' => $fnReqId, 'functionVersionId' => $fnReqVersionId);
		$versionInfo = $this->mFR->searchFunctionalRequirementVersionByCriteria($criteria);
		if(null != $versionInfo->previousVersionId && !empty($versionInfo->previousVersionId)){
			
			$previousVersionId = $versionInfo->previousVersionId;

			$criteria = (object) array(
				'functionId' => $fnReqId, 'functionVersionId' => $previousVersionId);
			$pVersionInfo = $this->mFR->searchFunctionalRequirementVersionByCriteria($criteria);

			//Get Functional Requirements Detail
			$param = (object) array(
				'projectId' 	=> $projectId, 
				'functionId' 	=> $fnReqId,
				'cTargetDate' 	=> $versionInfo->effectiveStartDate,	//Newer 
				'pTargetDate'   => $pVersionInfo->effectiveStartDate);	//Older
			$fnDetailList = $this->mVerMng->searchDiffPreviousVersion_Requirements($param);

			$output .= '
				<table class="table table-condensed">
					<tbody>
						<tr>
							<th></th>
							<th>Input Name</th>
							<th>Data Type</th>
							<th>Data Length</th>
							<th>Scale</th>
							<th>Unique</th>
							<th>NOT NULL</th>
							<th>Default</th>
							<th>Min</th>
							<th>Max</th>
						</tr>';

			foreach($fnDetailList as $value){
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
					<td>'.$value['inputName'].'</td>
					<td>'.$value['dataType'].'</td>
					<td>'.$value['dataLength'].'</td>
					<td>'.$value['decimalPoint'].'</td>
					<td>'.$value['constraintUnique'].'</td>
					<td>'.$value['constraintNull'].'</td>
					<td>'.$value['constraintDefault'].'</td>
					<td>'.$value['constraintMinValue'].'</td>
					<td>'.$value['constraintMaxValue'].'</td>
				</tr>';
			}
			$output .= '</tbody></table>';		
		}

		echo $output;
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
		$this->load->view('VersionManagement/bodyFunctionalRequirementsVersion_view', $data);
		$this->load->view('template/footer');

		//$this->load->view('template/body', $data);
	}

}

?>
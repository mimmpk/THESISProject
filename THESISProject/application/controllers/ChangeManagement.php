<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
* Change Management Controller
**/
class ChangeManagement extends CI_Controller{
	
	function __construct(){
		parent::__construct();
		$this->load->model('Project_model', 'mProject');
		$this->load->model('FunctionalRequirement_model', 'mFR');
		$this->load->model('Miscellaneous_model', 'mMisc');
		$this->load->model('DatabaseSchema_model', 'mDB');

		$this->load->library('form_validation', null, 'FValidate');
		$this->load->library('session');
	}

	public function index(){
		$data['error_message'] = '';
		$data['resultList'] = null;
		$this->openView($data, 'search');
	}

	public function search(){
		$error_message = '';
		$result = null;

		$projectId = $this->input->post('inputProject');
		$this->FValidate->set_rules('inputProject', null, 'required');
		if($this->FValidate->run()){
			$param = (object) array('projectId' => $projectId, 'status' => ACTIVE_CODE);
			$result = $this->mFR->searchFunctionalRequirementHeaderInfo($param);
			
			if(0 == count($result)){
				$error_message = ER_MSG_006;
			}

			$data['selectedProjectId'] = $projectId;
		}
		$formObj = (object) array('projectId' => $projectId);
		$data['formData'] = $formObj;
		$data['functionList'] = $result;
		$data['error_message'] = $error_message;
		$this->openView($data, 'search');
	}

	function viewFunctionDetail($projectId, $functionId){
		$error_message = '';
		$resultHeader = array();
		$resultList = array();

		if(!empty($projectId) && !empty($functionId)){
			//search project information
			$projectInfo = $this->mProject->searchProjectDetail($projectId);
			$data['projectInfo'] = $projectInfo;

			$param = (object) array('projectId' => $projectId, 'functionId' => $functionId);
			$resultList = $this->mFR->searchFunctionalRequirementDetail($param);
			if(!empty($resultList)){
				$resultHeader = (object) array(
					'functionId' => $resultList[0]['functionId'],
					'functionNo' => $resultList[0]['functionNo'],
					'functionDescription' => $resultList[0]['functionDescription'],
					'functionVersionNumber' => $resultList[0]['functionVersionNumber']);
			}else{
				$error_message = ER_MSG_012;
			}
		}else{
			$error_message = ER_MSG_011;
		}

		$hfield = array('projectId' => $projectId, 'functionId' => $functionId);
		$data['hfield'] = $hfield;
		$data['resultHeader'] = $resultHeader;
		$data['resultDetail'] = $resultList;
		$data['error_message'] = $error_message;
		$this->openView($data, 'detail');
	}

	function viewFRInputDetail(){
		$output = '';
		$keyId = $this->input->post('keyId');
		//var_dump($keyId);
		if(null !== $keyId && !empty($keyId)){
			$keyList = explode("|", $keyId);

			$param = (object) array('projectId' => $keyList[0], 'inputId' => $keyList[1], 'schemaVersionId' => $keyList[2]);
			$result = $this->mFR->searchFunctionalRequirementDetail($param);

			if(0 < count($result)){
				$row = $result[0];

				$dataTypeList = $this->mMisc->searchMiscellaneous(MISC_DATA_INPUT_DATA_TYPE, '');
				$dataTypeCombo = '
					<select name="inputDataType" id="inputDataType" class="form-control">
						<option value="">--Select Data Type--</option>';
				foreach ($dataTypeList as $value) {
					$dataTypeCombo .= '<option value="'.$value['miscValue1'].'">'.$value['miscValue1'].'</option>';
				}
				$dataTypeCombo .= '</select>';

				$checkUnique = ($row["constraintUnique"] == "Y")? 'checked' : '';
				$checkNotNull = ($row["constraintNull"] == "Y")? 'checked' : '';

				//var_dump($row);
				$output = '
					<form method="post" id="changeInput_form">
						<table style="width:100%">
						<tr>
							<td>
								<label>Input Name: 
								<p class="text-green" style="margin:0;">'.$row["inputName"].'</p>
								</label>
							</td>
						</tr>
						<tr>
							<td>
								<label>Data Type: 
								<p class="text-green" style="margin:0;">'.$row["dataType"].'</p>
								</label>
							</td>
							<td>
								'.$dataTypeCombo.'
							</td>
						</tr>
						<tr>
							<td>
								<label>Data Length: 
								<p class="text-green" style="margin:0;">'.$row["dataLength"].'</p>
								</label>
							</td>
							<td>
								<input type="text" name="inputDataLength" class="form-control"/>
							</td>
						</tr>
						<tr>
							<td>
								
							</td>
							<td>
								<div class="checkbox">
									<label style="font-weight:700;">
									<input type="checkbox" name="inputUnique" '.$checkUnique.' >Unique
									<p class="text-green" style="margin:0;">'.$row["constraintUnique"].'</p>
									</label>
									&nbsp;&nbsp;
									
									<label style="font-weight:700;">
									<input type="checkbox" name="inputNotNull" '.$checkNotNull.' >NOT NULL
									<p class="text-green" style="margin:0;">'.$row["constraintNull"].'</p>
									</label>
								</div>
							</td>
						</tr>
						<tr height="41">
							<td>
								<label>Default Value:
								<p class="text-green" style="margin:0;">'.$row["constraintDefault"].'</p>
								</label>
							</td>
							<td>
								<input type="text" name="inputDefault" class="form-control"/>
							</td>
						</tr>
						<tr height="41">
							<td>
								<label>Min Value:
								<p class="text-green" style="margin:0;">'.$row["constraintMinValue"].'</p>
								</label>
							</td>
							<td>
								<input type="text" name="inputMinValue" class="form-control"/>
							</td>
						</tr>
						<tr height="41">
							<td>
								<label>Max Value:
								<p class="text-green" style="margin:0;">'.$row["constraintMaxValue"].'</p>
								</label>
							</td>
							<td>
								<input type="text" name="inputMaxValue" class="form-control"/>
							</td>
						</tr>
						</table>
				';

				$output .= '
					<br/>
				 	<div align="right">
					 	<button type="button" name="save" id="saveChange" class="btn btn-primary">
					 	<i class="fa fa-save"></i> Save
					 	</button>
				 	</div>
				 	</form>	
				';
			}
			echo $output;
		}
	}

	function getTables(){
		if(isset($_GET['term'])){
			$q = strtolower($_GET['term']);
			$result = $this->mDB->getTableByProjectId(19, $q);
			echo $result;
		}
	}

	private function openView($data, $view){
		if('search' == $view){
			$data['html'] = 'ChangeManagement/changeRequestSearch_view';
			$data['projectCombo'] = $this->mProject->searchStartProjectCombobox();
		}else{
			$data['html'] = 'ChangeManagement/changeRequestDetail_view';
		}
		
		$data['active_title'] = 'changeManagement';
		$data['active_page'] = 'trns001';
		$this->load->view('template/header');
		$this->load->view('template/body', $data);
		$this->load->view('template/footer');
	}
}

?>
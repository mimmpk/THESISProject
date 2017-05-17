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
		$this->load->model('ChangeManagement_model', 'mChange');
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
		$functionVersion = '';

		$resultHeader = array();
		$resultList = array();
		$inputChangeList = array();

		$userId = $this->session->userdata('userId');

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
				$functionVersion = $resultList[0]['functionVersionNumber'];

				$criteria = (object) array(
					'userId' => $userId,
					'functionId' => $resultList[0]['functionId'],
					'functionVersion' => $resultList[0]['functionVersionNumber']);

				$inputChangeList = $this->mChange->searchTempFRInputChangeList($criteria);

			}else{
				$error_message = ER_MSG_012;
			}
		}else{
			$error_message = ER_MSG_011;
		}

		$hfield = array('projectId' => $projectId, 'functionId' => $functionId, 'functionVersion' => $functionVersion);
		$data['hfield'] = $hfield;
		$data['error_message'] = $error_message;
		$data['resultHeader'] = $resultHeader;
		$data['resultDetail'] = $resultList;
		$data['inputChangeList'] = $inputChangeList;
		$this->openView($data, 'detail');
	}

	function addFRInputDetail(){
		$output = '';
		$functionId = $this->input->post('functionId');
		$functionVersion = $this->input->post('functionVersion');

		$param = array(
			'functionId' => $functionId,
			'functionVersionNumber' => $functionVersion,
			'inputId' => '',
			'inputName' => '',
			'schemaVersionId' => '',
			'inputName' => '',
			'dataType' => '',
			'dataLength' => '',
			'decimalPoint' => '',
			'constraintUnique' => '', 
			'constraintNull' => '',
			'constraintDefault' => '',
			'constraintMinValue' => '',
			'constraintMaxValue' => ''
		);

		$output = $this->setFRInputDetailForm($param);
		echo $output;

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
				$output = $this->setFRInputDetailForm($row);
			}
			echo $output;
		}
	}

	function setFRInputDetailForm($row){
		//set Data Type combo
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

		$output = '
			<input type="hidden" name="changeMode" id="changeMode" value="">
			<input type="hidden" name="changeFunctionId" value="'.$row["functionId"].'">
			<input type="hidden" name="changeFunctionVersion" value="'.$row["functionVersionNumber"].'">
			<input type="hidden" name="changeInputId" value="'.$row["inputId"].'">
			<input type="hidden" name="changeInputName" value="'.$row["inputName"].'">
			<input type="hidden" name="changeSchemaVersionId" value="'.$row["schemaVersionId"].'">

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
					<input type="text" name="inputDataLength" id="inputDataLength" class="form-control"/>
				</td>
			</tr>
			<tr>
				<td>
					<label>Scale (if any*)
					<p class="text-green" style="margin:0;">'.$row["decimalPoint"].'</p>
					</label>
				</td>
				<td>
					<input type="text" name="inputScale" id="inputScale" class="form-control" placeholder="Enter when data Type is \'Decimal\'"/>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<div class="checkbox">
						<label style="font-weight:700;">
						<input type="checkbox" id="inputUnique" name="inputUnique" '.$checkUnique.' >Unique
						<p class="text-green" style="margin:0;">'.$row["constraintUnique"].'</p>
						</label>
						<input type="hidden" id="oldUniqueValue" value="'.$row["constraintUnique"].'">
						
						&nbsp;&nbsp;
						
						<label style="font-weight:700;">
						<input type="checkbox" id="inputNotNull" name="inputNotNull" '.$checkNotNull.' >NOT NULL
						<p class="text-green" style="margin:0;">'.$row["constraintNull"].'</p>
						</label>
						<input type="hidden" id="oldNotNullValue" value="'.$row["constraintNull"].'">
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
					<input type="text" id="inputDefault" name="inputDefault" class="form-control"/>
				</td>
			</tr>
			<tr height="41">
				<td>
					<label>Min Value:
					<p class="text-green" style="margin:0;">'.$row["constraintMinValue"].'</p>
					</label>
				</td>
				<td>
					<input type="text" id="inputMinValue" name="inputMinValue" class="form-control"/>
				</td>
			</tr>
			<tr height="41">
				<td>
					<label>Max Value:
					<p class="text-green" style="margin:0;">'.$row["constraintMaxValue"].'</p>
					</label>
				</td>
				<td>
					<input type="text" id="inputMaxValue" name="inputMaxValue" class="form-control"/>
				</td>
			</tr>
			</table>';
		return $output;
	}

	function saveTempFRInput_edit(){
		$output = '';
		
		if(!empty($_POST)){

			try{
				$changeMode = $this->input->post('changeMode');

				$userId = $this->session->userdata('userId');
				$functionId = $this->input->post('changeFunctionId');
				$inputId = $this->input->post('changeInputId');
				$schemaVersionId = $this->input->post('changeSchemaVersionId');

				$functionVersion = $this->input->post('changeFunctionVersion');
				$inputName = $this->input->post('changeInputName');
				$dataType = $this->input->post('inputDataType');
				$dataLength = $this->input->post('inputDataLength');
				$scalePoint = $this->input->post('inputScale');
				$unique = $this->input->post('inputUnique');
				$notNull = $this->input->post('inputNotNull');
				$defaultValue = $this->input->post('inputDefault');
				$minValue = $this->input->post('inputMinValue');
				$maxValue = $this->input->post('inputMaxValue');
				

				$user = $this->session->userdata('username');
			
				//validate check exist
				$criteria = (object) array(
					'userId' => $userId, 
					'functionId' => $functionId,
					'functionVersion' => $functionVersion,
					'inputId' => $inputId,
					'schemaVersionId' => $schemaVersionId);
				$records = $this->mChange->searchTempFRInputChangeList($criteria);
				
				if(0 == count($records)){
					$param = (object) array(
						'userId' => $userId,
						'functionId' => $functionId,
						'functionVersion' => $functionVersion,
						'inputId' => $inputId,
						'inputName' => $inputName,
						'schemaVersionId' => $schemaVersionId,
						'dataType' => $dataType,
						'dataLength' => $dataLength,
						'scaleLength' => $scalePoint,
						'unique' => $unique,
						'notNull' => $notNull,
						'default' => $defaultValue,
						'min' => $minValue,
						'max' => $maxValue,
						'changeType' => CHANGE_TYPE_EDIT,
						'user' => $user);
					$saveResult = $this->mChange->insertTempFRInputChange($param);
					if($saveResult){
						//refresh Change List
						$output = $this->setInputChangeListData($userId, $functionId, $functionVersion);
					}else{
						$output = 'error|'.ER_MSG_013;
					}
				}else{
					//Error already change
					$output = 'error|'.ER_IMP_057;
				}
			}catch (Exception $e){
				$output = 'error|'.ER_MSG_013.'<br/>'.$e;
			}
		}else{
			$output = 'error|EMPTY $_POST';
		}
		echo $output;
	}

	function saveTempFRInput_delete(){
		$output = '';
		
		//var_dump($keyId);
		if(!empty($_POST)){
			$keyList = explode("|", $this->input->post('keyId'));

			$userId = $this->session->userdata('userId');
			$functionId = $this->input->post('functionId');
			$functionVersion = $this->input->post('functionVersion');
			$inputId = $keyList[1];
			$schemaVersionId = $keyList[2];

			$user = $this->session->userdata('username');

			//validate check exist
			$criteria = (object) array(
				'userId' => $userId, 
				'functionId' => $functionId,
				'functionVersion' => $functionVersion,
				'inputId' => $inputId,
				'schemaVersionId' => $schemaVersionId);
			$records = $this->mChange->searchTempFRInputChangeList($criteria);
			if(0 == count($records)){
				$inputInfo = $this->mFR->searchFRInputInfoByInputId($inputId);

				$param = (object) array(
					'userId' => $userId,
					'functionId' => $functionId,
					'functionVersion' => $functionVersion,
					'inputId' => $inputId,
					'inputName' => $inputInfo->inputName,
					'schemaVersionId' => $schemaVersionId,
					'changeType' => CHANGE_TYPE_DELETE,
					'user' => $user);
				$saveResult = $this->mChange->insertTempFRInputChange($param);
				if($saveResult){
					//refresh Change List
					$output = $this->setInputChangeListData($userId, $functionId, $functionVersion);
				}else{
					$output = 'error|'.ER_MSG_013;
				}
			}else{
				$output = 'error|'.ER_IMP_057;
			}
		}
		echo $output;

	}

	private function setInputChangeListData($userId, $functionId, $functionVersion){

		$criteria = (object) array(
			'userId' => $userId, 
			'functionId' => $functionId,
			'functionVersion' => $functionVersion);

		$lineNo = 1;
		$changeList = $this->mChange->searchTempFRInputChangeList($criteria);
		$inputChangeOutput = '
			<table class="table table-condensed">
			<tbody>
			<tr>
				<th>#</th>
				<th>Input Name</th>
				<th>Data Type</th>
				<th>Data Length</th>
				<th>Scale</th>
				<th>Unique</th>
				<th>NOT NULL</th>
				<th>Default value</th>
				<th>Min</th>
				<th>Max</th>
				<th>Change Type</th>
				<th></th>
			</tr>';
		foreach ($changeList as $value) {
			$inputChangeOutput .= '
			<tr>
					<td>'.$lineNo++.'</td>
					<td>'.$value['inputName'].'</td>
					<td>'.$value['newDataType'].'</td>
					<td>'.$value['newDataLength'].'</td>
					<td>'.$value['newScaleLength'].'</td>
					<td>'.$value['newUnique'].'</td>
					<td>'.$value['newNotNull'].'</td>
					<td>'.$value['newDefaultValue'].'</td>
					<td>'.$value['newMinValue'].'</td>
					<td>'.$value['newMaxValue'].'</td>
					<td>'.$value['changeType'].'</td>
					<td><span class="glyphicon glyphicon-trash"></span></td>
				</tr>';
		}
		$inputChangeOutput .= '</tbody></table>';
		return $inputChangeOutput;
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
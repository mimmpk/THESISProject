<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
* Change Management Controller
**/
class ChangeManagement extends CI_Controller{
	
	function __construct(){
		parent::__construct();
		$this->load->model('RTM_model', 'mRTM');
		$this->load->model('Project_model', 'mProject');
		$this->load->model('TestCase_model', 'mTestCase');
		$this->load->model('DatabaseSchema_model', 'mDB');
		$this->load->model('Miscellaneous_model', 'mMisc');
		$this->load->model('ChangeManagement_model', 'mChange');
		$this->load->model('FunctionalRequirement_model', 'mFR');

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
		$this->reloadPage('', $projectId, $functionId);
	}

	function requestChangeFRInputs(){
		$errorFlag = false;
		$success_message = '';
		$error_message = '';

		$functionNo = '';
		$userId = $this->session->userdata('userId');
		$projectId = $this->input->post('projectId');
		$functionId = $this->input->post('functionId');
		$functionVersion = $this->input->post('functionVersion');
		
		try{
			/** 1.Validate
			*** 1.1 Check Temp Change List Data
			**/
			$criteria = (object) array('userId' => $userId, 'functionId' => $functionId, 'functionVersion' => $functionVersion);
			$resultExistTempChangeList = $this->mChange->searchTempFRInputChangeList($criteria);
			if(null == $resultExistTempChangeList || 0 == count($resultExistTempChangeList)){
				$error_message = ER_TRN_010;
				$this->reloadPage($error_message, $projectId, $functionId, $functionVersion);
				return false;
			}

			/* 1.2 Check status of Requirement Header */
			$criteria->status = '1';
			$resultReqHeader = $this->mFR->searchFunctionalRequirementHeaderInfo($criteria);
			if(null == $resultReqHeader || 0 == count($resultReqHeader)){
				$error_message = ER_TRN_011;
				$this->reloadPage($error_message, $projectId, $functionId, $functionVersion);
				return false;
			}else{
				$functionNo = $resultReqHeader[0]['functionNo'];
			}

			/** 2.Call Change API */
			$param = (object) array(
				'projectId' => $projectId,
				'functionId' => $functionId,
				'functionNo' => $functionNo,
				'functionVersion' => $functionVersion
				);
			$changeResult = $this->callChangeAPI($param);
			
			/** 3.Control Version*/
			if(null != $changeResult && !empty($changeResult)){
				$user = $this->session->userdata('username');

				$projectInfo = $this->mProject->searchProjectDetail($projectId);
				$result = $this->mChange->controlVersionChangedData($changeResult, $projectInfo, $user, $error_message);

				if($result){
					$success_message = "callChangeAPI";
				}

			}else{
				$errorFlag = true;
				$error_message = ER_MSG_016;
			}
			
			/** 4.Save Change Request */


			/** 5.Save Change History */


			/** 6.Display Result */

			
		}catch(Exception $e){
			$errorFlag = true;
			$error_message = $e;
		}

		$data['success_message'] = $success_message;
		$data['error_message'] = $error_message;
		$this->openView($data, 'result');
		//$this->reloadPage($error_message, $projectId, $functionId, $functionVersion);		
	}

	function reloadPage($errorMessage, $projectId, $functionId, $functionVersion = ''){
		$resultHeader = array();
		$resultList = array();
		$inputChangeList = array();

		$userId = $this->session->userdata('userId');
		if(!empty($projectId) && !empty($functionId)){
			$projectInfo = $this->mProject->searchProjectDetail($projectId);
			$data['projectInfo'] = $projectInfo;

			$param = (object) array('projectId' => $projectId, 'functionId' => $functionId);
			$resultList = $this->mFR->searchFunctionalRequirementDetail($param);

			if(null != $resultList && 0 < count($resultList)){
				$resultHeader = (object) array(
				'functionId' => $resultList[0]['functionId'],
				'functionNo' => $resultList[0]['functionNo'],
				'functionDescription' => $resultList[0]['functionDescription'],
				'functionVersionNumber' => $resultList[0]['functionVersionNumber']);

				$functionVersion = (!empty($functionVersion)? $functionVersion : $resultList[0]['functionVersionNumber']);

				//get temp change list
				$criteria = (object) array('userId' => $userId, 'functionId' => $functionId, 'functionVersion' => $functionVersion);
				$inputChangeList = $this->mChange->searchTempFRInputChangeList($criteria);
			}else{
				$error_message = ER_MSG_012;
			}
		}else{
			$error_message = ER_MSG_011;
		}

		$hfield = array('projectId' => $projectId, 'functionId' => $functionId, 'functionVersion' => $functionVersion);
		$data['hfield'] = $hfield;
		$data['error_message'] = $errorMessage;
		$data['resultHeader'] = $resultHeader;
		$data['resultDetail'] = $resultList;
		$data['inputChangeList'] = $inputChangeList;
		$this->openView($data, 'detail');
	}

	function addFRInputDetail(){
		$output = '';
		$projectId = $this->input->post('projectId');
		$functionId = $this->input->post('functionId');
		$functionVersion = $this->input->post('functionVersion');

		$param = array(
			'projectId' => $projectId,
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
			'constraintMaxValue' => '',
			'tableName' => '',
			'columnName' => ''
		);

		$output = $this->setFRInputDetailForm($param, CHANGE_TYPE_ADD);
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
				$output = $this->setFRInputDetailForm($row, CHANGE_TYPE_EDIT);
			}
			echo $output;
		}
	}

	function setFRInputDetailForm($row, $mode){
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

		$displayFlag = (CHANGE_TYPE_ADD == $mode)? 'block': 'none';
		$requiredField = (CHANGE_TYPE_ADD == $mode)? '<span style="color:red;">*</span>': '';

		$output = '
			<input type="hidden" name="changeProjectId" value="'.$row["projectId"].'">
			<input type="hidden" name="changeType" id="changeType" value="'.$mode.'">
			<input type="hidden" name="changeFunctionId" value="'.$row["functionId"].'">
			<input type="hidden" name="changeFunctionVersion" value="'.$row["functionVersionNumber"].'">
			<input type="hidden" name="changeInputId" value="'.$row["inputId"].'">
			<input type="hidden" name="changeSchemaVersionId" value="'.$row["schemaVersionId"].'">

			<input type="hidden" name="oldDataType" 	value="'.$row["dataType"].'">
			<input type="hidden" name="oldDataLength" 	value="'.$row["dataLength"].'">
			<input type="hidden" name="oldScale" 		value="'.$row["decimalPoint"].'">
			<input type="hidden" name="oldDefaultValue" value="'.$row["constraintDefault"].'">
			<input type="hidden" name="oldMin" 			value="'.$row["constraintMinValue"].'">
			<input type="hidden" name="oldMax" 			value="'.$row["constraintMaxValue"].'">
			<input type="hidden" id="oldNotNullValue" name="oldNotNullValue" value="'.$row["constraintNull"].'">
			<input type="hidden" id="oldUniqueValue" name="oldUniqueValue" value="'.$row["constraintUnique"].'">

			<table style="width:100%">
			<tr height="40">
				<td>
					<label>Input Name: '.$requiredField.'
					<p class="text-green" style="margin:0;">'.$row["inputName"].'</p>
					</label>
				</td>
				<td>
					<input type="text" name="inputName" id="inputName" class="form-control" value="'.$row["inputName"].'" style="display:'.$displayFlag.'" maxlength="'.MAX_INPUT_NAME.'" />
				</td>	
			</tr>
			<tr height="40">
				<td>
					<label>Data Type: '.$requiredField.'
					<p class="text-green" style="margin:0;">'.$row["dataType"].'</p>
					</label>
				</td>
				<td>
					'.$dataTypeCombo.'
				</td>
			</tr>
			<tr height="40">
				<td>
					<label>Data Length: 
					<p class="text-green" style="margin:0;">'.$row["dataLength"].'</p>
					</label>
				</td>
				<td>
					<input type="number" min="1" step="1" name="inputDataLength" id="inputDataLength" class="form-control"/>
				</td>
			</tr>
			<tr height="40">
				<td>
					<label>Scale (if any*)
					<p class="text-green" style="margin:0;">'.$row["decimalPoint"].'</p>
					</label>
				</td>
				<td>
					<input type="number" min="1" step="1" name="inputScale" id="inputScale" class="form-control" placeholder="Enter when data Type is \'Decimal\'"/>
				</td>
			</tr>
			<tr height="40">
				<td>&nbsp;</td>
				<td>
					<div class="checkbox">
						<label style="font-weight:700;">
						<input type="checkbox" id="inputUnique" name="inputUnique[]" value="Y" '.$checkUnique.' >Unique
						<p class="text-green" style="margin:0;">'.$row["constraintUnique"].'</p>
						</label>

						&nbsp;&nbsp;
						
						<label style="font-weight:700;">
						<input type="checkbox" id="inputNotNull" name="inputNotNull[]" value="Y" '.$checkNotNull.' >NOT NULL
						<p class="text-green" style="margin:0;">'.$row["constraintNull"].'</p>
						</label>
					</div>
				</td>
			</tr>
			<tr height="40">
				<td>
					<label>Default Value:
					<p class="text-green" style="margin:0;">'.$row["constraintDefault"].'</p>
					</label>
				</td>
				<td>
					<input type="text" id="inputDefault" name="inputDefault" class="form-control"/>
				</td>
			</tr>
			<tr height="40">
				<td>
					<label>Min Value:
					<p class="text-green" style="margin:0;">'.$row["constraintMinValue"].'</p>
					</label>
				</td>
				<td>
					<input type="number" step="1" id="inputMinValue" name="inputMinValue" class="form-control"/>
				</td>
			</tr>
			<tr height="40">
				<td>
					<label>Max Value:
					<p class="text-green" style="margin:0;">'.$row["constraintMaxValue"].'</p>
					</label>
				</td>
				<td>
					<input type="number" step="1" id="inputMaxValue" name="inputMaxValue" class="form-control"/>
				</td>
			</tr>
			<tr height="40">
				<td>
					<label>Table Name: '.$requiredField.'
					<p class="text-green" style="margin:0;">'.$row["tableName"].'</p>
					</label>
				</td>
				<td>
					<input type="text" id="inputTableName" name="inputTableName" class="form-control" style="display:'.$displayFlag.'"/>
				</td>
			</tr>
			<tr height="40">
				<td>
					<label>Column Name: '.$requiredField.'
					<p class="text-green" style="margin:0;">'.$row["columnName"].'</p>
					</label>
				</td>
				<td>
					<input type="text" id="inputColumnName" name="inputColumnName" class="form-control" style="display:'.$displayFlag.'"/>
				</td>
			</tr>
			</table>';
		return $output;
	}

	function saveTempFRInput_edit(){
		$output = '';
		$error_message = '';
		
		if(!empty($_POST)){

			try{
				$changeType = $this->input->post('changeType');

				$userId = $this->session->userdata('userId');
				$functionId = $this->input->post('changeFunctionId');
				$functionVersion = $this->input->post('changeFunctionVersion');
				$inputId = $this->input->post('changeInputId');
				$schemaVersionId = $this->input->post('changeSchemaVersionId');

				$inputName = trim($this->input->post('inputName'));
				$dataType = $this->input->post('inputDataType');
				$dataLength = $this->input->post('inputDataLength');
				$scalePoint = $this->input->post('inputScale');
				$unique = $this->input->post('inputUnique');
				$notNull = $this->input->post('inputNotNull');
				$defaultValue = trim($this->input->post('inputDefault'));
				$minValue = $this->input->post('inputMinValue');
				$maxValue = $this->input->post('inputMaxValue');
				$tableName = trim($this->input->post('inputTableName'));
				$columnName = trim($this->input->post('inputColumnName'));

				$oldUnique = $this->input->post('oldUniqueValue');
				$oldNotNull = $this->input->post('oldNotNullValue');

				$unique = empty($unique)? "N": "Y";
				$notNull = empty($notNull)? "N": "Y";

				$user = $this->session->userdata('username');

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
					'table' => $tableName,
					'column' => $columnName,
					'changeType' => '',
					'user' => $user);

				if(CHANGE_TYPE_ADD == $changeType){ 
					//*******Change Type: Add
					//Validate
					$projectId = $this->input->post('changeProjectId');
					$resultValidate = $this->validateNewFRInput($projectId, $param, $error_message);
					
					if($resultValidate){
						//Save
						$param->changeType = CHANGE_TYPE_ADD;
						$saveResult = $this->mChange->insertTempFRInputChange($param);
						if($saveResult){
							//refresh Change List
							$output = $this->setInputChangeListData($userId, $functionId, $functionVersion);
						}else{
							$output = 'error|'.ER_MSG_013;
						}
						
					}else{
						$output = 'error|'.$error_message;
					}

				}else{ 
					//*******Change Type: Edit
					//validate duplicate 
					$oldDataType = $this->input->post('oldDataType');
					$oldDataLength = $this->input->post('oldDataLength');
					$oldScale = $this->input->post('oldScale');
					$oldDefaultValue = $this->input->post('oldDefaultValue');
					$oldMinValue = $this->input->post('oldMin');
					$oldMaxValue = $this->input->post('oldMax');

					if($oldDataType == $dataType
						|| (!empty($oldDataLength) && !empty($dataLength) && ((int)$dataLength == (int)$oldDataLength))
						|| (!empty($oldScale) && !empty($scalePoint) && ((int)$scalePoint == (int)$oldScale))
						|| (!empty($oldDefaultValue) && !empty($defaultValue) && ($oldDefaultValue == $defaultValue))
						|| (!empty($oldMinValue) && !empty($minValue) && ((int)$oldMinValue == (int)$minValue))
						|| (!empty($oldMaxValue) && !empty($maxValue) && ((int)$oldMaxValue == (int)$maxValue)))
					{
						echo 'error|'.ER_TRN_009;
						return false;
					}

					//validate check exist
					$criteria = (object) array(
						'userId' => $userId, 
						'functionId' => $functionId,
						'functionVersion' => $functionVersion,
						'inputId' => $inputId,
						'schemaVersionId' => $schemaVersionId);
					$records = $this->mChange->searchTempFRInputChangeList($criteria);
					
					if(0 == count($records)){
						$param->unique = ($unique == $oldUnique)? "": $unique;
						$param->notNull = ($notNull == $oldNotNull)? "": $notNull;
						$param->changeType = CHANGE_TYPE_EDIT;
						$saveResult = $this->mChange->insertTempFRInputChange($param);
						if($saveResult){
							//refresh Change List
							$output = $this->setInputChangeListData($userId, $functionId, $functionVersion);
						}else{
							$output = 'error|'.ER_MSG_013;
						}
					}else{
						//Error already change
						$output = 'error|'.ER_TRN_001;
					}
				}
			}catch (Exception $e){
				$output = 'error|'.ER_MSG_013.'<br/>'.$e;
			}
		}
		echo $output;
		return false;
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
				$output = 'error|'.ER_TRN_001;
			}
		}
		echo $output;
	}

	function deleteTempFRInputList(){
		$output = '';

		if(!empty($_POST)){
			$userId = $this->session->userdata('userId');
			$functionId = $this->input->post('functionId');
			$functionVersion = $this->input->post('functionVersion');
			$lineNumber = $this->input->post('lineNumber');

			$param = (object) array('userId' => $userId,
				'functionId' => $functionId,
				'functionVersion' => $functionVersion,
				'lineNumber' => $lineNumber);
			$recordDelete = $this->mChange->deleteTempFRInputChangeList($param);
			if(0 < $recordDelete){
				//refresh Change List
				$output = $this->setInputChangeListData($userId, $functionId, $functionVersion);
			}else{
				$output = 'error|'.ER_MSG_015;
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
				<th style="text-align: center;">Change Type</th>
				<th></th>
			</tr>';
		foreach ($changeList as $value) {
			$labelType = ('add' == $value['changeType'])? 'label-success': (('edit' == $value['changeType'])? 'label-warning' : 'label-danger');

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
				<td style="text-align: center;">
					<small class="label '.$labelType.'">
						'.$value['changeType'].'
					</small>
				</td>
				<td>
					<span id="'.$value['lineNumber'].'" class="glyphicon glyphicon-trash deleteTmpFRInputChg">
					</span>
				</td>
			</tr>';
		}
		$inputChangeOutput .= '</tbody></table>';
		return $inputChangeOutput;
	}

	private function validateNewFRInput($projectId, &$param, &$errorMsg){
		//validate
		$this->load->library('common');
		
		$correctFRInput = false;
		$inputId = '';

		//1. Check Existing in Selected Functional Requirement
		$resultExist = $this->mFR->searchExistFRInputInFunctionalRequirement($param);
		if(0 < count($resultExist)){
			$errorMsg = ER_TRN_005;
			return false;
		}

		//2. Check Existing in Current Change List
		$criteria = (object) array(
			'userId' => $param->userId, 
			'functionId' => $param->functionId,
			'functionVersion' => $param->functionVersion,
			'inputName' => $param->inputName,
			'table' => $param->table,
			'column' => $param->column);
		$resultExist = $this->mChange->searchTempFRInputChangeList($criteria);
		if(0 < count($resultExist)){
			$errorMsg = ER_TRN_006;
			return false;
		}

		//3. Check input name match with table & column or not? (REQ INPUT DB)
		$resultInputInfo = $this->mFR->searchFRInputInformation($projectId, $param->inputName);
		if(0 < count($resultInputInfo)){
			$referTableName = $resultInputInfo->refTableName;
			$referColumnName = $resultInputInfo->refColumnName;
			
			if($referTableName != strtoupper($tableName)
				|| $referColumnName != strtoupper($columnName)){
				$errorMsg = ER_TRN_004;
				return false;
			}else{
				$param->inputId = $resultInputInfo->inputId;
				$correctFRInput = TRUE;
			}
		}

		//4. Check table & column name match with input data (REQ INPUT DB)
		if(!$correctFRInput){
			$resultInputInfo = $this->mFR->searchExistFRInputsByTableAndColumnName(
				$param->table, $param->column, $projectId);
			if(0 <  count($resultInputInfo)){
				if($param->inputName == $resultInputInfo->inputName){
					$param->inputId = $resultInputInfo->inputId;
					$correctFRInput = TRUE;
				}else{
					$errorMsg = ER_TRN_005;
					return false;
				}
			}
		}

		//5. Check table & column name match with Schema data (DB SCHEMA)
		$resultSchemaInfo = $this->mDB->searchExistDatabaseSchemaInfo($param->table, $param->column, $projectId);
		if(null != $resultSchemaInfo && !empty($resultSchemaInfo)){
			//5.1 Validate against Existing FR Input
			if($param->dataType !== $resultSchemaInfo->dataType 
				|| $param->dataLength !== $this->common->nullToEmpty($resultSchemaInfo->dataLength)
				|| $param->scaleLength !== $this->common->nullToEmpty($resultSchemaInfo->decimalPoint)
				|| $param->unique !== $resultSchemaInfo->constraintUnique
				|| $param->notNull !== $resultSchemaInfo->constraintNull
				|| $param->default !== $this->common->nullToEmpty($resultSchemaInfo->constraintDefault)
				|| $param->min !== $this->common->nullToEmpty($resultSchemaInfo->constraintMinValue)
				|| $param->max !==  $this->common->nullToEmpty($resultSchemaInfo->constraintMaxValue))
			{
				$errorMsg = ER_TRN_007;
				return false;
			}
		}

		//6. Validate New FR Input and Schema Info
		$exceptInputSize = array("date", "datetime", "int", "float", "real");
		//6.1 [Validate DataType]
		if(!in_array($param->dataType, $exceptInputSize)){
			if(empty($param->dataLength)){
				$errorMsg = ER_TRN_008;
				return false;
			}else{
				$dataLength = (int)$param->dataLength;
				if("char" == $param->dataType || "varchar" == $param->dataType){
					if($dataLength < 1 || $dataLength > 8000){
						$errorMsg = ER_IMP_015;
						return false;
					}
				}else if("nchar" == $param->dataType || "nvarchar" == $param->dataType){
					if($dataLength< 1 || $dataLength > 4000 ){
						$errorMsg = ER_IMP_016;
						return false;
					}
				}else{
					if($dataLength < 1 || $dataLength > 38){
						$errorMsg = ER_IMP_017;
						return false;
					}else{
						//6.2 [Validate Scale] for Decimal Type. If NULL, default value will be '0'.
						if(!empty($param->scaleLength)){
							$scale = (int)$param->scaleLength;
							if($scale < 0 || $scale > $dataLength){
								$errorMsg = ER_IMP_018;
								return false;
							}
						}else{
							$param->scaleLength = 0;
						}
					}
				}
			}
		}

		$dataTypeCategory = '';
		$result = $this->mMisc->searchMiscellaneous(MISC_DATA_INPUT_DATA_TYPE, $param->dataType);
		if(null != $result && !empty($result)){
			$dataTypeCategory = $result[0]['miscValue2'];
		}else{
			$errorMsg = ER_MSG_014;
			return false;
		}
		
		//6.3 [Validate Default Value]
		if(!empty($param->default)){
			if(DATA_TYPE_CATEGORY_NUMERICS == $dataTypeCategory){
				if(!is_numeric($param->default)){
					$errorMsg = ER_IMP_021;
					return false;
				}else if('decimal' == $param->dataType && is_float((float)$param->default)){
					$decimalFotmat = explode(".", $param->default);
					if(strlen($decimalFotmat[0]) > $param->dataLength){
						$errorMsg = ER_IMP_021;
					return false;
					}
				}	
			}else if(DATA_TYPE_CATEGORY_STRINGS == $dataTypeCategory
				&& strlen($param->default) > $param->dataLength){
				$errorMsg = ER_IMP_021;
				return false;
			}else{
				// Date Type
				if("getdate()" != strtolower($param->default)){
					$errorMsg = ER_IMP_021;
					return false;
				}
			}
		}

		//6.4 [Validate Min Value]
		if(!empty($param->min) && DATA_TYPE_CATEGORY_NUMERICS != $dataTypeCategory){
			$errorMsg = ER_IMP_024;
			return false;
		}

		//6.5 [Validate Max Value]
		if(!empty($param->max) && DATA_TYPE_CATEGORY_NUMERICS != $dataTypeCategory){
			$errorMsg = ER_IMP_025;
			return false;
		}

		if(!empty($param->min) && !empty($param->max)){
			if((float)$param->min > (float)$param->max){
				$errorMsg = ER_IMP_035;
				return false;
			}
		}

		return true;
	}

	private function callChangeAPI($param){
		$passData = array();
		$allFRHeader = array();
		$allFRDetail = array();
		$allTCHeader = array();
		$allTCDetail = array();
		$allRTM = array();
		$changeList = array();

		$userId = $this->session->userdata('userId');

		//1.Project Information
		$projectInfo = $this->mProject->searchProjectDetail($param->projectId);
		$passData['projectInfo'] = $param->projectId;
		$passData['connectDatabaseInfo'] = array(
			'databaseName' => $projectInfo->databaseName, 
			'hostname' => $projectInfo->hostname, 
			'port' => $projectInfo->port, 
			'username' => $projectInfo->username, 
			'password' => $projectInfo->password);
		
		//2. All Functional Requirements Header data
		$criteria = (object) array('projectId' => $param->projectId, 'status' => '1');
		$frHeaderList = $this->mFR->searchFunctionalRequirementHeaderInfo($criteria);
		foreach($frHeaderList as $value){
			$allFRHeader[$value['functionNo']] = array(
				'functionVersion' => $value['functionVersion'], 
				'functionDesc' => $value['fnDesc']);
		}
		$passData['FRHeader'] = $allFRHeader;

		//3. All Functional Requirements Detail data
		$functionNo = '';
		$frDetailList = $this->mFR->searchFunctionalRequirementDetail($criteria);
		foreach($frDetailList as $value){
			$allFRDetail[$value['functionNo']][$value['inputName']] = array( 
				'dataType' => $value['dataType'],
				'dataLength' => $value['dataLength'],
				'scale' => $value['decimalPoint'],
				'unique' => $value['constraintUnique'],
				'notNull' => $value['constraintNull'],
				'default' => $value['constraintDefault'],
				'min' => $value['constraintMinValue'],
				'max' => $value['constraintMaxValue'],
				'tabelName' => $value['tableName'],
				'columnName' => $value['columnName']);
		}
		$passData['FRDetail'] = $allFRDetail;

		//4. All Test Case Header data
		$tcHeaderList = $this->mTestCase->searchTestCaseInfoByCriteria($param->projectId, '1');
		foreach($tcHeaderList as $value){
			$allTCHeader[$value['testCaseNo']] = array(
				'testCaseVersion' => $value['testCaseVersion'], 
				'testCaseDesc' => $value['testCaseDescription'],
				'expectedResult' => $value['expectedResult']);
		}
		$passData['TCHeader'] = $allTCHeader;
		
		//5. All Test Case Detail data
		$tcDetailList = $this->mTestCase->searchExistTestCaseDetail($param->projectId);
		foreach ($tcDetailList as $value) {
			$allTCDetail[$value['testCaseNo']][$value['refInputName']] = $value['testData'];
		}
		$passData['TCDetail'] = $allTCDetail;


		//6. All RTM data
		$rtmList = $this->mRTM->searchRTMInfoByCriteria($param->projectId);
		foreach($rtmList as $value){
			$allRTM = array('functionNo' => $value['functionNo'], 'testCaseNo' => $value['testCaseNo']);
		}
		$passData['RTM'] = $allRTM;

		//7. Change Request Information
		$criteria->userId = $userId;
		$criteria->functionId = $param->functionId;
		$criteria->functionVersion = $param->functionVersion;
		$tempChangeFRInputsList = $this->mChange->searchTempFRInputChangeList($criteria);
		$changeList = array('functionNo' => $param->functionNo, 'functionVersion' => $param->functionVersion);
		foreach($tempChangeFRInputsList as $value){
			$changeList['inputs'][] = array(
				'changeType' => $value['changeType'],
				'inputName' => $value['inputName'],
				'dataType' => $value['newDataType'],
				'dataLength' => $value['newDataLength'],
				'scale' => $value['newScaleLength'],
				'unique' => $value['newUnique'],
				'notNull' => $value['newNotNull'],
				'default' => $value['newDefaultValue'],
				'min' => $value['newMinValue'],
				'max' => $value['newMaxValue'],
				'tableName' => $value['tableName'],
				'columnName' => $value['columnName'] 
			);
		}
		$passData['changeRequestInfo'] = $changeList;

		$url = 'http://localhost/StubService/ChangeAPI.php';

		$json = json_decode($this->postCURL($url, $passData));
	
		return $json;

		//echo '<br><hr><h2>'.$this->postCURL($url, $passData).'</h2><br><hr><br>';
	}

	private function postCURL($_url, $_param){        
        $this->http_build_query_for_curl($_param, $postData);
        
        //test
       	//var_dump($postData);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: multipart/form-data"));

        //test
        //echo (is_callable('curl_init')) ? '<h1>Enabled</h1>' : '<h1>Not enabled</h1>' ;
        
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    private function http_build_query_for_curl($arrays, &$new = array(), $prefix = null) {
	    if ( is_object( $arrays ) ) {
	        $arrays = get_object_vars( $arrays );
	    }

	    foreach ( $arrays AS $key => $value ) {
	        $k = isset( $prefix ) ? $prefix . '[' . $key . ']' : $key;
	        if ( is_array( $value ) OR is_object( $value )  ) {
	            $this->http_build_query_for_curl( $value, $new, $k );
	        } else {
	            $new[$k] = $value;
	        }
	    }
	}

	private function openView($data, $view){
		if('search' == $view){
			$data['html'] = 'ChangeManagement/changeRequestSearch_view';
			$data['projectCombo'] = $this->mProject->searchStartProjectCombobox();
		}else if('result' == $view){
			$data['html'] = 'ChangeManagement/changeRequestResult_view';
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
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
* Change Management Model
*/
class ChangeManagement_model extends CI_Model{
	
	function __construct(){
		parent::__construct();

		$this->load->model('FunctionalRequirement_model', 'mFR');
		$this->load->model('DatabaseSchema_model', 'mDB');
		$this->load->model('Miscellaneous_model', 'mMisc');
		$this->load->model('TestCase_model', 'mTestCase');
		$this->load->model('RTM_model', 'mRTM');
		$this->load->model('Common_model', 'mCommon');
	}

	function searchTempFRInputChangeList($param){

		if(!empty($param->userId)){
			$where[] = "userId = $param->userId";
		}

		if(!empty($param->functionId)){
			$where[] = "functionId = $param->functionId";
		}

		if(!empty($param->functionVersion)){
			$where[] = "functionVersion = $param->functionVersion";
		}

		if(!empty($param->inputId)){
			$where[] = "inputId = $param->inputId";
		}

		if(!empty($param->schemaVersionId)){
			$where[] = "schemaVersionId = $param->schemaVersionId";
		}

		//For Adding new input
		if(!empty($param->inputName) && !empty($param->table) && !empty($param->column)){
			$where[] = "((inputName = '$param->inputName') 
				OR (tableName = '$param->table' AND columnName = '$param->column'))";
		}

		$where_clause = implode(' AND ', $where);

		$sqlStr = "SELECT *
			FROM T_TEMP_CHANGE_LIST
			WHERE $where_clause
			ORDER BY lineNumber";
		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}

	function deleteTempFRInputChangeList($param){
		if(!empty($param->userId)){
			$where[] = "userId = $param->userId";
		}

		if(!empty($param->functionId)){
			$where[] = "functionId = $param->functionId";
		}

		if(!empty($param->functionVersion)){
			$where[] = "functionVersion = $param->functionVersion";
		}


		if(!empty($param->lineNumber)){
			$where[] = "lineNumber = $param->lineNumber";
		}

		$where_condition = implode(' AND ', $where);

		$sqlStr = "DELETE FROM T_TEMP_CHANGE_LIST
			WHERE $where_condition";
		$result = $this->db->query($sqlStr);
		return $this->db->affected_rows();
	}

	function insertTempFRInputChange($param){
		$currentDateTime = date('Y-m-d H:i:s');

		$inputId = !empty($param->inputId)? $param->inputId : "NULL";
		$schemaVersionId = !empty($param->schemaVersionId)? $param->schemaVersionId : "NULL";
		$dataType = !empty($param->dataType)? "'".$param->dataType."'" : "NULL";
		$dataLength = !empty($param->dataLength)? $param->dataLength : "NULL";
		$scale = !empty($param->scaleLength)? $param->scaleLength : "NULL";
		$unique = !empty($param->unique)? "'".$param->unique."'" : "NULL";
		$notNull = !empty($param->notNull)? "'".$param->notNull."'" : "NULL";
		$default = !empty($param->default)? "'".$param->default."'" : "NULL";
		$min = !empty($param->min)? "'".$param->min."'" : "NULL";
		$max = !empty($param->max)? "'".$param->max."'" : "NULL";
		$tableName = !empty($param->table)? "'".$param->table."'" : "NULL";
		$columnName = !empty($param->column)? "'".$param->column."'" : "NULL";

		$sqlStr = "INSERT INTO T_TEMP_CHANGE_LIST (userId, functionId, functionVersion, inputId, inputName, schemaVersionId, newDataType, newDataLength, newScaleLength, newUnique, newNotNull, newDefaultValue, newMinValue, newMaxValue, tableName, columnName, changeType, createUser, createDate) 
			VALUES (
				$param->userId, 
				$param->functionId,
				$param->functionVersion,
				$inputId,
				'$param->inputName',
				$schemaVersionId,
				$dataType,
				$dataLength,
				$scale,
				$unique,
				$notNull,
				$default,
				$min,
				$max,
				$tableName,
				$columnName,
				'$param->changeType', '$param->user', '$currentDateTime')";

		$result = $this->db->query($sqlStr);
		return $result;
	}

	function insertChangeRequestHeader($param){
		
		$sqlStr = "INSERT INTO T_CHANGE_REQUEST_HEADER (changeRequestNo, changeUserId, changeDate, projectId, changeFunctionId, changeFunctionNo, changeFunctionVersion, changeStatus, createUser, createDate, updateUser, updateDate) VALUES ('$param->changeRequestNo', $param->changeUser, '$param->changeDate', $param->projectId, $param->changeFunctionId, '$param->changeFunctionNo', '$param->changeFunctionVersion', '$param->changeStatus', '$param->user', '$param->currentDate', '$param->user', '$param->currentDate')";
		
		$result = $this->db->query($sqlStr);
		return $result;
	}

	function insertChangeRequestDetail($param){

		$inputId = !empty($param->inputId)? $param->inputId : "NULL";
		$schemaVersionId = !empty($param->schemaVersionId)? $param->schemaVersionId : "NULL";

		$dataType = !empty($param->dataType)? "'".$param->dataType."'" : "NULL";
		$dataLength = !empty($param->dataLength)? $param->dataLength : "NULL";
		$scale = !empty($param->scale)? $param->scale : "NULL";
		$unique = !empty($param->unique)? "'".$param->unique."'" : "NULL";
		$notNull = !empty($param->notNull)? "'".$param->notNull."'" : "NULL";
		$default = !empty($param->default)? "'".$param->default."'" : "NULL";
		$min = !empty($param->min)? "'".$param->min."'" : "NULL";
		$max = !empty($param->max)? "'".$param->max."'" : "NULL";
		$tableName = !empty($param->tableName)? "'".$param->tableName."'" : "NULL";
		$columnName = !empty($param->columnName)? "'".$param->columnName."'" : "NULL";

		$sqlStr = "INSERT INTO T_CHANGE_REQUEST_DETAIL (changeRequestNo, sequenceNo, changeType, refInputId, refSchemaVersionId, inputName, dataType, dataLength, scale, constraintUnique, constraintNotNull, constraintDefault, constraintMin, constraintMax, refTableName, refColumnName) VALUES ('$param->changeRequestNo', $param->sequenceNo, '$param->changeType', $inputId, $schemaVersionId, '$param->inputName', $dataType, $dataLength, $scale, $unique, $notNull, $default, $min, $max, $tableName, $columnName)";
		$result = $this->db->query($sqlStr);
		return $result;
	}

	function insertChangeHistory_RequirementsHeader($param){
		$sqlStr = "INSERT INTO T_CHANGE_HISTORY_REQ_HEADER (changeRequestNo, functionId, functionNo, oldFunctionVersion, newFunctionVersion) VALUES ('$param->changeRequestNo', $param->functionId, '$param->functionNo', $param->oldFnVersionNumber, $param->newFnVersionNumber)";
		$result = $this->db->query($sqlStr);
		if($result){
			$query = $this->db->query("SELECT IDENT_CURRENT('T_CHANGE_HISTORY_REQ_HEADER') as last_id");
			$resultId = $query->result();
			return $resultId[0]->last_id;
		}
		return NULL;
	}

	function insertChangeHistory_RequirementsDetail($param){
		$sqlStr = "INSERT INTO T_CHANGE_HISTORY_REQ_DETAIL (fnReqHistoryId, sequenceNo, changeType, inputName, refTableName, refColumnName) VALUES ($param->fnReqHistoryId, $param->sequenceNo, '$param->changeType', '$param->inputName', '$param->refTableName', '$param->refColumnName')";
		$result = $this->db->query($sqlStr);
		return $result;
	}

	function insertChangeHistory_Schema($param){
		$oldVersionNumber = !empty($param->oldSchemaVersionNumber)? $param->oldSchemaVersionNumber : "NULL";
		$newVersionNumber = !empty($param->newSchemaVersionNumber)? $param->newSchemaVersionNumber : "NULL";

		$sqlStr = "INSERT INTO T_CHANGE_HISTORY_SCHEMA (changeRequestNo, sequenceNo, tableName, columnName, oldSchemaVersionNumber, newSchemaVersionNumber, changeType) VALUES ('$param->changeRequestNo', $param->sequenceNo, '$param->tableName', '$param->columnName', $oldVersionNumber, $newVersionNumber, '$param->changeType') ";
		$result = $this->db->query($sqlStr);
		return $result;
	}

	function insertChangeHistory_TestCase($param){
		$oldTCVerNumber = !empty($param->oldTestCaseVersionNo)? $param->oldTestCaseVersionNo : "NULL";
		$newTCVerNumber = !empty($param->newTestCaseVersionNo)? $param->newTestCaseVersionNo : "NULL";

		$sqlStr = "INSERT INTO T_CHANGE_HISTORY_TESTCASE (changeRequestNo, testCaseId, testCaseNo, oldTestCaseVersionNumber, newTestCaseVersionNumber, changeType) VALUES ('$param->changeRequestNo', $param->testCaseId, '$param->testCaseNo', $oldTCVerNumber, $newTCVerNumber, '$param->changeType')";
		$result = $this->db->query($sqlStr);
		return $result;
	}

	function insertChangeHistory_RTMHeader($param){
		$sqlStr = "INSERT INTO T_CHANGE_HISTORY_RTM_HEADER (changeRequestNo, projectId, oldVersionNumber, newVersionNumber) VALUES ('$param->changeRequestNo', $param->projectId, $param->oldVersionNumber, $param->newVersionNumber)";
		$result = $this->db->query($sqlStr);
		if($result){
			$query = $this->db->query("SELECT IDENT_CURRENT('T_CHANGE_HISTORY_RTM_HEADER') as last_id");
			$resultId = $query->result();
			return $resultId[0]->last_id;
		}
		return NULL;
	}

	function updateChangeRequestHeader($param){
		$sqlStr = "UPDATE T_CHANGE_REQUEST_HEADER
			SET changeStatus = '$param->status',
				reason 		 = '$param->reason',
				updateDate 	 = '$param->updateDate',
				updateUser 	 = '$param->user'
			WHERE changeRequestNo = '$param->changeRequestNo'
			AND updateDate = '$param->updateDateCondition'";
		$result = $this->db->query($sqlStr);
		return $this->db->affected_rows();
	}

	function insertChangeHistory_RTMDetail($param){
		$sqlStr = "INSERT INTO T_CHANGE_HISTORY_RTM_DETAIL (rtmHistoryId, sequenceNo, functionId, testCaseId, changeType) VALUES ($param->rtmHistoryId, $param->sequenceNo, $param->functionId, $param->testCaseId, '$param->changeType')";
		$result = $this->db->query($sqlStr);
		return $result;
	}

	function getLastDatabaseSchemaVersion($projectId, $tableName, $columnName){
		$sqlStr = "SELECT schemaVersionId, schemaVersionNumber, updateDate
			FROM M_DATABASE_SCHEMA_VERSION
			WHERE activeFlag = '1'
			AND projectId = $projectId
			AND tableName = '$tableName'
			AND columnName = '$columnName'";
		$result = $this->db->query($sqlStr);
		return $result->row();
	}

	function getLastFunctionalRequirementVersion($functionId){
		$sqlStr = "SELECT functionVersionId, functionVersionNumber, updateDate
			FROM M_FN_REQ_VERSION
			WHERE functionId = $functionId
			AND activeFlag = '1'";

		$result = $this->db->query($sqlStr);
		return $result->row();
	}

	function getLastTestCaseVersion($projectId, $testCaseNo, $testCaseVersionNo){
		$sqlStr = "SELECT h.testCaseId, v.testCaseVersionId, v.testCaseVersionNumber, v.updateDate
			FROM M_TESTCASE_HEADER h
			INNER JOIN M_TESTCASE_VERSION v
			ON h.testCaseId = v.testCaseId
			WHERE v.activeFlag = '1'
			AND h.projectId = $projectId
			AND h.testCaseNo = '$testCaseNo'
			AND v.testCaseVersionNumber = $testCaseVersionNo";
		$result = $this->db->query($sqlStr);	
		return $result->row();
	}

	function getLastRTMVersion($projectId){
		$sqlStr = "SELECT 
				rtmVersionId,
				rtmVersionNumber,
				updateDate
			FROM M_RTM_VERSION 
			WHERE projectId = $projectId
			AND activeFlag = '1'";
		$result = $this->db->query($sqlStr);	
		return $result->row();
	}

	function getChangeRequestInformation($changeRequestNo){
		$sqlStr = "SELECT 
				h.changeRequestNo,
				CONVERT(nvarchar, h.changeDate, 103) as changeDate,
				CONCAT(u.firstname, '   ', u.lastname) as changeUser,
				h.changeStatus,
				h.changeFunctionId,
				h.changeFunctionNo,
				h.changeFunctionVersion,
				fh.functionDescription,
				h.updateDate
			FROM T_CHANGE_REQUEST_HEADER h 
			INNER JOIN M_USERS u 
			ON h.changeUserId = u.userId 
			INNER JOIN M_FN_REQ_HEADER fh 
			ON h.changeFunctionId = fh.functionId 
			WHERE h.changeRequestNo = '$changeRequestNo'";
		$result = $this->db->query($sqlStr);
		return $result->first_row();
	}

	function getChangeRequestInputList($changeRequestNo){
		$sqlStr = "SELECT *
			FROM T_CHANGE_REQUEST_DETAIL
			WHERE changeRequestNo = '$changeRequestNo' 
			ORDER BY sequenceNo";
		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}

	function getChangeHistoryFnReqHeaderList($changeRequestNo){
		$sqlStr = "SELECT c.*, f.functionDescription 
			FROM T_CHANGE_HISTORY_REQ_HEADER c 
			INNER JOIN M_FN_REQ_HEADER f 
			ON c.functionId = f.functionId
			WHERE c.changeRequestNo = '$changeRequestNo' 
			ORDER BY c.functionNo";
		$result = $this->db->query($sqlStr);
		return $result->result_array();	
	}

	function getChangeHistoryFnReqDetailList($fnReqHistoryId){
		$sqlStr = "SELECT *
			FROM T_CHANGE_HISTORY_REQ_DETAIL
			WHERE fnReqHistoryId = $fnReqHistoryId
			ORDER BY sequenceNo";
		$result = $this->db->query($sqlStr);
		return $result->result_array();	
	}

	function getChangeHistoryDatabaseSchemaList($changeRequestNo){
		$sqlStr = "SELECT * FROM T_CHANGE_HISTORY_SCHEMA 
			WHERE changeRequestNo = '$changeRequestNo'
			AND changeType <> ''
			ORDER BY tableName, columnName";
		$result = $this->db->query($sqlStr);
		return $result->result_array();	
	}

	function getChangeHistoryTestCaseList($changeRequestNo){
		$sqlStr = "SELECT ht.*, rh.functionNo  
			FROM T_CHANGE_HISTORY_TESTCASE ht
			INNER JOIN M_RTM r
			ON ht.testCaseId = r.testCaseId
			INNER JOIN M_FN_REQ_HEADER rh
			ON r.functionId = rh.functionId
			WHERE ht.changeRequestNo = '$changeRequestNo'
			ORDER BY ht.testCaseNo";
		$result = $this->db->query($sqlStr);
		return $result->result_array();	
	}

	function getChangeHistoryRTM($changeRequestNo){
		$sqlStr = "SELECT 
				h.changeRequestNo, 
				h.oldVersionNumber,
				h.newVersionNumber,
				d.testCaseId,
				t.testCaseNo,
				d.functionId,
				r.functionNo,
				d.changeType 
			FROM T_CHANGE_HISTORY_RTM_HEADER h
			INNER JOIN T_CHANGE_HISTORY_RTM_DETAIL d
			ON h.rtmHistoryId = d.rtmHistoryId 
			INNER JOIN M_FN_REQ_HEADER r
			ON d.functionId = r.functionId
			INNER JOIN M_TESTCASE_HEADER t
			ON d.testCaseId = t.testCaseId
			WHERE h.changeRequestNo = '$changeRequestNo'
			ORDER BY r.functionNo, t.testCaseNo";
		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}

	function getChangeHistoryRTMDetail($changeRequestNo){
		$sqlStr = "SELECT h.changeRequestNo, h.projectId, h.oldVersionNumber, h.newVersionNumber, d.functionId, d.testCaseId, d.changeType 
			FROM T_CHANGE_HISTORY_RTM_HEADER h
			INNER JOIN T_CHANGE_HISTORY_RTM_DETAIL d
			ON h.rtmHistoryId = d.rtmHistoryId
			WHERE h.changeRequestNo = '$changeRequestNo'";
		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}

	function changeProcess($changeInfo, &$changeResult, $connectionDB, $user, &$error_message, &$changeRequestNo){
		$this->db->trans_begin();

		$resultSuccess = $this->controlVersionCaseChangeRequest($changeResult, $connectionDB, $user, $error_message);

		if($resultSuccess){
			//Save ChangeRequest & ChangeHistory
			$resultSuccess = $this->saveChangeRequestInformation($changeInfo, $changeResult, $user, $error_message, $changeRequestNo);
		}

    	$trans_status = $this->db->trans_status();
	    if($trans_status == FALSE || !$resultSuccess){
	    	$this->db->trans_rollback();
	    	return false;
	    }else{
	   		$this->db->trans_commit();
	   		return true;
	    }
	}

	private function controlVersionCaseChangeRequest(&$changeResult, $connectionDB, $user, &$error_message){
		//$this->db->trans_start(); //Starting Transaction
		
		$errorFlag = false;
		$affectedProjectId = $changeResult->projectInfo;
		$affectedRequirements = $changeResult->affectedRequirement;
		$affectedSchemaList = $changeResult->affectedSchema;
		$affectedTestCase = $changeResult->affectedTestCase;
		$affectedRTM = $changeResult->affectedRTM;

		$newCurrentDate = date('Y-m-d H:i:s');

		//**[Version Control of Database Schema]
		foreach($affectedSchemaList as $value){
			$oldDBVersionNumber = '';

			$lastDBVersionInfo = $this->getLastDatabaseSchemaVersion($affectedProjectId, $value->tableName, $value->columnName);
			
			if(empty($value->affectedAction)){
				if(0 == count($lastDBVersionInfo)){
					$newDBVersionNumber = INITIAL_VERSION;
				}else{
					$value->oldSchemaVersionNo = $lastDBVersionInfo->schemaVersionNumber;
					$value->newSchemaVersionNo = '';
					continue;
				}
			}else{
				if(0 < count($lastDBVersionInfo)){
					$oldDBVersionId = $lastDBVersionInfo->schemaVersionId;
					$oldDBVersionNumber = $lastDBVersionInfo->schemaVersionNumber;
					$oldDBUpdateDate = $lastDBVersionInfo->updateDate;

					//update old version
					$dbParam = (object) array(
						'effectiveEndDate' => $newCurrentDate,
						'tableName' => $value->tableName,
						'columnName' => $value->columnName,
						'currentDate' => $newCurrentDate,
						'activeFlag' => UNACTIVE_CODE,
						'user' => $user,
						'projectId' => $affectedProjectId,
						'oldSchemaVersionId' => $oldDBVersionId,
						'oldUpdateDate' => $oldDBUpdateDate);
					$rowUpdate = $this->mDB->updateDatabaseSchemaVersion($dbParam);
					if(0 == $rowUpdate){
						$errorFlag = true;
						$error_message = ER_MSG_016;
						break;
					}
					$newDBVersionNumber = (int)$oldDBVersionNumber + 1;
				}else{
					$newDBVersionNumber = INITIAL_VERSION;
				}
			}

			$value->oldSchemaVersionNo = $oldDBVersionNumber;
			$value->newSchemaVersionNo = $newDBVersionNumber;

			//insert database schema data
			$dbParam = (object) array(
					'projectId' => $affectedProjectId,
					'tableName' => $value->tableName,
					'columnName' => $value->columnName,
					'schemaVersionNo' => $newDBVersionNumber,
					'previousVersionId' => $oldDBVersionId,
					'status' => ACTIVE_CODE);
			$resultInsert = $this->mDB->insertDatabaseSchemaVersion($dbParam, $user);
			if(null != $resultInsert){
				$schemaVersionId = $resultInsert;
			}else{
				$errorFlag = true;
				$error_message = ER_MSG_016;
				break;
			}

			//insert database schema detail**(get direct from database target)
			$dbSchemaDetail = $this->mDB->getSchemaFromDatabaseTarget($connectionDB, $value->tableName, $value->columnName);
			if(!empty($dbSchemaDetail)){
				$dataLength = '';
				$scaleLength = '';
				$defaultValue = '';

				$miscResult = $this->mMisc->searchMiscellaneous(MISC_DATA_INPUT_DATA_TYPE, $dbSchemaDetail['dataType']);
				$dataTypeCategory = $miscResult[0]['miscValue2'];
				if(DATA_TYPE_CATEGORY_STRINGS == $dataTypeCategory){
					$dataLength = $dbSchemaDetail['charecterLength'];
				}else if(DATA_TYPE_CATEGORY_NUMERICS == $dataTypeCategory){
					if("decimal" == $dbSchemaDetail['dataType']){
						$dataLength = $dbSchemaDetail['numericPrecision'];
						$scaleLength = $dbSchemaDetail['numericScale'];
					}
				}

				$defaultValue = $dbSchemaDetail['columnDefault'];
				if(!empty($dbSchemaDetail['columnDefault'])){
					if(DATA_TYPE_CATEGORY_DATE == $dataTypeCategory){
						$defaultValue = substr($defaultValue, 1, -1);
					}else{
						$defaultValue = str_replace('(', '', $defaultValue);
						$defaultValue = str_replace(')', '', $defaultValue);
					}
				}

				$param = (object) array(
					'tableName' => $dbSchemaDetail['tableName'],
					'columnName' => $dbSchemaDetail['columnName'],
					'schemaVersionId' => $schemaVersionId,
					'dataType' => $dbSchemaDetail['dataType'],
					'dataLength' => $dataLength,
					'scale' => $scaleLength,
					'defaultValue' => $defaultValue,
					'minValue' => $dbSchemaDetail['minValue'],
					'maxValue' => $dbSchemaDetail['maxValue'],
					'primaryKey' => $dbSchemaDetail['isPrimaryKey'],
					'unique' => $dbSchemaDetail['isUnique'],
					'null' => $dbSchemaDetail['isNotNull']);
				$resultInsert = $this->mDB->insertDatabaseSchemaInfo($param, $affectedProjectId);
			}else{
				$errorFlag = true;
				$error_message = ER_TRN_013;
				break;
			}
		}//endforeach; (database schema)

		//**[Version Control of Functional Requirements]
		if(!$errorFlag){
		foreach($affectedRequirements as $keyFunctionNo => $functionInfoVal){
			$keyFunctionId = '';

			$existFR = $this->mFR->searchExistFunctionalRequirement($keyFunctionNo, $affectedProjectId);

			if(null != $existFR && !empty($existFR)){
				$oldFunctionId = $existFR[0]['functionId'];
			}else{
				$errorFlag = true;
				$error_message = ER_MSG_016;
				break;
			}

			//3.1 get latest function version id
			$oldFnVersionNumber = $functionInfoVal->functionVersion;
			$resultLastFRInfo = $this->getLastFunctionalRequirementVersion($oldFunctionId, $oldFnVersionNumber);

			if(null == $resultLastFRInfo || 0 == count($resultLastFRInfo)){
				$errorFlag = true;
				$error_message = ER_TRN_012;
				break;
			}

			$oldFRVersionId = $resultLastFRInfo->functionVersionId;
			$oldFRVersionUpdateDate = $resultLastFRInfo->updateDate;
			$newFRVersionNumber = (int)$resultLastFRInfo->functionVersionNumber + 1;

			//3.1.1 Create new version of function
			$param = (object) array(
				'functionId' => $oldFunctionId,
				'functionVersionNo' => $newFRVersionNumber,
				'effectiveStartDate' => $newCurrentDate,
				'effectiveEndDate' => '',
				'activeFlag' => ACTIVE_CODE,
				'previousVersionId' => $oldFRVersionId,
				'currentDate' => $newCurrentDate,
				'user' => $user);
			$newFRVersionId = $this->mFR->insertFRVersion($param);
			
			//3.1.2 Update disabled previous version
			$param->effectiveEndDate = $newCurrentDate;
			$param->activeFlag = UNACTIVE_CODE;
			//condition
			$param->oldFunctionVersionId = $oldFRVersionId;
			$param->oldUpdateDate = $oldFRVersionUpdateDate;

			$rowUpdate = $this->mFR->updateFunctionalRequirementsVersion($param);
			if(0 == $rowUpdate){
				$errorFlag = true;
				$error_message = ER_MSG_016;
				break;
			}

			//3.2 Create new input of FR information(case: never has input before.)
			foreach($functionInfoVal->functionInput as $keyInputName => $value){

				$resultExistInput = $this->mFR->searchFRInputInformation($affectedProjectId, $keyInputName);
				if(null == $resultExistInput || 0 == count($resultExistInput)){
					//insert new input
					$paramFRInput = (object) array(
						'projectId' => $affectedProjectId,
						'inputName' => $keyInputName,
						'referTableName' => $value->refTableName,
						'referColumnName' => $value->refColumnName,
						'user' => $user );
					$resultInputId = $this->mFR->insertFRInput($paramFRInput);
					$inputId = $resultInputId;
				}else{
					$inputId = $resultExistInput->inputId;

					$paramFRInputCondition = (object) array(
						'functionId' => $oldFunctionId,
						'inputName' => $keyInputName);
					$resultFRInput = $this->mFR->searchExistFRInputInFunctionalRequirement($paramFRInputCondition);
					$oldSchemaVersionId = $resultFRInput[0]['schemaVersionId'];
				}

				//find latest version of reference schema that related with function's input
				$resultSchemaInfo = $this->mDB->searchExistDatabaseSchemaInfo($value->refTableName, $value->refColumnName, $affectedProjectId);

				$schemaVersionId = $resultSchemaInfo->schemaVersionId;

				if("add" == $value->changeType || "edit" == $value->changeType){
					//insert new version input detail
					$paramFRDetail = (object) array(
						'inputId' => $inputId,
						'schemaVersionId' => $schemaVersionId,
						'effectiveStartDate' => $newCurrentDate,
						'effectiveEndDate' => '',
						'activeFlag' => ACTIVE_CODE,
						'user' => $user);
					//insertFRDetail
					$resultInsert = $this->mFR->insertFRDetail($oldFunctionId, $paramFRDetail);
				}

				if("edit" == $value->changeType || "delete" == $value->changeType){
					//update disabled input
					$paramFRDetail = (object) array(
						'user' => $user,
						'activeFlag' => UNACTIVE_CODE,
						'currentDate' => $newCurrentDate,
						'effectiveEndDate' => $newCurrentDate,
						'functionId' => $oldFunctionId,
						'inputId' => $inputId,
						'oldSchemaVersionId' => $oldSchemaVersionId);
					$resultUpdate = $this->mFR->updateFunctionalRequirementsDetail($paramFRDetail);
					if(0 == $resultUpdate){
						$errorFlag = true;
						$error_message = ER_MSG_016;
						break 2;
					}
				}
			}
		}//endforeach; (functional requirement)
		}
		
		//**[Version Control of Test Cases]
		if(!$errorFlag){
		foreach($affectedTestCase as $keyTestCaseNo => $testcaseInfoVal){
			$testCaseId = '';
			$oldTCVersionNumber = '';
			$newTCVersionNumber = '';

			if(CHANGE_TYPE_ADD == $testcaseInfoVal->changeType){
				//insert new TEST CASE header
				$param = (object) array(
					'testCaseNo' => $keyTestCaseNo,
					'testCaseDescription' => $testcaseInfoVal->testCaseDesc,
					'expectedResult' => $testcaseInfoVal->expectedResult,
					'projectId' => $affectedProjectId);
				$testCaseId = $this->mTestCase->insertTestCaseHeader($param, $user);
				if(null == $testCaseId){
					$errorFlag = true;
					$error_message = ER_MSG_016;
					break;
				}
				$oldTCVersionId = '';
				$newTCVersionNumber = INITIAL_VERSION;
			}else{
				$resultLastTCVersion = $this->getLastTestCaseVersion($affectedProjectId, $keyTestCaseNo, $testcaseInfoVal->testCaseVersion);
				if(null == $resultLastTCVersion || 0 == count($resultLastTCVersion)){
					$errorFlag = true;
					$error_message = ER_MSG_016;
					break;
				}

				$testCaseId = $resultLastTCVersion->testCaseId;
				$oldTCVersionId = $resultLastTCVersion->testCaseVersionId;
				$oldTCVersionNumber = (int)$resultLastTCVersion->testCaseVersionNumber;
				$oldUpdateDate = $resultLastTCVersion->updateDate;
			}

			$testcaseInfoVal->testCaseId = $testCaseId;
			$testcaseInfoVal->oldVerNO = $oldTCVersionNumber;
			$testcaseInfoVal->newVerNO = $newTCVersionNumber;

			//Insert Test Case Version.
			if(CHANGE_TYPE_ADD == $testcaseInfoVal->changeType 
				|| CHANGE_TYPE_EDIT == $testcaseInfoVal->changeType){

				$newTCVersionNumber = $oldTCVersionNumber + 1;

				$paramInsert = (object) array(
					'testCaseId' 		 => $testCaseId,
					'initialVersionNo' 	 => $newTCVersionNumber,
					'effectiveStartDate' => $newCurrentDate,
					'previousVersionId'  => $oldTCVersionId,
					'activeStatus' 		 => ACTIVE_CODE);
				$result = $this->mTestCase->insertTestCaseVersion($paramInsert, $user);
			}
			
			//Disabled Old Test Case Version.
			if(CHANGE_TYPE_EDIT == $testcaseInfoVal->changeType 
				|| CHANGE_TYPE_DELETE == $testcaseInfoVal->changeType){
				$paramUpdate = (object) array(
					'effectiveEndDate' 	=> $newCurrentDate,
					'activeFlag' 		=> UNACTIVE_CODE,
					'updateDate' 		=> $newCurrentDate,
					'updateUser' 		=> $user,
					'testCaseId' 		=> $testCaseId,
					'testCaseVersionId' => $oldTCVersionId,
					'updateDateCondition' 	=> $oldUpdateDate);
				$rowUpdate = $this->mTestCase->updateTestCaseVersion($paramUpdate);
				if(0 == $rowUpdate){
					$errorFlag = true;
					$error_message = ER_MSG_016;
					break;
				}
			}

			//Insert or Update Test Case Detail
			foreach($testcaseInfoVal->testCaseDetails as $keyInputName => $value){

				$resultInputInfo = $this->mFR->searchFRInputInformation($affectedProjectId, $keyInputName);
				if(null == $resultInputInfo || 0 == count($resultInputInfo)){
					$errorFlag = true;
					$error_message = ER_MSG_016;
					break 2;
				}

				if(CHANGE_TYPE_EDIT == $value->changeType 
					|| CHANGE_TYPE_DELETE == $value->changeType){
					$paramUpdateDetail = (object) array(
						'effectiveEndDate' 	=> $newCurrentDate, 
						'activeFlag' 		=> UNACTIVE_CODE, 
						'updateDate' 		=> $newCurrentDate, 
						'updateUser' 		=> $user, 
						'testCaseId' 		=> $testCaseId, 
						'inputId' 			=> $resultInputInfo->inputId, 
						'activeFlagCondition' => ACTIVE_CODE);

					$rowUpdate =  $this->mTestCase->updateTestCaseDetail($paramUpdateDetail);
					If(0 == $rowUpdate){
						$errorFlag = true;
						$error_message = ER_MSG_016;
						break;
					}
				}

				if(CHANGE_TYPE_ADD == $value->changeType 
					|| CHANGE_TYPE_EDIT == $value->changeType){
					$paramInsertDetail = (object) array(
						'testCaseId' 	=> $testCaseId, 
						'refInputId' 	=> $resultInputInfo->inputId, 
						'refInputName' 	=> $keyInputName, 
						'testData' 		=> $value->testData, 
						'effectiveStartDate' => $newCurrentDate, 
						'activeStatus' 	=> ACTIVE_CODE);
					$resultInsert = $this->mTestCase->insertTestCaseDetail($paramInsertDetail, $user);
				}
			}
		}//endforeach; (test case)
		}

		//**[Version Control of RTM]
		if(!$errorFlag && !empty($affectedRTM)){
		
		//Get Latest RTM Info
		$resultLastRTMInfo = $this->getLastRTMVersion($affectedProjectId);
		$newRTMVersionNumber = (int)$resultLastRTMInfo->rtmVersionNumber + 1;
		$oldRtmVersionId = $resultLastRTMInfo->rtmVersionId;
		$oldRtmUpdateDate = $resultLastRTMInfo->updateDate;

		$affectedRTM->oldRTMVerNO = $resultLastRTMInfo->rtmVersionNumber;
		$affectedRTM->newRTMVerNO = $newRTMVersionNumber;

		//Update Disabled Old RTM Version
		$paramUpdate = (object) array(
			'effectiveEndDate' => $newCurrentDate,
			'activeFlag' => UNACTIVE_CODE,
			'updateDate' => $newCurrentDate,
			'user' => $user,
			'rtmVersionIdCondition' => $oldRtmVersionId,
			'projectId' => $affectedProjectId,
			'updateDateCondition' => $oldRtmUpdateDate);

		$rowUpdate = $this->mRTM->updateRTMVersion($paramUpdate);
		if(1 != $rowUpdate){
			$errorFlag = true;
			$error_message = ER_MSG_016;
			break;
		}

		//Insert New RTM Version
		$paramInsert = (object) array(
			'projectId' 		 => $affectedProjectId,
			'versionNo' 	 	 => $newRTMVersionNumber,
			'effectiveStartDate' => $newCurrentDate, 
			'activeFlag' 		 => ACTIVE_CODE,
			'previousVersionId'  => $oldRtmVersionId);

		$this->mRTM->insertRTMVersion($paramInsert, $user);

		foreach($affectedRTM->details as $value){
			$functionId = "";
			$testCaseId = "";

			//get Functional Requirement Info
			$resultFRInfo = $this->mFR->searchExistFunctionalRequirement($value->functionNo, $affectedProjectId);
			if(null == $resultFRInfo || 0 == count($resultFRInfo)){
				$errorFlag = true;
				$error_message = ER_MSG_016;
				break;
			}

			$functionId = $resultFRInfo[0]['functionId'];

			//get Test Case Info
			$resultTCInfo = $this->mTestCase->searchExistTestCaseHeader($affectedProjectId, $value->testCaseNo);
			if(null == $resultTCInfo || 0 == count($resultTCInfo)){
				$errorFlag = true;
				$error_message = ER_MSG_016;
				break;
			}

			$testCaseId = $resultTCInfo->testCaseId;

			//set Function Id & Test case Id
			$value->functionId = $functionId;
			$value->testCaseId = $testCaseId;

			//Insert RTM Info
			if(CHANGE_TYPE_ADD == $value->changeType){
				$paramInsert = (object) array(
					'projectId' 			=> $affectedProjectId,
					'functionId' 			=> $functionId,
					'testCaseId' 			=> $testCaseId,
					'effectiveStartDate' 	=> $newCurrentDate,
					'activeFlag' 			=> ACTIVE_CODE);
				$this->mRTM->insertRTMInfo($paramInsert, $user);
			}

			//Update RTM Info
			if(CHANGE_TYPE_DELETE == $value->changeType){
				$paramUpdate = (object) array(
					'effectiveEndDate'  => $newCurrentDate,
					'activeFlag' 		=> UNACTIVE_CODE,
					'updateDate' 		=> $newCurrentDate,
					'user' 				=> $user,
					'projectId' 		=> $affectedProjectId,
					'functionId' 		=> $functionId,
					'testCaseId' 		=> $testCaseId);
				$rowUpdate = $this->mRTM->updateRTMInfo($paramUpdate);
				if(1 != $rowUpdate){
					$errorFlag = true;
					$error_message = ER_MSG_016;
					break;
				}
			}
		}
		}

		//return result;
		if($errorFlag) {
			return false;
		}else{
			return true;
		}
		
	}

	private function saveChangeRequestInformation($changeInfo, $changeResult, $user, &$error_message, &$changeRequestNo = ''){
		
		$newCurrentDate = date('Y-m-d H:i:s');

		$affectedProjectId = $changeResult->projectInfo;
		$affectedRequirements = $changeResult->affectedRequirement;
		$affectedSchemaList = $changeResult->affectedSchema;
		$affectedTestCase = $changeResult->affectedTestCase;
		$affectedRTM = $changeResult->affectedRTM;

		//1. save change request header.
		$changeRequestNo = $this->mCommon->getChangeRequestNo(RUNNING_TYPE_CHANGE_REQUEST_NO);
		if(empty($changeRequestNo)){
			$error_message = ER_MSG_016;
			return false;
		}

		$paramSearch = (object) array(
			'userId' => $changeInfo->userId,
			'functionId' => $changeInfo->functionId,
			'functionVersion' => $changeInfo->functionVersion);
		$tmpChangeList = $this->searchTempFRInputChangeList($paramSearch);
		if(0 == count($tmpChangeList)){
			$error_message = ER_MSG_016;
			return false;
		}

		$resultFnReq = $this->db->query("SELECT functionNo FROM M_FN_REQ_HEADER WHERE functionId = $changeInfo->functionId")->row();

		$paramInsert = (object) array(
			'changeRequestNo' => $changeRequestNo,
			'changeUser' => $changeInfo->userId,
			'changeDate' => $newCurrentDate,
			'projectId' => $changeInfo->projectId,
			'changeFunctionId' => $changeInfo->functionId,
			'changeFunctionNo' => $resultFnReq->functionNo,
			'changeFunctionVersion' => $changeInfo->functionVersion,
			'changeStatus' => CHANGE_STATUS_CLOSE,
			'user' => $user,
			'currentDate' => $newCurrentDate);
		$this->insertChangeRequestHeader($paramInsert);

		//2. save change request details.
		$i = 1;
		foreach($tmpChangeList as $value){
			$paramInsert = (object) array(
				'changeRequestNo' => $changeRequestNo,
				'sequenceNo' => $i++,
				'changeType' => $value['changeType'],
				'inputId' => $value['inputId'],
				'inputName' => $value['inputName'], 
				'schemaVersionId' => $value['schemaVersionId'],
				'dataType' => $value['newDataType'],
				'dataLength' => $value['newDataLength'],
				'scale' => $value['newScaleLength'],
				'unique' => $value['newUnique'],
				'notNull' => $value['newNotNull'],
				'default' => $value['newDefaultValue'],
				'min' => $value['newMinValue'],
				'max' => $value['newMaxValue'],
				'tableName' => $value['tableName'],
				'columnName' => $value['columnName']);
			$this->insertChangeRequestDetail($paramInsert);
		}

		//3. save change history requirement header
		foreach($affectedRequirements as $keyFunctionNo => $functionDetailValue){
			
			$existFR = $this->mFR->searchExistFunctionalRequirement($keyFunctionNo, $affectedProjectId);

			$resultNewVersion = $this->searchRelatedNewVersion_FnReq($existFR[0]['functionId'], $functionDetailValue->functionVersion);

			$paramInsert = (object) array(
				'changeRequestNo' 	 => $changeRequestNo,
				'functionNo'		 => $keyFunctionNo,
				'functionId' 		 => $existFR[0]['functionId'],
				'oldFnVersionNumber' => $functionDetailValue->functionVersion,
				'newFnVersionNumber' => $resultNewVersion->functionVersionNumber);

			$fnReqHistoryId = $this->insertChangeHistory_RequirementsHeader($paramInsert);
			if(empty($fnReqHistoryId)){
				$error_message = ER_MSG_016;
				return false;
			}

			//3.1 save change history requirement detail
			$i = 1;
			foreach($functionDetailValue->functionInput as $keyInputName => $value){

				$paramInsert = (object) array(
				'fnReqHistoryId' 	 => $fnReqHistoryId,
				'sequenceNo' 	 	 => $i++,
				'changeType'  	 	 => $value->changeType,
				'inputName' 	 	 => $keyInputName,
				'refTableName' 	 	 => $value->refTableName,
				'refColumnName' 	 => $value->refColumnName);

				$this->insertChangeHistory_RequirementsDetail($paramInsert);
			}
		}

		//4. save change history database schema
		$i = 1;
		foreach($affectedSchemaList as $value){
			$paramInsert = (object) array(
				'changeRequestNo' => $changeRequestNo,
				'sequenceNo' => $i++,
				'changeType' => $value->affectedAction,
				'tableName'  => $value->tableName,
				'columnName' => $value->columnName,
				'oldSchemaVersionNumber' => $value->oldSchemaVersionNo,
				'newSchemaVersionNumber' => $value->newSchemaVersionNo);

			$this->insertChangeHistory_Schema($paramInsert);
		}

		//5. save change history test case
		foreach($affectedTestCase as $keyTestCaseNo => $value){
			$paramInsert = (object) array(
				'changeRequestNo' => $changeRequestNo,
				'testCaseId' => $value->testCaseId,
				'testCaseNo' => $keyTestCaseNo,
				'oldTestCaseVersionNo' => $value->oldVerNO,
				'newTestCaseVersionNo' => $value->newVerNO,
				'changeType' => $value->changeType);
			$this->insertChangeHistory_TestCase($paramInsert);
		}

		//6. save change history RTM header and detail
		if(null != $affectedRTM && 0 < count($affectedRTM)){

			$paramInsert = (object) array(
				'changeRequestNo' => $changeRequestNo,
				'projectId' => $affectedProjectId,
				'oldVersionNumber' => $affectedRTM->oldRTMVerNO,
				'newVersionNumber' => $affectedRTM->newRTMVerNO);

			$rtmHistoryId = $this->insertChangeHistory_RTMHeader($paramInsert);

			$i = 1;
			foreach($affectedRTM->details as $value){
				$paramInsert = (object) array(
					'rtmHistoryId' => $rtmHistoryId,
					'sequenceNo' => $i++,
					'functionId' => $value->functionId,
					'testCaseId' => $value->testCaseId,
					'changeType' => $value->changeType);
				$this->insertChangeHistory_RTMDetail($paramInsert);
			}
		}

		return true;
	}

	private function searchRelatedNewVersion_FnReq($functionId, $functionVersion){
		$sqlStr = "SELECT b.functionVersionNumber
			FROM M_FN_REQ_VERSION a
			INNER JOIN M_FN_REQ_VERSION b
			ON a.functionVersionId = b.previousVersionId
			and a.functionId = b.functionId
			WHERE a.functionId = $functionId 
			AND a.functionVersionNumber = $functionVersion";
		$result = $this->db->query($sqlStr);
		return $result->row();
	}
}

?>
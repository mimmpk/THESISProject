<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
* Cancellation Model
*/
class Cancellation_model extends CI_Model{
	
	function __construct(){
		parent::__construct();

		$this->load->model('ChangeManagement_model', 'mChange');
		$this->load->model('FunctionalRequirement_model', 'mFR');
		$this->load->model('TestCase_model', 'mTestCase');
		$this->load->model('DatabaseSchema_model', 'mDB');
		$this->load->model('RTM_model', 'mRTM');
	}

	public function searchChangesInformationForCancelling($param){
		
		if(isset($param->projectId) && !empty($param->projectId)){
			$where[] = "h.projectId = $param->projectId";
		}

		if(isset($param->changeRequestNo) && !empty($param->changeRequestNo)){
			$where[] = "h.changeRequestNo = '$param->changeRequestNo'";
		}

		if(isset($param->changeStatus) && !empty($param->changeStatus)){
			$where[] = "h.changeStatus = '$param->changeStatus'";
		}
		$where_condition = implode(" AND ", $where);

		$sqlStr = "
			SELECT 
				h.changeRequestNo,
				CONVERT(nvarchar, h.changeDate, 103) as changeDate,
				CONCAT(u.firstname, '   ', u.lastname) as changeUser,
				h.changeFunctionNo,
				h.changeFunctionVersion,
				fh.functionDescription,
				CASE WHEN h.changeRequestNo = (
					SELECT TOP 1 changeRequestNo
					FROM T_CHANGE_REQUEST_HEADER 
					WHERE projectId = $param->projectId
					ORDER by changeDate desc) THEN 'Y' ELSE 'N' 
				END as isLatestChange,
				h.changeStatus,
				m.miscDescription as changeStatusMisc,
				h.reason
			FROM T_CHANGE_REQUEST_HEADER h 
			INNER JOIN M_USERS u 
			ON h.changeUserId = u.userId
			INNER JOIN M_FN_REQ_HEADER fh 
			ON h.changeFunctionId = fh.functionId
			LEFT JOIN M_MISCELLANEOUS m
			ON m.miscValue1 = h.changeStatus
			AND m.miscData = 'changeRequestStatus'
			WHERE $where_condition
			ORDER BY h.changeDate desc";
		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}

	public function searchChangeHistoryDatabaseSchemaByCriteria($param){
		if(!empty($param->changeRequestNo)){
			$where[] = "changeRequestNo = '$param->changeRequestNo'";
		}

		if(!empty($param->tableName)){
			$where[] = "tableName = '$param->tableName'";
		}

		if(!empty($param->columnName)){
			$where[] = "columnName = '$param->columnName'";
		}

		$where_condition = implode(' AND ', $where);
		$sqlStr = "SELECT * FROM T_CHANGE_HISTORY_SCHEMA 
			WHERE $where_condition";
		$result = $this->db->query($sqlStr);
		return $result->row();
	}

	public function getCancelChangeRequestInputDetail($sequenceNo){
		$sqlStr = "
		SELECT 
			c.inputName,
			case 
				when c.dataType is null then '' 
				else d.dataType 
			end as dataType,
			case 
				when c.dataLength is null then null 
				else d.dataLength 
			end as dataLength,
			case 
				when c.scale is null then null 
				else d.decimalPoint 
			end as scale,
			case 
				when c.constraintUnique is null then '' 
				else d.constraintUnique 
			end as constraintUnique,
			case 
				when c.constraintNotNull is null then '' 
				else d.constraintNull 
			end as constraintNotNull,
			case 
				when c.constraintDefault is null then '' 
				else d.constraintDefault 
			end as constraintDefault,
			case 
				when c.constraintMin is null then '' 
				else d.constraintMinValue 
			end as constraintMin,
			case 
				when c.constraintMax is null then '' 
				else d.constraintMaxValue 
			end as constraintMax,
			c.refTableName, c.refColumnName
		FROM T_CHANGE_REQUEST_DETAIL c
		INNER JOIN M_FN_REQ_INPUT r
		ON c.refInputId = r.inputId
		INNER JOIN M_DATABASE_SCHEMA_INFO d
		ON r.refTableName = d.tableName
		AND r.refColumnName = d.columnName
		AND c.refSchemaVersionId = d.schemaVersionId
		WHERE c.sequenceNo = $sequenceNo";

		$result = $this->db->query($sqlStr);
		return $result->row_array();
	}

	public function cancelProcess(&$changeResult, &$error_message, $processData){
		$this->db->trans_begin();

		$changeRequestNo = $processData['changeRequestNo'];
		$user = $processData['user'];

		$isSuccess = $this->controlVersionCaseCancellationRequest($changeResult, $changeRequestNo, $error_message, $user);

		//UPDATE STATUS CHANGE REQUEST
		$currentDate = date('Y-m-d H:i:s');
		$paramUpdate = (object) array(
			'status' 				=> CHANGE_STATUS_CANCEL,
			'reason' 				=> $processData['reason'],
			'updateDate' 			=> $currentDate,
			'user' 					=> $user,
			'updateDateCondition'   => $processData['updateDateCondition'],
			'changeRequestNo'		=> $changeRequestNo);
		$rowUpdate = $this->mChange->updateChangeRequestHeader($paramUpdate);
		if(1 !== $rowUpdate){
			$error_message = ER_MSG_019;
			$isSuccess = false;
		}

		$trans_status = $this->db->trans_status();
	    if($trans_status == FALSE || !$isSuccess){
	    	$this->db->trans_rollback();
	    	return false;
	    }else{
	   		$this->db->trans_commit();
	   		return true;
	    }
	}

	private function controlVersionCaseCancellationRequest(&$changeResult, $changeRequestNo, &$error_message, $user){
		$errorFlag = false;
		$affectedProjectId = $changeResult->projectInfo;
		$affectedRequirements = $changeResult->affectedRequirement;
		$affectedTestCase = $changeResult->affectedTestCase;

		$newCurrentDate = date('Y-m-d H:i:s');

		//[1. Revert Version of Functional Requirements]
		$FnReqHeaderHistoryList = $this->mChange->getChangeHistoryFnReqHeaderList($changeRequestNo);
		foreach($FnReqHeaderHistoryList as $value){
			//1.1 Update Version of Functional Requirement Header 
			$functionId = $value['functionId'];
			$functionNo = $value['functionNo'];
			$newFunctionVersion = $value['newFunctionVersion'];
			$fnReqHistoryId = $value['fnReqHistoryId'];

			$param = (object) array(
				'functionId' => $functionId, 'functionVersionNumber' => $newFunctionVersion);
			$latestFnReqInfo = $this->mFR->searchFunctionalRequirementVersionByCriteria($param);

			$effectiveStartDate = $latestFnReqInfo->effectiveStartDate;
			$previousFnReqVersionId = $latestFnReqInfo->previousVersionId;

			//Get Previous Functional Requirement Info
			$param = (object) array(
				'functionId' => $functionId, 'functionVersionId' => $previousFnReqVersionId);
			$previousFnReqInfo = $this->mFR->searchFunctionalRequirementVersionByCriteria($param);

			$previousEffectiveEndDate = $previousFnReqInfo->effectiveEndDate;
			$previousUpdateDate = $previousFnReqInfo->updateDate;

			//A. Enable Previous Version
			$paramUpdate = (object) array(
				'effectiveEndDate' 		=> '',
				'activeFlag' 			=> ACTIVE_CODE,
				'currentDate' 			=> $newCurrentDate,
				'user' 					=> $user,
				'oldFunctionVersionId'  => $previousFnReqVersionId,
				'functionId' 			=> $functionId,
				'oldUpdateDate' 		=> $previousUpdateDate);
			$rowUpdate = $this->mFR->updateFunctionalRequirementsVersion($paramUpdate);
			if(1 !== $rowUpdate){
				$error_message = ER_MSG_019;
				return false;
			}

			//B. Delete Latest Version
			$paramDelete = (object) array(
				'functionId' => $functionId, 
				'functionVersionId' => $latestFnReqInfo->functionVersionId);
			$rowDelete = $this->mFR->deleteFunctionalRequirementHeader($paramDelete);
			if(1 !== $rowDelete){
				$error_message = ER_MSG_019;
				return false;
			}

			//1.2 Update Version of Functional Requirement Detail 
			$FnReqDetailHistoryList = $this->mChange->getChangeHistoryFnReqDetailList($fnReqHistoryId);
			foreach($FnReqDetailHistoryList as $detail){
				$inputInfo = $this->mFR->searchFRInputInformation($affectedProjectId, $detail['inputName']);

				$inputId = $inputInfo->inputId;

				if(CHANGE_TYPE_ADD == $detail['changeType'] 
					|| CHANGE_TYPE_EDIT == $detail['changeType']){
					//A. Delete Latest Version
					$paramDelete = (object) array(
						'functionId' 		 => $functionId,
						'inputId' 	 		 => $inputId,
						'effectiveStartDate' => $effectiveStartDate);
					$rowDelete = $this->mFR->deleteFunctionalRequirementDetail($paramDelete);
					if(1 !== $rowDelete){
						$error_message = ER_MSG_019;
						return false;
					}
				}

				if(CHANGE_TYPE_DELETE == $detail['changeType'] 
					|| CHANGE_TYPE_EDIT == $detail['changeType']){
					//B. Enable Previous Version
					$paramUpdate = (object) array(
						'effectiveEndDate' 	=> '',
						'activeFlag' 		=> ACTIVE_CODE,
						'currentDate' 		=> $newCurrentDate,
						'user' 				=> $user,
						'functionId' 		=> $functionId,
						'inputId' 			=> $inputId,
						'endDateCondition' 	=> $previousEffectiveEndDate);
					$rowUpdate = $this->mFR->updateFunctionalRequirementsDetail($paramUpdate);
					if(1 !== $rowUpdate){
						$error_message = ER_MSG_019;
						return false;
					}
				}
			}
		} //End FR

		//[2. Revert Version of Test Cases]
		$testCaseHistoryList = $this->mChange->getChangeHistoryTestCaseList($changeRequestNo);
		foreach($testCaseHistoryList as $value) {
			//2.1 Update Version of Header
			$testCaseId = $value['testCaseId'];
			$changeType = $value['changeType'];
			$oldVersion = $value['oldTestCaseVersionNumber'];
			$newVersion = $value['newTestCaseVersionNumber'];

			if(CHANGE_TYPE_ADD == $changeType){
				//A.Delete latest version
				$criteria = (object) array(
					'testCaseId' => $testCaseId, 'testCaseVersionNumber' => $newVersion);
				$testCaseVersionInfo = $this->mTestCase->searchTestCaseVersionInformationByCriteria($criteria);

				$paramDelete = (object) array(
					'testCaseId' => $testCaseId, 'testCaseVersionNumber' => $newVersion);
				$rowDelete = $this->mTestCase->deleteTestCaseVersion($paramDelete);
				if(1 !== $rowDelete){
					$error_message = ER_MSG_019;
					return false;
				}

				//B.Delete Header
				$rowDelete = $this->mTestCase->deleteTestCaseHeader($testCaseId);
				if(1 !== $rowDelete){
					$error_message = ER_MSG_019;
					return false;
				}

				//C.Delete Details(all)
				$paramDelete = (object) array(
					'testCaseId' => $testCaseId, 
					'effectiveStartDate' => $testCaseVersionInfo->effectiveStartDate);
				$rowDelete = $this->mTestCase->deleteTestCaseDetail($paramDelete);
				if(0 == $rowDelete){
					$error_message = ER_MSG_019;
					return false;
				}
			}

			if(CHANGE_TYPE_DELETE == $changeType){
				//A.Update previous version
				$criteria = (object) array(
					'testCaseId' => $testCaseId, 'testCaseVersionNumber' => $oldVersion);
				$testCaseVersionInfo = $this->mTestCase->searchTestCaseVersionInformationByCriteria($criteria);

				$testCaseVersionId = $testCaseVersionInfo->testCaseVersionId;
				$effectiveEndDate = $testCaseVersionInfo->effectiveEndDate;
				$updateDate = $testCaseVersionInfo->updateDate;

				$paramUpdate = (object) array(
					'effectiveEndDate' 	  => '',
					'activeFlag' 		  => ACTIVE_CODE,
					'updateDate' 		  => $newCurrentDate,
					'updateUser' 		  => $user,
					'testCaseId' 		  => $testCaseId,
					'testCaseVersionId'   => $testCaseVersionId,
					'updateDateCondition' => $updateDate);
				$rowUpdate = $this->mTestCase->updateTestCaseVersion($paramUpdate);
				if(1 !== $rowUpdate){
					$error_message = ER_MSG_019;
					return false;
				}

				//B. Update Detail (all)
				$paramUpdate = (object) array(
					'effectiveEndDate' 	  => '',
					'activeFlag' 		  => ACTIVE_CODE,
					'updateDate' 		  => $newCurrentDate,
					'updateUser' 		  => $user,
					'testCaseId' 		  => $testCaseId,
					'endDateCondition' 	  => $effectiveEndDate);
				$rowUpdate = $this->mTestCase->updateTestCaseDetail($paramUpdate);
				if(0 == $rowUpdate){
					$error_message = ER_MSG_019;
					return false;
				}
			}

			if(CHANGE_TYPE_EDIT == $changeType){
				$criteria = (object) array(
					'testCaseId' => $testCaseId, 'testCaseVersionNumber' => $newVersion);
				$testCaseVersionInfo = $this->mTestCase->searchTestCaseVersionInformationByCriteria($criteria);

				$testCaseVersionId = $testCaseVersionInfo->testCaseVersionId;
				$previousVersionId = $testCaseVersionInfo->previousVersionId;
				$effectiveStartDate = $testCaseVersionInfo->effectiveStartDate;
				$updateDate = $testCaseVersionInfo->updateDate;

				//A.Delete Latest Version
				$paramDetail = (object) array(
					'testCaseId' => $testCaseId,
					'testCaseVersionId' => $testCaseVersionId);
				$rowDelete = $this->mTestCase->deleteTestCaseVersion($paramDelete);
				if(1 != $rowDelete){
					$error_message = ER_MSG_019;
					return false;
				}

				//B.Update Previous Version
				$paramUpdate = (object) array(
					'effectiveEndDate' 	  => '',
					'activeFlag' 		  => ACTIVE_CODE,
					'updateDate' 		  => $newCurrentDate,
					'updateUser' 		  => $user,
					'testCaseId' 		  => $testCaseId,
					'testCaseVersionId'   => $previousVersionId,
					'updateDateCondition' => $updateDate);
				$rowUpdate = $this->mTestCase->updateTestCaseVersion($paramUpdate);
				if(1 !== $rowUpdate){
					$error_message = ER_MSG_019;
					return false;
				}

				//C.Update Specific Detail
				$paramDelete = (object) array(
					'testCaseId' => $testCaseId,
					'effectiveStartDate' => $effectiveStartDate);
				$rowDelete = $this->mTestCase->deleteTestCaseDetail($paramDelete);

				$paramUpdate = (object) array(
					'effectiveEndDate' 	=> '',
					'activeFlag' 		=> ACTIVE_CODE,
					'updateDate' 		=> $newCurrentDate,
					'updateUser' 		=> $user,
					'testCaseId' 		=> $testCaseId,
					'endDateCondition' 	=> $effectiveStartDate);
				$rowUpdate = $this->mTestCase->updateTestCaseDetail($paramUpdate);
			}

		} //End Test Case

		//[3. Database Schema]
		$databaseSchemaHistoryList = $this->mChange->getChangeHistoryDatabaseSchemaList($changeRequestNo);
		foreach($databaseSchemaHistoryList as $value){
			//3.1 Update Version
			$changeType = $value['changeType'];
			$tableName = $value['tableName'];
			$columnName = $value['columnName'];
			$oldSchemaVersion = $value['oldSchemaVersionNumber'];
			$newSchemaVersion = $value['newSchemaVersionNumber'];

			if(CHANGE_TYPE_ADD == $changeType){
				$criteria = (object) array(
					'projectId' 	=> $affectedProjectId,
					'tableName' 	=> $tableName,
					'columnName' 	=> $columnName,
					'versionNumber' => $newSchemaVersion);
				$schemaVersionInfo = $this->mDB->searchDatabaseSchemaVersionInformationByCriteria($criteria);

				$newSchemaVersionId = $schemaVersionInfo->schemaVersionId;

				//A.DELETE VERSION
				$paramDelete = (object) array(
					'projectId' 	  => $affectedProjectId,
					'tableName' 	  => $tableName,
					'columnName' 	  => $columnName,
					'schemaVersionId' => $newSchemaVersionId);

				$rowDelete = $this->mDB->deleteDatabaseSchemaVersion($paramDelete);
				if(0 == $rowDelete){
					$error_message = ER_MSG_019;
					return false;
				}

				//B.DELETE INFO
				$rowDelete = $this->mDB->deleteDatabaseSchemaInfo($paramDelete);
				if(0 == $rowDelete){
					$error_message = ER_MSG_019;
					return false;
				}
			}
			if(CHANGE_TYPE_DELETE == $changeType){
				
				$criteria = (object) array(
					'projectId' 	=> $affectedProjectId,
					'tableName' 	=> $tableName,
					'columnName' 	=> $columnName,
					'versionNumber' => $oldSchemaVersion);
				$schemaVersionInfo = $this->mDB->searchDatabaseSchemaVersionInformationByCriteria($criteria);

				$oldSchemaVersionId = $schemaVersionInfo->schemaVersionId;

				//A.UPDATE ACTIVATE VERSION
				$paramUpdate = (object) array(
					'effectiveEndDate' 	 => '',
					'activeFlag' 		 => ACTIVE_CODE,
					'currentDate' 		 => $newCurrentDate,
					'user' 				 => $user,
					'projectId' 		 => $affectedProjectId,
					'tableName' 		 => $tableName,
					'columnName' 		 => $columnName,
					'oldSchemaVersionId' => $oldSchemaVersionId);
				$rowUpdate = $this->mDB->updateDatabaseSchemaVersion($paramUpdate);
				if(1 !== $rowDelete){
					$error_message = ER_MSG_019;
					return false;
				}
			}
			if(CHANGE_TYPE_EDIT == $changeType){
				$criteria = (object) array(
					'projectId' 	=> $affectedProjectId,
					'tableName' 	=> $tableName,
					'columnName' 	=> $columnName,
					'versionNumber' => $newSchemaVersion);
				$schemaVersionInfo = $this->mDB->searchDatabaseSchemaVersionInformationByCriteria($criteria);

				$newSchemaVersionId = $schemaVersionInfo->schemaVersionId;
				$previousSchemaVersionId = $schemaVersionInfo->previousSchemaVersionId;

				//A.DELETE LATEST VERSION
				$paramDelete = (object) array(
					'projectId' 	  => $affectedProjectId,
					'tableName' 	  => $tableName,
					'columnName' 	  => $columnName,
					'schemaVersionId' => $newSchemaVersionId);

				$rowDelete = $this->mDB->deleteDatabaseSchemaVersion($paramDelete);
				if(1 !== $rowDelete){
					$error_message = ER_MSG_019;
					return false;
				}

				//B.DELETE LATEST INFO
				$rowDelete = $this->mDB->deleteDatabaseSchemaInfo($paramDelete);
				if(1 !== $rowDelete){
					$error_message = ER_MSG_019;
					return false;
				}

				//C.UPDATE ACTIVATE PREVIOUS VERSION
				$paramUpdate = (object) array(
					'effectiveEndDate' 	 => '',
					'activeFlag' 		 => ACTIVE_CODE,
					'currentDate' 		 => $newCurrentDate,
					'user' 				 => $user,
					'projectId' 		 => $affectedProjectId,
					'tableName' 		 => $tableName,
					'columnName' 		 => $columnName,
					'oldSchemaVersionId' => $previousSchemaVersionId);
				$rowUpdate = $this->mDB->updateDatabaseSchemaVersion($paramUpdate);
				if(1 !== $rowDelete){
					$error_message = ER_MSG_019;
					return false;
				}
			}

		} //End Database Schema

		//[4. RTM]
		$rtmHistoryList = $this->mChange->getChangeHistoryRTMDetail($changeRequestNo);
		if(null != $rtmHistoryList && 0 < count($rtmHistoryList)){
			$oldVersionNo = $rtmHistoryList[0]['oldVersionNumber'];
			$newVersionNo = $rtmHistoryList[0]['newVersionNumber'];

			$criteria = (object) array(
				'projectId' 		=> $affectedProjectId,
				'rtmVersionNumber' 	=> $newVersionNo);

			$rtmVersionInfo = $this->mRTM->searchRTMVersionInfoByCriteria($criteria);
			if(null == $rtmVersionInfo || 0 == count($rtmVersionInfo)){
				$error_message = ER_MSG_019;
				return false;
			}

			$newRTMVersionId = $rtmVersionInfo->new_rtmVersionId;
			$oldRTMVersionId = $rtmVersionInfo->old_rtmVersionId;
			$effectiveStartDate = $rtmVersionInfo->effectiveStartDate;
			$oldUpdateDate = $rtmVersionInfo->old_updateDate;

			//4.1 UPDATE RTM VERSION
			/* A. DELETE LATEST VERSION */
			$paramDelete = (object) array(
				'projectId' 	=> $affectedProjectId,
				'rtmVersionId' 	=> $newRTMVersionId);
			$rowDelete = $this->mRTM->deleteRTMVersion($paramDelete);
			if(1 !== $rowDelete){
				$error_message = ER_MSG_019;
				return false;
			}

			/* B. UPDATE ACTIVATE PREVIOUS VERSION*/
			$paramUpdate = (object) array(
				'effectiveEndDate' 		=> '',
				'activeFlag' 			=> ACTIVE_CODE,
				'updateDate' 			=> $newCurrentDate,
				'user' 					=> $user,
				'rtmVersionIdCondition' => $oldRTMVersionId,
				'projectId' 			=> $affectedProjectId,
				'updateDateCondition' 	=> $oldUpdateDate);
			$rowUpdate = $this->mRTM->updateRTMVersion($paramUpdate);
			if(1 !== $rowUpdate){
				$error_message = ER_MSG_019;
				return false;
			}

			//4.2 UPDATE RTM INFO
			foreach($rtmHistoryList as $value){
				if(CHANGE_TYPE_DELETE == $value['changeType']){
					$paramUpdate = (object) array(
						'effectiveEndDate' 	=> '',
						'activeFlag' 		=> ACTIVE_CODE,
						'updateDate' 		=> $newCurrentDate,
						'user' 				=> $user,
						'projectId' 		=> $affectedProjectId,
						'functionId' 		=> $value['functionId'],
						'testCaseId' 		=> $value['testCaseId']);
					$rowUpdate = $this->mRTM->updateRTMInfo($paramUpdate);
				}
				if(CHANGE_TYPE_ADD == $value['changeType']){
					$paramDelete = (object) array(
						'projectId'  => $affectedProjectId,
						'fucntionId' => $value['functionId'],
						'testCaseId' => $value['testCaseId']);
					$rowDelete = $this->mRTM->deleteRTMInfo($paramDelete);
				}
			}

		} //End RTM
		return true;
	}
}

?>
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

	/*function searchTempFRInputChangeList($userId, $functionId, $functionVersion){
		$sqlStr = "SELECT *
			FROM T_TEMP_CHANGE_LIST
			WHERE userId = $userId
			AND functionId = $functionId
			AND functionVersion = $functionVersion";
		$result = $this->db->query($sqlStr);
		return $result->num_rows();
	}*/

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
		$tableName = !empty($param->tableName)? "'".$param->tableName."'" : "NULL";
		$columnName = !empty($param->columnName)? "'".$param->columnName."'" : "NULL";

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
		$sqlStr = "SELECT h.testCaseId, v.testCaseVersionNumber, v.updateDate
			FROM M_TESTCASE_HEADER h
			INNER JOIN M_TESTCASE_VERSION v
			ON h.testCaseId = v.testCaseId
			WHERE v.activeFlag = '1'
			AND h.projectId = $projectId
			AND h.testCaseNo = $testCaseNo
			AND v.testCaseVersionNumber = $testCaseVersionNo";
		$result = $this->db->query($sqlStr);	
		return $result->row();
	}		

	function getSchemaFromDatabaseTarget($connectionDB, $tableName, $columnName){
		$dbSchemaDetail = array();

		$serverName = $connectionDB->hostname;
		$uid = $connectionDB->username;
		$pwd = $connectionDB->password;
		$databaseName = $connectionDB->databaseName;

		$connectionInfo = array( "UID" => $uid, "PWD" => $pwd, "Database" => $databaseName); 

		/* Connect using SQL Server Authentication. */    
		$conn = sqlsrv_connect( $serverName, $connectionInfo);

		$sqlStr = "
			SELECT 
				isc.TABLE_NAME,
				isc.COLUMN_NAME, 
				isc.DATA_TYPE,
				isc.CHARACTER_MAXIMUM_LENGTH,
				isc.NUMERIC_PRECISION,
				isc.NUMERIC_SCALE,
				isc.COLUMN_DEFAULT,
				CASE WHEN isc.IS_NULLABLE = 'YES' THEN 'N' ELSE 'Y' END as IS_NOTNULL,
				CASE WHEN istc1.CONSTRAINT_NAME IS NULL THEN 'N' ELSE 'Y' END as IS_UNIQUE,
				CASE WHEN istc3.CONSTRAINT_NAME IS NULL THEN 'N' ELSE 'Y' END as IS_PRIMARY_KEY,
				istc2.CONSTRAINT_NAME as CHECK_CONSTRAINT_NAME
			FROM INFORMATION_SCHEMA.COLUMNS isc
			LEFT JOIN INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE iscc
			ON isc.COLUMN_NAME = iscc.COLUMN_NAME
			LEFT JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS istc1
			ON iscc.CONSTRAINT_NAME = istc1.CONSTRAINT_NAME
			AND istc1.CONSTRAINT_TYPE = 'UNIQUE'
			LEFT JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS istc2
			ON iscc.CONSTRAINT_NAME = istc2.CONSTRAINT_NAME
			AND istc2.CONSTRAINT_TYPE = 'CHECK'
			LEFT JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS istc3
			ON iscc.CONSTRAINT_NAME = istc3.CONSTRAINT_NAME
			AND istc3.CONSTRAINT_TYPE = 'PRIMARY KEY'
			WHERE isc.COLUMN_NAME = '$columnName' AND isc.TABLE_NAME = '$tableName'";
		$stmt = sqlsrv_query( $conn, $sqlStr);

		if($stmt === false){
		    die(print_r(sqlsrv_errors(), true));
		}else{
			//echo "Statement executed.<br>\n"; 
		    while($row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_NUMERIC)){
		    	$dbSchemaDetail = array(
		    		'tableName' 			=> $row[0],
		    		'columnName' 			=> $row[1],
		    		'dataType' 				=> $row[2],
		    		'charecterLength' 		=> $row[3],
		    		'numericPrecision' 		=> $row[4],
		    		'numericScale' 			=> $row[5],
		    		'columnDefault' 		=> $row[6],
		    		'isNotNull' 			=> $row[7],
		    		'isUnique' 				=> $row[8],
		    		'isPrimaryKey'			=> $row[9],
		    		'checkConstraintName' 	=> $row[10],
		    		'minValue'				=> '',
		    		'maxValue'				=> ''
		    	);
			}

			//Check Does the column has CHECK_CONSTRAINT or not?
			if(!empty($dbSchemaDetail['checkConstraintName'])){
				$constraintName = $dbSchemaDetail['checkConstraintName'];
			    $min = 0.0;
			    $max = 0.0;
			    $procedure_params = array(
			    	array(&$constraintName, SQLSRV_PARAM_IN),
					array(&$min, SQLSRV_PARAM_OUT),
					array(&$max, SQLSRV_PARAM_OUT)
					);
			    $sqlStr = "{call getCheckConstraint(?, ?, ?)}";
				$stmt2 = sqlsrv_query($conn, $sqlStr, $procedure_params);
				if($stmt2 === false){  
				     die( print_r( sqlsrv_errors(), true));  
				}else{
					//print_r("Min: " .$min. "\nMax: ". $max);
					$dbSchemaDetail['minValue'] = $min;
					$dbSchemaDetail['maxValue'] = $max;
				}
				sqlsrv_free_stmt($stmt2);
			}
		} 

		/* Free statement and connection resources. */
		sqlsrv_free_stmt($stmt);    
		sqlsrv_close($conn); 
		return $dbSchemaDetail;
	}

	function controlVersionChangedData($changeResult, $connectionDB, $user, &$error_message){
		$this->db->trans_start(); //Starting Transaction

		$errorFlag = false;
		$affectedProjectId = $changeResult->projectInfo;
		$affectedRequirements = $changeResult->affectedRequirement;
		$affectedSchemaList = $changeResult->affectedSchema;
		$affectedTestCase = $changeResult->affectedTestCase;

		$newCurrentDate = date('Y-m-d H:i:s');

		//**[Version Control of Database Schema]
		foreach($affectedSchemaList as $value){
			if(empty($value->affectedAction)){
				continue;
			}

			$lastDBVersionInfo = $this->getLastDatabaseSchemaVersion($affectedProjectId, $value->tableName, $value->columnName);

			if(0 < count($lastDBVersionInfo)){
				$oldDBVersionId = $lastDBVersionInfo->schemaVersionId;
				$oldDBVersionNumber = $lastDBVersionInfo->schemaVersionNumber;
				$oldDBUpdateDate = $lastDBVersionInfo->updateDate;

				//update old version
				$dbParam = (object) array(
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
			$dbSchemaDetail = $this->getSchemaFromDatabaseTarget($connectionDB, $value->tableName, $value->columnName);
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
						break;
					}
				}
			}
		}//endforeach; (functional requirement)
		}
		
		//**[Version Control of Test Cases]
		if(!$errorFlag){
		foreach($affectedTestCase as $keyTestCaseNo => $testcaseInfoVal){
			$testCaseId = '';
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
				$newTCVersionNumber = INITIAL_VERSION;
			}else{
				$resultLastTCVersion = $this->getLastTestCaseVersion($affectedProjectId, $keyTestCaseNo, $testcaseInfoVal->testCaseVersion);
				if(null == $resultLastTCVersion || 0 == count($resultLastTCVersion)){
					$errorFlag = true;
					$error_message = ER_MSG_016;
					break;
				}

				$testCaseId = $resultLastTCVersion->testCaseId;
				$newTCVersionNumber = (int)$resultLastTCVersion->testCaseVersionNumber + 1;
				$oldUpdateDate = $resultLastTCVersion->updateDate;

			}

			//Insert Test Case Version.
			if(CHANGE_TYPE_ADD == $testcaseInfoVal->changeType 
				|| CHANGE_TYPE_EDIT == $testcaseInfoVal->changeType){
				$param = (object) array(
					'testCaseId' => $testCaseId,
					'initialVersionNo' => $newTCVersionNumber,
					'effectiveStartDate' => $newCurrentDate,
					'activeStatus' => ACTIVE_CODE);
				$result = $this->mTestCase->insertTestCaseVersion($param, $user);
			}
			
			//Disabled Old Test Case Version.
			if(CHANGE_TYPE_EDIT == $testcaseInfoVal->changeType 
				|| CHANGE_TYPE_DELETE == $testcaseInfoVal->changeType){

			}

			//Insert or Update Test Case Detail


		}//endforeach; (test case)
		}

		//**[Version Control of RTM]

		$this->db->trans_complete();
    	$trans_status = $this->db->trans_status();
	    if($trans_status == FALSE || $errorFlag){
	    	$this->db->trans_rollback();
	    	return false;
	    }else{
	   		$this->db->trans_commit();
	   		return true;
	    }
	}
}

?>
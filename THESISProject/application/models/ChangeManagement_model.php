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
		$sqlStr = "SELECT functionVersionNumber
			FROM M_FN_REQ_VERSION
			WHERE functionId = $functionId
			AND activeFlag = '1'";

		$result = $this->db->query($sqlStr);
		if(0 < $result->num_rows()){
			$lastVersionData = $result->row();
			return (int)$lastVersionData->functionVersionNumber + 1;
		}else{
			return "";
		}
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
				CASE WHEN istc1.CONSTRAINT_NAME IS NULL THEN 'N' ELSE 'Y' END as DEFAULT_VALUE,
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
			WHERE isc.TABLE_NAME = '$columnName' AND TABLE_NAME = '$tableName'";
		$stmt = sqlsrv_query( $conn, $sqlStr);

		if($stmt){    
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
		    		'defaultValue' 			=> $row[8],
		    		'checkConstraintName' 	=> $row[9]
		    	);
			}

			//Check Does the column has CHECK_CONSTRAINT or not?
			if(!empty($dbSchemaDetail['checkConstraintName'])){
				$sqlStr = "EXEC getCheckConstraint @constraintName = '".$dbSchemaDetail['checkConstraintName']."'";
				$result = sqlsrv_query($conn, $sqlStr);
				echo $result;
			}
		}else{   
		    die(print_r( sqlsrv_errors(), true));
		} 

		/* Free statement and connection resources. */
		sqlsrv_free_stmt($stmt);    
		sqlsrv_close($conn); 
		return $dbSchemaDetail;
	}

	function saveChangeImpactData($changeResult, $connectionDB, $user, &$error_message){
		$this->db->trans_start(); //Starting Transaction
		$this->db->trans_strict(FALSE);

		$errorFlag = false;
		$affectedProjectId = $changeResult->projectInfo;
		$affectedRequirements = $changeResult->affectedRequirement;
		$affectedSchemaList = $changeResult->affectedSchema;

		$newCurrentDate = date('Y-m-d H:i:s');

		//**Version Control of Database Schema
		foreach($affectedSchemaList as $value){
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
				$newDBVersionNumber = (int)oldDBVersionNumber + 1;
			}else{
				$newDBVersionNumber = INITIAL_VERSION;
			}

			//insert database schema data
			$dbParam = (object) array(
					'projectId' => $affectedProjectId,
					'tableName' => $value->tableName,
					'columnName' => $value->columnName,
					'schemaVersionNo' => $newDBVersionNumber,
					'status' => ACTIVE_CODE);
			$resultInsert = $this->insertDatabaseSchemaVersion($dbParam, $user);
			if(null != $resultInsert){
				$schemaVersionId = $resultInsert;
			}else{
				$errorFlag = true;
				$error_message = ER_MSG_016;
				break;
			}

			//insert database schema detail**(get direct from database target)
			
			
		}

		//**Version Control of Functional Requirements
		/*foreach($affectedRequirements as $keyFunctionNo => $functionInfoVal){
			$keyFunctionId = '';

			$existFR = $this->mFR->searchExistFunctionalRequirement($keyFunctionNo, $affectedProjectId);

			if(null != $existFR && !empty($existFR)){
				$keyFunctionId = $existFR[0]['functionId'];
			}else{
				$errorFlag = true;
				$error_message = ER_MSG_016;
				break;
			}

			//3.1 get latest function version id
			$keyFunctionVersion = $functionInfoVal->functionVersion;
			$latestFRInfo = $this->mFR->getLastFunctionalRequirementVersion($keyFunctionId, $keyFunctionVersion);

			if(null != $latestFRInfo && !empty($latestFRInfo)){
				$oldFRVersionId = $latestFRInfo->functionVersionId;
				$oldFRVersionUpdateDate = $latestFRInfo->updateDate;

				$newFRVersionNumber = $this->getLastFunctionalRequirementVersionNumber($keyFunctionId);

				//3.1.1 Create new version of function
				$param = (object) array(
					'functionId' => $keyFunctionId,
					'newVersionNumber' => $newFRVersionNumber,
					'effectiveStartDate' => $newCurrentDate,
					'effectiveEndDate' => '',
					'activeFlag' => ACTIVE_CODE,
					'previousVersionId' => $oldFRVersionId,
					'currentDate' => $newCurrentDate,
					'user' => $user);
				$this->mFR->insertFRVersion($param);
				
				//3.1.2 Update disabled previous version
				$param->activeFlag = UNACTIVE_CODE;
				$param->oldFunctionVersionId = $oldFRVersionId;
				$param->oldUpdateDate = $oldFRVersionUpdateDate;
				$rowUpdate = $this->updateFunctionalRequirementsVersion($param);
				if(0 == $rowUpdate){
					$errorFlag = true;
					$error_message = ER_MSG_016;
					break;
				}

				//3.1.3 Create new input of FR information
				foreach($functionInfoVal as $keyInputName => $detail){

					//var_dump($input);
					//var_dump($detail);
					//var_dump("<br/>");
				}

			}else{
				$errorFlag = true;
				$error_message = ER_TRN_012;
			}
		//end foreach
		}*/

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
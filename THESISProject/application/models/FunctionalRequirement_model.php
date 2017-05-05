<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FunctionalRequirement_model extends CI_Model {
	function __construct(){
		parent::__construct();
	}

	function searchFunctionalRequirementHeaderInfo($param){
		$where[] = "FRH.projectId = '".$param->projectId."'";
		if("2" != $param->status)
			$where[] = "FRV.activeVersionFlag = '".$param->status."'";
		
		$where_clause = implode(' AND ', $where);
		$queryStr = "SELECT FRH.functionId, FRH.functionNo, 
			CAST(FRH.functionDescription AS VARBINARY(MAX)) as fnDesc, 
			FRV.functionVersionNumber as functionVersion, 
			FRV.activeVersionFlag as functionStatus 
			FROM M_FN_REQ_HEADER FRH 
			INNER JOIN M_FN_REQ_VERSION FRV 
			ON FRH.functionId = FRV.functionId 
			WHERE $where_clause 
			ORDER BY FRH.functionNo, FRV.functionVersionNumber";
		$result = $this->db->query($queryStr);
		return $result->result_array();
	}

	function searchExistFunctionalRequirement($fnId, $projectId){
		$queryStr = "SELECT * FROM M_FN_REQ_HEADER WHERE projectId = '$projectId' AND functionNo = '$fnId'";
		$result = $this->db->query($queryStr);
		return $result->num_rows();
	}

	function searchFRInputInformation($projectId, $inputName){
		$queryStr = "SELECT * FROM M_FN_REQ_INPUT FRI WHERE FRI.projectId = '$projectId' AND FRI.inputName = '$inputName'";
		$result = $this->db->query($queryStr);
		return $result->result_array();
	}

	/*function searchActiveFunctionalRequirementCombo($projectId){
		$queryStr = "SELECT * 
			FROM FN_REQ_HEADER FRH 
			INNER JOIN FN_REQ_VERSION FRV
			ON FRH.functionId = FRV.functionId
			WHERE FRV.activeVersionFlag = '1' AND FRH.projectId = $projectId";
		$result = $this->db->query($queryStr);
		return $result->result_array();
	}*/

	function insertFRHeader($param){
		$currentDateTime = date('Y-m-d H:i:s');
		$sqlStr = "INSERT INTO M_FN_REQ_HEADER (functionNo, functionDescription, projectId, createDate, createUser, updateDate, updateUser) VALUES ('{$param->functionNo}', '{$param->functionDescription}', '{$param->projectId}', '$currentDateTime', '{$param->user}', '$currentDateTime', '{$param->user}')";
		$result = $this->db->query($sqlStr);
		if($result){
			$query = $this->db->query("SELECT IDENT_CURRENT('M_FN_REQ_HEADER') as last_id");
			$resultId = $query->result();
			return $resultId[0]->last_id;
		}
		return NULL;
	}

	function insertFRVersion($functionId, $versionNumber, $functionStatus, $user){
		$currentDateTime = date('Y-m-d H:i:s');
		$sqlStr ="INSERT INTO M_FN_REQ_VERSION (functionId, functionVersionNumber, activeVersionFlag, createDate, createUser) VALUES ($functionId, $versionNumber, '$functionStatus', '$currentDateTime', '$user')";
		$result = $this->db->query($sqlStr);
		return $result;
	}

	function insertFRInput($param){
		$currentDateTime = date('Y-m-d H:i:s');
		$dataLength = !empty($param->dataLength)? $param->dataLength : "NULL";
		$scale = !empty($param->scale)? $param->scale : "NULL";
		$defaultValue = !empty($param->defaultValue)? $param->defaultValue : "NULL";
		$minValue = !empty($param->minValue)? $param->minValue : "NULL";
		$maxValue = !empty($param->maxValue)? $param->maxValue : "NULL";
		$sqlStr = "INSERT INTO M_FN_REQ_INPUT (projectId, inputName, inputType, inputSize, decimalPoint, constraintUnique, constraintDefault, constraintNull, constraintMinValue, constraintMaxValue, relationTableName, relationColumnName, createDate, createUser, updateDate, updateUser) VALUES ({$param->projectId}, '{$param->inputName}', '{$param->dataType}', $dataLength, $scale, '{$param->unique}', $defaultValue, '{$param->notNull}', $minValue, $maxValue, '{$param->tableName}', '{$param->fieldName}', '$currentDateTime', '{$param->user}', '$currentDateTime', '{$param->user}')";
		//var_dump($sqlStr);
		$result = $this->db->query($sqlStr);
		if($result){
			$query = $this->db->query("SELECT IDENT_CURRENT('M_FN_REQ_INPUT') as last_id");
			$resultId = $query->result();
			return $resultId[0]->last_id;
		}
		return NULL;
	}

	function insertFRDetail($inputId, $functionId, $user){
		$currentDateTime = date('Y-m-d H:i:s');
		$sqlStr = "INSERT INTO M_FN_REQ_DETAIL (functionId, inputId, createDate, createUser, updateDate, updateUser) VALUES ($functionId, $inputId, '$currentDateTime', '$user', '$currentDateTime', '$user')";
		$result = $this->db->query($sqlStr);
		return $result;
	}

	function uploadFR($param){
		$this->db->trans_start(); //Starting Transaction
		$this->db->trans_strict(FALSE);

		//insert Functional Requirement Header
		$functionId = $this->insertFRHeader($param[0]);
		if(NULL != $functionId){
			//insert Functional Requirement Version
			$resultInsertVersion = $this->insertFRVersion($functionId, $param[0]->version, $param[0]->functionStatus, $param[0]->user);
			//insert Functional Requirement Detail
			foreach ($param as $value) {
				$inputId = '';
				//Check Exist Input
				$resultExistInput = $this->searchFRInputInformation($value->projectId, $value->inputName);
				if(0 < count($resultExistInput)){
					$inputId = $resultExistInput[0]['inputId'];
				}else{
					//Insert New Input
					$inputId = $this->insertFRInput($value);
				}
				$resultInsertDetail = $this->insertFRDetail($inputId, $functionId, $value->user);
			}// end foreach
		}// end if
		$this->db->trans_complete();
    	$trans_status = $this->db->trans_status();
	    if($trans_status == FALSE){
	    	$this->db->trans_rollback();
	    	return false;
	    }else{
	   		$this->db->trans_commit();
	   		return true;
	    }
	}
}

?>
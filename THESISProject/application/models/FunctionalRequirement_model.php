<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FunctionalRequirement_model extends CI_Model {
	function __construct(){
		parent::__construct();
	}

	function searchFunctionalRequirementHeaderInfo($param){
		$where[] = "projectId = '".$param->projectId."'";
		if("2" != $param->status)
			$where[] = "functionStatus = '".$param->status."'";
		
		$where_clause = implode(' AND ', $where);
		$queryStr = "SELECT functionId, functionNo, functionVersion, functionStatus, CAST(functionDescription AS VARBINARY(MAX)) as fnDesc 
			FROM FN_REQ_HEADER WHERE $where_clause ORDER BY functionNo, functionVersion";
		$result = $this->db->query($queryStr);
		return $result->result_array();
	}

	function searchExistFunctionalRequirement($fnId, $projectId){
		$queryStr = "SELECT * FROM FN_REQ_HEADER WHERE projectId = '$projectId' AND functionNo = '$fnId'";
		$result = $this->db->query($queryStr);
		return $result->num_rows();
	}

	function searchFRInputInformation($projectId, $inputName){
		$queryStr = "SELECT * FROM FN_REQ_INPUT FRI WHERE FRI.projectId = '$projectId' AND FRI.inputName = '$inputName'";
		$result = $this->db->query($queryStr);
		return $result->result_array();
	}

	function insertFRHeader($param){
		$currentDateTime = date('Y-m-d H:i:s');
		$sqlStr = "INSERT INTO FN_REQ_HEADER (functionNo, functionDescription, functionVersion, projectId, functionStatus, createDate, createUser, updateDate, updateUser) VALUES ('{$param->functionNo}', '{$param->functionDescription}', '{$param->version}', '{$param->projectId}', '{$param->functionStatus}', '$currentDateTime', '{$param->user}', '$currentDateTime', '{$param->user}')";
		$result = $this->db->query($sqlStr);
		if($result){
			$query = $this->db->query("SELECT IDENT_CURRENT('FN_REQ_HEADER') as last_id");
			$resultId = $query->result();
			return $resultId[0]->last_id;
		}
		return NULL;
	}

	function insertFRInput($param){
		$currentDateTime = date('Y-m-d H:i:s');
		$dataLength = !empty($param->dataLength)? $param->dataLength : "NULL";
		$scale = !empty($param->scale)? $param->scale : "NULL";
		$defaultValue = !empty($param->defaultValue)? $param->defaultValue : "NULL";
		$minValue = !empty($param->minValue)? $param->minValue : "NULL";
		$maxValue = !empty($param->maxValue)? $param->maxValue : "NULL";
		$sqlStr = "INSERT INTO FN_REQ_INPUT (projectId, inputName, inputType, inputSize, decimalPoint, constraintUnique, constraintDefault, constraintNull, constraintMinValue, constraintMaxValue, relationTableName, relationColumnName, createDate, createUser, updateDate, updateUser) VALUES ({$param->projectId}, '{$param->inputName}', '{$param->dataType}', $dataLength, $scale, '{$param->unique}', $defaultValue, '{$param->notNull}', $minValue, $maxValue, '{$param->tableName}', '{$param->fieldName}', '$currentDateTime', '{$param->user}', '$currentDateTime', '{$param->user}')";
		//var_dump($sqlStr);
		$result = $this->db->query($sqlStr);
		if($result){
			$query = $this->db->query("SELECT IDENT_CURRENT('FN_REQ_INPUT') as last_id");
			$resultId = $query->result();
			return $resultId[0]->last_id;
		}
		return NULL;
	}

	function insertFRDetail($inputId, $functionId, $user){
		$currentDateTime = date('Y-m-d H:i:s');
		$sqlStr = "INSERT INTO FN_REQ_DETAIL (functionId, inputId, createDate, createUser, updateDate, updateUser) VALUES ($functionId, $inputId, '$currentDateTime', '$user', '$currentDateTime', '$user')";
		$result = $this->db->query($sqlStr);
		return $result;
	}

	function uploadFR($param){
		$this->db->trans_start(); //Starting Transaction
		$this->db->trans_strict(FALSE);

		//insert Functional Requirement Header
		$functionId = $this->insertFRHeader($param[0]);
		if(NULL != $functionId){
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
				$result = $this->insertFRDetail($inputId, $functionId, $value->user);
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
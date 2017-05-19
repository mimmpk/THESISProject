<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FunctionalRequirement_model extends CI_Model {
	function __construct(){
		parent::__construct();
	}

	function searchFunctionalRequirementHeaderInfo($param){
		$where[] = "FRH.projectId = '".$param->projectId."'";
		
		if(isset($param->status) && ("2" != $param->status)){
			$where[] = "FRV.activeFlag = '".$param->status."'";
		}
		
		$where_clause = implode(' AND ', $where);
		$queryStr = "SELECT 
				FRH.functionId, 
				FRH.functionNo, 
				CAST(FRH.functionDescription AS VARBINARY(MAX)) as fnDesc, 
				FRV.functionVersionNumber as functionVersion, 
				FRV.activeFlag as functionStatus,
				CONVERT(nvarchar, FRV.effectiveStartDate , 103) as effectiveStartDate
			FROM M_FN_REQ_HEADER FRH 
			INNER JOIN M_FN_REQ_VERSION FRV 
			ON FRH.functionId = FRV.functionId 
			WHERE $where_clause 
			ORDER BY FRH.functionNo, FRV.functionVersionNumber";
		$result = $this->db->query($queryStr);
		return $result->result_array();
	}

	function searchExistFunctionalRequirement($fnId, $projectId){
		if(null != $projectId && !empty($projectId)){
			$where[] = "projectId = '$projectId'";
		}
		if(null != $fnId && !empty($fnId)){
			$where[] = "functionNo = '$fnId'";
		}
		$where_clause = implode(' AND ', $where);
		
		$queryStr = "SELECT * 
			FROM M_FN_REQ_HEADER 
			WHERE $where_clause";
		
		$result = $this->db->query($queryStr);
		return $result->result_array();
	}

	function searchFRInputInformation($projectId, $inputName){
		$queryStr = "SELECT * 
			FROM M_FN_REQ_INPUT i 
			WHERE i.projectId = $projectId 
			AND i.inputName = '$inputName'";
		$result = $this->db->query($queryStr);
		return $result->row();
	}

	function searchFRInputInfoByInputId($inputId){
		$queryStr = "SELECT * 
			FROM M_FN_REQ_INPUT i 
			WHERE i.inputId = $inputId";
		$result = $this->db->query($queryStr);
		return $result->row();
	}

	function searchExistFRInputInFunctionalRequirement($param){
		if(null != $param->functionId && !empty($param->functionId)){
			$where[] = "h.functionId = $param->functionId";
		}
		if(null != $param->inputName && !empty($param->inputName)){
			$where[] = "i.inputName = '$param->inputName'";
		}

		$where_clause = implode(' AND ', $where);

		$queryStr = "SELECT *
			FROM M_FN_REQ_HEADER h
			INNER JOIN M_FN_REQ_DETAIL d
			ON h.functionId = d.functionId
			AND d.activeFlag = '1'
			INNER JOIN M_FN_REQ_INPUT i
			ON d.inputId = i.inputId
			WHERE $where_clause";

		$result = $this->db->query($queryStr);
		return $result->result_array();

	}

	function searchExistFRInputsByTableAndColumnName($tableName, $columnName, $projectId){
		$queryStr = "SELECT *
			FROM M_FN_REQ_INPUT fi
			WHERE fi.refTableName = '$tableName'
			AND fi.refColumnName = '$columnName'
			AND fi.projectId = $projectId";
		$result = $this->db->query($queryStr);
		return $result->row();
	}

	function searchReferenceDatabaseSchemaInfo($param){

		if(isset($param->projectId) && !empty($param->projectId)){
			$where[] = "di.projectId = $param->projectId";
		}

		if(isset($param->referTableName) && !empty($param->referTableName)){
			$where[] = "di.tableName = '$param->referTableName'";
		}

		if(isset($param->referColumnName) && !empty($param->referColumnName)){
			$where[] = "di.columnName = '$param->referColumnName'";
		}

		$where_clause = implode(' AND ', $where);

		$queryStr = "SELECT di.*
			FROM M_DATABASE_SCHEMA_VERSION dv
			INNER JOIN M_DATABASE_SCHEMA_INFO di
			ON dv.schemaVersionId = di.schemaVersionId
			WHERE dv.activeFlag = '1'
			AND $where_clause";
		$result = $this->db->query($queryStr);
		return $result->row();
	}

	function searchFunctionalRequirementDetail($param){
		if(isset($param->projectId) && !empty($param->projectId)){
			$where[] = "h.projectId = $param->projectId";
		}

		if(isset($param->functionId) && !empty($param->functionId)){
			$where[] = "h.functionId = $param->functionId";
		}

		if(isset($param->inputId) && !empty($param->inputId)){
			$where[] = "d.inputId = $param->inputId";
		}

		if(isset($param->schemaVersionId) && !empty($param->schemaVersionId)){
			$where[] = "d.schemaVersionId = $param->schemaVersionId";
		}
		$where_clause = implode(' AND ', $where);

		$queryStr = "SELECT 
				h.projectId,
				h.functionId,
				h.functionNo,
				h.functionDescription,
				v.functionVersionNumber,
				d.inputId,
				d.schemaVersionId,
				i.inputName,
				db.tableName,
				db.columnName,
				db.dataType,
				db.dataLength,
				db.decimalPoint,
				db.constraintUnique,
				db.constraintNull,
				db.constraintDefault,
				db.constraintMinValue,
				db.constraintMaxValue
			FROM M_FN_REQ_HEADER h
			INNER JOIN M_FN_REQ_VERSION v
			ON h.functionId = v.functionId
			AND v.activeFlag = '1'
			INNER JOIN M_FN_REQ_DETAIL d
			ON h.functionId = d.functionId
			AND d.activeFlag = '1'
			INNER JOIN M_FN_REQ_INPUT i
			ON d.inputId = i.inputId
			INNER JOIN M_DATABASE_SCHEMA_INFO db
			ON i.refTableName = db.tableName
			AND i.refColumnName = db.columnName
			AND d.schemaVersionId = db.schemaVersionId
			WHERE $where_clause";
			//var_dump($queryStr);
		$result = $this->db->query($queryStr);
		return $result->result_array();
	}

	function insertFRHeader($param){
		$currentDateTime = date('Y-m-d H:i:s');
		$sqlStr = "INSERT INTO M_FN_REQ_HEADER (functionNo, functionDescription, projectId, createDate, createUser, updateDate, updateUser) VALUES ('{$param->functionNo}', '{$param->functionDescription}', {$param->projectId}, '$currentDateTime', '{$param->user}', '$currentDateTime', '{$param->user}')";
		$result = $this->db->query($sqlStr);
		if($result){
			$query = $this->db->query("SELECT IDENT_CURRENT('M_FN_REQ_HEADER') as last_id");
			$resultId = $query->result();
			return $resultId[0]->last_id;
		}
		return NULL;
	}

	function insertFRVersion($param){
		$currentDateTime = date('Y-m-d H:i:s');
		$sqlStr ="INSERT INTO M_FN_REQ_VERSION (functionId, functionVersionNumber, effectiveStartDate, effectiveEndDate, activeFlag, createDate, createUser, updateDate, updateUser) VALUES ($param->functionId, $param->functionVersionNo, '$param->effectiveStartDate', $param->effectiveEndDate, '$param->activeFlag', '$currentDateTime', '$param->user', '$currentDateTime', '$param->user')";
		$result = $this->db->query($sqlStr);
		return $result;
	}

	function insertFRInput($param){
		$currentDateTime = date('Y-m-d H:i:s');
		$sqlStr = "INSERT INTO M_FN_REQ_INPUT (projectId, inputName, refTableName, refColumnName, createDate, createUser, updateDate, updateUser) VALUES ({$param->projectId}, '{$param->inputName}', '{$param->referTableName}', '{$param->referColumnName}', '$currentDateTime', '{$param->user}', '$currentDateTime', '{$param->user}')";
		//var_dump($sqlStr);
		$result = $this->db->query($sqlStr);
		if($result){
			$query = $this->db->query("SELECT IDENT_CURRENT('M_FN_REQ_INPUT') as last_id");
			$resultId = $query->result();
			return $resultId[0]->last_id;
		}
		return NULL;
	}

	function insertFRDetail($functionId, $param){
		$currentDateTime = date('Y-m-d H:i:s');
		$sqlStr = "INSERT INTO M_FN_REQ_DETAIL (functionId, inputId, schemaVersionId, effectiveStartDate, effectiveEndDate, activeFlag, createDate, createUser, updateDate, updateUser) VALUES ($functionId, $param->inputId, $param->schemaVersionId, '$param->effectiveStartDate', $param->effectiveEndDate, '$param->activeFlag', '$currentDateTime', '$param->user', '$currentDateTime', '$param->user')";
		$result = $this->db->query($sqlStr);
		return $result;
	}

	function uploadFR($param){
		$this->db->trans_start(); //Starting Transaction
		$this->db->trans_strict(FALSE);

		//insert Functional Requirement Header
		$functionId = $this->insertFRHeader($param[0]);
		if(NULL != $functionId){
			$effectiveStartDate = date('Y-m-d H:i:s');
			
			$headerData = (object) array(
				'functionId' => $functionId, 
				'functionVersionNo' => $param[0]->functionVersionNo, 
				'effectiveStartDate' => $effectiveStartDate,
				'effectiveEndDate' => "NULL",
				'activeFlag' => $param[0]->activeFlag,
				'user' => $param[0]->user);

			//insert Functional Requirement Version
			$resultInsertVersion = $this->insertFRVersion($headerData);

			//insert Functional Requirement Detail
			foreach ($param as $detail) {
				$inputId = '';
				//Check Exist Input
				if(empty($detail->inputId)){
					//Insert New Input
					$inputId = $this->insertFRInput($detail);
					$detail->inputId = $inputId;
				}

				$resultSchemaInfo = $this->searchReferenceDatabaseSchemaInfo($detail);

				$detail->schemaVersionId = $resultSchemaInfo->schemaVersionId;
				$detail->effectiveStartDate = $effectiveStartDate;
				$detail->effectiveEndDate = "NULL";

				$resultInsertDetail = $this->insertFRDetail($functionId, $detail);
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
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
* Version Management Model
* Create Date: 08-06-2017
*/
class VersionManagement_model extends CI_Model{
	
	function __construct(){
		parent::__construct();
	}

	public function searchRelatedFunctionalRequirements($param){
		$sqlStr = "SELECT * 
			FROM M_FN_REQ_HEADER fh
			WHERE fh.projectId = $param->projectId";
		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}

	public function searchRelatedFunctionalRequirementVersion($param){
		$sqlStr = "SELECT 
				fh.functionId, 
				fh.functionNo, 
				fv.functionVersionId, 
				fv.functionVersionNumber 
			FROM M_FN_REQ_HEADER fh
			INNER JOIN M_FN_REQ_VERSION fv
			ON fh.functionId = fv.functionId
			WHERE fh.projectId = $param->projectId
			AND fh.functionId = $param->functionId
			ORDER BY fv.functionVersionNumber";
		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}

	public function searchFunctionalRequirementDetailsByVersion($param){
		$sqlStr = "SELECT 
			fh.functionId, 
			fh.functionNo, 
			fh.functionDescription,
			fd.inputId,
			fi.inputName,
			fd.schemaVersionId,
			ds.tableName, ds.columnName,
			ds.dataType, ds.dataLength,
			ds.decimalPoint,
			ds.constraintUnique, ds.constraintNull,
			ds.constraintDefault, ds.constraintPrimaryKey,
			ds.constraintMinValue, ds.constraintMaxValue
		FROM M_FN_REQ_HEADER fh
		INNER JOIN M_FN_REQ_DETAIL fd
		ON fh.functionId = fd.functionId
		INNER JOIN M_FN_REQ_INPUT fi
		ON fd.inputId = fi.inputId
		INNER JOIN M_DATABASE_SCHEMA_INFO ds
		ON ds.tableName = fi.refTableName
		AND ds.columnName = fi.refColumnName
		AND ds.schemaVersionId = fd.schemaVersionId
		WHERE fh.projectId = $param->projectId
		AND fh.functionId = $param->functionId
		AND  fd.effectiveStartDate <= '$param->targetDate'
		AND ('$param->targetDate' <= fd.effectiveEndDate OR fd.effectiveEndDate is null)
		AND (fd.effectiveEndDate != '$param->targetDate' OR fd.effectiveEndDate is null)";
		
		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}

	public function searchRelatedTestCases($param){
		$sqlStr = "SELECT th.*
			FROM M_TESTCASE_HEADER th
			WHERE th.projectId = $param->projectId";
		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}

	public function searchRelatedTestCaseVersion($param){
		$sqlStr = "SELECT 
				th.testCaseId,
				th.testCaseNo,
				tv.testCaseVersionId,
				tv.testCaseVersionNumber
			FROM M_TESTCASE_HEADER th
			INNER JOIN M_TESTCASE_VERSION tv
			ON th.testCaseId = tv.testCaseId
			WHERE th.testCaseId = $param->testCaseId
			ORDER BY tv.testCaseVersionNumber";
		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}

	public function searchTestCaseDetailByVersion($param){
		$sqlStr = "SELECT 
				th.testCaseId, 
				th.testCaseNo, 
				th.testCaseDescription, 
				th.expectedResult,
				td.refInputName, 
				td.testData
			FROM M_TESTCASE_HEADER th
			INNER JOIN M_TESTCASE_DETAIL td
			on th.testCaseId = td.testCaseId
			WHERE th.testCaseId = $param->testCaseId
			AND td.effectiveStartDate <= '$param->targetDate'
			AND (td.effectiveEndDate  >= '$param->targetDate' OR td.effectiveEndDate is null)
			AND (td.effectiveEndDate  != '$param->targetDate' OR td.effectiveEndDate is null)
			ORDER BY td.sequenceNo";
		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}

	public function searchRelatedTableName($param){
		$sqlStr = "SELECT distinct tableName
			FROM M_DATABASE_SCHEMA_INFO
			WHERE projectId = $param->projectId";
		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}

	public function searchRelatedColumnName($param){
		$sqlStr = "SELECT distinct columnName
			FROM M_DATABASE_SCHEMA_INFO
			WHERE projectId = $param->projectId
			AND tableName = '$param->tableName'
			ORDER BY columnName";
		$result = $this->db->query($sqlStr);
		return $result->result_array();	
	}

	public function searchRelatedColumnVersion($param){
		$sqlStr = "SELECT tableName, columnName, schemaVersionId, schemaVersionNumber
			FROM M_DATABASE_SCHEMA_VERSION
			WHERE projectId = $param->projectId
			AND tableName= '$param->tableName'
			AND columnName = '$param->columnName'
			order by schemaVersionNumber";
		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}

	public function searchDatabaseSchemaDetailByVersion($param){
		if(){
			$where[] = "projectId = $param->projectId";
		}
		if(){
			$where[] = "tableName= '$param->tableName'";
		}
		if(){
			$where[]
		}
		if(){
			$where[]
		}
		$where_condition = implode(" AND ", $where);
		
		$sqlStr = "SELECT *
			FROM M_DATABASE_SCHEMA_INFO
			WHERE $where_condition";

		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}
}

?>
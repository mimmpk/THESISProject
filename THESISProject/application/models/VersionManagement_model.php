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

	public function searchDiffPreviousVersion_Requirements($param){
		$sqlStr = "
			SELECT f.* 
			FROM
			(
				SELECT d.*, 
					count(inputId) over (partition by d.inputId, d.schemaVersionId order by d.inputId,d.schemaVersionId) as _count,
					row_number() over (partition by d.inputId, d.schemaVersionId order by d.inputId,d.schemaVersionId) as _orderNumber
				FROM
				(
					SELECT 
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
						ds.constraintMinValue, ds.constraintMaxValue,
						'newer' as version
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
					AND fd.effectiveStartDate <= '$param->cTargetDate'
					AND ('$param->cTargetDate' <= fd.effectiveEndDate OR fd.effectiveEndDate is null)
					AND (fd.effectiveEndDate != '$param->cTargetDate' OR fd.effectiveEndDate is null)
					UNION
					SELECT
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
						ds.constraintMinValue, ds.constraintMaxValue,
						'older' as version
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
					AND  fd.effectiveStartDate <= '$param->pTargetDate'
					AND ('$param->pTargetDate' <= fd.effectiveEndDate OR fd.effectiveEndDate is null)
					AND (fd.effectiveEndDate != '$param->pTargetDate' OR fd.effectiveEndDate is null)
				)d
			)f
			where f._orderNumber = 1";
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

	public function searchDiffPreviousVersion_TestCase($param){
		$sqlStr = "
			SELECT f.* 
			FROM
			(
				SELECT d.*, 
					count(d.testCaseId) over (partition by d.refInputId,d.sequenceNo order by d.refInputId) as _count,
					row_number() over (partition by d.refInputId,d.sequenceNo order by d.refInputId) as _orderNumber
				FROM
				(
				SELECT 
					th.testCaseId, 
					th.testCaseNo, 
					th.testCaseDescription, 
					th.expectedResult,
					td.refInputName,
					td.refInputId, 
					td.testData,
					td.sequenceNo,
					'newer' as version
				FROM M_TESTCASE_HEADER th
				INNER JOIN M_TESTCASE_DETAIL td
				ON th.testCaseId = td.testCaseId
				WHERE th.testCaseId = $param->testCaseId
				AND td.effectiveStartDate <= '$param->cTargetDate'
				AND (td.effectiveEndDate  >= '$param->cTargetDate' OR td.effectiveEndDate is null)
				AND (td.effectiveEndDate  != '$param->cTargetDate' OR td.effectiveEndDate is null)
				UNION
				SELECT
					th.testCaseId, 
					th.testCaseNo, 
					th.testCaseDescription, 
					th.expectedResult,
					td.refInputName, 
					td.refInputId, 
					td.testData,
					td.sequenceNo,
					'older' as version
				FROM M_TESTCASE_HEADER th
				INNER JOIN M_TESTCASE_DETAIL td
				ON th.testCaseId = td.testCaseId
				WHERE th.testCaseId = $param->testCaseId
				AND td.effectiveStartDate <= '$param->pTargetDate'
				AND (td.effectiveEndDate  >= '$param->pTargetDate' OR td.effectiveEndDate is null)
				AND (td.effectiveEndDate  != '$param->pTargetDate' OR td.effectiveEndDate is null)
				)d
			)f
			WHERE f._orderNumber = 1";
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
		if(isset($param->projectId) && !empty($param->projectId)){
			$where[] = "i.projectId = $param->projectId";
		}
		if(isset($param->tableName) && !empty($param->tableName)){
			$where[] = "i.tableName = '$param->tableName'";
		}
		if(isset($param->columnName) && !empty($param->columnName)){
			$where[] = "i.columnName = '$param->columnName'";
		}
		if(isset($param->schemaVersionId) && !empty($param->schemaVersionId)){
			$where[] = "v.schemaVersionId = $param->schemaVersionId";
		}
		$where_condition = implode(" AND ", $where);
		
		$sqlStr = "SELECT i.*, v.schemaVersionId, v.schemaVersionNumber, v.activeFlag
			FROM M_DATABASE_SCHEMA_INFO i
			INNER JOIN M_DATABASE_SCHEMA_VERSION v
			ON i.tableName = v.tableName
			AND i.columnName = v.columnName
			AND i.schemaVersionId = v.schemaVersionId
			WHERE $where_condition
			ORDER BY tableName, columnName, schemaVersionNumber";

		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}

	public function searchRelatedRTMVersion($projectId){
		$sqlStr = "SELECT * 
			FROM M_RTM_VERSION 
			WHERE projectId = $projectId 
			ORDER BY rtmVersionNumber";
		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}

	public function searchRTMDetailByVersion($param){
		$sqlStr = "SELECT r.*, fh.functionNo, th.testCaseNo
			FROM M_RTM r
			INNER JOIN M_FN_REQ_HEADER fh
			ON r.functionId = fh.functionId
			INNER JOIN M_TESTCASE_HEADER th
			ON r.testCaseId = th.testCaseId
			WHERE r.projectId = $param->projectId
			AND r.effectiveStartDate <= '$param->targetDate'
			AND (r.effectiveEndDate  >= '$param->targetDate' OR r.effectiveEndDate is null)
			AND (r.effectiveEndDate != '$param->targetDate' OR r.effectiveEndDate is null)
			ORDER BY fh.functionNo, th.testCaseNo";
		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}

	public function searchDiffPreviousVersion_RTM($param){
		$sqlStr = "
		SELECT f.* FROM
			(
			SELECT d.*, 
				count(d.testCaseId) over (partition by d.functionId,d.testCaseId order by d.functionId,d.testCaseId) as _count,
				row_number() over (partition by d.functionId,d.testCaseId order by d.functionId,d.testCaseId) as _orderNumber
			FROM
			(
				SELECT 
					r.projectId,r.functionId,r.testCaseId,
					fh.functionNo, th.testCaseNo, 'newer' as version
				FROM M_RTM r
				INNER JOIN M_FN_REQ_HEADER fh
				ON r.functionId = fh.functionId
				INNER JOIN M_TESTCASE_HEADER th
				ON r.testCaseId = th.testCaseId
				WHERE r.projectId = $param->projectId
				AND r.effectiveStartDate <= '$param->cTargetDate'
				AND (r.effectiveEndDate >= '$param->cTargetDate' OR r.effectiveEndDate is null)
				AND (r.effectiveEndDate != '$param->cTargetDate' OR r.effectiveEndDate is null)
				UNION
				SELECT 
					r.projectId,r.functionId,r.testCaseId,
					fh.functionNo, th.testCaseNo, 'older' as version
				FROM M_RTM r
				INNER JOIN M_FN_REQ_HEADER fh
				ON r.functionId = fh.functionId
				INNER JOIN M_TESTCASE_HEADER th
				ON r.testCaseId = th.testCaseId
				WHERE r.projectId = $param->projectId
				AND r.effectiveStartDate <= '$param->pTargetDate'
				AND (r.effectiveEndDate  >= '$param->pTargetDate' OR r.effectiveEndDate is null)
				AND (r.effectiveEndDate != '$param->pTargetDate' OR r.effectiveEndDate is null)
			)d
		)f
		WHERE f._orderNumber = 1
		ORDER BY f.functionNo, f.testCaseNo";

		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}

}

?>
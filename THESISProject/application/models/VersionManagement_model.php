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
}

?>
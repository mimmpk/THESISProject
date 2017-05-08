<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
* Database Schema Model
*/
class DatabaseSchema_model extends CI_Model{
	
	function __construct(){
		parent::__construct();
	}

	function searchDatabaseSchemaByCriteria($projectId, $dbSchemaStatus){
		$where[] = "di.projectId = ".$projectId." ";
		if("2" != $dbSchemaStatus)
			$where[] = "dv.activeFlag = '".$dbSchemaStatus."'";
		
		$where_clause = implode(' AND ', $where);

		$sqlStr = "SELECT 
				di.tableName,
				di.columnName,
				dv.schemaVersionNumber,
				CONVERT(nvarchar, dv.effectiveStartDate, 103) as effectiveStartDate,
				COALESCE(CONVERT(nvarchar, dv.effectiveEndDate, 103), '-') as effectiveEndDate,
				dv.activeFlag
			FROM M_DATABASE_SCHEMA_INFO di 
			INNER JOIN M_DATABASE_SCHEMA_VERSION dv
			ON di.schemaVersionId = dv.schemaVersionId
			WHERE $where_clause
			ORDER BY di.tableName, di.columnName, dv.schemaVersionNumber";

		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}

	function searchExistDatabaseSchemaInfo($tableName, $columnName, $projectId){
		$sqlStr = "SELECT di.*
			FROM M_DATABASE_SCHEMA_INFO di
			INNER JOIN M_DATABASE_SCHEMA_VERSION dv
			ON di.schemaVersionId = dv.schemaVersionId
			WHERE di.projectId = $projectId
			AND di.tableName = '$tableName'
			AND di.columnName =  '$columnName'
			AND dv.activeFlag = '1'";
		$result = $this->db->query($sqlStr);
		return $result->row();
	}

	function uploadDatabaseSchema($param, $user, $projectId){
		$this->db->trans_start(); //Starting Transaction
		$this->db->trans_strict(FALSE);

		foreach($param as $value){
			//Insert Database Schema Version
			$result = $this->insertDatabaseSchemaVersion($value, $user);
			if(null != $result){
				//Insert Database Schema Information
				$value->schemaVersionId = $result;
				$this->insertDatabaseSchemaInfo($value, $projectId);
			}
		}
		
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

	function insertDatabaseSchemaVersion($param, $user){
		$currentDate = date('Y-m-d');
		$currentDateTime = date('Y-m-d H:i:s');

		$sqlStr = "INSERT INTO M_DATABASE_SCHEMA_VERSION (tableName, columnName, schemaVersionNumber, effectiveStartDate, effectiveEndDate, activeFlag, previousSchemaVersionId, createDate, createUser, updateDate, updateUser) VALUES ('{$param->tableName}', '{$param->columnName}', {$param->schemaVersionNo}, '$currentDate', NULL, '{$param->status}', NULL, '$currentDateTime', '$user', '$currentDateTime', '$user')";

		$result = $this->db->query($sqlStr);
		if($result){
			$query = $this->db->query("SELECT IDENT_CURRENT('M_DATABASE_SCHEMA_VERSION') as last_id");
			$resultId = $query->result();
			return $resultId[0]->last_id;
		}
		return NULL;
	}

	function insertDatabaseSchemaInfo($param, $projectId){
		$dataType = $param->dataType;
		$dataLength = !empty($param->dataLength)? $param->dataLength : "NULL";
		$scale = !empty($param->scale)? $param->scale : "NULL";
		$defaultValue = !empty($param->defaultValue)? $param->defaultValue : "NULL";
		$minValue = !empty($param->minValue)? $param->minValue : "NULL";
		$maxValue = !empty($param->maxValue)? $param->maxValue : "NULL";

		$sqlStr = "INSERT INTO M_DATABASE_SCHEMA_INFO (tableName, columnName, schemaVersionId, dataType, [dataLength], decimalPoint, constraintPrimaryKey, constraintUnique, constraintDefault, constraintNull, constraintMinValue, constraintMaxValue, projectId) VALUES ('{$param->tableName}', '{$param->columnName}', {$param->schemaVersionId}, '{$dataType}', $dataLength, {$scale}, '{$param->primaryKey}', '{$param->unique}' , {$defaultValue}, '{$param->null}', {$minValue}, {$maxValue}, $projectId)";

		$result = $this->db->query($sqlStr);
		return $result;
	}
}

?>
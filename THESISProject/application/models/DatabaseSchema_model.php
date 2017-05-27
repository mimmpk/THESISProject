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

	function getTableByProjectId($projectId, $term){
		$row_set = array();

		$sqlStr = "SELECT distinct i.tableName 
			FROM M_DATABASE_SCHEMA_INFO i
			INNER JOIN M_DATABASE_SCHEMA_VERSION v
			ON i.schemaVersionId = v.schemaVersionId
			WHERE i.projectId = $projectId
			AND i.tableName like '%$term%'
			AND v.activeFlag = '1'";
		$result = $this->db->query($sqlStr);

		if($result->num_rows() > 0){
	      	foreach ($result->result_array() as $row){
	        	$row_set[] = htmlentities(stripslashes($row['tableName'])); //build an array
	      	}
    	}

    	return json_encode($row_set); //format the array into json data
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
			$value->projectId = $projectId;
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
		$currentDateTime = date('Y-m-d H:i:s');

		$previousSchemaVersionId = (empty($param->previousVersionId)? "NULL": $param->previousVersionId);

		$sqlStr = "INSERT INTO M_DATABASE_SCHEMA_VERSION (projectId, tableName, columnName, schemaVersionNumber, effectiveStartDate, effectiveEndDate, activeFlag, previousSchemaVersionId, createDate, createUser, updateDate, updateUser) VALUES ($param->projectId, '{$param->tableName}', '{$param->columnName}', {$param->schemaVersionNo}, '$currentDateTime', NULL, '{$param->status}', $previousSchemaVersionId, '$currentDateTime', '$user', '$currentDateTime', '$user')";

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

	function updateDatabaseSchemaVersion($param){
		$currentDateTime = date('Y-m-d H:i:s');
		$sqlStr = "UPDATE M_DATABASE_SCHEMA_VERSION
			SET effectiveEndDate = '$param->currentDate', 
				activeFlag = '$param->activeFlag', 
				updateDate = '$currentDateTime', 
				updateUser = '$param->user' 
			WHERE projectId = $param->projectId 
			AND tableName = '$param->tableName' 
			AND columnName = '$param->columnName' 
			AND schemaVersionId = $param->oldSchemaVersionId 
			AND updateDate = '$param->oldUpdateDate'";

		$result = $this->db->query($sqlStr);
		return $this->db->affected_rows();
	}
}

?>
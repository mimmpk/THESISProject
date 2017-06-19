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
				dv.activeFlag,
				CONVERT(nvarchar, dv.createDate, 120) as createDate,
				CONCAT(u.firstname, ' ', u.lastname) as createUser
			FROM M_DATABASE_SCHEMA_INFO di 
			INNER JOIN M_DATABASE_SCHEMA_VERSION dv
			ON di.schemaVersionId = dv.schemaVersionId
			LEFT JOIN M_USERS u
			ON dv.createUser = u.username
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
			AND di.tableName  = '$tableName'
			AND di.columnName = '$columnName'
			AND dv.activeFlag = '1'";
		$result = $this->db->query($sqlStr);
		return $result->row();
	}

	function searchDatabaseSchemaVersionInformationByCriteria($param){
		$sqlStr = "SELECT v.tableName, v.columnName, v.schemaVersionId, v.schemaVersionNumber, 
			v.effectiveStartDate, v.effectiveEndDate, v.previousSchemaVersionId
			FROM M_DATABASE_SCHEMA_VERSION v
			INNER JOIN M_DATABASE_SCHEMA_INFO i
			ON v.schemaVersionId = i.schemaVersionId
			WHERE v.projectId = $param->projectId
			AND v.tableName = '$param->tableName'
			AND v.columnName = '$param->columnName'
			AND v.schemaVersionNumber = $param->versionNumber";
		$result = $this->db->query($sqlStr);
		return $result->row();
	}

	function uploadDatabaseSchema($param, $user, $projectId){
		$this->db->trans_begin(); //Starting Transaction

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
		$effectiveEndDate = !empty($param->effectiveEndDate)? "'".$param->effectiveEndDate."'": "NULL";

		if(isset($param->projectId) && !empty($param->projectId)){
			$where[] = "projectId = $param->projectId ";
		}
		if(isset($param->tableName) && !empty($param->tableName)){
			$where[] = "tableName = '$param->tableName'";
		}
		if(isset($param->columnName) && !empty($param->columnName)){
			$where[] = "columnName = '$param->columnName'";
		}
		if(isset($param->oldSchemaVersionId) && !empty($param->oldSchemaVersionId)){
			$where[] = "schemaVersionId = $param->oldSchemaVersionId";
		}
		if(isset($param->oldUpdateDate) && !empty($param->oldUpdateDate)){
			$where[] = "updateDate = '$param->oldUpdateDate'";
		}
		$where_clause = implode(" AND ", $where);

		$sqlStr = "UPDATE M_DATABASE_SCHEMA_VERSION
			SET effectiveEndDate = $effectiveEndDate, 
				activeFlag = '$param->activeFlag', 
				updateDate = '$param->currentDate', 
				updateUser = '$param->user' 
			WHERE $where_clause";

		$result = $this->db->query($sqlStr);
		return $this->db->affected_rows();
	}

	function deleteDatabaseSchemaVersion($param){
		$sqlStr = "DELETE FROM M_DATABASE_SCHEMA_VERSION
			WHERE projectId = $param->projectId
			AND tableName = '$param->tableName'
			AND columnName = '$param->columnName' 
			AND schemaVersionId = $param->schemaVersionId";
		$result = $this->db->query($sqlStr);
		return $this->db->affected_rows();
	}

	function deleteDatabaseSchemaInfo($param){
		$sqlStr = "DELETE FROM M_DATABASE_SCHEMA_INFO
			WHERE tableName = '$param->tableName'
			AND columnName = '$param->columnName' 
			AND schemaVersionId = $param->schemaVersionId";
		$result = $this->db->query($sqlStr);
		return $this->db->affected_rows();
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
				CASE WHEN u.CONSTRAINT_NAME IS NULL THEN 'N' ELSE 'Y' END as IS_UNIQUE,
				CASE WHEN p.CONSTRAINT_NAME IS NULL THEN 'N' ELSE 'Y' END as IS_PRIMARY_KEY,
				c.CONSTRAINT_NAME as CHECK_CONSTRAINT_NAME
			FROM INFORMATION_SCHEMA.COLUMNS isc
			LEFT JOIN (
				SELECT istc1.CONSTRAINT_NAME, istc1.TABLE_NAME, iscc.COLUMN_NAME
				FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS istc1
				INNER JOIN INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE iscc
				ON iscc.CONSTRAINT_NAME = istc1.CONSTRAINT_NAME
				AND istc1.CONSTRAINT_TYPE = 'UNIQUE'
			) as u ON isc.COLUMN_NAME = u.COLUMN_NAME AND isc.TABLE_NAME = u.TABLE_NAME
			LEFT JOIN (
				SELECT istc1.CONSTRAINT_NAME, istc1.TABLE_NAME, iscc.COLUMN_NAME
				FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS istc1
				INNER JOIN INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE iscc
				ON iscc.CONSTRAINT_NAME = istc1.CONSTRAINT_NAME
				AND istc1.CONSTRAINT_TYPE = 'PRIMARY KEY'
			) as p ON isc.COLUMN_NAME = p.COLUMN_NAME AND isc.TABLE_NAME = p.TABLE_NAME
			LEFT JOIN (
				SELECT istc1.CONSTRAINT_NAME, istc1.TABLE_NAME, iscc.COLUMN_NAME
				FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS istc1
				INNER JOIN INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE iscc
				ON iscc.CONSTRAINT_NAME = istc1.CONSTRAINT_NAME
				AND istc1.CONSTRAINT_TYPE = 'CHECK'
			) as c ON isc.COLUMN_NAME = c.COLUMN_NAME AND isc.TABLE_NAME = c.TABLE_NAME
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
				     $dbSchemaDetail['minValue'] = '';
				     $dbSchemaDetail['maxValue'] = '';
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
}

?>
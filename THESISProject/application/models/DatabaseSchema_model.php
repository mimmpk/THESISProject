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
		$where[] = "dc.projectId = ".$projectId." ";
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
		INNER JOIN M_PROJECT_DATABASE_CONFIG dc
		ON di.databaseConfigId = dc.databaseConfigId
		INNER JOIN M_DATABASE_SCHEMA_VERSION dv
		ON di.schemaVersionId = dv.schemaVersionId
		WHERE $where_clause
		ORDER BY di.tableName, di.columnName, dv.schemaVersionNumber";

		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}
}

?>
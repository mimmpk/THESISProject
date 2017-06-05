<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
* Cancellation Model
*/
class Cancellation_model extends CI_Model{
	
	function __construct(){
		parent::__construct();
	}

	public function searchChangesInformationForCancelling($projectId = '', $changeRequestNo = ''){
		$where = "";
		
		if(null != $projectId && !empty($projectId)){
			$where .= "AND h.projectId = $projectId ";
		}

		if(null != $changeRequestNo && !empty($changeRequestNo)){
			$where .= "AND h.changeRequestNo = '$changeRequestNo' ";
		}

		$sqlStr = "
			SELECT 
				h.changeRequestNo,
				CONVERT(nvarchar, h.changeDate, 103) as changeDate,
				CONCAT(u.firstname, '   ', u.lastname) as changeUser,
				h.changeFunctionNo,
				h.changeFunctionVersion,
				fh.functionDescription,
				CASE WHEN h.changeRequestNo = (
					SELECT TOP 1 changeRequestNo
					FROM T_CHANGE_REQUEST_HEADER 
					WHERE projectId = $projectId
					ORDER by changeDate desc) THEN 'Y' ELSE 'N' 
				END as isLatestChange
			FROM T_CHANGE_REQUEST_HEADER h 
			INNER JOIN M_USERS u 
			ON h.changeUserId = u.userId
			INNER JOIN M_FN_REQ_HEADER fh 
			ON h.changeFunctionId = fh.functionId 
			WHERE h.changeStatus = 'CLS' $where 
			ORDER BY h.changeDate desc";
		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}

	function searchChangeHistoryDatabaseSchemaByCriteria($param){
		if(!empty($param->changeRequestNo)){
			$where[] = "changeRequestNo = '$param->changeRequestNo'";
		}

		if(!empty($param->tableName)){
			$where[] = "tableName = '$param->tableName'";
		}

		if(!empty($param->columnName)){
			$where[] = "columnName = '$param->columnName'";
		}

		$where_condition = implode(' AND ', $where);
		$sqlStr = "SELECT * FROM T_CHANGE_HISTORY_SCHEMA 
			WHERE $where_condition";
		$result = $this->db->query($sqlStr);
		return $result->row();
	}

	public function getCancelChangeRequestInputDetail($sequenceNo){
		$sqlStr = "
		SELECT 
			c.inputName,
			case 
				when c.dataType is null then '' 
				else d.dataType 
			end as dataType,
			case 
				when c.dataLength is null then null 
				else d.dataLength 
			end as dataLength,
			case 
				when c.scale is null then null 
				else d.decimalPoint 
			end as scale,
			case 
				when c.constraintUnique is null then '' 
				else d.constraintUnique 
			end as constraintUnique,
			case 
				when c.constraintNotNull is null then '' 
				else d.constraintNull 
			end as constraintNotNull,
			case 
				when c.constraintDefault is null then '' 
				else d.constraintDefault 
			end as constraintDefault,
			case 
				when c.constraintMin is null then '' 
				else d.constraintMinValue 
			end as constraintMin,
			case 
				when c.constraintMax is null then '' 
				else d.constraintMaxValue 
			end as constraintMax,
			c.refTableName, c.refColumnName
		FROM T_CHANGE_REQUEST_DETAIL c
		INNER JOIN M_FN_REQ_INPUT r
		ON c.refInputId = r.inputId
		INNER JOIN M_DATABASE_SCHEMA_INFO d
		ON r.refTableName = d.tableName
		AND r.refColumnName = d.columnName
		AND c.refSchemaVersionId = d.schemaVersionId
		WHERE c.sequenceNo = $sequenceNo";

		$result = $this->db->query($sqlStr);
		return $result->row_array();
	}

}

?>
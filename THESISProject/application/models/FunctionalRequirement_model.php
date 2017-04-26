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
		$queryStr = "SELECT * FROM FN_REQ_HEADER WHERE projectId = '$projectId' AND functionId = '$fnId'";
		$result = $this->db->query($queryStr);
		return $result->num_rows();
	}
}

?>
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
* Requirements Traceability Matrix Model
*/
class RTM_model extends CI_Model{
	
	function __construct(){
		parent::__construct();
	}

	function searchRTMInfoByCriteria($projectId){
		$sqlStr = "SELECT 
				f.functionNo,
				t.testCaseNo
			FROM M_RTM r
			INNER JOIN M_FN_REQ_HEADER f
			ON r.functionId = f.functionId
			INNER JOIN M_TESTCASE_HEADER t
			ON r.testCaseId = t.testCaseId
			WHERE r.projectId = '$projectId'
			AND r.activeFlag = '1'";

		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}

	function searchExistRTMInfoByTestCaseId($projectId, $testCaseId){
		$sqlStr = "SELECT *
			FROM M_RTM r
			WHERE r.projectId = $projectId
			AND r.testCaseId= $testCaseId";
	 	$result = $this->db->query($sqlStr);
		return $result->result_array();
	}

	function searchExistRTMVersion($projectId){
		$sqlStr = "SELECT *
			FROM M_RTM_VERSION 
			WHERE projectId = $projectId
			AND activeFlag = '1'";
		$result = $this->db->query($sqlStr);
		return $result->row();
	}

	function insertRTMInfo($param, $user){
		$currentDateTime = date('Y-m-d H:i:s');
		$sqlStr = "INSERT INTO M_RTM (projectId, functionId, testCaseId, effectiveStartDate, effectiveEndDate, activeFlag, createDate, createUser, updateDate, updateUser) VALUES ($param->projectId, $param->functionId, $param->testCaseId, '$param->effectiveStartDate', NULL, '$param->activeFlag', '$currentDateTime', '$user', '$currentDateTime', '$user') ";
		$result = $this->db->query($sqlStr);
		return $result;
	}

	function insertRTMVersion($param, $user){
		$currentDateTime = date('Y-m-d H:i:s');
		$sqlStr = "INSERT INTO M_RTM_VERSION (projectId, rtmVersionNumber, effectiveStartDate, effectiveEndDate, activeFlag, previousVersionId, createDate,	createUser, updateDate, updateUser) VALUES ($param->projectId, $param->versionNo, '$param->effectiveStartDate', NULL, '$param->activeFlag', NULL, '$currentDateTime', '$user', '$currentDateTime', '$user')";
		$result = $this->db->query($sqlStr);
		return $result;
	}

	function uploadRTM($param, $user){
		$this->db->trans_start(); //Starting Transaction
		$this->db->trans_strict(FALSE);

		$effectiveStartDate = '';
		
		//Check Existing RTM Version
		$result = $this->searchExistRTMVersion($param[0]->projectId);
		if((NULL != $result) && (0 < count($result))){
			$effectiveStartDate = $result->effectiveStartDate;
		}else{
			$effectiveStartDate = date('Y-m-d H:i:s');
			$param[0]->effectiveStartDate = $effectiveStartDate;
			$resultInsertRTMVersion = $this->insertRTMVersion($param[0], $user);
		}

		foreach ($param as $value) {
			$value->effectiveStartDate = $effectiveStartDate;
			$resultInsertRTMInfo = $this->insertRTMInfo($value, $user);
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
}

?>
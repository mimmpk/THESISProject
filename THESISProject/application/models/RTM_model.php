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
				t.testCaseNo,
				CONVERT(nvarchar, r.createDate, 120) as createDate,
				CONCAT(u.firstname, '  ', u.lastname) as createUser
			FROM M_RTM r
			INNER JOIN M_FN_REQ_HEADER f
			ON r.functionId = f.functionId
			INNER JOIN M_TESTCASE_HEADER t
			ON r.testCaseId = t.testCaseId
			LEFT JOIN M_USERS u
			ON r.createUser = u.username
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

	function searchRTMVersionInfo($param){
		$sqlStr = "SELECT *
			FROM M_RTM_VERSION 
			WHERE projectId = $param->projectId
			AND rtmVersionId = $param->rtmVersionId";
		$result = $this->db->query($sqlStr);
		return $result->row();
	}

	function searchRTMVersionInfoByCriteria($param){
		$sqlStr = "SELECT 
			new.rtmVersionId as new_rtmVersionId, 
			new.effectiveStartDate, 
			new.effectiveEndDate, 
			old.rtmVersionId as old_rtmVersionId, 
			old.updateDate as old_updateDate
			FROM M_RTM_VERSION new
			INNER JOIN M_RTM_VERSION old
			ON new.previousVersionId = old.rtmVersionId
			WHERE new.projectId = {$param->projectId}
			AND new.rtmVersionNumber = {$param->rtmVersionNumber}";
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
		$previousVersionId = !empty($param->previousVersionId)? $param->previousVersionId : 'NULL';

		$sqlStr = "INSERT INTO M_RTM_VERSION (projectId, rtmVersionNumber, effectiveStartDate, effectiveEndDate, activeFlag, previousVersionId, createDate,	createUser, updateDate, updateUser) VALUES ($param->projectId, $param->versionNo, '$param->effectiveStartDate', NULL, '$param->activeFlag', $previousVersionId, '$currentDateTime', '$user', '$currentDateTime', '$user')";
		$result = $this->db->query($sqlStr);
		return $result;
	}

	function updateRTMInfo($param){
		$effectiveEndDate = !empty($param->effectiveEndDate)? "'".$param->effectiveEndDate."'": "NULL";
		$sqlStr = "UPDATE M_RTM
			SET effectiveEndDate = $effectiveEndDate,
				activeFlag = '$param->activeFlag',
				updateDate = '$param->updateDate',
				updateUser = '$param->user'
			WHERE projectId = $param->projectId 
			AND functionId = $param->functionId 
			AND testCaseId = $param->testCaseId";
		$result = $this->db->query($sqlStr);
		return $this->db->affected_rows();
	}

	function updateRTMVersion($param){
		$effectiveEndDate = !empty($param->effectiveEndDate)? "'".$param->effectiveEndDate."'": "NULL";

		$sqlStr = "UPDATE M_RTM_VERSION
			SET effectiveEndDate = $effectiveEndDate,
				activeFlag = '$param->activeFlag',
				updateDate = '$param->updateDate',
				updateUser = '$param->user'
			WHERE rtmVersionId = $param->rtmVersionIdCondition 
			AND projectId = $param->projectId 
			AND updateDate = '$param->updateDateCondition'";
		$result = $this->db->query($sqlStr);
		return $this->db->affected_rows();
	}

	function deleteRTMVersion($param){
		$sqlStr = "DELETE FROM M_RTM_VERSION
			WHERE projectId = $param->projectId
			AND rtmVersionId = $param->rtmVersionId";
		$result = $this->db->query($sqlStr);
		return $this->db->affected_rows();
	}

	function deleteRTMInfo($param){
		$sqlStr = "DELETE FROM M_RTM
			WHERE projectId = {$param->projectId}
			AND functionId = {$param->fucntionId}
			AND testCaseId = {$param->testCaseId}";
		$result = $this->db->query($sqlStr);
		return $this->db->affected_rows();	
	}

	function uploadRTM($param, $user){
		$this->db->trans_begin(); //Starting Transaction
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
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
* Test Case Model
*/
class TestCase_model extends CI_Model{
	
	function __construct(){
		parent::__construct();
	}

	function searchTestCaseInfoByCriteria($projectId, $testCaseStatus){
		$where[] = "th.projectId = ".$projectId." ";
		if("2" != $testCaseStatus)
			$where[] = "tv.activeFlag = '".$testCaseStatus."'";

		$where_clause = implode(' AND ', $where);
		$sqlStr = "SELECT 
				th.testCaseId,
				th.testCaseNo,
				th.testCaseDescription,
				th.expectedResult,
				tv.testCaseVersionNumber as testCaseVersion,
				CONVERT(nvarchar, tv.effectiveStartDate, 103) as effectiveStartDate,
				CONVERT(nvarchar, tv.effectiveEndDate, 103) as effectiveEndDate,
				tv.activeFlag,
				h.functionNo,
				h.functionDescription
			FROM M_TESTCASE_HEADER th
			INNER JOIN M_TESTCASE_VERSION tv
			ON th.testCaseId = tv.testCaseId
			LEFT JOIN M_RTM r
			ON th.testCaseId = r.testCaseId
			LEFT JOIN M_FN_REQ_HEADER h
			ON r.functionId = h.functionId
			ORDER BY th.testCaseNo, tv.testCaseVersionNumber";
		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}

	function searchExistTestCaseDetail($projectId, $testCaseNo = '', $refInputId = ''){
		if(!empty($projectId)){
			$where[] = "th.projectId = $projectId";
		}

		if(!empty($testCaseNo)){
			$where[] = "th.testCaseNo = '$testCaseNo'";
		}

		if(!empty($refInputId)){
			$where[] = "td.refInputId = $refInputId";
		}

		$where_condition = implode(' AND ', $where);

		$sqlStr = "SELECT 
				th.testCaseId,
				th.testCaseNo,
				td.refInputId,
				td.refInputName,
				td.testData
			FROM M_TESTCASE_HEADER th
			INNER JOIN M_TESTCASE_DETAIL td
			ON th.testCaseId = td.testCaseId
			WHERE td.activeFlag = '1'
			AND $where_condition";
		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}

	function searchExistTestCaseHeader($projectId, $testCaseNo){
		if(null != $projectId && !empty($projectId)){
			$where[] = "th.projectId = $projectId";
		}
		if(null != $testCaseNo && !empty($testCaseNo)){
			$where[] = "th.testCaseNo = '$testCaseNo'";
		}
		$where_clause = implode(' AND ', $where);

		$sqlStr = "SELECT *
			FROM M_TESTCASE_HEADER th
			WHERE $where_clause";
		$result = $this->db->query($sqlStr);
		return $result->row();
	}

	function insertTestCaseHeader($param, $user){
		$currentDateTime = date('Y-m-d H:i:s');
		$sqlStr = "INSERT INTO M_TESTCASE_HEADER (testCaseNo, testCaseDescription, expectedResult, projectId, createDate, createUser, updateDate, updateUser) VALUES ('{$param->testCaseNo}', '{$param->testCaseDescription}', '{$param->expectedResult}', {$param->projectId}, '{$currentDateTime}', '$user', '{$currentDateTime}', '$user')";
		$result = $this->db->query($sqlStr);
		if($result){
			$query = $this->db->query("SELECT IDENT_CURRENT('M_TESTCASE_HEADER') as last_id");
			$resultId = $query->result();
			return $resultId[0]->last_id;
		}
		return null;
	}

	function insertTestCaseDetail($param, $user){
		$currentDateTime = date('Y-m-d H:i:s');
		$sqlStr = "INSERT INTO M_TESTCASE_DETAIL (testCaseId, refInputId, refInputName, testData, effectiveStartDate, effectiveEndDate, activeFlag, createDate, createUser, updateDate, updateUser) VALUES ('{$param->testCaseId}', {$param->refInputId}, '{$param->refInputName}', '{$param->testData}', '{$param->effectiveStartDate}', NULL, '{$param->activeStatus}', '{$currentDateTime}', '$user', '{$currentDateTime}', '$user')";
		$result = $this->db->query($sqlStr);
		return $result;
	}

	function insertTestCaseVersion($param, $user){
		$currentDateTime = date('Y-m-d H:i:s');
		$previousVersionId = !empty($param->previousVersionId)? $param->previousVersionId : "NULL";
		$sqlStr = "INSERT INTO M_TESTCASE_VERSION (testCaseId, testCaseVersionNumber, effectiveStartDate, effectiveEndDate, previousVersionId, activeFlag, createDate, createUser, updateDate, updateUser) VALUES ('{$param->testCaseId}', '{$param->initialVersionNo}', '{$param->effectiveStartDate}', NULL, $previousVersionId, '{$param->activeStatus}', '{$currentDateTime}', '$user', '{$currentDateTime}', '$user')";
		$result = $this->db->query($sqlStr);
		return $result;
	}

	function updateTestCaseVersion($param){
		$effectiveEndDate = empty($param->effectiveEndDate)? "NULL": "'".$param->effectiveEndDate."'";

		$sqlStr = "UPDATE M_TESTCASE_VERSION 
			SET effectiveEndDate = $effectiveEndDate, 
				activeFlag = '$param->activeFlag', 
				updateDate = '$param->updateDate', 
				updateUser = '$param->updateUser'  
			WHERE testCaseId = $param->testCaseId 
			AND testCaseVersionId = $param->testCaseVersionId 
			AND updateDate = '$param->updateDateCondition'";
		$result = $this->db->query($sqlStr);
		return $this->db->affected_rows();	
	}

	function updateTestCaseDetail($param){
		$effectiveEndDate = empty($param->effectiveEndDate)? "NULL": "'".$param->effectiveEndDate."'";
		$sqlStr = "UPDATE M_TESTCASE_DETAIL
			SET effectiveEndDate = $effectiveEndDate, 
				activeFlag = '$param->activeFlag', 
			 	updateDate = '$param->updateDate', 
			 	updateUser = '$param->updateUser' 
			WHERE testCaseId = $param->testCaseId 
			AND refInputId 	= $param->inputId 
			AND activeFlag 	= '$param->activeFlagCondition'";
		$result = $this->db->query($sqlStr);
		return $this->db->affected_rows();
	}

	function uploadTestCaseInfo($param, $user){
		$this->db->trans_start(); //Starting Transaction

		$testCaseId = '';
		$effectiveStartDate = date('Y-m-d H:i:s');

		//Check Existing Test Case Header
		//var_dump($param[0]);
		$result = $this->searchExistTestCaseHeader($param[0]->projectId, $param[0]->testCaseNo);
		if(null != $result && 0 < count($result)){
			$testCaseId = $result->testCaseId;
		}else{
			//Insert new Test Case Header
			$testCaseId = $this->insertTestCaseHeader($param[0], $user);
			
			//Insert new Test Case Version
			$param[0]->testCaseId = $testCaseId;
			$param[0]->effectiveStartDate = $effectiveStartDate;
			$this->insertTestCaseVersion($param[0], $user);
		}

		//Insert new Test Case Details
		if(null != $testCaseId && !empty($testCaseId)){
			foreach ($param as $value){
				$value->testCaseId = $testCaseId;
				$value->effectiveStartDate = $effectiveStartDate;

				$resultInsertDetail = $this->insertTestCaseDetail($value, $user);
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
}
?>
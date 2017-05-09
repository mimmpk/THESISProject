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

	function searchExistTestCaseDetail($projectId, $testCaseNo, $refInputId){
		$sqlStr = "SELECT *
			FROM M_TESTCASE_HEADER th
			INNER JOIN M_TESTCASE_DETAIL td
			ON th.testCaseId = td.testCaseId
			WHERE th.projectId = {$projectId}
			AND th.testCaseNo = '{$testCaseNo}'
			AND td.refInputId = {$refInputId}
			AND td.activeFlag = '1'";
		$result = $this->db->query($sqlStr);
		return $result->result_array();
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
		$sqlStr = "INSERT INTO M_TESTCASE_DETAIL (testCaseId, refInputId, refInputName, testData, effectiveStartDate, effectiveEndDate, activeFlag, createDate, createUser, updateDate, updateUser)";
	}

	function insertTestCaseVersion($param, $user){
		$currentDateTime = date('Y-m-d H:i:s');
		$sqlStr = "INSERT INTO M_TESTCASE_VERSION (testCaseId, testCaseVersionNumber, effectiveStartDate, effectiveEndDate, activeFlag, createDate, createUser, updateDate, updateUser) VALUES ('{$param->testCaseId}', '{$param->testCaseVersionNumber}', '{$param->effectiveStartDate}', NULL, '{$param->activeStatus}', '{$currentDateTime}', '$user', '{$currentDateTime}', '$user')";
		$result = $this->db->query($sqlStr);
		return $result;
	}

	/*function searchMappingTestCaseData($testCaseNo, $projectId){
		$sqlStr = "SELECT 
				r.functionId,
				r.testCaseId
			FROM M_RTM r
			INNER JOIN M_TESTCASE_HEADER t
			ON r.testCaseId = t.testCaseId
			WHERE t.testCaseNo = '$testCaseNo'
			AND r.projectId = $projectId"
		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}*/
}
?>
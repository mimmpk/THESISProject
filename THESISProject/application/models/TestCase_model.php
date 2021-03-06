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
			WHERE $where_clause
			ORDER BY th.testCaseNo, tv.testCaseVersionNumber";
		$result = $this->db->query($sqlStr);
		return $result->result_array();
	}

	function searchCountAllTestCases(){
		$result = $this->db->query("
			SELECT count(*) as counts FROM M_TESTCASE_HEADER");
		return $result->row();
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

	function searchTestCaseVersionInformationByCriteria($param){
		if(isset($param->testCaseId) && !empty($param->testCaseId)){
			$where[] = "h.testCaseId = $param->testCaseId";
		}
		if(isset($param->testCaseVersionNumber) && !empty($param->testCaseVersionNumber)){
			$where[] = "v.testCaseVersionNumber = $param->testCaseVersionNumber";
		}
		if(isset($param->testCaseVersionId) && !empty($param->testCaseVersionId)){
			$where[] = "v.testCaseVersionId = $param->testCaseVersionId";
		}
		$where_condition = implode(" AND ", $where);
		
		$sqlStr = "SELECT 
			h.testCaseId, h.testCaseNo, v.testCaseVersionId, v.testCaseVersionNumber, 
			v.previousVersionId, v.effectiveStartDate, v.effectiveEndDate, v.updateDate, v.activeFlag
			FROM M_TESTCASE_HEADER h
			INNER JOIN M_TESTCASE_VERSION v
			ON h.testCaseId = v.testCaseId
			WHERE $where_condition";
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

		if(isset($param->testCaseId) && !empty($param->testCaseId)){
			$where[] = "testCaseId = $param->testCaseId";
		}

		if(isset($param->inputId) && !empty($param->inputId)){
			$where[] = "refInputId 	= $param->inputId";
		}

		if(isset($param->activeFlagCondition) && !empty($param->activeFlagCondition)){
			$where[] = "activeFlag 	= '$param->activeFlagCondition'";
		}

		if(isset($param->endDateCondition) && !empty($param->endDateCondition)){
			$where[] = "effectiveEndDate = '$param->endDateCondition'";
		}
		$where_condition = implode(" AND ", $where);

		$sqlStr = "UPDATE M_TESTCASE_DETAIL
			SET effectiveEndDate = $effectiveEndDate, 
				activeFlag = '$param->activeFlag', 
			 	updateDate = '$param->updateDate', 
			 	updateUser = '$param->updateUser' 
			WHERE $where_condition";
		$result = $this->db->query($sqlStr);
		return $this->db->affected_rows();
	}

	function deleteTestCaseVersion($param){
		if(isset($param->testCaseId) && !empty($param->testCaseId)){
			$where[] = "testCaseId = $param->testCaseId";
		}
		if(isset($param->testCaseVersionId) && !empty($param->testCaseVersionId)){
			$where[] = "testCaseVersionId = $param->testCaseVersionId";
		}
		if(isset($param->testCaseVersionNumber) && !empty($param->testCaseVersionNumber)){
			$where[] = "testCaseVersionNumber = $param->testCaseVersionNumber";
		}
		$where_condition = implode(" AND ", $where);

		$sqlStr = "DELETE FROM M_TESTCASE_VERSION WHERE $where_condition";

		$result = $this->db->query($sqlStr);
		return $this->db->affected_rows();	
	}

	function deleteTestCaseHeader($testCaseId){
		$sqlStr = "DELETE FROM M_TESTCASE_HEADER
			WHERE testCaseId = $testCaseId";
		$result = $this->db->query($sqlStr);
		return $this->db->affected_rows();
	}

	function deleteTestCaseDetail($param){
		if(isset($param->testCaseId)  && !empty($param->testCaseId)){
			$where[] = "testCaseId = $param->testCaseId";
		}
		if(isset($param->inputId) && !empty($param->inputId)){
			$where[] = "inputId = $param->inputId";
		}
		if(isset($param->effectiveStartDate) && !empty($param->effectiveStartDate)){
			$where[] = "effectiveStartDate = '$param->effectiveStartDate'";
		}
		$where_condition = implode(" AND ", $where);
		
		$sqlStr = "DELETE FROM M_TESTCASE_DETAIL WHERE $where_condition";
		
		$result = $this->db->query($sqlStr);
		return $this->db->affected_rows();
	}

	function uploadTestCaseInfo($param, $user){
		$this->db->trans_begin(); //Starting Transaction

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
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
* Common Model
*/
class Common_model extends CI_Model{
	
	function __construct(){
		parent::__construct();
	}

	function getChangeRequestNo($runningType){
		$currentDate = date('Y-m-01');
		$formatStr = "";
		
		$runningNo = 1;
		$prefix = "CR-yymm";
		$affix = "";
		$length = "3";

		$sqlStr = "SELECT 
				p.prefix,
				p.affix,
				p.length,
				n.runningNo
			FROM M_RUNNING_PREFIX p
			INNER JOIN M_RUNNING_NO n
			ON p.runningType = n.runningType
			WHERE p.runningType = '$runningType'
			AND n.period = '$currentDate'";
		$result = $this->db->query($sqlStr);
		if(null != $result && 0 < count($result->result_array())){
			//update
			$runningInfo = $result->row();
			
			$runningNo 	= $runningInfo->runningNo;
			$prefix 	= $runningInfo->prefix;
			$affix 		= $runningInfo->affix;
			$length 	= $runningInfo->length;

			$runningNo = (int)$runningNo + 1;
			$sqlStr = "UPDATE M_RUNNING_NO 
				SET runningNo = $runningNo
				WHERE period = '$currentDate'
				AND runningType = '$runningType'";
			$result = $this->db->query($sqlStr);
		}else{
			//insert
			$sqlStr = "INSERT INTO M_RUNNING_NO(runningType, period, runningNo) VALUES ('$runningType', '$currentDate', 1)";
			$result = $this->db->query($sqlStr);
		}
		$formatStr = $this->setFormat($prefix, $affix, $length, $runningNo);
		return $formatStr;

	}

	function setFormat($prefix, $affix, $length, $runningNo){
		$padRunningNo = str_pad($runningNo, (int)$length, '0', STR_PAD_LEFT);
		
		$year = date('y');
		$month = date('m');
		$day = date('d');

		$prefix = str_replace("yy", $year, $prefix);
		$prefix = str_replace("mm", $month, $prefix);
		$prefix = str_replace("dd", $day, $prefix);

		return $prefix.$padRunningNo;
	}
}

?>
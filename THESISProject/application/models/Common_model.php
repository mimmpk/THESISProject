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
		if(0 < count($result->num_rows())){
			//update
			$result = $result->row();
			
			$runningNo 	= $result->runningNo;
			$prefix 	= $result->prefix;
			$affix 		= $result->affix;
			$length 	= $result->length;

			$runningNo = (int)$runningNo + 1;
			$sqlStr = "UPDATE M_RUNNING_NO 
				SET runningNo = $runningNo
				WHERE period = '$currentDate'
				AND runningType = '$runningType'";
			$result = $this->db->query($sqlStr);
		}else{
			//insert
			$array = array(
		        'runningType' => $runningType,
		        'period' => $currentDate,
		        'runningNo' => 1
			);
			$this->db->set($array);
			$this->db->insert('M_RUNNING_NO');
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
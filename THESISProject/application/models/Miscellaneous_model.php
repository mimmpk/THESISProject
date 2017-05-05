<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Miscellaneous_model extends CI_Model {
	function __construct(){
		parent::__construct();
	}

	function searchMiscellaneous($miscData, $miscValue){
		if(isset($miscData) && !empty($miscData)){
			$where[] = "miscData = '".$miscData."'";
		}

		if(isset($miscValue) && !empty($miscValue)){
			$where[] = "miscValue1 = '".$miscValue."'";
		}
		$where[] = "activeFlag = '1'";
		$where_clause = implode(' AND ', $where);
		$result = $this->db->query("SELECT miscData, miscValue1, miscValue2, miscDescription FROM m_miscellaneous where $where_clause");
		return $result->result_array();
	}
}
?>
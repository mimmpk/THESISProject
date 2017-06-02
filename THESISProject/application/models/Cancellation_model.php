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


}

?>
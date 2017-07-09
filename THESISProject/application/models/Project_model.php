<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Project_model extends CI_Model {
	function __construct(){
		parent::__construct();
	}

	function searchProjectInformation($projectName, $projectAlias, $startDateFrom, $startDateTo, $endDateFrom, $endDateTo, $customer){
		$format = 'd/m/Y';
		
		if(isset($projectName) && $projectName != "" && !empty($projectName)){
			$where[] = "projectName like '%".$this->ms_escape_string($projectName)."%'";
		}
		if(isset($projectAlias) && $projectAlias != "" && !empty($projectAlias)){
			$where[] = "projectNameAlias like '%".$this->ms_escape_string($projectAlias)."%'";
		}
		if(isset($startDateFrom) && $startDateFrom != "" && !empty($startDateFrom)){
			$date = DateTime::createFromFormat($format, $startDateFrom);
			$where[] = "startDate >= '".$date->format('Y-m-d')."'";
		}
		if(isset($startDateTo) && $startDateTo != "" && !empty($startDateTo)){
			$date = DateTime::createFromFormat($format, $startDateTo);
			$where[] = "startDate <= '".$date->format('Y-m-d')."'";
		}
		if(isset($endDateFrom) && $endDateFrom != "" && !empty($endDateFrom)){
			$date = DateTime::createFromFormat($format, $endDateFrom);
			$where[] = "endDate >= '".$date->format('Y-m-d')."'";
		}
		if(isset($endDateTo) && $endDateTo != "" && !empty($endDateTo)){
			$date = DateTime::createFromFormat($format, $endDateTo);
			$where[] = "endDate <= '".$date->format('Y-m-d')."'";
		}
		if(isset($customer) && $customer != "" && !empty($customer)){
			$where[] = "customer like '%".$this->ms_escape_string($customer)."%'";
		}
		
		$where_clause = implode(' AND ', $where);
		$queryStr = "SELECT projectId, projectName, projectNameAlias, startDate, endDate, customer FROM M_PROJECT 
			WHERE $where_clause 
			ORDER BY projectName";
		//echo $queryStr."<br/>";
		$result = $this->db->query($queryStr);
		return $result->result_array();
	}

	function searchProjectDetail($projectId){
		$queryStr 
			= "SELECT 
				p.projectId, p.projectName, p.projectNameAlias, 
				CONVERT(nvarchar, p.startDate, 103) as startDate, 
				CONVERT(nvarchar, p.endDate, 103) as endDate, 
				p.customer as customer, 
				p.databaseName, p.hostname, p.port, p.username, p.password, p.startFlag
			FROM M_PROJECT p
			WHERE p.projectId = $projectId";
		$result = $this->db->query($queryStr);
		return $result->row();
	}

	function searchActiveProjectCombobox(){
		$queryStr = "SELECT projectId, projectName, projectNameAlias 
			FROM M_PROJECT 
			WHERE activeFlag = '1' AND startFlag <> '1'
			ORDER BY projectName";
		$result = $this->db->query($queryStr);
		return $result->result_array();
	}

	function searchStartProjectCombobox(){
		$queryStr = "SELECT projectId, projectName, projectNameAlias
			FROM M_PROJECT
			WHERE activeFlag = '1' AND startFlag = '1'
			ORDER BY projectName";
		$result = $this->db->query($queryStr);
		return $result->result_array();
	}

	function searchCountAllProjects(){
		$result = $this->db->query("
			SELECT count(*) as counts FROM M_PROJECT WHERE activeFlag = '1'");
		return $result->row();
	}

	function searchCountProjectInformation($projectName, $projectAlias, $startDate, $endDate, $customer){
		$result = $this->db->query("SELECT count(*) as counts FROM users where username = '$username' and pwd = '$password'");
		//echo var_dump($result->counts);
		return $result->result_array();
	}

	function searchCountProjectInformationByProjectName($projectName){
		$queryStr = "SELECT count(*) as counts FROM M_PROJECT WHERE projectName = '$projectName'";
		return $this->db->query($queryStr)->result_array();
	}

	function insertProjectInformation($param){
		$this->db->trans_start(); //Starting Transaction
		$this->db->trans_strict(FALSE);

		$result = null;
		$currentDateTime = date('Y-m-d H:i:s');

		$sql = "INSERT INTO M_PROJECT (projectName, projectNameAlias, startDate, endDate, customer, createDate, createUser, updateDate, updateUser, activeFlag, databaseName, hostname, port, username, password) VALUES ('{$param->projectName}', '{$param->projectAlias}', '".$param->startDate->format('Y-m-d')."', '".$param->endDate->format('Y-m-d')."', '$param->customer', '$currentDateTime', '$param->user', '$currentDateTime', '$param->user', '".ACTIVE_CODE."', '$param->databaseName', '$param->hostname', '$param->port', '$param->username', '$param->password')";
		$insertResult = $this->db->query($sql);
		if($insertResult){
			$query = $this->db->query("SELECT IDENT_CURRENT('M_PROJECT') as last_id");
			$result = $query->result();
			
			$param->projectId = $result[0]->last_id;
		}

		$this->db->trans_complete();
    	$trans_status = $this->db->trans_status();
	    if($trans_status == FALSE){
	    	$this->db->trans_rollback();
	    	return null;
	    }else{
	   		$this->db->trans_commit();
	   		return $result[0]->last_id;
	    }
	}

	function updateProjectInformation($param){
		$this->db->trans_start(); //Starting Transaction
		$this->db->trans_strict(FALSE);

		$currentDateTime = date('Y-m-d H:i:s');

		$sql = "UPDATE [M_PROJECT] 
			SET projectNameAlias = '{$param->projectAlias}', 
				startDate = '{$param->startDate->format('Y-m-d')}', 
				endDate = '{$param->endDate->format('Y-m-d')}', 
				customer = '{$param->customer}',
				databaseName = '{$param->databaseName}',
				hostname = '{$param->hostname}',
				port = '{$param->port}',
				username = '{$param->username}',
				password = '{$param->password}',
				updateDate = '{$currentDateTime}', 
				updateUser = '{$param->user}' 
			WHERE projectId = {$param->projectId}";

		$this->db->query($sql);
		$this->db->trans_complete();
    	$trans_status = $this->db->trans_status();
	    if($trans_status == FALSE){
	    	$this->db->trans_rollback();
	    	return FALSE;
	    }else{
	   		$this->db->trans_commit();
	   		return TRUE;
	    }
	}	

	function updateProjectInformation_byStartFlag($param){
		$currentDateTime = date('Y-m-d H:i:s');
		$sql = "UPDATE [M_PROJECT] 
			SET startFlag = '1',
				updateDate = '{$currentDateTime}', 
				updateUser = '{$param->user}' 
			WHERE projectId = {$param->projectId}";

		$this->db->query($sql);
		return $this->db->affected_rows();
	}

	function ms_escape_string($data) {
        if ( !isset($data) or empty($data) ) return '';
        if ( is_numeric($data) ) return $data;

        $non_displayables = array(
            '/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
            '/%1[0-9a-f]/',             // url encoded 16-31
            '/[\x00-\x08]/',            // 00-08
            '/\x0b/',                   // 11
            '/\x0c/',                   // 12
            '/[\x0e-\x1f]/'             // 14-31
        );
        foreach ( $non_displayables as $regex )
            $data = preg_replace( $regex, '', $data );
        $data = str_replace("'", "''", $data );
        return $data;
    }
}
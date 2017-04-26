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
		if(isset($projectNameAlias) && $projectNameAlias != "" && !empty($projectNameAlias)){
			$where[] = "projectNameAlias like '%".$this->ms_escape_string($projectNameAlias)."%'";
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
		$queryStr = "SELECT projectId, projectName, projectNameAlias, startDate, endDate, customer FROM project WHERE $where_clause ORDER BY projectName";
		//echo $queryStr."<br/>";
		$result = $this->db->query($queryStr);
		return $result->result_array();
	}

	function searchProjectDetail($projectId){
		$queryStr 
			= "SELECT 
				projectId, projectName, projectNameAlias, 
				CONVERT(nvarchar, startDate, 103) as startDate, 
				CONVERT(nvarchar, endDate, 103) as endDate, customer 
			FROM project WHERE projectId = $projectId";
		$result = $this->db->query($queryStr);
		return $result->row();
	}

	function searchActiveProjectCombobox(){
		$queryStr = "SELECT projectId, projectName, projectNameAlias FROM project ORDER BY projectName";
		$result = $this->db->query($queryStr);
		return $result->result_array();
	}

	function searchCountProjectInformation($projectName, $projectAlias, $startDate, $endDate, $customer){
		$result = $this->db->query("SELECT count(*) as counts FROM users where username = '$username' and pwd = '$password'");
		//echo var_dump($result->counts);
		return $result->result_array();
	}

	function searchCountProjectInformationByProjectName($projectName){
		$queryStr = "SELECT count(*) as counts FROM project WHERE projectName = '$projectName'";
		return $this->db->query($queryStr)->result_array();
	}

	function insertProjectInformation($projectName, $projectAlias, $startDate, $endDate, $customer, $user){
		$currentDateTime = date('Y-m-d H:i:s');

		$sql = "INSERT INTO Project (projectName, projectNameAlias, startDate, endDate, customer, createDate, createUser, updateDate, updateUser) VALUES ('{$projectName}', '{$projectAlias}', '". $startDate->format('Y-m-d') ."', '". $endDate->format('Y-m-d') ."', '{$customer}', '$currentDateTime', '$user', '$currentDateTime', '$user')";
		//var_dump($this->db->insert_id());
		$result = $this->db->query($sql);
		if($result){
			$query = $this->db->query("SELECT IDENT_CURRENT('project') as last_id");
			$resultId = $query->result();
			return $resultId[0]->last_id;
		}else{
			return null;
		}
	}

	function updateProjectInformation($param){
		$currentDateTime = date('Y-m-d H:i:s');

		$sql = "UPDATE [PROJECT] SET projectNameAlias = '{$param->projectAlias}', startDate = '{$param->startDate->format('Y-m-d')}', endDate = '{$param->endDate->format('Y-m-d')}', customer = '{$param->customer}', updateDate = '$currentDateTime', updateUser = '{$param->user}' WHERE projectId = {$param->projectId}";

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
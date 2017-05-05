<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {
	function __construct(){
		parent::__construct();
	}

	function _checkUser($username, $password){
		$result = $this->db->query("SELECT count(*) as counts FROM m_users where username = '$username' and pwd = '$password'")->first_row();
		//echo var_dump($result->counts);
		return (int)$result->counts > 0 ? TRUE : FALSE;
	}
}
?>
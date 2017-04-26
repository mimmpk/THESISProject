<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	*/
	function __construct(){
		parent::__construct();
		$this->load->model('User_model', 'Users');
		$this->load->library('form_validation', null, 'FValidate');
		$this->load->library('session');
	}

	public function index($error_message = '')
	{
		$data['html'] = 'login_view';
		/*$data['active_page'] = 'login';*/
		$data['error_message'] = $error_message;
		$this->load->view('template/header');
		$this->load->view('template/body_singlePage',$data);
		$this->load->view('template/footer');
	}

	public function doLogin(){
		$error_message = '';
		$username = $this->input->post('username');
		$password = $this->input->post('password');

		//echo $username. "  ". $password;

		$this->FValidate->set_rules('username', '"Username"', 'required');
		$this->FValidate->set_rules('password', '"Password"', 'required');

		if($this->FValidate->run()){
			If($this->Users->_checkUser($username, $password)){
				//echo 'pass';
				$userSession = array(
					'username' 	=> $username,
					'logged'	=> TRUE
				);
				$this->session->set_userdata($userSession); //create session parameter
				redirect('Dashboard');
			}else{
				$error_message = '<br/><font color="red">Username or Password is not correct. Plase try again!!</font>';
			}
		}

		$this->index($error_message);
	}

	public function doLogout(){
		$user_data = $this->session->all_userdata();
		foreach ($user_data as $key => $value) {
			if($key != 'session_id' && $key != 'ip_address' && $key != 'user_agent' && $key != 'last_activity'){
				$this->session->unset_userdata($key);
			}
		}
		$this->session->sess_destroy();
   	 	$this->index();
	}

}

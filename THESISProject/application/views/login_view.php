<!DOCTYPE html>
<html class="full" lang="en">
<head>
	<meta charset="utf-8">
	<title>VCS | Version Control System 0.1</title>
	<link rel="icon" href="<?=base_url()?>/favicon.png" type="image">

 	<!-- Tell the browser to be responsive to screen width -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<!-- jQuery UI -->
	<?php echo css_asset('jQuery-UI/jquery-ui.css'); ?>

	<!-- Bootstrap 3.3.6 -->
	<?php echo css_asset('bootstrap.min.css'); ?>

	<!-- Font Awesome -->
  	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
 	<!-- Ionicons -->
  	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">

  	<!-- AdminLTE -->
	<?php echo css_asset('AdminLTE.min.css'); ?>

	<?php echo css_asset('loginStyle.css'); ?>
</head>
<body class="hold-transition">
	<div class="container">
		<div class="login-box">
			<div class="login-logo">
				<a href="../../index2.html"><b>VCS</b> Version 0.1</a>
			</div>
		  	<!-- /.login-logo -->
		  	<div class="login-box-body">
		    	<p class="login-box-msg">Sign in to start your session</p>

		        <form action="http://localhost/THESISProject/Login/doLogin" method="post">
			      	<div class="form-group has-feedback">
			        	<input type="text" class="form-control" placeholder="Username" name="username" value="<?=set_value('username')?>" autofocus>
			        	<span class="glyphicon glyphicon-user form-control-feedback"></span>
			      	</div>
			      	<div class="form-group has-feedback">
				        <input type="password" class="form-control" placeholder="Password" name="password" value="<?=set_value('password')?>">
				        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
			      	</div>
					<div class="row">
						<div class="col-xs-8"> 
						</div>
						<!-- /.col -->
						<div class="col-xs-4">
						  <button type="submit" class="btn btn-primary btn-block btn-flat">Sign In</button>
						</div>
					<!-- /.col -->
					</div>
					<?php echo form_error('username', '<font color="red">','</font><br>'); ?>
					<?php echo form_error('password', '<font color="red">','</font><br>'); ?>
					<?php echo $error_message; ?>
		    	</form>
		    <!-- <a href="#">I forgot my password</a><br> -->
		   	<!-- <a href="register.html" class="text-center">Register a new membership</a> -->

		  </div>
		  <!-- /.login-box-body -->
		</div>
	</div>
	<!-- /.login-box -->
	<?php $this->view('template/body_javascript'); ?>
</body>
</html>
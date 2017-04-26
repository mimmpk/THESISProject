<!-- <div class="container">
	<form  class="form-signin" action = "http://localhost/THESISProject/Login/login" method = "post">
		<h4 class = "form-signin-heading"></h4>
		<h2 class="form-signin-heading">Please sign in</h2>
		<input type="text" class="input-block-level" placeholder="Username" name="username" autofocus>
		<?php echo form_error('username', '<font color="red">','</font><br>'); ?>
		<input type="password" class="input-block-level" placeholder="Password" name = "password">
		<?php echo form_error('password', '<font color="red">','</font><br>'); ?>
		<button class="btn btn-large btn-primary" type="submit">Sign in</button>

		<?php echo $error_message; ?>
	</form>
</div> -->

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
<!-- /.login-box -->


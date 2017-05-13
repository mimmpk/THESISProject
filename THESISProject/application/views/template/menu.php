<div class="wrapper">
	<header class="main-header">
		<!-- Logo -->
	    <a href="#" class="logo">
			<!-- mini logo for sidebar mini 50x50 pixels -->
			<span class="logo-mini"><b>VCS</b></span>
			<!-- logo for regular state and mobile devices -->
			<span class="logo-lg"><b>VCS</b> Version 0.1</span>
	    </a>
	    <!-- Header Navbar: style can be found in header.less -->
	    <nav class="navbar navbar-static-top">
		    <!-- Sidebar toggle button-->
	      	<a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
		        <span class="sr-only">Toggle navigation</span>
		        <span class="icon-bar"></span>
		        <span class="icon-bar"></span>
		        <span class="icon-bar"></span>
      		</a>

      		<div class="navbar-custom-menu">
      			<ul class="nav navbar-nav">
      				<!-- User Account: style can be found in dropdown.less -->
      				<li class="dropdown user user-menu">
      					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
      						<img src="<?php echo base_url(); ?>/assets/img/avatar02.png" class="user-image">
          					<span class="hidden-xs"><?php echo $this->session->userdata('username') ?></span>
      					</a>
          			</li>
          			<li class="dropdown">
          				<a href="#" class="dropdown-toggle" data-toggle="dropdown">
          					<span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
          				</a>
          				<ul class="dropdown-menu">
          					<li><a href="http://localhost/THESISProject/Login/doLogout">Log Out</a></li>
          				</ul>
          			</li>
      			</ul>
      		</div>

      		<form name="form1" action="#" method="">
      			<input type="hidden" id="activeTitle" value="<?php echo $active_title; ?>">
		        <input type="hidden" id="activePage" value="<?php echo $active_page; ?>">
	    	</form>
	    </nav>
	</header>

	<!-- Left side column. contains the logo and sidebar -->
	<aside class="main-sidebar">
		<!-- sidebar: style can be found in sidebar.less -->
	    <section class="sidebar">
	    	<!-- Sidebar user panel -->
			<div class="user-panel">
				<div class="pull-left image">
				  <img src="<?php echo base_url(); ?>/assets/img/avatar02-1.png" class="img-circle">
				</div>
				<div class="pull-left info">
				  <p>Parichat Kiatphao</p>
				  <i class="fa fa-circle text-success"></i> Online
				</div>
			</div>
			<!-- sidebar menu: : style can be found in sidebar.less -->
      		<ul class="sidebar-menu">
      			<li class="header">MAIN NAVIGATION</li>
		        <li class="treeview">
					<a href="#">
						<i class="fa fa-dashboard"></i> 
						<span>Dashboard</span>
						<span class="pull-right-container">
					  		<i class="fa fa-angle-left pull-right"></i>
						</span>
					</a>
		          	<ul class="treeview-menu" id="dashboard">
			            <li id="dhbs001"><a href="#"><i class="fa fa-circle-o"></i> Dashboard v1</a></li>
			            <li id="dhbs002"><a href="#"><i class="fa fa-circle-o"></i> Dashboard v2</a></li>
		          	</ul>
		        </li>
		        <li class="treeview">
		        	<a href="#">
		        		<i class="fa fa-pencil-square-o"></i>
			        	<span>Master Management</span>
			        	<span class="pull-right-container">
					  		<i class="fa fa-angle-left pull-right"></i>
						</span>
		        	</a>
		        	<ul class="treeview-menu" id="master">
		        		<li id="mats001"><a href="<?php echo base_url(); ?>Project/"><i class="fa fa-circle-o"></i> Project</a></li>
			            <li id="mats002"><a href="<?php echo base_url(); ?>FunctionalRequirement/"><i class="fa fa-circle-o"></i> Functional Requirements</a></li>
			            <li id="mats003"><a href="<?php echo base_url(); ?>TestCaseManagement/"><i class="fa fa-circle-o"></i> Test Cases</a></li>
			            <li id="mats004"><a href="<?php echo base_url(); ?>RTM/"><i class="fa fa-circle-o"></i> RTM</a></li>
			            <li id="mats005"><a href="<?php echo base_url(); ?>DatabaseSchema/"><i class="fa fa-circle-o"></i> Database Schema</a></li>
		          	</ul>
		        </li>
		        <li class="treeview">
		        	<a href="#">
		        		<i class="fa fa-exchange"></i>
			        	<span>Change Management</span>
			        	<span class="pull-right-container">
					  		<i class="fa fa-angle-left pull-right"></i>
						</span>
		        	</a>
		        </li>
		        <li class="treeview">
		        	<a href="#">
		        		<i class="fa fa-history"></i>
			        	<span>Version Management</span>
			        	<span class="pull-right-container">
					  		<i class="fa fa-angle-left pull-right"></i>
						</span>
		        	</a>
		        </li>
      		</ul>
	    </section>
	    <!-- /.sidebar -->
	</aside>

	<!-- Content Wrapper. Contains page content -->
	<div class="content-wrapper">
		<?php $this->view($html); ?>
	</div>

	<!-- Footer -->
	<footer class="main-footer">
		<div class="pull-right hidden-xs">
			<b>Version</b> 0.1
		</div>
		<strong>Copyright &copy; 2017</strong> <b> THESIS Master Degree.</b> All rights reserved.
	</footer>
</div>
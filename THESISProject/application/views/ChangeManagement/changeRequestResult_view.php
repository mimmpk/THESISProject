<section class="content-header">
	<h1>
		<span class="glyphicon glyphicon-tasks"></span>
		Change Request Result
	</h1>
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i>Home</a></li>
		<li><a href="#">Change Management</a></li>
		<li class="active">Change Request Result</li>
	</ol>

	<!-- Main content -->
	<div class="row">
		<div class="col-md-12">
			<?php if(!empty($success_message)) { ?>
			<div class="alert alert-success alert-dismissible" style="margin-top: 3px;margin-bottom: 3px;">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
				<?php echo $success_message; ?>
			</div>
			<?php } ?>
			<?php if(!empty($error_message)) { ?>
			<div class="alert alert-danger alert-dismissible" style="margin-top: 3px;margin-bottom: 3px;">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
				<?php echo $error_message; ?>
			</div>
			<?php } ?>


			<div class="box box-primary">
				<div class="box-header with-border">
                    <h3 class="box-title">Result Information</h3>
                </div>
                <div class="box-body">
                	<div class="row">
                		<div class="col-sm-12">
                			<div class="form-group">
                			</div>
                		</div>
                	</div>
                </div>
			</div>
		</div>
	</div>
</section>
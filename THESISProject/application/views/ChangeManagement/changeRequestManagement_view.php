<section class="content-header">
	<h1>
		<span class="glyphicon glyphicon-list-alt"></span>
		Change Request
	</h1>
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i>Home</a></li>
		<li><a href="#">Change Management</a></li>
		<li class="active">Change Request</li>
	</ol>

	<!-- Main content -->
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">
				  <form role="form" action="<?php echo base_url() ?>ChangeManagement/changeRequest/" method="post">
				  	<input type="hidden" id="selectedProjectId" value="<?php echo isset($selectedProjectId)? $selectedProjectId : '' ?>">
				  	<div class="box-body">
				  		<div class="row">
				  			<div class="form-group">
				  				<div class="col-sm-8">
				  					<label for="inputProjectName">Project's name<span style="color:red;">*</span>:</label>
				  					<select name="inputProjectName" class="form-control select2" style="width: 100%;" value="<?php echo $formData->projectName ?>">
										<option value="">--Please Select--</option>
	            						<?php if(null != $projectCombo) {  ?>
	            						<?php foreach($projectCombo as $value): ?>
	            								<option value="<?php echo $value['projectId']; ?>" <?php echo set_select('inputProjectName', $value['projectId'], ( !empty($formData->projectName) && $formData->projectName == $value['projectId'] ? TRUE : FALSE )); ?>>
	            									<?php echo $value['projectNameAlias']; ?>: <?php echo $value['projectName']; ?>
	        									</option>
	            						<?php endforeach; ?>
	            						<?php } ?>
					                </select>
				  				</div>
				  				<div class="col-sm-4">
				  					<br/>
				  					<button type="submit" class="btn bg-primary" style="width: 100px;">
	                					<i class="fa fa-search"></i>
	                					Search
	                				</button>
				  				</div>
				  			</div>
				  		</div>
				  	</div>
				  </form>
			</div>
		</div>
	</div>
</section>
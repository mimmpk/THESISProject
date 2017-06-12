<section class="content-header">
	<h1>
		<span class="glyphicon glyphicon-list-alt"></span>
		Inquiry Database Schema by Version
	</h1>
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
		<li><a href="#">Version Management</a></li>
		<li class="active">Inquiry Database Schema by Version </li>
	</ol>
	<!-- Main content -->
	<div class="row">
		<div class="col-md-12">
			<?php if(!empty($error_message)) { ?>
			<div class="alert alert-danger alert-dismissible" style="margin-top: 3px;margin-bottom: 3px;">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
				<?php echo $error_message; ?>
			</div>
			<?php } ?>
			<div class="box box-primary">
				<div class="box-header with-border">
					<h3 class="box-title">Search Criteria</h3>
				</div>
				<form class="form-horizontal" action="<?php echo base_url() ?>VersionManagement_Schema/search/" method="post">
					<div class="box-body">
						<div class="form-group">
							<label for="inputProjectName" class="col-sm-2 control-label">
 								Project's name
        						<span style="color:red;">*</span>:
        					</label>
        					<div class="col-sm-10">
        						<select id="projectCombo" name="inputProjectName" class="form-control select2" style="width: 100%;" value="<?php echo $projectId; ?>">
    							<option value="">--Please Select--</option>
    							<?php if(null != $projectCombo) {  ?>
    							<?php foreach($projectCombo as $value): ?>
    								<option value="<?php echo $value['projectId']; ?>" <?php echo set_select('inputProjectName', $value['projectId'], (!empty($projectId) && $projectId == $value['projectId']? TRUE : FALSE )); ?>>
        									<?php echo $value['projectNameAlias']; ?>: <?php echo $value['projectName']; ?>
    								</option>
    							<?php endforeach; ?>
        						<?php } ?>
    						</select>
    						<?php echo form_error('inputProjectName', '<font color="red">','</font><br>'); ?>
        					</div>
						</div>
						<div class="form-group">
							<label for="inputTable" class="col-sm-2 control-label">
 								Table Name
        						<span style="color:red;">*</span>:
        					</label>
        					<div class="col-sm-10">
        						<select id="tableCombo" name="inputTable" class="form-control select2" style="width: 100%;" value="<?php echo $tableName; ?>">
            						<option value="">--Please Select--</option>
            						<?php 
            						if(isset($tableCombo) && 0 < count($tableCombo)){
            							foreach($tableCombo as $value){ ?>
            								<option value="<?php echo $value['tableName']; ?>" <?php echo set_select('inputTable', $value['tableName'], (!empty($tableName) && $tableName == $value['tableName']? TRUE : FALSE )); ?>>
	            									<?php echo $value['tableName']; ?>: <?php echo $value['tableName']; ?>
	        								</option>
            						<?php 
            							} 
            						} ?>
            					</select>
            					<?php echo form_error('inputTable', '<font color="red">','</font><br>'); ?>
        					</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</section>
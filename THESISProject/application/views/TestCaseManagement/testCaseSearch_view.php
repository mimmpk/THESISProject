<section class="content-header">
	<h1>
		<span class="glyphicon glyphicon-list-alt"></span>
		Test Case
	</h1>
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
		<li><a href="#">Master Management</a></li>
		<li class="active">Test Case Search</li>
	</ol>

	<!-- Main content -->
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">
				<div class="box-header with-border">
             		<h3 class="box-title">Search Criteria</h3>
            	</div>
            	<form role="form" action="<?php echo base_url() ?>TestCaseManagement/search/" method="post">
            		<div class="box-body">
            			<div class="row">
	            			<div class="col-sm-12">
	            				<div class="form-group">
	            					<label for="selectProjectName">Project's name<span style="color:red;">*</span>:</label>
	        						<select name="selectProjectName" class="form-control select2" style="width: 100%;" value="<?php echo $formData->selectedProjectId ?>">
	        							<option value="">--Please Select--</option>
	        							<?php if(null != $projectCombo) {  ?>
	        							<?php foreach($projectCombo as $value): ?>
	        								<option value="<?php echo $value['projectId']; ?>" <?php echo set_select('inputProjectName', $value['projectId'], (!empty($formData->selectedProjectId) && $formData->selectedProjectId == $value['projectId']? TRUE : FALSE )); ?>>
	            									<?php echo $value['projectNameAlias']; ?>: <?php echo $value['projectName']; ?>
	        								</option>
	        							<?php endforeach; ?>
	            						<?php } ?>
	        						</select>
            					</div>
        					</div>
            				<div class="form-group">
    							<div class="col-sm-6">
    								<label for="inputStatus">Test Case's Status: </label>
	            					&nbsp;&nbsp;&nbsp;
		            				<label>
					                	<input type="radio" name="inputStatus" class="minimal" value="1" <?php echo set_radio('inputStatus', '1', TRUE); ?>>
					                	Active 
					                </label>
					                <label>
					                	<input type="radio" name="inputStatus" class="minimal" value="0" <?php echo set_radio('inputStatus', '0'); ?>>
					                	Inactive
					                </label>
					                 <label>
					                	<input type="radio" name="inputStatus" class="minimal" value="2" <?php echo set_radio('inputStatus', '2'); ?>>
					                	All
					                </label>
    							</div>
    							<div class="col-sm-6">
    								<div align="right">
	            						<a href="<?php echo base_url(); ?>TestCaseManagement/reset/">
	            							<button type="button" class="btn bg-orange" style="width: 100px;">
	            							<i class="fa fa-refresh"></i> 
	            							Reset
	            							</button>	
	            						</a>
		                				<button type="submit" class="btn bg-primary" style="width: 100px;">
		                					<i class="fa fa-search"></i>
		                					Search
		                				</button>
	            					</div>
    							</div>
    						</div>
            			</div>
            		</div>
            	</form>
			</div>
		</div>
	</div>
</section>
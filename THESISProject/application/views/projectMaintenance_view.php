<section class="content-header">
	<h1>
		Project Maintenance
	</h1>
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
		<li><a href="#">Master Management</a></li>
		<li class="active">Project Maintenance</li>
	</ol>

	 <!-- Main content -->
	 <div class="row">
	 	<div class="col-md-12">
	 		<?php echo "<div style='color:red'>". $error_message ."</div>"?>
	 		<div class="box box-primary">
	 			<div class="box-header with-border">
	              <h3 class="box-title">Project Information</h3>
	            </div>
	            <!-- form start -->
            	<form role="form" action="<?php echo base_url() ?>Project/save/" method="post">
            		<input type="hidden" name="mode" id="mode" value="<?php echo $mode; ?>">
            		<input type="hidden" name="projectId" id="projectId" value="<?php echo $projectInfo->projectId?>">
            		<div class="box-body">
            			<?php if('view' == $mode) { ?>
            				<div class="form-group">
			                	<label for="inputProjectName">Project Name<span style="color:red;">*</span></label>
			                	<input type="text" class="form-control" name="inputProjectName" placeholder="ชื่อโครงการ" value="<?php echo $projectInfo->projectName ?>" readonly>
			                	<?php echo form_error('inputProjectName', '<font color="red">','</font><br>'); ?>
			                </div>
			                <div class="form-group">
			                	<label for="inputProjectNameAlias">Project Name Alias<span style="color:red;">*</span></label>
			                	<input type="text" class="form-control" name="inputProjectNameAlias" placeholder="ชื่อย่อโครงการ" value="<?php echo $projectInfo->projectNameAlias ?>" readonly>
			                	<?php echo form_error('inputProjectNameAlias', '<font color="red">','</font><br>'); ?>
			                </div>
			                <div class="form-group">
			                	<label for="inputStartDate">Project Start Date<span style="color:red;">*</span></label>
			                	<div class="input-group date">
			                		<div class="input-group-addon">
	                    				<i class="fa fa-calendar"></i>
	                  				</div>
	                  				<input type="text" class="form-control" data-date-format="dd/mm/yyyy" name="inputStartDate" placeholder="วันเริ่มต้นโครงการ" value="<?php echo $projectInfo->startDate ?>" readonly>
			                	</div>
			                	<?php echo form_error('inputStartDate', '<font color="red">','</font><br>'); ?>
			                </div>
			                <div class="form-group">
			                	<label for="inputEndDate">Project End Date<span style="color:red;">*</span></label>
			                	<div class="input-group date">
			                		<div class="input-group-addon">
	                    				<i class="fa fa-calendar"></i>
	                  				</div>
	                  				<input type="text" class="form-control" data-date-format="dd/mm/yyyy" name="inputEndDate" placeholder="วันสิ้นสุดโครงการ" value="<?php echo $projectInfo->endDate ?>" readonly>
			                	</div>
			                	<?php echo form_error('inputEndDate', '<font color="red">','</font><br>'); ?>
			                </div>
			                <div class="form-group">
			                	<label for="inputCustomer">Customer<span style="color:red;">*</span></label>
			                	<input type="text" class="form-control" name="inputCustomer" placeholder="ชื่อลูกค้า" value="<?php echo $projectInfo->customer ?>" readonly>
			                	<?php echo form_error('inputCustomer', '<font color="red">','</font><br>'); ?>
			                </div>

			                <div class="form-group">
			                	<div align="right">
			                		<a href="<?php echo base_url() ?>Project/back/" class="btn btn-app btn-default">
			                			<i class="fa fa-home"></i> Back
			                		</a>
			                		<a href="<?php echo base_url() ?>Project/editDetail/<?php echo $projectInfo->projectId ?>" class="btn btn-app btn-default">
			                			<i class="fa fa-edit"></i> Edit
			                		</a>
			                		<button type="button" id="btnCancel" class="btn btn-app btn-default disabled" disabled>
			                			<i class="fa fa-times"></i> Cancel
			                		</button>
			                		<button type="submit" id="btnSave" class="btn btn-app btn-default disabled" disabled>
			                			<i class="fa fa-save"></i> Save
			                		</button>
			                	</div>
			                </div>
            			<?php }else if('edit' == $mode){ ?> 
            				<div class="form-group">
			                	<label for="inputProjectName">Project Name<span style="color:red;">*</span></label>
			                	<input type="text" class="form-control" name="inputProjectName" placeholder="ชื่อโครงการ" value="<?php echo $projectInfo->projectName ?>" readonly>
			                	<?php echo form_error('inputProjectName', '<font color="red">','</font><br>'); ?>
			                </div>
			                <div class="form-group">
			                	<label for="inputProjectNameAlias">Project Name Alias<span style="color:red;">*</span></label>
			                	<input type="text" class="form-control" name="inputProjectNameAlias" placeholder="ชื่อย่อโครงการ" value="<?php echo $projectInfo->projectNameAlias ?>">
			                	<?php echo form_error('inputProjectNameAlias', '<font color="red">','</font><br>'); ?>
			                </div>
			                <div class="form-group">
			                	<label for="inputStartDate">Project Start Date<span style="color:red;">*</span></label>
			                	<div class="input-group date">
			                		<div class="input-group-addon">
	                    				<i class="fa fa-calendar"></i>
	                  				</div>
	                  				<input type="text" class="form-control" data-date-format="dd/mm/yyyy" name="inputStartDate" id="datepicker1" placeholder="วันเริ่มต้นโครงการ" value="<?php echo $projectInfo->startDate ?>">
			                	</div>
			                	<?php echo form_error('inputStartDate', '<font color="red">','</font><br>'); ?>
			                </div>
			                <div class="form-group">
			                	<label for="inputEndDate">Project End Date<span style="color:red;">*</span></label>
			                	<div class="input-group date">
			                		<div class="input-group-addon">
	                    				<i class="fa fa-calendar"></i>
	                  				</div>
	                  				<input type="text" class="form-control" data-date-format="dd/mm/yyyy" name="inputEndDate" id="datepicker2" placeholder="วันสิ้นสุดโครงการ" value="<?php echo $projectInfo->endDate ?>" >
			                	</div>
			                	<?php echo form_error('inputEndDate', '<font color="red">','</font><br>'); ?>
			                </div>
			                <div class="form-group">
			                	<label for="inputCustomer">Customer<span style="color:red;">*</span></label>
			                	<input type="text" class="form-control" name="inputCustomer" placeholder="ชื่อลูกค้า" value="<?php echo $projectInfo->customer ?>">
			                	<?php echo form_error('inputCustomer', '<font color="red">','</font><br>'); ?>
			                </div>

			                <div class="form-group">
			                	<div align="right">
			                		<button type="button" id="btnBack" class="btn btn-app btn-default disabled" disabled>
			                			<i class="fa fa-home"></i> Back
			                		</button>
			                		<button type="button" id="btnEdit" class="btn btn-app btn-default disabled" disabled>
			                			<i class="fa fa-edit"></i> Edit
			                		</button>
			                		<button type="button" id="btnCancel" class="btn btn-app btn-default" onclick="mst001CancelSave()">
			                			<i class="fa fa-times"></i> Cancel
			                		</button>
			                		<button type="submit" id="btnSave" class="btn btn-app btn-default">
			                			<i class="fa fa-save"></i> Save
			                		</button>
			                	</div>
			                </div>
            			<?php }else{ ?> 
            				<div class="form-group">
			                	<label for="inputProjectName">Project Name<span style="color:red;">*</span></label>
			                	<input type="text" class="form-control" name="inputProjectName" placeholder="ชื่อโครงการ" value="<?php echo $projectInfo->projectName ?>">
			                	<?php echo form_error('inputProjectName', '<font color="red">','</font><br>'); ?>
			                </div>
			                <div class="form-group">
			                	<label for="inputProjectNameAlias">Project Name Alias<span style="color:red;">*</span></label>
			                	<input type="text" class="form-control" name="inputProjectNameAlias" placeholder="ชื่อย่อโครงการ" value="<?php echo $projectInfo->projectNameAlias ?>">
			                	<?php echo form_error('inputProjectNameAlias', '<font color="red">','</font><br>'); ?>
			                </div>
			                <div class="form-group">
			                	<label for="inputStartDate">Project Start Date<span style="color:red;">*</span></label>
			                	<div class="input-group date">
			                		<div class="input-group-addon">
	                    				<i class="fa fa-calendar"></i>
	                  				</div>
	                  				<input type="text" class="form-control" data-date-format="dd/mm/yyyy" name="inputStartDate" id="datepicker1" placeholder="วันเริ่มต้นโครงการ" value="<?php echo $projectInfo->startDate ?>">
			                	</div>
			                	<?php echo form_error('inputStartDate', '<font color="red">','</font><br>'); ?>
			                </div>
			                <div class="form-group">
			                	<label for="inputEndDate">Project End Date<span style="color:red;">*</span></label>
			                	<div class="input-group date">
			                		<div class="input-group-addon">
	                    				<i class="fa fa-calendar"></i>
	                  				</div>
	                  				<input type="text" class="form-control" data-date-format="dd/mm/yyyy" name="inputEndDate" id="datepicker2" placeholder="วันสิ้นสุดโครงการ" value="<?php echo $projectInfo->endDate ?>" >
			                	</div>
			                	<?php echo form_error('inputEndDate', '<font color="red">','</font><br>'); ?>
			                </div>
			                <div class="form-group">
			                	<label for="inputCustomer">Customer<span style="color:red;">*</span></label>
			                	<input type="text" class="form-control" name="inputCustomer" placeholder="ชื่อลูกค้า" value="<?php echo $projectInfo->customer ?>">
			                	<?php echo form_error('inputCustomer', '<font color="red">','</font><br>'); ?>
			                </div>

			                <div class="form-group">
			                	<div align="right">
			                		<button type="button" id="btnBack" class="btn btn-app btn-default disabled" disabled>
			                			<i class="fa fa-home"></i> Back
			                		</button>
			                		<button type="button" id="btnEdit" class="btn btn-app btn-default disabled" disabled>
			                			<i class="fa fa-edit"></i> Edit
			                		</button>
			                		<button type="button" id="btnCancel" class="btn btn-app btn-default" onclick="mst001CancelSave()">
			                			<i class="fa fa-times"></i> Cancel
			                		</button>
			                		<button type="submit" id="btnSave" class="btn btn-app btn-default">
			                			<i class="fa fa-save"></i> Save
			                		</button>
			                	</div>
			                </div>
            			<?php } ?>
            		</div>
            	</form>
	 		</div>
	 	</div>
	 </div>
</section>
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
            			<div class="row">
            				<div class="col-md-6">
            					<div class="form-group">
				                	<label for="inputProjectName">Project Name<span style="color:red;">*</span></label>
				                	<input type="text" class="form-control" name="inputProjectName" placeholder="ชื่อโครงการ" value="<?php echo $projectInfo->projectName ?>" <?php echo ($mode == 'edit')? 'readonly ' : '' ?>>
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
            				</div>
            				<div class="col-md-6">
            					<div class="form-group">
	        						<label for="inputDatabaseName">Database Name<span style="color:red;">*</span></label>
				                	<input type="text" class="form-control" name="inputDatabaseName" placeholder="ชื่อฐานข้อมูล" value="<?php echo $projectInfo->databaseName ?>">
				                	<?php echo form_error('inputDatabaseName', '<font color="red">','</font><br>'); ?>
	        					</div>
	        					<div class="form-group">
	        						<label for="inputHostname">Host Name<span style="color:red;">*</span></label>
	        						<input type="text" class="form-control" name="inputHostname" placeholder="ชื่อเครื่องโฮส" value="<?php echo $projectInfo->hostname ?>">
	        						<?php echo form_error('inputHostname', '<font color="red">','</font><br>'); ?>
	        					</div>
	        					<div class="form-group">
	        						<label for="inputPort">Port Number<span style="color:red;">*</span></label>
	        						<input type="text" class="form-control" name="inputPort" placeholder="หมายเลขพอร์ต" value="<?php echo $projectInfo->port ?>">
	        						<?php echo form_error('inputPort', '<font color="red">','</font><br>'); ?>
	        					</div>
	        					<div class="form-group">
	        						<label for="inputUsername">User Name<span style="color:red;">*</span></label>
	        						<input type="text" class="form-control" name="inputUsername" placeholder="ชื่อผู้ใช้" value="<?php echo $projectInfo->username ?>">
	        						<?php echo form_error('inputUsername', '<font color="red">','</font><br>'); ?>
	        					</div>
	        					<div class="form-group">
	        						<label for="inputPassword">Password<span style="color:red;">*</span></label>
	        						<input type="text" class="form-control" name="inputPassword" placeholder="รหัสผู้ใช้" value="<?php echo $projectInfo->password ?>">
	        						<?php echo form_error('inputPassword', '<font color="red">','</font><br>'); ?>
	        					</div>
            				</div>
            			</div>
            			<div class="row">
            				<div class="col-md-12">
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
            				</div>
            			</div>
            		</div>
            	</form>
	 		</div>
	 	</div>
	 </div>
	 <script type="text/javascript">
	 	function initForm(){
	 		var mode = $('#mode').val();
	 		if('edit' == mode){
	 			alert('edit');
	 		}
	 	}
	 </script>
</section>
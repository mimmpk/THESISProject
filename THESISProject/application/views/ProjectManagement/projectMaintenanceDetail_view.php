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
	              <h3 class="box-title">Project Information</h3>
	            </div>
	            <!-- form start -->
            	<form role="form" action="<?php echo base_url() ?>Project/save/" method="post">
            		<input type="hidden" name="mode" id="mode" value="<?php echo $mode; ?>">
            		<input type="hidden" name="startFlag" id="startFlag" value="<?php echo $projectInfo->startFlag?>">
            		<input type="hidden" name="projectId" id="projectId" value="<?php echo $projectInfo->projectId?>">
            		<div class="box-body">
            			<div class="row">
	            			<div class="col-md-6">
	        					<div class="form-group">
				                	<label for="inputProjectName">Project Name<span style="color:red;">*</span></label>
				                	<input type="text" class="form-control" name="inputProjectName" placeholder="ชื่อโครงการ" value="<?php echo $projectInfo->projectName ?>" readonly>
				                </div>
				                <div class="form-group">
				                	<label for="inputProjectNameAlias">Project Name Alias<span style="color:red;">*</span></label>
				                	<input type="text" class="form-control" name="inputProjectNameAlias" placeholder="ชื่อย่อโครงการ" value="<?php echo $projectInfo->projectNameAlias ?>" readonly>
				                </div>
				                <div class="form-group">
				                	<label for="inputStartDate">Project Start Date<span style="color:red;">*</span></label>
				                	<div class="input-group date">
				                		<div class="input-group-addon">
		                    				<i class="fa fa-calendar"></i>
		                  				</div>
		                  				<input type="text" class="form-control" data-date-format="dd/mm/yyyy" name="inputStartDate" placeholder="วันเริ่มต้นโครงการ" value="<?php echo $projectInfo->startDate ?>" readonly>
				                	</div>
				                </div>
				                <div class="form-group">
				                	<label for="inputEndDate">Project End Date<span style="color:red;">*</span></label>
				                	<div class="input-group date">
				                		<div class="input-group-addon">
		                    				<i class="fa fa-calendar"></i>
		                  				</div>
		                  				<input type="text" class="form-control" data-date-format="dd/mm/yyyy" name="inputEndDate" placeholder="วันสิ้นสุดโครงการ" value="<?php echo $projectInfo->endDate ?>" readonly>
				                	</div>
				                </div>
				                <div class="form-group">
				                	<label for="inputCustomer">Customer<span style="color:red;">*</span></label>
				                	<input type="text" class="form-control" name="inputCustomer" placeholder="ชื่อลูกค้า" value="<?php echo $projectInfo->customer ?>" readonly>
				                	
				                </div>
	        				</div>
	        				<div class="col-md-6">
	        					<div class="form-group">
	        						<label for="inputDatabaseName">Database Name<span style="color:red;">*</span></label>
				                	<input type="text" class="form-control" name="inputDatabaseName" placeholder="ชื่อฐานข้อมูล" value="<?php echo $projectInfo->databaseName ?>" readonly>
	        					</div>
	        					<div class="form-group">
	        						<label for="inputHostname">Host Name<span style="color:red;">*</span></label>
	        						<input type="text" class="form-control" name="inputHostname" placeholder="ชื่อเครื่องโฮส" value="<?php echo $projectInfo->hostname ?>" readonly>
	        					</div>
	        					<div class="form-group">
	        						<label for="inputPort">Port Number<span style="color:red;">*</span></label>
	        						<input type="text" class="form-control" name="inputPort" placeholder="หมายเลขพอร์ต" value="<?php echo $projectInfo->port ?>" readonly>
	        					</div>
	        					<div class="form-group">
	        						<label for="inputUsername">User Name<span style="color:red;">*</span></label>
	        						<input type="text" class="form-control" name="inputUsername" placeholder="ชื่อผู้ใช้" value="<?php echo $projectInfo->username ?>" readonly>
	        					</div>
	        					<div class="form-group">
	        						<label for="inputPassword">Password<span style="color:red;">*</span></label>
	        						<input type="text" class="form-control" name="inputPassword" placeholder="รหัสผู้ใช้" value="<?php echo $projectInfo->password ?>" readonly>
	        					</div>
	        				</div>
            			</div>
            			<div class="row">
            				<div class="col-md-4">
            					<div class="form-group">
            						<div align="left" style="margin-left: -10px;">
            						<?php $var = ('1' == $projectInfo->startFlag)? 'disabled' : ''; ?>
            							<a onclick="startProject();" class="btn btn-app btn-default <?php echo $var;?>">
			                				<i class="fa fa-play"></i> Start Project
			                			</a>
            						</div>
		                		</div>
            				</div>
            				<div class="col-md-8">
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
            				</div>
            			</div>
            		</div>
            	</form>
	 		</div>
	 	</div>
	 </div>
	 <script type="text/javascript">
	 	function startProject(){
	 		var msg = "Are you sure to start project?";
	 		if(confirm(msg)){
	 			var projectId = $('#projectId').val();
	 			window.location  = baseUrl + "Project/startProject/" + projectId;
	 		}
	 	}
	 </script>
</section>
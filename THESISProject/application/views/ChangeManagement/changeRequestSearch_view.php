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
				  <form role="form" action="<?php echo base_url() ?>ChangeManagement/search/" method="post">
				  	<input type="hidden" id="selectedProjectId" value="<?php echo isset($selectedProjectId)? $selectedProjectId : '' ?>">
				  	<div class="box-body">
				  		<div class="row">
				  			<div class="form-group">
				  				<div class="col-sm-8">
				  					<label for="inputProject">Project's name<span style="color:red;">*</span>:</label>
				  					<select name="inputProject" class="form-control select2" style="width: 100%;" value="<?php echo $formData->projectId ?>">
										<option value="">--Please Select--</option>
	            						<?php if(null != $projectCombo) {  ?>
	            						<?php foreach($projectCombo as $value): ?>
	            								<option value="<?php echo $value['projectId']; ?>" <?php echo set_select('inputProject', $value['projectId'], ( !empty($formData->projectId) && $formData->projectId == $value['projectId'] ? TRUE : FALSE )); ?>>
	            									<?php echo $value['projectNameAlias']; ?>: <?php echo $value['projectName']; ?>
	        									</option>
	            						<?php endforeach; ?>
	            						<?php } ?>
					                </select>
				                 	<?php echo form_error('inputProject', '<font color="red">','</font><br>'); ?>
				  				</div>
				  				<div class="col-sm-4">
				  					<br/>
				  					<button type="submit" class="btn bg-primary" style="width: 100px;margin-top: 5px;">
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

	<!-- Start: Search Result Section -->
	<?php if(isset($functionList) and 0 < count($functionList)){ ?>
	<div class="row">
		<div class="col-md-12">
			<div class="box box-success" style="margin-top: -10px;">
				<div class="box-header">
					<h3 class="box-title">Functional Requirements List</h3>
				</div>

				<div class="box-body table-responsive no-padding" style="margin-top: -10px;">
					<table id="resultTbl" class="table table-striped">
						<tbody>
							<tr>
								<th>No.</th>
								<th>Requirement ID</th>
								<th>Requirement Description</th>
								<th>Version Number</th>
								<th>Effective Start Date</th>
								<th>Action</th>
							</tr>
							<?php 
			                $define = 1;
			                foreach($functionList as $value): 
			                	$classRow = (0 == $define%2)? 'even' : 'odd'; ?>
			                <tr class="<?php echo $classRow; ?>">
			                	<td style="text-align: right;width: 5%;">
			                		<?php echo $define++; ?>
			                	</td>
			                	<td style="text-align: left;width: 15%;">
			                		<?php echo $value['functionNo'] ?>
			                	</td>
			                	<td style="text-align: left;width: 40%;">
			                		<?php echo $value['fnDesc'] ?>
			                	</td>
			                	<td style="text-align: right;width: 15%;">
			                		<?php echo $value['functionVersion'] ?>
			                	</td>
			                	<td style="text-align: center;width: 15%;">
			                		<?php 
			                			echo $value['effectiveStartDate'];
			                		?>
			                	</td>
			                	<td style="text-align: center;width: 10%;">
			                		<button type="button" class="btn btn-block bg-orange btn-xs" onclick="viewFunctionDetail(<?php echo $value['functionId']; ?>)">
			                			<i class="fa fa-edit"></i>
			                			 Change
			                		</button>
			                	</td>
			                </tr>
			            	<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
	<script type="text/javascript">
		function viewFunctionDetail(functionId){
			var projectId = $('#selectedProjectId').val();
			window.location  = baseUrl + "ChangeManagement/viewFunctionDetail/" + projectId + "/" + functionId;
		}
	</script>
</section>
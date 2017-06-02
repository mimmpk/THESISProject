<section class="content-header">
	<h1>
		<span class="glyphicon glyphicon-refresh"></span>
		Cancellation of the Latest Change
	</h1>
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i>Home</a></li>
		<li><a href="#">Change Management</a></li>
		<li class="active">Cancellation</li>
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
				<form role="form" action="<?php echo base_url() ?>Cancellation/search/" method="post">
					<input type="hidden" id="selectedProjectId" value="<?php echo isset($selectedProjectId)? $selectedProjectId : '' ?>">
					<div class="box-body">
						<div class="row">
							<div class="form-group">
								<div class="col-sm-8">
									<label for="inputProject">Project's name<span style="color:red;">*</span>:</label>
									<select name="inputProject" class="form-control select2" style="width: 100%;" value="<?php echo $criteria->projectId ?>">
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
	<?php if(isset($changeList) and 0 < count($changeList)){ ?>
	<div class="row">
		<div class="col-md-12">
			<div class="box box-success" style="margin-top: -10px;">
				<div class="box-header">
					<h3 class="box-title">Changes List</h3>
				</div>

				<div class="box-body table-responsive no-padding" style="margin-top: -10px;">
					<table id="resultTbl" class="table table-striped">
						<tbody>
							<tr>
								<th class="col-md-1">#</th>
								<th class="col-md-2">Change Request No.</th>
								<th class="col-md-2">Change Date</th>
								<th class="col-md-2">Change User</th>
								<th class="col-md-3">
									Changed Functional Requirement ID
								</th>
								<th class="col-md-2"></th>
							</tr>
							<?php 
							$i = 1;
							foreach($changeList as $value){ ?>
							<tr>
								<td><?php echo $i++ ?></td>
								<td><?php echo $value['changeRequestNo'] ?></td>
								<td><?php echo $value['changeDate'] ?></td>
								<td><?php echo $value['changeUser'] ?></td>
								<td><?php echo $value['changeFunctionNo'] ?></td>
								<td>
									<button class="btn btn-block btn-default btn-xs" onclick="viewChangeDetail('<?php echo $value['changeRequestNo'] ?>')">
										<i class="fa fa-fw fa-file-text-o"></i>	
										See Detail
									</button>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
	<script type="text/javascript">
		function viewChangeDetail(changeRequestNo){
			var projectId = $('#selectedProjectId').val();
			window.location  = baseUrl + "Cancellation/viewDetail/" + projectId +"/" + changeRequestNo;
		}
	</script>
</section>
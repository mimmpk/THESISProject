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
			
			<form role="form" action="<?php echo base_url() ?>/ChangeManagement/" method="post" enctype="multipart/form-data">
					<?php echo form_hidden($hfield); ?>
				<div class="box box-primary">
					<div class="box-header with-border">
	                    <h3 class="box-title">Project Information</h3>
	                </div>
                	<div class="box-body">
                		<div class="row">
                			<div class="col-sm-12">
                				<div class="form-group">
                					<table class="table-bordered" cellpadding="1px" cellspacing="1px" style="width:100%">
                						<tr>
                							<td height="10" style="background: #F2F3F4;width: 30%;text-align: left;vertical-align: center;">
            									<label for="projectAlias" style="margin-right: 3px;margin-bottom: 0px;">Project Name Alias:</label>
            								</td>
            								<td height="10" style="width: 20%;text-align: left;vertical-align: center;">
            									<label for="projectAlias" style="margin-left: 5px;margin-bottom: 0px;"><?php echo $projectInfo->projectNameAlias; ?></label>
            								</td>
            								<td height="10" style="background: #F2F3F4;width: 20%;text-align: left;vertical-align: center;">
            									<label for="projectName" style="margin-right: 3px;margin-bottom: 0px;">Project Name:</label>
            								</td>
            								<td height="10" style="width: 30%;text-align: left;vertical-align: center;"">
            									<label for="projectName" style="margin-left: 5px;margin-bottom: 0px;"><?php echo $projectInfo->projectName; ?></label>
            								</td>
                						</tr>
                					</table>
                				</div>
                			</div>
                		</div>
                	</div>
                	<div class="box-header with-border" style="margin-top: -10px;">
	                    <h3 class="box-title">Functional Requirement Information</h3>
	                </div>
	                <div class="box-body">
                		<div class="row">
                			<div class="col-sm-12">
                				<div class="form-group">
                					<table class="table-bordered" cellpadding="1px" cellspacing="1px" style="width: 100%;">
                						<tr>
                							<td height="10" style="background: #F2F3F4;width: 30%;text-align: left;">
                								<label for="functionNo" style="margin-right: 3px;margin-bottom: 0px;">Functional Requirement ID:</label>
                							</td>
                							<td height="10" style="width: 70%;">
                								<label for="projectName" style="margin-left: 5px;margin-bottom: 0px;"><?php echo $resultHeader->functionNo; ?></label>
                							</td>
                						</tr>
                						<tr>
                							<td height="10" style="background: #F2F3F4;width: 30%;text-align: left;">
                								<label for="functionNo" style="margin-right: 3px;margin-bottom: 0px;">Functional Requirement Description:</label>
                							</td>
                							<td height="10" style="width: 70%;">
                								<label for="projectName" style="margin-left: 5px;margin-bottom: 0px;"><?php echo $resultHeader->functionDescription; ?></label>
                							</td>
                						</tr>
                						<tr>
                							<td height="10" style="background: #F2F3F4;width: 30%;text-align: left;">
                								<label for="functionNo" style="margin-right: 3px;margin-bottom: 0px;">Functional Requirement Version:</label>
                							</td>
                							<td height="10" style="width: 70%;">
                								<label for="projectName" style="margin-left: 5px;margin-bottom: 0px;"><?php echo $resultHeader->functionVersionNumber; ?></label>
                							</td>
                						</tr>
                					</table>
                				</div>
                			</div>
                		</div>
                	</div>
                	<div class="box-header with-border" style="margin-top: -10px;">
	                    <h3 class="box-title">Functional Requirement Detail</h3>
	                </div>
	                <div class="box-body no-padding">
	                	<div class="row">
	                		<div class="col-sm-12">
	                			<div class="form-group">
	                				<table class="table table-condensed">
	                					<tbody>
	                						<tr>
	                							<th>#</th>
	                							<th>Input Name</th>
	                							<th>Data Type</th>
	                							<th>Data Length</th>
	                							<th>Unique</th>
	                							<th>NOT NULL</th>
	                							<th>Default value</th>
	                							<th>Min</th>
	                							<th>Max</th>
	                							<th>Table</th>
	                							<th>Column</th>
	                							<th><a href="#"><span class="label label-success">Add new input</span></a></th>
	                						</tr>
	                						<?php 
	                						$define = 1;
	                						foreach ($resultDetail as $value): ?>
                							<tr>
                								<td><?php echo $define++; ?></td>
                								<td>
                									<?php echo $value['inputName']; ?>
                								</td>
                								<td>
                									<?php echo $value['dataType']; ?>
                								</td>
                								<td>
                									<?php echo $value['dataLength']; ?>
                								</td>
                								<td>
                									<?php echo $value['constraintUnique']; ?>
                								</td>
                								<td>
                									<?php echo $value['constraintNull']; ?>
                								</td>
                								<td>
                									<?php echo $value['constraintDefault']; ?>
                								</td>
                								<td>
                									<?php echo $value['constraintMinValue']; ?>
                								</td>
                								<td>
                									<?php echo $value['constraintMaxValue']; ?>
                								</td>
                								<td>
                									<?php echo $value['tableName']; ?>
                								</td>
                								<td>
                									<?php echo $value['columnName']; ?>
                								</td>
                								<td>
                									<?php $keyId = $projectInfo->projectId."|".$value['inputId']."|".$value['schemaVersionId']; ?>
                									
                									<button type="button" name="edit" id="<?php echo $keyId; ?>" class="btn btn-primary btn-xs view" >Edit</button>

                									<!-- <a href="#"><span class="label label-primary">Edit</span></a>
                									<a href="#"><span class="label label-danger">Delete</span></a> -->
                								</td>
                							</tr>
	                						<?php endforeach ?>
	                					</tbody>
	                				</table>
	                			</div>
	                		</div>
	                	</div>
	                </div>
                </div>
                <div class="box box-warning">
                	<div class="box-header with-border">
	                    <h3 class="box-title">Change List</h3>
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
                <div align="right">
                	<button type="button" class="btn btn-primary">
                		<i class="fa fa-save"></i> Submit
                	</button>
                </div>
            </form>
		</div>
	</div>

	<div id="edit_input_modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content" style="border-radius:6px;">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Change Input of Functional Requirements ID: 
						<b> <?php echo $resultHeader->functionNo; ?> </b>
					</h4>
				</div>
				<div class="modal-body" id="input_detail">
					
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>

</section>
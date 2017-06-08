<section class="content-header">
	<h1>
		<span class="glyphicon glyphicon-list-alt"></span>
		Functional Requirements Version Inquiry
	</h1>
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
		<li><a href="#">Version Management</a></li>
		<li class="active">Functional Requirements Version Inquiry</li>
	</ol>

	<!-- Main content -->
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">
				<div class="box-header with-border">
             		<h3 class="box-title">Search Criteria</h3>
            	</div>
            	<form role="form" action="<?php echo base_url() ?>VersionManagement_FnReq/search/" method="post">
            		<input type="hidden" id="selectedProjectId"  name="selectedProjectId" value="<?php echo $projectId; ?>">
            		<input type="hidden" id="selectedFnReqId" name="selectedFnReqId" value="<?php echo $fnReqId; ?>">
         			<div class="box-body">
         				<div class="row">
         					<div class="col-sm-12">
         						<div class="form-group">
         							<label for="inputProjectName">
         								Project's name
	            						<span style="color:red;">*</span>:
	            					</label>
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
         						<div class="form-group">
         							<label for="inputFnReq">
         								Functional Requirements
	            						<span style="color:red;">*</span>:
	            					</label>
	            					<select id="fnReqCombo" name="inputFnReq" class="form-control select2" style="width: 100%;" value="<?php echo $fnReqId; ?>">
	            						<option value="">--Please Select--</option>
	            						<?php 
	            						if(isset($fnReqCombo) && 0 < count($fnReqCombo)){
	            							foreach($fnReqCombo as $value){ ?>
	            								<option value="<?php echo $value['functionId']; ?>" <?php echo set_select('inputFnReq', $value['functionId'], (!empty($fnReqId) && $fnReqId == $value['functionId']? TRUE : FALSE )); ?>>
		            									<?php echo $value['functionNo']; ?>: <?php echo $value['functionDescription']; ?>
		        								</option>
	            						<?php 
	            							} 
	            						} ?>
	            					</select>
	            					<?php echo form_error('inputFnReq', '<font color="red">','</font><br>'); ?>
         						</div>
         						<div class="form-group">
         							<label for="inputVersion">
         								Version
	            						<span style="color:red;">*</span>:
	            					</label>
	            					<select id="versionCombo" name="inputVersion" class="form-control select2" style="width: 100%;" value="<?php echo $fnReqVersionId; ?>">
	            						<option value="">--Please Select--</option>
	            						<?php if(isset($fnReqVersionCombo) && 0 < count($fnReqVersionCombo)){ 
	            							foreach($fnReqVersionCombo as $value){ ?>
	            								<option value="<?php echo $value['functionVersionId']; ?>" <?php echo set_select('inputVersion', $value['functionVersionId'], (!empty($fnReqVersionId) && $fnReqVersionId == $value['functionVersionId']? TRUE : FALSE )); ?>>
		            									<?php echo 'Version '.$value['functionVersionNumber']; ?>
		        								</option>
	            						<?php } } ?>
	            					</select>
	            					<?php echo form_error('inputVersion', '<font color="red">','</font><br>'); ?>
         						</div>
         						<div class="form-group">
         							<div align="right">
	            						<a href="<?php echo base_url(); ?>VersionManagement_FnReq/reset/">
	            							<button id="btnReset" type="button" class="btn bg-orange" style="width: 100px;">
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

	<!-- Result Session -->
	<?php if(null != $resultList && 0 < count($resultList)){ ?>
	<div class="row">
		<div class="col-md-12">
			<div class="box box-success box-solid">
				<div class="box-body">
					<div class="row">
						<div class="col-sm-3">
							<div class="form-group">
								<dl>
									<dt>Functional Requirement ID:</dt>
									<dd><?php echo $resultList[0]['functionNo'] ?></dd>
								</dl>
							</div>
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<dl>
									<dt>Functional Requirement Description:</dt>
									<dd><?php echo $resultList[0]['functionDescription'] ?></dd>
								</dl>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group">
								<dl>
									<dt>Version:</dt>
									<dd><?php echo $resultVersionInfo->functionVersionNumber; ?></dd>
								</dl>
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group">
								<dl>
									<dt>Status:</dt>
									<dd>
									<?php echo ("1" == $resultVersionInfo->activeFlag? constant("ACTIVE_STATUS"): constant("UNACTIVE_STATUS")); ?>
									</dd>
								</dl>
							</div>
						</div>
					</div>
				</div>
				<div class="box-body no-padding">
					<div class="row">
						<div class="col-sm-12">
							<div class="form-group">
								<table class="table table-striped" style="margin-top: -20px;">
									<tbody>
										<tr>
                							<th>#</th>
                							<th>Input Name</th>
                							<th>Data Type</th>
                							<th>Data Length</th>
                							<th>Scale</th>
                							<th>Unique</th>
                							<th>NOT NULL</th>
                							<th>Default</th>
                							<th>Min</th>
                							<th>Max</th>
                							<th>Table</th>
                							<th>Column</th>
                						</tr>
                						<?php 
	                						$define = 1;
	                						foreach ($resultList as $value): ?>
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
                									<?php echo $value['decimalPoint']; ?>
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
                							</tr>
	                						<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
</section>
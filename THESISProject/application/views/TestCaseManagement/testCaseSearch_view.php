<section class="content-header">
	<h1>
		<span class="glyphicon glyphicon-list-alt"></span>
		Test Cases
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
            		<input type="hidden" id="selectedProjectId" value="<?php echo isset($selectedProjectId)? $selectedProjectId : '' ?>">
            		<div class="box-body">
            			<div class="row">
	            			<div class="col-sm-12">
	            				<div class="form-group">
	            					<label for="inputProjectName">Project's name
	            						<span style="color:red;">*</span>:
	            					</label>
	        						<select name="inputProjectName" class="form-control select2" style="width: 100%;" value="<?php echo $formData->selectedProjectId ?>">
	        							<option value="">--Please Select--</option>
	        							<?php if(null != $projectCombo) {  ?>
	        							<?php foreach($projectCombo as $value): ?>
	        								<option value="<?php echo $value['projectId']; ?>" <?php echo set_select('inputProjectName', $value['projectId'], (!empty($formData->selectedProjectId) && $formData->selectedProjectId == $value['projectId']? TRUE : FALSE )); ?>>
	            									<?php echo $value['projectNameAlias']; ?>: <?php echo $value['projectName']; ?>
	        								</option>
	        							<?php endforeach; ?>
	            						<?php } ?>
	        						</select>
	        						<?php echo form_error('inputProjectName', '<font color="red">','</font><br>'); ?>
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

	<?php if(isset($searchFlag) && 'Y' == $searchFlag){ ?>
	<!-- Start: Search Result Section -->
	<div class="row">
		<div class="col-md-12">
			<div class="box box-success" style="margin-top: -10px;">
				<div class="box-header">
					<h3 class="box-title">Search Result</h3>
					<div class="pull-right">
						<button type="button" class="btn bg-olive btn-sm" style="width: 100px;" onclick="doOpenAddMoreScreen();">
							<i class="fa fa-plus"></i> Add more
						</button>
					</div>
				</div>

				<div class="box-body" style="margin-top: -10px;">
					<table id="resultTbl" class="table table-bordered tableResult">
						<thead>
			            	<tr style="background: #CACFD2;">
								<th>No.</th>
								<th>Test Case ID</th>
								<th>Test Case Version</th>
								<th>Relate Functional Requirement ID</th>
								<th>Effective Start Date</th>
								<th>Effective End Date</th>
								<th>Status</th>
								<th>Action</th>
			                </tr>
		                </thead>
		                <?php if(null != $resultList and 0 < count($resultList)){ ?>
			                <tbody>
			                	<?php 
				                $define = 1;
				                foreach($resultList as $value): 
				                	$classRow = (0 == $define%2)? 'even' : 'odd'; ?>
				                	<tr class="<?php echo $classRow; ?>">
				                		<td><?php echo $define++; ?></td>
				                		<td><?php echo $value['testCaseNo'] ?></td>
				                		<td><?php echo $value['testCaseVersion'] ?></td>
				                		<td>
				                			<?php echo (!empty($value['functionNo']))? $value['functionNo'].' : '.$value['functionDescription']: '';?>
				                		</td>
				                		<td><?php echo $value['effectiveStartDate'] ?></td>
				                		<td><?php echo $value['effectiveEndDate'] ?></td>
				                		<td><?php if('0' == $value['activeFlag'] ) { ?>
				                			<span class="label label-danger">
				                				<?php echo UNACTIVE_STATUS; ?>
				                			</span>
				                			<?php } else { ?>
				                			<span class="label label-success">
				                				<?php echo ACTIVE_STATUS; ?>
				                			</span>
				                			<?php } ?>
				                		</td>
				                		<td></td>
				                	</tr>
				                <?php endforeach; ?>
			                </tbody>
		                <?php } else { ?>
		                	<tr>
		                		<td colspan="8" style="text-align: center;">
		                			<span style="color: red;">Search Not Found!!</span>
		                		</td>
		                	</tr>
		                <?php } ?>
					</table>
				</div>
			</div>
		</div>
	</div>
	<!-- End: Search Result Section -->
	<?php } ?>
	<script type="text/javascript">
		function doOpenAddMoreScreen(){
			var projectId = $('#selectedProjectId').val();
			window.location  = baseUrl + "TestCaseManagement/addMore/" + projectId;
		}
	</script>
</section>
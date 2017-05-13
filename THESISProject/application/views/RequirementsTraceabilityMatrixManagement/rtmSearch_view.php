<section class="content-header">
	<h1>
		<span class="glyphicon glyphicon-list-alt"></span>
		Requirements Traceability Matrix
	</h1>
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
		<li><a href="#">Master Management</a></li>
		<li class="active">Requirements Traceability Matrix Search</li>
	</ol>

	<!-- Main content -->
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">
				<div class="box-header with-border">
             		<h3 class="box-title">Search Criteria</h3>
            	</div>
            	<form role="form" action="<?php echo base_url() ?>RTM/search/" method="post">
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
            					<div class="form-group">
	    							<div align="right">
	            						<a href="<?php echo base_url(); ?>RTM/reset/">
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
								<th width="10%">No.</th>
								<th width="45%">Functional Requirement ID</th>
								<th width="45%">Test Case ID.</th>
			                </tr>
		                </thead>
		                <?php if(null != $searchResultList and 0 < count($searchResultList)){ ?>
		                	 <tbody>
			                	<?php 
				                $define = 1;
				                foreach($searchResultList as $value): 
				                	$classRow = (0 == $define%2)? 'even' : 'odd'; ?>
					                <tr class="<?php echo $classRow; ?>">
					                	<td width="10%"><?php echo $define++; ?></td>
					                	<td width="45%"><?php echo $value['functionNo'] ?></td>
					                	<td width="45%"><?php echo $value['testCaseNo'] ?></td>
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
			window.location  = baseUrl + "RTM/addMore/" + projectId;
		}
	</script>
</section>
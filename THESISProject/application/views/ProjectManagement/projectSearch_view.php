<section class="content-header">
	<h1>
		Project Search
	</h1>
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
		<li><a href="#">Master Management</a></li>
		<li class="active">Project Search</li>
	</ol>

	<!-- Main content -->
	<div class="row">
	 	<div class="col-md-12">
	 		<div class="box box-primary">
	 			<div class="box-header with-border">
              		<h3 class="box-title">Search Criteria</h3>
            	</div>
	            <form role="form" action="<?php echo base_url() ?>Project/search/" method="post">
	            	<div class="box-body">
		            	<div class="form-group">
		            		<div class="col-sm-2">
		            			<label for="inputProjectName">Project Name: </label>
		            		</div>
		            		<div class="col-sm-4">
		            			<input type="text" class="form-control" name="inputProjectName" placeholder="ชื่อโครงการ" value="<?=set_value('inputProjectName')?>">
		            		</div>
		            		<div class="col-sm-2">
		            			<label for="inputProjectNameAlias">Project Name Alias: </label>
		            		</div>
		            		<div class="col-sm-4">
		            			<input type="text" class="form-control" name="inputProjectNameAlias" placeholder="ชื่อย่อโครงการ" value="<?=set_value('inputProjectNameAlias')?>">
		            		</div>
		                </div>
		                <div class="form-group">
		                	<div class="col-sm-2">
		            			<label for="inputStartDate">Start Date: </label>
		            		</div>
		            		<div class="col-sm-2">
		            			<div class="input-group date form_date" data-date="" data-date-format="dd/mm/yyyy" data-link-field="inputStartDateFrom" data-link-format="yyyy-mm-dd">
									<input name="inputStartDateFrom" class="form-control" size="16" type="text" readonly placeholder="ตั้งแต่วันที่" value="<?=set_value('inputStartDateFrom')?>">
								  	<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
								</div>
		            		</div>
		            		<div class="col-sm-2">
		            			<div class="input-group date form_date" data-date="" data-date-format="dd/mm/yyyy" data-link-field="inputStartDateTo" data-link-format="yyyy-mm-dd">
									<input name="inputStartDateTo" class="form-control" size="16" type="text" readonly placeholder="ถึงวันที่" value="<?=set_value('inputStartDateTo')?>">
								  	<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
								</div>
		            		</div>
		            		<div class="col-sm-2">
		            			<label for="inputEndDate">End Date: </label>
		            		</div>
		            		<div class="col-sm-2">
		            			<div class="input-group date form_date" data-date="" data-date-format="dd/mm/yyyy" data-link-field="inputEndDateFrom">
		            				<input name="inputEndDateFrom" class="form-control" size="16" type="text" readonly placeholder="ตั้งแต่วันที่" value="<?=set_value('inputEndDateFrom')?>">
		            				<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
								</div>
		            		</div>
		            		<div class="col-sm-2">
	            				<div class="input-group date form_date" data-date="" data-date-format="dd/mm/yyyy" data-link-field="inputEndDateTo">
									<input name="inputEndDateTo" class="form-control" size="16" type="text" readonly placeholder="ถึงวันที่" value="<?=set_value('inputEndDateTo')?>">
								  	<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
								</div>
	            			</div>
		                </div>
		                <div class="form-group">
		                	<div class="col-sm-2">
		            			<label for="inputCustomer">Customer: </label>
		            		</div>
		            		<div class="col-sm-4">
		            			<input type="text" class="form-control" name="inputCustomer" value="<?=set_value('inputCustomer')?>" placeholder="ชื่อลูกค้า">
		            		</div>
		                </div>
		                <div class="form-group">
		                	<div class="col-sm-12">
		                		<div align="right">
		                			<a href="<?php echo base_url(); ?>Project/newProject/">
		                				<button type="button" class="btn bg-olive" style="width:100px;text-align: center;">
		                					<i class="fa fa-plus"></i> Add Project
		                				</button>
		                			</a>
		                			<a href="<?php echo base_url(); ?>Project/reset/">
		                				<button type="button" class="btn bg-orange" style="width:100px;">
		                					<i class="fa fa-refresh"></i> Reset
		                				</button>
		                			</a>
		                			<button type="submit" class="btn bg-primary" style="width:100px;">
		                				<i class="fa fa-search"></i> Search
		                			</button>
		                		</div>
		                	</div>
		                </div>
	            	</div>
	            	<?php echo "<div style='color:red'>". $error_message ."</div>"?>
	            </form>
            </div>
	 	</div>
	</div>

 	<!-- Start: Search Result Section -->
	<?php if($result != null) { ?>
		<div class="row">
    		<div class="col-md-12">
    			<div class="box box-success" style="margin-top: -10px;">
    				<div class="box-header">
     	 				<h3 class="box-title">Search Result</h3>
    				</div>
    				<div class="box-body" style="margin-top: -10px;">
    					<table id="example2" class="table table-bordered">
    						<thead>
				            	<tr>
									<th>Project Name</th>
									<th>Project Name Alias</th>
									<th>Start Date</th>
									<th>End Date</th>
									<th>Customer</th>
									<th>Action</th>
				                </tr>
			                </thead>
			                <tbody>
			                	<?php foreach($result as $value): ?> 
			                		<tr>
			                			<td><?php echo $value['projectName']; ?></td>
			                			<td><?php echo $value['projectNameAlias']; ?></td>
			                			<td><?php echo $value['startDate']; ?></td>
			                			<td><?php echo $value['endDate']; ?></td>
			                			<td><?php echo $value['customer']; ?></td>
			                			<td>
			                				<a href="<?php echo base_url(); ?>Project/viewDetail/<?php echo $value['projectId']?>">
			                					<button type="button" class="btn btn-xs">
			                						<i class="fa fa-sticky-note-o"></i> View
			                					</button>
			                				</a>
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
	<!-- End: Search Result Section -->
</section>
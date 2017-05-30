<section class="content-header">
	<h1>
		<span class="glyphicon glyphicon-tasks"></span>
		Change Request Result
	</h1>
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i>Home</a></li>
		<li><a href="#">Change Management</a></li>
		<li class="active">Change Request Result</li>
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


			<div class="box box-primary box-solid">
				<!-- <div class="box-header with-border">
                    <h3 class="box-title">Change Request Information</h3>
                </div> -->
                <div class="box-body">
                	<div class="row">
                		<div class="col-sm-12">
                			<div class="form-group">
                				<h2 class="page-header">
                					<i class="fa fa-tag"></i>
                					Change Request No. : 
                					<b><?php echo $changeInfo->changeRequestNo ?></b>
                					<small class="pull-right">
                						Change User: 
                						<b><?php echo $changeInfo->changeUser ?></b>
                						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                						Change Date: 
                						<b><?php echo $changeInfo->changeDate ?></b>
                					</small>
                				</h2>
                			</div>
                		</div>
                	</div>
                	<!-- <div class="box-header with-border">
	                    <h3 class="box-title">Change Request Information</h3>
	                </div> -->
	                <div class="row">
	                	<div class="col-sm-12">
	                		<div class="form-group">
	                			<table class="table-bordered" cellpadding="1px" cellspacing="1px" style="width:100%">
	                				<tr>
	                					<td height="10" style="background: #F2F3F4;width: 30%;text-align: left;vertical-align: center;">
        									<label for="fnReqNo" style="margin-right: 3px;margin-bottom: 0px;">Functional Requirement No. :</label>
        								</td>
        								<td height="10" style="width: 20%;text-align: left;vertical-align: center;">
        									<label for="fnReqNo" style="margin-left: 5px;margin-bottom: 0px;"><?php echo $changeInfo->changeFunctionNo ?></label>
        								</td>
        								<td height="10" style="background: #F2F3F4;width: 30%;text-align: left;vertical-align: center;">
        									<label for="fnReqVer" style="margin-right: 3px;margin-bottom: 0px;">Functional Requirement Version :</label>
        								</td>
        								<td height="10" style="width: 20%;text-align: left;vertical-align: center;">
        									<label for="fnReqVer" style="margin-left: 5px;margin-bottom: 0px;"><?php echo $changeInfo->changeFunctionVersion ?></label>
        								</td>
	                				</tr>
	                			</table>
		        			</div>
	                	</div>
	                </div>
	                <div class="row">
	                	<div class="col-sm-12">
	                		<div class="form-group">
	                			<h4>Change Functional Requirement's Inputs List</h4>
	                			<table class="table table-condensed" cellpadding="1px" cellspacing="1px" style="width:100%">
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
	            							<th>Change Type</th>
	                					</tr>
	                					<!-- list -->
	                					<?php 
						                $define = 1;
						                foreach($changeInputList as $value): ?>
						                	<tr>
						                		<td>
							                		<?php echo $define++; ?>
							                	</td>
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
                									<?php echo $value['scale']; ?>
                								</td>
                								<td>
                									<?php echo $value['constraintUnique']; ?>
                								</td>
                								<td>
                									<?php echo $value['constraintNotNull']; ?>
                								</td>
                								<td>
                									<?php echo $value['constraintDefault']; ?>
                								</td>
                								<td>
                									<?php echo $value['constraintMin']; ?>
                								</td>
                								<td>
                									<?php echo $value['constraintMax']; ?>
                								</td>
                								<td>
                									<?php echo $value['refTableName']; ?>
                								</td>
                								<td>
                									<?php echo $value['refColumnName']; ?>
                								</td>
                								<td>
                									<?php 
                									if('add' == $value['changeType']){
                										echo "<span class=' badge bg-green'>".$value['changeType']."</span>";
                									}else if('edit' == $value['changeType']){
                										echo "<span class='badge bg-orange'>".$value['changeType']."</span>";
                									}else{
                										echo "<span class='badge bg-red'>".$value['changeType']."</span>";
                									}
                									?>
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

			<!-- 1. Functional Requirements -->
			<div class="box box-success box-solid collapsed-box">
				<div class="box-header with-border">
					<h3 class="box-title">The affected Functional Requirements</h3>
					<div class="box-tools pull-right">
						<button type="button" class="btn btn-box-tool" data-widget="collapse">
							<i class="fa fa-plus"></i>
						</button>
					</div>
				</div>
				<div class="box-body no-padding">
				<?php if(null != $affectedFnReqList && 0 < count($affectedFnReqList)) {?>
				<table class="table table-striped">
				<tbody>
					<tr>
						<th class="col-md-1">#</th>
						<th class="col-md-5">Functional Requirement No.</th>
						<th class="col-md-3 hidden-sm hidden-xs">Old Functional Requirement Version</th>
						<th class="col-md-3 hidden-sm hidden-xs">New Functional Requirement Version</th>
					</tr>
				<?php 
				$i = 1;
				foreach($affectedFnReqList as $value){ ?>
					<tr>
						<td><?php $i++ ?></td>
						<td><?php echo $value['functionNo'] ?></td>
						<td class="hidden-sm hidden-xs"><?php echo $value['oldFunctionVersion'] ?></td>
						<td class="hidden-sm hidden-xs"><?php echo $value['newFunctionVersion'] ?></td>
					</tr>
				<?php } ?>
				</tbody>
				</table>
				<?php }else{
					echo "<h3>".constant("ER_MSG_018")."</h3>";
				} ?>
				</div>
			</div>

			<!-- 2. Database Schema -->
			<div class="box box-success box-solid collapsed-box">
				<div class="box-header with-border">
					<h3 class="box-title">The affected Database Schema</h3>
					<div class="box-tools pull-right">
						<button type="button" class="btn btn-box-tool" data-widget="collapse">
							<i class="fa fa-plus"></i>
						</button>
					</div>
				</div>
				<div class="box-body">
				</div>
			</div>

			<!-- 3. Test Cases -->
			<div class="box box-success box-solid collapsed-box">
				<div class="box-header with-border">
					<h3 class="box-title">The affected Test Cases</h3>
					<div class="box-tools pull-right">
						<button type="button" class="btn btn-box-tool" data-widget="collapse">
							<i class="fa fa-plus"></i>
						</button>
					</div>
				</div>
				<div class="box-body">
				</div>
			</div>

			<!-- 4. RTM -->
			<div class="box box-success box-solid collapsed-box">
				<div class="box-header with-border">
					<h3 class="box-title">The affected Requirements Traceability Matrix</h3>
					<div class="box-tools pull-right">
						<button type="button" class="btn btn-box-tool" data-widget="collapse">
							<i class="fa fa-plus"></i>
						</button>
					</div>
				</div>
				<div class="box-body">
				</div>
			</div>
			
		</div>
	</div>
</section>
<section class="content-header">
	<h1>
		Dashboard
	</h1>
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
		<li class="active">Dashboard</li>
	</ol>

	<!-- Main content -->
	<div class="row">
		<div class="col-lg-3 col-ms-6">
			<div class="small-box bg-aqua">
				<div class="inner">
					<h3><?php echo $projectCount; ?></h3>
					<p>Projects</p>
				</div>
				<div class="icon">
					<i class="ion ion-ios-paper"></i>
				</div>
			</div>
		</div>
		<div class="col-lg-3 col-ms-6">
			<div class="small-box bg-green">
				<div class="inner">
					<h3><?php echo $requirementsCount; ?></h3>
					<p>Functional Requirements</p>
				</div>
				<div class="icon">
					<i class="ion ion-pound"></i>
				</div>
			</div>
		</div>
		<div class="col-lg-3 col-ms-6">
			<div class="small-box bg-yellow">
				<div class="inner">
					<h3><?php echo $testCaseCount; ?></h3>
					<p>Test Cases</p>
				</div>
				<div class="icon">
					<i class="ion ion-ios-search-strong"></i>
				</div>
			</div>
		</div>
		<div class="col-lg-3 col-ms-6">
			<div class="small-box bg-red">
				<div class="inner">
					<h3><?php echo $dbSchemaCount; ?></h3>
					<p>Database Schema</p>
				</div>
				<div class="icon">
					<i class="ion ion-ios-bolt"></i>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="box box-info">
				<div class="box-header with-border">
					<h3 class="box-title">
						Latest Change
					</h3>
				</div>
				<div class="box-body">
					<div class="table-responsive">
						<table class="table table-striped">
							<tbody>
								<tr>
									<th>#</th>
									<th>Project</th>
									<th>Change Request No.</th>
									<th>Change Date</th>
									<th>Change User</th>
									<th>Status</th>
								</tr>
							<?php if(isset($changeList) && 0 < count($changeList)){ 
								$define = 1;
								foreach($changeList as $value): ?>
								<tr>
									<td><?php echo $define++; ?></td>
									<td><?php echo $value['projectNameAlias']." : ".$value['projectName'] ?></td>
									<td><?php echo $value['changeRequestNo']; ?></td>
									<td><?php echo $value['changeDate']; ?></td>
									<td><?php echo $value['changeUser']; ?></td>
									<td>
									<?php
										if("CCL" == $value['changeStatus']){
											echo "<span class='label label-danger'>Cancelled
												 </span>";
										}else{
											echo "<span class='label label-success'>Closed
												 </span>";
										}
									 ?>
									 </td>
								</tr>
							<?php endforeach; } ?>
							</tbody>
						</table>
					</div>
				</div>
				<!-- <div class="box-footer clearfix">
					<ul class="pagination pagination-sm no-margin pull-right">
						
					</ul>
				</div> -->
			</div>
		</div>
	</div>
	<!-- <div class="row">
		<div class="col-md-12">
			<div class="box box-danger">
				<div class="box-header ui-sortable-handle">
					<i class="ion ion-clipboard"></i>
					<h3 class="box-title">To Do List</h3>
				</div>
				<div class="box-body">
					<ui class="todo-list ui-sortable">
						<li class="done">
							<span class="handle ui-sortable-handle">
								<i class="fa fa-ellipsis-v"></i>
								<i class="fa fa-ellipsis-v"></i>
							</span>
							<input type="checkbox">
							<span class="text">Master Screen</span>
						</li>
						<li class="done">
							<span class="handle ui-sortable-handle">
								<i class="fa fa-ellipsis-v"></i>
								<i class="fa fa-ellipsis-v"></i>
							</span>
							<input type="checkbox">
							<span class="text">Change Request Screen</span>
						</li>
						<li class="done">
							<span class="handle ui-sortable-handle">
								<i class="fa fa-ellipsis-v"></i>
								<i class="fa fa-ellipsis-v"></i>
							</span>
							<input type="checkbox">
							<span class="text">Latest Change Cancellation Screen</span>
						</li>
						<li>
							<span class="handle ui-sortable-handle">
								<i class="fa fa-ellipsis-v"></i>
								<i class="fa fa-ellipsis-v"></i>
							</span>
							<input type="checkbox">
							<span class="text">Test Data When revert version</span>
						</li>
						<li>
							<span class="handle ui-sortable-handle">
								<i class="fa fa-ellipsis-v"></i>
								<i class="fa fa-ellipsis-v"></i>
							</span>
							<input type="checkbox">
							<span class="text">ปัญหาเรื่องการอ่านภาษาไทย</span>
						</li>
						
					</ui>
				</div>
			</div>
		</div>
	</div> -->
</section>
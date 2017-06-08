<section class="content-header">
	<h1>
		<span class="glyphicon glyphicon-tasks"></span>
		Cancellation Result
	</h1>
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i>Home</a></li>
		<li><a href="#">Change Management</a></li>
		<li><a href="#">Cancellation</a></li>
		<li class="active">Cancellation Result</li>
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
			<div class="box box-solid">
				 <div class="box-body">
				 	<div class="row">
				 		<div class="col-sm-12">
				 			<div class="form-group">
				 				<h2 class="page-header">
				 					<i class="fa fa-tag"></i>
				 					Change Request No. : 
                					<b>
                					<?php echo isset($headerInfo['changeRequestNo'])? $headerInfo['changeRequestNo']: ""; ?>
                					</b>
                					<small class="pull-right">
                						Status:
                						<span class="label label-danger">
                							<b><?php echo isset($headerInfo['changeStatus'])? $headerInfo['changeStatus']: "";  ?></b>
                						</span>
                						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                						
                						Change User: 
                						<b><?php echo isset($headerInfo['changeUser'])? $headerInfo['changeUser']: "";  ?></b>
                						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                						
                						Change Date: 
                						<b><?php echo isset($headerInfo['changeDate'])? $headerInfo['changeDate']: "";  ?></b>
                					</small>
				 				</h2>
				 			</div>
				 		</div>
				 	</div>

				 	<div class="row">
				 		<div class="col-sm-12">
				 			<div class="form-group">
				 				<table class="table table-bordered" cellpadding="1px" cellspacing="1px">
	                				<tr>
	                					<td class="col-md-3" height="10" style="background: #F2F3F4;text-align: right;vertical-align: center;">
        									<label for="fnReqNo" style="margin-right: 3px;margin-bottom: 0px;">Functional Requirement ID:</label>
        								</td>
        								<td class="col-md-2" height="10" style="text-align: left;vertical-align: center;">
        									<label for="fnReqNo" style="margin-left: 5px;margin-bottom: 0px;"><?php echo isset($headerInfo['fnReqNo'])? $headerInfo['fnReqNo'] : "";  ?></label>
        								</td>
        								<td class="col-md-1" style="background: #F2F3F4;text-align: right;vertical-align: center;">
        									<label for="fnVersion" style="margin-right: 3px;margin-bottom: 0px;">Version :</label>
        								</td>
        								<td class="col-md-1">
        									<label for="fnVersion" style="margin-left: 5px;margin-bottom: 0px;"><?php echo isset($headerInfo['fnReqVer'])? $headerInfo['fnReqVer'] : "";  ?></label>
        								</td>
        								<td class="col-md-2" height="10" style="background: #F2F3F4;text-align: right;vertical-align: center;">
        									<label for="fnReqVer" style="margin-right: 3px;margin-bottom: 0px;">Description :</label>
        								</td>
        								<td class="col-md-3" height="10" style="text-align: left;vertical-align: center;">
        									<label for="fnReqVer" style="margin-left: 5px;margin-bottom: 0px;"><?php echo isset($headerInfo['fnReqDesc'])? $headerInfo['fnReqDesc']: "";  ?></label>
        								</td>
	                				</tr>
	                			</table>
				 			</div>
				 		</div>
				 	</div>

				 	<div class="row">
				 		<div class="col-sm-12">
				 			<div class="form-group table-responsive no-padding" style="margin-top: -5px;margin-bottom: -5px;">
				 				<h4>Change Functional Requirement's Inputs List</h4>
				 				<table class="table table-striped" cellpadding="1px" cellspacing="1px">
	                				<tbody>
	                					<tr>
	                						<th class="col-md-1">#</th>
	                						<th class="col-md-1">Change Type</th>
	            							<th class="col-md-1">Input Name</th>
	            							<th class="col-md-1">Data Type</th>
	            							<th class="col-md-1">Data Length</th>
	            							<th class="col-md-1">Scale</th>
	            							<th class="col-md-1">Unique</th>
	            							<th class="col-md-1">NOT NULL</th>
	            							<th class="col-md-1">Default</th>
	            							<th class="col-md-1">Min</th>
	            							<th class="col-md-1">Max</th>
	            							<th class="col-md-1">Table</th>
	            							<th class="col-md-1">Column</th>
	                					</tr>
	                					<!-- list -->
	                					<?php 
						                $define = 1;
						                foreach($detailInfo as $value): ?>
						                	<tr>
						                		<td>
							                		<?php echo $define++; ?>
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
						                	</tr>
						                <?php endforeach ?>
	                				</tbody>
	                			</table>
				 			</div>
				 		</div>
				 	</div>

				 </div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
			<div class="box box-solid" style="margin-top: 10px;">
				<div class="box-body">
					<div class="row">
						<div class="col-sm-12">
							<div class="form-group" id="_inputForm">
								<label>The reason for cancelling the change.</label>
								<input type="text" class="form-control" id="inputReason" name="inputReason" value="<?php echo $reason ?>" disabled>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
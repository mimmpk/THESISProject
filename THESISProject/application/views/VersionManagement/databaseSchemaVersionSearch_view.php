<section class="content-header">
	<h1>
		<span class="glyphicon glyphicon-list-alt"></span>
		Inquiry Database Schema by Version
	</h1>
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
		<li><a href="#">Version Management</a></li>
		<li class="active">Inquiry Database Schema by Version </li>
	</ol>
	<!-- Main content -->
	<div class="row">
		<div class="col-md-12">
			<?php if(!empty($error_message)) { ?>
			<div class="alert alert-danger alert-dismissible" style="margin-top: 3px;margin-bottom: 3px;">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
				<?php echo $error_message; ?>
			</div>
			<?php } ?>
			<div class="box box-primary">
				<div class="box-header with-border">
					<h3 class="box-title">Search Criteria</h3>
				</div>
				<form class="form-horizontal" action="<?php echo base_url() ?>VersionManagement_Schema/search/" method="post">
					<div class="box-body">
						<div class="form-group">
							<label for="inputProjectName" class="col-sm-2 control-label">
 								Project's name
        						<span style="color:red;">*</span>:
        					</label>
        					<div class="col-sm-10">
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
						</div>
						<div class="form-group">
							<label for="inputTable" class="col-sm-2 control-label">
 								Table Name
        						<span style="color:red;">*</span>:
        					</label>
        					<div class="col-sm-2">
        						<select id="tableCombo" name="inputTable" class="form-control select2" style="width: 100%;" value="<?php echo $tableName; ?>">
            						<option value="">--Please Select--</option>
            						<?php 
            						if(isset($tableCombo) && 0 < count($tableCombo)){
            							foreach($tableCombo as $value){ ?>
            								<option value="<?php echo $value['tableName']; ?>" <?php echo set_select('inputTable', $value['tableName'], (!empty($tableName) && $tableName == $value['tableName']? TRUE : FALSE )); ?>>
	            								   <?php echo $value['tableName']; ?>
	        								</option>
            						<?php 
            							} 
            						} ?>
            					</select>
            					<?php echo form_error('inputTable', '<font color="red">','</font><br>'); ?>
        					</div>
        					<label for="inputColumn" class="col-sm-2 control-label">
 								Column Name :
        					</label>
        					<div class="col-sm-2">
        						<select id="columnCombo" name="inputColumn" class="form-control select2" style="width: 100%;" value="<?php echo $columnName; ?>">
            						<option value="">--Please Select--</option>
            						<?php 
            						if(isset($columnCombo) && 0 < count($columnCombo)){
            							foreach($columnCombo as $value){ ?>
            								<option value="<?php echo $value['columnName']; ?>" <?php echo set_select('inputColumn', $value['columnName'], (!empty($columnName) && $columnName == $value['columnName']? TRUE : FALSE )); ?>>
	            							    <?php echo $value['columnName']; ?>
	        								</option>
            						<?php 
            							} 
            						} ?>
            					</select>
        					</div>
        					<label for="inputSchemaVersion" class="col-sm-2 control-label">
 								Schema Version :
        					</label>
        					<div class="col-sm-2">
        						<select id="schemaVersionCombo" name="inputVersion" class="form-control select2" style="width: 100%;" value="<?php echo $schemaVersionId; ?>">
            						<option value="">--Please Select--</option>
            						<?php 
            						if(isset($schemaVersionCombo) && 0 < count($schemaVersionCombo)){
            							foreach($schemaVersionCombo as $value){ ?>
            								<option value="<?php echo $value['schemaVersionId']; ?>" <?php echo set_select('inputVersion', $value['schemaVersionId'], (!empty($schemaVersionId) && $schemaVersionId == $value['schemaVersionId']? TRUE : FALSE )); ?>>
	            								<?php echo 'Version '.$value['schemaVersionNumber']; ?>
	        								</option>
            						<?php 
            							} 
            						} ?>
            					</select>
        					</div>
						</div>
						<div class="form-group">
 							<div class="col-md-12" align="right">
        						<a href="<?php echo base_url(); ?>VersionManagement_Schema/reset/">
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
				</form>
			</div>
		</div>
	</div>

    <!-- Result Session -->
    <?php if(null != $resultList && 0 < count($resultList)){ ?>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-success box-solid">
                <div class="box-body no-padding">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <table id="resultTbl" class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <th>#</th>
                                            <th>Table Name</th>
                                            <th>Column Name</th>
                                            <th>Version</th>
                                            <th>isPrimaryKey</th>
                                            <th>Data Type</th>
                                            <th>Data Length</th>
                                            <th>Scale</th>
                                            <th>Unique</th>
                                            <th>NOT NULL</th>
                                            <th>Default</th>
                                            <th>Min</th>
                                            <th>Max</th>
                                            <th>Status</th>
                                        </tr>
                                        <?php 
                                            $define = 1;
                                            foreach ($resultList as $value): ?>
                                            <tr>
                                                <td><?php echo $define++; ?></td>
                                                <td><?php echo $value['tableName']; ?></td>
                                                <td><?php echo $value['columnName']; ?></td>
                                                <td>
                                                    <?php echo $value['schemaVersionNumber']; ?>
                                                </td> 
                                                <td><?php echo $value['constraintPrimaryKey']; ?></td>
                                                <td><?php echo $value['dataType']; ?></td>
                                                <td><?php echo $value['dataLength']; ?></td>
                                                <td><?php echo $value['decimalPoint']; ?></td>
                                                <td><?php echo $value['constraintUnique']; ?></td>
                                                <td><?php echo $value['constraintNull']; ?></td>
                                                <td><?php echo $value['constraintDefault']; ?></td>
                                                <td><?php echo $value['constraintMinValue']; ?></td>
                                                <td><?php echo $value['constraintMaxValue']; ?></td>
                                                <td>
                                                    <?php 
                                                    if("1" == $value['activeFlag']){
                                                        echo "<span class='label label-success'>".constant("ACTIVE_STATUS")."</span>";
                                                    }else{
                                                        echo "<span class='label label-danger'>".constant("UNACTIVE_STATUS")."</span>";
                                                    } ?>    
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
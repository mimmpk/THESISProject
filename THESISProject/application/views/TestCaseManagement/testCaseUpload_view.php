<section class="content-header">
	<h1>
		<span class="glyphicon glyphicon-list-alt"></span>
		Test Case
	</h1>
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
		<li><a href="#">Master Management</a></li>
		<li class="active">Test Case Uploading</li>
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
				<div class="box-header with-border">
             		<h3 class="box-title">Import Test Case Information</h3>
            	</div>
            	<form role="form" action="<?php echo base_url() ?>TestCase/doUpload/" method="post" enctype="multipart/form-data">
        			<?php echo form_hidden($hfield); ?>
	        		<div class="box-body">
	        			<div class="row">
	        				<div class="col-sm-12">
	        					<div class="form-group">
	        						<table cellpadding="1px" cellspacing="1px">
	        							<tr>
	        								<td height="10" style="background: #F2F3F4;width: 25%;text-align: right;vertical-align: center;">
	        									<label for="inputFileName" style="margin-right: 3px;margin-bottom: 0px;">Project Name Alias:</label>
	        								</td>
	        								<td height="10" style="width: 25%;text-align: left;vertical-align: center;">
	        									<label for="inputFileName" style="margin-left: 5px;margin-bottom: 0px;"><?php echo $hfield['projectNameAlias'] ?></label>
	        								</td>
	        								<td height="10" style="background: #F2F3F4;width: 25%;text-align: right;vertical-align: center;">
	        									<label for="inputFileName" style="margin-right: 3px;margin-bottom: 0px;">Project Name:</label>
	        								</td>
	        								<td height="10" style="width: 25%;text-align: left;vertical-align: center;"">
	        									<label for="inputFileName" style="margin-left: 5px;margin-bottom: 0px;"><?php echo $hfield['projectName'] ?></label>
	        								</td>
	        							</tr>
	        						</table>
	        					</div>
	        					<div class="form-group">
	        						<label for="inputFileName">Select file to upload<span style="color:red;">*</span>:</label>
	        						<input type="file" name="fileName" size="20" />
	        						<?php echo form_error('inputFileName', '<font color="red">','</font><br>'); ?>
	        					</div>
	        					<div class="form-group">
	        						<?php $var = ('0' == $hfield['screenMode'])? 'disabled' : ''; ?>
        							<button type="submit" class="btn bg-primary" style="width: 100px;" <?php echo $var;?> >
                						<i class="fa fa-upload"></i>Upload
                					</button>
	        					</div>
	        				</div>
	        			</div>
	        		</div>
        		</form>
        	</div>
		</div>
	</div>
</section>
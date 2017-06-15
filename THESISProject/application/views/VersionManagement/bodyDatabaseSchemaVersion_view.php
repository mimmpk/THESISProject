<body class="hold-transition skin-blue-light sidebar-mini">
	<?php $this->view('template/menu'); ?>
	<?php $this->view('template/body_javascript'); ?>

	<script type="text/javascript">
		var baseUrl = '<?php echo base_url(); ?>';

		$(function(){
			$(document).on('change','#projectCombo',function(){
				//Clear Table Combo
				$('#tableCombo').html(); 
				$('#tableCombo').html("<option value=''>--Please Select--</option>");
				//Clear Column Combo
				$('#columnCombo').html();
				$('#columnCombo').html("<option value=''>--Please Select--</option>");
				//Clear Schema Version Combo
				$('#schemaVersionCombo').html();
				$('#schemaVersionCombo').html("<option value=''>--Please Select--</option>");

				var projectId = $(this).val();
				if('' != projectId){
					$.ajax({
						url: baseUrl+'VersionManagement_Schema/getRelatedTableName/',
						data: {projectId : projectId},
						type: 'POST',
						success: function(result){
							//alert(result);
		                    $('#tableCombo').html();  
		                    $('#tableCombo').html(result);

		                    //$('#selectedProjectId').val(projectId);
	                   	}
					});
				} 
			});

			$(document).on('change','#tableCombo',function(){
				//Clear Column Combo
				$('#columnCombo').html();
				$('#columnCombo').html("<option value=''>--Please Select--</option>");
				//Clear Schema Version Combo
				$('#schemaVersionCombo').html();
				$('#schemaVersionCombo').html("<option value=''>--Please Select--</option>");

				var tableName = $(this).val();
				var projectId = $('#projectCombo').val();
				if('' != tableName){
					$.ajax({
						url: baseUrl+'VersionManagement_Schema/getRelatedColumnName/',
						data: {projectId : projectId, tableName : tableName},
						type: 'POST',
						success: function(result){
							//alert(result);
		                    $('#columnCombo').html();  
		                    $('#columnCombo').html(result);
	                   	}
					});
				}
			});

			$(document).on('change','#columnCombo',function(){
				//Clear Schema Version Combo
				$('#schemaVersionCombo').html();
				$('#schemaVersionCombo').html("<option value=''>--Please Select--</option>");

				var projectId = $('#projectCombo').val();
				var tableName = $('#tableCombo').val();
				var columnName = $(this).val();
				if('' != columnName){
					$.ajax({
						url: baseUrl+'VersionManagement_Schema/getRelatedColumnVersion/',
						data: {projectId : projectId, tableName : tableName, columnName : columnName},
						type: 'POST',
						success: function(result){
							//alert(result);
		                    $('#schemaVersionCombo').html();  
		                    $('#schemaVersionCombo').html(result);
	                   	}
					});
				}
			});

		});
	</script>
</body>
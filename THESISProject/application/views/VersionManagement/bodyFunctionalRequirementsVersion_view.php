<body class="hold-transition skin-blue-light sidebar-mini">
	<?php $this->view('template/menu'); ?>
	<?php $this->view('template/body_javascript'); ?>

	<script type="text/javascript">
		var baseUrl = '<?php echo base_url(); ?>'; 
		
		$(function(){
			$(document).on('change','#projectCombo',function(){
				//Clear Function Combo
				$('#fnReqCombo').html(); 
				$('#fnReqCombo').html("<option value=''>--Please Select--</option>");
				//Clear Version Combo
				$('#versionCombo').html();
				$('#versionCombo').html("<option value=''>--Please Select--</option>");  

				var projectId = $(this).val();
				if('' != projectId){
					$.ajax({
						url: baseUrl+'VersionManagement_FnReq/getRelatedFnReq/',
						data: {projectId : projectId},
						type: 'POST',
						success: function(result){
							//alert(result);
		                    $('#fnReqCombo').html();  
		                    $('#fnReqCombo').html(result);

		                    $('#selectedProjectId').val(projectId);
	                   	}
					});
				}
			});

			$(document).on('change', '#fnReqCombo', function(){
				//Clear Version Combo
				$('#versionCombo').html();
				$('#versionCombo').html("<option value=''>--Please Select--</option>");

				var projectId = $('#selectedProjectId').val();
				var functionId = $(this).val();
				if('' != functionId){
					$.ajax({
						url: baseUrl+'/VersionManagement_FnReq/getRelatedFnReqVersion/',
						data: {projectId: projectId, functionId: functionId},
						type: 'POST',
						success: function(result){
							$('#versionCombo').html();  
		                    $('#versionCombo').html(result);

		                    $('#selectedFnReqId').val(functionId);
						}
					});
				}
			});

			$("#btnReset").click(function() {
				window.location  = baseUrl + "VersionManagement_FnReq/reset";
			});

			$("#btnDiffVersion").click(function(){
				var projectId = $('#selectedProjectId').val();
				var functionId = $('#selectedFnReqId').val();
				var functionVersion = $('#selectedFnReqVersion').val();
				$.ajax({
					url: baseUrl+'/VersionManagement_FnReq/diffWithPreviousVersion/',
					data: {projectId: projectId, functionId: functionId, functionVersion: functionVersion},
					type: 'POST',
					success: function(result){
						if("" != result){
							$('#diffVersionModal').modal('show');
							$('#diffVersionContent').html(result);
						}else{
							alert('This is an initial Version!!');
						}
						return false;
					}
				});
			});
		});
	</script>
</body>
<body class="hold-transition skin-blue-light sidebar-mini">
	<?php $this->view('template/menu'); ?>
	<?php $this->view('template/body_javascript'); ?>
	
	<script type="text/javascript">
		var baseUrl = '<?php echo base_url(); ?>'; 

		$(function(){
			$(document).on('change','#projectCombo',function(){
				//Clear Function Combo
				$('#testCaseCombo').html(); 
				$('#testCaseCombo').html("<option value=''>--Please Select--</option>");
				//Clear Version Combo
				$('#versionCombo').html();
				$('#versionCombo').html("<option value=''>--Please Select--</option>");

				var projectId = $(this).val();
				if('' != projectId){
					$.ajax({
						url: baseUrl+'VersionManagement_TestCase/getRelatedTestCase/',
						data: {projectId : projectId},
						type: 'POST',
						success: function(result){
							//alert(result);
		                    $('#testCaseCombo').html();  
		                    $('#testCaseCombo').html(result);
	                   	}
					});
				}
			});
			
			$(document).on('change','#testCaseCombo',function(){
				//Clear Version Combo
				$('#versionCombo').html();
				$('#versionCombo').html("<option value=''>--Please Select--</option>");

				var testCaseId = $(this).val();
				if('' != testCaseId){
					$.ajax({
						url: baseUrl+'VersionManagement_TestCase/getRelatedTestCaseVersion/',
						data: {testCaseId : testCaseId},
						type: 'POST',
						success: function(result){
							//alert(result);
		                    $('#versionCombo').html();  
		                    $('#versionCombo').html(result);
	                   	}
					});
				}
			});

			$("#btnReset").click(function() {
				window.location  = baseUrl + "VersionManagement_TestCase/reset";
			});

			$("#btnDiffVersion").click(function(){
				var testCaseId = $('#selectedTestCaseId').val();
				var testCaseVersionId = $('#selectedTestCaseVersionId').val();
				$.ajax({
					url: baseUrl+'VersionManagement_TestCase/diffWithPreviousVersion',
					data: {testCaseId: testCaseId, testCaseVersionId: testCaseVersionId},
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
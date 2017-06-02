<body class="hold-transition skin-blue-light sidebar-mini">
	
	<?php $this->view('template/menu'); ?>
	<?php $this->view('template/body_javascript'); ?>

	<script type="text/javascript">
		var baseUrl = '<?php echo base_url(); ?>'; 
		
		$(function () {
			$('.form_date').datetimepicker({
		        language:  'en',
		        weekStart: 1,
		        todayBtn:  1,
				autoclose: 1,
				todayHighlight: 1,
				startView: 2,
				minView: 2,
				forceParse: 0
		    });

		    $('#datepicker1').datepicker({
		    	autoclose: true
		    });

		    $('#datepicker2').datepicker({
		    	autoclose: true
		    });

		    //Initialize Select2 Elements
    		$(".select2").select2();

    		//iCheck for checkbox and radio inputs
		    $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
				checkboxClass: 'icheckbox_minimal-blue',
				radioClass: 'iradio_minimal-blue'
		    });


		    //**************************[Start: Change Management]*************************
		    function fetch_post_data(keyId){
				$.ajax({
					url:"<?php echo base_url(); ?>ChangeManagement/viewFRInputDetail/",
					method:"POST",
					data:{keyId: keyId},
					success:function(data){
						$('#edit_input_modal').modal('show');
						$('#input_detail').html(data);
					}
				});
			}

			$(document).on('click', '.view', function(){
				var keyId = $(this).attr("id");
				fetch_post_data(keyId);
			});


			$(document).on('click', '.addInput', function(){
				var projectId = $('input[name=projectId]').val();
				var functionId = $('input[name=functionId]').val();
				var functionVersion = $('input[name=functionVersion]').val();

				$.ajax({
					url:"<?php echo base_url(); ?>ChangeManagement/addFRInputDetail/",
					method:"POST",
					data:{
						projectId: projectId, 
						functionId: functionId, 
						functionVersion: functionVersion
					},
					success:function(data){
						$('#edit_input_modal').modal('show');
						$('#input_detail').html(data);
					}
				});
			});

			$(document).on('click', '.delete', function(){
				var msg = "Are you sure to delete this functional requirement's input?";
				
				if(confirm(msg)){
					var keyId = $(this).attr("id");
					var functionId = $('input[name=functionId]').val();
					var functionVersion = $('input[name=functionVersion]').val();

					$.ajax({
						url:"<?php echo base_url(); ?>ChangeManagement/saveTempFRInput_delete/",
						method:"POST",
						data:{keyId: keyId, functionId: functionId, functionVersion: functionVersion},
						success:function(data){
							if("" != data){
								var result = data.split("|");
								if("error" == result[0]){
									alert(result[1]);
									return false;
								}else{
									$('#inputChangeListTbl').html(data); 
								}
							}
							return false;
						}
					});
				}
			});

			$('#changeInput_form').on("submit", function(event){
				event.preventDefault(); 

				//alert($('input[name=projectId]').val());
				
				var newUnique = ($('#inputUnique').is(":checked"))? "Y": "N";
				var newNotNull = ($('#inputNotNull').is(":checked"))? "Y": "N";

				//var projectId = $('input[name=projectId]').val();
				//var functionId = $('input[name=functionId]').val();

				var changeType = $('#changeType').val();

				if('edit' == changeType){
					if($('#inputDataType').val() == "" 
						&& $('#inputDataLength').val() == "" 
						&& $('#inputScale').val() == "" 
						&& newUnique == $('#oldUniqueValue').val() 
						&& newNotNull == $('#oldNotNullValue').val() 
						&& $('#inputDefault').val() == "" 
						&& $('#inputMinValue').val() == "" 
						&& $('#inputMaxValue').val() == ""){
						alert("Please enter at least one field.");
						return false;
					}
				}else{
					if($('#inputDataType').val() == "" 
						|| $('#inputName').val() == "" 
						|| $('#inputTableName').val() == "" 
						|| $('#inputColumnName').val() == ""){
						alert("Please enter all required fields.");
						return false;
					}
				}

				//Pass Validation
				$.ajax({
					url: "<?php echo base_url(); ?>ChangeManagement/saveTempFRInput_edit/",
					method: "POST",
					data: $("#changeInput_form").serialize(),
					success: function(data){
						if(null != data){
							//alert(data);
							var result = data.split("|");
							if("error" == result[0]){
								alert(result[1]);
								return false;
							}else{
								//alert(result[1]);
								$('#changeInput_form')[0].reset();  
     							$('#edit_input_modal').modal('hide');
     							$('#inputChangeListTbl').html(data);  
							}
						}else{
							alert("There is a problem when save data, Please try to save again.");
							return false; 
						}
					},
					error: function(){ 
						alert("There is a problem when save data, Please try to save again.");
						return false; 
					}
				});
			});

			$(document).on('click', '.deleteTmpFRInputChg', function(){
				var lineNo = $(this).attr("id");
				var msg = "Are you sure to delete?";
				if(confirm(msg)){
					var functionId = $('input[name=functionId]').val();
					var functionVersion = $('input[name=functionVersion]').val();

					$.ajax({
						url:"<?php echo base_url(); ?>ChangeManagement/deleteTempFRInputList/",
						method:"POST",
						data:{lineNumber: lineNo, functionId: functionId, functionVersion: functionVersion},
						success:function(data){
							//alert(data);
							if("" != data){
								var result = data.split("|");
								if("error" == result[0]){
									alert(result[1]);
									return false;
								}else{
									$('#inputChangeListTbl').html(data); 
								}
							}
							return false;
						}
					});	
				}
			});
			//**************************[End: Change Management]*************************


			//**************************[Start: Change Cancellation]*************************


			/*$('#cancelChange_form').on("submit", function(event){
				event.preventDefault(); 

				var reason = $('#inputReason').val();
				if(null == reason || "" == reason){
					$("#").addClass("myClass yourClass");
					return false;
				}else{

				}
			});*/
			//**************************[End: Change Cancellation]*************************

		});
    </script>
</body>
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

			$("body").on( "click", "#saveChange", function(){
				 alert( "Handler for .click() called." );
			});
		});
    </script>
    <style>
		.ui-autocomplete{
			z-index:1050;
		}
	</style>
</body>
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
		});
    </script>
</body>
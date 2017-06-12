<body class="hold-transition skin-blue-light sidebar-mini">
	<?php $this->view('template/menu'); ?>
	<?php $this->view('template/body_javascript'); ?>

	<script type="text/javascript">
		var baseUrl = '<?php echo base_url(); ?>';

		$(function(){
			$(document).on('change','#projectCombo',function(){
				
			});
		});
	</script>
</body>
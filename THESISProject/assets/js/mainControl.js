$(document).ready(function(){
	function mst001CancelSave(){
		var msg = "Are you sure to cancel? - data that you have entered may not be saved.";
		if(confirm(msg)){
			var mode = $('#mode').val();
			//alert(mode);
			if("new" == mode){
				window.location  = baseUrl + "Project/back";
			}else{
				var projectId = $('#projectId').val();
				window.location  = baseUrl + "Project/viewDetail/"+ projectId;
			}
		}
	}
});

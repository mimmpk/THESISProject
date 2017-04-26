$(function(){
    var activeTitle = $("#activeTitle").val();
    var activePage = $("#activePage").val();
   
   	//alert(activeTitle + " " + activePage);
    if((activeTitle !== undefined) && (activePage !== undefined)){
    	$(".treeview-menu").removeClass('menu-open');
    	$(".treeview-menu").css({ display: "none" });
    	$('.treeview-menu li.active').removeClass('active');

    	$("#"+activeTitle).addClass('menu-open');
    	$("#"+activeTitle).css({ display: "block" });
    	$("#"+activePage).addClass('active');
    }else{
    	$(".treeview-menu").removeClass('menu-open');
    	$(".treeview-menu").css({ display: "none" });
    	$('.treeview-menu li.active').removeClass('active');
    }

});
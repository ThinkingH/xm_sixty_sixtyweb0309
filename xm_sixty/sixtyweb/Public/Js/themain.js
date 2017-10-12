
/* ************************************* */
$(document).bind("keydown",function(e){   
	e=window.event||e;
	if(e.keyCode==116){
		e.keyCode = 0;
		return false; //屏蔽F5刷新键   
	}
});
/* ************************************* */


/* ************************************* */
//对所有有背景颜色的下拉框执行背景颜色改变

$(".yu_selectcolor").live('change',function(){
	var thecolor = $(this).find("option:selected").css('background-color'); 
	$(this).css('background-color',thecolor);
	//alert(color);
	//this.attr('color')
});

/* ************************************* */


//动态获取当前时间
function yu_getnowtime(){
	var datedd = new Date();
	var str = '';
	var Y = datedd.getFullYear();
	var m = datedd.getMonth();
	var d = datedd.getDate();
	var H = datedd.getHours();
	var i = datedd.getMinutes();
	var s = datedd.getSeconds();
	if(m<10){ m="0"+m; }
	if(d<10){ d="0"+d; }
	if(H<10){ H="0"+H; }
	if(i<10){ i="0"+i; }
	if(s<10){ s="0"+s; }
	
	str = Y+"-"+m+"-"+d+" "+H+":"+i+":"+s;
	return str;
}




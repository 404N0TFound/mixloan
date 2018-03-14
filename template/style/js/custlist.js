var curUnix;
var gdFlag=false;//广大白金卡2018/1/1之后数据(包含2018/1/1)
var minUnix='1496275200';//最小日期
var gdminUnix='1514736000';//光大白金卡2018/1/1前展示放款成功数
// 返回值判断
function dataTransform(res){
    var ret="";
    if(res==null||res==undefined){
        ret="";
    }else{
        ret=res;
    }
    return ret;
}
//向左筛选时间
$(".btnLeft").click(function(){
	if($(this).hasClass("btnLeftL")){
		var _year=parseInt($("#pointY").html());
		var _month=parseInt($("#pointM").html());
		if(_month==1){
			$("#pointY").html(_year-1);
			$("#pointM").html(12);
		}else{
			$("#pointM").html(_month-1);
		}
		$(".btnRight").removeClass("btnRightD").addClass("btnRightL");
		var pointUnix=$("#pointY").html()+"-"+$("#pointM").html();
		if(parseInt(DateToUnix(pointUnix))<parseInt(gdminUnix)){
			gdFlag=true;//光大白金卡2018/1/1之前数据
		}else{
			gdFlag=false;//光大白金卡2018/1/1之后数据
		}
		if(parseInt(DateToUnix(pointUnix))<parseInt(minUnix)){
			$(".btnLeft").removeClass("btnLeftL").addClass("btnLeftD");
		}
		ajaxData($("#pointY").html(),$("#pointM").html());
	}	
})
//向右筛选时间
$(".btnRight").click(function(){
	if($(this).hasClass("btnRightL")){
		var _year=parseInt($("#pointY").html());
		var _month=parseInt($("#pointM").html());
		if(_month==12){
			$("#pointY").html(_year+1);
			$("#pointM").html(1);
		}else{
			$("#pointM").html(_month+1);
		}
		$(".btnLeft").removeClass("btnLeftD").addClass("btnLeftL");
		var pointUnix=$("#pointY").html()+"-"+$("#pointM").html();
		if(parseInt(DateToUnix(pointUnix))>=parseInt(gdminUnix)){
			gdFlag=false;//光大白金卡2018/1/1之后数据
		}else{
			gdFlag=true;//光大白金卡2018/1/1之前数据
		}
		if(parseInt(DateToUnix(pointUnix))>=parseInt(curUnix)){
			$(".btnRight").removeClass("btnRightL").addClass("btnRightD");
		}
		ajaxData($("#pointY").html(),$("#pointM").html());
	}	
})
// 日期 转换为 Unix时间戳
function DateToUnix(string) {
    var f = string.split(' ', 2);
        var d = (f[0] ? f[0] : '').split('-', 3);
        var t = (f[1] ? f[1] : '').split(':', 3);
        return (new Date(
                parseInt(d[0], 10) || null,
                (parseInt(d[1], 10) || 1),
                parseInt(d[2], 10) || null,
                parseInt(t[0], 10) || null,
                parseInt(t[1], 10) || null,
                parseInt(t[2], 10) || null
                )).getTime() / 1000;
  }
// 时间戳转换日期
function UnixToDate(unixTime, isFull, timeZone) {  
    if (typeof (timeZone) == 'number'){  
        unixTime = parseInt(unixTime) + parseInt(timeZone) * 60 * 60;  
    }  
    var time = new Date(unixTime * 1000);  
    var ymdhis = "";  
    ymdhis += time.getUTCFullYear() + "-";  
    ymdhis += (time.getUTCMonth()+1); 
    return ymdhis;  
}
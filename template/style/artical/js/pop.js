// 非代理弹窗
document.writeln("<div class=\'pop_art\'>");
document.writeln("	        <div class=\'temp_share_bg\'>");
document.writeln("		       	<div class=\'temp_share_contain_add temp_share_contain_new\' >");
document.writeln("		            <div class=\'temp_pic\'><img src=\'"+aa+"/idai/images/app/head.png\' /></div>");
document.writeln("		            <h1></h1>");
document.writeln("		            <h2>成为i代流量主即可代理所有商品</h2>");
document.writeln("		            <h2 style=\'text-align:center;\'>i代在手，赚钱无忧</h2>");
document.writeln("		            <p>如有疑问，请联系400-110-0760</p>");
document.writeln("		            <div class=\'buy_hf clearfix\'>");
document.writeln("			            <a href=\'/Wap/goods/index/id/1.html\' ><img src=\'"+aa+"/idai/images/app/buy.png\' /></a>");
document.writeln("			        </div>        ");
document.writeln("			        <div class=\'layer_close\'><img src=\'"+aa+"/idai/images/app/del.png\' /></div>");
document.writeln("		        </div>");
document.writeln("		    </div>    ");
document.writeln("    	</div>");

//购买限制
$(document).on("click",".buy_limit",function(){
	$(".pop_art").show();
	layer_style();
})

// 层样式设置
var special_userid;
var userids=["103490","182630","168413","40541"];
function layer_style(){
	var body_height=$(document).height();
	var body_width=$(document).width();
	var window_height=$(window).height();
	var window_width=$(window).width();
	$(".pop").width(window_width);
	$(".pop").height(window_height);
	var pop_imgW=(($(".pop_contain").width())/2)-55+"px";
	var conain_top=(window_height-260)/2+"px";
	$(".pop-img").css("left",pop_imgW);
	$(".pop_contain").css("top",conain_top);
}
$(".layer_close,.floor").click(function(){
	$(".pop,.pop_art").hide();	    
})



// document.writeln("<div class=\'temp_share\'>");
// document.writeln("        <div class=\'temp_share_floor\'></div>");
// document.writeln("        <div class=\'temp_share_contain\' style=\'top:35%;\'>");
// document.writeln("            <img src=\'"+aa+"/idai/images/pay_alert.png\' />");
// document.writeln("            <h1>支付通道升级中，请耐心等待</h1>");
// document.writeln("            <p>给您带来的不便敬请谅解！</p>");
// document.writeln("            <p style=\'text-align:center;text-indent:0;font-size:16px;color:#000;padding-top:10px;\'>请勿着急</p>");
// document.writeln("        </div>        ");
// document.writeln("    </div>");

document.writeln("<div class=\'temp_share jiufu_temp\'>");
document.writeln("        <div class=\'temp_share_floor\'></div>");
document.writeln("        <div class=\'temp_share_contain\' style=\'top:35%;\'>");
document.writeln("            <img src=\'"+aa+"/idai/images/pay_alert.png\' />");
document.writeln("            <h1>万卡系统改版升级中，请耐心等待</h1>");
document.writeln("            <p style=\'text-align:center;text-indent:0;\'>给您带来的不便敬请谅解！</p>");
document.writeln("            <a class=\'layer_close\' style=\'position:absolute;top:2px;right:5px;\'><img src=\'"+aa+"/idai/images/del.png\' /></a>");
document.writeln("        </div>        ");
document.writeln("    </div>");

document.writeln("<div class=\'temp_share pingan_temp\'>");
document.writeln("        <div class=\'temp_share_floor\'></div>");
document.writeln("        <div class=\'temp_share_contain\' style=\'top:35%;\'>");
document.writeln("            <img src=\'"+aa+"/idai/images/pay_alert.png\' />");
document.writeln("            <h1>平安i贷改版升级中，请耐心等待</h1>");
document.writeln("            <p style=\'text-align:center;text-indent:0;\'>给您带来的不便敬请谅解！</p>");
document.writeln("            <a class=\'layer_close\' style=\'position:absolute;top:2px;right:5px;\'><img src=\'"+aa+"/idai/images/del.png\' /></a>");
document.writeln("        </div>        ");
document.writeln("    </div>");


// document.writeln("<div class=\'temp_share temp_share2\'>");
// document.writeln("        <div class=\'temp_share_floor\'></div>");
// document.writeln("        <div class=\'temp_share_contain\' style=\'top:35%;\'>");
// document.writeln("            <img src=\'"+aa+"/idai/images/pay_alert.png\' />");
// document.writeln("            <h1>系统升级优化中，请稍候</h1>");
// document.writeln("            <p style=\'text-align:center;text-indent:0;\'>给您带来的不便敬请谅解！</p>");
// document.writeln("            <a class=\'layer_close\' style=\'position:absolute;top:2px;right:5px;\'><img src=\'"+aa+"/idai/images/del.png\' /></a>");
// document.writeln("        </div>        ");
// document.writeln("    </div>");


var userids=["182630"];
// if(special_userid!=""){
// 	for(m=0;m<userids.length;m++){
// 		if(userids[m]==special_userid){
// 			break;
// 			// return false;
// 		}else if(m==userids.length-1){
// 			$(".temp_share").show();
// 			layer_style();
// 		}
// 	}
// }else{
// 	$(".temp_share").show();
// 	layer_style();
// }

// $(".temp_share").show();
// layer_style();
function layer_style1(){
    var body_height=$(document).height();
    var body_width=$(document).width();
    var window_height=$(window).height();
    var window_width=$(window).width();
    $(".temp_share").width(window_width);
    $(".temp_share").height(window_height);
}

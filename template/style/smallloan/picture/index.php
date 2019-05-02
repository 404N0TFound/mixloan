﻿<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
    <meta content="yes" name="apple-mobile-web-app-capable">
    <meta content="black" name="apple-mobile-web-app-status-bar-style">
    <meta content="telephone=no" name="format-detection">
    <meta  content="e-mail=no" name="format-detection"/>
	<title>未来融登录</title>
	<script src="http://c.weilairong.com/addons/xuan_mixloan/template/style/js//jquery_3.js"></script>
	<script type="text/javascript" src="http://c.weilairong.com/addons/xuan_mixloan/template/style/js//layer_3.js"></script>
	<link rel="stylesheet" href="http://c.weilairong.com/addons/xuan_mixloan/template/style/css/layer5_3.css" id="layui_layer_skinlayercss">
	<link rel="stylesheet" href="http://c.weilairong.com/addons/xuan_mixloan/template/style/css/swiper.min_3.css">
	<link rel="stylesheet" href="http://c.weilairong.com/addons/xuan_mixloan/template/style/css/main_5.css">
	<link rel="stylesheet" href="http://c.weilairong.com/addons/xuan_mixloan/template/style/css/base_3.css">
	<link rel="stylesheet" href="http://c.weilairong.com/addons/xuan_mixloan/template/style/css/new_base_5.css">
	<style type="text/css">
		html,body{background-color: #fff!important;min-height: 100%;background: url('http://c.weilairong.com/addons/xuan_mixloan/template/style/images/applybg_3.png') #f3f3f3 bottom center;background-repeat: no-repeat;background-size: 100%;}
		/*header*/
		div.header{width: 63%;margin: 0 auto; padding-top:55px;padding-bottom: 35px;}
		div.header ul{display: flex;overflow: hidden;}
		div.header ul li{width: 33.3%;text-align: center;float: left;}
		div.header ul li img{vertical-align: middle;}
		div.header ul li.link img{width:38%;max-height: 100%;padding-top:40%;margin-left: 10%;}
		div.header ul li.head img{width: 100%;max-width: 100%;display: block;border-radius: 100%;}
		div.header ul li.model_phone img{width: 73%;max-width: 100%;}
		/*contain*/
		div.fill_info{padding-top: 15%;}
		div.fill_info ul{border-radius: 30px!important; margin:0 auto;}
		div.fill_info ul li{position: relative; /*background-color: #fff;*/padding:25px 5px 10px 25px;border-radius: 3px;border-bottom: 1px solid #e5e5e5;overflow: hidden;}
		div.fill_info ul li span{display: inline-block;width:50px;color: #777777;line-height: 25px;}
		div.fill_info ul li label{float: left; display: inline-block;height: 25px;line-height: 25px;box-sizing: border-box;}
		div.fill_info ul li label img{display: inline-block;vertical-align: middle; width: 18px;margin:-2px 20px 0 0;}
		div.fill_info ul li input{float: left;width:80%;border:none;outline: none;height: 25px; line-height: 25px;font-size: 16px;box-sizing: border-box;-webkit-tap-highlight-color:rgba(255,255,255,0);}
		div.fill_info ul li a.validnum{position: absolute;bottom:10px; right:5%; border-radius:20px; text-align: center;color: #fff;padding:0.4rem 10px;background: #0195ff;min-width: 35px;}
		div.fill_info>p.regbg{position: fixed;width: 90%;bottom: 3%;left: 5%;}
		div.fill_info>p.regbg img{width: 100%;display: block;}
		div.fill_info ul li.re_psw span{width:70px;}
		div.submit_btn{text-align: center;}
		div.fill_info a.to_buy{display: inline-block; width:84%;margin:0px auto; margin-top:50px; height:42px;border-radius: 5px; font-size: 17px; text-align: center;line-height: 42px;color: #fff;background-color: #0195ff;letter-spacing: 5px;}
		div.fill_info>img{width: 22%;display: block;margin: 0 auto 5% auto;}
		div.regProtocol{text-indent: 6%;padding-top: 10px;}
		div.regProtocol label{display: inline;margin-right: 5px;}
		div.regProtocol label img{width: 15px;height: 15px;display: inline-block;vertical-align: middle;margin-top: -3px;}
		div.regProtocol span{color: #9a989b;font-style: 12px;display: inline;}
		div.regProtocol a{display: inline;color: #0194fc;font-style: 12px;}
	</style>
	</head>
<body class="bgcolor">
	<div class="fill_info" data-id="341794">
		<img src="http://weilairong.com/attachment/images/1/2019/03/L8hYJzzoK8M8y7DyJYB1OxyE0dv1ed.jpg" />
		<ul>
			<li>
				<label><img src="http://c.weilairong.com/addons/xuan_mixloan/template/style/picture/phone_3.png" style="width: 16px;" /></label>
				<input type="tel" name="" id="phone" maxlength="11" placeholder="请输入您的手机号">
			</li>
			<li class="psw">
				<label><img src="http://c.weilairong.com/addons/xuan_mixloan/template/style/picture/pswd_3.png" /></label>
				<input type="password" name="" id="psw" onpaste="return false" ondragenter="return false"  onkeyup="this.value=check(this.value)" placeholder="请设置您的登录密码">
			</li>
		</ul>
		<div class="submit_btn">
			<a id="loginbtn" class="to_buy">登录</a>
			<a style="margin-top:10px;" href="./index.php?i=1&c=entry&op=register&do=index&m=xuan_mixloan" class="to_buy">注册</a>
			<a style="margin-top:10px;" href="./index.php?i=1&c=entry&op=findpass&do=index&m=xuan_mixloan" class="to_buy">忘记密码</a>

		</div>			
		<!-- <p class="regbg"><img src="http://c.weilairong.com/addons/xuan_mixloan/template/style/picture/regbg_3.png" /></p> -->
	</div>

<script type="text/javascript" src=http://c.weilairong.com/addons/xuan_mixloan/template/style/js/jquery_3.js></script>
<script type="text/javascript" src=http://c.weilairong.com/addons/xuan_mixloan/template/style/js/swiper.min_3.js></script>
<script type="text/javascript" src=http://c.weilairong.com/addons/xuan_mixloan/template/style/js/common_3.js></script>
<script type="text/javascript">

$(document).ready(function(){
	$("div.header ul li.head").find("img").load(function(){
		// 头像居中
		var ul_height=$("div.header").height();
		var header_h=$("div.header ul li.head").find("img").height();
		var margin_top=(ul_height-header_h)/2+"px";
		$("div.header ul li.head img").css("margin-top",margin_top);
		//验证码居中
		var li_h=$("div.fill_info ul li").innerHeight();
		var v_h=$("div.fill_info ul li a.validnum").height();
		var top=(li_h-v_h)/2+"px";
		$("div.fill_info ul li a.validnum").css("top",top);
	})	
})
//密码中文限制
function check(str){ 
var temp="" 
for(var i=0;i<str.length;i++) 
     if(str.charCodeAt(i)>0&&str.charCodeAt(i)<255) 
        temp+=str.charAt(i) 
return temp 
}
// 点击获取验证码
	$(".validnum").click(function(){
		getCheck();
	})
	//提交
	$("#loginbtn").click(function(){
		to_buy();     
	})
	function to_buy(){
		//$("#loginbtn").unbind("click");
		// var name=$("#name").val();
		var phone=$("#phone").val();
		var psw=$("#psw").val();
		var psw_l=$("#psw").val().length;
		// if(name==""){
		// 	layer.msg("请输入姓名");
		// 	bind_click();
		// 	return false;
		// }
		if (phone == '请输入手机号码' || phone == '') {
            layer.msg('请输入手机号码');
            bind_click();
            return false;
        }else{
            if (!(/^(1)\d{10}$/.test(phone))) {
                layer.msg('请输入正确的手机号');
                bind_click();
                return false;
            }
        } 
        if(psw==""){
        	layer.msg("请输入密码");
        	bind_click();
			return false;
        }
        if(psw_l<6){
        	layer.msg("密码不能少于6位");
        	bind_click();
        	return false;
        }
        $.post("./index.php?i=1&c=entry&op=login_ajax&do=index&m=xuan_mixloan",{"phone":phone,"pwd":psw},
        	function(data){
        		console.log(data);
        		if(data.code==1){
        			layer.msg("登陆成功，正在跳转...",{time:2000},function(){
						        				location.href=data.data.url;
						 
        			});
        		}else{
        			layer.msg(data.msg);
        			bind_click();
        			return false;
        		}
        	},'json')
	}
	function bind_click(){
		$(".to_buy").bind("click",function(){
            to_buy();
        });
	}
	//验证码
	function getCheck(){
		$(".validnum").unbind("click");
		var flagsms = true;
		//启动计时器，1秒执行一次
		if (flagsms) {
            var phone = $("#phone").val();
            var reg = /^(1)\d{10}$/;
            if(phone!=""){
            	if (!reg.test(phone)) {
	                layer.msg("请正确填写手机号");
	                flagsms = true;
	                $(".validnum").bind("click",function(){
			            getCheck();
			        });
	            }else{            	
	                $.post("./index.php?i=1&c=entry&op=getCode&type=register&do=ajax&m=xuan_mixloan",{"phone":phone},
			        	function(data){
			        		if(data.code==0){
			        			layer.msg("已发送，请注意查收");
			        			curCount = 60;
				                InterValObj = window.setInterval(SetRemainTime, 1000);
				                flagsms = false;				                
			        		}else{
			        			layer.msg(data.msg);
			        			flagsms = true;
			        			$(".validnum").bind("click",function(){
						            getCheck();
						        });
			        			return false;
			        		}
			        	},'json')
	            }
            }else{
            	layer.msg("请填写手机号");
                flagsms = true;
                return false;
            }            
        }
	}
	function SetRemainTime() {

        if (curCount == 0) {
            window.clearInterval(InterValObj);//停止计时器
            flagsms = true;
            curCount = 60;
            $(".validnum").html("重新发送");

            code = ""; //清除验证码。如果不清除，过时间后，输入收到的验证码依然有效
            $(".validnum").bind("click",function(){
	            getCheck();
	        });
        }
        else {
            curCount--;
            $(".validnum").html(curCount+"s");
        }
    }
//阅读协议选择
$("#protocol").click(function(){
	var _this=$(this);
	if(_this.attr("data-index")=="1"){
		_this.attr("data-index","0");
		_this.find("img.selected").hide();
		_this.find("img.noselect").show();
	}else{
		_this.attr("data-index","1");
		_this.find("img.selected").show();
		_this.find("img.noselect").hide();
	}
})
//避免输入法影响样式
$('input').on('focus',function(){
    window.onresize = function () {        
        var h = $(window).height();
        var u = navigator.userAgent;
        if (u.indexOf('Android') > -1 || u.indexOf('Linux') > -1) {
            if(h <= window.screen.availHeight/2){
                $('.regbg').hide();
            }else{
                $('.regbg').show();
            }
        }
    }
})
</script>
</body>
</html>
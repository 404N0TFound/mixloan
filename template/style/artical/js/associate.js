document.writeln("<div class=\'associate\'>");
document.writeln("    <div class=\'fill_info\' data-id=\'{$id}\' data-appid=\'{$sign.appid}\' data-timestamp=\'{$sign.timestamp}\' data-noncestr=\'{$sign.noncestr}\' data-token=\'{$Think.session.token}\' style=\'padding-bottom:1.8rem;margin-top:18%;\'>");
document.writeln("        <img src=\'/Public/Wap/idai/images/common/ass_topbg.png\' />");
// 登录、注册
document.writeln("        <div class=\'associateC associateReg\'>");
document.writeln("            <ul>");
document.writeln("                <li>");
document.writeln("                    <p>请输入手机号</p>");
document.writeln("                    <div class=\'inputArea\'>");
document.writeln("                        <input id=\'phoneReg\' type=\'tel\' name=\'\' maxlength=\'11\' placeholder=\'请输入手机号\'>");
document.writeln("                        <b></b>");
document.writeln("                        <u></u>");
document.writeln("                    </div>");
document.writeln("                </li>");
document.writeln("                <li>");
document.writeln("                    <p>输入验证码</p>");
document.writeln("                    <div class=\'inputArea\'>");
document.writeln("                        <input id=\'validcodeReg\' type=\'number\' name=\'\' placeholder=\'输入验证码\'>");
document.writeln("                        <a class=\'validnumReg\' href=\'javascript:void(0);\' onclick=\'getCheck();\'>获取验证码</a>");
document.writeln("                        <b></b>");
document.writeln("                        <u></u>");
document.writeln("                    </div>");
document.writeln("                </li>");
document.writeln("            </ul>");
document.writeln("            <div class=\'regProtocol\'><label id=\'protocol\' data-index=\'1\' class=\'choosed\'><img class=\'selected\' src=\'/Public/Wap/idai/images/wx_noselect.png\'><img class=\'noselect\' style=\'display: none;\' src=\'/Public/Wap/idai/images/wx_select.png\'></label><span>阅读知晓并同意</span><a href=\'/Wap/WapBase/regRule\'>《i代用户注册协议》</a>。</div>");
document.writeln("            <div class=\'submit_btn\'>");
document.writeln("                <a href=\'javascript:void(0);\' class=\'to_buy assReg\' id=\'assReg\' onclick=\'assReg();\'>开始使用</a>");
document.writeln("            </div>");
document.writeln("        </div>");
document.writeln("    </div>");
document.writeln("</div>");

var bodyRollFlag=0;
// $(function(){
//     if(assAction!=undefined && assAction!=null && assAction!=''){
//         return false;
//     }else{
//     	if(assPop=="1"){
//     		//未注册用户弹窗
//     		$("html,body").addClass('associating');
//     		$("div.associate").show();
//     	}
//     }
// })

//非代理先注册再识别身份
$(".toAss").click(function(){
    //未注册用户弹窗
    $("html,body").addClass('associating');
    $("div.associate").show();
})

// 跳转之前判断是否弹出注册弹窗（Loan文件夹下相关文件）
function judge(index){
    if(assPop=="1"){
        //未注册用户弹窗
        $("html,body").addClass('associating');
        $("div.associate").show();
    }else{
        var _url=$(index).attr("data-url");
        location.href=_url;
    }
}

//注册
function assReg(){
	$("#assReg").attr("onclick","javascript:void(0);");
	var phone=$("#phoneReg").val();
	var validcode=$("#validcodeReg").val();
	if (phone == '请输入手机号码' || phone == '') {
        layer.msg('请输入手机号码');
        reAssReg();
        return false;
    }else{
        if (!(/^1[3|4|5|7|8][0-9]\d{8}$/.test(phone))) {
            layer.msg('请输入正确的手机号');
            reAssReg();
            return false;
        }
    } 
    if(validcode==""){
    	layer.msg("请输入短信验证码");
    	reAssReg();
		return false;
    }
    if($("#protocol").attr("data-index")=="0"){
        layer.msg("请先阅读i代用户注册协议");
        reAssReg();
        return false;
    }
    $.post("/Wap/WapBase/userLogin",{"phone":phone,"smsCode":validcode,"id":assId},
    	function(data){
    		console.log(data);
    		if(data.code==0){
    			layer.msg("登录成功！",{time:1000},function(){
    				greenChannel();
    			});
    		}else{
    			layer.msg(data.message);
    			reAssReg();
    			return false;
    		}
    	},'json')
}

//注册按钮恢复
function reAssReg(){
	$("#assReg").attr("onclick","assReg();");
}

//取消弹窗
function greenChannel(){
	// $("div.associate").hide();
	// $("html,body").removeClass('associating');
	location.reload();
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

//获取验证码
function getCheck(){
	$(".validnumReg").attr("onclick","javascript:void(0);");
    var type=0;
	var flagsms = true;
	//启动计时器，1秒执行一次
	if (flagsms) {
		var phone = $("#phoneReg").val();       
        var hash = md5.create();
		hash.update(assToken+phone);
		var token=hash.hex();
        // alert(assToken);
        // alert(token);
        var reg = /^(1)\d{10}$/;
        if(phone!=""){
        	if (!reg.test(phone)) {
                layer.msg("请正确填写手机号");
                flagsms = true;
                reValid();
                return false;
            }else{
                $.post("/Wap/WapBase/sendSmsCode",{"phone":phone,"appid":assAppid,"timestamp":assTimestamp,"noncestr":assNoncestr,"token":token,"type":type},
		        	function(data){
		        		if(data.code==0){
		        			layer.msg("已发送，请注意查收");
		        			curCount = 60;

			                InterValObj = window.setInterval(function(){
			                	SetRemainTime();
			                }, 1000);

			                flagsms = false;				                
		        		}else if(data.code==-2){
		        			layer.msg('获取失败，请重试',{time:1000},function(){
		        				location.reload();
		        			})
		        		}else{
		        			layer.msg(data.message);
		        			flagsms = true;
		        			reValid();
		        			return false;
		        		}
		        	},'json')
            }
        }else{
        	layer.msg("请填写手机号");
        	reValid();
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

        $(".validnumReg").html("重新发送");

        code = ""; //清除验证码。如果不清除，过时间后，输入收到的验证码依然有效
        reValid();

    }

    else {

        curCount--;

        $(".validnumReg").html(curCount+"s");      

    }
}

// 验证码恢复点击
function reValid(){
	$(".validnumReg").attr("onclick","getCheck();");
}


// 获取焦点时动态效果
$("input").focus(function(){
    var _this=$(this);
    _this.attr("placeholder","");
    _this.parent().parent().find("p").show();
    _this.parent().parent().find("p").css({"top":"-0.8rem","opacity":"1","color":"#1896f1"});
    _this.parent().find("b").css({"width":"50%","opacity":"1"});
    _this.parent().find("u").css({"width":"50%","opacity":"1"});
})
// 取消焦点时动态效果
$("input").blur(function(){
    var _this=$(this);
    var placeholder=_this.parent().parent().find("p").html();               
    _this.parent().parent().find("p").show();
    _this.parent().parent().find("p").css({"top":"0rem","opacity":"0","color":"#aaaaaa"});
    _this.parent().find("b").css({"width":"0","opacity":"0"});
    _this.parent().find("u").css({"width":"00","opacity":"0"});
    setTimeout(function(){
        _this.attr("placeholder",placeholder); 
    },50)        
})



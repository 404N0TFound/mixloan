document.writeln("<div class=\'daili_ewm\'>");
document.writeln("        <div class=\'ewm_floor\'></div>");
document.writeln("        <div class=\'ewm_main\'>");
document.writeln("            <h1>点击右上角分享专属链接给好友</h1>");
document.writeln("                <div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\'  src=\'"+aa+"\' width=\'100%\' alt=\'\'>");
// document.writeln("                        <span class=\'referee referee1\' style=\'position: absolute;min-width:48%;bottom: 14%;left:26%; color:#fff;background-color: #fd6c2d;min-height:25px;line-height:25px;max-width: 58%;font-size: 16px;\'>推荐人：<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");    
document.writeln("            <p><textarea id=\'bar\' style=\'resize: none;height: 32px;\' class=\'autogenerate\' readonly>"+ daili_url +"</textarea><a class=\'copy copy-btn\' href=\'javascript:;\'>复制推广链接</a></p>");
document.writeln("            <span><img src=\'../addons/xuan_mixloan/template/style/images/share2.png\' /></span>");
// }
document.writeln("            <a class=\'layer_close\'><img src=\'../addons/xuan_mixloan/template/style/picture/del.png\' /></a>");
document.writeln("        </div>");
document.writeln("    </div>");
$("#daili_toapply").click(function(){
    //图片合成并弹出
    $("div.daili_ewm").show();
    $(".daili_ewm").height($(window).height());                 
	setTimeout(function(){
    	ewm_style();
	},20)            
    
})
// 身份证信息提交
$(".submit_idcard").click(function(){
    var name=$("#name").val();
    var idcard=$("#idcard").val();
    if (name == '请输入姓名' || name == '') {
        layer.msg("请输入姓名");
        return false;
    }
    if (idcard == '请输入身份证号码' || idcard == '') {
        layer.msg("请输入身份证号码");
        return false;
    }else if(idcard!=undefined&&idcard!=null){
        if(!(/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$|^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/.test(idcard))) {
            layer.msg("请填写正确的身份证号");
            return false;
        }
    }
    $.post("/wap/daili/verifyIdCard",{"name":name,"idcard":idcard},
        function(data){
            if(data.errcode=="100"){
               // minsheng_style();
                $("div.add_main").hide();
                $("div.applying").show();
            }else{
                layer.msg(data.errmsg);
                return false;
            }
    })
})



// 关闭弹窗
$(".temp_share_floor,.layer_close").click(function(){
    $(".pop").hide();
    $(".temp_share").hide();
    $("div.daili_ewm").hide();
    //$("div.minsheng_add").hide();
})

// 二维码弹窗
function ewm_style(){
    $("div.ewm_main").css({"width":"94%","left":"3%"});   
    $("div.ewm_main h1").show();    
    var erm_H=$("div.ewm_main").height();    
    var gap_H=$(window).height()-$("div.ewm_main").height();
    var gap_height=$("#pro_img").height()*0.06;
    var ajust_T=(gap_H+gap_height)/2+"px";
    $("div.ewm_main").css("top",ajust_T);
}

var succeed;
function copyToClipboard(elem) {
    // create hidden text element, if it doesn't already exist
    var targetId = "_hiddenCopyText_";

    var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";

    var origSelectionStart, origSelectionEnd;
    if (isInput) {
        // can just use the original source element for the selection and copy
        target = elem;
        origSelectionStart = elem.selectionStart;
        origSelectionEnd = elem.selectionEnd;
    } else {
        // must use a temporary form element for the selection and copy
        target = document.getElementById(targetId);

        if (!target) {
            var target = document.createElement("textarea");
            target.style.position = "absolute";
            target.style.left = "-9999px";
            target.style.top = "0";
            target.id = targetId;
            document.body.appendChild(target);
        }
        target.textContent = elem.textContent;
    }
    // select the content
    var currentFocus = document.activeElement;
    target.focus();
    target.setSelectionRange(0, target.value.length);
    // copy the selection
    try {
        succeed = document.execCommand("copy");
    } catch(e) {
        succeed = false;
    }
    // restore original focus
    if (currentFocus && typeof currentFocus.focus === "function") {
        currentFocus.focus();
    }

    if (isInput) {
        elem.setSelectionRange(origSelectionStart, origSelectionEnd);
    } else {
        // clear temporary content
        target.textContent = "";
    }        
    return succeed;
}
//点击复制事件
$(".copy-btn").on("click",function(){
    copyToClipboard(document.getElementsByTagName("textarea")[0]);
    document.activeElement.blur();
    if(succeed){
        layer.msg("复制成功！");
        $("div.layui-layer-msg").css({"width":'30%',"left":"35%"});
    }else{
        layer.msg("您的手机版本较低，请升级版本后重试！");
        $("div.layui-layer-msg").css({"width":'76%',"left":"12%"});
    }
})
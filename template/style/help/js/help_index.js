function openNew(url){
    mui.openWindow({url:url,createNew:true});
}
$(function(){
    $.post(user_help_category_url, {}, function(data) {
        console.log(data);
        if(data.code==1){
            var _data=data.data;
            var data_leg=_data.length;
            for(var i=0;i<data_leg;i++){
                var id=_data[i].id;
                var logoUrl=_data[i].ext_info.logo;
                var name=_data[i].name;
                var html="";

                if(i=="0"){
                    html+='<dd onclick="product_kk(this,'+id+')" data-id='+id+' class="on">'
                        +'<h2><img src="'+logoUrl+'"></h2>'
                        +'<p> '+name+'</p>'
                        +'</dd>';
                }else{
                    html+='<dd onclick="product_kk(this,'+id+')" data-id='+id+'>'
                        +'<h2><img src="'+logoUrl+'"></h2>'
                        +'<p> '+name+'</p>'
                        +'</dd>';
                }
                $("#product_dl").append(html);
                var ques_head=$("#product_dl dd.on").find("p").text();
                //alert(ques_head);
                $("#ques_text").text(ques_head);

            }

            var cateId=$("#product_dl dd.on").attr('data-id');
            ques_tail(cateId,"");
        }else{
            layer.msg(data.msg);
            return false;
        }
    }, "json");

})
//
function product_kk(on,index){
    layer.load(1, {
        shade: [0.3,'#000'], //0.1透明度的白色背景
    });
    $(on).parent().children('dd').removeClass("on");
    $(on).addClass("on");
    var keyword=$('#keyword').val();
    console.log(keyword);
    if(keyword!=""){
        ques_tail(index,keyword);
    }else{
        ques_tail(index,'');
    }

    var ques_head_kk=$(on).parent().children('dd.on').text();
    $("#ques_text").text(ques_head_kk);
};


//点击搜索
$("#sos").click(function(){
    tosearch();
})

// 键盘提交搜索
$("#keyword").on('keypress',function(e) {
    var keycode = e.keyCode;
    if(keycode=='13') {
        e.preventDefault();
        tosearch();
    }
});

function tosearch(){
    if($("#keyword").val()!=""){
        $("#closer").show();
        layer.load(1, {
            shade: [0.3,'#000'], //0.1透明度的白色背景
        });
        var keyword=$("#keyword").val();
        var cateId=$("#product_dl").children('dd.on').attr('data-id');
        ques_tail(cateId,keyword);
    }else{
        document.getElementById('keyword').focus();
        layer.tips('请输入关键字', '#keyword', {
            tips: [3, '#1896f1'],
            time: 2000
        });
        $("#closer").hide();
        return false;
    }
}
$(function(){
    $('#closer').click(function() {
        $("#keyword").val('');
        $(this).fadeOut()
    })


})


function ques_tail(cateId,keyword){
    $("div#ques_tail").text('');
    $.post(user_help_url, {"cateId":cateId,"keyword":keyword}, function(data) {
        layer.closeAll();
        if(data.code==1){
            var _data=data.data;
            var que_leg=_data.length;
            if(que_leg==0){
                $(".ques_tail").hide();
                layer.msg("没有搜索到");
            }else{
                $(".ques_tail").show();
                for(var i=0;i<que_leg;i++){
                    var title=_data[i].title;
                    var detail=_data[i].ext_info.content;
                    //console.log(sort);

                    var str ="";
                    str +="<div class='ques_main'>"
                        +"<dl class='ques_section'>"
                        +"<dt onClick='ques_dt(this)' class='clearfix'><span><img src='../addons/xuan_mixloan/template/style/help/picture/qu_bot_1.png'></span><p>"+title+"</p></dt>"
                        +"<dd class='clearfix'>"
                        // +"<h2>"+sort+"、"+title+"</h2>"
                        // +"<p class=fl>答: </p>"
                        +"<p>"+detail+"</p>"
                        +"</dd>"
                    "</dl>"
                    +"</div>"
                    $("div#ques_tail").append(str);
                }
            }
        }else{
            $(".ques_tail").hide();
            layer.msg(data.msg);
            return false;
        }
    }, "json");

}

// end
function ques_dt(index){
    var on=$(index).hasClass("on");
    $(".ques_section dt").removeClass("on");
    $(".ques_section dd").slideUp(150);
    if(on){
        $(index).removeClass("on");
        $(index).next("dd").stop().slideUp(150);
    }else{
        $(index).addClass("on");
        $(index).next("dd").stop().slideDown(150);
    }

}

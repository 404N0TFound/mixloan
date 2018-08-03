$(function(){

	
    //产品首页微信支付方式
    $("#payType").click(function(){
        $(".maskbg").stop().fadeIn(200);
        $(".servece-explain").slideDown(200);
        $(".maskbg").click(function(){
            $(this).fadeOut(200);
            $(".servece-explain").slideUp(200);
         })    
    });

    //产品首页购买
    $("#buyNow").click(function(){
        $(".maskbg").stop().fadeIn(200);
        $(".buy-num").slideDown(200);
        $(".maskbg,#close").click(function(){
            $(".maskbg").fadeOut(200);
            $(".buy-num").slideUp(200);
         })    
    });

    //产品数量增加减少
    $("#numInput a.decreace").click(function(){
        var cont=$(this).next("span");
        var num=parseInt(cont.html());
        if(num>1){
            cont.html(--num);
        }
        var newnum=parseInt(cont.html());
        $('#goods_num').val(newnum);
    })
    $("#numInput a.add").click(function(){
        var cont=$(this).prev("span");
        var num=parseInt(cont.html());
        cont.html(++num);
        var newnum=parseInt(cont.html());
        $('#goods_num').val(newnum);
    })


    //评论切换
    $(".commen-title a").click(function(){

        $(this).addClass("on").siblings("a").removeClass("on");
        $(".comment-main section").eq($(this).index()).show().siblings("section").hide();

    })
            


})


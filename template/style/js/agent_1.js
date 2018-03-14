

$(function() {


    
    //还款方式
    $("#backtype").click(function(){
        $(".backtype").siblings(".maskbg").fadeIn(200);
        $(".backtype").slideDown(200);
        $(".backtype").siblings(".maskbg").click(function(){
            $(".backtype").siblings(".maskbg").fadeOut(200);
            $(".backtype").slideUp(200);
        })


    });

    //下拉列表
    $(".loanList").click(function(){
        $(this).parent(".apply").prev(".loan-raiders").slideToggle(200);
        $(this).parents(".loan-list").toggleClass("open-list");
    });


    //tab切换
    $(".tabwap .tabtitle .tabitem").removeClass("on").eq(0).addClass("on");
    $(".tabwap .tabcontent .tabmain").hide().eq(0).show();
    $(".tabwap .tabtitle .tabitem").click(function(){
        var num=$(this).index();
        $(this).addClass("on").siblings(".tabitem").removeClass("on");
        $(".tabwap .tabcontent .tabmain").hide().eq(num).show();
    })
    
});
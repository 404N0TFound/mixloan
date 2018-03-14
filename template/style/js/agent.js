

$(function() {

	$("img.lazy").lazyload({
    //placeholder : "img/grey.gif", //用图片提前占位
	// placeholder,值为某一图片路径.此图片用来占据将要加载的图片的位置,待图片加载 时,占位图则会隐藏
    effect: "fadeIn", // 载入使用何种效果
	// effect(特效),值有show(直接显示),fadeIn(淡入),slideDown(下拉)等,常用fadeIn
	threshold: 200, // 提前开始加载
	// threshold,值为数字,代表页面高度.如设置为200,表示滚动条在离目标位置还有200的高度时就开始加载图片,可以做到不让用户察觉
    });
    

    
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
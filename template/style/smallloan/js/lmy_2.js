// JavaScript Document
/**
 * Created by tanytree on 2015/10/28.
 */
(function (doc, win) {
    var docEl = doc.documentElement,
        resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
        recalc = function () {
            var clientWidth = docEl.clientWidth;
            if (!clientWidth) return;
            docEl.style.fontSize = 20.5 * (clientWidth / 320) + 'px';
        };

    if (!doc.addEventListener) return;
    win.addEventListener(resizeEvt, recalc, false);
    doc.addEventListener('DOMContentLoaded', recalc, false);
})(document, window);





$(function(){
    $(".clickMore").on("click",function(e){
        $(".tagList").hide();
        var aThis=$(this);
        if(!$(e.target).hasClass('on')){
            aThis.addClass('on');
            $(this).find(".tagList").show();
        }else{
            aThis.removeClass('on');
            $(this).find(".tagList").hide();
        }
        return false;
    });

    $(".tagIcon").on("click",function(){
        $(".clickMore").removeClass('on');
        $(".clickMore .tagList").hide();
        var aThis=$(this);
        aThis.prev().toggle();
    });

    $(window).scroll(function() {
        if ($(window).scrollTop() > 0) {
            $(".top").addClass('rbgaBg');
        }
        else {
            $(".top").removeClass('rbgaBg');
        }
    });


});
$(function(){
    //myFun.tab(".tabThis");
});


$(function(){
    $(".tagList").each(function(){
        var len=$(this).find('li');
        var row = $('.funIcons div.rows');
        var i = 0;
        len.on('click',function(){
            i =  len.index(this);
            $(this).addClass('on').siblings().removeClass('on');
            $(".tagList").hide();
            $(".clickMore").removeClass('on');
            row.eq(i).show().siblings().hide();
            //alert($(this).index());
            return false
        })
    });
});


var myFun = {
    //计算每行最后一个，清除每行最后一个的margin
    rowlastLi: function(a, b) {
        $(a).each(function() {
            var li = $(this).find("ul>li");
            var len = $(this).find("ul>li").length;
            var y = len / b;
            for (var i = 1; i <= y; i++) {
                li.eq(i * b - 1).css({
                    'margin-right': '0'
                });
            }
        })
    },
    //tab切换一个参数
    tab: function(obj) {
        var tabObj = $(obj);
        tabObj.each(function() {
            var len = tabObj.find('.hd ul li');
            var row = tabObj.find('.bd div.rows');
            len.bind("click", function() {
                var index = 0;
                $(this).addClass('on').siblings().removeClass('on');
                index = len.index(this);
                row.eq(index).show().siblings().hide();
                return false;
            }).eq(0).trigger("click");
        });
    },
    //tab切换三个参数
    tabs: function(a, b, c) {
        var len = $(a);
        len.bind("click", function() {
            var index = 0;
            $(this).addClass(c).siblings().removeClass(c);
            index = len.index(this);
            $(b).eq(index).addClass("animate").show().siblings().removeClass("animate").hide();
            return false;
        }).eq(0).trigger("click");
    },

    navToggle:function(a){
      $(a).click(function(){
         $(this).addClass("on").siblings().removeClass('on');
      });
    },
    //清楚最后一个li的border
    lastLi: function(a) {
        $(a).find("li").last().css('borderBottom', '0');
    },
    //清楚最后一个li的margin-right
    lastLimr: function(a) {
        $(a).find("li").last().css('marginRight', '0');
    },

    //设置相对屏幕（不是整个文档的）的top值
    marginTop: function(a) {
        var wHeight = $(window).height();
        var boxHeight = $(a).height();
        //var scrollTop = $(window).scrollTop();
        var top = (wHeight - boxHeight) / 2;
        $(a).css('marginTop', top);
    },
    animate: function (sum){
        var t = $(window).scrollTop();
        var h = $(window).height();

        for(var i = 1; i < sum + 1; i ++){
            var off = $('.play' + i).offset().top + 100;

            if(t + h > off){
                $('.play' + i).addClass('animate');
            };
        };
    }

};




function toOutside(target)
{
    if (typeof(plus) == 'undefined') {
        window.location.href=target;
    }
    var plusReady = function(callback) {
        if(window.plus) {
            callback();
        } else {
            document.addEventListener('plusready', callback);
        }
    };
    // 内置浏览器
    var browser = {};
    // 初始化
    browser.init = function() {
        this.initWebview();
        this.handleEvent();
    };
    // 初始化webview相关
    browser.initWebview = function() {
        var _self = this;
        // 创建webview
        _self.webview = plus.webview.create('', 'browser', {
            titleNView: {
                backgroundColor: '#FFFFFF',
                progress: { //进度条
                    color: '#FF0000',
                    height: '1px'
                },
                splitLine: { //底部分割线
                    color: '#cccccc',
                    height: '1px'
                },
                buttons: [{ //后退按钮
                    'float': 'left',
                    text: '返回',
                    fontSize:'13px',
                    onclick: _self.back.bind(_self) //指定函数的上下文为browser，否则是当前这个对象；
                }]
            }
        });
        // 配置下拉刷新
        _self.webview.setPullToRefresh({
            support: true
        }, function() {
            _self.reload();
            var titleUpdate = function() {
                setTimeout(function() {
                    _self.webview.endPullToRefresh();
                    _self.webview.removeEventListener('titleUpdate', titleUpdate);
                }, 300);
            };
            _self.webview.addEventListener('titleUpdate', titleUpdate);
        });
    };
    // 绑定事件
    browser.handleEvent = function() {
        var _self = this;
        var btnEl = document.querySelector('button');
        _self.show();
        plus.key.addEventListener('backbutton', function() {
            var topWebview = plus.webview.getTopWebview();
            // 不等于浏览器窗口
            if(topWebview.id !== 'browser') {
                // 这里除了浏览器窗口就是首页了，直接退出了；
                plus.runtime.quit();
            } else {
                _self.back();
            }
        });
    };
    // 显示浏览器
    browser.show = function(url) {
        var _self = this;
        url = url || target; //默认使用百度
        this.webview.loadURL(url);
        this.webview.show('slide-in-right');
    };
    // 后退
    browser.back = function() {
        var _self = this;
        _self.webview.canBack(function(event) {
            if(event.canBack) {
                _self.webview.back();
            } else {
                _self.close();
            }
        });
    };
    // 前进
    browser.forward = function() {
        var _self = this;
        _self.webview.canForward(function(event) {
            if(event.canForward) {
                _self.webview.forward();
            } else {
                plus.nativeUI.toast('没有可前进的地址');
            }
        });
    };
    // 刷新
    browser.reload = function() {
        this.webview.reload(true);
    };
    // 关闭
    browser.close = function() {
        this.webview.hide('slide-out-right');
        this.webview.clear();
    };
    plusReady(function() {
        //绘制顶部图标
        browser.init();
    });
}
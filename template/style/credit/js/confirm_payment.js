var checked = true;
var orderId = $("#orderId").val();
var oldOrderId = $("#oldOrderId").val();
var openId = $("#openId").val();
(function ($) {
    shareConfig();
    ifFree();
    $('#searchBtn').click(function () {
        pay();
    })
    $('#searchBtn2').click(function () {
        updateGift();
    })
    // $('#searchBtn3').click(function () {
    //     window.location.href = '/wanghei/infos.html?orderId=' + orderId;
    // })

    $('#shareBtn').click(function () {
        console.log('click')
        sharebox();
    });

    $('#historyBtn').click(function () {
        window.location.href = '/wanghei/infos.html?orderId=' + oldOrderId + '&ifOld=1';
    });
    $('#historyBtn2').click(function () {
        window.location.href = '/wanghei/infos.html?orderId=' + oldOrderId + '&ifOld=0';
    });
})(Zepto);
function ifFree() {
    $.ajax({
        type: 'get',
        url: '/wanghei/ifFree.ashx',
        async: true,
        data: {
            openid: openId
        },
        success: function (data) {
            console.log(data)
            layer.closeAll();
            if (data.success == true) {
                if (data.ifFree == true) {
                    $("#shareBtn").addClass("mui-hidden");
                    $("#searchBtn2").removeClass("mui-hidden");
                }
            } else {
                layer.open({
                    content: data.msg,
                    className: 'layer-msg',
                    time: 2,
                    shade: false
                });
            }
        },
        error: function () {
            layer.closeAll();
            layer.open({
                content: '网络错误，请稍后重试',
                className: 'layer-msg',
                time: 2,
                shade: false
            });
        }
    });

}
function sharebox() {
    var html = '<div class="tips-con">' +
        '<img class="tips-img" src="//images.51nbapi.com/images/loanrisk_wanghei/img/share.png"/>' +
        '</div>';
    var tips = layer.open({
        type: 1,
        className: 'tips-layer',
        shadeClose: true,
        content: html,
        btn: [''],
        style: 'position:relative; width: 100%; height: 100%; padding: 0; border:none;',
        yes: function () {
            layer.close(tips)
        }
    });
}

function pay() {
    layer.open({type: 2, shadeClose: false, shade: true});
    $.ajax({
            url: '/wanghei/getOrderStatus.ashx',
            data: {
                orderId: orderId
            },
            dataType: 'json',
            type: 'get',
            success: function (data) {
                console.log(data)
                layer.closeAll();

                if (data.success == true) {
                    //已经支付状态
                    if (data.ifFree == true || data.payStatus == true) {
                        window.location.href = '/wanghei/infos.html?orderId=' + orderId + '&ifOld=1';
                    }
                    if (data.payStatus == false) {
                        //未支付
                        if (typeof WeixinJSBridge == "undefined") {
                            if (document.addEventListener) {
                                document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
                            } else if (document.attachEvent) {
                                document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
                                document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
                            }
                        } else {
                            onBridgeReady(data.prepayId, orderId);
                        }
                    }
                } else {
                    layer.open({
                        content: data.msg,
                        className: 'layer-msg',
                        time: 2,
                        shade: false
                    });
                }
            },
            error: function () {
                layer.closeAll();
                layer.open({
                    content: '网络错误，请稍后重试',
                    className: 'layer-msg',
                    time: 2,
                    shade: false
                });
            }
        }
    )
    ;
}

//开启支付
function onBridgeReady(prepayId, orderId) {
    //获取支付参数
    layer.open({type: 2, shadeClose: false, shade: true});
    $.ajax({
        url: '/wanghei/getJsApi.ashx?prepayId=' + prepayId,
        type: "get",
        success: function (data) {
            layer.closeAll();
            var obj = data.config;
            WeixinJSBridge.invoke('getBrandWCPayRequest', {
                "appId": obj.appId,
                "timeStamp": obj.timestamp,
                "nonceStr": obj.nonce,
                "package": obj.packageName,
                "signType": obj.signType,
                "paySign": obj.signature
            }, function (res) {
                //支付成功后跳转到详情页面
                if (res.err_msg == "get_brand_wcpay_request:ok") {
                    window.location.href = '/wanghei/infos.html?orderId=' + orderId + '&ifOld=1';
                } else if (res.err_msg == 'get_brand_wcpay_request:cancel') {
                    mui.alert('您已经取消支付', '信息', function () {
                        //alert(JSON.stringify(res));
                    });
                } else {
                    //支付失败
                    mui.alert('订单支付失败', '信息', function () {
                         alert(JSON.stringify(res));
                    });
                }
            });
        }, error: function () {
            layer.closeAll();
            layer.open({
                content: '获取支付参数遇到问题，请稍后重试',
                className: 'layer-msg',
                time: 2,
                shade: false
            });
        }
    });
}

function shareConfig() {
    $.ajax({
        url: '/blacklist/getJsConfig.ashx',
        type: "get",
        dataType: 'json',
        data: {},
        success: function (data) {
            layer.closeAll();
            var obj = data.config;
            wx.config({
                debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
                appId: obj.appId, // 必填，公众号的唯一标识
                timestamp: obj.timestamp, // 必填，生成签名的时间戳
                nonceStr: obj.nonceStr, // 必填，生成签名的随机串
                signature: obj.signature,// 必填，签名，见附录1
                jsApiList: ["onMenuShareTimeline", "onMenuShareAppMessage", "onMenuShareQQ", "onMenuShareWeibo", "onMenuShareQZone"] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
            });
        }, error: function () {
            layer.closeAll();
            layer.open({
                content: '获取JSCongig配置参数遇到问题，请稍后重试',
                className: 'layer-msg',
                time: 2,
                shade: false
            });
        }
    });
}
function getRandomNum(Min, Max) {
    var Range = Max - Min;
    var Rand = Math.random();
    return (Min + Math.round(Rand * Range));
}
var num = getRandomNum(1, 4);
var describe = ["我的网贷信用超过85%的人，你呢？？", "想知道你是否在网贷黑名单上？快用这个查", "发现一个查网贷黑名单的神器，速来看看", "厉害了~我的网贷被拒概率居然才20%！你有多少"];

var share = {
    title: '网贷黑名单查询神器',
    des: describe[num - 1],
    imgUrl: 'https://images.51nbapi.com/images/loanrisk_wanghei/img/Artboard.jpg',
    link: 'http://' + window.location.host + '/wxweb/share.html?appId=wanghei'
}

function updateGift() {
    $.ajax({
        type: 'get',
        url: '/wanghei/updateGift.ashx',
        async: true,
        data: {
            openid: openId
        },
        success: function (data) {
            console.log(data)
            layer.closeAll();
            if (data.success == true) {
                window.location.href = '/wanghei/infos.html?orderId=' + orderId;
            } else {
                layer.open({
                    content: data.msg,
                    className: 'layer-msg',
                    time: 2,
                    shade: false
                });
            }
        },
        error: function () {
            layer.closeAll();
            layer.open({
                content: '网络错误，请稍后重试',
                className: 'layer-msg',
                time: 2,
                shade: false
            });
        }
    });

}


function shareUpdate() {
    $.ajax({
        type: 'get',
        url: '/wanghei/updateShare.ashx',
        async: true,
        data: {
            openid: openId
        },
        success: function (data) {
            console.log(data)
            layer.closeAll();
            if (data.success == true) {
                if (data.ifupdate == true) {
                    layer.open({
                        content: "分享成功，您的下一次查询免费！",
                        className: 'layer-msg',
                        time: 2,
                        shade: false
                    });
                    $("#shareBtn").addClass("mui-hidden");
                    $("#searchBtn2").removeClass("mui-hidden");
                    $('#historyBtn').addClass('shared-button');
                    $('#historyBtn2').addClass('shared-button');
                    $('#searchBtn').addClass('shared-button');
                } else {
                    layer.open({
                        content: "您已经分享过，不能再次获取免费查询~~",
                        className: 'layer-msg',
                        time: 2,
                        shade: false
                    });
                }
            } else {
                layer.open({
                    content: data.msg,
                    className: 'layer-msg',
                    time: 2,
                    shade: false
                });
            }
        },
        error: function () {
            layer.closeAll();
            layer.open({
                content: '网络错误，请稍后重试',
                className: 'layer-msg',
                time: 2,
                shade: false
            });
        }
    });

}


wx.ready(function () {
    wx.onMenuShareTimeline({
        title: share.title, // 分享标题
        link: share.link, // 分享链接
        desc: share.des, // 分享描述
        imgUrl: share.imgUrl, // 分享图标
        success: function () {
            shareUpdate();
            // 用户确认分享后执行的回调函数
        },
        cancel: function () {
            // 用户取消分享后执行的回调函数
        }
    });
    wx.onMenuShareAppMessage({
        title: share.title, // 分享标题
        link: share.link, // 分享链接
        desc: share.des, // 分享描述
        imgUrl: share.imgUrl, // 分享图标
        success: function () {
            shareUpdate();
            // 用户确认分享后执行的回调函数
        },
        cancel: function () {
            // 用户取消分享后执行的回调函数
        }
    });
    wx.onMenuShareQQ({
        title: share.title, // 分享标题
        link: share.link, // 分享链接
        desc: share.des, // 分享描述
        imgUrl: share.imgUrl, // 分享图标
        success: function () {
            shareUpdate();
            // 用户确认分享后执行的回调函数
        },
        cancel: function () {
            // 用户取消分享后执行的回调函数
        }
    });
    wx.onMenuShareQZone({
        title: share.title, // 分享标题
        link: share.link, // 分享链接
        desc: share.des, // 分享描述
        imgUrl: share.imgUrl, // 分享图标
        success: function () {
            shareUpdate();
            // 用户确认分享后执行的回调函数
        },
        cancel: function () {
            // 用户取消分享后执行的回调函数
        }
    });
});



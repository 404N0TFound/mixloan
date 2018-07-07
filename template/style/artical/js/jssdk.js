
wx.config({
    debug: false,// 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
    appId: appId, // 必填，公众号的唯一标识  
    timestamp: timestamp, // 必填，生成签名的时间戳
    nonceStr: nonceStr, // 必填，生成签名的随机串
    signature: signature,// 必填，签名，见附录1
    jsApiList: ['onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ','onMenuShareQZone','onMenuShareWeibo','hideMenuItems'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
});

wx.ready(function (){ 
    //分享到朋友圈
     wx.onMenuShareTimeline({  
          title: s_title,  
          link: s_link,  
          imgUrl: s_imgUrl,  
          trigger: function (res) {  
          },  
          success: function (res) {  
            alert('分享成功');  
            //分享之后增加游戏次数   
          },  
          cancel: function (res) {  
            alert('已取消分享');   
          },  
          fail: function (res) {  
            alert('分享失败');  
          }  
    });
     //分享到朋友
    wx.onMenuShareAppMessage({
        title: s_title, // 分享标题
        link: s_link,  
        imgUrl: s_imgUrl,
        desc: s_desc,
        type: '', // 分享类型,music、video或link，不填默认为link
        dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
        success: function () { 
         // alert()
            // 用户确认分享后执行的回调函数
            alert('分享成功！'); 
        },
        cancel: function () { 
            alert('已取消分享');
            // 用户取消分享后执行的回调函数
        }
    });
    
     //分享到QQ
    wx.onMenuShareQQ({
      title: s_title, // 分享标题
      link: s_link, // 分享链接
      imgUrl: s_imgUrl, // 分享图标
      desc: s_desc,
      success: function () { 
          // 用户确认分享后执行的回调函数
        alert("分享成功");
      },
      cancel: function () { 
        alert("分享失败");
          // 用户取消分享后执行的回调函数
      }
    });

     //分享到QQ空间
    wx.onMenuShareQZone({
      title: s_title, // 分享标题
      link: s_link, // 分享链接
      imgUrl: s_imgUrl, // 分享图标
      desc: s_desc,
      success: function () { 
          // 用户确认分享后执行的回调函数
        alert("分享成功");
      },
      cancel: function () { 
        alert("分享失败");
          // 用户取消分享后执行的回调函数
      }
    });
    //隐藏部分功能
    //默认隐藏功能
    wx.hideMenuItems({
      menuList: [
        'menuItem:readMode', // 阅读模式
        'menuItem:favorite', // 收藏
        'menuItem:copyUrl', // 复制链接
        'menuItem:openWithQQBrowser',
        'menuItem:share:email',
        'menuItem:openWithSafari',
        'menuItem:share:brand',
        'menuItem:originPage',
        'menuItem:editTag',
        'menuItem:share:facebook'
      ],
      success: function (res) {
      },
      fail: function (res) {
      }
    });
});
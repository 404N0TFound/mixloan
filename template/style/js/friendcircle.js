// 页面初始化
$(function(){  
  //表情包
  for(m=0;m<emojis.length;m++){
    var _emoji=emojis[m];
    $("div.emojiBox ul").append("<li onclick=\'addEmoji(this);\'>"+_emoji+"</li>");
  }
})
// 返回值判断
function dataTransform(res){
    var ret="";
    if(res==null||res==undefined){
        ret="";
    }else{
        ret=res;
    }
    return ret;
}

$.fn.autoHeight = function(){
    function autoIndex(elem){
      elem.style.height = '35px';
    }
    function autoHeight(elem){
        elem.style.height = 'auto';
        elem.scrollTop = 0; //防抖动
        if(elem.scrollHeight>50){
          elem.style.height = elem.scrollHeight + 'px';
        }else{
          elem.style.height = '35px';
        }
        
    }
    this.each(function(){
        autoIndex(this);
        $(this).bind('input propertychange', function() {
          autoHeight(this);                    
        });            
          
    });
}

// 屏蔽微信下拉出黑底现象
if (!HTMLElement.currentStyle) {
    function _getStyle(prop) {
        var _s = window.getComputedStyle(this, null)
        return prop ? _s[prop] : _s;
    }
    HTMLElement.prototype.currentStyle = _getStyle;
    HTMLElement.prototype.getStyle = _getStyle;
}

// 阻止微信下拉出黑底插件
function PreventScroll() {
    // // 非微信浏览器直接跳出 -- 后来发现好些浏览器都有这个坑，所以去掉
    // var ua = navigator.userAgent.toLowerCase();
    // if (!ua.match(/MicroMessenger/i)) return;    
 
    var elem = arguments || []; // 传入绑定的元素
    var $elem = [];     // 存储所有需要监听的元素
 
    // 获取需要监听的元素
    for (var i=0,len=elem.length; i<len; i++) {
        var $e = document.querySelectorAll(elem[i]);
        if (!$e) {console.error('您输入的元素不对，请检查'); return;}
        for(var j=0; j<$e.length; j++) {
            if ($e[j].currentStyle('overflow').match(/auto|scroll/i)) {
                $elem.push($e[j]);
            }
        }
    }
 
    window.addEventListener('touchstart', function(e){
        window.scroll_start = e.touches[0].clientY;
    });
    window.addEventListener('touchmove', prevent);
 
    function prevent(e) {
        var status = '11'; // 1容许 0禁止，十位表示向上滑动，个位表示向下滑动
        var startY = window.scroll_start;
        var currentY = e.touches[0].clientY;
        var direction = currentY - startY > 0 ? '10' : '01';  // 当前的滚动方向，10 表示向上滑动
 
        $elem.forEach(function(ele){
            var scrollTop = ele.scrollTop,
                offsetHeight = ele.offsetHeight,
                scrollHeight = ele.scrollHeight;
 
            if (scrollTop === 0) {
                // 到顶，禁止向下滑动，或高度不够，禁止滑动
                status = offsetHeight >= scrollHeight ? '00' : '01';
            } else if (scrollTop + offsetHeight >= scrollHeight) {
                // 到底，则禁止向上滑动
                status = '10';
            }
        });
 
        // output.innerHTML = status + ' ' + ++count;
        // 如果有滑动障碍，如到顶到底等
        if (status != '11') {
            if (!(parseInt(status, 2) & parseInt(direction, 2))) {
                e.preventDefault();
                return;
            }
        }
    }
}
PreventScroll('.content_detail','.fcMainList','.psn_main');

// 点击进入内容详情
function tomainDetail(index){
// 权限验证
$.post("/Wap/FriendCircle/checkAccess",
    function(res){
      if(res.code==0||res.code=='-4'||res.code=='-5'){
        var _url=$(index).attr("data-src");
        location.href=_url; 
      }else if(res.code=="-3"){
        var _url="/Wap/FinaFriend/reg";
        location.href=_url; 
      }else{
        layer.msg(res.message);
        return false;
      }
    },'json') 
}
// 实名认证
function toAuth(objIndex,userId){
  $(".toAuth").unbind("click");
  var name=$("#name").val();
  var idcard=$("#idcard").val();
  if(name=='' || name=="请输入姓名"){
      document.getElementById('name').focus();

      layer.tips('请输入姓名', '#name', {

          tips: [3, '#f90'],

          time: 3000

      });
      reClick();
      return false;
  }else if(name!=undefined&&name!=null){
      var reg = /^[\u4E00-\u9FA5]{2,15}$/;
      if(!(reg.test(name))){
          document.getElementById('name').focus();
          layer.tips('请输入正确的姓名', '#name', {

              tips: [3, '#f90'],

              time: 3000

          });
          reClick();
          return false;
      }
  }
  if (idcard == '请输入身份证号' || idcard == '') {
      document.getElementById('idcard').focus();

      layer.tips('请输入身份证号码', '#idcard', {

          tips: [3, '#f90'],

          time: 3000

      });
      reClick();
      return false;
  }else if(idcard!=undefined&&idcard!=null){
      if (!(/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$|^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/.test(idcard))) {

          document.getElementById('idcard').focus();

          layer.tips('请填写正确的身份证号', '#idcard', {

              tips: [3, '#f90'],

              time: 3000

          });
          reClick();
          return false;
      }
  }
  $.post('/Wap/FriendCircle/certifyUserInfo',{'userName':name,'idCard':idcard,'userId':userId},
    function(res){
      if(res.code==0){
        if(objIndex=="0"){
          var _url="/wap/FinaFriend/publishComm";
          location.href=_url;
        }else{
          layer.msg("实名认证成功！")
          $("div.realAuth").hide();
        }        
      }else{
        layer.msg(res.message);
        reClick();
        return false;
      }
  })
}
// 实名认证弹窗关闭
$(".authClose").click(function(){
  $("div.realAuth").hide();
})
// 预览图片
function maxImg(index){
  event.stopPropagation();
  var _this=$(index);
  var _imgs=_this.parent().find("img");
  var initialSlide=_this.index();
  $("div.scrollImg .swiper-wrapper").html("");
  for(i=0;i<_imgs.length;i++){
    var imgUrl=_imgs.eq(i).attr("src");
    $("div.scrollImg .swiper-wrapper").append("<div class=\"swiper-slide\"><img src="+ imgUrl +" width=\"100%\"></div>");
  }
  initialSwiper.slideTo(initialSlide, 50, false);
  $("div.scrollImg").show();
}
function tomaxImg(index){
  event.stopPropagation();
  var _this=$(index);
  var _imgs=_this.parent().parent().find(".swiper-slide");
  var _index=_this.parent().index();
  $("div.scrollImg .swiper-wrapper").html("");
  preview(_index,_imgs);  
}
function pubmaxImg(index){
  event.stopPropagation();
  var _this=$(index);
  var _imgs=_this.parent().parent().find("li");
  $("div.scrollImg .swiper-wrapper").html("");
  var initialSlide=_this.parent().index();
  preview(initialSlide,_imgs);
}
// 图片预览代码整合
function preview(initialSlide,index){
  for(i=0;i<index.length;i++){
    var imgUrl=index.eq(i).find('img').attr("src");
    $("div.scrollImg .swiper-wrapper").append("<div class=\"swiper-slide\"><img src="+ imgUrl +" width=\"100%\"></div>");
  }  
  initialSwiper.slideTo(initialSlide, 50, false);
  $("div.scrollImg").show();
}
// 预览图片容器 swiper初始化
var initialSwiper = new Swiper('.scrollImg-swiper', {
    // initialSlide :initialSlide,
    autoplayDisableOnInteraction : false,
    pagination : '.scrollImg-pagination',
    spaceBetween : 20,
    observer:true,//修改swiper自己或子元素时，自动初始化swiper
    observeParents:true//修改swiper的父元素时，自动初始化swiper      
});
// 上传图片swiper初始化
var slidesPerView=1;
var initialSwiper2 = new Swiper('.imgSwiper', {
    freeMode:true,
    slidesPerView: 'auto',
    // freeModeMomentum :false,
    // spaceBetween : 10,
    // slidesPerView :slidesPerView,
    observer:true,//修改swiper自己或子元素时，自动初始化swiper
    observeParents:true//修改swiper的父元素时，自动初始化swiper      
});
// 预览图片退出
$("div.scrollImg .swiper-wrapper").click(function(){
  $("div.scrollImg").hide();
})
//触发表情
$("#emoji").click(function(){
  if($("div.emojiBox").css("height")=='0px'){
    $("div.emojiBox").show().css("height","200px");
  }else{
    $("div.emojiBox").css("height","0px");
  }
})
//选择表情
function addEmoji(index){
  var choosedEmoji=$(index).html();
  $("#contentor").val($("#contentor").val()+choosedEmoji);
}
$("#contentor").focus(function(){
  $("div.emojiBox").css("height","0px");
})
// 选择图片
$("#dummyImgs").click(function(){
  $("#contentImgs").click();
})
// $(".contentImgs").change(function(){
//   var idFile = $(this).attr("id");
//   var file = document.getElementById(idFile);
//   var fileList = file.files; //获取的图片文件
//   fileList = validateUp(fileList);
//   var before_L=$("div.imgSwiper .swiper-wrapper .swiper-slide").length;
//   var choosing_L=fileList.length;
//   var total_L=before_L+choosing_L;
//   if(total_L<=9){
//     if(total_L>0){
//       $("div.waitImgs").show();
//       slidePerView(total_L);
//       for(i=0;i<fileList.length;i++){
//         $("div.imgUploading").show();
//         var _file=fileList[i];
//         readFile(_file,i,fileList.length);
//       }
//     }    
//   }else{
//       layer.msg("上传图片不能超过9张");
//       return false;
//   }
// })
//上传图片(接口)
function readFile(file,x,y) {
    var reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = function (e) {
        var carousel = this.result;
        $.ajax({
            url:'/Wap/friendCircle/uploadOneToAli',
            dataType:'JSON',
            type:'POST',
            async:true,
            data:{'carousel':carousel,'bucket':4},
            success:function( res ){
                if(res.code=="0"){
                  var url=res.data.url;
                  $("div.commentArea form").show();
                  $("div.imgSwiper .swiper-wrapper").append("<div class=\"swiper-slide\"><img src="+ url +" onclick=\'tomaxImg(this);\' /><a onclick=\"delImg(this);\"><img src=\"/Public/Wap/idai/images/friendscircle/delImg.png\" /></a></div>");
                  if(x==y-1){
                    $("div.imgUploading").hide();
                  }
                }else if(res.code=="1"){
                    layer.msg(res.message);
                    return false;
                }
            }
        })
    }
}
// 删除图片
function delImg(index){
  var _this=$(index);
  var url=_this.parent().find('img').attr("src");  
  $.post('/Wap/friendCircle/deleOneImgToAli',{'url':url},
    function(res){
      console.log(res);
      if(res.code==0){
        _this.parent().remove();
        var imgL=$("div.imgSwiper .swiper-slide").length;        
        if(imgL==0){
          $("div.commentArea form").hide();
        }else{
          slidePerView(imgL);
        }
      }
    })
}
//验证图片类型大小
function validateUp(files){
    var delParent;
    var defaults = {
        fileType         : ["jpg","png","jpeg"],   // 上传文件的类型
        fileSize         : 1024 * 1024 * 5        // 上传文件的大小 5M
    };
    var arrFiles = [];//替换的文件数组
    for(var i = 0, file; file = files[i]; i++){
        //获取文件上传的后缀名
        var newStr = file.name.split("").reverse().join("");
        if(newStr.split(".")[0] != null){
                var type = (newStr.split(".")[0].split("").reverse().join("")).toLowerCase();
                console.log(type+"===type===");
                if(jQuery.inArray(type, defaults.fileType) > -1){
                    // 类型符合，可以上传
                    if (file.size >= defaults.fileSize) {
                        // alert(file.size);
                        layer.msg('文件过大');
                        return;
                    } else {
                        // 在这里需要判断当前所有文件中
                        arrFiles.push(file);
                    }
                }else{
                    layer.msg('上传类型不符合');
                    return;
                }
            }else{
                layer.msg('无法识别的文件');
                return;
            }
    }
    return arrFiles;
}
// 动态更改上传图片预览数(slidesPerView)
function slidePerView(index){
  var swiperW=70*index+10;
  var formW=$("div.commentArea form").outerWidth();
  if(swiperW>formW){
    $("div.waitImgs").css('width','100%');
  }else{
    $("div.waitImgs").css('width',swiperW);
  }
}
// 评论发送
function toComment(index){
  $.ajax({
     url:'/Wap/friendCircle/replayComm',
     type:'post',
     data:index,
     dataType:'json',
     success:function (res) {
        if(res.code==0){
          layer.msg("评论成功！",{shift: -1,time:800},function(){
            location.reload();
            // window.history.back();
            // window.history.back();location.reload();
          });
        }else{
          layer.msg(res.message);
          reComment();
          return false;
        }                                              
     },
     error: function(XMLHttpRequest, textStatus, errorThrown) {
         layer.msg("系统异常，请重试!");
         reComment();
         return false;
         // alert(XMLHttpRequest.readyState);
         // alert(textStatus);
     }
   })
}
//验证码
function getCheck(){
  $(".valid_num").unbind("click");
  var flagsms = true;
  //启动计时器，1秒执行一次
  if (flagsms) {
          var phone = $("#phone").val();
          var reg = /^(1)\d{10}$/;
          if(phone!=""){
            if (!reg.test(phone)) {
                document.getElementById('phone').focus();
                layer.tips('请正确填写手机号', '#phone', {

                    tips: [3, '#f90'],

                    time: 3000

                });
                $(".valid_num").bind("click",function(){
                    getCheck();
                });
                flagsms = true;
            }else{              
              $.post("/Wap/FriendCircle/sendSmsCode",{"phone":phone},
              function(data){
                if(data.code==0){
                  layer.msg("已发送");
                  curCount = 60;

                      InterValObj = window.setInterval(SetRemainTime, 1000);

                      flagsms = false;                        
                }else{
                  layer.msg(data.message);
                  flagsms = true;
                  $(".valid_num").bind("click",function(){
                      getCheck();
                  });
                  return false;
                }
              },'json')
            }
          }else{
            document.getElementById('phone').focus();
              layer.tips('请填写手机号', '#phone', {

                  tips: [3, '#f90'],

                  time: 3000

              });
            $(".valid_num").bind("click",function(){
              getCheck();
            });
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

        $(".valid_num").html("重新发送");

        code = ""; //清除验证码。如果不清除，过时间后，输入收到的验证码依然有效
        $(".valid_num").bind("click",function(){
          getCheck();
      });

    }
    else {

        curCount--;

        $(".valid_num").html(curCount+"s");

    }
}

// 关注公众号
$(".noteWx span em").click(function(){
  $(".noteWx span").hide();
  event.stopPropagation();
})
$(".noteWx span,.noteWx").click(function(){
  $("div.noteIdai").show();
})
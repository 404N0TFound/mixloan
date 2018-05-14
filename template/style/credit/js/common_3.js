;(function($){
	setFontSize();
})(Zepto);

/*--------设置字体--------*/
function setFontSize(){
	var scWid = document.documentElement.offsetWidth||document.body.offsetWidth;
	var initScWid = 375;
	var initFontSize = 20;
	
	$('html').css({
		'fontSize': parseInt((scWid * initFontSize) / initScWid) + 'px'
	});
}
	
/*------去除字符串中的空格------*/
function trim(str) {
  return str.replace(/\s/g,'');
}

/*--------解析url 参数--------*/
function getQueryString(name){
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var l = decodeURI(window.location.search);
    var r = l.substr(1).match(reg);
    if (r != null) return unescape(r[2]);
    return null;
}

/*--------被拒概率-------*/
function drawCircle(precent) {
    var parent = precent; //最终概率
    var x = 0; //初始概率
    var c = document.getElementById("myCanvas1");
    var ctx = c.getContext("2d");
    ctx.canvas.width = 300;
    ctx.canvas.height = 300;
    animate();

    function clearFill() {
        ctx.clearRect(0, 0, 300, 300);
    }

    //绘制
    function fill(x) {
        //灰色圈圈
        ctx.beginPath();
        ctx.lineWidth = 6;
        ctx.strokeStyle = "#FFCA43";
        ctx.arc(150, 150, 144, 0, 2 * Math.PI);
        ctx.stroke();
        
        //蓝色圈圈
        ctx.beginPath();
        ctx.lineCap = "round";
        ctx.lineWidth = 6;
        ctx.strokeStyle = "#FFFFFF";

        /*var grd = ctx.createLinearGradient(0, 0, 0, 200);
        grd.addColorStop(0, "#2481D6");
        grd.addColorStop(1, "#30ABF2");
        grd.addColorStop(1, "#30ABF2");
        grd.addColorStop(0, "#2481D6");
        ctx.strokeStyle = grd;*/
           
        ctx.arc(150, 150, 144, -270 * Math.PI / 180, (x * 3.6 - 270) * Math.PI / 180); //设置为12点钟方向
        ctx.stroke();
        
        //百分比
        ctx.font = '96px Arial';
        ctx.fillStyle = '#FFFFFF';
        ctx.textBaseline = 'middle';
        ctx.textAlign = 'center';
        ctx.fillText(x, 144, 125);
        
        //百分号%
        ctx.font = '36px Arial';
        ctx.fillStyle = '#FFFFFF';
        ctx.textBaseline = 'top';
        ctx.textAlign = 'right';
        ctx.fillText('%', 240, 110);

        ctx.font = '28px Arial';
        ctx.fillStyle = '#FFFFFF';
        ctx.textBaseline = 'top';
        ctx.textAlign = 'right';
        ctx.fillText('被拒概率', 205, 195);
    }

    //重绘
    function animate() {
        if (++x > parent) {
            return false;
        } else {
            setTimeout(animate, 10);
            clearFill();
            fill(x);
        }
    }
}

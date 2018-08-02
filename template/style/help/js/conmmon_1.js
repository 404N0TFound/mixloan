
var urlRoot="https://www.kakacaifu.com";



// 阻止微信下拉出黑底插件
function PreventScroll() {
    // // 非微信浏览器直接跳出 -- 后来发现好些浏览器都有这个坑，所以去掉
    // var ua = navigator.userAgent.toLowerCase();
    // if (!ua.match(/MicroMessenger/i)) return;

    // 屏蔽微信下拉出黑底现象
    if (!HTMLElement.currentStyle) {
        function _getStyle(prop) {
            var _s = window.getComputedStyle(this, null)
            return prop ? _s[prop] : _s;
        }
        HTMLElement.prototype.currentStyle = _getStyle;
        HTMLElement.prototype.getStyle = _getStyle;
    }
 
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

PreventScroll('.notice_tail');

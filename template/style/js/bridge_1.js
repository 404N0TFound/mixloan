
$(function(){
	var htmlURL = document.location.href;
    if (htmlURL.indexOf("kdetail.html") >= 0
        || htmlURL.indexOf("card.html") >= 0
        || htmlURL.indexOf("loanCenter.html") >= 0
        || htmlURL.indexOf("tier.html") >= 0
        || htmlURL.indexOf("friendcircle.html") >= 0
        || htmlURL.indexOf("evdaynew.html") >= 0
        || htmlURL.indexOf("evdayhot.html") >= 0) {
	    	function onBridgeReady(){
			 WeixinJSBridge.call('showOptionMenu');
			}

			if (typeof WeixinJSBridge == "undefined"){
			    if( document.addEventListener ){
			        document.addEventListener('WeixinJSBridgeReady', onBridgeReady, true);
			    }else if (document.attachEvent){
			        document.attachEvent('WeixinJSBridgeReady', onBridgeReady); 
			        document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
			    }
			}else{
			    onBridgeReady();
			}
    }else{
    	function onBridgeReady(){
			WeixinJSBridge.call('hideOptionMenu');
		}

		if (typeof WeixinJSBridge == "undefined"){
		    if( document.addEventListener ){
		        document.addEventListener('WeixinJSBridgeReady', onBridgeReady, true);
		    }else if (document.attachEvent){
		        document.attachEvent('WeixinJSBridgeReady', onBridgeReady); 
		        document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
		    }
		}else{
		    onBridgeReady();
		}
    }
})
















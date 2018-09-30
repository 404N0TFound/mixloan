//App 通用类
var GuoleiMuiApp = {
	//注销
	"logout": function(parameters) {
        plus.storage.removeItem("userId");
        plus.storage.removeItem("userMobile");
        plus.storage.removeItem("inviterUserId");
        plus.storage.removeItem("avatarUrl");
        plus.storage.removeItem("inviterCode");
	},

	//当前用户唯一标识
	"userId": function() {
		if(GuoleiMuiApp.isLogin()) {
			var userId = plus.storage.getItem("userId");
			return userId;
		}
		return 0;
	},
	//获取用户手机号
	"userMobile": function(parameters) {
		if(GuoleiMuiApp.isLogin()) {
			var userMobile = plus.storage.getItem("userMobile");
			return userMobile;
		}
		return 0;
	},
	//用户是否登录
	"isLogin": function() {
		var userId = plus.storage.getItem("userId");
		var userMobile = plus.storage.getItem("userMobile");
		return GuoleiJsUtil.isDefine(userId) && GuoleiJsUtil.isDefine(userMobile);
	},
	"getNetWorkType": function() {
		if(window.plus) {
			var currentNetworkType = plus.networkinfo.getCurrentType();
			if(currentNetworkType == plus.networkinfo.CONNECTION_UNKNOW)
				return "unknown";
			if(currentNetworkType == plus.networkinfo.CONNECTION_NONE)
				return "none";
			if(currentNetworkType == plus.networkinfo.CONNECTION_ETHERNET)
				return "ethernet";
			if(currentNetworkType == plus.networkinfo.CONNECTION_WIFI)
				return "wifi";
			if(currentNetworkType == plus.networkinfo.CONNECTION_CELL2G)
				return "2g";
			if(currentNetworkType == plus.networkinfo.CONNECTION_CELL3G)
				return "3g";
			if(currentNetworkType == plus.networkinfo.CONNECTION_CELL4G)
				return "4g";
			return currentNetworkType;
		}
		return "unknown";
	},
	//获取子窗体
	"getChildWebView":function (parentWebView,childWebViewId) {
        var children=parentWebView.children();
        for(var i=0;i<children.length;i++){
            if(children[i].id==childWebViewId){
                return children[i];
            }
        }
        return false;
    },
    'setIOSClipboard': function (value) {
        if (window.plus) {
            if (window.plus.os.name == 'iOS') {

                //获取剪切板
                var UIPasteboard = plus.ios.importClass('UIPasteboard');
                var generalPasteboard = UIPasteboard.generalPasteboard();
                // 设置/获取文本内容:
                generalPasteboard.setValueforPasteboardType(value, 'public.utf8-plain-text');
                return true;
            }
        }
        return false;
    },
    'getIOSClipboard': function () {
        if (window.plus) {
            if (window.plus.os.name == 'iOS') {
                //获取剪切板
                var UIPasteboard = plus.ios.importClass('UIPasteboard');
                var generalPasteboard = UIPasteboard.generalPasteboard();
                var value = generalPasteboard.valueForPasteboardType('public.utf8-plain-text');
                return value;
            }
        }
        return false;
    },
    'setAndroidClipboard': function (value) {
        if (window.plus) {
            if (window.plus.os.name == 'Android') {
                var Context = plus.android.importClass('android.content.Context');
                var main = plus.android.runtimeMainActivity();
                var clip = main.getSystemService(Context.CLIPBOARD_SERVICE);
                plus.android.invoke(clip, 'setText', value);
                return true;
            }
        }
        return false;
    },
    'getAndroidClipboard': function () {
        if (window.plus) {
            if (window.plus.os.name == 'Android') {
                //获取剪切板
                var Context = plus.android.importClass('android.content.Context');
                var main = plus.android.runtimeMainActivity();
                var clip = main.getSystemService(Context.CLIPBOARD_SERVICE);
                return plus.android.invoke(clip, 'getText');
            }
        }
        return false;
    },

};

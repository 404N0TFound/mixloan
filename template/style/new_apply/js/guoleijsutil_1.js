/**
 * 郭磊 js util 工具类
 * @author 郭磊
 * @email 174000902@qq.com
 * @phone 15210720528
 * @github https://github.com/guolei19850528/guolei-js-util.git
 **/

var GuoleiJsUtil = {
	/**
	 * @description 判断值是否为空
	 */
	"isDefine": function(value) {
		if(value == null || value == "" || value == "undefined" || value == undefined || value == "null" || value == "(null)" || value == 'NULL' || typeof(value) == 'undefined') {
			return false;
		} else {
			value = value + "";
			value = value.replace(/\s/g, "");
			if(value == "") {
				return false;
			}
			return true;
		}
	},
	/**
	 *@description 获取url参数
	 * @param name 参数名称
	 * @param locationStr 地址字符串 如果未null 则取当前url
	 */
	"getQueryString": function(name, locationStr) {
		var uri = window.location.search;
		if(GuoleiJsUtil.isDefine(locationStr)) {
			uri = locationStr
		}
		var re = new RegExp("" + name + "=([^&?]*)", "ig");
		return((uri.match(re)) ? (uri.match(re)[0].substr(name.length + 1)) : null);
	},
	/**
	 * @description 设置本地值
	 * @param key 键
	 * @param value 值
	 */
	"setLocalValue": function(key, value) {
		if(window.localStorage) {
			window.localStorage[key] = value;
			return true;
		}
		return false;
	},
	/**
	 * @description 获取本地存储值
	 * @param key 键
	 */
	"getLocalValue": function(key) {
		if(window.localStorage) {
			if(window.localStorage[key]) {
				return window.localStorage[key];
			}
			return false;
		}
		return false;
	},
	/**
	 * @description 删除值
	 * @param key 键
	 */
	"clearLocalValue": function(key) {
		if(window.localStorage) {
			if(GuoleiJsUtil.isDefine(key)) {
				if(window.localStorage[key])
					window.localStorage.removeItem(key);
			} else {
				window.localStorage.clear();
			}
			return true;
		}
		return false;

	},
	/**
	 * @description 获取对象属性数组
	 * @param object object
	 */
	"getObjectProperties": function(object) {
		if(typeof object != "object") {
			return false;
		}
		var properties = [];
		for(var property in object) {
			properties.push(property);
		}
		return properties;
	},
	/**
	 * @description 将json对象转换成key1=value1&key2=value2形式
	 * @param object object
	 */
	"getParametersByJsonObject": function(o) {
		var parameterStr = "";
		var objectProperties = GuoleiJsUtil.getObjectProperties(o);
		if(objectProperties) {
			for(var i = 0; i < objectProperties.length; i++) {
				var jsonObjectProperty = objectProperties[i];
				if(typeof(o[jsonObjectProperty]) != "object" && typeof(o[jsonObjectProperty]) != "function" && typeof(o[jsonObjectProperty]) != "undefined") {
					parameterStr += jsonObjectProperty + "=" + encodeURI(o[jsonObjectProperty]) + "&";
				}

			}
			if(parameterStr.length > 0) {
				parameterStr = parameterStr.substr(0, parameterStr.length - 1);
			}

		}
		return parameterStr;
	},
	/**
	 * @description 获取文件后缀名
	 * @param fileName 文件名称
	 */
	"getFileSuffixName": function(fileName) {
		var suffixName = str.substring(fileName.lastIndexOf("."));
		return suffixName.toLocaleLowerCase();
	},
	/**
	 * @description 去除左右空格
	 * @param str 字符串
	 */
	"trim": function(str) {
		return str.replace(/(^\s*)|(\s*$)/g, "");
	},
	/**
	 * @description 去除左侧空格
	 * @param str 字符串
	 */
	"leftTrim": function(str) {
		return str.replace(/(^\s*)/g, "");
	},
	/**
	 * @description 去除右侧空格
	 * @param str 字符串
	 */
	"rightTrim": function(str) {
		return str.replace(/(\s*$)/g, "");
	},
	/**
	 * @description 返回随机数
	 * @param max 随机数最大值
	 */
	"randomInt": function(max) {
		max = parseInt(max);
		return(Math.ceil(Math.random() * max)) - 1;
	},
	/**
	 * @description 返回制定长度的随机字符串
	 * @param length 长度
	 * @param str 字符串
	 */
	"randomString": function(length, str) {
		var result = '';
		var chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		if(GuoleiJsUtil.isDefine(str)) {
			chars = str;
		}
		for(i = 0; i < parseInt(length); i++) {
			result += chars[GuoleiJsUtil.randomInt(chars.length - 1)];
		}
		return result;
	},
	/**
	 * @description 获取单选列表的选中值
	 * @param name radio 控件名称
	 */
	"getRadioCheckedValue": function(name) {
		var radios = document.getElementsByName(name);
		if(GuoleiJsUtil.isDefine(radios)) {
			for(var i = 0; i < radios.length; i++) {
				var radio = radios[i];
				if(radio.checked) {
					return radio.value;
				}
			}
		}
		return null;
	},
	/**
	 * @description 获取复选框选中值
	 * @param name checkbox名称
	 */
	"getCheckBoxCheckedValues": function(name) {
		var checkBoxs = document.getElementsByName(name);
		if(GuoleiJsUtil.isDefine(checkBoxs)) {
			var checkedValues = [];
			for(var i = 0; i < checkBoxs.length; i++) {
				var checkBox = checkBoxs[i];
				if(checkBox.checked) {
					checkedValues.push(checkBox.value);
				}
			}
			return checkedValues;
		}
		return null;
	},
	/**
	 * @description 获取当前日期对象
	 */
	"getCurrentDateObject": function() {
		var date = new Date();
		var fullYear = date.getFullYear();
		var month = date.getMonth() + 1;
		if(month < 10)
			month = '0' + month;
		var dayInMonth = date.getDate();
		if(dayInMonth < 10)
			dayInMonth = '0' + dayInMonth;
		var dayInWeek = date.getDay();
		var hours = date.getHours();
		if(hours < 10)
			hours = '0' + hours;
		var minutes = date.getMinutes();
		if(minutes < 10)
			minutes = '0' + minutes;
		var seconds = date.getSeconds();
		if(seconds < 10)
			seconds = '0' + seconds;
		var time = date.getTime();
		return {
			'fullYear': fullYear,
			'month': month,
			'dayInMonth': dayInMonth,
			'dayInWeek': dayInWeek,
			'hours': hours,
			'minutes': minutes,
			'seconds': seconds,
			'time': time,
			"dateTimeStr": fullYear + "-" + month + "-" + dayInMonth + " " + hours + ":" + minutes + ":" + seconds,
			"dateStr": fullYear + "-" + month + "-" + dayInMonth,
			"timeStr": hours + ":" + minutes + ":" + seconds,
			"timestamp": parseInt(parseInt(time) / 1000),
			'FullYear': fullYear,
			'Month': month,
			'DayInMonth': dayInMonth,
			'DayInWeek': dayInWeek,
			'Hours': hours,
			'Minutes': minutes,
			'Seconds': seconds,
			'Time': time,
			"DateTimeStr": fullYear + "-" + month + "-" + dayInMonth + " " + hours + ":" + minutes + ":" + seconds,
			"DateStr": fullYear + "-" + month + "-" + dayInMonth,
			"TimeStr": hours + ":" + minutes + ":" + seconds,
			"Timestamp": parseInt(parseInt(time) / 1000),
		}
	},
	/**
	 * @description 时间戳转换为日期对象
	 * @param timestamp 时间戳
	 */
	"unixTimeStampToDate": function(timetamp) {
		if(typeof(timetamp) == "string") {
			timetamp = timetamp.replace('/Date(', '');
			timetamp = timetamp.replace('+0800)/', '');
		}

		var date = new Date();
		date.setTime(timetamp * 1000);
		var fullYear = date.getFullYear();
		var month = date.getMonth() + 1;
		if(month < 10)
			month = '0' + month;
		var dayInMonth = date.getDate();
		if(dayInMonth < 10)
			dayInMonth = '0' + dayInMonth;
		var dayInWeek = date.getDay();
		var hours = date.getHours();
		if(hours < 10)
			hours = '0' + hours;
		var minutes = date.getMinutes();
		if(minutes < 10)
			minutes = '0' + minutes;
		var seconds = date.getSeconds();
		if(seconds < 10)
			seconds = '0' + seconds;
		var time = date.getTime();
		return {
			'fullYear': fullYear,
			'month': month,
			'dayInMonth': dayInMonth,
			'dayInWeek': dayInWeek,
			'hours': hours,
			'minutes': minutes,
			'seconds': seconds,
			'time': time,
			"dateTimeStr": fullYear + "-" + month + "-" + dayInMonth + " " + hours + ":" + minutes + ":" + seconds,
			"dateStr": fullYear + "-" + month + "-" + dayInMonth,
			"timeStr": hours + ":" + minutes + ":" + seconds,
			"timetamp": parseInt(parseInt(time) / 1000),
			'FullYear': fullYear,
			'Month': month,
			'DayInMonth': dayInMonth,
			'DayInWeek': dayInWeek,
			'Hours': hours,
			'Minutes': minutes,
			'Seconds': seconds,
			'Time': time,
			"DateTimeStr": fullYear + "-" + month + "-" + dayInMonth + " " + hours + ":" + minutes + ":" + seconds,
			"DateStr": fullYear + "-" + month + "-" + dayInMonth,
			"TimeStr": hours + ":" + minutes + ":" + seconds,
			"Timestamp": parseInt(parseInt(time) / 1000),
		}
	},
	/**
	 * @description 将字符串转换为日期对象
	 * @param dateStr 日期字符串
	 */
	"stringToDate": function(dateStr) {
		dateStr = dateStr.split(/[- :]/);
		//var date = new Date(dateStr);
		date = new Date(dateStr[0], dateStr[1], dateStr[2], dateStr[3], dateStr[4], dateStr[5]);
		var fullYear = date.getFullYear();
		var month = date.getMonth() + 1;
		if(month < 10)
			month = '0' + month;
		var dayInMonth = date.getDate();
		if(dayInMonth < 10)
			dayInMonth = '0' + dayInMonth;
		var dayInWeek = date.getDay();
		var hours = date.getHours();
		if(hours < 10)
			hours = '0' + hours;
		var minutes = date.getMinutes();
		if(minutes < 10)
			minutes = '0' + minutes;
		var seconds = date.getSeconds();
		if(seconds < 10)
			seconds = '0' + seconds;
		var time = date.getTime();
		return {
			'fullYear': fullYear,
			'month': month,
			'dayInMonth': dayInMonth,
			'dayInWeek': dayInWeek,
			'hours': hours,
			'minutes': minutes,
			'seconds': seconds,
			'time': time,
			"dateTimeStr": fullYear + "-" + month + "-" + dayInMonth + " " + hours + ":" + minutes + ":" + seconds,
			"dateStr": fullYear + "-" + month + "-" + dayInMonth,
			"timeStr": hours + ":" + minutes + ":" + seconds,
			"timestamp": parseInt(parseInt(time) / 1000),
			'FullYear': fullYear,
			'Month': month,
			'DayInMonth': dayInMonth,
			'DayInWeek': dayInWeek,
			'Hours': hours,
			'Minutes': minutes,
			'Seconds': seconds,
			'Time': time,
			"DateTimeStr": fullYear + "-" + month + "-" + dayInMonth + " " + hours + ":" + minutes + ":" + seconds,
			"DateStr": fullYear + "-" + month + "-" + dayInMonth,
			"TimeStr": hours + ":" + minutes + ":" + seconds,
			"Timestamp": parseInt(parseInt(time) / 1000),
		}
	},
	/**
	 * @description 是否是合法手机号
	 */
	"isPhoneNo": function(value) {
		var myreg = /^(((13[0-9]{1})|(15[0-9]{1})|(17[0-9]{1})|(14[0-9]{1})|(166{1})|(199{1})|(18[0-9]{1}))+\d{8})$/;
		if(!myreg.test(value)) {
			return false;
		}
		return true;
	},
	/**
	 * @description 是否是合法邮箱
	 */
	"isEmail": function(value) {
		var myreg = /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
		if(!myreg.test(value)) {
			return false;
		}
		return true;
	},
	/**
	 * @description 是否是整数
	 */
	"isInt": function(value) {
		var myreg = /^\-{0,1}[0-9]{1,}$/;
		if(!myreg.test(value)) {
			return false;
		}
		return true;
	},
	/**
	 * @description 是否是小数
	 */
	"isFloat": function(value) {
		var myreg = /^[-]?[0-9]+\.?[0-9]+$/;
		if(!myreg.test(value)) {
			return false;
		}
		return true;
	},
	/**
	 * @description 是否是微信
	 */
	"isWeChatBrower": function(ua) {
		if(GuoleiJsUtil.isDefine(ua) == false) {
			ua = window.navigator.userAgent;
		}
		return ua.match(/MicroMessenger/i);
	},
	/**
	 * @description 是否是ipad
	 */
	"isIPadBrower": function(ua) {
		if(GuoleiJsUtil.isDefine(ua) == false) {
			ua = window.navigator.userAgent;
		}
		return ua.match(/(iPad).*OS\s([\d_]+)/);
	},
	/**
	 * @description 是否是iphone
	 */
	"isIPhoneBrower": function(ua) {
		if(GuoleiJsUtil.isDefine(ua) == false) {
			ua = window.navigator.userAgent;
		}
		return !GuoleiJsUtil.isIPad() && ua.match(/(iPhone\sOS)\s([\d_]+)/);
	},
	/**
	 * @description 是否是android
	 */
	"isAndroidBrower": function(ua) {
		if(GuoleiJsUtil.isDefine(ua) == false) {
			ua = window.navigator.userAgent;
		}
		return ua.match(/(Android)\s+([\d.]+)/);
	},
	/**
	 * @description 是否移动设备
	 */
	"isMobileBrower": function() {
		return GuoleiJsUtil.isAndroid() || GuoleiJsUtil.isIPhone();
	},
	/**
	 *@description 是否合法身份证 
	 */
	"isIdCard": function(value) {
		num = value.toUpperCase();
		// 身份证号码为18位，18位前17位为数字，最后一位是校验位，可能为数字或字符X。
		var len, re;
		len = num.length;
		if(len < 18 || len > 18) {
			return false;
		}
		if(len == 18) {
			re = new RegExp(/^(\d{6})(\d{4})(\d{2})(\d{2})(\d{3})([0-9]|X|x)$/);
			var arrSplit = num.match(re);
			// 检查生日日期是否正确
			var dtmBirth = new Date(arrSplit[2] + "/" + arrSplit[3] + "/" + arrSplit[4]);

			var bGoodDay;
			bGoodDay = (dtmBirth.getFullYear() == Number(arrSplit[2])) && ((dtmBirth.getMonth() + 1) == Number(arrSplit[3])) && (dtmBirth.getDate() == Number(arrSplit[4]));
			if(!bGoodDay) {
				return false;
			} else {
				// 检验18位身份证的校验码是否正确。
				var valnum;
				var arrInt = new Array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
				var arrCh = new Array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
				var nTemp = 0,
					i;
				for(i = 0; i < 17; i++) {
					nTemp += num.substr(i, 1) * arrInt[i];
				}
				valnum = arrCh[nTemp % 11];
				if(valnum != num.substr(17, 1)) {
					return false;
				}
				return true;
			}
		}
		return false;
	},

	/**
	 * @description 替换所有目标字符串
	 * */
	"replaceAll": function(str, target, value) {
		while(str.indexOf(target) >= 0) {
			str = str.replace(target, value);
		}
		return str;
	},
	"getJsonArraysSameCount": function(jsonArray1, jsonArray2, key1, key2) {
		var result = 0;
		for(var i = 0; i < jsonArray1.length; i++) {
			var json1 = jsonArray1[i];
			for(var j = 0; j < jsonArray2.length; j++) {
				var json2 = jsonArray2[j];
				if(json1[key1].toString() == json2[key2].toString()) {
					result++;
				}
			}
		}
		return result;
	}
};

String.prototype.replaceAll = function(s1, s2) {
	return this.replace(new RegExp(s1, "gm"), s2);
}

function StringBuilder() {
	this.data = Array("");
}
StringBuilder.prototype.append = function() {
	this.data.push(arguments[0]);
}
StringBuilder.prototype.toString = function() {
	return this.data.join("");
}
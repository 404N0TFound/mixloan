var Browser = new Object();
Browser.isMozilla = (typeof document.implementation != 'undefined') && (typeof document.implementation.createDocument != 'undefined') && (typeof HTMLDocument != 'undefined');
Browser.isIE = window.ActiveXObject ? true : false;
Browser.isFirefox = (navigator.userAgent.toLowerCase().indexOf("firefox") != -1);
Browser.isSafari = (navigator.userAgent.toLowerCase().indexOf("safari") != -1);
Browser.isOpera = (navigator.userAgent.toLowerCase().indexOf("opera") != -1);

var Utils = new Object();

Utils.htmlEncode = function (text) {
    return text.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
};

Utils.trim = function (text) {
    if (typeof (text) == "string") {
        return text.replace(/^\s*|\s*$/g, "");
    } else {
        return text;
    }
};

Utils.isEmpty = function (val) {
    switch (typeof (val)) {
        case 'string':
            return Utils.trim(val).length == 0 ? true : false;
            break;
        case 'number':
            return val == 0;
            break;
        case 'object':
            return val == null;
            break;
        case 'array':
            return val.length == 0;
            break;
        default:
            return true;
    }
};

Utils.isUsername = function (username) {
    var reg = /^[a-zA-Z0-9_]{5,30}$/;
    return reg.test(username);
};

//只能輸入數字
Utils.isNumberKey = function (evt) {
    var charCode = (evt.which) ? evt.which : event.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    } else {
        return true;
    }
};

Utils.isNumber = function (val) {
    var reg = /^[\d|\.|,]+$/;
    return reg.test(val);
};

Utils.isInt = function (val) {
    if (val == "") {
        return false;
    }
    var reg = /\D+/;
    return !reg.test(val);
};

Utils.isEmail = function (email) {
    var reg = /([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)/;
    return reg.test(email);
};

Utils.isTel = function (tel) {
    var reg = /^([0-9]{3,4}-)?[0-9]{7,8}$/;
    return reg.test(tel);
};

//验证手机号
Utils.isMobile = function (mobile) {
    var reg = /^134[0-8]\d{7}$|^13[^4]\d{8}$|^14[5-9]\d{8}$|^15[^4]\d{8}$|^16[6]\d{8}$|^17[0-8]\d{8}$|^18[\d]{9}$|^19[8,9]\d{8}$/;
    return reg.test(mobile);
};

//验证ICCID
Utils.isIccid = function (card_no) {
    var reg = /^\d{19}[A-Za-z]{0,1}$/;
    return reg.test(card_no);
};

Utils.isIdCardNo = function (id_card_no) {
    var reg = /^\d{6}(18|19|20)?\d{2}(0[1-9]|1[12])(0[1-9]|[12]\d|3[01])\d{3}(\d|X)$/;
    return reg.test(id_card_no);
};

Utils.fixEvent = function (e) {
    var evt = (typeof e == "undefined") ? window.event : e;
    return evt;
};

Utils.srcElement = function (e) {
    if (typeof e == "undefined")
        e = window.event;
    var src = document.all ? e.srcElement : e.target;
    return src;
};

Utils.isTime = function (val) {
    var reg = /^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}$/;
    return reg.test(val);
};

//用户名是否合法
Utils.isIllegal = function (val) {
    var reg = /^[0-9a-zA-Z_@\.-]+$/;
    return reg.test(val);
};

//只能数字和字母
Utils.isNumOREn = function (val) {
    var reg = /^[0-9a-zA-Z]+$/;
    return reg.test(val);
};

//当前鼠标X坐标
Utils.x = function (e) {
    return Browser.isIE ? event.x + document.documentElement.scrollLeft - 2 : e.pageX;
};

//当前鼠标Y坐标
Utils.y = function (e) {
    return Browser.isIE ? event.y + document.documentElement.scrollTop - 2 : e.pageY;
};

Utils.request = function (url, item) {
    var sValue = url.match(new RegExp("[\?\&]" + item + "=([^\&]*)(\&?)", "i"));
    return sValue ? sValue[1] : sValue;
};

Utils.$ = function (name) {
    return document.getElementById(name);
};

function rowindex(tr) {
    if (Browser.isIE) {
        return tr.rowIndex;
    } else {
        table = tr.parentNode.parentNode;
        for (i = 0; i < table.rows.length; i++) {
            if (table.rows[i] == tr) {
                return i;
            }
        }
    }
}

document.getCookie = function (sName) {
    // cookies are separated by semicolons
    var aCookie = document.cookie.split("; ");
    for (var i = 0; i < aCookie.length; i++) {
        // a name/value pair (a crumb) is separated by an equal sign
        var aCrumb = aCookie[i].split("=");
        if (sName == aCrumb[0])
            return decodeURIComponent(aCrumb[1]);
    }
    // a cookie with the requested name does not exist
    return null;
};

document.setCookie = function (sName, sValue, sExpires) {
    var sCookie = sName + "=" + encodeURIComponent(sValue);
    if (sExpires != null) {
        sCookie += "; expires=" + sExpires;
    }
    document.cookie = sCookie;
};

document.removeCookie = function (sName, sValue) {
    document.cookie = sName + "=; expires=Fri, 31 Dec 1999 23:59:59 GMT;";
};

function getPosition(o) {
    var t = o.offsetTop;
    var l = o.offsetLeft;
    while (o = o.offsetParent) {
        t += o.offsetTop;
        l += o.offsetLeft;
    }
    var pos = {top: t, left: l};
    return pos;
}

function cleanWhitespace(element) {
    var element = element;
    for (var i = 0; i < element.childNodes.length; i++) {
        var node = element.childNodes[i];
        if (node.nodeType == 3 && !/\S/.test(node.nodeValue))
            element.removeChild(node);
    }
}
var posturl = "/wap/idaiK/sendCodePaiPai";
var codeE = "/wap/idaiK/sendCodeE";

function checkImg(phone, key) {
    return "/Api/api/Img/phoneCheck?phone=" + phone + "&key=" + key + "&r=" + randomString(32);
}

//arg.data post提交数据
//arg.success 成功时响应数据
//arg.error 服务器返回error时响应数据
//arg.httpError 请求错误时相应数据
function ApiAjax(arg) {

    if (typeof(arg.count_auto) == "undefined") {
        arg.count_auto = 0;
    }

    arg.success = arg.success || function () { };
    arg.error = arg.error || function () { };
    arg.httpError = arg.httpError || function () { alert("通信出现异常") };

    var ajax = {};
    ajax.type = "post";
    ajax.url = posturl;
    ajax.data = arg.data;

    ajax.success = function (data) {

        if (typeof (data) != "object") {
            if (arg.count_auto < 3) {
                arg.count_auto++;
                ajaxPhoneSms();
            } else {
                alert("获取验证码出现了一个错误!");
            }
            return;
        }

        arg.count_auto = 0;
        if (data.Status != "success") {
            arg.error(data);
        } else {
            arg.success(data);
        }
    }

    ajax.error = function (data) {
        arg.httpError(data);
    }

    $.ajax(ajax);
}

function ApiAjaxE(arg) {

    if (typeof(arg.count_auto) == "undefined") {
        arg.count_auto = 0;
    }

    arg.success = arg.success || function () { };
    arg.error = arg.error || function () { };
    arg.httpError = arg.httpError || function () { alert("通信出现异常") };

    var ajax = {};
    ajax.type = "post";
    ajax.url = codeE;
    ajax.data = arg.data;

    ajax.success = function (data) {

        if (typeof (data) != "object") {
            if (arg.count_auto < 3) {
                arg.count_auto++;
                ajaxPhoneSms();
            } else {
                alert("获取验证码出现了一个错误!");
            }
            return;
        }

        arg.count_auto = 0;
        if (data.Status != "success") {
            arg.error(data);
        } else {
            arg.success(data);
        }
    }

    ajax.error = function (data) {
        arg.httpError(data);
    }

    $.ajax(ajax);
}
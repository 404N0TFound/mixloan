function userCode(mobile,userCodereId) {

    var usersCode = 0;
    usersCode = getCookie("usercode");
    $.ajax({
        url: '/api/userCode',
        data: {
            userId:userCodeuId,
            productId:userCodepId,
            userType: '5',
            mobile:mobile,
            reId:userCodereId,
            usersCode: usersCode,
        },
        type: 'POST',
        timeout: 60000,
        dataType: 'json',
        success: function (data, status, xhr) {

            if (data.code == '10000') {
                //用户注册成功需要跳转
                setCookie("usercode", data.usersCode,3000);
            }
        },
        error: function (xhr, type, error) {

        }
    });
}

function setCookie(cname,cvalue,exdays)
{
    var d = new Date();
    d.setTime(d.getTime()+(exdays*24*60*60*1000));
    var expires = "expires="+d.toGMTString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

//读取cookies
function getCookie(cname)
{
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++)
    {
        var c = ca[i].trim();
        if (c.indexOf(name)==0) return c.substring(name.length,c.length);
    }
    return "";
}
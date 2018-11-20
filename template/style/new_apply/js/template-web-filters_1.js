// /**
//  * artTemplate filters js
//  * */
template.defaults.imports.jsonToString = function (o) {
    return JSON.stringify(o);
};

template.defaults.imports.numberFormat = function (value, format, fixed) {
    if (!GuoleiJsUtil.isDefine(value)) {
        return 0;
    }
    if (format == "decimal")
        return parseFloat(value).toFixed(fixed);
    if (format == "intger")
        return parseInt(value);
    return parseFloat(value).toFixed(fixed);
};

template.defaults.imports.isDefine = function (value) {
    if (value == null || value == "" || value == "undefined" || value == undefined || value == "null" || value == "(null)" || value == 'NULL' || typeof(value) == 'undefined') {
        return false;
    } else {
        value = value + "";
        value = value.replace(/\s/g, "");
        if (value == "") {
            return false;
        }
        return true;
    }
};

template.defaults.imports.getProductTags = function (value) {
    if (GuoleiJsUtil.isDefine(value)) {
        var arr = value.split("\n");
        var str = '';
        for (var i = 0; i < arr.length; i++) {
            str += '<span>' + arr[i] + '</span>';
        }
        return str;
    }

    return '';
};

template.defaults.imports.timestamp = function (value, format) {
    if (GuoleiJsUtil.isDefine(value)) {
        var dateObject = GuoleiJsUtil.unixTimeStampToDate(value);
        return dateObject[format];
    }
    return '';
};

template.defaults.imports.htmlRender = function (value) {
    if (GuoleiJsUtil.isDefine(value)) {
        var htmlStr =value;
        htmlStr= htmlStr.replaceAll("&lt;", "<");
        htmlStr= htmlStr.replaceAll("&gt;", ">");
        htmlStr= htmlStr.replaceAll("&amp;", "&");
        htmlStr= htmlStr.replaceAll("&quot;", "\"");
        htmlStr= htmlStr.replaceAll("&#039;", "'");
        return htmlStr
    }
    return '';
}

template.defaults.imports.showMobile = function (value) {
    if (GuoleiJsUtil.isDefine(value)&&GuoleiJsUtil.isPhoneNo(value)) {
        return value.substr(0,3)+'****'+value.substr(7,10)
    }
    return '';
}



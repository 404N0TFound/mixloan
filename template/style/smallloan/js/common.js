// 筛选
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
function str2asc(str){
    return str.charCodeAt(0).toString(16);
}
function UrlEncode(str){
    var ret="";
    var strSpecial="!\"#$%&'()*+,/:;<=>?[]^`{|}~%";
    var tt= "";

    for(var i=0;i<str.length;i++){
        var chr = str.charAt(i);
        var c=str2asc(chr);
        tt += chr+":"+c+"n";
        if(parseInt("0x"+c) > 0x7f){
            ret+="%"+c.slice(0,2)+"%"+c.slice(-2);
        }else{
            if(chr==" ")
                ret+="+";
            else if(strSpecial.indexOf(chr)!=-1)
                ret+="%"+c.toString(16);
            else
                ret+=chr;
        }
    }
    return ret;
}
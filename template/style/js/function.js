function makeurl(url){
	var ext = arguments[1] || '';
	if(url.indexOf("http")>=0){
		return url;
	}
	if(url.length==0){
    	return '/images/default.png';
    }
    if(url.substr(0,1)!='/'){
    	url = '/'.url;
    }
    if(ext.length>0){
    	url += ext;
    }
    
    var domain = (window.location.host).replace('www.','');

    return 'http://ks.dym5.com'+url;
}

function makeavatar(url){
    var ext = arguments[1] || '';
    if(url.indexOf("http")>=0){
        return url;
    }
    if(url.length==0){
        return '/images/default.png';
    }
    if(url.substr(0,1)!='/'){
        url = '/'.url;
    }
    if(ext.length>0){
        url += ext;
    }

    var domain = (window.location.host).replace('www.','');

    return 'http://m.sh516.com'+url;
}
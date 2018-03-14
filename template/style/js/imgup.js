var _length;
var img_box;
var newImg_box;
$(function(){
	var delParent;
	var defaults = {
		fileType         : ["jpg","png","bmp","jpeg"],   // 上传文件的类型
		fileSize         : 1024 * 1024 * 10                  // 上传文件的大小 10M
	};
		/*点击图片的文本框*/
	$(".file").change(function(){
		var idFile = $(this).attr("id");
		var file = document.getElementById(idFile);
		imgContainer = $(this).parents(".z_photo"); //存放图片的父亲元素
		var fileList = file.files; //获取的图片文件		
		// console.log(fileList+"======filelist=====");
		var input = $(this).parent();//文本框的父亲元素
		var imgArr = [];
		//遍历得到的图片文件
		var numUp = imgContainer.find(".up-section").length;
		var totalNum = numUp + fileList.length;  //总的数量		
		if(fileList.length > 3 || totalNum > 3 ){
			layer.msg("上传图片数目不可以超过3个，请重新选择");  //一次选择上传超过5个 或者是已经上传和这次上传的到的总数也不可以超过5个
		}
		else if(numUp < 3){
			fileList = validateUp(fileList);
			img_box=[];
			for(var i = 0;i<fileList.length;i++){
				_length=fileList.length;
				var _file=fileList[i];
				var _name=_file.name;
				var temp_img={};				
				var imgUrl = window.URL.createObjectURL(fileList[i]);
				imgArr.push(imgUrl);
				_imgArr=imgArr[i];				
				temp_img.name=_name;
				temp_img.src=_imgArr;
				img_box.push(temp_img);
				console.log(img_box);
				console.log(img_box[0].src);

				imgContainer.prepend("<section class=\'up-section fl loading\'>"
	        							+ "<span class=\'up-span\'></span>"
	        							+ "<img class=\'close-upimg no_close\' data-relname='"+ _name +"' onclick=\'del_img(this);\' />"
	        							+ "<img class=\'up-img up-opcity\' src='"+ _imgArr +"' />"
	        							+ "<p class=\'img-name-p\'>"+ _name +"</p>"
	        							+ "<input id=\'taglocation\' name=\'taglocation\' value=\'\' type=\'hidden\' />"
	        							+ "<input id=\'tags\' name=\'tags\' value=\'\' type=\'hidden\'/>"
	        		)
	            	setTimeout(function(){
			             $(".up-section").removeClass("loading");
					 	 $(".up-img").removeClass("up-opcity");
					 },300);
					 numUp = imgContainer.find(".up-section").length;
					if(numUp >= 3){
						$(".z_file").hide();
					}

				// newImg_box=[];
				readFile(_file);//filereader
		   }
		}		
	});

	function readFile(file) {
	    var reader = new FileReader();
	    reader.readAsDataURL(file);
	    var name=file.name;
	    reader.onload = function (e) {
	        var carousel = this.result;
	        $.ajax({
	            url:'/wap/idai/release_ok',
	            dataType:'JSON',
	            type:'POST',
	            data:{'carousel':carousel,'name':name},
	            success:function( res ){
	            	if(res.errcode=="0"){
	            	var imgName=res.errmsg;
	            	var errphoto=res.errphoto;
	            	console.log(imgName);
	            	console.log(errphoto);	            	
	            	var back_data={};
	            	console.log(newImg_box);
	            	var section=$(".up-section");
		            	for(m=0;m<section.length;m++){
		            		var _name=section.eq(m).find(".close-upimg").attr("data-relname");
	            			// var before_name=img_box[m].name;
	            			// var before_src=img_box[m].src;
	            			if(_name==errphoto){	
	            			var src="/Public/Wap/idai/images/upimg_del.png";         				
	            				section.eq(m).find(".close-upimg").attr({"data-name":imgName,"src":src}).removeClass("no_close");
	            			}
	            		}
		            		            	
	            	}else if(res.errcode=="1"){
	            		layer.msg(res.errmsg);
	            		return false;
	            	}
	            }
	        })
	    }
	}
 //    $(".z_photo").delegate(".close-upimg","click",function(){
 //     	  $(".works-mask").show();
 //     	  delParent = $(this).parent();
	// });
	
	// $(".wsdel-ok").click(function(){
	// 	$(".works-mask").hide();
	// 	var numUp = delParent.siblings().length;
	// 	alert(numUp);
	// 	if(numUp < 3){
	// 		delParent.parent().find(".z_file").show();
	// 	}
	// 	 delParent.remove();
		
	// });
	
	$(".wsdel-no").click(function(){
		$(".works-mask").hide();
	});
		
		function validateUp(files){
			var arrFiles = [];//替换的文件数组
			for(var i = 0, file; file = files[i]; i++){
				//获取文件上传的后缀名
				var newStr = file.name.split("").reverse().join("");				
				if(newStr.split(".")[0] != null){
						var type = (newStr.split(".")[0].split("").reverse().join("")).toLowerCase();
						console.log(type+"===type===");
						if(jQuery.inArray(type, defaults.fileType) > -1){
							// 类型符合，可以上传
							if (file.size >= defaults.fileSize) {
								// alert(file.size);
								layer.msg('文件过大');
								return;	
							} else {
								// 在这里需要判断当前所有文件中
								arrFiles.push(file);	
							}
						}else{
							layer.msg('上传类型不符合');	
							return;
						}
					}else{
						layer.msg('无法识别的文件');	
						return;
					}
			}
			return arrFiles;
		}
})


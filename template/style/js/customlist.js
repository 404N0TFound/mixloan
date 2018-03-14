
/*返回值undefined判断*/
function dataTransform(data) {
    var ret = "";
    if (data == null || data == undefined || data == "undefined") {
        ret = "-";
    } else {
        ret = data;
    }
    return ret;
}

// 客户列表 
// 不同卡种切换及数据展示
function _click(index){	
	console.log(index);
	$(index).parent().find("li").removeClass("card_on");
	$(index).addClass("card_on");
	var arrayList = ['7','8','10','12','14','15','16'];
	//console.log(arr);
	//alert(arr);
	var product_id=$(index).attr("data-id");
	//var num=arrayList .indexOf(product_id);
	var num=$.inArray(product_id, arrayList);
	
	if(product_id=="2"){//信而富
		$.post('/wap/daili/xinerfu',{'product_id':product_id},
            function(data){
            	console.log(data);
            	if(data != ""||data != "false"){
            		$("div.customerlist .list_head").html("");
	            	$("div.customerlist .list_content").html("");            	
	            	$("div.customerlist .list_head").append("<span>日期</span>"
											                    + "<span>注册用户数</span>"
											                    + "<span>有效用户数</span>"
											                    + "<span>借款用户数</span>"
	        		)
	        		$("div.customerlist .list_head span").eq(0).css("width","25%");
	            	$("div.customerlist .list_head span").eq(1).css("width","25%");
	            	$("div.customerlist .list_head span").eq(2).css("width","25%");
	            	$("div.customerlist .list_head span").eq(3).css("width","25%");            	
	                for(var i in data){
	                	var regUsers=data[i].regUsers;
	                	var valid=data[i].valid;
	                	var date=data[i].date;
	                	var loan=data[i].loan;
	                	if(loan==undefined){
	                		var _loan="";
	                	}else{
	                		var _loan=loan;
	                	}
	                	if(valid==undefined){
	                		var _valid="";
	                	}else{
	                		var _valid=valid;
	                	}
	                	// $("div.customerlist .list_content").append("<div class=\"listrow iconfont list_main\" data-date="+ dataTransform(date) +" onclick=\"row_click(this);\">"
	                	$("div.customerlist .list_content").append("<div class=\"listrow iconfont list_main\" data-date="+ dataTransform(date) +">"
												                        + "<span style=\"width:25%;\">"+ dataTransform(date) +"</span>"
												                        + "<span style=\"width:25%;\">"+ dataTransform(regUsers) +"</span>"
												                        + "<span style=\"width:25%;\">"+ dataTransform(_valid) +"</span>"
												                        + "<span style=\"width:25%;\">"+ dataTransform(_loan) +"</span>"
												                        // + "<a><img src='"+ _res +"/idai/images/icon_arrow.png' /></a>"
												                    + "</div>"

	            		)
	                }
            	}         	
                
        },'json')
	}else if(product_id=="3"){
		$.post('/wap/daili/xjCount',{'product_id':product_id},
            function(data){
            	console.log(data);
            	$("div.customerlist .list_head").html("");
            	$("div.customerlist .list_content").html("");
            	$("div.customerlist .list_head").append("<span style=\"width:50%;\">日期</span>"
										                    + "<span style=\"width:50%;\">放款用户数</span>"
        		) 
        		for(var i in data){
	                	var count=data[i].money;
	                	var date=data[i].month;
	                	$("div.customerlist .list_content").append("<div class=\"listrow iconfont list_main\" data-date="+ dataTransform(date) +">"
												                        + "<span style=\"width:50%;\">"+ dataTransform(date) +"</span>"
												                        + "<span style=\"width:50%;\">"+ dataTransform(count) +"</span>"
												                        // + "<a><img src='"+ _res +"/idai/images/icon_arrow.png' /></a>"
												                    + "</div>"

	            		)
	                }     	
                
        },'json')
	}else if(product_id=="5"){
		$.post('/wap/daili/jiufu',{'product_id':product_id},
            function(data){
            	console.log(data);
            	$("div.customerlist .list_head").html("");
            	$("div.customerlist .list_content").html("");
            	$("div.customerlist .list_head").append("<span style=\"width:50%;\">日期</span>"
										                    + "<span style=\"width:50%;\">放款金额</span>"
        		) 
        		for(var i in data){
	                	var count=data[i].money;
	                	var date=data[i].date;
	                	$("div.customerlist .list_content").append("<div class=\"listrow iconfont list_main\" data-date="+ dataTransform(date) +">"
												                        + "<span style=\"width:50%;\">"+ dataTransform(date) +"</span>"
												                        + "<span style=\"width:50%;\">"+ dataTransform(count) +"</span>"
												                        // + "<a><img src='"+ _res +"/idai/images/icon_arrow.png' /></a>"
												                    + "</div>"

	            		)
	                }     	
                
        },'json')
	}else if(product_id=="4"){
		$.post('/wap/daili/cardnum_list',{'product_id':product_id},
            function(data){
            	console.log(data);
            	$("div.customerlist .list_head").html("");
                	$("div.customerlist .list_content").html("");
                	$("div.customerlist .list_head").append("<span>日期</span>"
											                    + "<span>注册用户数</span>"
											                    + "<span>核卡用户数</span>"
            		)
        		for(var i in data){
	                	var count=data[i].regUsers;
	                	var date=data[i].date;
	                	var _loan=data[i].money
	                	$("div.customerlist .list_content").append("<div class=\"listrow iconfont list_main\" data-date="+ dataTransform(date) +">"
												                        + "<span>"+ dataTransform(date) +"</span>"
												                        + "<span>"+ dataTransform(count) +"</span>"
												                        + "<span>"+ _loan +"</span>"
												                        // + "<a><img src='"+ _res +"/idai/images/icon_arrow.png' /></a>"
												                    + "</div>"

	            		)
	                }     	
                
        },'json')
	}else if(product_id=="1"){
		$.post('/wap/daili/cardnum_list',{'product_id':product_id},
            function(data){
            	console.log(data);
            	$("div.customerlist .list_head").html("");
                	$("div.customerlist .list_content").html("");
                	$("div.customerlist .list_head").append("<span>日期</span>"
											                    + "<span>注册用户数</span>"
											                    + "<span>放款用户数</span>"
            		)
        		for(var i in data){
	                	var count=data[i].regUsers;
	                	var date=data[i].date;
	                	var _loan=data[i].money
	                	$("div.customerlist .list_content").append("<div class=\"listrow iconfont list_main\" data-date="+ dataTransform(date) +">"
												                        + "<span>"+ dataTransform(date) +"</span>"
												                        + "<span>"+ dataTransform(count) +"</span>"
												                        + "<span>"+ _loan +"</span>"
												                        // + "<a><img src='"+ _res +"/idai/images/icon_arrow.png' /></a>"
												                    + "</div>"

	            		)
	                }     	
                
        },'json')
	}else if(product_id == '11'){
		$.post('/wap/daili/client_list',{'product_id':product_id},
            function(data){
            	console.log(data);
            	$("div.customerlist .list_head").html("");
            	$("div.customerlist .list_content").html("");
            	$("div.customerlist .list_head").append("<span style=\"width:50%;\">日期</span>"
										                    + "<span style=\"width:50%;\">放款用户数</span>"
        		)
        		for(var i in data){
	                	var count=data[i].money;
	                	var date=data[i].date;
	                	$("div.customerlist .list_content").append("<div class=\"listrow iconfont list_main\" data-date="+ dataTransform(date) +">"
												                        + "<span style=\"width:50%;\">"+ dataTransform(date) +"</span>"
												                        + "<span style=\"width:50%;\">"+ dataTransform(count) +"</span>"
												                        // + "<a><img src='"+ _res +"/idai/images/icon_arrow.png' /></a>"
												                    + "</div>"

	            		)
	                }   	
                
        },'json')
	}else if(num != -1){
		$.post('/wap/daili/client_list',{'product_id':product_id},
            function(data){
            	console.log(data);
            	$("div.customerlist .list_head").html("");
            	$("div.customerlist .list_content").html("");
            	$("div.customerlist .list_head").append("<span style=\"width:50%;\">日期</span>"
										                    + "<span style=\"width:50%;\">放款金额</span>"
        		)
        		for(var i in data){
	                	var count=data[i].money;
	                	var date=data[i].date;
	                	$("div.customerlist .list_content").append("<div class=\"listrow iconfont list_main\" data-date="+ dataTransform(date) +">"
												                        + "<span style=\"width:50%;\">"+ dataTransform(date) +"</span>"
												                        + "<span style=\"width:50%;\">"+ dataTransform(count) +"</span>"
												                        // + "<a><img src='"+ _res +"/idai/images/icon_arrow.png' /></a>"
												                    + "</div>"

	            		)
	                }   	
                
        },'json')
	}else if(product_id=='6'){
		$.post('/wap/daili/client_list',{'product_id':product_id},
            function(data){
            	console.log(data);
            	$("div.customerlist .list_head").html("");
            	$("div.customerlist .list_content").html("");
            	$("div.customerlist .list_head").append("<span style=\"width:50%;\">日期</span>"
										                    + "<span style=\"width:50%;\">核卡用户数</span>"
        		)
        		for(var i in data){
	                	var count=data[i].money;
	                	var date=data[i].date;
	                	$("div.customerlist .list_content").append("<div class=\"listrow iconfont list_main\" data-date="+ dataTransform(date) +">"
												                        + "<span style=\"width:50%;\">"+ dataTransform(date) +"</span>"
												                        + "<span style=\"width:50%;\">"+ dataTransform(count) +"</span>"
												                        // + "<a><img src='"+ _res +"/idai/images/icon_arrow.png' /></a>"
												                    + "</div>"

	            		)
	                }   	
                
        },'json')
	}else if(product_id=='9'){
		$.post('/wap/daili/client_list',{'product_id':product_id},
            function(data){
            	console.log(data);
            	$("div.customerlist .list_head").html("");
            	$("div.customerlist .list_content").html("");
            	$("div.customerlist .list_head").append("<span style=\"width:50%;\">日期</span>"
										                    + "<span style=\"width:50%;\">激活用户数</span>"
        		)
        		for(var i in data){
	                	var count=data[i].money;
	                	var date=data[i].date;
	                	$("div.customerlist .list_content").append("<div class=\"listrow iconfont list_main\" data-date="+ dataTransform(date) +">"
												                        + "<span style=\"width:50%;\">"+ dataTransform(date) +"</span>"
												                        + "<span style=\"width:50%;\">"+ dataTransform(count) +"</span>"
												                        // + "<a><img src='"+ _res +"/idai/images/icon_arrow.png' /></a>"
												                    + "</div>"

	            		)
	                }   	
                
        },'json')
	}else{
		$.post('/wap/daili/information',{'product_id':product_id},
            function(data){
                console.log(data);
                
                
                	$("div.customerlist .list_head").html("");
                	$("div.customerlist .list_content").html("");
                	$("div.customerlist .list_head").append("<span>日期</span>"
											                    + "<span>注册用户数</span>"
											                    + "<span>放款用户数</span>"
            		)
                
                
            		for(i=0;i<data.length;i++){
	                	var date=data[i].date;
	                	var count=data[i].count;
	                	var loan=data[i].loan;  
	                	if(loan==""||loan==undefined){
	                		var _loan="0";
	                	}else{
	                		var _loan=loan;
	                	}  	
	                	// $("div.customerlist .list_content").append("<div class=\"listrow iconfont list_main\" data-date="+ dataTransform(date) +" onclick=\"row_click(this);\">"
	                	$("div.customerlist .list_content").append("<div class=\"listrow iconfont list_main\" data-date="+ dataTransform(date) +">"
												                        + "<span>"+ dataTransform(date) +"</span>"
												                        + "<span>"+ dataTransform(count) +"</span>"
												                        + "<span>"+ _loan +"</span>"
												                        // + "<a><img src='"+ _res +"/idai/images/icon_arrow.png' /></a>"
												                    + "</div>"

	            		)
	            	
                }
        },'json')
	}
}

//点击行查看详细数据
function row_click(index){
	var _this=$(index);
	if(_this.find("a").length != 0){
		var productId=$(".card_on").attr("data-id");
		var date=_this.attr("data-date");
		location.href="customList_detail.html?productId="+productId+"&date="+date;
	}else{
		//layer.msg("暂时无法显示详细信息！");
		return false;
	}
}
// $("div.list_content").on("click",".list_main",function(){
		
// })

//每月数据
function monthData(date){
	$.post('/wap/daili/summarizing',{'date':date},
            function(data){
            	if(data.errcode=="001"){
            		$("div.list_content table tbody").html("");
            		var errmsg=data.errmsg;
            		for(i=0;i<errmsg.length;i++){
            			var id=errmsg[i].id;//产品id
            			var title=errmsg[i].title;//产品
            			var date=errmsg[i].date;//日期
            			var apply=errmsg[i].apply;//申请数据
            			var success=errmsg[i].success;//放款成功（下款）
            			var valid=errmsg[i].valid;//有效（信而富）
            			var commission=errmsg[i].commission;//奖金
            			// var commission=(errmsg[i].commission).toFixed(2);//奖金
            			var unit=errmsg[i].unit;//单位
            			if(apply==null||apply==undefined){
            				if(id=="5"||id=="6"||id=="12"){
            					apply="-";
            				}else{
            					apply="0";
            				}            				
            			}else{
            				apply=apply;
            			}
            			if(valid==null||valid==undefined){
            				valid="0"+unit;
            			}else{
            				valid=parseInt(valid)+unit;
            			}
            			if(success==NaN||success==null||success==undefined){
            				if(id=="5"||id=="12"){
            					success="0元";
            				}else if(id=="6"){
            					success="0位";
            				}else{
            					success="0"+unit;
            				}            				
            			}else{
            				success=parseInt(success)+unit;
            			}
            			if(commission==""||commission==null||commission==undefined){
            				commission=0;
            			}else{
            				commission=parseInt(commission);
            			}
            			//奖金
            			if(id=="2"){
            				$("div.list_content table tbody").append("<tr class=\"xrf\">"
									                                    + "<td><span>"+ dataTransform(title) +"</span></td>"
									                                    + "<td><span>"+ dataTransform(date) +"</span></td>"
									                                    + "<td><span>"+ dataTransform(apply) +"</span></td>"
									                                    + "<td class=\"td_xrf\"></td>"
									                                    + "<td><span>"+ dataTransform(commission) +"</span></td>"
									                                + "</tr>");
            				if(success=="-"){
            					success="0";
            				}
            				$("div.list_content table tbody tr").eq(i).find("td.td_xrf").append("<table>"
														                                            + "<thead>"
														                                                + "<tr>"
														                                                    + "<th>有效</th>"
														                                                    + "<th>下款</th>"
														                                                + "</tr>"
														                                            + "</thead>"
														                                            + "<tbody>"
														                                                + "<tr>"
														                                                    + "<td>"+ dataTransform(valid) +"</td>"
														                                                    + "<td>"+ dataTransform(success) +"</td>"
														                                                + "</tr>"
														                                            + "</tbody>"
														                                        + "</table>");
            			}else{
            				if(success=="-"){
            					success="0";
            				}
            				$("div.list_content>table>tbody").append("<tr>"
									                                    + "<td>"+ dataTransform(title) +"</td>"
									                                    + "<td>"+ dataTransform(date) +"</td>"
									                                    + "<td>"+ dataTransform(apply) +"</td>"
									                                    + "<td>"+ dataTransform(success) +"</td>"
									                                    + "<td>"+ dataTransform(commission) +"</td>"
									                                + "</tr>");
            			}      			
            		}
            	}else{
            		$("div.list_content table tbody").html("");
            		layer.msg(data.errmsg);
            		return false;
            	}
            })
}
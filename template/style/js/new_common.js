document.writeln("<div class=\'daili_ewm\'>");
document.writeln("        <div class=\'ewm_floor\'></div>");
document.writeln("        <div class=\'ewm_main\'>");
// if(daili_id!="6"){
 document.writeln("            <h1>点击右上角分享专属链接给好友</h1>");
// }
if(daili_id=="1"){
document.writeln("                <div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_ppd3.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:50.5%;bottom: 22.2%;left:24.9%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee1\' style=\'position: absolute;min-width:48%;bottom: 14%;left:26%; color:#fff;background-color: #fd6c2d;min-height:25px;line-height:25px;max-width: 58%;font-size: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");    
}else if(daili_id=="2"){
document.writeln("                <div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_xrf.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:46%;bottom: 27%;left:27%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee2\' style=\'position: absolute;min-width:48%;bottom: 16%;left:26%; color:#fff;background-color: #1372de;min-height:25px;line-height:25px;max-width: 58%;font-size: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>    ");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="3"){
document.writeln("                <div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_xjk1.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:44%;bottom: 25%;left:30%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee3\' style=\'position: absolute;min-width:48%;bottom: 17%;left:26%;background-color: #fff;border-radius: 16px; min-height:25px;line-height:25px;max-width: 58%;font-size: 16px; margin-left: 10px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="4"){
document.writeln("                <div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_pf3.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:52%;bottom: 24%;left:24%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 16%;left:26%; color:#fff;background-color: #4589de;min-height:25px;line-height:25px;max-width: 58%;font-size: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="5"){
document.writeln("                <div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_jf2.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:46%;bottom: 22%;left:27%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:60%;bottom: 14%;left:20%; color:#fff;background-color:#f07374;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius:5px; \'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}

//民生信用卡
else if(daili_id=="6"){

// document.writeln("                <div class=\'text-center agentcode poster\' id=\'poster\'>");
// document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
// document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_ms2.png\' width=\'100%\' alt=\'\'>");
// document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:72%;bottom: 12%;left:14%;\' alt=\'\'>");
// document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 3%;left:26%; color:#20a53f;background-color: #fff;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
// document.writeln("                    </div>");
// document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
// document.writeln("                </div>");

document.writeln("                <div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_ms_add.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:60%;bottom: 23%;left:20%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 14%;left:26%; color:#70bee0;background-color: #fff;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");


}else if(daili_id=="7"){

document.writeln("                <div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_jlh1.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:60%;bottom: 23%;left:20%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 14%;left:26%; color:#70bee0;background-color: #fff;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");

}else if(daili_id=="8"){
document.writeln("                <div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_kld.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:54%;bottom: 24%;left:23%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 15%;left:26%; color:#6b74db;background-color: #fff;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="9"){
document.writeln("                <div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_xy.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:58%;bottom: 18.5%;left:21%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 12%;left:26%; color:#1a93ee;background-color: #fff;min-height:25px;line-height:25px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="10"){
document.writeln("                <div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_yrd.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:64%;bottom: 22%;left:18%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 14%;left:26%; color:#15abff;background-color: #fff;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="11"){
document.writeln("                <div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_ddq.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:68%;bottom: 24%;left:16%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 15%;left:26%; color:#ffa558;background-color: #fff;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="12"){
document.writeln("                <div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_zyd.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:60%;bottom: 23%;left:20%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 15%;left:26%; color:#ffa558;background-color: #fff;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="14"){
document.writeln("                <div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_kyh.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:62%;bottom: 22%;left:19%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 14%;left:26%; color:#31a2e9;background-color: #fff;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="15"){
document.writeln("                <div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_lrh.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:64%;bottom: 23%;left:18%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 15%;left:26%; color:#fe6585;background-color: #fff;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="16"){
document.writeln("                <div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_xxqd.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:66%;bottom: 23%;left:18%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 15%;left:26%; color:#d86556;background-color: #fff;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="18"){
document.writeln("                <div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_pingan.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:60%;bottom: 23%;left:20%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 15%;left:26%; color:#d86556;background-color: #fff;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="19"){
document.writeln("                <div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_jsqb.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:66%;bottom: 22%;left:17%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 14%;left:26%; color:#0582a2;background-color: #fff;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="20"){
document.writeln("                <div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_xshph.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:64%;bottom: 22%;left:18%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 14%;left:26%; color:#0582a2;background-color: #fff;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="21"){
document.writeln("<div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_gdxyk.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:58%;bottom: 21%;left:21%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 14%;left:26%; color:#ed710c;background-color: transparent;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="22"){
document.writeln("<div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_qmqb2.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:60%;bottom: 24%;left:20%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 15%;left:26%; color:#fff;background-color: #2783c5;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="23"){
document.writeln("<div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_mxd3.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:52%;bottom: 21%;left:24%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 15%;left:26%; color:#545353;background-color: transparent;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="24"){
document.writeln("<div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_gfd1.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:60%;bottom: 23%;left:20%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 15%;left:26%; color:#fff;background-color: transparent;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="25"){
document.writeln("<div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_nwd1.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:62%;bottom: 23%;left:19%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 15%;left:26%; color:#fff;background-color: transparent;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="26"){
document.writeln("<div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_hb1.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:60%;bottom: 23%;left:20%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 15%;left:26%; color:#fff;background-color: transparent;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="27"){
document.writeln("<div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_mzjk1.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:62%;bottom: 21%;left:19%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 14%;left:26%; color:#fff;background-color: transparent;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="28"){
document.writeln("<div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_sxd.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:62%;bottom: 23%;left:19%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 15%;left:26%; color:#fff;background-color: transparent;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="29"){
document.writeln("<div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_rp.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:60%;bottom: 23%;left:20%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 15%;left:26%; color:#fff;background-color: transparent;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="30"){
document.writeln("<div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_hl.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:60%;bottom: 23%;left:20%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 15%;left:26%; color:#fbb45e;background-color: transparent;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="31"){
document.writeln("<div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_hkcr.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:58%;bottom: 23%;left:21%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 15%;left:26%; color:#fbb45e;background-color: transparent;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="33"){
document.writeln("<div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_yk.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:58%;bottom: 23.5%;left:21%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 15%;left:26%; color:#fbb45e;background-color: transparent;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="34"){
document.writeln("<div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_jyd.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:62%;bottom: 22.6%;left:19%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 15%;left:26%; color:#fbb45e;background-color: transparent;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="35"){
document.writeln("<div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_jt.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:58%;bottom: 22%;left:21%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 14%;left:26%; color:#fff;background-color: transparent;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="36"){
document.writeln("<div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_rxf.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:60%;bottom: 22.8%;left:20%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 15%;left:26%; color:#fff;background-color: transparent;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="32"){
document.writeln("<div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_shxyk.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:60%;bottom: 22.8%;left:20%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 15%;left:26%; color:#fff;background-color: transparent;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="37"){
document.writeln("<div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_wdw.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:60%;bottom: 23.5%;left:20%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 15%;left:26%; color:#fff;background-color: transparent;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="38"){
document.writeln("<div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_shb.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:60%;bottom: 23.5%;left:20%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 15%;left:26%; color:#fff;background-color: transparent;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="39"){
document.writeln("<div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_tqy.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:56%;bottom: 23.5%;left:22%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 17%;left:26%; color:#4f7af6;background-color: transparent;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="40"){
document.writeln("<div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_wxkkd.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:62%;bottom: 22.5%;left:19%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 16.5%;left:26%; color:#ff8327;background-color: transparent;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}else if(daili_id=="41"){
document.writeln("<div class=\'text-center agentcode poster\' id=\'poster\'>");
document.writeln("                    <div class=\'pro_img\' id=\'pro_img\'>");
document.writeln("                        <img id=\'source_img\' onclick=\'hecheng()\' src=\'"+ aa +"/idai/images/ppd/daili_chrk.png\' width=\'100%\' alt=\'\'>");
document.writeln("                        <img id=\'ewm_img\' src=\'"+ ewm_url +"\' style=\'position: absolute;width:58%;bottom: 26%;left:21%;\' alt=\'\'>");
document.writeln("                        <span class=\'referee referee4\' style=\'position: absolute;min-width:48%;bottom: 16.5%;left:26%; color:#fff;background-color: transparent;min-height:32px;line-height:32px;max-width: 58%;font-size: 16px;border-radius: 16px;\'>推荐人："+ daili_name +"<span>");
document.writeln("                    </div>");
document.writeln("                    <div id=\'compose2\' style=\'display: none\'></div>");
document.writeln("                </div>");
}

// if(daili_id!="6"){
document.writeln("            <p><textarea id=\'bar\' style=\'resize: none;height: 32px;\' class=\'autogenerate\' readonly>"+ daili_url +"</textarea><a class=\'copy copy-btn\' href=\'javascript:;\'>复制推广链接</a></p>");
document.writeln("            <span><img src=\'"+ aa +"/idai/images/newbackstage/share2.png\' /></span>");
// }
document.writeln("            <a class=\'layer_close\'><img src=\'"+ aa +"/idai/images/del.png\' /></a>");
document.writeln("        </div>");
document.writeln("    </div>");
// 玖富弹窗
document.writeln("<div class=\'jiufu_pop\'>");
document.writeln("        <div class=\'jiufu_pop_floor\'></div>");
document.writeln("        <div class=\'jiufu_pop_contain\'>");
document.writeln("            <img class=\'jiufu_img\' src=\'"+ aa +"/idai/images/newbackstage/jiufu_popbg.png\' />");
document.writeln("            <a class=\'contact_service\' href=\'javascript:;\'>联系客服生成</a>");
document.writeln("        </div>");
document.writeln("    </div>");
//图片合成
function hecheng(){
    var i = $("#pro_img");
    var width = i.width();
    var height = i.height();
    if(daili_id=="3"){
    	var scaleBy = 1.1;
    }else if(daili_id=="4"){
    	var scaleBy = 1.3;
    }else{
    	var scaleBy = 1.6;
    }
    var type = "png";
    var canvas = document.createElement('canvas');
    canvas.width = width * scaleBy;
    canvas.height = height * scaleBy;
    canvas.style.width = width * scaleBy + 'px';
    canvas.style.height = height * scaleBy + 'px';
    var context = canvas.getContext('2d');
    context.scale(scaleBy, scaleBy);
    html2canvas(i,{
        canvas: canvas,
        onrendered: function(canvas) {
            var compose_width = $("#source_img").width()*0.94;
            $("#compose2").append(Canvas2Image.convertToImage(canvas, width * scaleBy, height * scaleBy, type));
            $("#compose2 img").css("width", compose_width + "px");            
            var compose2=$("#compose2");
            var pro_img=$("#pro_img");
            compose2.show();
            pro_img.hide();                   
        }
    });
}
// 申请
$("#daili_toapply").click(function(){

    if(daili_id==5){
    	if(daili_url!=""){
    		//图片合成并弹出
	        $("div.daili_ewm").show();
	        $(".daili_ewm").height($(window).height());                 
	        if($("#compose2").css("display")=="none"){
	        	// $("#source_img").click();
          		// ewm_style();
	          	setTimeout(function(){
	        		$("#source_img").click();
	            	ewm_style();
	        	},20) 	        		            
	        }else{
	        	ewm_style();
	        }
    	}else{
    		$(".jiufu_pop").show();
        	jiufu_pop();
    	}        
    }else{
        //图片合成并弹出
        $("div.daili_ewm").show();
        $(".daili_ewm").height($(window).height());                 
        if($("#compose2").css("display")=="none"){
        	setTimeout(function(){
        		$("#source_img").click();
            	ewm_style();
        	},20)            
        }else{
        	ewm_style();
        }
    }        
})
// 身份证信息提交
$(".submit_idcard").click(function(){
    var name=$("#name").val();
    var idcard=$("#idcard").val();
    if (name == '请输入姓名' || name == '') {
        layer.msg("请输入姓名");
        return false;
    }
    if (idcard == '请输入身份证号码' || idcard == '') {
        layer.msg("请输入身份证号码");
        return false;
    }else if(idcard!=undefined&&idcard!=null){
        if(!(/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$|^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/.test(idcard))) {
            layer.msg("请填写正确的身份证号");
            return false;
        }
    }
    $.post("/wap/daili/verifyIdCard",{"name":name,"idcard":idcard,"daili_id":daili_id},
        function(data){
            if(data.errcode=="100"){
               // minsheng_style();
                $("div.add_main").hide();
                $("div.applying").show();
            }else{
                layer.msg(data.errmsg);
                return false;
            }
    })
})
// 玖富联系客服
$(".contact_service").click(function(){
    var _url="/wap/daili/addService";
    location.href=_url;
})
// 二维码弹窗
function ewm_style(){
	$("div.ewm_main").css({"width":"94%","left":"3%"});	  
	$("div.ewm_main h1").show();	
    var erm_H=$("div.ewm_main").height();    
    if($("#compose2").css("display")=="none"){
    	var gap_H=$(window).height()-$("div.ewm_main").height();
	    var gap_height=$("#pro_img").height()*0.06;
	    var ajust_T=(gap_H+gap_height)/2+"px";
	    $("div.ewm_main").css("top",ajust_T);
    }else{
    	var window_height=$(window).height();
    	var erm_T=(window_height-erm_H)/2+"px";
    	$("div.ewm_main").css("top",erm_T);
    }    
}

// 民生实名认证
// function minsheng_style(){
//     $("div.minsheng_add").show(); 
//     var window_height=$(window).height();
//     var main_H=$("div.add_main").height();
//     var applying_H=$("div.applying").height();
//     var main_T=(window_height-main_H)/2.2+"px";
//     var applying_T=(window_height-applying_H)/2.2+"px";
//     $("div.add_main").css("top",main_T); 
//     $("div.applying").css("top",applying_T);
// }
//玖富弹窗
function jiufu_pop(){
    var window_height=$(window).height();
    var window_width=$(window).width();
    var contain_H=$("div.jiufu_pop_contain").height();
    var contain_T=(window_height-contain_H)/2+"px";
    $(".jiufu_pop").width(window_width);
    $(".jiufu_pop").height(window_height);
    $("div.jiufu_pop_contain").css("top",contain_T);
}
// 关闭弹窗
$(".temp_share_floor,.layer_close").click(function(){
    $(".pop").hide();
    $(".temp_share").hide();
    $("div.daili_ewm").hide();
    //$("div.minsheng_add").hide();
})

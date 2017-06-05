<?php

    //下拉菜单
   require('./mywx.php');
   $wechatObj = new wechatCallbackapiTest();

    $json='{
		     "button":[
		     {	
		          "type":"click",
		          "name":"今日歌曲",
		          "key":"1000"
		      },
		      {
		           "name":"菜单",
		           "sub_button":[
		           {	
		               "type":"view",
		               "name":"搜索",
		               "url":"http://www.soso.com/"
		            },
		            {
		                 "type":"miniprogram",
		                 "name":"wxa",
		                 "url":"http://mp.weixin.qq.com",
		                 "appid":"wx286b93c14bbf93aa",
		                 "pagepath":"pages/lunar/index.html"
		             },
		            {
		               "type":"click",
		               "name":"赞一下我们",
		               "key":"2000"
		            }]
		       }]
		 }';
       
        //获取access_token的值
        $id=$wechatObj->getAccessToken(); 

        $url="https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$id}";
        $wechatObj->getData($url,$json);
		 
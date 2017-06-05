<?php

    //下拉菜单
   
class getData1{

    public function getData($url,$data=null)
    {
        // 1. cURL初始化
        $ch = curl_init();

        // 2. 设置cURL选项
        /*
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        */
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        if(!empty($data)){
        	curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        // 3. 执行cURL请求
        $ret = curl_exec($ch);

        // 4. 关闭资源
        curl_close($ch);

        return $ret;
    }

     public function jsonToArray($json)
    {
        $arr = json_decode($json, 1);
        return $arr;
    }

    public function getAccessToken()
    {
        // redis  memcache SESSION
        session_start();

        if ($_SESSION['access_token'] && (time()-$_SESSION['expire_time']) < 7000 )
        {
            return $_SESSION['access_token'];
        } else {
            $appid = "wx58f393b77aeb9cf1";
            $appsecret = "fba09072347681949aae45301c8c4de5";

            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
            $access_token = $this->jsonToArray($this->getData($url))['access_token'];

            // 写入SESSION
            $_SESSION['access_token'] = $access_token;
            $_SESSION['expire_time'] = time();
            return $access_token;
        }
    }
}


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
  

       $mod=new getData1;

        //获取access_token的值
        $id=$mod->getAccessToken(); 

        $url="https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$id}";
        $mod->getData($url,$json);
		 

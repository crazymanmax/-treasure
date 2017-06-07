<?php
    
    //扫码的功能

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
   
     //{"expire_seconds": 604800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": 123}}}

     $mod=new getData1();
     
     $id=$mod->getAccessToken();

     //生成临时二维码
     $url1="https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token={$id}";
     $data=array("expire_seconds"=>50000,"action_name"=>'QR_SCENE',"action_info"=>array("scene"=>array('scene_id'=>123)));
     $data=json_encode($data);
     $ticket=$mod->getData($url1,$data);
     $ticket=json_decode($ticket,true);
     var_dump($ticket);
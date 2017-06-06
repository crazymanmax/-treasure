<?php
   
   //群发消息的
    /*
        根据OpenID列表群发【订阅号不可用，服务号认证后可用】
		接口调用请求说明
		http请求方式: POST
		https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=ACCESS_TOKEN
		POST数据说明
		POST数据示例如下：
		
		文本：
		{
		   "touser":[
		    "OPENID1",
		    "OPENID2"
		   ],
		    "msgtype": "text",
		    "text": { "content": "hello from boxer."}
		}
    */


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

   $mod=new getData1;

   $id=$mod->getAccessToken();
   echo $id;
   //获取openid列表
   $url1="https://api.weixin.qq.com/cgi-bin/user/get?access_token={$id}";
  
   $data=json_decode($mod->getData($url1),true);
   //var_dump($data);
   $num=$data['total'];
   $list=$data['data']['openid'];
   //var_dump($list);
    
    //群发短信了
   /*$list1="";
  foreach($list as $v){
  	$list1.='"'.$v.'",';
  }
  $list1=trim($list1,',');
  if(count($list)>1){
  	$list1='['.$list1.']';
  }*/
   $data2='{
		   "touser":'.$list.',
		    "msgtype": "text",
		    "text": { "content": "hello from boxer."}
		}';
   $url="https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token={$id}";

   var_dump($data2);
   //var_dump($mod->getData($url,$data2));		
 ?>
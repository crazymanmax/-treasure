<?php 

   //获取微信发来的code值
   
   //微信网页授权的步骤：走mywx.php页的test超链接，再走到ab.php，跳转到bb.php页面


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


    $mod=new getData1();

    /*$url1="https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx58f393b77aeb9cf1&redirect_uri=".urlencode('http://39.108.1.202/weixin/bb.php')."&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
    $data=json_decode($mod->getData($url1),true);
    var_dump($data);
    exit;*/
    $code=$_GET['code'];
    //echo $code;

    $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=wx58f393b77aeb9cf1&secret=fba09072347681949aae45301c8c4de5&code={$code}&grant_type=authorization_code";

    $data=json_decode($mod->getData($url),true);

    //var_dump($data);


    $url2="https://api.weixin.qq.com/sns/userinfo?access_token={$data['access_token']}&openid={$data['openid']}&lang=zh_CN";

    $data2=json_decode($mod->getData($url2),true);

    var_dump($data2);
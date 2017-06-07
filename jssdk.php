<?php
   //获取 通过config接口注入权限验证信息的参数
   
class check{

    public function getData($url, $method = 'GET', $arr = '')
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
        if (strtoupper($method) == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
        }
        // 3. 执行cURL请求
        $ret = curl_exec($ch);
        // 4. 关闭资源
        curl_close($ch);
        return $ret;
    }
    /*
     * JSON 转化为数组
     * */
    public function jsonToArray($json)
    {
        $arr = json_decode($json, 1);
        return $arr;
    }
    public function getAccessToken()
    {
        // redis  memcache SESSION
        session_start();
        if (isset($_SESSION['access_token']) && (time() - $_SESSION['expire_time']) < 7000) {
            return $_SESSION['access_token'];
        } else {
            $appid = "wx58f393b77aeb9cf1";
            $appsecret = "fba09072347681949aae45301c8c4de5";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $appsecret;
            $access_token = $this->jsonToArray($this->getData($url))['access_token'];
            // 写入SESSION
            $_SESSION['access_token'] = $access_token;
            $_SESSION['expire_time'] = time();
            return $access_token;
        }
    }
   
   //获取用户openid列表
    public function getUserOpenIdList()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=" . $this->getAccessToken();
        return $this->getData($url);
    }
    // 网页授权的接口，获取用户信息
    public function getUserInfo()
    {
        $appid = $this->appid;
        $redirect_uri = urlencode('http://wechat.bls666.club/login.php');
        $scope = 'snsapi_userinfo';
        // $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid . "&redirect_uri=" . $redirect_uri . "&response_type=" . $response_type . "&scope=" . $scope . "&state=STATE#wechat_redirect";
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid . "&redirect_uri=" . $redirect_uri . "&response_type=code&scope=" . $scope . "&state=STATE#wechat_redirect";
        header('location:' . $url);
        // return $url;
    }
    // 拉取用户信息
    public function getUserDetail()
    {
        // 通过code换取网页授权access_token
        $code = $_GET['code'];
        $appid = $this->appid;
        $secret = $this->appsecret;
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $appid . "&secret=" . $secret . "&code=" . $code . "&grant_type=authorization_code";
        $access_token_arr = $this->jsonToArray($this->getData($url));
        $access_token = $access_token_arr['access_token'];
        $open_id = $access_token_arr['openid'];
        // 获取用户的详细信息
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $access_token . "&openid=" . $open_id . "&lang=zh_CN";
        return json_decode($this->getData($url), 1);
    }
    public function geiIp()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=" . $this->getAccessToken();
        return $this->getData($url);
    }
    // 创建二维码ticket：临时
    public function getQrCode()
    {
        // 1. 创建二维码ticket
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $this->getAccessToken();
        $postStr = '{"expire_seconds": 604800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": 888}}}';
        $ret = $this->getData($url, 'POST', $postStr);
        $arr = $this->jsonToArray($ret);
        $ticket = $arr['ticket'];
        // 2.通过ticket换取二维码
        // 提醒：1. TICKET记得进行UrlEncode
        // ticket正确情况下，http 返回码是200，是一张图片，可以直接展示或者下载。(不需要curl请求)
        $imgUrl = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . urlencode($ticket);
        // $imgUrl = $this->getData($url);
        return $imgUrl;
        // echo $imgUrl;
    }
    private function getJsApiTicket()
    {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode($this->get_php_file("jsapi_ticket.php"));
        if ($data->expire_time < time()) {
            $accessToken = $this->getAccessToken();
            // 如果是企业号用以下 URL 获取 ticket
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
            // https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = json_decode($this->httpGet($url));
            $ticket = $res->ticket;
            if ($ticket) {
                $data->expire_time = time() + 7000;
                $data->jsapi_ticket = $ticket;
                $this->set_php_file("jsapi_ticket.php", json_encode($data));
            }
        } else {
            $ticket = $data->jsapi_ticket;
        }
        return $ticket;
    }
    private function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }
    private function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    private function get_php_file($filename)
    {
        return trim(substr(file_get_contents($filename), 15));
    }
    private function set_php_file($filename, $content)
    {
        $fp = fopen($filename, "w");
        fwrite($fp, "<?php exit();?>" . $content);
        fclose($fp);
    }
    public function getSignPackage()
    {
        $jsapiTicket = $this->getJsApiTicket();
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $timestamp = time();
        $nonceStr = $this->createNonceStr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);
        $signPackage = array(
            "appId" => $this->appid,
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            "url" => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }

    public function getSignature(){
    	//获取jsapi_ticket的值，通过access_token来获取

    	$token=$this->getAccessToken();

    	$urljs="https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$token}&type=jsapi";

        $arr=$this->jsonToArray($this->getData($urljs));

        //return $arr['ticket'];

        //签名算法

		 $noncestr='Wm3WZYTPz0wzccnWsdferjhghterew46';

		 $timestamp=time();
		 
		 $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		 $url = $protocol.$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI];

		 $string = "jsapi_ticket={$arr['ticket']}&noncestr={$noncestr}&timestamp={$timestamp}&url={$url}";
        $signature = sha1($string);
        $signPackage = array(
            "appId" => 'wx58f393b77aeb9cf1',
            "nonceStr" => $noncestr,
            "timestamp" => $timestamp,
            "url" => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }
}  

 $wxObj = new check();
 $jspapi_ticket = $wxObj->getSignature();

 var_dump($jspapi);

 




?>

<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>jssdk</title>
	<script src='http://res.wx.qq.com/open/js/jweixin-1.2.0.js'></script>
	<script>
            wx.config({
					    debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
					    appId: 'wx58f393b77aeb9cf1', // 必填，公众号的唯一标识
					    timestamp: , // 必填，生成签名的时间戳
					    nonceStr: '', // 必填，生成签名的随机串
					    signature: '',// 必填，签名，见附录1
					    jsApiList: [] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
            });


            wx.ready(function(){
			    // config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready函数中。
			});

			wx.error(function(res){
			    // config信息验证失败会执行error函数，如签名过期导致验证失败，具体错误信息可以打开config的debug模式查看，也可以在返回的res参数中查看，对于SPA可以在这里更新签名。
			});
	</script>
     
</head>
<body>
	
</body>
</html>
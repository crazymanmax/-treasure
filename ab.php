<?php

    //跳转到bb.php页面；获取code

    $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx58f393b77aeb9cf1&redirect_uri=".urlencode('http://39.108.1.202/weixin/bb.php')."&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";

    header('location:' . $url);
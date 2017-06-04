<?php
/**
  * wechat php test
  */

//define your token
define("TOKEN", "xdl2017");
$wechatObj = new wechatCallbackapiTest();

if ($_GET['echostr'])
{
	$wechatObj->valid();
}
else
{
	$wechatObj->responseMsg();
}

//$wechatObj->responseMsg();
//$wechatObj->valid();

class wechatCallbackapiTest
{
	public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
        	echo $echoStr;
        	exit;
        }
    }

    public function responseMsg()
    {
		//get post data, May be due to the different environments
		//$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];//php5.6的可以用

		//引入数据库操作类
		require './db.php';

       $postStr =file_get_contents('php://input');

      	//extract post data
		if (!empty($postStr)){
                /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
                   the best way is to check the validity of xml by yourself */
                libxml_disable_entity_loader(true);
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $keyword = trim($postObj->Content);
                $time = time();
                $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
			     //首次关注，返回一段话	
			      $msgType = $postObj->MsgType;	
			      $event = $postObj->Event;

                 if($msgType=='event' && $event=='subscribe'){
                     
                    $msgType = "text";
                	$contentStr = "欢迎关注 南窗映雪！回复图文 可以看new新闻，回复美女 可以看美女大图,回复 音乐可以听歌";
                	
                	$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                	echo $resultStr;

                 }

                 //判断用户是否上传了地理位置
                  $latitude=$postObj->Location_X;
                  $longitude=$postObj->Location_Y;
                 if($msgType=='location'){
                    //$data1=$database->select('location',['id','name'],["name[=]" =>$fromUsername]);
                    
                    file_put_contents('../aa.txt',$latitude.'/'.$longitude.'/'.$fromUsername);
                    //if(count($data1)==1){
                       // $database->update('location',['latitude'=>$latitude,'longitude'=>$longitude,'time'=>$time],['id[=]'=>$data1[0]['id']]);
                    //}else{
                        $database->insert('location',['name'=>$fromUsername,'latitude'=>$latitude,'longitude'=>$longitude,'time'=>$time]);
                    //}*/
                    $msgType = "text";
                	$contentStr = "欢迎关注 南窗映雪！回复cxdz名称，可以查出地理位置";
                	
                	$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                	echo $resultStr;

                 }	

                 //判断用户发送的内容
                 switch($keyword){
                 	case '图文':
                       $textTpl ="<xml>
								<ToUserName><![CDATA[%s]]></ToUserName>
								<FromUserName><![CDATA[%s]]></FromUserName>
								<CreateTime>%s</CreateTime>
								<MsgType><![CDATA[news]]></MsgType>
								<ArticleCount>%s</ArticleCount>
								<Articles>";
						$data1=$database->select('picText',['title','date','url','description','picurl'],["id[<]" => 100]);		
						 
						  $data2='';
						  foreach($data1 as $v){
                               $data2.="<item>
										<Title><![CDATA[{$v['title']}]]></Title> 
										<Description><![CDATA[{$v['description']}]]></Description>
										<PicUrl><![CDATA[{$v['picurl']}]]></PicUrl>
										<Url><![CDATA[{$v['url']}]]></Url>
										</item>";
						  }		
						$textTpl.=$data2;		
								
						$textTpl.="</Articles>
								</xml>";
                         $count=count($data1);
					   	$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $count);
					   	echo $resultStr;		
                 	break;
                 	case '美女':
                          $textTpl ="<xml>
									<ToUserName><![CDATA[%s]]></ToUserName>
									<FromUserName><![CDATA[%s]]></FromUserName>
									<CreateTime>%s</CreateTime>
									<MsgType><![CDATA[image]]></MsgType>
									<Image>
									<MediaId><![CDATA[%s]]></MediaId>
									</Image>
									</xml>";
                          $mediaid='8b2d7aac7d5dc87173bc62a429545e18';
						  $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $mediaid);
					   	  echo $resultStr;			
                 	break;
                 	case '音乐':
                         $textTpl ="<xml>
									<ToUserName><![CDATA[%s]]></ToUserName>
									<FromUserName><![CDATA[%s]]></FromUserName>
									<CreateTime>%s</CreateTime>
									<MsgType><![CDATA[music]]></MsgType>
									<Music>
									<Title><![CDATA[%s]]></Title>
									<Description><![CDATA[%s]]></Description>
									<MusicUrl><![CDATA[%s]]></MusicUrl>
									<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>									
									</Music>
									</xml>";

						$title='音乐';
						$desc='美妙的music';
                        $musicurl='http://39.108.1.202/一次就好.mp3';
                        $hqurl='http://39.108.1.202/凉凉.mp3';
						$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $title,$desc,$musicurl,$hqurl);
					   	echo $resultStr;				
                 	break;
                 	default:
                            $msgType = "text";
		                	$contentStr = "欢迎来到微信的世界!";
		                	$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);

		                	$database->insert('text',['text'=>$keyword]);
		                	echo $resultStr;
                 	break;
                 }

				//提交文本时，自动回复文字			
				/*if(!empty( $keyword ))
                {
              		$msgType = "text";
                	$contentStr = "Welcome to wechat world!";
                	$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                	echo $resultStr;
                }else{
                	echo "Input something...";
                }*/

        }else {
        	echo "fgfdgdfsdfsdfdsf";
        	exit;
        }
    }

	private function checkSignature()
	{
        /*
        1）将token、timestamp、nonce三个参数进行字典序排序
        2）将三个参数字符串拼接成一个字符串进行sha1加密
        3）开发者获得加密后的字符串可与signature对比，标识该请求来源于微信
         */
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }

        $signature = $_GET["signature"];

        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
		$token = TOKEN;

        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );

		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
}

?>
<?php
namespace app\common\controller;
use think\Controller;
use think\Session;
use think\Config;
use think\Cache;
class ApiWeiXin {
	private $appId			= ''; //第三方用户唯一凭证
	private $appSecret 		= ''; //唯一凭证密钥appsecret
	public $errCode = null;
	public $errMsg = "";
	
	const STATE ="STATE";
	const WX_GET_TOKEN_URL ="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential";
	const WX_AUTHORIZE_URL ="https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=%s&state=%s#wechat_redirect";
	
	public function __construct($appId, $appSecret){
		$this->appId =config("wx_AppID");
		$this->appSecret = config("wx_AppSecret");
	}
		
	
	public function getSignPackage() {
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
				"appId"     => $this->appId,
				"nonceStr"  => $nonceStr,
				"timestamp" => $timestamp,
				"url"       => $url,
				"signature" => $signature,
				"rawString" => $string
		);
		return $signPackage;
	}
	
	private function createNonceStr($length = 16) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}
	
	private function getJsApiTicket() {
		$ticket = Cache::get("jsapi_ticket");
		if (empty($ticket)) {
			$accessToken = $this->getAccessToken();
			// 如果是企业号用以下 URL 获取 ticket
			// $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
			$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
			$res = json_decode($this->httpGet($url),true);
			$ticket = $res['ticket'];
			Cache::set("jsapi_ticket", $ticket,7000);
		} 
		return $ticket;
	}
	
	private function getAccessToken() {
		$access_token =Cache::get("access_token");
		if (empty($access_token)) {
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
			$data = json_decode($this->httpGet($url),true);
			$access_token = $data['access_token'];
			Cache::set("access_token", $access_token,7000);
		}
		return $access_token;
	}
	
	//页面授权
	
	public function getAuthCode($redirectUrl,$scope){
		$format = self::WX_AUTHORIZE_URL;
		$state = input('state', '');
		$code = input('code', '');
		if (empty($code) || $state !="future") {
			$url = sprintf($format, $this->appId, $redirectUrl,$scope,'future');
			header("location:".$url);
			exit;
		}
	}
	
	//获取openid
	public function getOpenid($code){
		$url='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->appId.'&secret='.$this->appSecret.'&code='.$code.'&grant_type=authorization_code';
		$result = json_decode($this->httpGet($url), true);
		if (!$result) return false;
		if (!isset($result['access_token'])) {
			$this->errCode = $result['errcode'];
			$this->errMsg = $result['errmsg'];
			return false;
		}
		return $result['openid'];
	}
	
	private function httpGet($url) {
		if (ini_get("allow_url_fopen") == "1") {			
			$result = file_get_contents($url); 
		} else {
			if(function_exists('curl_init')){
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
				$result =  curl_exec($ch);
				curl_close($ch);
			}else{
				$result = file_get_contents($url);
			}
		}		
		
		return $result;
	}
	
	
	
}
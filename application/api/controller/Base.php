<?php

namespace app\api\controller;

use think\Input;
use think\Config;
use think\Controller;
use think\Lang;
use think\Session;
use think\Model;
use think\Db;
use Think\Log;
use think\Cache;
class Base extends Controller
{
	const RET_CODE_OK = 1;
	const RET_CODE_ERR_UNKNOWN = - 1;
	const RET_CODE_ERR_ARGUMENT = - 2;
	const RET_CODE_ERR_GETDATA = - 3;
	const RET_CODE_ERR_OPDATA = - 4;
	const RET_CODE_ERR_EXEPTION = - 5;
	const RET_CODE_ERR_PRIVATE = - 6;
	const RET_CODE_ERR_AUTHTOKEN = - 7;
	const RET_CODE_ERR_NOTLOGIN = -8;
	const RET_CODE_ERR_CONFIG = 1001;
	const RET_CODE_ERR_EMS = 2001;
	const RET_CODE_ERR_EMPTYPHONE = 2002;
	const RET_CODE_ERR_PHONE = 2003;
	const RET_CODE_ERR_ERROREG = 2004;
	const RET_CODE_ERR_SUSSREG = 2005;
	const RET_CODE_ERR_EMSNO = 2006;
	const RET_CODE_ERR_NOREGISTER = 2007;

	/**
	 * 权限控制类
	 * @var Auth
	 */
	protected $auth = null;
	protected $params = array ();
	protected $arrErrorMsg = array (
			'1' => "操作成功",
			'-1' => '未知错误',
			'-2' => '参数不合格',
			'-3' => '获取数据失败',
			'-4' => '数据操作失败',
			'-5' => '数据操作异常',
			'-6' => '没有权限',
			'-7' => 'auth_token无效',
			'-8' => '用户未登录',
			'1001' => '获取配置失败' ,
			'2001' => '短信发送失败',
			'2002' => '手机号码为空',
			'2003' => '手机号码错误',
			'2004' => '用户注册失败',
			'2005' => '用户注册成功',
			'2006' => '验证码不一致',
			'2007' => '用户还没有注册',

	);
	
	public function __construct() {
		$data = file_get_contents ( 'php://input' );
		$this->params = json_decode ( $data, TRUE );
		$retType = $this->getReturnType ();
		config ( 'default_return_type', $retType );
/* 		$token = input ( 'token' );
		$checktoken = self::checkApiToken("456");
		if(!$checktoken){
			$data['code'] = '404';
			$data['msg'] = 'token无效';
			$return = self::returnData ( self::RET_CODE_ERR_ARGUMENT, 'token无效', '' );
			echo json_encode($return);
		} */
	}


	protected function getReturnType() {
		return "json";
	}

	
	protected function returnData($retCode, $retMsg, $data = "") {
		if ($retMsg === '') {
			if (isset ( $this->arrErrorMsg ['' . $retCode] )) {
				$retMsg = $this->arrErrorMsg ['' . $retCode];
			}
		}
		$ajaxResult = array (
				"code" => $retCode,
				"msg" => $retMsg 
		);
		if (! empty ( $data )) {
			$ajaxResult ["data"] = $data;
		}
		return $ajaxResult;
	}
	public function trimEmptyStr($val) {
		$cleanVal = '';
		if (is_array ( $val )) {
			$cleanVal = array_map ( 'trim', $val );
			$cleanVal = array_map ( 'htmlspecialchars', $cleanVal );
		} else if (is_string ( $val )) {
			$cleanVal = trim ( $val );
			$cleanVal = htmlspecialchars ( $cleanVal );
		} else {
			$cleanVal = $val;
		}
		return $cleanVal;
	}
	public function getParam($name, $value_while_empty = "") {
		$value = null;
		if (isset ( $this->params [$name] )) {
			$value = $this->trimEmptyStr ( $this->params [$name] );
		}
		
		if ($value === null || $value === "") {
			if (isset ( $_GET [$name] )) {
				$value = $this->trimEmptyStr ( $_GET [$name] );
			}
		}
		
		if ($value === null || $value === "") {
			if (isset ( $_POST [$name] )) {
				$value = $this->trimEmptyStr ( $_POST [$name] );
			}
		}
		
		if ($value === null || $value === "") {
			$value = $value_while_empty;
		}
		return $value;
	}
	
		
		protected function checkApiToken($apitoken){
			$accesstoken=cache::get($apitoken);//获取缓存
			    if($accesstoken){
			    	return $accesstoken;
			    }
			    return "";
		}

}

?>
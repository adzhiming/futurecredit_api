<?php
namespace app\index\controller;
use app\index\controller\Base;
use app\index\model\Agent as AgentModel;
use app\common\controller\FW;
use think\Controller;
use phpDocumentor\Reflection\DocBlock\Tags\Uses;
class Agent extends Base {
	protected $model = null;
	protected $relationSearch = true;
	
	public function __construct()
	{
		parent::__construct();
		//$this->model =model("Agent");
		
	}
    public function index() {

    	$rs = AgentModel::where ('id',1);
    	dump($rs);
    	
//     	 $rs = $this->model->where('id',1)->select();
//     	 $data['phone'] = "123456";
//     	 $data['salt'] = "123456";
//     	 $this->model->where('id',1)->update($data);

    }
		
		// 用户注册
	public function applyRegister() {
		$phone = input ( 'phone' );
		if (empty ( $phone )) {
			return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '请输入手机号码', '' );
		}
		if (! preg_match ( "/^1[2345789]{1}\d{9}$/", $phone )) {
			return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '手机号码格式错误', '' );
		}
		$check = AgentModel::get(['phone' => $phone]);
		if($check){
			return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '用户已存在,请直接登录', '' );
		}
		$data = array();
		$data['phone'] = $phone;
		$data['salt'] = createNoncestr(6);
		$data['password'] =  encryptPassword("123456",$data['salt']);
		$rs = AgentModel::create($data);
		if($rs){
			return $this->returnData ( self::RET_CODE_OK, '申请成功', '' );
		}
		else{
			return $this->returnData ( self::RET_CODE_ERR_OPDATA, '申请失败', '' );
		}
	}
	
	/*  用户登录 
	 * @data 2017-07-28
	 * */
	public function login() {
		if (request()->isPost()) {
			$phone = input ( 'phone' );
			$password = input ( 'password' );
			if (empty ( $phone )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '登录失败，请输入账号', '' );
			}
			if (empty ( $password )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '登录失败，请输入密码', '' );
			}
			$salt = AgentModel::get(['phone' => $phone]);
			$password = encryptPassword($password,$salt->salt);
			$where ['phone'] = $phone;
			$where ['password'] = $password;
			$checkLgoin = $this->model->all($where);
			if ($checkLgoin) {
				// 登录成功
				session ( 'agentInfo', $checkLgoin);
				return $this->returnData ( self::RET_CODE_OK, '登录成功，开始跳转', '' );
			} else {
				session ( 'agentInfo', "");
				return $this->returnData ( self::RET_CODE_ERR_OPDATA, '登录失败', '' );
    		}   		
    	}
    	FW::header403();
    }
    
    public function getUserInfoById(){
    	if (request()->isPost()) {
    		 AgentModel::get(['phone' => $phone]);
    	}
    	FW::header403();
    }
    
   
}

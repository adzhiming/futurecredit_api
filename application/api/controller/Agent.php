<?php

namespace app\api\controller;

use app\api\controller\Base;
use app\api\model\Agent as AgentModel;
use app\api\model\Bank_card;
use app\api\model\User_access_log;
use app\api\model\User;
use app\common\controller\FW;
use think\Controller;
use phpDocumentor\Reflection\DocBlock\Tags\Uses;
use think\Db;
use app\api\model\Agent_price;
use app\api\model\Agent_apply;
use think\Session;
use think\Config;
use think\Cache;
use app\common\controller\ApiWeiXin;
use app\common\controller\VerifyHelper;

class Agent extends Base {
	protected $model = null;
	protected $agent = null;
	protected $relationSearch = true;
	public function __construct() {
		parent::__construct ();
		$this->agent = new AgentModel ();
	}

	public function index() {
		/* $wx = new ApiWeiXin();
		$token = $wx->getAccessToken();
		$arr = json_decode($token,true);
		var_dump($arr); */
		$rs = AgentModel::where ( 'id', 1 );
	}


    /*
     * 帮客来服务平台手表总代理申请信贷代理
     */
    public function applyRegisterBkl()
    {
        $phone = input('phone');
        $real_code = session('code');
        $input_code = input('code');
        $aid = input('aid');
        if (empty($real_code))
        {
           // return $this->returnData(self::RET_CODE_ERR_EMS, '未发送验证码短信', '');
        }
        if (empty($phone))
        {
            return $this->returnData(self::RET_CODE_ERR_ARGUMENT, '请输入手机号码', '');
        }
        if (!preg_match("/^1[2345789]{1}\d{9}$/", $phone ))
        {
            return $this->returnData(self::RET_CODE_ERR_ARGUMENT, '手机号码格式错误', '');
        }
        if ($real_code != $input_code)
        {
            return $this->returnData(self::RET_CODE_ERR_EMSNO, '验证码不一致', '');
        }
        if (empty($aid))
        {
            return $this->returnData(self::RET_CODE_ERR_ARGUMENT, 'aid不能为空', '');
        }
        $data = array ();
        $data['amid'] = $aid;
        //查看该aid是否已经是信贷代理
        $agent_get_rs = \app\api\model\Agent::get($data);
        if ($agent_get_rs)
        {
            $create_phone = $agent_get_rs->phone;
            $id = $agent_get_rs->id;
            $url = Config::get('web_url') . 'agent/login';
            $return_data = array('phone'=>$create_phone, 'id'=>$id, 'url'=>$url, 'table'=>'agent');
            $params = array();
            $params['aid'] = $aid;
            $params['status'] = 2;
            $params['token'] = md5($aid.$params['status']."bklTofuturn2017");
            $params['phone'] = $create_phone;
            $result = bklChanggeStatus($params);
            $result = json_decode($result, true);
            if ($result['code']!=1)
            {
                $message = isset($result['data']['msg']) ? $result['data']['msg'] : (isset($result['msg']) ? $result['msg'] : $result['message']);
                return $this->returnData(self::RET_CODE_ERR_OPDATA, $message, '');
            }
            return $this->returnData(self::RET_CODE_OK, '您已开通信贷代理，开通手机号为' . $create_phone, $return_data);
        }
        
        $data = array ();
        $data['aid'] = $aid;
        //查看该aid是否已经申请信贷代理
        $agentapply_get_rs = db('agent_apply')->field('id,status,aid,phone')->where($data)->order('status asc')->limit(1)->select();
        if ($agentapply_get_rs)
        {
            if ($agentapply_get_rs[0]['status']==1)
            {
                $create_phone = $agentapply_get_rs[0]['phone'];
                $id = $agentapply_get_rs[0]['id'];
                $url = Config::get('web_url') . 'agent/login';
                $return_data = array('phone'=>$create_phone, 'id'=>$id, 'url'=>$url, 'table'=>'agent_apply');
                $params = array();
                $params['aid'] = $aid;
                $params['status'] = 1;
                $params['token'] = md5($aid.$params['status']."bklTofuturn2017");
                $params['phone'] = $create_phone;
                $result = bklChanggeStatus($params);
                $result = json_decode($result, true);
                if ($result['code']!=1)
                {
                    $message = isset($result['data']['msg']) ? $result['data']['msg'] : (isset($result['msg']) ? $result['msg'] : $result['message']);
                    return $this->returnData(self::RET_CODE_ERR_OPDATA, $message, '');
                }
                return $this->returnData(2, '您已经申请代理,正在审核，申请手机号为' . $create_phone, $return_data);
            }
        }


        $data = array ();
        $data['phone'] = $phone;
        //查看该手机号是否已经是信贷代理
        $agent_get_rs = \app\api\model\Agent::get($data);
        if ($agent_get_rs)
        {
            //若aid是空的，即该手机号申请代理时不是以手表总代理或者是手表拥有者的身份申请的
            if (empty($agent_get_rs->amid))
            {
                $id = $agent_get_rs->id;
                $where = array('id'=>$id);
                $update_data = array('amid'=>$aid, 'bkltype'=>'A');
                $update_rs = \app\api\model\Agent::update($update_data, $where);
                if ($update_rs)
                {
                    $url = Config::get('web_url') . 'agent/login';
                    $return_data = array('phone'=>$phone, 'id'=>$id, 'url'=>$url, 'table'=>'agent');
                    $params = array();
                    $params['aid'] = $aid;
                    $params['status'] = 2;
                    $params['token'] = md5($aid.$params['status']."bklTofuturn2017");
                    $params['phone'] = $phone;
                    $result = bklChanggeStatus($params);
                    $result = json_decode($result, true);
                    if ($result['code']!=1)
                    {
                        $message = isset($result['data']['msg']) ? $result['data']['msg'] : (isset($result['msg']) ? $result['msg'] : $result['message']);
                        return $this->returnData(self::RET_CODE_ERR_OPDATA, $message, '');
                    }
                    return $this->returnData(self::RET_CODE_OK, '该手机号已经注册，关联账号成功', $return_data);
                }
            }
            //若aid不为空，即该手机号申请代理时是以手表总代理或是手表拥有者的身份申请的
            else
            {
                return $this->returnData(self::RET_CODE_ERR_OPDATA, '该手机号已经被其它代理注册，请使用其他手机号码', '');
            }
        }
        //查看该手机号是否已经申请信贷代理
        $agentapply_get_rs = db('agent_apply')->field('id,status,aid')->where($data)->order('status asc')->limit(1)->select();
        if ($agentapply_get_rs)
        {
        	
            switch ($agentapply_get_rs[0]['status'])
            {
                //如果正在申请中
                case 1:
                    //若aid是空的，即该手机号申请代理时不是以手表总代理或者是手表拥有者的身份申请的
                    if (empty($agentapply_get_rs[0]['aid']))
                    {
                        $id = $agentapply_get_rs[0]['id'];
                        $where = array('id'=>$id);
                        $update_data = array('aid'=>$aid);
                        $update_rs = Agent_apply::update($update_data, $where);
                        if ($update_rs)
                        {
                            $url = Config::get('web_url') . 'agent/login';
                            $return_data = array('phone'=>$phone, 'id'=>$id, 'url'=>$url, 'table'=>'agent_apply');
                            $params = array();
                            $params['aid'] = $aid;
                            $params['status'] = 1;
                            $params['token'] = md5($aid.$params['status']."bklTofuturn2017");
                            $params['phone'] = $phone;
                            $result = bklChanggeStatus($params);
                            $result = json_decode($result, true);
                            if ($result['code']!=1)
                            {
                                $message = isset($result['data']['msg']) ? $result['data']['msg'] : (isset($result['msg']) ? $result['msg'] : $result['message']);
                                return $this->returnData(self::RET_CODE_ERR_OPDATA, $message, '');
                            }
                            return $this->returnData(2, '该手机号已经申请代理,正在审核，关联账号成功', $return_data);
                        }
                        else
                        {
                            return $this->returnData(self::RET_CODE_ERR_OPDATA, '该手机号已经申请代理，正在审核，关联账号失败，请重试', '');
                        }
                    }
                    else
                    {
                        return $this->returnData(self::RET_CODE_ERR_OPDATA, '该手机号已经被其它代理注册，请使用其他手机号码', '');
                    }
                    break;
                //如果申请成功
                case 2:
                    $data['aid'] = $aid;
                    $data['apply_time'] = date("Y-m-d H:i:s", time());
                    $data['status'] = 1;
                    $agentapply_create_rs = Agent_apply::create ( $data );
                    if ($agentapply_create_rs) {
                        $id = $agentapply_create_rs->id;
                        $url = Config::get('web_url') . 'agent/login';
                        $return_data = array('phone'=>$phone, 'id'=>$id, 'url'=>$url, 'table'=>'agent_apply');
                        $params = array();
                        $params['aid'] = $aid;
                        $params['status'] = 1;
                        $params['token'] = md5($aid.$params['status']."bklTofuturn2017");
                        $params['phone'] = $phone;
                        $result = bklChanggeStatus($params);
                        $result = json_decode($result, true);
                        if ($result['code']!=1)
                        {
                            $message = isset($result['data']['msg']) ? $result['data']['msg'] : (isset($result['msg']) ? $result['msg'] : $result['message']);
                            return $this->returnData(self::RET_CODE_ERR_OPDATA, $message, '');
                        }
                        return $this->returnData(2, '申请成功，请耐心等待审核', $return_data);
                    } else {
                        return $this->returnData(self::RET_CODE_ERR_OPDATA, '申请失败，请重试', '');
                    }
                    break;
                //如果申请失败
                case 3:
                    $data['aid'] = $aid;
                    $data['apply_time'] = date("Y-m-d H:i:s", time());
                    $data['status'] = 1;
                    $agentapply_create_rs = Agent_apply::create ( $data );
                    if ($agentapply_create_rs) {
                        $id = $agentapply_create_rs->id;
                        $url = Config::get('web_url') . 'agent/login';
                        $return_data = array('phone'=>$phone, 'id'=>$id, 'url'=>$url, 'table'=>'agent_apply');
                        $params = array();
                        $params['aid'] = $aid;
                        $params['status'] = 1;
                        $params['token'] = md5($aid.$params['status']."bklTofuturn2017");
                        $params['phone'] = $phone;
                        $result = bklChanggeStatus($params);
                        $result = json_decode($result, true);
                        if ($result['code']!=1)
                        {
                            $message = isset($result['data']['msg']) ? $result['data']['msg'] : (isset($result['msg']) ? $result['msg'] : $result['message']);
                            return $this->returnData(self::RET_CODE_ERR_OPDATA, $message, '');
                        }
                        return $this->returnData(2, '申请成功，请耐心等待审核', $return_data);
                    } else {
                        return $this->returnData(self::RET_CODE_ERR_OPDATA, '申请失败，请重试', '');
                    }
                    break;

            }
        }
        else{
        	$data['aid'] = $aid;
        	$data['apply_time'] = date("Y-m-d H:i:s", time());
        	$data['status'] = 1;
        	$agentapply_create_rs = Agent_apply::create ( $data );
        	if ($agentapply_create_rs) {
        		$id = $agentapply_create_rs->id;
        		$url = Config::get('web_url') . 'agent/login';
        		$return_data = array('phone'=>$phone, 'id'=>$id, 'url'=>$url, 'table'=>'agent_apply');
        		$params = array();
        		$params['aid'] = $aid;
        		$params['status'] = 1;
        		$params['token'] = md5($aid.$params['status']."bklTofuturn2017");
        		$params['phone'] = $phone;
        		$result = bklChanggeStatus($params);
        		$result = json_decode($result, true);
        		if ($result['code']!=1)
        		{
        			$message = isset($result['data']['msg']) ? $result['data']['msg'] : (isset($result['msg']) ? $result['msg'] : $result['message']);
        			return $this->returnData(self::RET_CODE_ERR_OPDATA, $message, '');
        		}
        		return $this->returnData(2, '申请成功，请耐心等待审核', $return_data);
        	} else {
        		return $this->returnData(self::RET_CODE_ERR_OPDATA, '申请失败，请重试', '');
        	}
        }
    }


    /*
     * 手表扫码开通信贷代理
     */
    public function createAgentBkl()
    {
        $phone = input('phone');
        $real_code = session('code');
        $input_code = input('code');
        $aid = input('aid');
        $mid = input('mid');
        $realname = input('realname');
        
        if (empty($real_code))
        {
            return $this->returnData(self::RET_CODE_ERR_EMS, '未发送验证码短信', '');
        }
        if (empty($phone))
        {
            return $this->returnData(self::RET_CODE_ERR_ARGUMENT, '请输入手机号码', '');
        }
        if (!preg_match("/^1[2345789]{1}\d{9}$/", $phone ))
        {
            return $this->returnData(self::RET_CODE_ERR_ARGUMENT, '手机号码格式错误', '');
        }
        if ($real_code != $input_code)
        {
            return $this->returnData(self::RET_CODE_ERR_EMSNO, '验证码不一致', '');
        }
        if (empty($aid))
        {
            return $this->returnData(self::RET_CODE_ERR_ARGUMENT, 'aid不能为空', '');
        }
        if (empty($mid))
        {
            return $this->returnData(self::RET_CODE_ERR_ARGUMENT, 'mid不能为空', '');
        }
        if (empty($realname))
        {
        	return $this->returnData(self::RET_CODE_ERR_ARGUMENT, '姓名不能为空', '');
        }
        $data = array ();
        $data['amid'] = $aid;

        //查看手表总代理是否已经开通信贷代理
        $agent_get_rs = \app\api\model\Agent::get($data);
        if (!$agent_get_rs)
        {
            return $this->returnData(self::RET_CODE_ERR_EXEPTION, '您的手表代理商还未开通该功能，您暂时不能申请此业务', '');
        }
        else
        {
            $parentid = $agent_get_rs->id;
        }
       //判断可发展下家数
        $is_create = can_create_sub_agent($parentid);
        if($is_create['return_code'] == 1){
        	return $this->returnData ( self::RET_CODE_ERR_ARGUMENT,$is_create['return_msg'], '' );
        }
        //查看该mid是否开通代理商
        $data = array ();
        $data['amid'] = $mid;
        $agent_get_rs = \app\api\model\Agent::get($data);
        if ($agent_get_rs)
        {
            $create_phone = $agent_get_rs->phone;
            return $this->returnData(self::RET_CODE_OK, '您已经开通代理商，开通手机号为' . $create_phone. '，请直接登录','');
        }


        //查看手机号是否已经开通信贷代理
        $data = array ();
        $data['phone'] = $phone;
        $agent_get_rs = \app\api\model\Agent::get($data);
        if ($agent_get_rs)
        {
            //若amid是空的，即该手机号申请代理时不是以手表拥有者的身份申请的
            if (empty($agent_get_rs->amid))
            {
                $id = $agent_get_rs->id;
                $where = array('id'=>$id);
                $update_time = date("Y-m-d H:i:s");
                $update_agent_data = array('amid'=>$mid, 'bkltype'=>'M','update_time'=>$update_time);
                //若手机号已经开通，且amid为空，关联账号
                $update_agent_rs = \app\api\model\Agent::update($update_agent_data, $where);
                if ($update_agent_rs)
                {
                    //若agent_aplly表中依然有该手机号，且status是正在申请中
                    $data['status'] = 1;
                    $agentapply_get_rs = db('agent_apply')->field('id,status,aid')->where($data)->order('status asc')->limit(1)->select();
                    if ($agentapply_get_rs)
                    {
                        $id = $agentapply_get_rs[0]['id'];
                        $where = array('id'=>$id);
                        $confirm_time = date("Y-m-d H:i:s");
                        $update_agentapply_data = array('status'=>2, 'confirm_time'=>$confirm_time, 'confirm_remark'=>'手表扫码申请已通过');
                        $update_agentapply_rs = Agent_apply::update($update_agentapply_data, $where);
                        if (!$update_agentapply_rs)
                        {
                            return $this->returnData(self::RET_CODE_OK, '该手机号已经注册，关联成功，发生了未知错误，请联系管理员', '');
                        }
                    }
                    return $this->returnData(self::RET_CODE_OK, '该手机号已经注册，关联成功，请直接登录','');
                }
                else
                {
                    return $this->returnData(self::RET_CODE_ERR_EXEPTION, '开通失败，发生未知错误', '');
                }


            }
            else
            {
                return $this->returnData(self::RET_CODE_OK, '开通失败，该手机号已经绑定其它商家', '');
            }
        }
        else
        {
            $data['amid'] = $mid;
            $data['bkltype'] = 'M';
            $data['parentid'] = $parentid;
            $data['level'] = 1;
            $salt = '';
            for ($i = 1; $i <= 6; $i++)
            {
                $salt .= chr(rand(97, 122));
            }
            $data['salt'] = $salt;
            $data["name"] = $realname;
            $data['password'] = encryptPassword ( "123456" . $salt  );
            $agent_create_rs = \app\api\model\Agent::create($data);
            if ($agent_create_rs)
            {
                //若agent_aplly表中依然有该手机号，且status是正在申请中
                $where = array('phone'=>$phone, 'status'=>1);
                $agentapply_get_rs = db('agent_apply')->field('id,status,aid')->where($where)->order('status asc')->limit(1)->select();
                if ($agentapply_get_rs)
                {
                    $id = $agentapply_get_rs[0]['id'];
                    $where = array('id'=>$id);
                    $confirm_time = date("Y-m-d H:i:s");
                    $update_agentapply_data = array('status'=>2, 'confirm_time'=>$confirm_time, 'confirm_remark'=>'手表扫码申请已通过');
                    $update_agentapply_rs = Agent_apply::update($update_agentapply_data, $where);
                    if (!$update_agentapply_rs)
                    {
                        return $this->returnData(self::RET_CODE_OK, '开通代理成功，发生了未知错误，请联系管理员', '');
                    }
                }
                if($is_create['return_code'] == 2){
                	return $this->returnData ( self::RET_CODE_OK,$is_create['return_msg'], '' );
                }
                return $this->returnData(self::RET_CODE_OK, '开通代理成功,请直接登录', '');
            }
            else
            {
                return $this->returnData(self::RET_CODE_ERR_EXEPTION, '开通失败，发生未知错误', '');
            }
        }
    }


    /*
     * 手表点击信用卡  返回链接
     */
    public function returnUrl()
    {
        $type = input('type');
        $aid = input('aid');
        if (empty($type))
        {
            return $this->returnData(self::RET_CODE_ERR_ARGUMENT, '参数不合格', '');
        }
        switch ($type)
        {
            case 1:
                $url = Config::get('web_url') . 'user/homepage';
                $data = array('url'=>$url);
                return $this->returnData(self::RET_CODE_OK, '操作成功', $data);
                break;
            case 2:
                if (empty($aid))
                {
                    return $this->returnData(self::RET_CODE_ERR_ARGUMENT, '参数不合格', '');
                }
                $url = Config::get('web_url') . 'user/homepage/aid/' . $aid;
                $data = array('url'=>$url);
                return $this->returnData(self::RET_CODE_OK, '操作成功', $data);
                break;
        }
    }


    /*
     * 通过mid查询用户是否有开通代理
     */
    public function queryAgentByMid()
    {
        $mid = input('mid');
        $aid = input('aid');
        $type = input('type');
        $sn = input('sn');
        if (empty($mid) || empty($aid) || empty($type))
        {
            return $this->returnData(self::RET_CODE_ERR_ARGUMENT, '参数不合格', '');
        }
        $judge_data = array('amid'=>$mid);
        $agent_get_rs = AgentModel::get($judge_data);
        if ($agent_get_rs)
        {
            $id = $agent_get_rs->id;
            $phone = $agent_get_rs->phone;
            $salt = $agent_get_rs->salt;
            $code = md5 ($id . $phone . $salt);
            $url = Config::get('web_url') . 'user/index?id=' . $id . '&code=' . $code . '&type=' . $type;
            $data = array('url'=>$url);
            return $this->returnData(0, '该用户已经开通代理商', $data);
        }
        $url = Config::get('bkl_url_base') . '/admin.php/watch/applyfrwatch/sn/'.$sn.'/aid/' . $aid . '/mid/' . $mid."/token/".md5($sn.$aid.$mid."futurecredit2017");
        $data = array('url'=>$url);
        return $this->returnData(-1, '该用户未开通代理商', $data);
    }


	// 用户注册
	public function applyRegister() {
		$phone = input ( 'phone' );
		$real_code = session('code');
		$input_code = input('code');
		$password = input('password');
		$realname = input('realname');
		if (empty ( $phone )) {
			return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '请输入手机号码', '' );
		}
		if (empty ( $password )) {
			return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '请输入登录密码', '' );
		}
		if (! preg_match ( "/^1[2345789]{1}\d{9}$/", $phone )) {
			return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '手机号码格式错误', '' );
		}
		
		if ($real_code != $input_code)
		{
			return $this->returnData(self::RET_CODE_ERR_EMSNO, '验证码不一致', '');
		}
		if (empty ( $realname )) {
			return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '请输入名称', '' );
		}
		$check = AgentModel::get ( [ 
				'phone' => $phone 
		] );
		if ($check) {
			return $this->returnData ( self::RET_CODE_ERR_NOTLOGIN, '用户已存在,请直接登录', '' );
		}
		$check = Agent_apply::get ( [
				'phone' => $phone,
				'status' =>1
		] );

		if ($check) {
			return $this->returnData ( self::RET_CODE_ERR_NOTLOGIN, '你已经申请过，请耐心等待审核', '' );
		}
		$data = array ();
		$password = empty($password)?"123456":$password;
		$data ['phone'] = $phone;
		$data['password'] = base64_encode($password);
		$data["name"] = $realname;
		$data ['apply_time'] = date ( "Y-m-d H:i:s", time () );
		$data ['status'] = 1;
		$rs = Agent_apply::create ( $data );
		if ($rs) {
			return $this->returnData ( self::RET_CODE_OK, '申请成功，请耐心等待审核', '' );
			// $this->success ( "申请成功", "web/agent/index", $data ['phone'] );
		} else {
			return $this->returnData ( self::RET_CODE_ERR_OPDATA, '申请失败', '' );
		}
	}
	
	// 编辑用户
	public function editUserCommit() {
		if (request ()->isPost ()) {
			$id = input ( 'id' );
			if (empty ( $id )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, 'id参数不能为空', '' );
			}
			$tokenaid = input ( 'tokenaid' );
			$token = input ( 'token' );
			/*  if($this->checkToken($tokenaid, $token,&$recode,&$redate)){
			 if(!$code){
			return $this->returnData ( self::RET_CODE_ERR_ARGUMENT,$date, '' );
			}
			} */
			if($id == $tokenaid){
				$map = "id = '{$tokenaid}'";
			}
			else{
				$map = "id = '{$id}' and parentid ='{$tokenaid}'";
			}
			$res = AgentModel::where ($map )->field ( "id,phone,salt" )->find ();
			if(!$res){
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT,"非法操作", '' );
			}
			$res = AgentModel::where ( "id", "{$tokenaid}" )->field ( "id,phone,salt" )->find ();
			if (empty ( $res ['id'] )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT,"非法操作", '' );
			}
			$retoken = md5($res["id"].$res['phone'].$res['salt'].date("ymd",time()));
			if($token !=$retoken){
				return $this->returnData ( self::RET_CODE_ERR_AUTHTOKEN,"token无效", '' );
			}
				
			$info = AgentModel::where ( "id", $id )->field ( "salt" )->find ();
			if (empty ( $info )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '非法操作，更新失败', '' );
			}
			$updateData = array ();
			if (! empty ( input ( 'level' ) ) && null != input ( 'level' )) {
				$updateData ['level'] = input ( 'level' );
			}
			if (! empty ( input ( 'name' ) ) && null != input ( 'name' )) {
				$updateData ['name'] = input ( 'name' );
			}
			if (! empty ( input ( 'phone' ) ) && null != input ( 'phone' )) {
				$updateData ['phone'] = input ( 'phone' );
			}
			if (! empty ( input ( 'id_card' ) ) && null != input ( 'id_card' )) {
				$updateData ['id_card'] = input ( 'id_card' );
			}
			if (! empty ( input ( 'card_no' ) ) && null != input ( 'card_no' )) {
				$updateData ['card_no'] = input ( 'card_no' );
			}
			if (! empty ( input ( 'card_name' ) ) && null != input ( 'card_name' )) {
				$updateData ['card_name'] = input ( 'card_name' );
			}
			if (! empty ( input ( 'card_sub_name' ) ) && null != input ( 'card_sub_name' )) {
				$updateData ['card_sub_name'] = input ( 'card_sub_name' );
			}
			if (! empty ( input ( 'email' ) ) && null != input ( 'email' )) {
				$updateData ['email'] = input ( 'email' );
			}
			if (input ( 'password' ) != input ( 'repassword' )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '两次输入的密码不一样，请重新输入', '' );
			}
			if (! empty ( input ( 'password' ) ) && null != input ( 'password' )) {
				$updateData ['password'] = encryptPassword ( input ( 'password' ), $info ['salt'] );
			}
			if (! empty ( input ( 'last_login_time' ) ) && null != input ( 'last_login_time' )) {
				$updateData ['last_login_time'] = input ( 'last_login_time' );
			}
			if (! empty ( input ( 'last_login_time' ) ) && null != input ( 'last_login_time' )) {
				$updateData ['last_login_time'] = input ( 'last_login_time' );
			}
			if (! empty ( input ( 'last_login_ip' ) ) && null != input ( 'last_login_ip' )) {
				$updateData ['last_login_ip'] = input ( 'last_login_ip' );
			}
			if (! empty ( input ( 'remark' ) ) && null != input ( 'remark' )) {
				$updateData ['remark'] = input ( 'remark' );
			}
			if (! empty ( input ( 'status' ) ) && null != input ( 'status' )) {
				$updateData ['status'] = input ( 'status' );
			}
			if (! empty ( input ( 'is_deleted' ) ) && null != input ( 'is_deleted' )) {
				$updateData ['is_deleted'] = input ( 'is_deleted' );
			}
			
			if (empty ( $updateData )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '请提交更新数据', '' );
			}
			$res = $this->agent->editUser ( $updateData, $id );
			if ($res) {
				$res ['id'] = $id;
				return $this->returnData ( self::RET_CODE_OK, '更新成功', $res );
			}
			return $this->returnData ( self::RET_CODE_ERR_OPDATA, '数据更新失败', '' );
		}
		FW::header403 ();
	}
	
	/*
	 * 用户登录 @data 2017-07-28
	 */
	public function login() {
		if (request ()->isPost ()) {
			$phone = trim(input ( 'post.phone' ));
			$password =trim(input ( 'post.password' ));
			if (empty ( $phone )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '登录失败，请输入账号', '' );
			}
			if (empty ( $password )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '登录失败，请输入密码', '' );
			}
			$apply = Agent_apply::get ( [ 
					'phone' => $phone,
					'status' => '1' 
			] );
			
			if (! empty ( $apply )) {
				return $this->returnData ( self::RET_CODE_ERR_OPDATA, '审核中，请耐心等待审核！', '' );
			}
			$salt = AgentModel::get ( [ 
					'phone' => $phone,
					 'is_deleted' =>0
			] );
			if (empty ( $salt )) {
				return $this->returnData ( self::RET_CODE_ERR_OPDATA, '用户名不存在', '' );
			}
			
			$password = encryptPassword ( $password . $salt ['salt'] );
			$where ['phone'] = $phone;
			$where ['password'] = $password;
			$checkLgoin = AgentModel::where ( $where )->field ( 'id,phone,name,level,level as lev,salt' )->find ();
			if ($checkLgoin) {
				$info = $checkLgoin->toArray ();
				//判断上级是否被删除
				/* $id_str = getAllParentAgentIds($info["id"]);
				if($id_str !=''){
					 $del=  db("agent")->where("id in ({$id_str}) and is_deleted = 1")->find();
					 if($del){
					 	 return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '登录失败，因您所属上级代理账号已被冻结，您的账号同时受到冻结影响！', '' );
					 }
				} */
				
				$data["last_login_ip"] = $_SERVER["REMOTE_ADDR"];//用户登录ip
				AgentModel::update($data,$where);
				// 登录成功
				
				$info["token"] = md5($info["id"].$phone.$info['salt'].date("ymd",time()));
				unset($info['salt']);
				//Session::set( 'agentinfo', $info,time()+60);
				session ( 'agentinfo', $info);
				cookie("login_id",$info['id'],7200);
				cookie("login_token",$info["token"],7200);
				return $this->returnData ( self::RET_CODE_OK, '登录成功', $info );
			} else {
				session ( 'agentloginid', "");
				return $this->returnData ( self::RET_CODE_ERR_OPDATA, '登录失败,用户名或密码错误', '' );
			}
		}
		FW::header403 ();
	}
	
	/*
	 * 退出登录
	* 2017.8.3
	* */
	public  function logout(){
		$phone=session('agentinfo',null);
		cookie('login_id',null);
		cookie('login_token',null);
		if($phone == null){
			return $this->returnData(self::RET_CODE_OK,'退出成功','');
		}else{
			return $this->returnData(self::RET_CODE_ERR_GETDATA,'退出失败','');
		}
	}
	
	// 新增我的代理商
	public function addMyAgent() {
		if (request ()->isPost ()) {
			$id = input ( 'id' );
			$rs = AgentModel::where ( "id", "{$id}" )->field ( "id,phone,name,salt" )->find ();
			if (empty ( $rs )) {
				return $this->returnData ( self::RET_CODE_ERR_GETDATA, '账户不存在', '' );
			}
			return $this->returnData ( self::RET_CODE_OK, '成功', $rs );
		}
	}
	
	// 删除我的代理商
	public function delMyAgent() {
		if (request ()->isPost ()) {
			$id = input ( 'id' );
			$tokenaid = input ( 'tokenaid' );
			$token = input ( 'token' );
			$map = "id = '{$id}' and parentid ='{$tokenaid}'";
			$res = AgentModel::where ($map )->field ( "id,phone" )->find ();
			if(!$res){
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT,"非法操作", '' );
			}
			$res = AgentModel::where ( "id", "{$tokenaid}" )->field ( "id,phone,salt" )->find ();
			if (empty ( $res ['id'] )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT,"非法操作", '' );
			}
			$retoken = md5($res["id"].$res['phone'].$res['salt'].date("ymd",time()));
			if($token !=$retoken){
				return $this->returnData ( self::RET_CODE_ERR_AUTHTOKEN,"token无效", '' );
			}
			if(empty(trim($id))){
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '参数不能为空', '' );
			}
			$data["is_deleted"] = 1;
			$where['id'] = $id;
			$res = AgentModel::update($data,$where);
			if ($res) {
				return $this->returnData ( self::RET_CODE_OK, '删除成功', $res );
			}
			return $this->returnData ( self::RET_CODE_ERR_OPDATA, '数据更新失败', '' );
		}
		FW::header403 ();
	}
	
	// 提交新增我的代理商处理
	public function addMyAgentCommit() {
		if (request ()->isPost ()) {
			$id = input ( 'id' );
			$rs = AgentModel::where ( "id", "{$id}" )->field ( "id,phone,name,salt,level as lev,level" )->find ();
			if (empty ( $rs )) {
				return $this->returnData ( self::RET_CODE_ERR_GETDATA, '账户不存在', '' );
			}
			if ($rs ['lev'] > 2) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '对不起，你不能再发展下级推广员', '' );
			}

			$addData = array ();
			if (empty ( input ( 'name' ) ) or null == input ( 'name' )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '请输入姓名', '' );
			}
			if (empty ( input ( 'phone' ) ) or null == input ( 'phone' )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '请输入手机号码', '' );
			}
			if (empty ( input ( 'password' ) ) or null == input ( 'password' )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '请输入登录密码', '' );
			}
			if (empty ( input ( 'repassword' ) ) or null == input ( 'repassword' )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '请输入确认密码', '' );
			}
			if (input ( 'password' ) != input ( 'repassword' )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '两次输入的密码不一样，请重新输入', '' );
			}
			$where = array();
			$where["status"] = 1;
			$where["phone"] = trim(input ( 'phone' ) );
			$has = Agent_apply::where ($where)->field ( "id,phone" )->find ();
			if (! empty ( $has )) {
				return $this->returnData ( self::RET_CODE_ERR_GETDATA, '该电话号码已经在申请中', '' );
			}
			
			$has = AgentModel::where ( "phone", trim(input ( 'phone' ) ))->field ( "id,phone,name,salt,level" )->find ();
			if (! empty ( $has )) {
				return $this->returnData ( self::RET_CODE_ERR_GETDATA, '电话号已存在', '' );
			}
			//判断可发展下家数]
			$is_create = can_create_sub_agent($id);
			
			if($is_create['return_code'] == 1){
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT,$is_create['return_msg'], '' );
			}
			$salt = createNoncestr ( 6 );
			$level = $rs ['lev'] + 1;
			$addData ['parentid'] = $rs ['id'];
			$addData ['level'] = $level;
			$addData ['salt'] = $salt;
			$addData ['name'] = input ( 'name' );
			$addData ['phone'] = input ( 'phone' );
			$addData ['password'] = encryptPassword (trim(input ( 'password' )), $salt );
			$res = $this->agent->addAgent ( $addData );
			if ($res) {
				if($is_create['return_code'] == 2){
					return $this->returnData ( self::RET_CODE_OK,$is_create['return_msg'], '' );
				}
				return $this->returnData ( self::RET_CODE_OK, '添加成功', $res );
			}
			return $this->returnData ( self::RET_CODE_ERR_OPDATA, '添加失败', '' );
		}
	}
	
	// 根据电话号码获取代理商信息
	public function getAgentInfoById() {
		if (request ()->isPost ()) {
			$id = input ( 'id' );
			$tokenaid = input ( 'tokenaid' );
			$token = input ( 'token' );
		   /*  if($this->checkToken($tokenaid, $token,&$recode,&$redate)){
		    	 if(!$code){
		    	 	return $this->returnData ( self::RET_CODE_ERR_ARGUMENT,$date, '' );
		    	 }
		    } */
		   if($id == $tokenaid){
				$map = "id = '{$tokenaid}'";
			}
			else{
				$map = "id = '{$id}' and parentid ='{$tokenaid}'";
			}
			$res = AgentModel::where ($map )->field ( "id,phone,salt" )->find ();
			if(!$res){
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT,"非法操作", '' );
			}
			$res = AgentModel::where ( "id", "{$tokenaid}" )->field ( "id,phone,salt" )->find ();
			if (empty ( $res ['id'] )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT,"非法操作", '' );
			}
			$retoken = md5($res["id"].$res['phone'].$res['salt'].date("ymd",time()));
			if($token !=$retoken){
				return $this->returnData ( self::RET_CODE_ERR_AUTHTOKEN,"登录超时，请重新登录", '' );
			}
			
			if (empty ( trim ( $id ) )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '登录账号不能为空', '' );
			}
			
			
			$info = $this->agent->getAgentInfoById ($id );
			if ($info) {
				return $this->returnData ( self::RET_CODE_OK, '获取成功', $info );
			} else {
				return $this->returnData ( self::RET_CODE_ERR_OPDATA, '获取失败', array () );
			}
		}
		FW::header403 ();
	}
	
	// 获取我的代理商列表
	public function mySelfAgenList() {
		if (request ()->isPost ()) {
			$id = input ( 'id' );
			$keyword = input ( 'keyword' );
			$page = input ( 'page' );
			if (isset ( $page ) && null !== $page) {
				$currentpage = $page;
			} else {
				$currentpage = 1;
			}
			$pagesize = empty ( input ( "pagesize" ) ) ? 8 : input ( "pagesize" );
				
			if (empty ( trim ( $id ) )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '参数不能为空', '' );
			}
			$agentinfo = AgentModel::get ( [
					'id' => $id,
					'is_deleted' => 0
					] );
			if (empty ( $agentinfo )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '非法操作', '' );
			}
			
		    $where ['parentid'] = $agentinfo ['id'];
			if(trim($keyword)){
				$where ['name'] = array("like","%{$keyword}%");
			}
			$where ['is_deleted'] = 0;
				
			$agentlist = AgentModel::all ( $where );
	
			$total = count ( $agentlist );
			$page_count = ceil ( $total / $pagesize );
			if ($page > $page_count) {
				$currentpage = $page_count;
			}
			$options = [
			'page' => $currentpage,
			'path' => url ( 'index' )
			];
			$data = array ();
			$listdata = array ();
			$total = 0;
			$current_page = 0;
			$page_count = 0;
			$list = \think\Db::name ( 'agent' )->order ( 'id' )->field ( "id,parentid,if(name ='','未知',name) name ,phone,salt,case `level` when 0 then '特级' when 1 then '一级' when 2 then '二级' when 3 then '三级' else '未知' end as lev" )->where ( $where )->paginate ( $pagesize, false, $options );
			//echo db()->getLastSql();
			if ($list) {
				$rs = $list->toArray ();
				$total = $rs ['total'];
				$pagesize = $rs ['per_page'];
				$current_page = $rs ['current_page'];
				$page_count = $rs ['last_page'];
				$listdata = $rs ['data'];
				foreach ( $listdata as $k => $v ) {
					$code = md5 ( $v ['id'] . $v ['phone'] . $v ['salt'] );
					$listdata [$k] ['agenter'] = $agentinfo ['name'];
					$listdata [$k] ['cardurl'] = config ( "web_url" ) . "user/index?id={$v['id']}&code={$code}&type=1";
					$listdata [$k] ['loanurl'] = config ( "web_url" ) . "user/index?id={$v['id']}&code={$code}&type=2";
				}
			}
			$data ['data'] = $listdata;
			$data ['total'] = $total;
			$data ['pagesize'] = $pagesize;
			$data ['current_page'] = $currentpage;
			$data ['page_count'] = $page_count;
			/*
			 * echo "<pre>"; print_r($data); echo "</pre>";
			*/
			// echo AgentModel::getLastSql();die;
			return $this->returnData ( self::RET_CODE_OK, '成功', $data );
		}
		FW::header403 ();
	}
	
	// 获取我的代理商以及下级的代理商列表
	public function myAgenList() {
		if (request ()->isPost ()) {
			$id = input ( 'id' );
			$keyword = input ( 'keyword' );
			$page = input ( 'page' );
			if (isset ( $page ) && null !== $page) {
				$currentpage = $page;
			} else {
				$currentpage = 1;
			}
			$pagesize = empty ( input ( "pagesize" ) ) ? 9 : input ( "pagesize" );
			
			if (empty ( trim ( $id ) )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '参数不能为空', '' );
			}
			$agentinfo = AgentModel::get ( [ 
					'id' => $id,
					'is_deleted' => 0 
			] );
			if (empty ( $agentinfo )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '非法操作', '' );
			}
/* 			$AllsubAgentIds = getAllSubAgentIds ( $id );
			$where = array ();
			$subAgentIdstr ="";
			if($AllsubAgentIds){
				foreach ($AllsubAgentIds as $k=>$v){
					 if($subAgentIdstr ==""){
					 	 $subAgentIdstr =$v['id'];
					 }
					 else{
					 	$subAgentIdstr =$subAgentIdstr.",".$v['id'];
					 }
				}
			}
			
			$where ['id'] = array (
					'in',
					$subAgentIdstr
			); */
			
			$where ['parentid'] = $id;
			if(trim($keyword)){
				$where ['name'] = array("like","%{$keyword}%");
			}
			$where ['is_deleted'] = 0;
			
			$agentlist = AgentModel::all ( $where );
	
			$total = count ( $agentlist );
			$page_count = ceil ( $total / $pagesize );
			if ($page > $page_count) {
				$currentpage = $page_count;
			}
			$options = [ 
					'page' => $currentpage,
					'path' => url ( 'index' ) 
			];
			$data = array ();
			$listdata = array ();
			$total = 0;
			$current_page = 0;
			$page_count = 0;
			$list = \think\Db::name ( 'agent' )->order ( 'id' )->field ( "*,case `level` when 0 then '特级' when 1 then '一级' when 2 then '二级' when 3 then '三级' else '未知' end as lev" )->where ( $where )->paginate ( $pagesize, false, $options );
			// echo db()->getLastSql();
			if ($list) {
				$rs = $list->toArray ();
				$total = $rs ['total'];
				$pagesize = $rs ['per_page'];
				$current_page = $rs ['current_page'];
				$page_count = $rs ['last_page'];
				$listdata = $rs ['data'];
				foreach ( $listdata as $k => $v ) {
					$has = db("agent")->where("parentid = '{$v['id']}' and is_deleted =0")->find();
					if($has){
						$listdata [$k] ['has_sub'] = 1;
					}
					else{
						$listdata [$k] ['has_sub'] = 0;
					}
					$code = md5 ( $v ['id'] . $v ['phone'] . $v ['salt'] );
					$pargentInfo = getAgentInfoById($v ['parentid']);
					$listdata [$k] ['agenter'] = $pargentInfo ['name'];
					$listdata [$k] ['cardurl'] = config ( "web_url" ) . "user/index?id={$v['id']}&code={$code}&type=1";
					$listdata [$k] ['loanurl'] = config ( "web_url" ) . "user/index?id={$v['id']}&code={$code}&type=2";
				}
			}
			$data ['data'] = $listdata;
			$data ['total'] = $total;
			$data ['pagesize'] = $pagesize;
			$data ['current_page'] = $currentpage;
			$data ['page_count'] = $page_count;
			/*
			 * echo "<pre>"; print_r($data); echo "</pre>";
			 */
			// echo AgentModel::getLastSql();die;
			return $this->returnData ( self::RET_CODE_OK, '成功', $data );
		}
		FW::header403 ();
	}
	
	// 获取我的用户列表
	public function myUserList() {
		if (request ()->isPost ()) {
			$id = input ( 'id' );
			$keyword = input ( 'keyword' );
			$page = input ( 'page' );
			$pagesize = input ( 'pagesize' );
			if (isset ( $page ) && null !== $page) {
				$page = $page;
			} else {
				$page = 1;
			}
			
			if (empty ( trim ( $id ) )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '参数不能为空', '' );
			}
			$agentinfo = AgentModel::get ( [ 
					'id' => $id,
					'is_deleted' => 0
			] );
			if (empty ( $agentinfo )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '非法操作', '' );
			}
			$agentlist = User::getUserListByAgentID ( $agentinfo ['id'], $keyword, $page, $pagesize );
			return $this->returnData ( self::RET_CODE_OK, '成功', $agentlist );
		}
		FW::header403 ();
	}
	
	// 我的用户访问记录
	public function myUserAccessLog() {
		if (request ()->isPost ()) {
			$id = input ( 'id' );
			$page = input ( 'page' );
			$sday = date('Y-m-d',strtotime("-7 day"));
			$today = date('Y-m-d',time());
			$stime = empty(trim(input ( 'stime' )))?$sday:trim(input ( 'stime' ));
			$etime = empty(trim(input ( 'etime' )))?$today:trim(input ( 'etime' ));
			if (isset ( $page ) && null !== $page) {
				$currentpage = $page;
			} else {
				$currentpage = 1;
			}
			$pagesize = empty ( input ( "pagesize" ) ) ? 10 : input ( "pagesize" );
			
			if (empty ( trim ( $id ) )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '参数不能为空', '' );
			}
			$agentinfo = AgentModel::get ( [ 
					'id' => $id,
					'is_deleted' => 0 
			] );
			if (empty ( $agentinfo )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '非法操作', '' );
			}
			$where= array();
			$where['a.create_time'] = array("between",array($stime." 00:00:00",$etime." 23:59:59"));
			$agentlist = User::getUserAccessLogByAgentID ( $id, $page, $pagesize,$where );
			
			return $this->returnData ( self::RET_CODE_OK, '成功', $agentlist );
		}
		FW::header403 ();
	}
	
	// 我的用户申请信用卡记录
	public function myUserApplyCardLog() {
		if (request ()->isPost () || request ()->isAjax ()) {
			$id = input ( 'id' );
			$sday = date('Y-m-d',strtotime("-7 day"));
			$today = date('Y-m-d',time());
			$stime = empty(trim(input ( 'stime' )))?$sday:trim(input ( 'stime' ));
			$etime = empty(trim(input ( 'etime' )))?$today:trim(input ( 'etime' ));
			$page = input ( 'page' );
			$pagesize = input ( 'pagesize' );
			if (isset ( $page ) && null !== $page) {
				$page = $page;
			} else {
				$page = 1;
			}
			
			if (empty ( trim ( $id ) )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '参数不能为空', '' );
			}
			$agentinfo = AgentModel::get ( [ 
					'id' => $id,
					'is_deleted' => 0 
			] );
			if (empty ( $agentinfo )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '非法操作', '' );
			}
			if(strtotime($stime) > strtotime($etime)){
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '开始时间不能大于结束时间', '' );
			}
			$where= array();
			$where['a.apply_time'] = array("between",array($stime." 00:00:00",$etime." 23:59:59"));
			$agentlist = User::getUserApplyCardListByAgentID ( $id, $page, $pagesize,$where );
			
			return $this->returnData ( self::RET_CODE_OK, '成功', $agentlist );
		}
		FW::header403 ();
	}
	
	// 我的用户申请贷款记录
	public function myUserApplyLoanLog() {
		if (request ()->isPost () || request ()->isAjax ()) {
			$id = input ( 'id' );
			$page = input ( 'page' );
			$pagesize = input ( 'pagesize' );
			$sday = date('Y-m-d',strtotime("-7 day"));
			$today = date('Y-m-d',time());
			$stime = empty(trim(input ( 'stime' )))?$sday:trim(input ( 'stime' ));
			$etime = empty(trim(input ( 'etime' )))?$today:trim(input ( 'etime' ));
			if (isset ( $page ) && null !== $page) {
				$page = $page;
			} else {
				$page = 1;
			}
			
			if (empty ( trim ( $id ) )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '参数不能为空', '' );
			}
			$agentinfo = AgentModel::get ( [ 
					'id' => $id,
					'is_deleted' => 0 
			] );
			if (empty ( $agentinfo )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '非法操作', '' );
			}
			$where= array();
			$where['a.apply_time'] = array("between",array($stime." 00:00:00",$etime." 23:59:59"));
			$agentlist = User::getUserApplyLoanListByAgentID ( $id, $page, $pagesize,$where);
			
			return $this->returnData ( self::RET_CODE_OK, '成功', $agentlist );
		}
		FW::header403 ();
	}
	/*
	 * 代理商报价单
	 * 总代的报价单在超过授权的时候生产
	 * 一级代理的报价单是根据总代的报价单生成自己的报价单，
	 * 如果自己的报价单自己没改过的情况下，上级修改了，那么自己的报价单也跟着修改。
	 * 如果自己编辑过的情况下，上级修改报价单，对自己的报价单不影响。依此类推。
	 */
	public function editSysPrice() {
		if (request ()->isPost () || request ()->isAjax ()) {
			$id = input ( 'id' );
			$agent_id = input ( 'agent_id' );
			$agentinfo = $this->agent->getAgentInfoById ( $agent_id );
			if (empty ( $agentinfo ['id'] )) {
				return $this->returnData ( self::RET_CODE_ERR_OPDATA, '数据更新失败,非法操作', '' );
			}
			if (empty ( $id )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '缺少参数:id' );
			}
			
			$updateData = array ();
			/* if (( int ) $agentinfo ['level'] == 0) {
				if (! empty ( input ( "level_price" ) ) && null != input ( "level_price" )) {
					$updateData ["level_price"] = input ( "level_price" );
				}
				
			} */
		      $start = $agentinfo ['level']+1;
				for($i = $start; $i <= 3; $i ++) {
					if ( input ( "level{$i}_price" ) !="") {
						$updateData ["level{$i}_price"] = input ( "level{$i}_price" );
					}
				}
			
			switch ($agentinfo ['level']){
				case 0 :
					if((input ( "level_price" ) > $updateData ["level1_price"]) && ($updateData ["level1_price"]>$updateData ["level2_price"]) && ($updateData ["level2_price"]>$updateData ["level3_price"])){
				         continue;	
					}
					else{
						return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '价格不能高于上级价格' );
					}
					break;
				case 1 :
					if((input ( "level1_price" )>$updateData ["level2_price"]) && ($updateData ["level2_price"]>$updateData ["level3_price"]) ){
						continue;
					}
					else{
						return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '价格不能高于上级价格' );
					}
					break;
				case 2 :
					if(input ( "level2_price" )>$updateData ["level3_price"]){
						continue;
					}
					else{
						return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '价格不能高于上级价格' );
					}
					break;
				default:
					break;
			}
			
			$updateData['level'] = $agentinfo ['level'];
			$res = $this->agent->editSysPrice ( $updateData,$id, $agent_id );
			if ($res) {
				return $this->returnData ( self::RET_CODE_OK, '更新成功', $res );
			}
			return $this->returnData ( self::RET_CODE_ERR_OPDATA, '数据更新失败', '' );
		}
		FW::header403 ();
	}
	
	// 新增特定代理商报价单
	public function addAgentPrice() {
		if (request ()->isPost () || request ()->isAjax ()) {
			$bank_id = input ( 'bank_id' );
			$card_id = input ( 'card_id' );
			$type = input ( 'type' );
			$agent_id = input ( 'agent_id' );
			$parent_agent_id = input ( 'parent_agent_id' );
			$updateData = array ();
			$agentinfo = $this->agent->getAgentInfoById ( $parent_agent_id );
			
			if (empty ( $agentinfo ['id'] )) {
				return $this->returnData ( self::RET_CODE_ERR_OPDATA, '新增失败,非法操作', '' );
			}
			if (! empty ( $type )) {
				$updateData ["type"] = $type;
			}
			if (! empty ( $bank_id )) {
				$updateData ["bank_id"] = $bank_id;
			}
			if (! empty ( $card_id )) {
				$updateData ["card_id"] = $card_id;
			}
			if (! empty ( $agent_id )) {
				$updateData ["agent_id"] = $agent_id;
			}
	
			if($type ==1){
				if (( int ) $agentinfo ['level'] == 0) {
					if (! empty ( input ( "level_price" ) ) && null != input ( "level_price" )) {
						$updateData ["level_price"] = input ( "level_price" );
					}
				}
				for($i = $agentinfo ['level']; $i <= 3; $i ++) {
					if (! empty ( input ( "level{$i}_price" ) ) && null != input ( "level{$i}_price" )) {
						$updateData ["level{$i}_price"] = input ( "level{$i}_price" );
					}
				}
			
			    $has = db("agent_price")->field("id")->where("agent_id = '{$agent_id}' and  bank_id ='{$bank_id}' and type ='{$type}'")->find();
				//echo db()->getLastSql();
				if ($has) {
					return $this->returnData ( self::RET_CODE_OK, '改用户已经添加过', $has );
				}
				$res = $this->agent->addAgentPrice ( $updateData ,$agent_id,$parent_agent_id);
			}
			else
			{ 
				//处理贷款业务
				$cpa = false;
				$cps = false;
				$res = false;
				$cpa_updateData = array();
				$cps_updateData = array();
				$cpa_updateData = $updateData;
				$cps_updateData = $updateData;
				if (( int ) $agentinfo ['level'] == 0) {
					if (! empty ( input ( "cpa_level_price" ) ) && null != input ( "cpa_level_price" )) {
						$cpa_updateData ["level_price"] = input ( "cpa_level_price" );
						$cpa_updateData ["fy_type"] = 1;
						$cpa = true;
					}
					if (! empty ( input ( "cps_level_price" ) ) && null != input ( "cps_level_price" )) {
						$cps_updateData ["level_price"] = input ( "cps_level_price" );
						$cps_updateData ["fy_type"] = 2;
						$cps = true;
					}
				}
				for($i = $agentinfo ['level']; $i <= 3; $i ++) {
					if (! empty ( input ( "cpa_level{$i}_price" ) ) && null != input ( "cpa_level{$i}_price" )) {
						$cpa_updateData ["level{$i}_price"] = input ( "cpa_level{$i}_price" );
						$cpa_updateData ["fy_type"] = 1;
						$cpa = true;
					}
					if (! empty ( input ( "cps_level{$i}_price" ) ) && null != input ( "cps_level{$i}_price" )) {
						$cps_updateData ["level{$i}_price"] = input ( "cps_level{$i}_price" );
						$cps_updateData ["fy_type"] = 2;
						$cps = true;
					}
				}
				$wherecp = array();
				$wherecp["agent_id"] = $agent_id;
				$wherecp["bank_id"] = $bank_id;
				$wherecp["card_id"] = $card_id;
				$hasinsert = false;
				if($cpa){
					$wherecpa = $wherecp;
					$wherecpa["fy_type"] =1;
					$has = db("agent_price")->field("id")->where($wherecpa)->find();
					if (!$has) {
						$res = $this->agent->addAgentPrice ( $cpa_updateData ,$agent_id,$parent_agent_id);
					}
					else
					{
						$hasinsert = true;
					}
				}
				if($cps){
					$wherecps = $wherecp;
					$wherecps["fy_type"] =2;
					$has = db("agent_price")->field("id")->where($wherecps)->find();
					if (!$has) {
					
						$res = $this->agent->addAgentPrice ( $cps_updateData ,$agent_id,$parent_agent_id);
					}
					else
					{
						$hasinsert = true;
					}
				}
				if($hasinsert){
					return $this->returnData ( self::RET_CODE_OK, '改用户已经添加过', $has );
				}
			}
           
			if ($res) {
				return $this->returnData ( self::RET_CODE_OK, '添加成功', $res );
			} else {
				return $this->returnData ( self::RET_CODE_ERR_OPDATA, '添加失败', '' );
			}
		}
		FW::header403 ();
	}
	
	/*
	 * 编辑特定代理商报价单 @param id 代理商的报价单id @param agentid 当前代理商id @date 2017-08-07
	 */
	public function editAgentPrice() {
		if (request ()->isPost () || request ()->isAjax ()) {
			$id = input ( 'id' );
			$type = input("type");
			$bank_id = input ( 'bank_id' );
			$card_id = input ( 'card_id' );
			$agent_id = input ( 'agent_id' );
			$new_agent_id = input ( 'new_agent_id' );
			$parent_agent_id = input ( 'parent_agent_id' );
			$updateData = array ();
			$agentinfo = $this->agent->getAgentInfoById ( $parent_agent_id );
			
			if (empty ( $agentinfo ['id'] )) {
				return $this->returnData ( self::RET_CODE_ERR_OPDATA, '数据更新失败,非法操作', '' );
			}
			if (empty ( $id )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '缺少参数:id' );
			}
			if (! empty ( $bank_id )) {
				$updateData ["bank_id"] = $bank_id;
			}
			if (! empty ( $card_id )) {
				$updateData ["card_id"] = $card_id;
			}
			if (! empty ( $agent_id )) {
				$updateData ["agent_id"] = $new_agent_id;
			}
			if($type ==1){
				if (( int ) $agentinfo ['level'] == 0) {
					if (! empty ( input ( "level_price" ) ) && null != input ( "level_price" )) {
						$updateData ["level_price"] = floatval(input ( "level_price" ));
					}
				}
				for($i = $agentinfo ['level']; $i <= 3; $i ++) {
					if (! empty ( input ( "level{$i}_price" ) ) && null != input ( "level{$i}_price" )) {
						$updateData ["level{$i}_price"] = floatval(input ( "level{$i}_price" ));
					}
				}
				$res = $this->agent->editAgentPrice ( $updateData, $id,$type,$cpwhere="");
				
				if ($res) {
					return $this->returnData ( self::RET_CODE_OK, '更新成功', $res );
				} else {
					return $this->returnData ( self::RET_CODE_ERR_OPDATA, '数据更新失败', '' );
				}
			}
			else
			{
				$cpa = false;
				$cps = false;
				$cpa_updateData=$updateData;
				$cps_updateData=$updateData;
				if (( int ) $agentinfo ['level'] == 0) {
					if (! empty ( input ( "cpa_level_price" ) ) && null != input ( "cpa_level_price" )) {
						$cpa_updateData ["level_price"] = input ( "cpa_level_price" );
						$cpa_updateData ["fy_type"] = 1;
						$cpa = true;
					}
					if (! empty ( input ( "cps_level_price" ) ) && null != input ( "cps_level_price" )) {
						$cps_updateData ["level_price"] = input ( "cps_level_price" );
						$cps_updateData ["fy_type"] = 2;
						$cps = true;
					}
				}
				for($i = $agentinfo ['level']; $i <= 3; $i ++) {
					if (! empty ( input ( "cpa_level{$i}_price" ) ) && null != input ( "cpa_level{$i}_price" )) {
						$cpa_updateData ["level{$i}_price"] = input ( "cpa_level{$i}_price" );
						$cpa_updateData ["fy_type"] = 1;
						$cpa = true;
					}
					if (! empty ( input ( "cps_level{$i}_price" ) ) && null != input ( "cps_level{$i}_price" )) {
						$cps_updateData ["level{$i}_price"] = input ( "cps_level{$i}_price" );
						$cps_updateData ["fy_type"] = 2;
						$cps = true;
					}
				}
				if($cpa){
					$wherecpa = array();
					$wherecpa['agent_id'] = $agent_id;
					$wherecpa['bank_id'] = $bank_id;
					$wherecpa['card_id'] = $card_id;
					$wherecpa['fy_type'] = 1;
					$res = $this->agent->editAgentPrice ( $cpa_updateData, $agent_id ,$type,$wherecpa);
					//echo db()->getLastSql();
					
				}
				if($cps){
					$wherecps = array();
					$wherecps['agent_id'] = $agent_id;
					$wherecps['bank_id'] = $bank_id;
					$wherecps['card_id'] = $card_id;
					$wherecps['fy_type'] = 2;
					$res = $this->agent->editAgentPrice ( $cps_updateData, $agent_id ,$type,$wherecps);
					
						
				}
				if ($res) {
					return $this->returnData ( self::RET_CODE_OK, '更新成功', $res );
				} else {
					return $this->returnData ( self::RET_CODE_ERR_OPDATA, '数据更新失败', '' );
				}
			}
	     
/* 			switch ($agentinfo ['level']){
				case 0 :
					if((($updateData ["level_price"] > $updateData ["level1_price"]) && ($updateData ["level1_price"]>$updateData ["level2_price"]) && ($updateData ["level2_price"]>$updateData ["level3_price"])) ){
				         continue;	
					}
					else{
						return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '价格不能高于上级价格' );
					}
					break;
				case 1 :
			
					if((($updateData ["level1_price"]>$updateData ["level2_price"]) && ($updateData ["level2_price"]>$updateData ["level3_price"])) ){
						continue;
					}
					else{
						return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '价格不能高于上级价格' );
					}
					break;
				case 2 :
					if((($updateData ["level2_price"]>$updateData ["level3_price"])) ){
						continue;
					}
					else{
						return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '价格不能高于上级价格' );
					}
					break;
				default:
					break;
			} */
			
		
			
		}
		FW::header403 ();
	}
	
	// 获取特定代理商报价单
	public function getAgentPrice() {
		if (request ()->isPost () || request ()->isAjax ()) {
			$type = input ( 'type' );
			$bank_id = input ( 'bank_id' );
			$agent_id = input ( 'agent_id' );
			$map="";
			$tokenaid = input ( 'tokenaid' );
			$token = input ( 'token' );
			if($agent_id != $tokenaid){
				$map = "id = '{$agent_id}' and parentid ='{$tokenaid}'";
			}
			else
			{
				$map = "id = '{$agent_id}' ";
			}
			$res = AgentModel::where ($map )->field ( "id,phone" )->find ();
			if(!$res){
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT,"非法操作", '' );
			}
			$res = AgentModel::where ( "id", "{$tokenaid}" )->field ( "id,phone,salt" )->find ();
			if (empty ( $res ['id'] )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT,"非法操作", '' );
			}
			$retoken = md5($res["id"].$res['phone'].$res['salt'].date("ymd",time()));
			if($token !=$retoken){
				return $this->returnData ( self::RET_CODE_ERR_AUTHTOKEN,"token无效", '' );
			}
			
			$agentinfo = $this->agent->getAgentInfoById ( $agent_id );
			if (empty ( $agentinfo ['id'] )) {
				return $this->returnData ( self::RET_CODE_ERR_OPDATA, '数据获取失败,非法操作', '' );
			}
			$res = AgentModel::getAgentPrice ( $type, $bank_id, $agent_id );
			return $this->returnData ( self::RET_CODE_OK, '成功', $res );
		}
		FW::header403 ();
	}
	
	//编辑特定用户时，获取特定用户的报价单
	public function getMyselfAgentPrice(){
		if (request ()->isPost ()) {
			$type = input ( 'type' );
			$bank_id = input ( 'bank_id' );
			$agent_id = input ( 'agent_id' );
			$token = input ( 'token' );
			$tokenaid = input ( 'tokenaid' );
			
			$res = AgentModel::where ( "id", "{$tokenaid}" )->field ( "id,phone,salt" )->find ();
			if (empty ( $res ['id'] )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT,"非法操作", '' );
			}
			$retoken = md5($res["id"].$res['phone'].$res['salt'].date("ymd",time()));
			if($token !=$retoken){
				return $this->returnData ( self::RET_CODE_ERR_AUTHTOKEN,"token无效", '' );
			}
				
			$agentinfo = $this->agent->getAgentInfoById ( $agent_id );
			if (empty ( $agentinfo ['id'] )) {
				return $this->returnData ( self::RET_CODE_ERR_OPDATA, '数据获取失败,非法操作', '' );
			}
			$res = AgentModel::getMyselfAgentPrice ( $type, $bank_id, $agent_id );
			return $this->returnData ( self::RET_CODE_OK, '成功', $res );
		}
		FW::header403 ();
	}
	
	
	// 删除特定用户报价单
	public function delAgentPrice($id,$card_id=0,$type=1) {
		if (request ()->isPost ()) {
			$tokenaid = input ( 'tokenaid' );
			$token = input ( 'token' );
			if($type == 1){
				$agentinfo = Agent_price::where ("id='{$id}'" )->field ( "agent_id" )->find ();
				if(!$agentinfo){
					return $this->returnData ( self::RET_CODE_ERR_ARGUMENT,"非法操作", '' );
				}
			}
			else{
				$agentinfo = Agent_price::where ("agent_id='{$id}' and bank_id ='{$card_id}' and type =2" )->field ( "agent_id" )->find ();
				if(!$agentinfo){
					return $this->returnData ( self::RET_CODE_ERR_ARGUMENT,"非法操作", '' );
				}
			}
			$map = "id = '{$agentinfo['agent_id']}' and parentid ='{$tokenaid}'";
			$res = AgentModel::where ($map)->field ( "id,phone" )->find ();
			if(!$res){
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT,"非法操作", '' );
			}
			$res = AgentModel::where ( "id", "{$tokenaid}" )->field ( "id,phone,salt" )->find ();
			if (empty ( $res ['id'] )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT,"非法操作", '' );
			}
			
			$retoken = md5($res["id"].$res['phone'].$res['salt'].date("ymd",time()));
			if($token !=$retoken){
				return $this->returnData ( self::RET_CODE_ERR_AUTHTOKEN,"token无效", '' );
			}
			
			if($type == 1){
				$res = Agent_price::destroy ( $id ); // 删除id=1
			}
			else
			{
				$delwhere["agent_id"] = $id;
				$delwhere["card_id"] = $card_id;
				$res = Agent_price::where($delwhere)->delete();
			}
			if ($res) {
				return $this->returnData ( self::RET_CODE_OK, '删除成功', $res );
			}
			return $this->returnData ( self::RET_CODE_ERR_OPDATA, '删除失败', '' );
		}
		FW::header403 ();
	}
	
	// 获取代理商分享链接
	public function getAgentShareUrl() {
		if (request ()->isPost ()) {
			$data = array ();
			$id = input ( 'id' );
			if (empty ( trim ( $id ) )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '参数不能为空', '' );
			}
			$agentinfo = AgentModel::get ( [ 
					'id' => $id,
					'is_deleted' => 0 
			] );
			if (empty ( $agentinfo )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '没找到分享链接', '' );
			}
			$id = $agentinfo ['id'];
			$code = md5 ( $id . $agentinfo ['phone'] . $agentinfo ['salt'] );
			$data ['cardurl'] = config ( "web_url" ) . "user/index?id={$id}&code={$code}&type=1";
			$data ['loanurl'] = config ( "web_url" ) . "user/index?id={$id}&code={$code}&type=2#!/loan/speed";
			$data ['share_juhe_url'] = config ( "web_url" ) . "agent/share/urlcode/{$code}/id/{$id}";
			$data ['share_card_url'] = config ( "web_url" ) . "agent/xyshare/urlcode/{$code}/type/1/id/{$id}";
			$data ['share_loan_url'] = config ( "web_url" ) . "agent/xdshare/urlcode/{$code}/type/2/id/{$id}";
			$data ['data'] = $agentinfo;
			return $this->returnData ( self::RET_CODE_OK, '成功', $data );
		}
		FW::header403 ();
	}
	
	// 报价单列表
	public function quotationList() {
		if (request ()->isPost () || request ()->isAjax ()) {
			$type = empty ( input ( 'type' ) ) ? 1 : input ( 'type' ); // 1信用卡 2贷款
			$id = input ( 'id' );
			$page = input ( 'page' );
			$pagesize = input ( 'pagesize' );
			if (isset ( $page ) && null !== $page) {
				$page = $page;
			} else {
				$page = 1;
			}
			$pagesize = empty ( $pagesize ) ? 10 : $pagesize;
			if (empty ( trim ( $id ) )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '参数不能为空', '' );
			}
			$agentinfo = AgentModel::get ( [ 
					'id' => $id,
					'is_deleted' => 0 
			] );
			if (empty ( $agentinfo )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '非法操作', '' );
			}
			$agentlist = $this->agent->getQuotationListByAgentId ( $type, $id, $page, $pagesize );
			
			return $this->returnData ( self::RET_CODE_OK, '成功', $agentlist );
		}
		FW::header403 ();
	}
	
	
	// 获取信用卡列表
	public function getCreditList() {
		if (request ()->isPost () || request ()->isAjax ()) {
			$bank_id = input ( 'bank_id' );
			$keyword = input ( 'keyword' );
			if (empty ( $bank_id )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '非法操作', '' );
			}
			$agentlist = AgentModel::getCreditList ( $bank_id,$keyword );
			return $this->returnData ( self::RET_CODE_OK, '成功', $agentlist );
		}
		FW::header403 ();
	}
	
	// 获取贷款产品列表
	public function getLoanList() {
		if (request ()->isPost () || request ()->isAjax ()) {
			$loan_id = input ( 'loan_id' );
			$keyword = input ( 'keyword' );
			if (empty ( $loan_id )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '非法操作', '' );
			}
			$agentlist = AgentModel::getLoanList ( $loan_id ,$keyword);
			return $this->returnData ( self::RET_CODE_OK, '成功', $agentlist );
		}
		FW::header403 ();
	}
	
	// 提现
	public function withdraw() {
		if (request ()->isPost () || request ()->isAjax ()) {
			$agent_id = input ( 'id' );
			$money = input ( 'money' );
			$code = input ( 'post.code' );
			$checkcode = session ( 'code' );
			if (! is_numeric ( $money ) || $money <=0) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '请输入正确的提现金额', '' );
			}
			if ($money >2000) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '每次提现金额不能超过2000元', '' );
			}
			if (empty ( $agent_id )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '非法操作', '' );
			}
			if ($code == $checkcode) {
				$info = $this->agent->getAgentInfoById ( $agent_id );
				if (empty ( $info )) {
					return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '非法操作', '' );
				}
				if ($money > $info ['left_money']) {
					return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '提现金额超过了可提现金额，提现失败', '' );
				}
				$rs = $this->agent->withdraw ( $money, $agent_id );
				if($rs){
					return $this->returnData ( self::RET_CODE_OK, '申请提现成功', $rs );
				}
				else{
					return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '申请提现失败', $rs );
				}
			} else {
				return $this->returnData ( self::RET_CODE_ERR_EMSNO, '验证码不一致', '' );
			}
		}
		FW::header403 ();
	}
	
	// 报表
	public function agentReport() {
		if (request ()->isPost () || request ()->isAjax ()) {
			$agent_id = input ( 'id' );
			$sday = date('Y-m-d',strtotime("-7 day"));
			$today = date('Y-m-d',time());
			$stime = empty(trim(input ( 'stime' )))?$sday:trim(input ( 'stime' ));
			$etime = empty(trim(input ( 'etime' )))?$today:trim(input ( 'etime' ));
			$page = input ( 'page' );
			$pagesize = input ( 'pagesize' );
			if (isset ( $page ) && null !== $page) {
				$page = $page;
			} else {
				$page = 1;
			}
			if (empty ( $agent_id )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '非法操作', '' );
			}
			$info = AgentModel::get ( [ 
					'id' => $agent_id 
			] );
			
			if (empty ( $info )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '非法操作', '' );
			}
			
			
			$res = AgentModel::getReportList ( $agent_id, $stime,$etime, $page, $pagesize );
			return $this->returnData ( self::RET_CODE_OK, '成功', $res );
		}
		FW::header403 ();
	}
	
	//根据代理获取银行或者贷款公司信息
	public function getBankLoanList()
	{
		if (request ()->isPost () || request ()->isAjax ()) {
			$agent_id = input ( 'aid' );
			$type = input ( 'type' );
			$keyword = input ( 'keyword' );
			if (empty ( $agent_id )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '非法操作', '' );
			}
			$info = AgentModel::get ( [
					'id' => $agent_id,
					'is_deleted' => 0
					] );
				
			if (empty ( $info )) {
				return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '非法操作', '' );
			}
			$agentlist = AgentModel::getBankLoanList ( $agent_id,$type,$keyword );
			return $this->returnData ( self::RET_CODE_OK, '成功', $agentlist );
		}
		FW::header403 ();
	}
	
	//根据银行id，或者贷款产品id获取产品信息
	public function getProductInfoByid(){
		if (request ()->isPost ()) {
			  $agent_id = input ( 'aid' );
			  $productid = input ( 'productid' );
			  $type = input ( 'type' );
			  $list = array();
			  $agentinfo = AgentModel::get ( [
			  		'id' => $agent_id,
			  		'is_deleted' => 0
			  		] );
			  if (empty ( $agentinfo )) {
			  	return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '非法操作', '' );
			  }
			  $id = $agentinfo ['id'];
			  $code = md5 ( $id . $agentinfo ['phone'] . $agentinfo ['salt'] );
			  if($type == 1){ //根据银行id或者信用卡产品
			  	    $where = array();
			  	    $where["is_deleted"] = 0;
			  	    $where["id"] = $productid;
			  	    $list =  db("bank")->field("id,bank_name,passing_rate,score,average_amount money_range,bank_logo logo")->where($where)->find();
			  	    if($list){
			  	    	$where = array();
			  	    	$where["is_deleted"] = 0;
			  	    	$where["bank_id"] = $list['id'];
			  	    	$cardlist = db("bank_card")->field("card_name,card_logo,card_details")->where($where)->select();
			  	    	if($cardlist){
			  	    		$subhtml ="";
			  	    		 foreach ($cardlist as $k =>$v){
			  	    		 	 $list["card_list"][$k] =$v;
			  	    		 	 $list["card_list"][$k]["card_logo"] = config('file_url').$v['card_logo'];
			  	    		 	$subhtml =$subhtml ."<table>".
			  	    			                    "<tr><td class='label'>提供商</td><td class='conton'>{$v['card_name']}</td></tr>".
			  	    			                   "<tr><td class='label'>推广内容</td><td class='conton'>{$v['card_details']}</td></tr>".
			  	    			                 "</table>";
			  	    		 }
			  	    	}
			  	    	$list["share_url"] =config("web_url")."user/index?id={$agent_id}&code={$code}&type=1#!/bankCenter?bankid={$productid}";
			  	    	$list["xykhtml"] ="<div class='spread-details-box box-show' id='box'>".
                                                       "<div class='header' >".
				  	    			                          "<img src='{$list["logo"]}' class='logo'/>".
						                                      "<div class='er-box text-center'>".
									  	    			           "<div id='erweima'></div>".
									  	    			          "<p>长按图中二维码了解详情</p>".
									  	    			      "</div>".
                                                       "</div>".
                                            "<div class='info-box'>".
			  	    			                $subhtml.
			  	    			           "</div>".
			  	    			      "</div>";
			  	    }
			  }	    
			  elseif($type == 2){ //根据贷款产品id获取贷款产品信息
				  	$where = array();
				  	$where["a.is_deleted"] = 0;
				  	$where["a.id"] = $productid;
				  	$list =  db("loan_product")->alias("a")->join("loan b","a.loan_id = b.id","left")
				  	->field("a.id,a.product_details card_details,a.loan_range money_range, a.name card_name,b.score,b.advance_rate,a.loan_range money_range,b.name bank_name,a.product_logo card_logo,b.logo")
				  	->where($where)->find();
				  	if($list){
				  		$list["share_url"] =config("web_url")."user/index?id={$agent_id}&code={$code}&type=2#!/loanLogin?loanid={$list['id']}";
				  		$list["card_logo"] = config('file_url').$list['card_logo'];

				  		$list["xykhtml"] ="<div class='spread-details-box box-show' id='box'>".
				  				"<div class='header' >".
				  				"<img src='{$list["logo"]}' class='logo'/>".
				  				"<div class='er-box text-center'>".
				  				"<div id='erweima'></div>".
				  				"<p>长按图中二维码了解详情</p>".
				  				"</div>".
				  				"</div>".
				  				"<div class='info-box'>".
                                 "<table>".
				  				         "<tr><td class='label'>提供商</td><td class='conton'>{$list['card_name']}</td></tr>".
                             		     "<tr><td class='label'>额度</td><td class='conton'>{$list['money_range']}</td></tr>".
                             		     "<tr><td class='label' >推广内容</td><td class='conton'>{$list['card_details']}</td></tr>".
                                 "</table>".
				  				"</div>".
				  				"</div>";
				  	}
				  	
			  }
			  else{
			  	$list = array();
			  }
			  if($list){
			  	$list["logo"] = config('file_url').$list['logo'];
			  }
			  return $list;
		}
		FW::header403 ();
	}
	
	public function  checkToken($tokenaid,$token,&$recode,&$redata){
		
		$res = AgentModel::where ( "id", "{$tokenaid}" )->field ( "id,phone,salt" )->find ();
		$recode = 1;
		$redata ="成功";
		if (empty ( $res ['id'] )) {
			$recode = 0;
			$redata ="非法操作";
		}
		$retoken = md5($res["id"].$res['phone'].$res['salt'].date("ymd",time()));
		if($token !=$retoken){
			$recode = 0;
			$redata ="token无效";
		}
	}
	
	public function resetPass(){
		if(request ()->isPost ()){
			$type = input ( 'type' );
			if($type ==2){ 
				//忘记密码时的修改
				$phone = input ( 'phone' );
				$code = input ( 'code' );
				$recode = session("code");
				$real_phone = session('mobile');
				$newpassword = input ( 'password' );
				if (empty($phone))
				{
					return $this->returnData(self::RET_CODE_ERR_ARGUMENT, '请输入手机号码', '');
				}
				if (!preg_match("/^1[2345789]{1}\d{9}$/", $phone ))
				{
					return $this->returnData(self::RET_CODE_ERR_ARGUMENT, '手机号码格式错误', '');
				}
				if($code != $recode){
					return $this->returnData(self::RET_CODE_ERR_EMSNO, '验证码不一致', '');
				}
				if($phone != $real_phone){
					return $this->returnData(self::RET_CODE_ERR_EMSNO, '输入的手机号码不一致', '');
				}
				
				$where = array();
				$where ['phone'] = $phone;
				$salt = AgentModel::where ( $where )->field ( 'salt' )->find ();
				if(!$salt){
					return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '你输入的手机号码不存在，请重新输入', "" );
				}
				$data = array();
				$data["password"] = encryptPassword ( $newpassword . $salt ['salt'] );
				AgentModel::update($data,$where);
				return $this->returnData ( self::RET_CODE_OK, '密码修改成功', "" );
				
			}
			else{
				$where = array();
				$agent_id = input ( 'aid' );
				$newpassword = input ( 'newpassword' );
				$oldpassword = input ( 'oldpassword' );
				$repeatpassword = input ( 'repeatpassword' );
				if($newpassword != $repeatpassword){
					return $this->returnData ( self::RET_CODE_OK, '两次输入的密码不一致，请重新输入密码', "" );
				}
				$where ['id'] = $agent_id;
				$salt = AgentModel::where ( $where )->field ( 'salt' )->find ();
				$password = encryptPassword ( $oldpassword . $salt ['salt'] );
				$where ['password'] = $password;
				$checkLgoin = AgentModel::where ( $where )->field ( 'id,phone,name,level,level as lev,salt' )->find ();
				if($checkLgoin){
					$data = array();
					$data["password"] = encryptPassword ( $newpassword . $salt ['salt'] );
					AgentModel::update($data,$where);
					return $this->returnData ( self::RET_CODE_OK, '密码修改成功', "" );
				}
				else{
					return $this->returnData ( self::RET_CODE_ERR_ARGUMENT, '你输入的旧密码有误，请重新输入', "" );
				}
			}	
		}
		FW::header403 ();
	}

	//找出自己和所有上级代理商的报价单
	public function getCommissionByAgentID(){
		if(request ()->isPost ()){
			  $aid = input("agentid");
			  $card_id = input("card_id");
			  $type = input("type");
			  $money = input("money");
			  if(empty($aid)){
			     	return $this->returnData(self::RET_CODE_ERR_ARGUMENT, '参数agentid不能为空', '');
			  }
			  if(empty($card_id)){
			  	return $this->returnData(self::RET_CODE_ERR_ARGUMENT, '参数card_id不能为空', '');
			  }
			  if(empty($type)){
			  	return $this->returnData(self::RET_CODE_ERR_ARGUMENT, '参数type不能为空', '');
			  }
			  if($type == 2){
			  	if(empty($money)){
			  		return $this->returnData(self::RET_CODE_ERR_ARGUMENT, '参数money不能为空', '');
			  	}
			  }
			  $parentids = getAllParentAgentIds($aid);
			  $ids_arr = array();
			  if($parentids){
			  	   $ids_arr = explode(",", $parentids);
			  }
			  $return_data = array();
			  if($ids_arr){
			  	   foreach ($ids_arr as $k=> $v){
			  	   	      //代理商信息
			  	   	     $agentinfo = getAgentInfoById($v);
			  	   	     if($agentinfo){
			  	   	     	$level = $agentinfo['level'];
			  	   	     	if($level <3){
			  	   	     		$sub_level = $level+1;
			  	   	     		$field_sub = "level".$sub_level."_price";
			  	   	     		$field_sub_clo = ",".$field_sub;
			  	   	     	}
			  	   	     	else
			  	   	     	{
			  	   	     		$field_sub = "";
			  	   	     		$field_sub_clo = "";
			  	   	     	}
			  	   	     	$field = "level".$level."_price";
			  	   	     	
			  	   	     	$field = str_replace("0", "", $field);
			  	   	     	//找出相应的报价单
			  	   	     	if($type ==1){
			  	   	     		$where_id = " and  bank_id ='{$card_id}'";
			  	   	     	}
			  	   	     	else{
			  	   	     		$where_id = " and  card_id ='{$card_id}'";
			  	   	     	}
			  	   	     	$has =  db("agent_price")->field($field.",fy_type".$field_sub_clo)->where("agent_id ='{$v}' and type ='{$type}'  $where_id")->select();
			  	   	     	if(!$has){
			  	   	     	       $has = db("agent_sys_price")->field($field.",fy_type".$field_sub_clo)->where("agent_id ='{$v}' and type ='{$type}'  $where_id")->select(); //3
			  	   	     	       if(!$has){
			  	   	     	           $has = db("agent_sys_price")->field($field.",fy_type".$field_sub_clo)->where("agent_id ='{$agentinfo['parentid']}' and type ='{$type}'  $where_id")->select();//2
			  	   	     	           if(!$has){
			  	   	     	             	$p1 = getAgentInfoById($agentinfo['parentid']);
			  	   	     	           	    $has = db("agent_sys_price")->field($field.",fy_type".$field_sub_clo)->where("agent_id ='{$p1['parentid']}' and type ='{$type}'  $where_id")->select();//1
			  	   	     	           	    if(!$has){
			  	   	     	           	    	$p2 = getAgentInfoById($p1['parentid']);
			  	   	     	           	    	$has = db("agent_sys_price")->field($field.",fy_type".$field_sub_clo)->where("agent_id ='{$p2['parentid']}' and type ='{$type}'  $where_id")->select();//0
			  	   	     	           	    }
			  	   	     	           }
			  	   	     	       }
			  	   	     	}
			  	   	     	if(!$has){ //一直到总代都找不到报价单，就取系统默认
			  	   	     		$has = db("agent_sys_price")->field($field.",fy_type".$field_sub_clo)->where("agent_id ='-1' and type ='{$type}'  $where_id")->select();//-1
			  	   	     	}
			  	   	     // echo db()->getLastSql();
			  	   	     	$m1 = 0;
			  	   	     	$m2 = 0;
			  	   	     	$m =0;
			  	   	     	if($has){
			  	   	     		 foreach ($has as $vl){
			  	   	     		 	    if($vl['fy_type'] == 1){
			  	   	     		 	    	   if($level <3){
			  	   	     		 	    	   	  $m1 = $vl[$field]-$vl[$field_sub];
			  	   	     		 	    	   }
			  	   	     		 	    	   else{
			  	   	     		 	    	   	  $m1 = $vl[$field];
			  	   	     		 	    	   }
			  	   	     		 	    }
			  	   	     		 	     if($vl['fy_type'] == 2){
			  	   	     		 	     	if($level <3){
			  	   	     		 	    	   $m2 = round($money*($vl[$field]-$vl[$field_sub])/100,2);
			  	   	     		 	     	}
			  	   	     		 	     	else
			  	   	     		 	     	{
			  	   	     		 	     		$m2 = round($money*$vl[$field]/100,2);
			  	   	     		 	     	} 
			  	   	     		 	    }
			  	   	     		 }
			  	   	     		 $m = $m1+$m2;
			  	   	     	}
			  	   	     	$return_data[$k]["agentid"] = $v;
			  	   	     	$return_data[$k]["level"] = $agentinfo['level'];
			  	   	     	$return_data[$k]["phone"] =$agentinfo['phone'];
			  	   	     	$return_data[$k]["type"] = $type;
			  	   	     	$return_data[$k]["money"] = $m;
			  	   	     	$return_data[$k]["card_id"] = $card_id;
			  	   	     }
			  	   }
			  }
			 return $this->returnData ( self::RET_CODE_OK, 'ok', $return_data );
		}
		FW::header403 ();
	}
	
	
	public function test() {
		echo VerifyHelper::verify();
		die;
		$data = array(
				"agentid"=>'260',
				"card_id"=>1,
				"type"=>1,
				"money"=>100
		);
		$url ="http://local.futurecreditapi.com/api/agent/getCommissionByAgentID";
		//$url ="http://test.api.futurecredit.net/api/agent/getCommissionByAgentID";
		$rs = http_request_post($url,$data);
		$data = json_decode($rs,true);
		echo "<pre>";
		print_r($data);
		echo "</pre>";
		die;
		echo "<pre>";
		print_r(getAgentMyDefaultPriceAgentId(170)) ;
		echo "</pre>";
		die;
		echo md5("123456bondhl");die;
		/*
		 * sign --> 签名 string paramMd5Str = MD5( token=AccessToken&phone=Phone ); string sign = MD5( token=AccessToken&phone=Phone&paramMd5=paramMd5Str );(必选 MD5 32位小写）
		 */
		$token = "c46c2106189e48e5a96450c16c824cbf";
		$phone = "13710332009";
		$paramMd5Str = MD5 ( "token={$token}&phone={$phone}" );
		$sign = MD5 ( "token={$token}&phone={$phone}&paramMd5={$paramMd5Str}" );
		$cid = "200";
		$sid = "338";
		$post ["ChannelId"] = ( int ) $cid;
		$post ["SourceId"] = ( int ) $sid;
		$post ['token'] = "c46c2106189e48e5a96450c16c824cbf";
		$post ['sign'] = $sign;
		$post ['phone'] = $phone;
		$post ['platformType'] = 2;
		$post ['info'] = "{'name':'张三','shenfenzh':'340321198410250815','zhiye':'上班族','fangchan':'无','chechan':'有车未抵押','phone':'15763352005','gongzixingshi':'现金','jingyingzz':'有','yuexin':'1500~3000','shebao':'无','duigongliushui':'半年银行流水 10万以下','fuzhaiqingkuang':'有','applyCity':'蚌埠','money':'3000','month':'12','daikuanlx':'个人贷款','chushengnian':'1996'}";
		echo request_by_curl ( 'http://cps.ppdai.com/bd/RegLogin', $post );
		// var_dump($post);
		$url = "http%3a%2f%2fac.ppdai.com%2fUser%2fAuthCookie%3ftoken%3d61AX1XK6B37L%26jump%3dhttp%3a%2f%2fm.ppdai.com%2floan%2fusers%2fuserinfo%3fsourceId%3d338_timespan%3dd1379428-3b31-4564-adcb-d035149b8cb2";
		echo urldecode ( $url );
	}
}

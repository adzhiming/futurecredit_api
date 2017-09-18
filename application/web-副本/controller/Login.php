<?php
namespace app\web\controller;
use think\Log;
use think\Config;
use think\Controller;
use think\Input;
use think\Lang;
use think\Session;
use think\Model;
use think\Db;
use think\Request;
class Login extends Controller {

    public function __construct() {
        parent::__construct();
        $this->api_url = config("api_url");
        $this->web_url = config("web_url");
        $this->assign("api_url",$this->api_url);
        $this->assign("web_url",$this->web_url);

    }

    public function test(){
        echo session("code");die;
    }
	public function userLogin() {
        $agent_id=input('get.id');
        $agent_codes=input('get.code');
        $type=input('get.type');
        if(!empty($agent_id) && !empty($agent_codes)){
            $agent=db('agent')->field('id,phone,salt')->where('id',$agent_id)->find();
            if(!empty($agent)){
                $check=md5($agent['id'].$agent['phone'].$agent['salt']);
                if($check==$agent_codes){
                    session('agent_id',$agent_id);
                    session('agent_code',$agent_codes);
                    session('agent_type',$type);
                }
            }
        }


		return $this->fetch("./login/index");
	}
  /*   public function agentLogin(){
     	return $this->fetch("./agent/index");
   } */

/*    public function agent(){
    $this->view->assign('url', "<a class='op' style='display:block;' href='../agent#/agentApply'>如何申请代理商</a>");
    return $this->fetch("./agent/login");
   } */
}
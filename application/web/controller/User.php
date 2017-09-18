<?php
namespace app\web\controller;
use think\Log;
use think\Config;
class User extends Base {

    public function apply(){
        return $this->fetch("./user/tel/apply");
    }

    public function bankCenter(){
        return $this->fetch("./user/tel/bankCenter");
    }
	public function index() {

        $agent_id=isset($_GET['id'])?$_GET['id']:'';
        $agent_codes=isset($_GET['code'])?$_GET['code']:'';
        $type=isset($_GET['type'])?$_GET['type']:'';
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
		return $this->fetch("./user/index");

	}

    public function  card() {
		return $this->fetch("./user/tel/card");
	}

	public function  creditCard() {
		return $this->fetch("./user/tel/creditCard");
	}


	public function free(){
		return $this->fetch("./user/tel/free");
	}

	//我的贷款页面
	public function loan(){
		return $this->fetch("./user/tel/loan");
	}

	public function loanLogin(){
		return $this->fetch("./user/tel/loanLogin");
	}
    public function login(){
        return $this->fetch("./user/tel/login");
    }
    public function lowMoney(){
        return $this->fetch("./user/tel/lowMoney");
    }
    public function money(){
        return $this->fetch("./user/tel/money");
    }
    public function my(){
        return $this->fetch("./user/tel/my");
    }
    public function myLoan(){
        return $this->fetch("./user/tel/myLoan");
    }
    public function platinum(){
        return $this->fetch("./user/tel/platinum");
    }
    public function speed(){
        return $this->fetch("./user/tel/speed");
    }
    public function veto(){
        return $this->fetch("./user/tel/veto");
    }
    public function signIn(){
        return $this->fetch("./user/tel/signIn");
    }
    public function xdlogin(){
       return $this->fetch("./user/tel/xdlogin");
    }
    public function wodedxlogin(){
       return $this->fetch("./user/tel/wodedxlogin");
    }
}
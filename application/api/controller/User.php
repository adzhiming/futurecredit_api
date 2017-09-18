<?php
namespace app\api\controller;
use think\Log;
use think\Request;
use think\Session;
class User extends Base {

    public function __construct(Port $request)
    {
        $this->Port = $request;
    }

//    public function test(){
//
//        session('name',123456);
//
//    }
//
//    public function test1(){
//        var_dump(session('name'));
//    }

//登录验证接口
    public  function check_all_login(){
        $phone=session('phone');
        $agent_id =session('agent_id');//代理商id
        $agent_codes = session('agent_code');;//验证信息
        $agent_type = session('agent_type');//跳转类型
        if(!empty($phone)){
            //添加代理关系
            //如果用户登录，修改代理商信息（如果带参数的url），返回1表示已登录
            $checklogin=db('user')->where('phone',$phone)->find();
            if(!empty($checklogin)){
                if(!empty($agent_id) && !empty($agent_codes)){
                    $agent=db('agent')->field('id,phone,salt')->where('id',$agent_id)->find();
                    if(!empty($agent)){
                        $check=md5($agent['id'].$agent['phone'].$agent['salt']);
                        // $check="add6f6ba4acc1cf32643848b1a974a70";
                        if($check==$agent_codes){
                            //根据用户id判断该关系有没有建立，有的话修改，没的话添加
                            $agent_user=db('agent_user')
                                ->where('user_id',$checklogin['id'])
                                ->where('agent_id',$agent_id)
                                ->find();
                            if(empty($agent_user)){
                                $age['agent_id']=$agent_id;
                                $age['user_id']=$checklogin['id'];
                                $age['create_time']=date("Y-m-d H:i:s",time());
                                db('agent_user')->insert($age);
                            }else{
                                $ages['create_time']=date("Y-m-d H:i:s",time());
                                db('agent_user')->where('user_id',$checklogin['id'])->update($ages);
                            }
                        }
                    }
                }
                return $this->returnData(self::RET_CODE_OK,'用户已登录','');
            }else{
                return $this->returnData(self::RET_CODE_ERR_EXEPTION,'用户不存在','');
            }
        }else{
            if(!empty(cookie('login_id'))){
                return $this->returnData(self::RET_CODE_OK,'用户已登录','');
            }else{
                return $this->returnData(self::RET_CODE_ERR_EXEPTION,'请先登录','');
            }
            //如果用户没有登录，返回2，弹出登录框，走登录接口，获取代理商信息（如果带参数的url）,登录状态走登录接口

        }
    }

    /*
    * 发送验证码方法
    * 2017.8.1
    * */
    public function crode(){
//      session('code',123456);
//      $mobile=trim(input('phone'));//电话号码
//      session('mobile',$mobile);
//     return $this->returnData(self::RET_CODE_OK,'短信发送成功','');
//    exit;
       $callback = trim(input('callback')); //兼容跨域
       vendor("auto.TopSdk");
       $mobile=trim(input('phone'));//电话号码
       $type_id=input('type_id');//模板类型
        $type_id = empty($type_id)?1:$type_id;
     //$type_id=3;//模板类型
       $data = array();
       session_start();
       $data['session_id'] = session_id();
        if(empty($mobile)){
        	if($callback){
        		$data["code"] = self::RET_CODE_ERR_EMPTYPHONE;
        		$data["msg"] = '手机号码不能为空';
        		echo $callback.'('.json_encode($data).')';exit;
        	}
            return $this->returnData(self::RET_CODE_ERR_EMPTYPHONE,'手机号码不能为空','');
        }
        if(!preg_match("/^1[2345789]{1}\d{9}$/",$mobile)){
        	if($callback){
        		$data["code"] = self::RET_CODE_ERR_PHONE;
        		$data["msg"] = '手机号码错误';
        		echo $callback.'('.json_encode($data).')';exit;
        	}
            return  $this->returnData(self::RET_CODE_ERR_PHONE,'手机号码错误','');
        }

        $code = rand(100000,999999);
        $purpose  = config('purpose');//签名名称
        $mobiles= substr_replace($mobile,'****',3,4);
        if(empty($type_id)){
            $params   = "{\"name\":\"$mobiles\",\"product\":\"'聚合共享流量平台'\",\"code\":\"$code\"}";
            $template = config('sms_template_1');//短信模板
        }else{
            switch ($type_id){
                case 1:
                    $params   = "{\"name\":\"$mobiles\",\"product\":\"'聚合共享流量平台'\",\"code\":\"$code\"}";
                    $template = config('sms_template_1');//短信模板
                    break;
                //提现申请
                case 2:
                    $params   = "{\"name\":\"$mobiles\",\"product\":\"'聚合共享流量平台'\",\"code\":\"$code\"}";
                    $template = config('sms_template_2');//短信模板
                break;
                //申请成为代理商
                case 3:
                    $params   = "{\"name\":\"$mobiles\",\"product\":\"'聚合共享流量平台'\",\"code\":\"$code\"}";
                    $template = config('sms_template_3');//短信模板
                break;
                //申请更改密码
                case 4:
                	$params   = "{\"name\":\"$mobiles\",\"product\":\"'聚合共享流量平台'\",\"code\":\"$code\"}";
                	$template = config('sms_template_4');//短信模板
                break;
                default:
                    $params   = "{\"name\":\"$mobiles\",\"product\":\"'聚合共享流量平台'\",\"code\":\"$code\"}";
                    $template = config('sms_template_1');//短信模板
            }
        }


        $c = new \TopClient;
        $c->appkey    = config('appkey');//appkeu
        $c->secretKey = config('secretKey');//secreKey
        $req = new \AlibabaAliqinFcSmsNumSendRequest;
        $req->setExtend("");
        $req->setSmsType("normal");
        $req->setSmsFreeSignName($purpose);
        $req->setSmsParam($params);
        $req->setRecNum($mobile);
        $req->setSmsTemplateCode($template);
        $resp = $c->execute($req);

        if($resp->code || $resp->msg){
            if($callback){
                $data["code"] = self::RET_CODE_ERR_EMS;
                $data["msg"] = $resp;
                echo $callback.'('.json_encode($data).')';exit;
            }
            $test="短信发送失败，code：".$resp->code.',sub_code：'.$resp->sub_code.'msg：'.$resp->msg;
            trace($test,'info');
            return $this->returnData(self::RET_CODE_ERR_EMS,'短信发送失败',$test);
        }else{
            session('code',$code);
            session('mobile',$mobile);
            if($callback){
                $data["code"] = self::RET_CODE_OK;
                $data["msg"] = '短信发送成功';
                echo $callback.'('.json_encode($data).')';exit;
            }
            $test="短信发送成功，code：".$resp->code.',sub_code：'.$resp->sub_code.'msg：'.$resp->msg;
            trace($test,'info');
            return $this->returnData(self::RET_CODE_OK,'短信发送成功',$test);
        }
    }

    /*
      *注册与登录写在同一个接口
      * 2017.7.28
      * */

    public function check_login(){
        $checkcodes =session('code');
        if(request()->isPost()) {
            $username=input('post.username');//用户真实姓名
            if(!empty($username)){
                session('username',$username);
            }else{
                return $this->returnData(self::RET_CODE_ERR_GETDATA,'用户名不能为空','');
            }
            $phone = input('post.phone');//用户号码
            $code = trim(input('post.code'));//验证码
            $agent_id =session('agent_id');//代理商id
            $agent_codes = session('agent_code');;//验证信息
            $agent_type = session('agent_type');//跳转类型
            if(!preg_match("/^1[2345789]{1}\d{9}$/",$phone)){
                return  $this->returnData(self::RET_CODE_ERR_PHONE,'请填写正确的手机号码','');
            }
            if(empty($code)){
                return  $this->returnData(self::RET_CODE_ERR_PHONE,'验证码不能为空','');
            }
            if ($code == $checkcodes ) {
                $user_list = db('user')->where('phone', $phone)->find();
                if (empty($user_list)) {
                    $data['phone'] =$phone;//电话号码
                    $data['user_name'] =$username;//用户名
                    $data['create_time'] = date("Y-m-d H:i:s",time());;
                    if(isset($agent_id)){
                        $data['first_agent_id']=$agent_id;
                    }else{
                        $data['first_agent_id']=0;
                    }
                    $add = db('user')->insert($data);
                    if ($add) {
                        //清除当前验证码缓存
                        session('code',null);
                        //添加用户登录日志信息
                        $check=db('user')->field('id')->where('phone',$phone)->find();
                        $log['agent_id']=$agent_id;//代理id
                        $log['user_name']=$username;//用户真实姓名
                        $log['login_ip']=$_SERVER["REMOTE_ADDR"];//用户登录ip
                        $log['user_id']=$check['id'];//用户id
                        $log['type']=1;//登录状态
                        $log['remark']=$username.":用户通过：".$_SERVER["REMOTE_ADDR"]."ip地址登录";//登录状态
                        $log['create_time']=date("Y-m-d H:i:s",time());
                        $res=db('user_access_log')->insert($log);
                        //添加代理关系
                        if(!empty($agent_id) && !empty($agent_codes)){
                            $agent=db('agent')->field('id,phone,salt')->where('id',$agent_id)->find();
                            if(!empty($agent)){
                                $checks=md5($agent['id'].$agent['phone'].$agent['salt']);
                                if($checks==$agent_codes){
                                    //根据用户id判断该关系有没有建立，有的话修改，没的话添加
                                    $agent_user=db('agent_user')
                                        ->where('user_id',$check['id'])
                                        ->where('agent_id',$agent_id)
                                        ->find();
                                    if(empty($agent_user)){
                                        $age['agent_id']=$agent_id;
                                        $age['user_id']=$check['id'];
                                        $age['user_name']=$username;
                                        $age['create_time']=date("Y-m-d H:i:s",time());
                                        db('agent_user')->insert($age);
                                    }else{
                                        if(empty($agent_user['user_name'])){
                                            $ages['user_name'] =$username;//用户名
                                        }
                                        $ages['update_time']=date("Y-m-d H:i:s",time());
                                        db('agent_user')
                                            ->where('user_id',$check['id'])
                                            ->where('agent_id',$agent_id)
                                            ->update($ages);
                                    }
                                }
                            }
                        }
                        if($res){
                            session('phone',$phone);
                            session('agent_id',$agent_id);
                            cookie("login_id",$phone,'7200');
                            return $this->returnData(self::RET_CODE_ERR_SUSSREG,'注册成功，开始跳转',$agent_type);
                        }else{
                            return $this->returnData(self::RET_CODE_ERR_ERROREG,'注册失败，重新注册','');
                        }
                    } else {
                        return $this->returnData(self::RET_CODE_ERR_ERROREG,'注册失败，重新注册','');
                    }
                }else{
                    //如果用户存在，直接登录
                    if(empty($user_list['first_agent_id']) || $user_list['first_agent_id']==0 ){
                        if(isset($agent_id)){
                            $upload['first_agent_id'] =$agent_id;//用户名
                        }
                    }
                    if(empty($user_list['user_name'])){
                        $upload['user_name'] =$username;//用户名
                    }
                    if(!empty($upload)){
                        db('user')->where('id',$user_list['id'])->update($upload);
                    }
                        //清除当前验证码缓存
                        session('code',null);
                        $log['agent_id']=$agent_id;//代理id
                        $log['user_id']=$user_list['id'];//用户id
                        $log['user_name']=$username;//用户真实姓名
                        $log['login_ip']=$_SERVER["REMOTE_ADDR"];//用户登录ip
                        $log['type']=1;//登录状态
                        $log['remark']=$username.":用户通过：".$_SERVER["REMOTE_ADDR"]."ip地址登录";//登录状态
                        $log['create_time']=date("Y-m-d H:i:s",time());
                        $res=db('user_access_log')->insert($log);

                        //添加代理关系
                        if(!empty($agent_id) && !empty($agent_codes)){
                            $agent=db('agent')->field('id,phone,salt')->where('id',$agent_id)->find();
                            if(!empty($agent)){
                                $check=md5($agent['id'].$agent['phone'].$agent['salt']);
                                if($check==$agent_codes){
                                    //根据用户id判断该关系有没有建立，有的话修改，没的话添加
                                    $agent_user=db('agent_user')
                                        ->where('user_id',$user_list['id'])
                                        ->where('agent_id',$agent_id)
                                        ->find();
                                    if(empty($agent_user)){
                                        $age['agent_id']=$agent_id;
                                        $age['user_id']=$user_list['id'];
                                        $age['user_name']=$username;
                                        $age['create_time']=date("Y-m-d H:i:s",time());
                                        db('agent_user')->insert($age);
                                    }else{
                                        if(empty($agent_user['user_name'])){
                                            $ages['user_name']=$username;
                                        }
                                        $ages['update_time']=date("Y-m-d H:i:s",time());
                                        db('agent_user')->where('user_id',$user_list['id'])->where('agent_id',$agent_id)->update($ages);
                                    }
                                }
                            }
                        }
                        if($res){
                            session('phone',$phone);
                            session('agent_id',$agent_id);
                            cookie("login_id",$phone,'7200');
                            return $this->returnData(self::RET_CODE_ERR_SUSSREG,'登录成功，开始跳转',$agent_type);
                        }else{
                            return $this->returnData(self::RET_CODE_ERR_ERROREG,'登录失败，重新登录','');
                        }
                }
            } else {
                return $this->returnData(self::RET_CODE_ERR_EMSNO,'验证码不一致','');
            }
        }else{
            return $this->returnData(self::RET_CODE_ERR_EXEPTION,'数据操作异常','');
        }
    }



    /*
      *获取首页银行、信用卡推荐信息
      * 2017.7.31
      * */
    public function index(){
        header('content-type:text/html;charset=utf-8');
        //推荐银行
        $bank_list=db('bank')
            ->where('is_deleted',0)
            ->field('id,bank_name,bank_logo,bank_url,card_speed,average_amount,passing_rate')
            ->select();
        $bank_lists=array();
        foreach ($bank_list as $k=>$v){
            $bank_lists[]=array(
                'id'=>$v['id'],
                'bank_name'=>$v['bank_name'],
                'bank_logo'=>config('file_url').$v['bank_logo'],
                'bank_url'=>$v['bank_url'],
                'card_speed'=>$v['card_speed'],
                'average_amount'=>$v['average_amount'],
                'passing_rate'=>$v['passing_rate'],
            );
        }

        //精品推荐

        //推荐银行卡
        $bank_card_hot=db('bank_card')
            ->alias('a')
            ->join('bank b','a.bank_id=b.id and b.is_deleted=0')
            ->field('a.id,a.card_name,a.card_logo,a.card_url,a.card_details,a.apply_number,a.follow_number,a.displayorder')
            ->where('a.is_recommend',1)
            ->where('a.is_deleted',0)
            ->limit(3)
            ->order('displayorder', 'asc')
            ->select();
//        var_dump($bank_card_hot);exit;
        $bank_cards_hots=array();
        foreach ($bank_card_hot as $key=>$val){
            $bank_cards_hots[]=array(
                'id'=>$val['id'],
                'card_name'=>$val['card_name'],
                'card_logo'=>config('file_url').$val['card_logo'],
                'card_details'=>$val['card_details'],
                'card_url'=>$val['card_url'],
                'apply_number'=>$val['apply_number'],
                'follow_number'=>$val['follow_number'],
                'displayorder'=>$val['displayorder'],
            );
        }

        //var_dump($bank_cards_hots);exit;
        //主题精选
        $index_theme=db('theme')->field('id,name,remark,logo')->limit(4)->order('id', 'desc')->select();
        $index_themes=array();
        foreach ($index_theme as $key=>$val){
            $index_themes[]=array(
                'id'=>$val['id'],
                'name'=>$val['name'],
                'card_logo'=>config('file_url').'/uploads'.'/'.$val['logo'],
                'remark'=>$val['remark'],
            );
        }
        //推荐银行卡
        $bank_card=db('bank_card')
            ->alias('a')
            ->join('bank b','a.bank_id=b.id and b.is_deleted=0')
            ->field('a.id,a.card_name,a.card_logo,a.card_url,a.card_details,a.apply_number,a.follow_number')
            ->where('a.is_hot',1)
            ->where('a.is_deleted',0)
            ->order('a.displayorder', 'asc')
            ->select();
       // var_dump($bank_card);exit;
        $bank_cards=array();
        foreach ($bank_card as $key=>$val){
            $bank_cards[]=array(
                'id'=>$val['id'],
                'card_name'=>$val['card_name'],
                'card_logo'=>config('file_url').$val['card_logo'],
                'card_details'=>$val['card_details'],
                'card_url'=>$val['card_url'],
                'apply_number'=>$val['apply_number'],
                'follow_number'=>$val['follow_number'],
            );
        }
        $allList=array(
            'bank_list'=>$bank_lists, //银行信息
            'index_theme'=>$index_themes,//主题精选
            'bank_card'=>$bank_cards,  //银行卡信息
            'bank_cards_hot'=>$bank_cards_hots  //精品推荐
        );
        return $this->returnData(self::RET_CODE_OK,'获取信息成功',$allList);
    }

    /*
     * 获取对应主题对应银行卡信息
     * 2.17.8.1
     * */
    public function theme_list()
    {
        if(request()->isPost()){
            $id=input('post.id');
           // $id=1;
            //根据精选id获取对应银行卡id，再把所有的银行卡信息查询出来
            $theme=db('theme_bank_card')->where('theme_id',$id)->field('card_id')->select();
            if(empty($theme)){
                return $this->returnData(self::RET_CODE_ERR_GETDATA,'没有对应数据','');
            }else{
                $a='';
                foreach($theme as $key=>$val){
                    $a .= $val["card_id"].",";
                }
                $bank_card=db('bank_card')->field('id,card_name,card_logo,card_url,card_details,apply_number,follow_number')->where('id','in',$a)->where('is_deleted',0)->select();
                $bank_cards=array();
                foreach ($bank_card as $key=>$val){
                    $bank_cards[]=array(
                        'id'=>$val['id'],
                        'card_name'=>$val['card_name'],
                        'card_logo'=>config('file_url').$val['card_logo'],
                        'card_details'=>$val['card_details'],
                        'card_url'=>$val['card_url'],
                        'apply_number'=>$val['apply_number'],
                        'follow_number'=>$val['follow_number'],
                    );
                }
                if(!empty($bank_card)){
                    return $this->returnData(self::RET_CODE_OK,'获取信息成功',$bank_cards);
                }else{
                    return $this->returnData(self::RET_CODE_ERR_GETDATA,'没有对应数据','');
                }
            }
        }else{
            return $this->returnData(self::RET_CODE_ERR_EXEPTION,'数据操作异常','');
        }
    }


    /*
    *获取银行信息
    * 2017.7.31
    * */
    public function bank_list(){
        $bank=db('bank')
            ->where('is_deleted',0)
            ->field('id,bank_name,bank_detail,bank_logo,bank_phone,bank_url,card_speed,average_amount,passing_rate')
            ->select();
        $banks=array();
        foreach ($bank as $key=>$val){
            $banks[]=array(
                'id'=>$val['id'],
                'bank_name'=>$val['bank_name'],
                'bank_phone'=>$val['bank_phone'],
                'bank_url'=>$val['bank_url'],
                'bank_logo'=>config('file_url').$val['bank_logo'],
                'bank_detail'=>$val['bank_detail'],
                'card_speed'=>$val['card_speed'],
                'average_amount'=>$val['average_amount'],
                'passing_rate'=>$val['passing_rate'],
            );
        }
        if(!empty($bank)){
            return $this->returnData(self::RET_CODE_OK,'获取信息成功',$banks);
        }else{
            return $this->returnData(self::RET_CODE_ERR_GETDATA,'获取信息失败','');
        }
    }


     /*
    *获取银行卡信息
    * 2017.7.31
    * */
    public function card_list(){
        //根据提交信息获取对应的信息
       if(request()->isPost()){
           $check=input('post.check');
          //  $check=1;
            if($check==1){
                $bank=db('bank_card')
                    ->alias('a')
                    ->join('bank b','a.bank_id=b.id and b.is_deleted=0')
                    ->field('a.id,a.card_name,a.card_logo,a.card_url,a.card_details,a.apply_number,a.follow_number,b.bank_name')                      ->where('a.card_type_id',1)
                    ->where('a.is_deleted',0)
                    ->order('a.displayorder','asc')
                    ->select();

                $banks=array();
                foreach ($bank as $key=>$val){
                    $count=db('bank_card_log')->where('user_id',$val['id'])->count();
                    $banks[]=array(
                        'id'=>$val['id'],
                        'card_name'=>$val['card_name'],
                        'card_url'=>$val['card_url'],
                        'card_details'=>$val['card_details'],
                        'apply_number'=>$val['apply_number'],
                        'follow_number'=>$val['follow_number'],
                        'card_logo'=>config('file_url').$val['card_logo'],
                        'bank_name'=>$val['bank_name'],
                        'count'=>$count
                    );
                }
                if(!empty($bank)){
                    return $this->returnData(self::RET_CODE_OK,'获取信息成功',$banks);
                }else{
                    return $this->returnData(self::RET_CODE_ERR_GETDATA,'获取信息失败','');
                }
            }else if($check==2){
                $bank=db('bank_card')->alias('a')->join('bank b','a.bank_id=b.id and b.is_deleted=0')->field('a.id,a.card_name,a.card_logo,a.card_url,a.card_details,a.apply_number,a.follow_number,b.bank_name')->where('a.card_type_id',2)->where('a.is_deleted',0)->order('a.id','desc')->select();
                $banks=array();
                foreach ($bank as $key=>$val){
                    $count=db('bank_card_log')->where('user_id',$val['id'])->count();
                    $banks[]=array(
                        'id'=>$val['id'],
                        'card_name'=>$val['card_name'],
                        'card_url'=>$val['card_url'],
                        'card_details'=>$val['card_details'],
                        'apply_number'=>$val['apply_number'],
                        'follow_number'=>$val['follow_number'],
                        'card_logo'=>config('file_url').$val['card_logo'],
                        'bank_name'=>$val['bank_name'],
                        'count'=>$count
                    );
                }
                if(!empty($bank)){
                    return $this->returnData(self::RET_CODE_OK,'获取信息成功',$banks);
                }else{
                    return $this->returnData(self::RET_CODE_ERR_GETDATA,'获取信息失败','');
                }
            }else{
                return $this->returnData(self::RET_CODE_ERR_ARGUMENT,'参数不合格','');

            }
        }else{
            return $this->returnData(self::RET_CODE_ERR_EXEPTION,'数据操作异常','');
        }
    }


    /*
     * 贷款公司接口
     * 2.17.8.1
     * */
    public function loan(){
        $loan=db('loan')->field('id,name,address,phone,logo,comment,loan_url')->where('is_deleted',0)->order('id','desc')->select();
        $loans=array();
        foreach ($loan as $key=>$val){
            $loans[]=array(
                'id'=>$val['id'],
                'name'=>$val['name'],
                'address'=>$val['address'],
                'phone'=>$val['phone'],
                'logo'=>config('file_url').$val['logo'],
                'comment'=>$val['comment'],
            );
        }
        if(!empty($loan)){
            return $this->returnData(self::RET_CODE_OK,'获取信息成功',$loans);
        }else{
            return $this->returnData(self::RET_CODE_ERR_GETDATA,'获取信息失败','');
        }

    }

    /*
    * 贷款公司业务接口
    * 2.17.8.1
    * */
    public function loan_list(){
       if(request()->isPost()){
           $check=input('post.check');
            if($check==1){
                $loan=db('loan_product')
                    ->alias('a')
                    ->join('loan b','a.loan_id=b.id and b.is_deleted=0')
                    ->field('a.id,a.name,a.product_logo,a.product_url,a.product_details,a.add_time')
                    ->where('a.product_type',$check)
                    ->where('a.is_deleted',0)
                    ->order('a.displayorder','asc')
                    ->select();

                $loans=array();
                foreach ($loan as $key=>$val){
                    $loans[]=array(
                        'id'=>$val['id'],
                        'name'=>$val['name'],
                        'logo'=>config('file_url').$val['product_logo'],
                        'comment'=>$val['product_details'],
                        'loan_url'=>$val['product_url'],
                    );
                }
                if(!empty($loan)){
                    return $this->returnData(self::RET_CODE_OK,'获取信息成功',$loans);
                }else{
                    return $this->returnData(self::RET_CODE_ERR_GETDATA,'获取信息失败','');
                }
            }else if($check==2){
                $loan=db('loan_product')
                    ->alias('a')
                    ->join('loan b','a.loan_id=b.id and b.is_deleted=0')
                    ->field('a.id,a.name,a.product_logo,a.product_url,a.product_details,a.add_time')
                    ->where('a.product_type',$check)
                    ->where('a.is_deleted',0)
                    ->order('a.id','desc')
                    ->select();
                $loans=array();
                foreach ($loan as $key=>$val){
                    $loans[]=array(
                        'id'=>$val['id'],
                        'name'=>$val['name'],
                        'logo'=>config('file_url').$val['product_logo'],
                        'comment'=>$val['product_details'],
                        'loan_url'=>$val['product_url'],
                    );
                }
                if(!empty($loan)){
                    return $this->returnData(self::RET_CODE_OK,'获取信息成功',$loans);
                }else{
                    return $this->returnData(self::RET_CODE_ERR_GETDATA,'获取信息失败','');
                }
            }else{
                return $this->returnData(self::RET_CODE_ERR_ARGUMENT,'参数不合格','');
            }

        }else{
            return $this->returnData(self::RET_CODE_ERR_EXEPTION,'数据操作异常','');
        }

    }



    /*
     * 我的信用卡接口
     * 2.17.8.1
     * */
    public function my_card(){

      $phone=!empty(session('phone'))?session('phone'):cookie('login_id');
         $check_user=db('user')->field('id')->where('phone',$phone)->find();
         if(!empty($check_user)){
             $bank_card=db('bank_card_log')->alias('a')->join('bank_card b','a.card_id=b.id','left')->field('b.id,b.card_name,b.card_logo,b.card_url,b.card_details,a.confirm_time')->where('a.user_id','eq',$check_user['id'])->where('a.status',2)->select();
             foreach ($bank_card as $key=>$val){
                 $bank_cards[]=array(
                     'id'=>$val['id'],
                     'card_name'=>$val['card_name'],
                     'card_logo'=>config('file_url').$val['card_logo'],
                     'card_details'=>$val['card_details'],
                     'confirm_time'=>$val['confirm_time'],
                 );
             }
             if(!empty($bank_card)){
                 return $this->returnData(self::RET_CODE_OK,'获取信息成功',$bank_cards);
             }else{
                 return $this->returnData(self::RET_CODE_ERR_GETDATA,'没有对应数据','');
             }
         }else{
             return $this->returnData(self::RET_CODE_ERR_GETDATA,'请先登录','');
         }
    }

    /*
       * 我的贷款接口
       * 2.17.8.1
     * */
    public function loan_log(){
        $phone=!empty(session('phone'))?session('phone'):cookie('login_id');
        $check_user=db('user')->field('id')->where('phone',$phone)->find();
        if(!empty($check_user)){
            $loan_list=db('loan_log')->alias('a')->join('loan b','a.loan_product_id=b.id','left')->field('b.id,b.name,b.logo,a.apply_time,a.loan_price,a.confirm_time')->where('a.user_id','eq',$check_user['id'])->where('a.status',2)->select();
            $loan_lists=array();
            foreach ($loan_list as $key=>$val){
                $loan_lists[]=array(
                    'id'=>$val['id'],
                    'name'=>$val['name'],
                    'logo'=>config('file_url').$val['logo'],
                    'loan_price'=>$val['loan_price'],
                    'confirm_time'=>$val['confirm_time'],
                );
            }
            if(!empty($loan_list)){
                return $this->returnData(self::RET_CODE_OK,'获取信息成功',$loan_lists);
            }else{
                return $this->returnData(self::RET_CODE_ERR_GETDATA,'获取数据为空','');
            }
        }else{
            return $this->returnData(self::RET_CODE_ERR_GETDATA,'请先登录','');
        }
    }


    /*
       * 获取用户信息接口
       * 2.17.7.31
     * */
//    public function user_list(){
//        $phone=session('phone');
//        if(!empty($phone)){
//            $menber=db('user')->field('id,user_name,headimg,phone,total_loan')->where('phone',$phone)->find();
//            $count_price=db('loan_log')->where('user_id',$menber['id'])->where('status',2)->field("IFNULL(SUM(loan_price),0) tp_sum")->find();
//            $bank_applying_nub=db('bank_card_log')->where('user_id',$menber['id'])->where('status',1)->count();
//            $loan_applying_nub=db('loan_log')->where('user_id',$menber['id'])->where('status',1)->count();
//            $bank_passed_nub=db('bank_card_log')->where('user_id',$menber['id'])->where('status',2)->count();
//            $loan_passed_nub=db('loan_log')->where('user_id',$menber['id'])->where('status',2)->count();
//            $menbers['id']=$menber['id'];
//            $menbers['user_name']=!empty($menber['user_name'])?$menber['user_name']:$menber['phone'];
//            $menbers['phone']=$menber['phone'];
//            $menbers['check']="登录";
//            $menbers['total_loan']=($count_price['tp_sum']*100)/100;
//            $menbers['applying_nub']=$bank_applying_nub;
//            $menbers['passed_nub']=$bank_applying_nub + $loan_applying_nub;
//            $menbers['invite_nub']=$bank_passed_nub;
//            $menbers['headimg'] = !empty($menber['headimg']) ? config('file_url').$menber['headimg'] :$_SERVER['HTTP_HOST'].'/assets/user/images/yhmr.png';
//            if(!empty($menber)){
//                $date['total_loan']=$count_price['tp_sum'];
//                db('user')->where('id',$menber['id'])->update($date);
//                return $this->returnData(self::RET_CODE_OK,'获取信息成功',$menbers);
//            }else{
//                return $this->returnData(self::RET_CODE_ERR_GETDATA,'获取信息失败','');
//            }
//        }else{
//            return $this->returnData(self::RET_CODE_ERR_GETDATA,'请先登录','');
//        }
//    }

    public function user_list(){
        $phone=!empty(session('phone'))?session('phone'):cookie('login_id');
        if(!empty($phone)){
            $menber=db('user')->field('id,user_name,headimg,phone,total_loan,total_card')->where('phone',$phone)->find();
            $menbers['id']=$menber['id'];
            $menbers['user_name']=!empty($menber['user_name'])?$menber['user_name']:$menber['phone'];
            $menbers['phone']=$menber['phone'];
            $menbers['check']="登录";
            $menbers['total_loan']=($menber['total_loan']*100)/100;
            $menbers['invite_nub']=$menber['total_card'];
            $menbers['headimg'] = !empty($menber['headimg']) ? config('file_url').$menber['headimg'] :$_SERVER['HTTP_HOST'].'/assets/user/images/yhmr.png';
            if(!empty($menber)){
                return $this->returnData(self::RET_CODE_OK,'获取信息成功',$menbers);
            }else{
                return $this->returnData(self::RET_CODE_ERR_GETDATA,'获取信息失败','');
            }
        }else{
            return $this->returnData(self::RET_CODE_ERR_GETDATA,'请先登录','');
        }
    }

    /*
       * 申请中的信息接口
       * 2.17.8.1
     * */
    public function applying_list(){
        $phone=!empty(session('phone'))?session('phone'):cookie('login_id');
        $check_user=db('user')->field('id')->where('phone',$phone)->find();
        if(!empty($check_user)){
            //获取信用卡信息
        	$bank_card=db('bank_card_log')->alias('a')->join('bank_card b','a.card_id=b.id','left')->field('b.id,b.card_name,b.card_logo,b.card_url,b.card_details,a.apply_time,a.confirm_time')->where('a.user_id','eq',$check_user['id'])->where('a.status',1)->select();
        	$bank_cards=array();
            foreach ($bank_card as $key=>$val){
                $bank_cards[]=array(
                    'id'=>$val['id'],
                    'name'=>$val['card_name'],
                    'logo'=>config('file_url').$val['card_logo'],
                    'card_details'=>$val['card_details'],
//                     'card_url'=>$val['card_url'],
                    'confirm_time'=>$val['apply_time'],
                );
            }
            //获取贷款信息
            $loan_list=db('loan_log')->alias('a')->join('loan b','a.loan_product_id=b.id','left')->field('b.id,b.name,b.logo,a.apply_time,a.loan_price,a.apply_time,a.confirm_time')->where('a.user_id','eq',$check_user['id'])->where('a.status',1)->select();
            $loan_lists=array();
            foreach ($loan_list as $key=>$val){
                $loan_lists[]=array(
                    'id'=>$val['id'],
                    'name'=>$val['name'],
                    'logo'=>config('file_url').$val['logo'],
                    'loan_price'=>$val['loan_price'],
                    'confirm_time'=>$val['apply_time'],
                );
            }
            $all=array_merge($bank_cards, $loan_lists);
            if(!empty($bank_card) || !empty($loan_list)){
                return $this->returnData(self::RET_CODE_OK,'获取信息成功',$all);
            }else{
                return $this->returnData(self::RET_CODE_ERR_GETDATA,'没有对应数据','');
            }
        }else{
            return $this->returnData(self::RET_CODE_ERR_GETDATA,'请先登录','');
        }
    }

    /*
       * 驳回的信息接口
       * 2.17.8.1
     * */
    public function reject_list(){
       $phone=!empty(session('phone'))?session('phone'):cookie('login_id');
        $check_user=db('user')->field('id')->where('phone',$phone)->find();
        if(!empty($check_user)){
           $bank_card=db('bank_card_log')->alias('a')->join('bank_card b','a.card_id=b.id','left')->field('b.id,b.card_name,b.card_logo,b.card_url,b.card_details,a.apply_time,a.confirm_time')->where('a.user_id','eq',$check_user['id'])->where('a.status',3)->select();
            $bank_cards=array();
            foreach ($bank_card as $key=>$val){
                $bank_cards[]=array(
                    'id'=>$val['id'],
                    'name'=>$val['card_name'],
                    'logo'=>config('file_url').$val['card_logo'],
                    'card_details'=>$val['card_details'],
                    'confirm_time'=>$val['confirm_time'],
                );
            }

            $loan_list=db('loan_log')->alias('a')->join('loan b','a.loan_product_id=b.id','left')->field('b.id,b.name,b.logo,a.apply_time,a.loan_price,a.apply_time,a.confirm_time')->where('a.user_id','eq',$check_user['id'])->where('a.status',3)->select();
            $loan_lists=array();
            foreach ($loan_list as $key=>$val){
                $loan_lists[]=array(
                    'id'=>$val['id'],
                    'name'=>$val['name'],
                    'logo'=>config('file_url').$val['logo'],
                    'loan_price'=>$val['loan_price'],
                    'confirm_time'=>$val['confirm_time'],
                );
            }
            $all=array_merge($bank_cards, $loan_lists);
            if(!empty($bank_card) || !empty($loan_list)){
                return $this->returnData(self::RET_CODE_OK,'获取信息成功',$all);
            }else{
                return $this->returnData(self::RET_CODE_ERR_GETDATA,'没有对应数据','');
            }
        }else{
            return $this->returnData(self::RET_CODE_ERR_GETDATA,'请先登录','');
        }
    }


    /*
        * 添加反馈信息
        * 2.17.7.31
      * */
    public function tickling(){
        $phone=!empty(session('phone'))?session('phone'):cookie('login_id');
        $check_user=db('user')->field('id')->where('phone',$phone)->find();
        if(!empty($check_user)){
            $data['user_id']=$check_user['id'];
            $data['user_type']=2;
            $data['content']=input('post.content');
            $data['create_time']=time();
            $res=db('sye_feedback')->insert($data);
            if($res){
                return $this->returnData(self::RET_CODE_OK,'添加反馈信息成功','');
            }else{
                return $this->returnData(self::RET_CODE_ERR_GETDATA,'获取信息失败','');
            }
        }else{
            return $this->returnData(self::RET_CODE_ERR_GETDATA,'请先登录','');
        }
    }

/*
* 退出登录
* 2017.8.3
* */
    public  function out_login(){
        $phones=!empty(session('phone'))?session('phone'):cookie('login_id');
        if($phones==null){
              return $this->returnData(self::RET_CODE_ERR_NOTLOGIN,'用户未登陆','');
        }else{
            $phone=session('phone',null);
            $phones=cookie('login_id',null);
            if($phone == null){
                return $this->returnData(self::RET_CODE_OK,'退出成功','');
            }else{
                return $this->returnData(self::RET_CODE_ERR_GETDATA,'退出失败','');
            }
        }

    }

    /*
    * 贷款跳转接口
    * 2017.8.8
    * */
    public function loan_url(){
        header('content-type:text/html;charset=utf-8');
        //获取信用信息id，和根据电话查当前的上级代理id,根据贷款信息id查询
       // $id=1;//信贷信息id
       if(request()->isPost()){
             $id=input('post.id');//信贷信息id
            if(!empty($id)){
            $phones=!empty(session('phone'))?session('phone'):cookie('login_id');
            $agent_id=session('agent_id');
            $user_name=session('username');
                //获取用户id
                $user=db('user')->field('id')->where('phone',$phones)->find();
                //获取对应贷款信息
                $loan=db('loan_product')->field('id,name,product_url,product_style')->where('id',$id)->find();

                //添加默认订单
                if(!empty($agent_id)){
                    $card_log_check=db('loan_log')
                        ->where('user_id',$user['id'])
                        ->where('agent_id',$agent_id)
                        ->where('loan_product_id',$loan['id'])
                        ->find();
                    if(empty($card_log_check)){
                        $card_log_list=array(
                            'user_id'=>$user['id'],
                            'loan_product_id'=>$loan['id'],
                            'user_name'=>$user_name,
                            'user_phone'=>$phones,
                            'agent_id'=>$agent_id,
                            'status'=>1,
                            'loan_price'=>0,
                            'comment'=>'用户点击链接进入，默认为申请中',
                            'apply_time'=>date("Y-m-d H:i:s",time()),

                        );
                        db('loan_log')->insert($card_log_list);
                    }

                }

                //获取关联代理商id
                $agent=db('agent_user')
                    ->field('agent_id')
                    ->where('user_id',$user['id'])
                    ->where('agent_id',$agent_id)
                    ->find();



                //添加点击事件，生成log文件
                if(!empty($agent)){
                    $agent_user=db('agent')->field('id,name')->where('id',$agent['agent_id'])->find();

                   // var_dump($agent_user);exit;
                    $log['agent_id']=$agent_user['id'];//代理id
                    $log['remark']=$phones."用户通过:".$agent_user['name']."代理商发送的链接"."点击进入:"."'".$loan['name']."‘"."链接";//跳转的务
                }else{
                    $log['remark']=$phones."用户"."点击进入:"."'".$loan['name']."‘"."链接";//跳转的务
                }
                $log['user_name']=session('username');//用户真实姓名
                $log['login_ip']=$_SERVER["REMOTE_ADDR"];//用户登录ip
                $log['user_id']=$user['id'];//用户id
                $log['type']=3;//状态
                $log['loan_id']=$loan['id'];//跳转的业务id
                $log['create_time']=date("Y-m-d H:i:s",time());
                db('user_access_log')->insert($log);
                if($loan['product_style']==1){
                    $data['url']=$loan['product_url'];
                    return $this->returnData(self::RET_CODE_OK,'获取信息成功',$data);
                }else{
                    switch ($loan['name']){
                        case "拍拍贷":
                            $list= $this->Port->paipaidai_url($phones);
                            if($list['Code']== -1){
                                $data['url']=$loan['product_url'];
                            }else{
                                $data['url']=urldecode($list['Content']['RedirectUrl']);
                            }
                            return $this->returnData(self::RET_CODE_OK,'获取信息成功',$data);
                            break;
                    }
                }

            }else{
                return $this->returnData(self::RET_CODE_ERR_EXEPTION,'获取对应信息失败',$id);
            }
        }else{
            return $this->returnData(self::RET_CODE_ERR_EXEPTION,'数据操作异常','');
        }
    }


    /*
 * 银行跳转接口
 * 2017.8.10（9.15：添加信用卡佣金录入）
 * */
    public function bank_url(){
        //获取信用信息id，和根据电话查当前的上级代理id,根据贷款信息id查询
         $id=input('post.id');//银行信息id
       //$id=1;//银行信息id
        $agent_id=session('agent_id');
        $phones=!empty(session('phone'))?session('phone'):cookie('login_id');
        $user_name=session('username');
        if(!empty($id)){
          if(request()->isPost()){
                //获取用户id

              $user=db('user')->field('id')->where('phone',$phones)->find();
              $loan=db('bank_card')->field('id,bank_id,card_name,card_url,apply_number,follow_number')->where('id',$id)->where('is_deleted',0)->find();

              //添加默认订单
              if(!empty($agent_id)){
                  $card_log_check=db('bank_card_log')
                      ->where('user_id',$user['id'])
                      ->where('agent_id',$agent_id)
                      ->where('card_id',$loan['id'])
                      ->find();

                  if(empty($card_log_check)){

                      $url=config('api_url')."agent/getCommissionByAgentID";
                      $data=array(
                          'agentid'=>$agent_id,
                          'card_id'=>$loan['bank_id'],
                          'type'=>1,
                          'money'=>(int)0,
                      );
                      $a=http_request_post($url,$data);
                      $all= json_decode($a,TRUE);
                      foreach ($all['data'] as $k=>$v){
                          if($v['agentid']==$agent_id){
                              $card_log_list['commission']=$v['money'];
                              $card_log_list['apply_time']=date('Y-m-d H:i:s',time());
                              $card_log_list['status']=1;
                              $card_log_list['user_id']=$user['id'];
                              $card_log_list['card_id']=$loan['id'];
                              $card_log_list['user_name']=$user_name;
                              $card_log_list['user_phone']=$phones;
                              $card_log_list['agent_id']=$agent_id;
                              $card_log_list['status']=1;
                              $card_log_list['comment']='用户点击链接进入，默认为申请中';
                              $card_log_list['apply_time']=date("Y-m-d H:i:s",time());
                          }
                      }
                      db('bank_card_log')->insert($card_log_list);
                  }

              }

              //获取代理和用户关系
                $agent=db('agent_user')
                    ->field('agent_id')
                    ->where('user_id',$user['id'])
                    ->where('agent_id',$agent_id)
                    ->find();
                //获取对应银行卡信息
                //添加点击事件，生成log文件
                if(!empty($agent)){
                    $agent_user=db('agent')
                        ->field('id,name')
                        ->where('id',$agent['agent_id'])
                        ->find();

                    $log['agent_id']=$agent_user['id'];//代理id
                    $log['remark']=$phones."用户通过:".$agent_user['name']."代理商发送的链接"."点击进入:"."'".$loan['card_name']."‘"."链接";//跳转的务
                }else{
                    $log['remark']=$phones."用户点击进入:"."'".$loan['card_name']."‘"."链接";//跳转的务
                }
                $log['user_id']=$user['id'];//用户id
                $log['user_name']=session('username');//用户真实姓名
                $log['login_ip']=$_SERVER["REMOTE_ADDR"];//用户登录ip
                $log['type']=2;//状态
                $log['bank_id']=$loan['id'];//跳转的业务id
                $log['create_time']=date("Y-m-d H:i:s",time());
                db('user_access_log')->insert($log);
                if(!empty($loan)){
                    $data['url']=$loan['card_url'];
                    return $this->returnData(self::RET_CODE_OK,'获取信息成功',$data);
                }else{
                    return $this->returnData(self::RET_CODE_ERR_EXEPTION,'获取对应信息失败',$id);
                }
            }else{
                return $this->returnData(self::RET_CODE_ERR_EXEPTION,'数据操作异常','');
            }
        }else{
            return $this->returnData(self::RET_CODE_ERR_EXEPTION,'获取对应信息失败',$id);
        }

    }


    /*
 * 获取银行对应银行卡信息
 * 2017.8.9
 * */
    public function bank_to_card()
    {
        if(request()->isPost()){
            $id=input('post.id');
            //获取银行信息
            $bank=db('bank')
                ->field('id,bank_name,bank_detail,bank_logo,card_speed,average_amount,passing_rate')
                ->where('id',$id)
                ->where('is_deleted',0)
                ->find();
            $banks=array(
                'id'=>$bank['id'],
                'bank_name'=>$bank['bank_name'],
                'bank_detail'=>$bank['bank_detail'],
                'bank_logo'=>config('file_url').$bank['bank_logo'],
                'card_speed'=>$bank['card_speed'],
                'average_amount'=>$bank['average_amount'],
                'passing_rate'=>$bank['passing_rate'],
            );
            //获取对应银行卡信息
            $bank_card=db('bank_card')
                ->field('id,card_name,card_logo,card_url,card_details,apply_number,follow_number')
                ->where('bank_id',$id)
                ->where('is_deleted',0)
                ->order('displayorder','asc')
                ->select();
            $bank_cards=array();
            foreach ($bank_card as $key=>$val){
                $bank_cards[]=array(
                    'id'=>$val['id'],
                    'card_name'=>$val['card_name'],
                    'card_logo'=>config('file_url').$val['card_logo'],
                    'card_details'=>$val['card_details'],
                    'card_url'=>$val['card_url'],
                    'apply_number'=>$val['apply_number'],
                    'follow_number'=>$val['follow_number'],
                );
            }
            $list=array(
                'bank'=>$banks,
                'bank_card'=>$bank_cards
            );
            if(!empty($bank)){
                return $this->returnData(self::RET_CODE_OK,'获取信息成功',$list);
            }else{
                return $this->returnData(self::RET_CODE_ERR_EXEPTION,'获取信息失败','');
            }
        }else{
            return $this->returnData(self::RET_CODE_ERR_EXEPTION,'数据操作异常','');
        }
    }


    /***
     * data 2017.08.15(9.7修改，添加利率参数)
     * 信贷跳转页面数据接口
     *
     */
    public function loan_product_url(){
        header('content-type:text/html;charset=utf-8');
    $id=input('post.id');
   // $id=1;
        $productlist=db('loan_product')->where('id',$id)->where('is_deleted',0)->find();
        $flow=db('loan_flow')->field('id,flow_name,flow_img')->where('id','in',$productlist['loan_flow'])->select();
        $flows=array();
        foreach ($flow as $k=>$v){
           $flows[]=array(
               'flow_name'=>$v['flow_name'],
               'flow_img'=>config('file_url').$v['flow_img']
               );
        }
        $productlist['repayment_cycle_range'];
        $all_days=explode('-',$productlist['repayment_cycle_range']);
        $productdata=array(
            'id'=>$productlist['id'],
            'unit_rate'=>$productlist['unit_rate'],
            'interest_rate'=>(int)($productlist['interest_rate']*100)/100,
            'repayment_cycle_range'=>isset($all_days)?$all_days:0,
            'interest_free_days'=>$productlist['interest_free_days'],
            'product_logo'=>config('file_url').$productlist['product_logo'],
            'product_url'=>$productlist['product_url'],
            'product_details'=>$productlist['product_details'],
            'min_loan_price'=>$productlist['min_loan_price'],
            'max_loan_price'=>$productlist['max_loan_price'],
            'apply_number'=>$productlist['apply_number'],
            'play_type'=>$productlist['play_type'],
            'loan_range'=>$productlist['loan_range'],
            'loan_term'=>$productlist['loan_term'],
            'loan_flow'=>$flows,
            'product_comment'=>explode('/n',$productlist['product_comment']),
            'product_datum'=>$productlist['product_datum'],
        );
     // var_dump($productdata);exit;
        if(!empty($productlist)){
            return $this->returnData(self::RET_CODE_OK,'获取信息成功',$productdata);
        }else{
            return $this->returnData(self::RET_CODE_ERR_EXEPTION,'获取信息失败','');
        }
    }

      /*
      *登录接口
      * 2017.8.31
      * */

     public function login()
     {
          $checkcodes = session('code');
          if (request()->isPost()) {
              $phone = input('post.phone');//用户号码
              $code = trim(input('post.code'));//验证码
              $agent_id = session('agent_id');//代理商id
              $agent_codes =  session('agent_code');;//验证信息
              $agent_type = session('agent_type');//跳转类型
              if (!preg_match("/^1[2345789]{1}\d{9}$/", $phone)) {
                  return $this->returnData(self::RET_CODE_ERR_PHONE, '请填写正确的手机号码', '');
              }
              if (empty($code)) {
                  return $this->returnData(self::RET_CODE_ERR_PHONE, '验证码不能为空', '');
              }
              if ($checkcodes == $code) {
                      $checklogin = db('user')->where('phone', $phone)->find();
                      if (!empty($checklogin)) {
                          //清除当前验证码缓存
                          session('code', null);
                          $log['agent_id'] = $agent_id;//代理id
                          $log['user_id'] = $checklogin['id'];//用户id
                          $log['user_name'] = $checklogin['user_name'];//用户真实姓名
                          $log['login_ip'] = $_SERVER["REMOTE_ADDR"];//用户登录ip
                          $log['type'] = 1;//登录状态
                          if (empty($checklogin['user_name'])) {
                              $log['remark'] = $checklogin['phone'] . ":用户通过：" . $_SERVER["REMOTE_ADDR"] . "ip地址登录";//登录状态
                          } else {
                              $log['remark'] = $checklogin['user_name'] . ":用户通过：" . $_SERVER["REMOTE_ADDR"] . "ip地址登录";//登录状态
                          }
                          $log['create_time'] = date("Y-m-d H:i:s", time());
                          $res = db('user_access_log')->insert($log);
                          //添加代理关系
                          if (!empty($agent_id) && !empty($agent_codes)) {
                              $agent = db('agent')->field('id,phone,salt')->where('id', $agent_id)->find();
                              if (!empty($agent)) {
                                  $checks = md5($agent['id'] . $agent['phone'] . $agent['salt']);
                                  if ($checks == $agent_codes) {
                                      //根据用户id判断该关系有没有建立，有的话修改，没的话添加
                                      $agent_user = db('agent_user')
                                          ->where('user_id', $checklogin['id'])
                                          ->where('agent_id', $agent_id)
                                          ->find();
                                      if (empty($agent_user)) {
                                          $age['agent_id'] = $agent_id;
                                          $age['user_id'] = $checklogin['id'];
                                          $age['user_name'] = $checklogin['user_name'];
                                          $age['create_time'] = date("Y-m-d H:i:s", time());
                                          db('agent_user')->insert($age);
                                      } else {
                                          if (empty($agent_user['user_name'])) {
                                              $ages['user_name'] = $checklogin['user_name'];
                                          }
                                          $ages['update_time'] = date("Y-m-d H:i:s", time());
                                          db('agent_user')->where('user_id', $checklogin['id'])->where('agent_id',$agent_id)->update($ages);
                                      }
                                  }
                              }
                          }
                      
                          if ($res) {
                              session('phone', $phone);
                              session('agent_id', $agent_id);
                              session('username', $checklogin['user_name']);
                              return $this->returnData(self::RET_CODE_ERR_SUSSREG, '登录成功，开始跳转', $agent_type);
                          } else {
                              return $this->returnData(self::RET_CODE_ERR_ERROREG, '登录失败，重新登录', '');
                          }
                      } else {
                          return $this->returnData(self::RET_CODE_ERR_NOREGISTER, '用户还没有注册，请先注册', '');
                      }
                  }
              else {
                      return $this->returnData(self::RET_CODE_ERR_EMSNO, '验证码不一致,或对应手机不真确', '');
              }

              }
          else {
                  return $this->returnData(self::RET_CODE_ERR_EXEPTION, '数据操作异常', '');
          }
      }


    /*
     *注册接口
     * 2017.8.31
     * */

    public function register()
    {
        $checkcodes = session('code');
        if (request()->isPost()) {
            $username=input('post.username');//用户真实姓名
            if(!empty($username)){
                session('username',$username);
            }else{
                return $this->returnData(self::RET_CODE_ERR_GETDATA,'用户名不能为空','');
            }
            $phone = input('post.phone');//用户号码
            $code = trim(input('post.code'));//验证码
            $agent_id =  session('agent_id');//代理商id
            $agent_codes = session('agent_code');;//验证信息
            $agent_type =  session('agent_type');//跳转类型
            if (!preg_match("/^1[2345789]{1}\d{9}$/", $phone)) {
                return $this->returnData(self::RET_CODE_ERR_PHONE, '请填写正确的手机号码', '');
            }
            if (empty($code)) {
                return $this->returnData(self::RET_CODE_ERR_PHONE, '验证码不能为空', '');
            }
            if ($checkcodes == $code) {
                $checklogin = db('user')->where('phone', $phone)->find();
                if (empty($checklogin)) {
                    $data['phone'] =$phone;//电话号码
                    $data['user_name'] =$username;//用户名
                    $data['create_time'] = date("Y-m-d H:i:s",time());;
                    $add = db('user')->insert($data);
                    if ($add) {
                        //清除当前验证码缓存
                        session('code', null);
                        //添加用户登录日志信息
                        $check = db('user')->field('id')->where('phone', $phone)->find();
                        $log['agent_id'] = $agent_id;//代理id
                        $log['user_name'] = $username;//用户真实姓名
                        $log['login_ip'] = $_SERVER["REMOTE_ADDR"];//用户登录ip
                        $log['user_id'] = $check['id'];//用户id
                        $log['type'] = 1;//登录状态
                        $log['remark'] = $username . ":用户通过：" . $_SERVER["REMOTE_ADDR"] . "ip地址注册登录";//登录状态
                        $log['create_time'] = date("Y-m-d H:i:s", time());
                        $res = db('user_access_log')->insert($log);
                        //添加代理关系
                        if (!empty($agent_id) && !empty($agent_codes)) {
                            $agent = db('agent')->field('id,phone,salt')->where('id', $agent_id)->find();
                            if (!empty($agent)) {
                                $checks = md5($agent['id'] . $agent['phone'] . $agent['salt']);
                                if ($checks == $agent_codes) {
                                    //根据用户id判断该关系有没有建立，有的话修改，没的话添加
                                    $agent_user = db('agent_user')
                                        ->where('user_id', $check['id'])
                                        ->where('agent_id', $agent_id)
                                        ->find();
                                    if (empty($agent_user)) {
                                        $age['agent_id'] = $agent_id;
                                        $age['user_id'] = $check['id'];
                                        $age['user_name'] = $username;
                                        $age['create_time'] = date("Y-m-d H:i:s", time());
                                        db('agent_user')->insert($age);
                                    } else {
                                        if (empty($agent_user['user_name'])) {
                                            $ages['user_name'] = $username;//用户名
                                        }
                                        $ages['update_time'] = date("Y-m-d H:i:s", time());
                                        db('agent_user')->where('user_id', $check['id'])->where('agent_id',$agent_id)->update($ages);
                                    }
                                }
                            }
                        }
                        if ($res) {
                            session('phone', $phone);
                            session('agent_id', $agent_id);
                            session('username', $username);
                            return $this->returnData(self::RET_CODE_ERR_SUSSREG, '注册成功，开始跳转', $agent_type);
                        } else {
                            return $this->returnData(self::RET_CODE_ERR_ERROREG, '注册失败，重新注册', '');
                        }
                    }else{
                        return $this->returnData(self::RET_CODE_ERR_ERROREG, '注册失败，重新注册', '');
                    }
                }else{
                    return $this->returnData(self::RET_CODE_ERR_NOREGISTER, '用户已注册，请登录', '');
                }
            }else{
                return $this->returnData(self::RET_CODE_ERR_EMSNO, '验证码不一致,或对应手机不真确', '');
            }

        }else{
            return $this->returnData(self::RET_CODE_ERR_EXEPTION, '数据操作异常', '');
        }
    }


    /*
      *头像上传
      * 2017.9.4
   * */

    public  function head_img_base(){
        // 获取表单上传文件 例如上传了001.jpg。
        //判断用户是否存在
        $phone=!empty(session('phone'))?session('phone'):cookie('login_id');
        $user_check=db('user')->where('phone',$phone)->find();
        if(!empty($user_check)){
            $file = request()->file('file');
           // var_dump($_FILES);exit;
            $info = $file->validate(['size'=>2097152,'ext'=>'jpg,png,gif'])->move($_SERVER["DOCUMENT_ROOT"]."/../../futurecredit_admin/public/uploads");
            if($info){
                $image=$info->getSaveName();
                $data['headimg']='/uploads/'.$image;
                $uesr_headimg_uplate=db('user')->where('phone',$phone)->update($data);
                $call_bank_img['images']=config('file_url').'/uploads/'.$image;
                if($uesr_headimg_uplate){
                    return $this->returnData(self::RET_CODE_OK, '头像修改成功', $call_bank_img);
                }else{
                    return $this->returnData(self::RET_CODE_ERR_OPDATA, '头像上传失败!', '');
                }

            }else{
                // 上传失败获取错误信息
                $check=$file->getError();
                return $this->returnData(self::RET_CODE_ERR_OPDATA, '头像上传失败!', $check);
            }
        }else{
            return $this->returnData(self::RET_CODE_ERR_NOTLOGIN, '请登录!', '');

        }

    }




//结束行
}

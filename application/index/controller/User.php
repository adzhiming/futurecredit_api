<?php
namespace app\index\controller;
use think\Log;
class User extends Base {
    //发送验证码方法
    public function crode(){
        vendor("auto.TopSdk");
       $mobile=input('post.phone');
        //$mobile='13560402302';
        if(empty($mobile)){
           return $this->returnData(self::RET_CODE_ERR_EMPTYPHONE,'手机号码不能为空','');
        }
        if(!preg_match("/^1[2345789]{1}\d{9}$/",$mobile)){
            return  $this->returnData(self::RET_CODE_ERR_PHONE,'手机号码错误','');
        }
        $code = rand(100000,999999);
        $purpose  = '未莱商城';//签名名称
        $params   ="{\"code\":\"$code\"}";
        $template = "SMS_76935031";//短信模板
        $c = new \TopClient;
        $c->appkey    = '24296949';//appkeu
        $c->secretKey ='1cf548355b0c936436c1d7a4ee45017a';//secreKey
        $req = new \AlibabaAliqinFcSmsNumSendRequest;
        $req->setExtend("");
        $req->setSmsType("normal");
        $req->setSmsFreeSignName($purpose);
        $req->setSmsParam($params);
        $req->setRecNum($mobile);
        $req->setSmsTemplateCode($template);
        $resp = $c->execute($req);
        if($resp->code || $resp->msg){
            $msg= "短信发送失败，code：".$resp->code.',sub_code：'.$resp->sub_code.'msg：'.$resp->msg;
            return $this->returnData(self::RET_CODE_ERR_EMS,$msg,'');
        }else{
            session('code',$code);
            return $this->returnData(self::RET_CODE_OK,'短信发送成功','');
        }

    }

    //用户注册方法
    public function register(){
        if(request()->isPost()) {
            $phone = input('post.phone');
            $phone = db('user')->where('phone', $phone)->select();
            if (empty($phone)) {
                $code  = input('post.code');
                $checkcode = session('code');
                if ($code == $checkcode) {
                    $data['phone'] =$phone;//电话号码
                    $data['create_time'] = time();
                    $log['agent_id']=input('get.agent_id');//代理id
                    $add = db('user')->insert($data);
                    if ($add) {
                        //清除当前验证码缓存
                        session('code', null);
                        session('phone',$phone);
                        $check=db('user')->field('id')->where('phone',$phone)->find();
                        $log['user_id']=$check['id'];//用户id
                        $log['type']=1;//登录状态
                        $res=db('user_access_log')->insert($log);
                        if($res){
                            return $this->returnData(self::RET_CODE_ERR_SUSSREG,'注册成功，开始跳转','');
                        }else{
                            return $this->returnData(self::RET_CODE_ERR_ERROREG,'注册失败，重新注册','');
                        }
                    } else {
                        return $this->returnData(self::RET_CODE_ERR_ERROREG,'注册失败，重新注册','');
                    }
                } else {
                    return $this->returnData(self::RET_CODE_ERR_EMSNO,'验证码不一致','');
                }

            }else{
                return $this->returnData(self::RET_CODE_ERR_ERROREG,'注册失败，用户已存在','');
            }
        }
    }

    //用户登录方法
    public function login()
    {
        if(request()->isPost()){
            $phone=input('post.phone');
            $code=input('post.code');
            $checkcode=session('code');
            if($code==$checkcode){
                $checklogin=db('user')->where('phone',$phone)->find();
                if(!empty($checkcode)){
                    //清除当前验证码缓存
                    session('code', null);
                    session('phone',$phone);
                    return $this->returnData(self::RET_CODE_ERR_SUSSREG,'登录成功，开始跳转','');
                }else{
                    return $this->returnData(self::RET_CODE_ERR_ERROREG,'登录失败，重新登录','');
                }
            }else{
                return $this->returnData(self::RET_CODE_ERR_EMSNO,'验证码不一致','');
            }
        }
    }

    //注册与登录写在同一个接口
    public function check_login(){
        if(request()->isPost()) {
            $phone = input('post.phone');
            $code  = input('post.code');
            if(!preg_match("/^1[2345789]{1}\d{9}$/",$code)){
                return  $this->returnData(self::RET_CODE_ERR_PHONE,'请填写正确的手机号码','');
            }
            $checkcode = session('code');
            if ($code == $checkcode) {
                $phone = db('user')->where('phone', $phone)->select();
                if (empty($phone)) {
                    $data['phone'] =$phone;//电话号码
                    $data['create_time'] = time();
                    $log['agent_id']=input('get.agent_id');//代理id
                    $add = db('user')->insert($data);
                    if ($add) {
                        //清除当前验证码缓存
                        session('code', null);
                        session('phone',$phone);
                        //添加用户登录信息
                        $check=db('user')->field('id')->where('phone',$phone)->find();
                        $log['user_id']=$check['id'];//用户id
                        $log['type']=1;//登录状态
                        $log['create_time']=time();
                        $res=db('user_access_log')->insert($log);
                        if($res){
                            return $this->returnData(self::RET_CODE_ERR_SUSSREG,'注册成功，开始跳转','');
                        }else{
                            return $this->returnData(self::RET_CODE_ERR_ERROREG,'注册失败，重新注册','');
                        }
                    } else {
                        return $this->returnData(self::RET_CODE_ERR_ERROREG,'注册失败，重新注册','');
                    }
                }else{
                    //如果用户存在，直接登录
                    $checklogin=db('user')->field('id')->where('phone',$phone)->select();
                    if(!empty($checklogin)){
                        //清除当前验证码缓存
                        session('code', null);
                        session('phone',$phone);
                        $log['agent_id']=input('get.agent_id');//代理id
                        $log['user_id']=$checklogin['id'];//用户id
                        $log['type']=1;//登录状态
                        $log['create_time']=time();
                        $res=db('user_access_log')->insert($log);
                        if($res){
                            return $this->returnData(self::RET_CODE_ERR_SUSSREG,'登录成功，开始跳转','');
                        }else{
                            return $this->returnData(self::RET_CODE_ERR_ERROREG,'登录失败，重新登录','');
                        }
                    }else{
                        return $this->returnData(self::RET_CODE_ERR_ERROREG,'登录失败，重新登录','');
                    }
                }
            } else {
                return $this->returnData(self::RET_CODE_ERR_EMSNO,'验证码不一致','');
            }
        }
    }

    //获取首页银行、信用卡推荐信息方法
    public function index(){
        //推荐银行
        $bank_list=db('bank')->field('id,bank_name,bank_logo')->select();
        $bank_lists=array();
        foreach ($bank_list as $k=>$v){
            $bank_lists[]=array(
                'id'=>$v['id'],
                'bank_name'=>$v['bank_name'],
                'bank_logo'=>$_SERVER['HTTP_HOST'].'/'.$v['bank_logo'],
            );
        }

        //主题精选
        $index_theme=db('theme')->field('id,name,remark')->limit(4)->order('id', 'desc')->select();

        //推荐银行卡$_SERVER['HTTP_HOST']
        $bank_card=db('bank_card')->where('card_hot',1)->field('id,card_name,card_logo,card_url,card_details')->limit(10)->order('id', 'desc')->select();
       // var_dump($bank_card);exit;
        $bank_cards=array();
        foreach ($bank_card as $key=>$val){
            $bank_cards[]=array(
                'id'=>$val['id'],
                'card_name'=>$val['card_name'],
                'card_logo'=>$_SERVER['HTTP_HOST'].'/'.$val['card_logo'],
                'card_details'=>$val['card_details'],
            );
        }
        $allList=array(
                'bank_list'=>$bank_lists, //银行信息
                'index_theme'=>$index_theme,//主题精选
                'bank_card'=>$bank_cards  //银行卡信息
        );
        return $this->returnData(self::RET_CODE_OK,'获取信息成功',$allList);
    }

    //获取对应主题对应银行卡信息
    public function theme_list()
    {
        //$id=input('post.id');
        $id=1;
        //根据精选id获取对应银行卡id，再把所有的银行卡信息查询出来
        $theme=db('theme_bank_card')->find('card_id')->where('theme_id',$id)->select();
        var_dump($theme);
    }

    //获取银行信息
    public function bank_list(){
         $bank=db('bank')->field('id,bank_name,bank_detail,bank_logo,bank_phone,bank_url')->select();
         if(!empty($bank)){
             return $this->returnData(self::RET_CODE_OK,'获取信息成功',$bank);
         }else{
             return $this->returnData(self::RET_CODE_ERR_GETDATA,'获取信息失败','');
         }
    }

    //获取银行卡信息
    public function card_list(){
        //根据提交信息获取对应的信息
        if(request()->isPost()){
            $check=input('post.check');
            //$check=1;
            if($check==1){
                $bank=db('bank_card')->alias('a')->join('bank b','a.bank_id=b.id')->field('a.id,a.card_name,a.card_logo,a.card_url,a.card_details,b.bank_name')->where('card_type_id',1)->order('a.id','desc')->select();
                if(!empty($bank)){
                    return $this->returnData(self::RET_CODE_OK,'获取信息成功',$bank);
                }else{
                    return $this->returnData(self::RET_CODE_ERR_GETDATA,'获取信息失败','');
                }
            }else if($check==2){
                $bank=db('bank_card')->alias('a')->join('bank b','a.bank_id=b.id')->field('a.id,a.card_name,a.card_logo,a.card_url,a.card_details,b.bank_name')->where('card_type_id',2)->order('a.id','desc')->select();
                if(!empty($bank)){
                    return $this->returnData(self::RET_CODE_OK,'获取信息成功',$bank);
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

    //获取信贷公司信息
    public function loan_list(){
        $loan=db('loan')->field('id,name,address,phone,logo,comment')->order('id','desc')->select();
        if(!empty($loan)){
            return $this->returnData(self::RET_CODE_OK,'获取信息成功',$loan);
        }else{
            return $this->returnData(self::RET_CODE_ERR_GETDATA,'获取信息失败','');
        }
    }

    //获取信贷产品信息
    public function loan_log(){
       if(request()->isPost()){
          $check=input('post.check');
        if($check==1){
            $business=db('loan_log')->alias('a')->join('loan b','a.loan_id=b.id')->field('a.id,a.biz_name,a.biz_url,a.biz_logo,a.biz_type,a.comment,b.name')->where("a.biz_type",1)->order('a.id','desc')->select();
            if(!empty($business)){
                return $this->returnData(self::RET_CODE_OK,'获取信息成功',$business);
            }else{
                return $this->returnData(self::RET_CODE_ERR_GETDATA,'获取信息失败','');
            }
        }else if($check==2){
            $business=db('loancompany_business')->alias('a')->join('loancompany b','a.companyid=b.id')->field('a.id,a.biz_name,a.biz_url,a.biz_logo,a.biz_type,a.comment,b.name')->where("a.type",2)->order('a.id','desc')->select();
            if(!empty($business)){
                return $this->returnData(self::RET_CODE_OK,'获取信息成功',$business);
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

    //获取用户信息接口
    public function user_list(){
       // $phone=session('phone');
       $phone='13560402302';
         if($phone){
             //还缺计算出申请中等的统计数字
            $menber=db('user')->where('phone',$phone)->select();
        //    var_dump(getLastSql($menber));exit;
            if(!empty($menber)){
                return $this->returnData(self::RET_CODE_OK,'获取信息成功',$menber);
            }else{
                return $this->returnData(self::RET_CODE_ERR_GETDATA,'获取信息失败','');
            }
         }else{
             return $this->returnData(self::RET_CODE_ERR_ERROREG,'用户未登陆','');
         }
    }

    //我的贷款接口
    public function log_type_list(){
        $check=1;
        //我的贷款里面的4种状态
        if($check==1){

        }else if($check==2){

        }else if($check==3){

        }else if($check==4){

        }else{

        }
    }

    //我的贷款详情
    public function  log_type_details(){

    }

    //进度查询
    public function check_progress(){

    }

    //我的征信
    public function credit(){

    }

    //办卡攻略
    public  function appley_strategy(){

    }

    //邀请好友
    public function share(){

    }

    //收款账号
    public function shroff_number(){

    }

    //帮助中心
    public function help_center(){

    }

//结束行
}

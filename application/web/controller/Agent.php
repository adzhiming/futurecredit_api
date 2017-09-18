<?php
namespace app\web\controller;
use think\Log;
use think\Config;
use app\common\controller\ApiWeiXin;
class Agent extends Base {

	public function __empty(){
		$this->redirect('Agent/index');
	}
	//首页
    public function index() {
    	$code_card = isset($_REQUEST['code_card'])?$_REQUEST['code_card']:"";
    	$xd_token = isset($_REQUEST['xd_token'])?$_REQUEST['xd_token']:"";
    	if(!empty($code_card)){
    		$params = array();
    		$params['code'] = $code_card;
    		$params['prj_id'] = 3;
    		$params['sign_t'] = time();
    		
    		$signData =array();
    		$signData['prj_id'] = $params['prj_id'];
    		$signData['code'] = $params['code'];
    		$signData['sign_t'] = $params['sign_t'];
    		$key = getPrjKeyValue($params['prj_id'],'key','');
    		$sign=signData($key,$signData);    		
    		$params['sign'] =$sign;
    		
    		$url ="http://eyao.beizo.cn/api.php/Withdraw/getOpenidByCode";
    		$info =  request_by_curl($url,$params);
    		if($info){
    			$info_arr = json_decode($info,true);
    			if(isset($info_arr["data"])){
    				if($info_arr["data"]['openid']){
    					  $agentinfo = session("agentinfo");
    					  $res = db("agent")->field("id,phone,salt")->where("id ='{$agentinfo["id"]}'")->find();
    					  $retoken = md5($res["id"].$res['phone'].$res['salt'].date("ymd",time()));
    					  if($xd_token ==$retoken){
    					  	     $data = array();
    					  	     $data['wx_openid'] = $info_arr["data"]['openid'];
    					  	     $data['wx_unionid'] = $info_arr["data"]['unionid'];
    					  	     $save = db("agent")->where("id ='{$res["id"]}'")->update($data);
    					  }
    					  else
    					  {
    					          echo "授权失败";	
    					  }
    				}
    			}
    		}
    	}
    	
       $rs = array_keys($_REQUEST);
       	if (in_array("/web/agent", $rs)){  //处理 /web/agent 结尾 这种情况不显示问题
           $this->redirect('Agent/index');
       }
       return $this->fetch("./agent/index");
    }
    /*public function agent(){
    	$this->view->assign('url', "<a class='op' style='display:block;' href='../agent#/agentApply'>如何申请代理商</a>");
    	return $this->fetch("./agent/login");
    }*/

    //申请代理 - 普通申请 - 需要验证码的
    public function applyfrwatch(){
        $mid = input('mid');
        $aid = input('aid');
        $this->assign("aid",$aid);
        $this->assign("mid",$mid);
    	return $this->fetch("./agent/applyfrwatch");
    }
    //申请代理 - 手表扫码申请 - 不需要验证码
    public function apply(){
        return $this->fetch("./agent/apply");
    }
    // 忘记密码
    public function misspass(){
        return $this->fetch("./agent/misspass");
    }
    //我的
    public function personal() {
    	return $this->fetch("./agent/tel/personal");
    }
    //我的用户
    public function myuser() {
    	return $this->fetch("./agent/tel/myuser");
    }
    public function user() {
    	return $this->fetch("./agent/tel/user");
    }
    
    //添加特定用户报价
    public function  teding_add(){
    	return $this->fetch("./agent/tel/teding_add");
    }
    
    //编辑特定用户报价
    public function  teding_edit(){
    	return $this->fetch("./agent/tel/teding_edit");
    }
    
    //我的代理入口
    public function daili() {
    	return $this->fetch("./agent/tel/daili");
    }
    
    //我的代理
    public function myAgent() {
    	return $this->fetch("./agent/tel/myAgent");
    }
    
    //我的代理 - 下一级
    public function myAgentThree() {
        return $this->fetch("./agent/tel/myAgentThree");
    }
    //我的代理 - 下一级 - 第三级
    public function myAgentThreeTwo() {
        return $this->fetch("./agent/tel/myAgentThreeTwo");
    }
    //我的代理编辑
    public function myAgent_edit() {
    	return $this->fetch("./agent/tel/myAgent_edit");
    }
    
    //新增我的代理
    public function agentAdd() {
    	return $this->fetch("./agent/tel/agentAdd");
    }
    
    //报价单
    public function quotedPrice(){
    	return $this->fetch("./agent/tel/quotedPrice");
    }
    
    //提现
    public function withdraw(){
    	return $this->fetch("./agent/tel/withdraw");
    }
    
    //报表
    public function report(){
    	return $this->fetch("./agent/tel/report");
    }
    
    //收款账户
    public function collectionAccount(){
    	return $this->fetch("./agent/tel/collectionAccount");
    }
    
    //申请信用卡记录
    public function crdApplyRecord(){
    	return $this->fetch("./agent/tel/crdApplyRecord");
    }
    
    //申请贷款记录
    public function loanApplyRecord(){
    	return $this->fetch("./agent/tel/loanApplyRecord");
    }
    
    //访问
    public function access() {
    	return $this->fetch("./agent/tel/access");
    }
    //底部
    public function footer() {
    	return $this->fetch("./agent/tel/footer");
    }
    //登录
    public function login() {
    	$this->view->assign('url', "<a class='op' style='display:block;' href='../agent#/agentApply'>如何申请代理商</a>");
    	return $this->fetch("./agent/login");
    }
    
    public function subIndex() {
    	$agentinfo = session("agentinfo");
    	$has_openid = 0;
    	if($agentinfo){
    		$xd_token = $agentinfo["token"];
    		$rs = db("agent")->field("wx_openid")->where("phone ='{$agentinfo['phone']}'")->find();
    		if($rs['wx_openid'] !='' && !empty($rs['wx_openid'])){
    			$has_openid = 1;
    		}
    	}
    	else 
    	{
    		$xd_token = 1;
    	}
    	
    	$ret_url = config("web_url")."agent/index?xd_token=".$xd_token;
    	$ret_url = urlencode($ret_url);
    	$this->assign("has_openid",$has_openid);
    	//$this->assign("get_openid_url",config('bkl_url_base')."/api.php/Withdraw/oauth/prj_id/3?ret_url={$ret_url}");
    	$this->assign("get_openid_url","http://eyao.beizo.cn/api.php/Withdraw/oauth/prj_id/3?ret_url={$ret_url}");
    	return $this->fetch("./agent/tel/subIndex");
    }
    
    //分享页面
    public function sharePage(){
    	$card_url =config("web_url")."agent/xyshare";
    	$loan_url =config("web_url")."agent/xdshare";
    	$xykhtml = '<div id="xiyongka" class="a1" name="xiyongka" style="background:url(\'../../assets/agent/img/erbox.png\')no-repeat;background-size:100% 6.8rem;"><div id="cardurl"></div></div>';
    	$xdhtml = '<div id="xindai" class="a1" name="xiyongka" style="background:url(\'../../assets/agent/img/erbox.png\')no-repeat;background-size:100% 6.8rem;"><div id="loanurl"></div></div>';
    	$juhehtml = '<div id="zhgx" class="a1" name="xiyongka" style="background:url(\'../../assets/agent/img/erbox.png\')no-repeat;background-size:100% 6.8rem;"><div id="zhgxurl"></div></div>';
    	$this->assign("xykhtml",$xykhtml);
    	$this->assign("xdhtml",$xdhtml);
    	$this->assign("juhehtml",$juhehtml);
    	$this->assign("card_url",$card_url);
    	$this->assign("loan_url",$loan_url);
    	return $this->fetch("./agent/tel/sharePage");
    }

    //分享页面
    public function share(){
     	$appId =config("wx_AppID");
    	$appSecret = config("wx_AppSecret");
    	$wx = new ApiWeiXin($appId,$appSecret);
    	$scope ="snsapi_base";
    	//注意：需要以 api.futurecredit.net/web/agent/share/id/1 这种url形式
    	$redirectUrl ='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].$_SERVER['QUERY_STRING'];
        $wx->getAuthCode(urlencode($redirectUrl),$scope);
        $code = input('code');
    	$signPackage = $wx->getSignPackage();
    	
    	$openid = $wx->getOpenid($code);
    	
    	$this->assign("url",$redirectUrl);
    	$this->assign("signPackage",$signPackage); 
    	$id = input('id');
    	$this->assign("id",$id);
    	return $this->fetch("./agent/tel/share");
    }
    //我的分类
    public function personalthree(){
        return $this->fetch("./agent/tel/personalthree");
    }
    //贷款用户编辑
    public function teding_edit_dk(){
        return $this->fetch("./agent/tel/teding_edit_dk");
    }
    //贷款用户增加
    public function teding_add_dk(){
        return $this->fetch("./agent/tel/teding_add_dk");
    }
    //我的分页
    public function pagination(){
        return $this->fetch("./agent/tel/pagination");
    }
    //重置密码
    public function resetPass(){
        return $this->fetch("./agent/tel/resetPass");
    }
    //推广页
    public function spread(){
        return $this->fetch("./agent/tel/spread");
    }
    //推广页 - 详情
    public function spreadDetails(){
        return $this->fetch("./agent/tel/spreadDetails");
    }
    //信用卡分享
    public function xyshare(){
    	$appId =config("wx_AppID");
    	$appSecret = config("wx_AppSecret");
    	$wx = new ApiWeiXin($appId,$appSecret);
    	$scope ="snsapi_base";
    	//注意：需要以 api.futurecredit.net/web/agent/share/id/1 这种url形式
    	$redirectUrl ='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].$_SERVER['QUERY_STRING'];
    	$wx->getAuthCode(urlencode($redirectUrl),$scope);
    	$code = input('code');
    	$signPackage = $wx->getSignPackage();
    	 
    	$openid = $wx->getOpenid($code);
    	$this->assign("url",$redirectUrl);
    	$this->assign("signPackage",$signPackage);
    	$id = input('id');
    	$this->assign("id",$id);
        return $this->fetch("./agent/tel/xyshare");
    }
    //信贷分享
    public function xdshare(){
    	$appId =config("wx_AppID");
    	$appSecret = config("wx_AppSecret");
    	$wx = new ApiWeiXin($appId,$appSecret);
    	$scope ="snsapi_base";
    	//注意：需要以 api.futurecredit.net/web/agent/share/id/1 这种url形式
    	$redirectUrl ='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].$_SERVER['QUERY_STRING'];
    	$wx->getAuthCode(urlencode($redirectUrl),$scope);
    	$code = input('code');
    	$signPackage = $wx->getSignPackage();
    	$openid = $wx->getOpenid($code);
    	$this->assign("url",$redirectUrl);
    	$this->assign("signPackage",$signPackage);
    	$id = input('id');
    	$this->assign("id",$id);
        return $this->fetch("./agent/tel/xdshare");
    }
}
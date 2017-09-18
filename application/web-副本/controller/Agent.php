<?php
namespace app\web\controller;
use think\Log;
use think\Config;
class Agent extends Base {

	public function __empty(){
		$this->redirect('Agent/index');
	}
	//首页
    public function index() {
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
    	return $this->fetch("./agent/tel/subIndex");
    }
    
    //分享页面
    public function sharePage(){
    	return $this->fetch("./agent/tel/sharePage");
    }

    //分享页面
    public function share(){
    	return $this->fetch("./agent/tel/share");
    }
    
    //贷款用户编辑
    public function teding_edit_dk(){
        return $this->fetch("./agent/tel/teding_edit_dk");
    }
    //贷款用户增加
    public function teding_add_dk(){
        return $this->fetch("./agent/tel/teding_add_dk");
    }
    //分页
    public function pagination(){
        return $this->fetch("./agent/tel/pagination");
    }
}
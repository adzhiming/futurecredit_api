<?php

namespace app\api\model;

use think\Model;
use think\Db;
use app\api\model\Agent_user;
use app\api\model\Bank_card_log;
use app\api\model\Agent as AgentModel;
use app\api\model\Loan_log;

class User extends Model {
	function __construct() {
		
	}
	// 根据代理商id或者用户列表
	//author lzm
	public static function getUserListByAgentID($aid,$keyword, $page, $pagesize) {
		if (is_null ( $aid )) {
			return;
		}
		$agentinfo = AgentModel::get ( [
				'id' => $aid,
				'is_deleted' => 0
				] );
		
		if ($page <= 1 || empty ( $page )) {
			$page = 1;
		}
	    $where = "";
	    $where ="a.agent_id ='{$aid}' and b.is_deleted =0 ";
		if(!empty($keyword)){
			$where=$where. " and (a.user_name like '%{$keyword}%' or b.phone like '%{$keyword}%')";
		}
		$pagesize = empty ( $pagesize ) ? 8 : $pagesize;

		
		$total = db('agent_user')->alias('a')->join('user b','a.user_id=b.id')->field("a.*")->where($where)->count();
		$page_count = ceil ( $total / $pagesize );
	
		if ($page > $page_count) {
			$page = empty($page_count)?1:$page_count;
		}
		$offset = $pagesize * ($page - 1);
		
		$list = db('agent_user')->alias('a')->join('user b','a.user_id=b.id')->field("b.id,a.user_name,b.id,b.phone,b.headimg,round(b.total_loan) total_loan,b.create_time,b.status")->where($where)->order("a.create_time desc")->limit($offset,$pagesize)->select();
      //echo db()->getLastSql();
		if($list){
			$where = array();
			foreach ($list as $k=>$v){
				$where['user_id'] = $v['id'];
				$where['status'] = 2;
				$rs =Bank_card_log::where($where)->count();
				$list[$k]['card_num'] = $rs;
				$list[$k]['agenter'] = $agentinfo ['name'];
				/*  $price =Loan_log::where($where)->field("sum(IFNULL(loan_price,0)) total")->find();
				 $list[$k]['loan_price'] = empty($price->total)?0:$price->total;  *///user 表有记录
			}
		}

		// echo db::getLastSql();die;
		$data = array();
		$data ['data'] = $list;
		$data ['total'] = $total;
		$data ['page'] = $page;
		$data ['page_count'] = $page_count;
		return $data;
	}
	
	// 根据代理商id获取用户访问记录列表
	//author lzm
	public static function getUserAccessLogByAgentID($aid, $page, $pagesize,$where) {
		if (is_null ( $aid )) {
			return;
		}
		$agentinfo = AgentModel::get ( [
				'id' => $aid,
				'is_deleted' => 0
				] );
		if ($page <= 1 || empty ( $page )) {
			$page = 1;
		}
		$pagesize = empty ( $pagesize ) ? 10 : $pagesize;
	
		$where['a.agent_id'] = $aid;
		$total = db('user_access_log')->alias('a')->where($where)->count();
		$page_count = ceil ( $total / $pagesize );
	
		if ($page > $page_count) {
			$page = empty($page_count)?1:$page_count;
		}
		$offset = $pagesize * ($page - 1);
	
	
		$field="a.create_time,c.user_name,b.headimg,d.card_name,e.name loan_name,a.remark";
		$list = db('user_access_log')->alias('a')
		->field($field)
		->join("user b","a.user_id = b.id",'left')
		->join("agent_user c","a.user_id = c.user_id",'left')
		->join("bank_card d","a.bank_id = d.id",'left')
		->join("loan e","a.loan_id = e.id",'left')
		->where($where)->group("a.id")->order("a.create_time desc")->limit($offset,$pagesize)->select();
	
		if($list){
			foreach ($list as $k=>$v){
				$list[$k]['agenter'] = $agentinfo['name'];
			}
		}
		$data = array();
		$data ['data'] = $list;
		$data ['total'] = $total;
		$data ['page'] = $page;
		$data ['page_count'] = $page_count;
		return $data;
	}
	
	
	// 根据代理商id获取用户申请信用卡记录列表
	//author lzm
	public static function getUserApplyCardListByAgentID($aid, $page, $pagesize,$where) {
		if (is_null ( $aid )) {
			return;
		}
		$agentinfo = AgentModel::get ( [
				'id' => $aid,
				'is_deleted' => 0
				] );
		if ($page <= 1 || empty ( $page )) {
			$page = 1;
		}
		$pagesize = empty ( $pagesize ) ? 8 : $pagesize;
	
		$where['a.agent_id'] = $aid;
		$where['a.is_deleted'] = 0;
		$total = db('bank_card_log')->alias('a')->where($where)->count();
		$page_count = ceil ( $total / $pagesize );
	
		if ($page > $page_count) {
			$page = empty($page_count)?1:$page_count;
		}
		$offset = $pagesize * ($page - 1);

		
		$field="a.id,date_format(a.apply_time,'%Y-%m-%d') apply_time,date_format(a.confirm_time,'%Y-%m-%d') confirm_time,case a.status when 1 then '申请中' when 2 then '通过' when 3 then '拒绝' end zt,b.user_name,b.phone,b.headimg,c.card_name,d.bank_name";
		$list = db('bank_card_log')->alias('a')
		->field($field)
		->join("user b","a.user_id = b.id",'left')
		->join("bank_card c","a.card_id = c.id",'left')
		->join("bank d","c.bank_id = d.id",'left')
		->where($where)
        ->group("a.id")
        ->order("a.apply_time desc")
		->limit($offset,$pagesize)->select();
		if($list){
			foreach ($list as $k=>$v){
				$list[$k]['agenter'] = $agentinfo['name'];
			}
		}
		
		$data = array();
		$data ['data'] = $list;
		$data ['total'] = $total;
		$data ['page'] = $page;
		$data ['page_count'] = $page_count;
		return $data;
	}
	
	// 根据代理商id获取用户申请贷款记录列表
	//author lzm
	public static function getUserApplyLoanListByAgentID($aid, $page, $pagesize,$where) {
		if (is_null ( $aid )) {
			return;
		}
		$agentinfo = AgentModel::get ( [
				'id' => $aid,
				'is_deleted' => 0
				] );
		if ($page <= 1 || empty ( $page )) {
			$page = 1;
		}
		$pagesize = empty ( $pagesize ) ? 8 : $pagesize;
	
		$where['a.agent_id'] = $aid;
		$where['a.is_deleted'] =0;
		$total = db('loan_log')->alias('a')->where($where)->count();
		$page_count = ceil ( $total / $pagesize );
	
		if ($page > $page_count) {
			$page = empty($page_count)?1:$page_count;
		}
		$offset = $pagesize * ($page - 1);
	
	
		$field="a.id,date_format(a.apply_time,'%Y-%m-%d') apply_time,date_format(a.confirm_time,'%Y-%m-%d') confirm_time,round(a.loan_price) loan_price,b.user_name,b.phone,b.headimg,c.name product_name,
				    case a.status when 1 then '审核中' when 2 then '通过' when 3 then '不通过' end zt,d.name loan_name";
		$list = db('loan_log')->alias('a')
		->field($field)
		->join("user b","a.user_id = b.id",'left')
		->join("loan_product c","a.loan_product_id = c.id",'left')
		->join("loan d","c.loan_id = d.id",'left')
		->where($where)
		->group("a.id")
		->order("a.apply_time desc")
		->limit($offset,$pagesize)->select();
        //echo db()->getLastSql();
		if($list){
			foreach ($list as $k=>$v){
				$list[$k]['agenter'] = $agentinfo['name'];
			}
		}
		$data = array();
		$data ['data'] = $list;
		$data ['total'] = $total;
		$data ['page'] = $page;
		$data ['page_count'] = $page_count;
		return $data;
	}
	
}
<?php

namespace app\api\model;

use think\controller;
use think\Model;
use app\api\model\Agent_price;

class Agent extends Model {
	/*
	 * 获取代理商信息 未审计金额：pending_money 可提现金额：left_money 冻结金额：freeze_money 已提现金额：withdraw author lzm
	 */
	public function getAgentInfoById($aid) {
		if (is_null ( $aid )) {
			return;
		}
		$prefix = config('database.prefix');
		$field = "a.id,a.`level`,case a.`level` when 0 then '特级' when 1 then '一级' when 2 then '二级'  when 3 then '三级' else '未知' end as lev,a.name ,a.id_card,a.card_no,a.card_name,a.card_sub_name,a.phone,
				IFNULL((a.income_card +a.income_loan - a.withdraw -b.freeze_money),0.00) left_money,a.withdraw,a.email,a.last_login_ip,IFNULL(a.last_login_time,CURRENT_TIMESTAMP) last_login_time,a.remark,IFNULL(b.freeze_money,0.00)freeze_money,IFNULL(c.pending_money,0.00) pending_money";
		$join = "(select agent_id,sum(if((`status` = 1 and pay_status = 1),withdraw,0)) freeze_money  from ".$prefix."agent_withdraw_log group by agent_id) b";
		$condition = "b.agent_id = a.id";
		$join2 = "(select agent_id,sum(if(`status` = 1,commission,0)) pending_money   from ".$prefix."loan_log group by agent_id) c";
		$condition2 = "c.agent_id = a.id";
		$res = db ( 'agent' )->alias ( 'a' )->field ( $field )->join ( $join, $condition, 'left' )->join ( $join2, $condition2, 'left' )->where ( "a.id", $aid )->find ();
		if ($res) {
			return $res;
		}
		return false;
	}
	
	// 编辑用户
	// author lzm
	public function editUser($updata, $id) {
		$data = $updata;
		$where ['id'] = $id;
		$res = Agent::update ( $data, $where );
		return $res; // return Agent::getLastSql();
	}
	
	// 新增代理商
	// author lzm
	public function addAgent($addData) {
		if (empty ( $addData )) {
			return false;
		}
		$res = Agent::create ( $addData, true ); // 加true可以排除字段不存在的情况下报错的问题
		return $res;
	}
	
	// 代理商报价单
	// author lzm
	public function getQuotationListByAgentId($type, $aid, $page, $pagesize) {
		$type = empty ( $type ) ? 1 : $type;
		if (is_null ( $aid )) {
			return;
		}
		if ($page <= 1 || empty ( $page )) {
			$page = 1;
		}
		$field ="";
		$agentinfo = db("agent")->field("level")->where("id ='{$aid}'")->find();
		for($i = $agentinfo['level']; $i <= 3; $i ++) {
			if($field ==""){
				$field ="a.level{$i}_price";
			}
			else
			{
				$field =$field.",a.level{$i}_price";
			}
		}
		$field = str_replace("0", "", $field);
		
		$data = array ();
		

		
		//自己的报价单
		$find = db ( 'agent_sys_price' )->field("agent_id")->where("agent_id ='{$aid}' and type ='{$type}'")->find();
		$where = array ();
		$where ['b.is_deleted'] = 0;
		$where ['a.type'] = $type;
		$default = array();
		$default1 = false;
		$default2 = array();
		//上级的报价单
		$parenAgentId = getAgentMyDefaultPriceAgentId($aid);
		$general_agent_id = getParentAgentID($aid);//找出总代id
		if($parenAgentId){
			$where ['a.agent_id'] = empty($parenAgentId)?0:$parenAgentId;
			if ($type == 1) { // 信用卡
				$field = $field.",a.id,a.bank_id,a.card_id,b.bank_name card_name,case b.price_type when 1 then 'CPA' when 2 then 'CPA' when 3 then 'CPA' else 'CPA' end price_type,
					     case b.price_type when 1 then '元' when 2 then '%' when 3 then '元' else '' end price_unit,b.rule_description";
				$default2 = db ( 'agent_sys_price')->alias ( 'a' )->field ($field )->join ( "bank b", "a.bank_id =b.id", 'left' )->where ( $where )->group("a.bank_id")->order("a.bank_id")->select ();
			 // echo db()->getLastSql();
			} else { // 贷款
				//找出该代理商的总代所有贷款产品
				$card  = db("agent_bank_loan")->alias ( 'a' )
									->join ( "loan_product b", "a.bank_loan_id = b.id", 'left' )
									->field("b.loan_id bank_id,a.bank_loan_id card_id")->where("a.agent_id ='{$general_agent_id}' and a.type = 2")->select();				
				//$card = db ( 'agent_sys_price')->alias ( 'a' )->field ( "a.bank_id,a.card_id" )->join ( "loan_product b", "a.card_id = b.id", 'left' )->where ( $where )->group("a.card_id")->order("a.bank_id,a.card_id")->select ();
				//echo db()->getLastSql();
				$cdata = array();
			    if($card){
			    	foreach ($card as $k=>$v){
			    		$where ['a.bank_id'] = $v['bank_id'];
			    		$where ['a.card_id'] = $v['card_id'];
			    		$where ['c.is_deleted'] = 0;
			    		$field = $field.",a.id,a.agent_id,a.fy_type,a.bank_id,a.card_id,b.name card_name,case b.price_type when 1 then '金额' when 2 then '比例' when 3 then '注册数' else '未知' end price_type,
					     case c.rule_type when 1 then '元' when 2 then '%'  else '' end price_unit";
			    		$default = db ( 'agent_sys_price')->alias ( 'a' )->field ( $field )
			    		->join ( "loan_product_price c", "a.card_id = c.loan_product_id and a.fy_type = c.rule_type", 'INNER' )
			    		->join ( "loan_product b", "a.card_id = b.id", 'left' )
			    		->where ( $where )->group("a.fy_type")->order("a.bank_id,a.card_id")->select ();
			    		//echo db()->getLastSql().";<br>";
			    		if($default){
			    			foreach ($default as $kl =>$val){
			    				$rs = db("loan_product_price")->field("remark")->where("loan_product_id ='{$val['card_id']}' and rule_type ='{$val['fy_type']}' and is_deleted =0")->find();
			    				$cdata[$k][$kl]['rule_description'] = $rs['remark'];
			    				$cdata[$k][$kl] = $val;
			    			}
			    		}
			    	}
			    	$default2 = $cdata;
			    }
				/* $field = $field.",a.id,a.bank_id,a.card_id,b.name card_name,case b.price_type when 1 then '金额' when 2 then '比例' when 3 then '注册数' else '未知' end price_type,
			     case b.price_type when 1 then '元' when 2 then '%' when 3 then '元' else '' end price_unit";
				$default2 = db ( 'agent_sys_price')->alias ( 'a' )->field ( $field )->join ( "loan_product b", "a.card_id = b.id", 'left' )->where ( $where )->order("a.bank_id")->select (); */
			}
		}
		if($find){
			$default1 = getMyselfPice($aid,$field,$type);
			
			//自己的报价单与上级报价单对比差异，发现差异就补全
			/* echo "<pre>";
			print_r($default1);
			echo "</pre>";
			echo "<pre>";
			print_r($default2);
			echo "</pre>";
			die; */
			if($default2){
				if($type == 1){
					foreach ($default1 as $v){
						foreach ($default2 as $k=>$val){
							if($v["bank_id"] == $val['bank_id'] && $v["card_id"] == $val["card_id"]){
								unset($default2[$k]);
							}
						}
					}
				}
				else{
					foreach ($default1 as $k=>$arr){
						  foreach ($arr as $arrk =>$arrv){
							  	foreach ($default2 as $kl=>$arr2v){
							  		foreach ($arr2v as $arr2vk =>$arr2vv){
							  			if($default1[$k][$arrk]['bank_id'] ==$default2[$kl][$arr2vk]['bank_id'] && $default1[$k][$arrk]['card_id'] ==$default2[$kl][$arr2vk]['card_id'] && $default1[$k][$arrk]['fy_type'] ==$default2[$kl][$arr2vk]['fy_type'] ){
							  				  unset($default2[$kl][$arr2vk]);
							  			}
							  		}
							  	}
						  }  		
					}
				}
			}
           
			if($default2){
				//补充新的报价单
				if($type ==1){
					foreach ($default2 as $val){
						$addData = array();
						$addData['type'] =$type;
						$addData['bank_id'] =$val['bank_id'];
						$addData['card_id'] =$val["card_id"];
						$addData['agent_id'] =$aid;
						$addData['level'] =$agentinfo['level'];
						$start = $agentinfo['level'];
						for($i = $start; $i <= 3; $i ++) {
							$insertfield ="level{$i}_price";
							$insertfield = str_replace("0", "", $insertfield);
							$addData[$insertfield] = $val[$insertfield];
						}
						$res = Agent_sys_price::create ( $addData, true ); // 加true可以排除字段不存在的情况下报错的问题
					}
				}
				else 
				{
					foreach ($default2 as $valarr){
						if($valarr){
							foreach ($valarr as $val){
								$addData = array();
								$addData['type'] =$type;
								$addData['fy_type'] =$val['fy_type'];
								$addData['bank_id'] =$val['bank_id'];
								$addData['card_id'] =$val["card_id"];
								$addData['agent_id'] =$aid;
								$addData['level'] =$agentinfo['level'];
								$start = $agentinfo['level'];
								for($i = $start; $i <= 3; $i ++) {
									$insertfield ="level{$i}_price";
									$insertfield = str_replace("0", "", $insertfield);
									$addData[$insertfield] = $val[$insertfield];
								}
								$res = Agent_sys_price::create ( $addData, true ); // 加true可以排除字段不存在的情况下报错的问题
							}
						}
					}
				}	
			}
		}
		else{
			//自己的报价单与上级报价单对比差异，发现差异就补全
			if($default2){
				    if($type == 1){
				    	foreach ($default2 as $k=>$val){
				    		//补充新的报价单
				    		$addData = array();
				    		$addData['type'] =$type;
				    		$addData['bank_id'] =$val['bank_id'];
				    		$addData['card_id'] =$val["card_id"];
				    		$addData['agent_id'] =$aid;
				    		$addData['level'] =$agentinfo['level'];
				    		$start = $agentinfo['level'];
				    		for($i = $start; $i <= 3; $i ++) {
				    			$insertfield ="level{$i}_price";
				    			$insertfield = str_replace("0", "", $insertfield);
				    			$addData[$insertfield] = $val[$insertfield];
				    		}
				    		$res = Agent_sys_price::create ( $addData, true ); // 加true可以排除字段不存在的情况下报错的问题
				    		//echo db()->getLastSql()."<br>";
				    	}
				    }
				    else
				    {
				    	foreach ($default2 as $k=>$valarr){
				    		//补充新的报价单
				    		if($valarr){
				    			foreach ($valarr as $val){
				    				$addData = array();
				    				$addData['type'] =$type;
				    				$addData['fy_type'] =$val['fy_type'];
				    				$addData['bank_id'] =$val['bank_id'];
				    				$addData['card_id'] =$val["card_id"];
				    				$addData['agent_id'] =$aid;
				    				$addData['level'] =$agentinfo['level'];
				    				$start = $agentinfo['level'];
				    				for($i = $start; $i <= 3; $i ++) {
				    					$insertfield ="level{$i}_price";
				    					$insertfield = str_replace("0", "", $insertfield);
				    					$addData[$insertfield] = $val[$insertfield];
				    				}
				    				$res = Agent_sys_price::create ( $addData, true ); // 加true可以排除字段不存在的情况下报错的问题
				    				//echo db()->getLastSql()."<br>";
				    			}
				    		}
				    	}
				    }
			   }
		}
	
		//重新获取一下自己补全后的报价单
		$default = getMyselfPice($aid,$field,$type);
		if($default){
			sort($default);
		}
		
		/* echo "<pre>";
		print_r($default);
		echo "</pre>"; */
		
		$data ['default_pricelist'] = $default;
		
		
		//以下是拿特定用户报价单
		// 特定用户报价列表 找出该代理商下的代理商报价单
		$subAgentIdstr = getSubAgentIds ( $aid );
		$where = array ();
		if (empty ( $subAgentIdstr )) {
			$subAgentIdstr = -1;
		} else {
			$subAgentIdstr =  $subAgentIdstr;
		}
		$where ['a.agent_id'] = array (
				'in',
				$subAgentIdstr 
		);
		
		$where ['a.type'] = $type;
	   $total =0;
	   if ($type == 1) { // 信用卡
	     	$field = "a.id";
	     	$cardwhere = $where;
	     	$cardwhere["c.is_deleted"] = 0;
			$total = db ( 'agent_price' )->alias ( 'a' )->field ( $field )->join ( "agent b", "a.agent_id = b.id", 'left' )->join ( "bank c", "a.bank_id = c.id", 'left' )->where ( $cardwhere )->order("a.create_time desc")->count ();
		   // echo db()->getLastSql();
	   } else {
			//贷款
	   	    $where ['d.is_deleted'] = 0;
			$field = "a.id";
			$total = db ( 'agent_price' )->alias ( 'a' )->field ($field)->join ( "agent b", "a.agent_id = b.id", 'left' )->join ( "loan c", "a.bank_id = c.id", 'left' )->join ( "loan_product d", "a.card_id = d.id", 'left' )->where ( $where )->group("a.agent_id")->order("a.create_time desc")->count ();
		}	
		
		$page_count = ceil ( $total / $pagesize );
		if ($page > $page_count) {
			$page = empty ( $page_count ) ? 1 : $page_count;
		}
		$offset = $pagesize * ($page - 1);
		
		if ($type == 1) { // 信用卡
			$field = "a.id,a.bank_id,a.card_id,a.agent_id,a.level_price,a.level1_price,a.update_time,a.level2_price,a.level3_price,a.remark,a.creater,b.level,b.name user_name,c.bank_name,d.card_name,case d.price_type when 1 then '金额' when 2 then '比例' when 3 then '注册数' else '未知' end price_type,
			case d.price_type when 1 then '元' when 2 then '%' when 3 then '元' else '' end price_unit";
			$list = db ( 'agent_price' )->alias ( 'a' )->field ( $field )->join ( "agent b", "a.agent_id = b.id", 'left' )->join ( "bank c", "a.bank_id = c.id", 'left' )->join ( "bank_card d", "a.card_id = d.id", 'left' )->where ( $where )->order("a.create_time desc")->limit ( $offset, $pagesize )->select ();
		} else {
			unset($where ['d.is_deleted']);
			$pricelistarr = array();
			$agent_id_rs = db ( 'agent_price a' )->field("agent_id,card_id")->where($where)->group("agent_id,card_id")->limit ( $offset, $pagesize )->select ();
			$where ['d.is_deleted'] = 0;
			if($agent_id_rs){
				foreach ($agent_id_rs as $k=>$v){
					$where ['a.agent_id'] = $v["agent_id"];
					$where ['a.card_id'] = $v["card_id"];
					$field = "a.id,a.fy_type,a.bank_id,a.card_id,a.agent_id,a.level_price,a.level1_price,a.update_time,a.level2_price,a.level3_price,a.remark,a.creater,b.level,b.name user_name,c.name bank_name,d.name card_name,case d.price_type when 1 then '金额' when 2 then '比例' when 3 then '注册数' else '未知' end price_type,
			                      case d.price_type when 1 then '元' when 2 then '%' when 3 then '元' else '' end price_unit";
					$pricelist = db ( 'agent_price' )->alias ( 'a' )->field ( $field )->join ( "agent b", "a.agent_id = b.id", 'left' )->join ( "loan c", "a.bank_id = c.id", 'left' )->join ( "loan_product d", "a.card_id = d.id", 'left' )->where ( $where )->order("a.create_time desc")->select ();
                    if($pricelist){
                    	foreach ($pricelist as $kl=>$vl){
                    		$pricelistarr[$k][$kl] = $vl;
                    	}
                    }				
				}
			}
			$list = $pricelistarr;
			/* $field = "a.id,a.bank_id,a.card_id,a.agent_id,a.level_price,a.level1_price,a.update_time,a.level2_price,a.level3_price,a.remark,a.creater,b.level,b.name user_name,c.name bank_name,d.name card_name,case d.price_type when 1 then '金额' when 2 then '比例' when 3 then '注册数' else '未知' end price_type,
			case d.price_type when 1 then '元' when 2 then '%' when 3 then '元' else '' end price_unit";
			$list = db ( 'agent_price' )->alias ( 'a' )->field ( $field )->join ( "agent b", "a.agent_id = b.id", 'left' )->join ( "loan c", "a.bank_id = c.id", 'left' )->join ( "loan_product d", "a.card_id = d.id", 'left' )->where ( $where )->order("a.create_time desc")->limit ( $offset, $pagesize )->select (); */
		}
       
		$parentAgent = getAgentInfoById ( $aid );
		if($type == 1){
			foreach ( $list as $k => $v ) {
				$res = getAgentInfoById ( $v ['creater'] );
				$list [$k] ['creater_name'] = $res ['name'];
				$list [$k] ['parent_agenter'] = $parentAgent ['name'];
			}
		}
		else{
			foreach ( $list as $k => $varr ) {
				foreach ($varr as $kl =>$vl){
					$res = getAgentInfoById ( $vl ['creater'] );
					$list [$k][$kl] ['creater_name'] = $res ['name'];
					$list [$k][$kl] ['parent_agenter'] = $parentAgent ['name'];
				}
			}
		}
		// $sql = Agent::getLastSql();
		$data ["given_user_pricelist"] = $list;
		
		$data ['total'] = $total;
		$data ['page'] = $page;
		$data ['page_count'] = $page_count;
		return $data;
	}
	// 获取代理商的银行、贷款公司列表
	// author lzm
	public static function getBankLoanList($aid,$type,$keyword) {
		if (is_null ( $aid )) {
			return;
		}
		$type = empty($type)?1:$type;
		$where = array();
		if(!empty(trim($keyword))){
			if($type == 1){
				$where['b.bank_name'] = array("like","%{$keyword}%");
			}
			if($type == 2){
				$where['b.name'] = array("like","%{$keyword}%");
			}
		}
		/* $find = db ( 'agent_sys_price' )->field("agent_id")->where("agent_id ='{$aid}'")->find();
		if($find){
			$parenAgentId = $aid;
		}
		else{
			$parenAgentId = getAgentMyDefaultPriceAgentId($aid);
		} */
		$parenAgentId = getParentAgentID($aid);
		$where["a.type"] = $type;
		$where["a.agent_id"] = empty($parenAgentId)?0:$parenAgentId;
		$where["b.is_deleted"] = 0;
		$general_agent_id = getParentAgentID($aid);//找出总代id
		$list = array();
		if($type == 1){
			//找出该代理商的总代所有银行产品 
			$list  = db("agent_bank_loan")->alias ( 'a' )
			->join ( "bank b", "a.bank_loan_id = b.id", 'left' )
			->field("b.id bank_id,a.type,b.bank_name name,b.bank_logo logo")->where("a.agent_id ='{$general_agent_id}' and a.type = 1")->select();
			if($list){
				foreach ($list as $k =>$v){
					$list[$k]["logo"] = config('file_url').$v['logo'];
				}
			}
		}
		elseif($type == 2){
			//找出该代理商的总代所有贷款公司
			$loan_product  = db("agent_bank_loan")->alias ( 'a' )
									->join ( "loan_product b", "a.bank_loan_id = b.id", 'left' )
									->join ( "loan c", "b.loan_id = c.id", 'left' )
									->field("a.id,c.id loan_id")->where("a.agent_id ='{$general_agent_id}' and a.type = 2")
			                        ->group('a.id')->select();
			if($loan_product){
				$loan_id = "";
				 foreach ($loan_product as $v){
				 	   if($loan_id ==""){
				 	   	      $loan_id = $v['loan_id'];
				 	   }
				 	   else
				 	   {
				 	          $loan_id = $loan_id.",".$v['loan_id'];
				 	   }
				 }
				 if($loan_id !=""){
				 	  $list = db("loan")->field("id bank_id,name")->where("id in ($loan_id)")->select();
				 }
			}
		}
		elseif($type ==3){
			//找出该代理商的总代所有贷款产品
			$list  = db("agent_bank_loan")->alias ( 'a' )
			->join ( "loan_product b", "a.bank_loan_id = b.id", 'left' )
			->field("a.id,a.type,b.id bank_id, b.name,product_logo logo")->where("a.agent_id ='{$general_agent_id}' and a.type = 2")
			->group('a.id')->select();
			if($list){
				foreach ($list as $k =>$v){
					$list[$k]["logo"] = config('file_url').$v['logo'];
				}
			}
		}
		else{
			$list = array();
		}
		
		return $list;
	}
	
	
	// 获取信用卡列表
	// author lzm
	public static function getCreditList($bank_id,$keyword) {
		if (is_null ( $bank_id )) {
			return;
		}
		$where = array();
		if(!empty(trim($keyword))){
			$where['a.card_name'] = array("like","%{$keyword}%");
		}
		$where["a.is_deleted"] = 0;
		$where["a.bank_id"] = $bank_id;
		$field = "*";
		$list = db ( 'bank_card' )->alias ( 'a' )->field ( $field )->where ($where)->select();
		return $list;
	}
	
	// 获取贷款列表
	// author lzm
	public static function getLoanList($loan_id,$keyword) {
		if (is_null ( $loan_id )) {
			return;
		}
	    $where = array();
        if(!empty(trim($keyword))){
        	$where['a.name'] = array("like","%{$keyword}%");
        }
        $where["a.loan_id"] = $loan_id;
        $where["a.is_deleted"] = 0;
		$field = "*";
		$list = db ( 'loan_product' )->alias ( 'a' )->field ( $field )->where ( $where )->select ();
		return $list;
	}
	
		/*
	 * 代理商报价单
	 * 总代的报价单在超过授权的时候生产
	 * 一级代理的报价单是根据总代的报价单生成自己的报价单，
	 * 如果自己的报价单自己没改过的情况下，上级修改了，那么自己的报价单也跟着修改。
	 * 如果自己编辑过的情况下，上级修改报价单，对自己的报价单不影响。依此类推。
	 */
	public function editSysPrice($updata, $id,$agent_id) {
		$data = $updata;
		$where ['id'] = $agent_id;
		
		$flag = false;
		$findrs = db("agent_sys_price")->where("id = '{$id}' and agent_id = '{$agent_id}'")->field("id,type,fy_type,bank_id,card_id,level_price,level1_price,level2_price,level3_price")->find();
		if($findrs){
			$price = $data['level'] + 1;
			for ($i = $price; $i<4; $i++){
				$cfile = "level".$i."_price";
				if($updata[$cfile] != $findrs[$cfile]){
					$flag = true;
				}
			}
			
			$where["id"] = $findrs['id'];
			$data['update_time'] = date("Y-m-d H:i:s",time());
			$data['is_edit'] = 1; //标注自己修改过
			$res = Agent_sys_price::update ( $data, $where );
			
			//记录日志
			unset($data['is_edit']);
			$data['type'] = $findrs['type'];
			$data['fy_type'] = $findrs['fy_type'];
			$data['agent_id'] = $agent_id;
			$data['bank_id'] = $findrs['bank_id'];
			$data['card_id'] = $findrs['card_id'];
			$data['op_type'] = 2;
			$data['operator'] = $agent_id;
			$res = Agent_sys_price_log::create($data,true);
			
			//并且要修改下级价格
			if($flag){		
				$editwhere = array();
				$editwhere['bank_id'] = $findrs['bank_id'];
				$editwhere['card_id'] = $findrs['card_id'];
				$editwhere['type'] = $findrs['type'];
				$editwhere['fy_type'] = $findrs['fy_type'];

				$levlprice = $data['level']+1;
				$modi = array();
				for ($i = $levlprice; $i<4; $i++){
					$modiprice = "level".$i."_price";
					$modi[$modiprice] = $data[$modiprice];
				}
				if($this->updateSubAgentPrice($agent_id,$editwhere,$modi)){
						$sub = db("agent")->field("id,level")->where("parentid ='{$agent_id}'")->select();
						$levlprice = $sub[0]['level'];
						$modi = array();
						for ($i = $levlprice; $i<4; $i++){
							$modiprice = "level".$i."_price";
							$modi[$modiprice] = $data[$modiprice];
						}						
						foreach ($sub as $subv){
								
							if($this->updateSubAgentPrice($subv['id'],$editwhere,$modi)){
									$sub2 = db("agent")->field("id,level")->where("parentid ='{$subv['id']}'")->select();
									if($sub2){
										$levlprice = $sub[0]['level']+1;
										$modi = array();
										for ($i = $levlprice; $i<4; $i++){
											$modiprice = "level".$i."_price";
											$modi[$modiprice] = $data[$modiprice];
										}
										foreach ($sub2 as $subv2){
											$this->updateSubAgentPrice($subv2['id'],$editwhere,$modi);
										}
									}
							}
						}
				}
			}
		}
		else{
			//如果找不到，那么就取当前的数据新增一条
			$findrs = db("agent_sys_price")->where("id = '{$id}'")->field("id,type,bank_id,card_id")->find();
			$data['agent_id'] = $agent_id;
			$data['type'] = $findrs['type'];
			$data['bank_id'] = $findrs['bank_id'];
			$data['card_id'] = $findrs['card_id'];
			$res = Agent_sys_price::create($data,true);
			//记录日志
			$data['op_type'] = 1;
			$data['operator'] = $agent_id;
			$res = Agent_sys_price_log::create($data,true);
		}
		return $res; // return Agent::getLastSql();
	}
	
	//更新下级代理商报价单
	public function updateSubAgentPrice($agent_id,$where,$modi){
		$sub = db("agent")->field("id,level")->where("parentid ='{$agent_id}'")->select();
		if($sub){
			foreach ($sub as $v){
				$editwhere = $where;
				$editwhere['agent_id'] = $v['id'];
				$editwhere['is_edit'] = 0;
				$rs = db("agent_sys_price")->field("is_edit")->where($editwhere)->find();
				if($rs){
					if($rs["is_edit"] == 0){
						if($v['level'] !=0){
							$updatewhere =array();
							$updatePrice = array();
							$updatePrice = $modi;
							$updatewhere = $where;
							$updatewhere["agent_id"] = $v["id"];
							$rs = db("agent_sys_price")->where($updatewhere)->update($updatePrice);
							//echo db()->getLastSql();
						}
						return true;
					}
					else{
						return false;
					}
				}
			}
		}
	}
	
	// 添加代理商报价单
	public function addAgentPrice($updata,$agent_id,$parent_agent_id) {
		$data = $updata;
		$data['agent_id'] = $agent_id;
		$data['creater'] = $parent_agent_id;
		$res = Agent_price::create ( $data, true );
		$log = Agent_price_log::create ( $data, true );
		return $res; // return Agent::getLastSql();
	}
	// 获取特定代理商报价单
	public static function getAgentPrice($type, $bank_id, $agent_id) {
		$where ['a.type'] = $type;
		$where ['a.agent_id'] = $agent_id;
		$where ['b.is_deleted'] = 0;
		$info = array ('data'=>'');
		$res = false;
		if($type ==1){
			$where ['a.bank_id'] = $bank_id;
			$res = db("agent_price")->alias ( 'a' )->join ( "bank b", "a.bank_id =b.id", 'left' )->where ( $where )
			->field ( "a.level_price,a.level1_price,a.level2_price,a.level3_price,case b.price_type when 1 then 'CPA' when 2 then 'CPA' when 3 then 'CPA' else 'CPA' end price_type,b.rule_description" )->find ();
			/* $res = db("agent_price")->alias ( 'a' )->join ( "bank_card b", "a.card_id =b.id", 'left' )->where ( $where )
			->field ( "a.level_price,a.level1_price,a.level2_price,a.level3_price,case b.price_type when 1 then '金额' when 2 then '比例' when 3 then '注册数' else '未知' end price_type" )->find (); */		
		}
		elseif($type ==2){
			$where ['a.card_id'] = $bank_id;
			$card_id_res = db("agent_price")->alias ( 'a' )->join ( "loan_product b", "a.card_id = b.id", 'left' )->where ( $where )
			->field ( "a.card_id" )->select();
			$array = array();
			if($card_id_res){
				foreach ($card_id_res as $k =>$v){
					$where ['a.card_id'] = $v['card_id'];
					$res = db("agent_price")->alias ( 'a' )->join ( "loan_product b", "a.card_id = b.id", 'left' )->where ( $where )
					->field ( "a.card_id,a.fy_type,a.level_price,a.level1_price,a.level2_price,a.level3_price,case b.price_type when 1 then '金额' when 2 then '比例' when 3 then '注册数' else '未知' end price_type" )->select();
				    if($res){
				    	foreach ($res as $kl =>$vl){
				    		$array[$k][$kl] =$vl;
				    		$rs = db("loan_product_price")->field("remark")->where("loan_product_id ='{$vl['card_id']}' and rule_type ='{$vl['fy_type']}'")->find();
				    		$array[$k][$kl]['rule_description'] = $rs['remark'];
				    	}
				    }
				}
				$res = $array;
			}
			
			/* $res = db("agent_price")->alias ( 'a' )->join ( "loan_product b", "a.card_id = b.id", 'left' )->where ( $where )
			->field ( "a.level_price,a.level1_price,a.level2_price,a.level3_price,case b.price_type when 1 then '金额' when 2 then '比例' when 3 then '注册数' else '未知' end price_type" )->find (); */
		}
		
      // echo db()->getLastSql();
		if ($res) {
			$info = $res;
		} else {
			$find = db ( 'agent_sys_price' )->field("agent_id")->where("agent_id ='{$agent_id}'")->find();
			if($find){
				$parenAgentId = $agent_id;
			}
			else{
				$parenAgentId = getAgentMyDefaultPriceAgentId($agent_id);
			}
			$where ['a.agent_id'] = empty($parenAgentId)?0:$parenAgentId;
			if($type ==1){
				$res =  db("agent_sys_price")->alias ( 'a' )->join ( "bank b", "a.bank_id =b.id", 'left' )->where ( $where )
				->field ( "a.level_price,a.level1_price,a.level2_price,a.level3_price,case b.price_type when 1 then 'CPA' when 2 then 'CPA' when 3 then 'CPA' else 'CPA' end price_type,b.rule_description" )->find ();
				
				/* $res =  db("agent_sys_price")->alias ( 'a' )->join ( "bank_card b", "a.card_id =b.id", 'left' )->where ( $where )
				->field ( "a.level_price,a.level1_price,a.level2_price,a.level3_price,case b.price_type when 1 then '金额' when 2 then '比例' when 3 then '注册数' else '未知' end price_type" )->find (); */
			}
			elseif($type ==2){
				$res = db("agent_sys_price")->alias ( 'a' )->join ( "loan_product b", "a.card_id = b.id", 'left' )
				->join ( "loan_product_price c", "a.card_id = c.loan_product_id", 'left' )
				->where ( $where )
				->field ( "a.fy_type,a.level_price,a.level1_price,a.level2_price,a.level3_price,case b.price_type when 1 then '金额' when 2 then '比例' when 3 then '注册数' else '未知' end price_type,c.remark rule_description" )->group("a.fy_type")->select();
		
				/* $res =  db("agent_sys_price")->alias ( 'a' )->join ( "loan_product b", "a.card_id = b.id", 'left' )->where ( $where )
				->field ( "a.level_price,a.level1_price,a.level2_price,a.level3_price,case b.price_type when 1 then '金额' when 2 then '比例' when 3 then '注册数' else '未知' end price_type" )->find (); */
			}	
			if ($res) {
				$info = $res;
			}
		}
		return $info;
	}
	
	// 获取特定代理商自己的报价单
	public static function getMyselfAgentPrice($type, $bank_id, $agent_id) {
		$where ['a.type'] = $type;
		$where ['a.agent_id'] = $agent_id;
		$info = array ('data'=>'');
		$res = false;
		$parentname="";
		$rs = db("agent")->field("parentid")->where("id = '{$agent_id}'")->find();
		if($rs){
			$rs = db("agent")->field("name")->where("id = '{$rs['parentid']}'")->find();
			$parentname = $rs['name'];
		}
		
		if($type ==1){
			$where ['a.bank_id'] = $bank_id;
			$res = db("agent_price")->alias ( 'a' )->join ( "bank b", "a.bank_id =b.id", 'left' )->where ( $where )
			->field ( "a.agent_id,a.bank_id,a.card_id,a.level_price,a.level1_price,a.level2_price,a.level3_price,b.bank_name,case b.price_type when 1 then '金额' when 2 then '比例' when 3 then '注册数' else '未知' end price_type" )->find ();	
		    if($res){
		    	$info = $array;
		    }
		}
		elseif($type ==2){
			$where ['a.card_id'] = $bank_id;
			$res = db("agent_price")->alias ( 'a' )->join ( "loan b", "a.bank_id = b.id", 'left' )->join ( "loan_product c", "a.card_id = c.id", 'left' )
			->join ( "agent d", "a.agent_id = d.id", 'left' )
			->where ( $where )
			->field ( "a.agent_id,a.bank_id,a.card_id,a.fy_type,a.level_price,a.level1_price,a.level2_price,a.level3_price,a.creater,b.name loan_name,c.name product_name,case c.price_type when 1 then '金额' when 2 then '比例' when 3 then '注册数' else '未知' end price_type,d.name agent_name" )->select();
			//echo db()->getLastSql().";";
			if($res){
				foreach ($res as $k =>$v){
					$agentinfo = getAgentInfoById($v["creater"]);
					$res[$k]["creatername"] = $agentinfo["name"];
					$res[$k]["parentname"] = $parentname;
					$rs = db("loan_product_price")->field("remark")->where("loan_product_id ='{$v['card_id']}' and rule_type ='{$v['fy_type']}'")->find();
					$res[$k]['rule_description'] = $rs['remark'];
				}
				$info = $res;
			}
			
/* 			$where ['a.card_id'] = $bank_id;
			$card_id_res = db("agent_price")->alias ( 'a' )->join ( "loan_product b", "a.card_id = b.id", 'left' )->where ( $where )
			->field ( "a.card_id" )->select();
			$array = array();
			if($card_id_res){
				foreach ($card_id_res as $k =>$v){
					$where ['a.card_id'] = $v['card_id'];
					$res = db("agent_price")->alias ( 'a' )->join ( "loan_product b", "a.card_id = b.id", 'left' )->where ( $where )
					->field ( "a.level_price,a.level1_price,a.level2_price,a.level3_price,a.creater,b.name bank_name,case b.price_type when 1 then '金额' when 2 then '比例' when 3 then '注册数' else '未知' end price_type" )->select();
					if($res){
						foreach ($res as $kl =>$vl){
							$agentinfo = getAgentInfoById($vl["creater"]);
							$array[$k][$kl] =$vl;
							$array[$k][$kl]["creatername"] = $agentinfo["name"];
							$array[$k][$kl]["parentname"] = $parentname;
						}
					}
				}
				$info = $array;
			}	 */	
		}
		
		return $info;
	}
	
	
	// 编辑代理商报价单
	public function editAgentPrice($updata, $id,$type,$wherecp) {
		$data = $updata;
		$where = array();
		if($type ==1){
			$where ['id'] = $id;
			$res = Agent_price::update ( $data, $where );
		}
		else
		{
			$where = array();
			$where = $wherecp;
			$res = Agent_price::update ( $data, $where );
			//echo db()->getLastSql();
		}
		return $res; // return Agent::getLastSql();
	}
	
	// 申请提现
	public function withdraw($money, $agent_id) {
		if($agent_id){
			$info = db("agent")->field("wx_openid")->where("id ='{$agent_id}'")->find();
			if(!$info){
				return false;
			}
			if(empty($info['wx_openid']) ||  $info['wx_openid'] ==''){
				return false;
			}
			$data = array();
			$data ['agent_id'] = $agent_id;
			$data ['openid'] = $info['wx_openid'];
			$data ['withdraw'] = $money;
			$data ['apply_time'] = date ( "Y-m-d H:i:s", time ());
			$data ['status'] = 1;

			$rs = db ( "Agent_withdraw_log" )->insert ( $data );
			if($rs){
				return true;
			}
			
			return false;
		}
		return false;
	}
	
	// 获取报表列表
	// author lzm
	public static function getReportList($aid, $stime, $etime, $page, $pagesize) {
		if (is_null ( $aid )) {
			return;
		}
		if ($page <= 1 || empty ( $page )) {
			$page = 1;
		}
		$pagesize = empty ( $pagesize ) ? 8 : $pagesize;
		$field = "a.id,a.log_id,date_format(a.confirm_time, '%Y-%m-%d') time,a.confirm_time,a.commission,a.type,case a.type when 1 then '开卡' when 2 then '贷款' else '未知' end typename ,c.user_name,c.phone,
				      case a.status when 1  then '审核中' when 2  then '通过' when 3 then '拒绝' end zt";
		if (! empty ( $stime ) && ! empty ( $etime )) {
			$start = $stime . " 00:00:00";
			$end = $etime . " 23:59:59";
			$where ['a.confirm_time'] = array (
					'between',
					array (
							$start,
							$end 
					) 
			);
		}
		if(!empty($aid)){
             $where ['a.agent_id'] = $aid;
		}
		$where ['a.status'] = 2;
		
		$total = db ( 'agent_commission_log' )->alias ( 'a' )->field ( "count(a.*) cnt" )->where ( $where )->count ();
		
		$page_count = ceil ( $total / $pagesize );
		
		if ($page > $page_count) {
			$page = empty ( $page_count ) ? 1 : $page_count;
		}
		$offset = $pagesize * ($page - 1);
		$list = db ( 'agent_commission_log' )->alias ( 'a' )
		->field ( $field )->join ( "agent b", "a.agent_id = b.id", 'left' )
		->join ( "user c", "a.user_id = c.id", 'left' )
		->where ( $where )->limit ( $offset, $pagesize )->select ();
		if($list){
			$name = "";
			foreach ($list as $k =>$v){
				 if($v['type'] == 1){
				 	    $rs = db("bank_card_log")->alias ( 'a' )
				 	            ->field("b.card_name")
						 	    ->join("bank_card b","a.bank_id = b.id","left")
						 	    ->where("a.id = '{$v['log_id']}'")->find();
				 }
				 if($v['type'] ==2){
					 	$rs = db("loan_log")->alias ( 'a' )
							 	->field("b.name card_name")
							 	->join("loan_product b","a.loan_product_id = b.id","left")
							 	->where("a.id = '{$v['log_id']}'")->find();
				 }
				 if($rs){
				 	$name = $rs["card_name"];
				 }
				 $list[$k]["card_name"] = $name;
			}
		}
		$data = array ();
		$data ['data'] = $list;
		$data ['total'] = $total;
		$data ['page'] = $page;
		$data ['page_count'] = $page_count;
		return $data;
	}
	
	//
	// author lzm
	public function getLevelAttr($val) { // 为字段名
		switch ($val) {
			case 0 :
				return "特级";
				break;
			case 1 :
				return "一级";
				break;
			case 2 :
				return "二级";
				break;
			case 3 :
				return "三级";
				break;
			default :
				return "未知";
				break;
		}
	}
	
	// 字段完成
	// author lzm
	protected $insert = [ 
			'create_time' 
	];
	protected $update = [ 
			'update_time' 
	];
	public function setCreatetimeAttr() { // 操作执行
		return date ( 'Y-m-d H:i:s', time () );
	}
	public function setUpdatetimeAttr() { // 更新才执行
		return date ( "Y-m-d H:i:s", time () );
	}
}

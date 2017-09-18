<?php
/**
 * 	作用：产生随机字符串，不长于32位
 */
  function createNoncestr( $length = 32,$start=0 )
{
	$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
	$str ="";
	for ( $i = 0; $i < $length; $i++ )  {
		$str.= substr($chars, mt_rand($start, strlen($chars)-1), 1);
	}
	return $str;
}

//MD5加密
 function encryptPassword($password, $salt = '', $encrypt = 'md5')
{
	return $encrypt($password . $salt);
}

 function request_by_curl($url, $data) {
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	if (!empty($data)){
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	}
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($curl);
	curl_close($curl);
	return $output;

}

//获取总代id
function getParentAgentID($aid){
	$parentid = db('agent') ->where("id",'eq',$aid)->field("parentid")->find();
	if($parentid){
		$id = (int)$parentid['parentid'];
		if($id == 0){
			return $aid;
		}
		else{
			return getParentAgentID($id);
		}
	}
    return $aid;
}
 
//获取所有下级代理商
function getAllSubAgentIds($aid){
	$subAgentIds = db('agent')->field("id,parentid,level")->select();
	return getSubs($subAgentIds,$aid);
}

//获取某个分类的所有子分类  
function getSubs($categorys,$aid){  
    $subs=array();  
    foreach($categorys as $item){  
        if($item['parentid']==$aid){  
            $subs[]=$item;  
            $subs=array_merge($subs,getSubs($categorys,$item['id']));  
        }  
    }  
    return $subs;  
} 

//获取自己的报价单
//超管后台审核代理---1.可以对总代进行授权 2.授权（对crd_agent_sys_price 添加数据）；3.取消授权（对对crd_agent_sys_price 删除数据）
//
function  getMyselfPice($aid,$field,$type){
	$parenAgentId = $aid;
	$where ['a.agent_id'] = empty($parenAgentId)?0:$parenAgentId;
	$where ['b.is_deleted'] = 0;
	$where ['a.type'] = $type;
	$default = false;
	$general_agent_id = getParentAgentID($aid);//找出总代id
	if ($type == 1) { // 信用卡
		//找出该代理商的总代所有银行产品
		$bank  = db("agent_bank_loan")->alias ( 'a' )
		->join ( "bank b", "a.bank_loan_id = b.id", 'left' )
		->field("a.bank_loan_id bank_id")->where("a.agent_id ='{$general_agent_id}' and a.type = 1")->select();
		//echo db()->getLastSql();
		if($bank){
			$bank_id = "";
			foreach ($bank as $v){
				  if($bank_id ==""){
				  	   $bank_id = $v["bank_id"];
				  }
				  else
				  {
				  	  $bank_id = $bank_id.",".$v["bank_id"];
				  }
			}
			if($bank_id ==""){
				 $where["a.bank_id"] = -1;
			}
			else
			{
				$where["a.bank_id"] = array("in",$bank_id);
			}
			
			$field = $field.",a.id,a.bank_id,a.card_id,b.bank_name card_name,case b.price_type when 1 then 'CPA' when 2 then 'CPA' when 3 then 'CPA' else 'CPA' end price_type,
					     case b.price_type when 1 then '元' when 2 then '%' when 3 then '元' else '' end price_unit,b.rule_description";
			$default = db ( 'agent_sys_price')->alias ( 'a' )->field ($field )->join ( "bank b", "a.bank_id =b.id", 'left' )->where ( $where )->group("a.bank_id")->order("a.bank_id")->select ();
		}
		
	    // echo db()->getLastSql();
	} else { // 贷款
		//找出该代理商的总代所有贷款产品
		$card  = db("agent_bank_loan")->alias ( 'a' )
					 ->join ( "loan_product b", "a.bank_loan_id = b.id", 'left' )
					 ->field("b.loan_id bank_id,a.bank_loan_id card_id")->where("a.agent_id ='{$general_agent_id}' and a.type = 2")->select();
		//$card = db ( 'agent_sys_price')->alias ( 'a' )->field ( "a.bank_id,a.card_id" )->join ( "loan_product b", "a.card_id = b.id", 'left' )->where ( $where )->group("a.card_id")->order("a.bank_id,a.card_id")->select ();
	    $data = array();
	    if($card){
	    	foreach ($card as $k=>$v){
	    		$where ['a.bank_id'] = $v['bank_id'];
	    		$where ['a.card_id'] = $v['card_id'];
	    		$where ['c.is_deleted'] = 0;
	    		$field = $field.",a.id,a.fy_type,a.bank_id,a.card_id,b.name card_name,case b.price_type when 1 then '金额' when 2 then '比例' when 3 then '注册数' else '未知' end price_type,
			     case c.rule_type when 1 then '元' when 2 then '%'  else '' end price_unit";
	    		$default = db ( 'agent_sys_price')->alias ( 'a' )->field ( $field )
	    		->join ( "loan_product_price c", "a.card_id = c.loan_product_id and a.fy_type = c.rule_type", 'INNER' )
	    		->join ( "loan_product b", "a.card_id = b.id", 'left' )
	    		->group("a.fy_type")->where ( $where )->order("a.bank_id,a.card_id")->select ();
	    		if($default){
	    			foreach ($default as $kl =>$val){
	    				$data[$k][$kl] = $val;
	    				$rs = db("loan_product_price")->field("remark")->where("loan_product_id ='{$val['card_id']}' and rule_type ='{$val['fy_type']}'")->find();
	    				$data[$k][$kl]['rule_description'] = $rs['remark'];
	    			}
	    		}
	    	}
	    	$default = $data;
	    }
	}
	return $default;
}




//获取代理商默认报价单(返回agentid)
function getAgentMyDefaultPriceAgentId($aid){
	$parentid = db("agent")->field("id,parentid")->where("id ='{$aid}'")->find();
	if($parentid){
		if($parentid['parentid'] !=0){
			$findrs = db ( 'agent_sys_price' )->where("agent_id = '{$parentid['parentid']}'")->find();
			if($findrs){
				return $findrs["agent_id"];
			}
			else{
				 return getAgentMyDefaultPriceAgentId($parentid['parentid']);
			}
		}
		else{
			return $aid;
		}
	}
    return false;
}

//获取所有上级代理商
function getAllParentAgentIds($aid){
	$ParentAgentIds = db('agent')->field("id,parentid,level")->select();
	return getParents($ParentAgentIds,$aid);
}

//获取某个分类的所有父分类  
function getParents($categorys,$aid){  
    $subs="";  
    foreach($categorys as $item){  
        if($item['id']==$aid){  
            $subs = $item['id'];
            if(getParents($categorys,$item['parentid'])){
            	$subs=$subs.",".getParents($categorys,$item['parentid']);
            }
        }  
    }  
    return $subs;  
} 



//获取子代理商
function getSubAgentIds($aid){
	$subAgentIds = db('agent') ->where("parentid",'eq',$aid)->field("id")->select();
	$subAgentIdstr = "";
	foreach($subAgentIds as $v){
		if($subAgentIdstr == ""){
			$subAgentIdstr = $v['id'];
		}
		else{
			$subAgentIdstr = $subAgentIdstr.",".$v['id'];
		}
	}
	return $subAgentIdstr;
}

//获取代理商信息
function getAgentInfoById($aid){
	$res = db('agent') ->where("id",'eq',$aid)->field("id,name,phone,parentid,level")->find();	
	return $res;
}

//获取客户端ip地址
function get_client_ip()
{
	$arr_ip_header = array(
			'HTTP_CDN_SRC_IP',
			'HTTP_PROXY_CLIENT_IP',
			'HTTP_WL_PROXY_CLIENT_IP',
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'REMOTE_ADDR',
	);
	$client_ip = 'unknown';
	foreach ($arr_ip_header as $key)
	{
		if (!empty($_SERVER[$key]) && strtolower($_SERVER[$key]) != 'unknown')
		{
			$client_ip = $_SERVER[$key];
			break;
		}
	}
	return $client_ip;
}

/*
 * get形式调用接口获取数据
 */
function http_request($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data =  curl_exec($ch);
    if(curl_errno($ch)){return 'ERROR'.curl_error($ch);}
    curl_close($ch);
    return $data;
}

/*
 * post形式调用接口获取数据
 */
function http_request_post($url,$data = null)
{
  //  $curlPost = http_build_query($data);
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data))
    {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}






/*
 * 帮客来服务平台手表总代理申请信贷代理通过，更改帮客来的状态
 */
function bklChanggeStatus($params)
{
    $url = config('bkl_url_base') . '/api.php/BklTofuturnCredit/applyAgentNotify';
    $result = request_by_curl($url, $params);
    return $result;
}

function signData($key, $data) {
	ksort($data);
	$data['key'] = $key;
	$sign_now = strtoupper(MD5(urldecode(http_build_query($data))));

	return $sign_now;
}

function getPrjKeyValue($index,$key){
	  $arr = config("PRJ_INFO");
	  return $arr[$index][$key];
}

		
function can_create_sub_agent($aid){
	  if(empty($aid)){
	  	   $data['return_code'] = 0;
	  	   $data['return_msg'] = "参数aid不能为空";
	  	   return $data;
	  }
	  //已有下家数
	  $cnt = db("agent")->where("parentid ='{$aid}' and is_deleted =0")->count();
	 
	  $agentinfo = getAgentInfoById($aid);
	  $level = $agentinfo["level"]+1;
	  $field = "level".$level."_num";
	  //找出代理商可发展下家
	  $num = db("agent_num")->where("agent_id = '{$aid}'")->find();
	  if(!$num){
	  	$num = db("agent_num")->where("agent_id = '0'")->find();
	  }
	  $can_num = $num[$field];
	  if((int)$cnt >= (int)$can_num){
	  	   $data['return_code'] =1;
	  	   $msg ="添加失败，当前账号可添加{$can_num}个推广员，目前已添加{$can_num}个，还可添加0个";
	  	   $data['return_msg'] = $msg;
	  	   return $data;
	  }
	  else
	  {
	  	$num = (int)$can_num - (int)$cnt-1;
	  	$hascreate = $cnt+1;
	  	$data['return_code'] =2;
	  	$msg ="当前账号可添加{$can_num}个推广员，目前已添加{$hascreate}个，还可添加{$num}个";
	  	$data['return_msg'] = $msg;
	  	return $data;
	  }
	  
}		
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
function getAllSubAgentIds($aid,$level){
	$subAgentIds = db('agent')->field("id,parentid,level")->select();
	return getSubs($subAgentIds,$aid,$level);
}

//获取某个分类的所有子分类  
function getSubs(&$categorys,$aid,$level){  
    foreach($categorys as $key=>$item){  
        if($item['parentid']==$aid){  
        	$item['level'] = $level;
            $subs[]=$item; 
            unset($categorys[$key]);
            getSubs($categorys,$item['parentid'],$level+1);  
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
 * 帮客来服务平台手表总代理申请信贷代理通过，更改帮客来的状态
 */
function bklChanggeStatus($params)
{
    $url = config('bkl_url_base') . '/api.php/BklTofuturnCredit/applyAgentNotify';
    $result = request_by_curl($url, $params);
    return $result;
}
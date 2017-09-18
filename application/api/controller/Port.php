<?php
namespace app\api\controller;
use think\Log;
use think\Config;
class Port extends Base {
    //拍拍贷接口
    public function paipaidai_url($phones) {
        $token = "c46c2106189e48e5a96450c16c824cbf";
        $phone = $phones;
        $paramMd5Str = MD5 ( "token={$token}&phone={$phone}" );
        $sign = MD5 ( "token={$token}&phone={$phone}&paramMd5={$paramMd5Str}" );
        $cid = "200";
        $sid = "338";
        $post ["ChannelId"] = ( int ) $cid;
        $post ["SourceId"] = ( int ) $sid;
        $post ['token'] = "c46c2106189e48e5a96450c16c824cbf";
        $post ['sign'] = $sign;
        $post ['phone'] = $phone;
        $post ['platformType'] = 2;
        $list= json_decode(request_by_curl('http://cps.ppdai.com/bd/RegLogin', $post),1);
        return $list;
    }
    //华融接口
    public function huarong_url($telephonenumber,$name,$loan_amount,$city,$duration ){

    }
}

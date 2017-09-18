<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$
$extra_file_list = \think\Config::get('extra_file_list');
return [
   /*  'wx_AppID' =>'wx1a6243d5de4e6131',
    'wx_AppSecret' =>'879afcb4902b8adffd74466ca1b404dd', */
	'wx_AppID' =>'wx3e7b405e4757f7a2',
	'wx_AppSecret' =>'b52b38b8ef090125843f80c36a311511',
    'app_debug' => true,
	'sid_user' => '_LAU', // 登录标识
    'url_route_on' => true,
    'default_timezone'=>'Asia/Shanghai',
    'extra_file_list'=>[ APP_PATH . 'helper.php', THINK_PATH . 'helper.php', APP_PATH .'/common/function.php'], //定义函数
    'log'   => [
    'type' => 'File',
    'path' => LOG_PATH,
    ],
    'default_module'      => 'api',
    'default_ajax_return' => 'json',
    'dispatch_success_tmpl'  => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',
    'dispatch_error_tmpl'    => APP_PATH  . 'tpl' . DS . 'dispatch_jump.tpl',
    // 开启语言包功能
    'lang_switch_on' => true,
    // 支持的语言列表
    'lang_list'     => ['zh-cn'],
    //添加短信验证参数
    'purpose'=>"聚合共享",
    'appkey' =>'23396438',
    'secretKey' =>'d2c5e50ee22a929a0261c0b42cf38ee5',
  //  'template' =>'SMS_91910049',
    'sms_template_1' =>'SMS_91910049',//用户端登录
    'sms_template_2' =>'SMS_91860070',//提现申请
    'sms_template_3' =>'SMS_91990053',//代理商申请
    'sms_template_4' =>'SMS_94355038',//申请修改密码

//
/*     'web_url' =>"http://test.api.futurecredit.net/web/",
  'api_url' =>"http://test.api.futurecredit.net/api/", */

//    'api_url' =>"http://test.api.futurecredit.net/api/",
//    'web_url' =>"http://192.168.1.115/web/",
//    'api_url' =>"http://192.168.1.115/api/",

   'web_url' =>"http://local.futurecreditapi.com/web/",
     'api_url' =>"http://local.futurecreditapi.com/api/",
    'agent_aid_prefix' => "bkl_",
    'bkl_url_base' => "http://test.banklay.com.cn",
   'file_url' => "test.admin.futurecredit.net", //后台地址
   'get_commission_url' => "http://test.api.futurecredit.net/api/agent/getCommissionByAgentID", //获取佣金接口
    'file_url' => "api.futurecredit.net", //后台地址
    'cache' =>[
	    //驱动方式
	    'type' =>'File',
	    //缓存保存目录
	    'path' =>CACHE_PATH,
	    //缓存前缀
	    'prefix' =>'',
	    //缓存有效期，0表示永久缓存
	    'expire' =>0
    ],
    "PRJ_INFO" => array(
    		"3" => array("key" => "asd13~=jee45-:dg","ip_limits"=>""),
    ),

    'session'            => [
        'prefix'         => 'think',
        'type'           => '',
        'auto_start'     => true,
        'expire'         => 7200,
    ],
];

<?php
/**
  * Created by PhpStorm.
 * User: 申法宽
 * Date: 16/4/25
 * Time: 13:19
 * Email: sfk@live.cn
 * File: VerifyHelper.php
 */
namespace app\common\controller;
use think\Controller;
use think\Session;
use think\Config;
use Gregwar\Captcha\CaptchaBuilder;

class VerifyHelper
{
	
    public static function verify(){
    	    new CaptchaBuilder();
    }
/**
     * 检测验证码是否正确
     * @param $code
     * @return bool
     */
    public static function check($code)
    {
        return ($code == session('verify_code') && $code != '') ? true : false;
    }
}
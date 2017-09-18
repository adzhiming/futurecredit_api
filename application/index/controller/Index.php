<?php
namespace app\index\controller;
use think\Log;
use think\Config;
class Index extends Base {
    public function index() {
        //return 'hahahah';
    	return $this->returnData(self::RET_CODE_OK,'',array('count' =>100));
    }
}

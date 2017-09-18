<?php

namespace app\common\controller;

class FW {
	static private $_namespace_root = '';
	
	static function init(){
		self::$_namespace_root = str_replace("\\", '/', dirname(__FILE__). '/..');
		spl_autoload_register(__NAMESPACE__ . '\FW::_autoLoadByNamespace');
	}
	
	static function _autoLoadByNamespace($classname){
		$loaded = false;
		$path = self::$_namespace_root . '/' . str_replace('\\', '/', $classname) . '.php';
		
		if(file_exists($path)){
			include_once $path;
			$loaded = true;
		}
		//load thirdparty library
		else {			
			$baseUrl = LIB_PATH;
			$classFile = $baseUrl . $classname . DIRECTORY_SEPARATOR . $classname . '.php';
						
			/**
			$filePaht = explode("\\", $classname);
			
			if (count($filePaht) > 1) {
				$dir = $filePaht[0];
				$file = $filePaht[1];
				$classFile = $baseUrl . $dir . DIRECTORY_SEPARATOR . $file . '.php';
			}
			**/			

			$subDirectory = strrpos($classname, "\\");
			if ($subDirectory) {
				$classname = str_replace("\\", '/', $classname);//fix directory separator on linux
				$dir = substr($classname, 0, $subDirectory); //get dir path
				$trueClassName = substr($classname, $subDirectory + 1);//get class name
				$classFile = $baseUrl . $dir . DIRECTORY_SEPARATOR . $trueClassName . '.php';
			}			
			//if (false !== strpos($classname, 'UseCommand')) { var_dump($classFile); exit(test); }
			if (file_exists($classFile)) {
				include_once $classFile;
				$loaded = true;
			}
		}
		
		return $loaded;
	}

	static public function _REQUEST($var, $default=null){
		$return = isset($_POST[$var]) ? $_POST[$var] : (isset($_GET[$var]) ? $_GET[$var] : $default);
		return $return;
	}
	static public function _POST($var, $default = null){
		$return = isset($_POST[$var]) ? $_POST[$var] : $default;
		return $return;
	}
	static public function _GET($var, $default = null){
		$return = isset($_GET[$var]) ? $_GET[$var] : $default;
		return $return;
	}
	static public function _COOKIE($var, $default=null){
		$return = isset($_COOKIE[$var]) ? $_COOKIE[$var] : $default;
		return $return;
	}
	/**
	 * 输出JSON头和数据
	 */
	static function returnJSON($obj){
		header('Content-type: text/json');
		echo json_encode($obj);
		//exit;
	}
	static function header404(){
		header("HTTP/1.0 404 Not Found");
		exit;
	}
	static function return404(){
		header("HTTP/1.0 404 Not Found");
		exit;
	}
	
	public static function header403() {
		header('HTTP/1.0 403 Forbidden');
		exit;
	}
	
	static function debug(){
		$debug = debug_backtrace();
		$arr = func_get_args();
		echo $debug[0]['file'] . ':' . $debug[0]['line'] . "=> <br />\r\n";
		foreach ($arr as $v){
			echo "<pre>\r\n";
			var_export($v);
			echo "\r\n</pre>\r\n\r\n";
		}
// 		debug_print_backtrace(null,2);
	}
}
FW::init();
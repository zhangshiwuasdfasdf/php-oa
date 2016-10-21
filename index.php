<?php
    // +----------------------------------------------------------------------
    // | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
    // +----------------------------------------------------------------------
    // | Copyright (c) 2012 http://thinkphp.cn All rights reserved.
    // +----------------------------------------------------------------------
    // | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
    // +----------------------------------------------------------------------
    // | Author: liu21st <liu21st@gmail.com>
   // +----------------------------------------------------------------------
    header("Content-type:text/html;charset=utf-8");
    // 瀹氫箟ThinkPHP妗嗘灦璺緞
    //瀹氫箟椤圭洰鍚嶇О鍜岃矾寰�
	if(file_exists("install.php")){
		Header("Location: /install.php");
	}
    define('APP_NAME', 'App');
    define('APP_PATH', './App/');
    define('APP_DEBUG',true);

    // 引入ThinkPHP入口文件
    require( "./ThinkPHP/ThinkPHP.php");
    // 亲^_^ 后面不需要任何代码了 就是如此简单
?>
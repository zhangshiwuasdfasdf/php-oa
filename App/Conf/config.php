<?php
    if (!defined('THINK_PATH')) exit();
    $array=array(
    	//* 模板相关配置 */
    	'TMPL_PARSE_STRING' => array(
    			'__INS__' => __ROOT__ . '/Public/Ins',
    			'__STATIC__' => __ROOT__ . '/Public/Static',
    			'__ADDONS__' => __ROOT__ . '/Public/' . MODULE_NAME . '/Addons',
    			'__IMG__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/images',
    			'__CSS__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/css',
    			'__JS__'     => __ROOT__ . '/Public/' . MODULE_NAME . '/js',
    	),
        'LOAD_EXT_CONFIG'	=>'db,auth,wechat,ldap',
		'VAR_PAGE'	=>'p',
		'TMPL_EXCEPTION_FILE'=>APP_PATH.'/Tpl/Public/error.html',
		'TMPL_NO_HAVE_AUTH'=>APP_PATH.'/Tpl/Public/no_have_auth.html',	
		'TMPL_CACHE_ON' => false,
		'TOKEN_ON'=>false, 
		'URL_CASE_INSENSITIVE' =>   true,
		'TMPL_STRIP_SPACE'=>false,
		'URL_HTML_SUFFIX' => '',
		'DB_FIELDS_CACHE'=>false,
        'SESSION_AUTO_START'=>true,
        'USER_AUTH_KEY'	=>'auth_id',	// 用户认证SESSION标记
        'ADMIN_AUTH_KEY'			=>'administrator',        
        'USER_AUTH_GATEWAY'=>'login/index',// 默认认证网关
    	'MOBILE_TOKEN_LIFETIME'=>24*60*60,// 手机端token的有效期
        'DB_LIKE_FIELDS'            =>'content|remark',
		'SAVE_PATH'=>"Data/Files/",
    	'SHOW_PAGE_TRACE' => true,
        'ADMIN_MAIL_ACCOUNT'=>array('smtpsvr'=>'smtp.163.com','email'=>'zhang6970153@163.com','mail_id'=>'zhang6970153@163.com','mail_pwd'=>'zx6624000
        ','mail_name'=>'神洲酷奇'),
    	
    	'DATA_CACHE_TYPE' => 'Memcache',
    	'DATA_CACHE_TIME' => '3600',
    		
//     	'SESSION_TYPE'          =>  'Memcache',
    	'MEMCACHE_HOST'=>'127.0.0.1',
    	'MEMCACHE_PORT'=>'11211',
    		
    	'TEST_PARAM' =>true,
    );
    return $array;
?>
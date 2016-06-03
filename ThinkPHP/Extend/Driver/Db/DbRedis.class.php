<?php
// +----------------------------------------------------------------------
// | BeauytSoft
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://beauty-soft.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: ceiba <kf@86055.com>
// +----------------------------------------------------------------------
defined('THINK_PATH') or exit();
class DbRedis extends Db{
	protected $_redis           =   null; // Redis Object
    protected $_keyname      =   null; // Redis Key
    protected $_dbName          =   ''; // dbName
    protected $_cursor          =   null; // Reids Cursor Object
    /**
     * 架构函数 读取数据库配置信息
     * @access public
     * @param array $config 数据库配置数组
     */
    public function __construct($config=''){
        if ( !class_exists('redis') ) {
            throw_exception(L('_NOT_SUPPERT_').':redis');
        }     
        if(!empty($config)) {        	
            $this->config   =   array(
            	"REDIS_HOST"=>C("REDIS_HOST"),
            	"REDIS_PORT"=>C("REDIS_PORT"),
            	"REDIS_AUTH"=>C("REDIS_AUTH"),            	
            	
            );
            if(empty($this->config['params'])) {
                $this->config['params'] =   array();
            }
            
        }
    }
    /**
     * 连接数据库方法
     * @access public
     */
    public function connect($config='',$linkNum=0) {
        if ( !isset($this->linkID[$linkNum]) ) {
            if(empty($config))  $config =   $this->config;           
            $redis = new Redis();  
			$redis->connect($config["REDIS_HOST"]?$config["REDIS_HOST"]:"localhost",$config["REDIS_PORT"]?$config["REDIS_PORT"]:6379);  
			$redis->auth($config["REDIS_AUTH"]?$config["REDIS_AUTH"]:""); 
			$info=$redis->info();
            // 标记连接成功
            if (!empty($info["redis_version"])){
            	$this->linkID[$linkNum] = $redis;
            	$this->connected    =   true;
            }
            // 注销数据库连接配置信息
            if(1 != C('DB_DEPLOY_TYPE')) unset($this->config);
        }
        return $this->linkID[$linkNum];
    }
    /**
     * 切换当前操作的Db和redis key
     * @access public
     * @param string $keyname  redis key
     * @param string $db  db
     * @param boolean $master 是否主服务器
     * @return void
     */
    public function switchKey($keyname,$db='',$master=true){
        // 当前没有连接 则首先进行数据库连接
        if ( !$this->_linkID ) $this->initConnect($master);
        try{
            if(!empty($db)) { // 传入Db则切换数据库
                // 当前MongoDb对象
                $this->_dbName  =  $db;
                $this->_redis = $this->_linkID->select($db);
            }
            // 当前MongoCollection对象
            if(C('DB_SQL_LOG')) {
                $this->queryStr   =  $this->_dbName.'.getKey('.$keyname.')';
            }
            if($this->_keyname != $keyname) {
                N('db_read',1);
                // 记录开始执行时间
                G('queryStartTime');
                $this->debug();
                $this->_keyname  = $keyname;
            }
        }catch (Exception $e){
            throw_exception($e->getMessage());
        }
    }
	/**
     * 释放查询结果
     * @access public
     */
    public function free() {
        $this->_cursor = null;
    }
    /**
     * 关闭数据库
     * @access public
    */
    public function close() {
        if($this->_linkID) {
            $this->_linkID->close();
            $this->_linkID = null;
            $this->_redis = null;
            $this->_keyname =  null;
            $this->_cursor = null;
        }
    }
    /**
     * 查找记录
     * @access public
     * @param array $options 表达式
     * @return iterator
     */
    public function select($options=array()) { 	
        if(isset($options['table'])) {
            $this->switchKey($options['table'],'',false);
        }
        $cache  =  isset($options['cache'])?$options['cache']:false;
        if($cache) { // 查询缓存检测
            $key =  is_string($cache['key'])?$cache['key']:md5(serialize($options));
            $value   =  S($key,'','',$cache['type']);
            if(false !== $value) {
                return $value;
            }
        }
        $this->model  =   $options['model'];
        N('db_query',1);
        //$query  =  $this->parseWhere($options['where']);
        $field =  $this->parseField($options['field']);
        try{
            if(C('DB_SQL_LOG')) {
                $this->queryStr   =  $this->_dbName.'查询出错:'.$field;
            }
            // 记录开始执行时间
            G('queryStartTime');
            
        	if ($options['limit']){
                $limit=$this->parseLimit($options['limit']);
            }else{
            	$limit=array("0"=>0,"1"=>20);
            }
            if($options['type']) {
                if ($options["type"]==strtolower("list")){
                	//列表
                	$_cursor   = $this->_linkID->lRange($this->_keyname, $limit[0],$limit[1]);
                }elseif ($options["type"]==strtolower("sets")){
                	//集合
                	
                	switch (strtolower($options["where"])) {
                		case "sinterstore":
                			//求交集
                			$_cursor   = $this->_linkID->sInter($field);	                		
                			break;
                		case "sunion":
                			//求并集
                			$_cursor   = $this->_linkID->sUnion($field);
                			break;
                		case "sdiff":
                			//求差值
                			$_cursor   = $this->_linkID->sDiff($field);
                			break;
                		default:
                			$_cursor   = $this->_linkID->sMembers($this->_keyname);           				
                		
                	}
                }elseif ($options["type"]==strtolower("zset")){
                	//有序集合
                	$zsets=$options["order"][0]; 
                	switch (strtolower($zsets)) {
                		case strtolower("zRevRange"):
                			$_cursor   = $this->_linkID->zRevRange($this->_keyname, $limit[0],$limit[1],$options["order"][1]);
                		break;
                		
                		default:
                			$_cursor   = $this->_linkID->zRange($this->_keyname, $limit[0],$limit[1]);
                		break;
                	}
                }elseif ($options["type"]==strtolower("string")){
                	//字符串
                	$_cursor   = $this->_linkID->mget($field);
                }elseif ($options["type"]==strtolower("hash")){
                	//HASH
                	dump($field);
                	if (empty($field)){
                		$_cursor   = $this->_linkID->hGetAll($this->_keyname);
                	}else{
                		$_cursor   = $this->_linkID->hmGet($this->_keyname,$field);
                	}
                }
            }else{
            	$_cursor   = $this->_linkID->lRange($this->_keyname, $limit[0],$limit[1]);
            }
            $this->debug();
            $this->_cursor =  $_cursor;
            $resultSets  =  $_cursor;
            if($cache && $resultSet ) { // 查询缓存写入
                S($key,$resultSet,$cache['expire'],$cache['type']);
            }
            return $resultSets;
        } catch (Exception $e) {
            throw_exception($e->getMessage());
        }
    
    }
    /**
     * 统计记录数
     * @access public
     * @param array $options 表达式
     * @return iterator
     */
    public function count($options=array()){
    	$count=0; 	
        if(isset($options['table'])) {
            $this->switchKey($options['table'],'',false);
        }
        $this->model  =   $options['model'];
        N('db_query',1);
        //$query  =  $this->parseWhere($options['where']);
        $field =  $this->parseField($options['field']);
        try{
            if(C('DB_SQL_LOG')) {
                $this->queryStr   =  $this->_dbName.'查询出错:'.$field;
            }
            // 记录开始执行时间
            G('queryStartTime');
            
        	if ($options['limit']){
                $limit=$this->parseLimit($options['limit']);
            }else{
            	$limit=array("0"=>0,"1"=>20);
            }
            if($options['type']) {
                if ($options["type"]==strtolower("list")){
                	//列表
                	$count   = $this->_linkID->lSize($this->_keyname);
                }elseif ($options["type"]==strtolower("sets")){
                	//集合
                	$count   = $this->_linkID->sCard($this->_keyname);
                }elseif ($options["type"]==strtolower("zset")){
                	//有序集合
                	if (empty($limit)){
                		$count   = $this->_linkID->zSize($this->_keyname);
                	}else {
                		$count   = $this->_linkID->zCount($this->_keyname,$limit[0],$limit[1]);
                	}
                }elseif ($options["type"]==strtolower("string")){
                	//字符串
                }elseif ($options["type"]==strtolower("hash")){
                	//HASH
                	$count   = $this->_linkID->hLen($this->_keyname);
                }
            }else{
            	$count   = $this->_linkID->lSize($this->_keyname);
            }
            $this->debug();
            return $count;
        } catch (Exception $e) {
            throw_exception($e->getMessage());
        }
    
    
    	
    }
    /**
     * 添加数据
     * Enter description here ...
     * @param unknown_type $options
     * @param unknown_type $data
     */
    public function add($options=array(),$data){    	
        if(isset($options['table'])) {
            $this->switchKey($options['table'],'',false);
        }
        $this->model  =   $options['model'];
        N('db_query',1);
        //$query  =  $this->parseWhere($options['where']);
        $field =  $this->parseField($options['field']);
        try{
            if(C('DB_SQL_LOG')) {
                $this->queryStr   =  $this->_dbName.'查询出错:'.$field;
            }
            // 记录开始执行时间
            G('queryStartTime');           
            if($options['type']) {
                if ($options["type"]==strtolower("list")){
                	//列表
                	$add   = $this->_linkID->lPush($this->_keyname,$data);
                }elseif ($options["type"]==strtolower("sets")){
                	//集合
                	$add   = $this->_linkID->sAdd($this->_keyname,$data);
                }elseif ($options["type"]==strtolower("zset")){
                	//有序集合
                	foreach ($data as $key=>$value) {
                		$add   = $this->_linkID->zAdd($this->_keyname,$key,$value);
                	}
                	
                }elseif ($options["type"]==strtolower("string")){
                	//字符串
                	$add   = $this->_linkID->mset($data);
                }elseif ($options["type"]==strtolower("hash")){
                	//HASH
                	$add   = $this->_linkID->hmSet($this->_keyname,$data);
                }
            }else{
            	$add   = $this->_linkID->lPush($this->_keyname,$data);
            }
            $this->debug();
            return $add;
        } catch (Exception $e) {
            throw_exception($e->getMessage());
        }    
    }
    /**
     * 删除数据
     * Enter description here ...
     * @param unknown_type $options
     * @param unknown_type $data
     */
	public function delete($options=array(),$way=""){    	
        if(isset($options['table'])) {
            $this->switchKey($options['table'],'',false);
        }
        $this->model  =   $options['model'];
        N('db_query',1);
        //$query  =  $this->parseWhere($options['where']);
        $field =  $this->parseField($options['field']);
        try{
            if(C('DB_SQL_LOG')) {
                $this->queryStr   =  $this->_dbName.'查询出错:'.$field;
            }
            // 记录开始执行时间
            G('queryStartTime');  
            if ($options["type"]==strtolower("list") || empty($options["type"])){
                	//列表
                	switch (strtolower($way)) {
                		case "lpop":
                			$delete=$this->_linkID->lPop($this->_keyname);
                			break;
                		case "ltrim":
                			$delete=$this->_linkID->lTrim('key', $options["where"][0], $options["where"][1]);
                			break;
                		default:
                			if ($this->_linkID->lSet($this->_keyname,intval($options["where"]),"_deleted_")){                				
                				$delete=$this->_linkID->lRem($this->_keyname,"_deleted_",0);	
                			}
                			break;
                	}
            }elseif ($options["type"]==strtolower("sets")){
                	//集合
                	$delete   = $this->_linkID->sRem($this->_keyname,$options["where"]);
            }elseif ($options["type"]==strtolower("zset")){
                	//有序集合
           			 switch (strtolower($way)) {
                		case strtolower("zremrangebyscore"):
                			$delete   = $this->_linkID->zRemRangeByScore($this->_keyname,$options["where"][0],$options["where"][1]);
                		break;
                		case strtolower("zRemRangeByRank"):
                			$delete   = $this->_linkID->zRemRangeByRank($this->_keyname,$options["where"][0],$options["where"][1]);
                		break;                		
                		default:
                			$delete   = $this->_linkID->zDelete($this->_keyname,$options["where"]);
                		break;
                	}
                	
            }elseif ($options["type"]==strtolower("string")){
                	//字符串
                	$delete   = $this->_linkID->delete($field);
            }elseif ($options["type"]==strtolower("hash")){
                	//HASH
                	$delete   = $this->_linkID->hDel($this->_keyname, $options["where"]);
            }            
            $this->debug();
            return $delete;
        } catch (Exception $e) {
            throw_exception($e->getMessage());
        }    
    }
    
   	/**
     * limit分析
     * @access protected
     * @param mixed $limit
     * @return array
     */
    protected function parseLimit($limit) {
        if(strpos($limit,',')) {
            $array  =  explode(',',$limit);
        }else{
            $array   =  array(0,$limit);
        }
        return $array;
    }
    
    /**
     * field分析
     * @access protected
     * @param mixed $fields
     * @return array
     */
    public function parseField($fields){
        if (is_array($fields)){
        	return $fields;
        }
    }
     /**
     * 取得数据表的字段信息
     * @access public
     * @return array
     */
    public function getFields($keyname=''){
        if(!empty($keyname) && $keyname != $this->_keyname) {
            $this->switchKey($keyname,'',false);
        }
        N('db_query',1);
        if(C('DB_SQL_LOG')) {
            //$this->queryStr   =  $this->_dbName.'.'.$this->_collectionName.'.findOne()';
        }
        try{
            // 记录开始执行时间
            G('queryStartTime');
            $result   =  $this->_linkID->hkeys($this->_keyname);
            $this->debug();
        } catch (Exception $e) {
            throw_exception($e->getMessage());
        }
        if($result) { // 存在数据则分析字段
            $info =  array();
            foreach ($result as $key=>$val){
                $info[$key] =  array(
                    'name'=>$key,
                    'type'=>getType($val),
                    );
            }
            return $info;
        }
        // 暂时没有数据 返回false
        return false;
    }
}
?>
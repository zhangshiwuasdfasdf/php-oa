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
class RedisModel extends Model{
	function _initialize(){
		C("DB_TYPE","redis");
		C("DB_PREFIX",C("REDIS_DB_PREFIX"));
	}
    /**
     +----------------------------------------------------------
     * 利用__call方法实现一些特殊的Model方法
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $method 方法名称
     * @param array $args 调用参数
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function __call($method,$args) {    	
        if(in_array(strtolower($method),array('type','where','order','limit','page'),true)) {
            // 连贯操作的实现
            $this->options[strtolower($method)] =   $args[0];
            return $this;
        }else{
            throw_exception(__CLASS__.':'.$method.L('_METHOD_NOT_EXIST_'));
            return;
        }
    }
    /**
     +----------------------------------------------------------
     * count统计 配合where连贯操作
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return integer
     +----------------------------------------------------------
     */
    public function count(){
        // 分析表达式
        $options =  $this->_parseOptions();
        return $this->db->count($options);
    }
	public function add($data){
        // 分析表达式
        $options =  $this->_parseOptions();
        return $this->db->add($options,$data);
    }
	public function delete($way=""){
        // 分析表达式
        $options =  $this->_parseOptions();
        return $this->db->delete($options,$way);
    }
}

?>
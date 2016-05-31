<?php
/**
+------------------------------------------------------------------------------
* Memcache缓存类
+------------------------------------------------------------------------------
* @category Think
* @package Think
* @subpackage Util
* @author liu21st <liu21st@gmail.com>
* @version $Id$
+------------------------------------------------------------------------------
*/
class CacheMemcache extends Cache
{//类定义开始

/**
* 架构函数
*/
function __construct($options='')
{
if(empty($options)) {
$options = array
(
'host' => '127.0.0.1',
'port' => 11211,
'timeout' => false,
'persistent' => false
);
}
$func = $options['persistent'] ? 'pconnect' : 'connect';
$this->expire = isset($options['expire'])?$options['expire']:C('DATA_CACHE_TIME');
$this->handler = new Memcache;
$this->connected = $options['timeout'] === false ?
$this->handler->$func($options['host'], $options['port']) :
$this->handler->$func($options['host'], $options['port'], $options['timeout']);
$this->type = strtoupper(substr(__CLASS__,6));
}

/**
* 是否连接
*/
private function isConnected()
{
return $this->connected;
}

/**
* 读取缓存
*/
public function get($name)
{
return $this->handler->get($name);
}

/**
* 写入缓存
*/
public function set($name, $value, $ttl = null)
{
if(isset($ttl) && is_int($ttl))
$expire = $ttl;
else
$expire = $this->expire;
return $this->handler->set($name, $value, 0, $expire);
}

/**
* 删除缓存
*/
public function rm($name, $ttl = false)
{
return $ttl === false ?
$this->handler->delete($name) :
$this->handler->delete($name, $ttl);
}

/**
* 清除缓存
*/
public function clear()
{
return $this->handler->flush();
}
}//类定义结束
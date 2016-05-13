<?php
/**
  * NOTICE OF LICENSE
  *
  * THIS SOURCE FILE IS PART OF EVEBIT'S PRIVATE PROJECT.
  *
  * DO NOT USE THIS FILE IN OTHER PLACE.
  *
  * @category    EveBit_Library
  * @package     Application
  * @author      Chen Qiao 
 * @version     $$Id: Email.PHP 175 2011-03-26 09:52:16Z chen.qiao $$
  * @copyright   Copyright (c) 2011 Evebit Inc. China (http://www.evebit.com)
  */
 
/**
  * Email class
  *
  * get mail total count,get mail list,get mail content,get mail attach
  *
  * For a example, if you want to get some specified email list.
  * 
 * $mail = new Evebit_Mail();
  * $mail->mailConnect($host,$port,$user,$pass,'INBOX',$ssl);
  * $mail->mail_list('5,9:20');
  * 
 *
  * show the five,and nine to twenty mail.
  * 
 * $mail->mail_list('5,9:20');
  * 
 *
  * @docinfo
  *
  * @package     Application
  * @author      Chen Qiao 
 * @version     $$Id: Email.PHP 175 2011-03-26 09:52:16Z chen.qiao $$
  */

 class receiveMail {
     
    /**
      * @var resource $_connect
      */
     private $_connect;
     /**
      * @var object $_mailInfo
      */
     private $_mailInfo;
     /**
      * @var int $_total_count
      */
     private $_total_count;
     /**
      * @var array $_total_count
      */
    /**
      * __construct of the class
      */
     public function __construct() {

     }
     
    /**
      * Open an IMAP stream to a mailbox
      *
      * @param string $host
      * @param string $port
      * @param string $user
      * @param string $pass
      * @param string $folder
      * @param string $ssl
      * @param string $pop
      * @return resource|bool
      */
     public function connect($host,$port,$user,$pass,$folder="INBOX",$ssl,$pop=false) {
         if($pop){
             $ssl = $pop.'/'.$ssl.'/novalidate-cert/notls';
         }
         $this->_connect = imap_open("{"."$host:$port/$ssl"."}$folder",$user,$pass);
         if(!$this->_connect) {
             //Evebit_Application::getSession()->addError('cannot connect: ' . imap_last_error());
             return false;   
        }
         return $this->_connect;
     }
     
    /**
      * Get information about the current mailbox
      *
      * @return object|bool
      */
     public function mailInfo(){
         $this->_mailInfo = imap_mailboxmsginfo($this->_connection);
         if(!$this->_mailInfo) {
             echo "get mailInfo failed: " . imap_last_error();
             return false;
         }
         return $this->_mailInfo;
     }

    /**
      * Read an overview of the information in the headers of the given message
      *
      * @param string $msg_range
      * @return array
      */
     public function mail_list($msg_range='')
     {
         if ($msg_range) {
             $range=$msg_range;
         } else {
             $this->mail_total_count();
             $range = "1:".$this->_total_count;
         }
         $overview  = imap_fetch_overview($this->_connect,$range);
         foreach ($overview  as $val) {
             $mail_list[$val->msgno]=$val->message_id;
         }
         return $mail_list;
     }
     
    /**
      * get the total count of the current mailbox
      *
      * @return int
      */
     public function mail_total_count(){
         $check = imap_check($this->_connect);
            $this->_total_count = $check->Nmsgs;
            return $this->_total_count;
     }
     
    /**
      * Read the header of the message
      *
      * @param string $msg_count
      * @return array
      */
     public function mail_header($msg_count) {
         $mail_header = array();
         $header=imap_header($this->_connect,$msg_count);
         if(strtolower($sender->mailbox)!='mailer-daemon' && strtolower($sender->mailbox)!='postmaster') {
			$mail_header['name']=$this->mail_decode($header -> subject);
			$mail_header['mid']=$header -> message_id;
			$mail_header['to']=$this->contact_conv($header -> to);
			$mail_header['from']=$this->contact_conv($header -> from);
			$mail_header['cc']=$this->contact_conv($header -> cc);
			$mail_header['reply_to']=$this->contact_conv($header -> reply_to);
			$create_time=explode(",",$header -> date);
			if (strlen($create_time[0])>6){
				$create_time=$create_time[0];	
			}else{
				$create_time=$create_time[1];	
			}
			$mail_header['create_time']=strtotime($create_time); 
			$subject = $header -> subject;
			$charset = substr($subject, stripos($subject, "=?") + 2, stripos($subject, "?", 3)-2); 
			$content=$this->get_body($msg_count);
			//$mail_header['content']=$this->auto_charset($content, $charset, 'utf-8'); 
			$mail_header['content']=$content;
         }
         return $mail_header;
     }
     
  /**
      * decode the subject of chinese
      *
      * @param string $subject
      * @return sting
      */
 function mail_decode($str) {
	if (stripos($str, 'GBK?B')) {
		$arr_temp = explode(" ", $str); 
		for ($i = 0;$i <= count($arr_temp);$i++) {
			$tmp = str_ireplace('=?GBK?B?', '', $arr_temp[$i]);
			$tmp = str_ireplace('=?', '', $tmp);
			$tmp2 = $tmp2 . auto_charset(base64_decode($tmp), 'gb2312', 'utf-8');
		} 
		return $tmp2 ;
	}
	if (stripos($str, 'GBK?Q')) {
		$arr_temp = explode(" ", $str); 
		for ($i = 0;$i <= count($arr_temp);$i++) {
			$tmp = str_ireplace('=?GBK?B?', '', $arr_temp[$i]);
			$tmp = str_ireplace('=?', '', $tmp);
			$tmp2 = $tmp2 . auto_charset(base64_decode($tmp), 'gb2312', 'utf-8');
		} 
		return $tmp2 ;
	}
	if (stripos($str, 'utf-8?B')) {
		$arr_temp = explode(" ", $str); 
		// dump($arr_temp[0]);
		for ($i = 0;$i <= count($arr_temp);$i++) {
			$tmp = str_ireplace('=?utf-8?B?', '', $arr_temp[$i]);
			$tmp = str_ireplace('=?', '', $tmp);
			$tmp2 = $tmp2 . base64_decode($tmp);
		} 
		return $tmp2 ;
	}
	if (stripos($str, 'utf-8?Q')) {
		$arr_temp = explode(" ", $str); 
		for ($i = 0;$i <= count($arr_temp);$i++) {
			$tmp = str_ireplace('=?utf-8?Q?', '', $arr_temp[$i]);
			$tmp = str_ireplace('=?', '', $tmp);
			$tmp = str_ireplace('?', '', $tmp);
			$tmp2 = $tmp2 . quoted_printable_decode($tmp);
		} 
		return $tmp2 ;
	} 
	if (stripos($str, 'gb2312?B')) {
		$arr_temp = explode(" ", $str); 
		for ($i = 0;$i <= count($arr_temp);$i++) {
			$tmp = str_ireplace('=?gb2312?B?', '', $arr_temp[$i]);
			$tmp = str_ireplace('=?', '', $tmp);
			$tmp2 = $tmp2 . auto_charset(base64_decode($tmp), 'gb2312', 'utf-8');
		}
		return $tmp2 ;
	}
	if (stripos($str, 'gb2312?Q')) {
		$arr_temp = explode(" ", $str); 
		for ($i = 0;$i <= count($arr_temp);$i++) {
			$tmp = str_ireplace('=?gb2312?Q?', '', $arr_temp[$i]);
			$tmp = str_ireplace('=?', '', $tmp);
			$tmp = str_ireplace('?', '', $tmp);
			$tmp2 = $tmp2 . auto_charset(quoted_printable_decode($tmp),'gb2312','utf-8');
		}
		return $tmp2 ;
	}
	if (stripos($str, 'gb18030?B')) {
		$arr_temp = explode(" ", $str); 
		// dump($arr_temp[0]);
		for ($i = 0;$i <= count($arr_temp);$i++) {
			$tmp = str_ireplace('=?gb18030?B?', '', $arr_temp[$i]);
			$tmp = str_ireplace('=?', '', $tmp);
			$tmp2 = $tmp2 . auto_charset(base64_decode($tmp), 'gb2312', 'utf-8');
		} 
		return $tmp2 ;
	}
	if (stripos($str, 'gb18030?Q')) {
		$arr_temp = explode(" ", $str); 
		for ($i = 0;$i <= count($arr_temp);$i++) {
			$tmp = str_ireplace('=?gb18030?Q?', '', $arr_temp[$i]);
			$tmp = str_ireplace('=?', '', $tmp);
			$tmp = str_ireplace('?', '', $tmp);
			$tmp2 = $tmp2 . auto_charset(quoted_printable_decode($tmp),'gb18030','utf-8');
		}
		return $tmp2 ;
	}
	return $str;
}

function auto_charset($fContents,$from,$to){
    $from   =  strtoupper($from)=='UTF8'? 'utf-8':$from;
    $to       =  strtoupper($to)=='UTF8'? 'utf-8':$to;
    if( strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents)) ){
        //如果编码相同或者非字符串标量则不转换
        return $fContents;
    }
    if(is_string($fContents) ) {
        if(function_exists('mb_convert_encoding')){
            return mb_convert_encoding ($fContents, $to, $from);
        }elseif(function_exists('iconv')){
            return iconv($from,$to,$fContents);
        }else{
            return $fContents;
        }
    }
    elseif(is_array($fContents)){
        foreach ( $fContents as $key => $val ) {
            $_key =     auto_charset($key,$from,$to);
            $fContents[$_key] = auto_charset($val,$from,$to);
            if($key != $_key )
                unset($fContents[$key]);
        }
        return $fContents;
    }
    else{
        return $fContents;
    }
}
    /**
      * Mark a message for deletion from current mailbox
      *
      * @param string $msg_count
      */
     public function delete($msg_count) {
         imap_delete($this->_connect,$msg_count);
     }
     
    /**
      * get attach of the message
      *
      * @param string $msg_count
      * @param string $path
      * @return array
      */
     public function get_attach($msg_count,$path) {
		if(!$this->_connect) 
			return false;
		$struckture = imap_fetchstructure($this->_connect,$msg_count);
		$ar="";
		if($struckture->parts)
        {
			foreach($struckture->parts as $key => $value)
			{
				$enc=$struckture->parts[$key]->encoding;
				if($struckture->parts[$key]->ifdparameters)
				{
					$name=$this->mail_decode($struckture->parts[$key]->dparameters[0]->value);
					$cid=$struckture->parts[$key]->id;
					$cid=substr($cid,1,strlen($cid)-2);
					$disposition=$struckture->parts[$key]->disposition;
					
					$name=$cid."_".$disposition."_".$name;
					$message = imap_fetchbody($this->_connect,$msg_count,$key+1);
					if ($enc == 0)
						$message = imap_8bit($message);
					if ($enc == 1)
						$message = imap_8bit ($message);
					if ($enc == 2)
						$message = imap_binary ($message);
					if ($enc == 3)
						$message = imap_base64 ($message); 
					if ($enc == 4)
						$message = quoted_printable_decode($message);
					if ($enc == 5)
						$message = $message;
					$fp=fopen($path.urlencode($name),"w");
					fwrite($fp,$message);
					fclose($fp);
					$ar=$ar.$name.",";
				}
				if($struckture->parts[$key]->parts)
				{
					foreach($struckture->parts[$key]->parts as $keyb => $valueb)
					{
						$enc=$struckture->parts[$key]->parts[$keyb]->encoding;
						if($struckture->parts[$key]->parts[$keyb]->ifdparameters)
						{
							$name=$this->mail_decode($struckture->parts[$key]->parts[$keyb]->dparameters[0]->value);
							$id=$struckture->parts[$key]->parts[$keyb]->id;
							$disposition=$struckture->parts[$key]->parts[$keyb]->disposition;							
							
							$name=$id."_".$disposition."_".$name;
							$partnro = ($key+1).".".($keyb+1);
							$message = imap_fetchbody($this->_connect,$msg_count,$partnro);

							if ($enc == 0)
								   $message = imap_8bit($message);
							if ($enc == 1)
								   $message = imap_8bit ($message);
							if ($enc == 2)
								   $message = imap_binary ($message);
							if ($enc == 3)
								   $message = imap_base64 ($message);
							if ($enc == 4)
								   $message = quoted_printable_decode($message);
							if ($enc == 5)
								   $message = $message;
							$fp=fopen($path.urlencode($name),"w");
							fwrite($fp,$message);
							fclose($fp);
							$ar=$ar.$name.",";
						}
					}
				}	
			}
		}
		$ar=substr($ar,0,(strlen($ar)-1));
		return $ar;
	}
     
    /**
      * download the attach of the mail to localhost
      *
      * @param string $file_path
      * @param string $message
      * @param string $name
      */
     public function down_attach($file_path,$name,$message) {
         if(is_dir($file_path)) {
             $file_open = fopen($file_path.$name,"w");
         } else {
             mkdir($file_path,"0777",true);
         }
         fwrite($file_open,$message);
         fclose($file_open);
     }
 
     
    /**
      * get the body of the message
      *
      * @param string $msg_count
      * @return string
      */
     public function get_body($msg_count) {
         $body = $this->get_part($msg_count, "TEXT/HTML");
         if ($body == '') {
             $body = $this->get_part($msg_count, "TEXT/PLAIN");
         }
         if ($body == ''){
             return '';
         }
         return $this->mail_decode($body);
     }
     
    /**
      * Read the structure of a particular message and fetch a particular
      * section of the body of the message
      *
      * @param string $msg_count
      * @param string $mime_type
      * @param object $structure
      * @param string $part_no
      * @return string|bool
      */
     private function get_part($msg_count, $mime_type, $structure = false, $part_no = false) {
         if(!$structure) {
             $structure = imap_fetchstructure($this->_connect, $msg_count);
         }
         if($structure) {
             if($mime_type == $this->get_mime_type($structure)) {
                 if(!$part_no) {
                     $part_no = "1";
                 }
                 $from_encoding = $structure->parameters[0]->value;
                 $text = imap_fetchbody($this->_connect, $msg_count, $part_no);
                 if($structure->encoding == 3) {
                     $text =  imap_base64($text);
                 } else if($structure->encoding == 4) {
                     $text =  imap_qprint($text);
                 }
                 $text = mb_convert_encoding($text,'utf-8',$from_encoding);
                 return $text;
             }
             if($structure->type == 1) {
                 while(list($index, $sub_structure) = each($structure->parts)) {
                     if($part_no) {
                         $prefix = $part_no . '.';
                     }
                     $data = $this->get_part($msg_count, $mime_type, $sub_structure, $prefix . ($index + 1));
                     if($data){
                         return $data;
                     }
                 }
             }
         }
         return false;
     }
     
    /**
      * get the subtype and type of the message structure
      *
      * @param object $structure
      */
     private function get_mime_type($structure) {
         $mime_type = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER");
         if($structure->subtype) {
             return $mime_type[(int) $structure->type] . '/' . $structure->subtype;
         }
         return "TEXT/PLAIN";
     }
     
    /**
      * put the message from unread to read
      *
      * @param string $msg_count
      * @return bool
      */
     public function mail_read($msg_count) {
         $status = imap_setflag_full($this->_connect, $msg_count , "//Seen");
         return $status;
     }
     
    /**
      * Close an IMAP stream
      */
     public    function close_mail() {
         imap_close($this->_connect,CL_EXPUNGE);
     }

	 function contact_conv($contact){
		foreach($contact as $vo) {
			if (isset($vo -> personal)) {
				$tmp = $tmp.$this->mail_decode($vo -> personal)."|".$vo -> mailbox . '@' . $vo -> host.';';
			} else {
				$tmp = $tmp.$this->mail_decode($vo -> mailbox)."|".$vo -> mailbox . '@' . $vo -> host.';';
			} 
			return $tmp;
		}
	}     
}
 
?>
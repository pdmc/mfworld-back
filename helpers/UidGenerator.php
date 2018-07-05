<?php
/**
 * author     : Jett <jett@139.com>
 * createTime : 2017/6/9 20:30
 * description: 生成16位伪随机数（含字母UID）
 *  
 */

namespace app\helpers;

class UidGenerator
{
    
    const ELECASCKEYLABEL = 'label';
    const ELECASCVALLABEL = 'value';

    public static function create()
    {
        $pr = $_SERVER['REMOTE_ADDR'];
      	$pr_bits = '';
      
        $fp = @fopen('/dev/urandom','r');
        
        if ($fp !== FALSE) {
            $pr_bits .= @fread($fp, 16);
            @fclose($fp);
            
            $pr = $pr_bits;
        }
            
        $str = md5(uniqid($pr, true)); 
        $uid = substr($str, 8 ,16);

        return $uid;
    }
  
  
    public static function createUuid()
    {
        $pr = $_SERVER['REMOTE_ADDR'];
      
      
        $fp = @fopen('/dev/urandom','r');
        
        if ($fp !== FALSE) {
            $pr_bits .= @fread($fp, 16);
            @fclose($fp);
            
            $pr = $pr_bits;
        }
            
        $str = md5(uniqid($pr, true)); 

        return $str;
    }
	
	public static function demo()
	{
		return 'success';
	}
  
  
}
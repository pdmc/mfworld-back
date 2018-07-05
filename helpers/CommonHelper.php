<?php
namespace app\helpers;

class CommonHelper {
	const PSW_KEY = 'easy_app.gy_miiti@app_16.05f';
	
	// 接口输出
	public static function echoResult($errorCode = 0, $errorMsg = 'success', $data = array()) {
		$arr_result = array(
			'errorCode' => $errorCode,
			'errorMsg' => $errorMsg,
			'responseData' => array() 
		);
		if ($data) {
			if (is_array($data) && isset($data['view'])) {
				$arr_result['responseData'] = $data['view'];
			} else {
				$arr_result['responseData'] = $data;
			}
		}
		
		// 调试
		if (isset($_REQUEST['t']) && $_REQUEST['t'] == 1) {
			echo '<pre>';
			// print_r($arr_result);
			print_r(json_decode(json_encode($arr_result),true));
			die();
		}
		if (isset($_REQUEST['callback'])) {
			$callback = $_REQUEST['callback'];
			return $callback . '(' . json_encode($arr_result) . ')';
		} else {
			
			return json_encode($arr_result, JSON_UNESCAPED_UNICODE);
		}
	}

	public static function jsonResult($code, $msg = '', $data = array()) {
		$arr_temp = array();
		$arr_temp['code'] = $code;
		$arr_temp['msg'] = $msg;
		if (is_array($data)) {
			if (!empty($data)) {
				$arr_temp['data'] = $data;
			}
		} else {
			$arr_temp['data'] = $data;
		}
		return json_encode($arr_temp);
	}
	
	// 是否是合法的手机号码
	public static function isMobiles($mobile) {
		if (preg_match("/^((13|14|15|16|17|18)+\d{9})$/", $mobile)) {
			return true;
		} else {
			return false;
		}
	}
	
	//验证时间
	public static function isDate($date) {
	    $patten = "/^\d{4}[\-](0?[1-9]|1[012])[\-](0?[1-9]|[12][0-9]|3[01])(\s+(0?[0-9]|1[0-9]|2[0-3])\:(0?[0-9]|[1-5][0-9])\:(0?[0-9]|[1-5][0-9]))?$/";
	    if (preg_match($patten, $date)) {
	        return true;
	    } else {
	        return false;
	    }
	}
	
	//是否是合法昵称(4-20个字符，支持中英文、数字、"_"或减号)
	public static function isNickName($nickname) {
	    $length = self::utf8Strlen($nickname);
	    if ($length<4 || $length>20) {
	        return false;
	    }
	    $patten = "/^[\x{4e00}-\x{9fa5}A-Za-z0-9_-]+$/u";
	    if (preg_match($patten, $nickname)) {
	        return true;
	    } else {
	        return false;
	    }
	}
	
	//是否是合法的密码(6-16位数字、字母或常用符号，字母区分大小写)
	//需要转义的 *.?+$^[](){}|\/
	public static function isPassWord($password) {
	    $patten = "/^[A-Za-z0-9!@#\$%\^&~,\*\?\.\-\_]{6,16}$/";
	    if (preg_match($patten, $password)) {
	        return true;
	    } else {
	        return false;
	    }
	}
	
	//是否是合法的验证码
	public static function isVerifyCode($password) {
	    $patten = "/^[0-9]{4}$/";
	    if (preg_match($patten, $password)) {
	        return true;
	    } else {
	        return false;
	    }
	}
	
	/**
	 * 统计utf8字符，中文按照2个字计算
	 */
	public static function utf8Strlen($str) {
	    $count = 0;
	    for ($i = 0; $i < strlen($str); $i++) {
	        $value = ord($str[$i]);
	        if ($value > 127) {
	            $count++;
	            if ($value >= 192 && $value <= 223)
	                $i++;
	            elseif ($value >= 224 && $value <= 239)
	            $i = $i + 2;
	            elseif ($value >= 240 && $value <= 247)
	            $i = $i + 3;
	        }
	        $count++;
	    }
	    return $count;
	}
	
	/**
	 * 统计utf8字符，中文按照1个字计算
	 */
	public static function strlenUtf8($str) {
	    $i = 0;
	    $count = 0;
	    $len = strlen($str);
	    while ($i < $len) {
	        $chr = ord($str[$i]);
	        $count++;
	        $i++;
	        if ($i >= $len)
	            break;
	
	        if ($chr & 0x80) {
	            $chr <<= 1;
	            while ($chr & 0x80) {
	                $i++;
	                $chr <<= 1;
	            }
	        }
	    }
	    return $count;
	}
	
	public static function substr($string, $length)
	{
	    $string = strip_tags($string);
	    $string_strlen = mb_strlen($string, 'utf-8');
	    if ($string_strlen>$length) {
	       return  mb_substr($string, 0, $length, 'utf-8').'......';
	    }
	    return $string;
	}
	
	// 使用curl模拟post
	public static function curlPost($url, $data) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		$result = curl_exec($curl);
		curl_close($curl);
		return $result;
	}
	
	// 使用curl模拟get
	public static function curlGet($url) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($curl);
		curl_close($curl);
		return $result;
	}
	
	// 获取用户IP
	public static function getClientIp() {
		static $ip = NULL;
		if ($ip !== NULL)
			return $ip;
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			$pos = array_search('unknown', $arr);
			if (false !== $pos)
				unset($arr[$pos]);
			$ip = trim($arr[0]);
		} elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
			$ip = $_SERVER['HTTP_X_REAL_IP'];
		} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (isset($_SERVER['REMOTE_ADDR'])) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		// IP地址合法验证
		$ip = (false !== ip2long($ip)) ? $ip : '0.0.0.0';
		return $ip;
	}
	
	// 转换时间风格
	public static function stylizeDate($the_date) {
		if (empty($the_date)) {
			return '1秒前';
		}
		$now_time = time();
		$the_time = strtotime($the_date);
		if ($now_time > $the_time) {
			$range = $now_time - $the_time;
			if ($range < 60) {
				return $range . '秒前';
			} elseif ($range < 3600) {
				return floor($range / 60) . '分钟前';
			} elseif ($range < 3600 * 24) {
				return floor($range / 3600) . '小时前';
			} elseif ($range < 3600 * 24 * 7) {
				return floor($range / 86400) . '天前';
			} else {
				if (date('Y') == date('Y', $the_time)) { // 当前年
					return date('m-d', $the_time);
				} else {
					return date('Y-m-d', $the_time);
				}
			}
		}
		return '1秒前';
	}
	
	public static function diffBetweenTwoDays ($day1, $day2,$input_comment)
    {
      $second1 = $day1;
      $second2 = strtotime($day2);
	  
    
     if ($second1 < $second2) {
       $tmp = $second2;
       $second2 = $second1;
       $second1 = $tmp;
       }
     return $input_comment.floor(($second1 - $second2) / 86400).'天';
    }
	
	
	// 格式化时间
	public static function stylizePrice($price) {
		if ($price < 0) {
			return '0万';
		}
		return ($price / 10000) . '万';
	}
	
	//圈子名字
	public static function stylizeGroupName($group_name) {
	    if (empty($group_name)) {
	        return $group_name;
	    }
	    return $group_name . '圈';
	}
	
	//格式化浏览数
	public static function stylizeViewCnt($view_cnt) {
	    return (string)$view_cnt;
	}
	
	//格式化评论数
	public static function stylizeCommentCnt($comment_cnt) {
	    if ($comment_cnt>=1000) {
	        return '999+';
	    }   return (string)$comment_cnt;
	}
	
	//修正获取移动适配版html内容
	public static function fixMobileHtmlContent($content, $to_width = "100%", $to_height="200", $is_lazyload=0) {
	    if (empty($content)) {
	        return $content;
	    }
	    preg_match_all('/<img[\s\t\r\n]+[^>]*src\s*=\s*[\'"\s\t\r\n]+([^>\'"]+?)[\'"\s\t\r\n]+[^>]*[\s\t\r\n]*\/>/i', $content, $out_img, PREG_PATTERN_ORDER);
	    if (isset($out_img[0]) && is_array($out_img[0]) && count($out_img[0]) > 0) {
	        foreach ($out_img[0] as $key => $img) {
	            if($is_lazyload==0){
	               $new_img = isset($out_img[1][$key]) && !empty($out_img[1][$key]) ? '<img class="dz_ImgHtmlObject" src="' . $out_img[1][$key] . '" width="' . $to_width . '" />' : '';
	            }else{
	                $new_img = isset($out_img[1][$key]) && !empty($out_img[1][$key]) ? '<img class="dz_ImgHtmlObject lazyLoad" src="'.YII_URLPRO.YII_DOMAIN_M.'/assets/images/grey.png" data-original="' . $out_img[1][$key] . '" width="' . $to_width . '" />' : '';
	            }
	            $content = str_replace($img, $new_img, $content);
	        }
	    }
	    preg_match_all('/<object[\s\t\r\n]+[^>]*data[\s\t\r\n]*=[\'"\s\t\r\n]+([^>\'"]+?)[\'"\s\t\r\n]+[^>]*[\s\t\r\n]*>[\s\t\r\n\.]*<\/object>/i', $content, $out_video, PREG_PATTERN_ORDER);
	    if (isset($out_video[0]) && is_array($out_video[0]) && count($out_video[0]) > 0) {
	        foreach ($out_video[0] as $key => $video) {
	            $new_video = isset($out_video[1][$key]) && !empty($out_video[1][$key]) ? '<object name="videoObject" data="' . $out_video[1][$key] . '" width="' . $to_width . '" height="' . $to_height . '" type="text/html" wmode="transparent"></object>' : '';
	            $content = str_replace($video, $new_video, $content);
	        }
	    }
	    return $content;
	}
	
	public static function crc32Encode($str) 
	{
	    return sprintf("%u", crc32($str));
	}
	
	public static function stringExplodeToArr($str_ids, $delimiter=',')
	{
	    $ids_arr = array();
	    if(!empty($str_ids) && is_string($str_ids)){
	        $arr = explode(',', $str_ids);
	        if(is_array($arr) && count($arr) >0){
	            foreach($arr as $v){
	                if(is_numeric($v)){
	                    $ids_arr[] = (int)$v;
	                }
	            }
	        }
	    }
	    return $ids_arr;
	}
	
	// 检查图片地址是否符合规则
	public static function isImgUrl($img)
	{
	    if (preg_match("/^http\:\/\/img[0-9]\.wmiweb\.com\/[a-z0-9\/\@]+(\.)(jpg|jpeg|gif|png)$/", $img)) {
	        return true;
	    }
	    return false;
	}
	
	//验证图片真实性并返回
	public static function imgDataArr($img_data)
	{
	    $new_img_data = array();
	    if(is_array($img_data) && count($img_data)>0){
	        foreach ($img_data as $v){
//	            if (self::isImgUrl($v)) {
                   if(!empty($v)) {
                       $new_img_data[] = $v;
                   }
//	            }
	        }
	    }
	    unset($img_data);
	    return $new_img_data;
	}
	
	//图片缩略图原图处理返回
	public static function imgDataThumbArr($img_data, $img_num=9, $width = 180, $height = 180)
	{
	    $img_data_new = array();
	    $img_length = 0;
	    if (is_array($img_data) && count($img_data)>0) {
	        foreach ($img_data as $key=>$value) {
	            if ($img_length==$img_num){
	                continue;
	            }
	            $img_data_new[] = array(
	                'thumb_img_url' => Img::cacheThumb($value, $width, $height),
	                'img_url' => $value,
	            );
	            $img_length++;
	        }
	    }
	    unset($img_data);
	    return $img_data_new;
	}
	
	//月份格式化
	public static function stylizeMonth($month)
	{   
	   $monthArr =  array('一月','二月','三月','四月','五月','六月','七月','八月','九月','十月','十一月','十二月');
	   
	   return isset($monthArr[$month-1]) ? $monthArr[$month-1] : '';
	}
	
	//app加载cass样式文件
	public static function appLoadCssFile($domain_type, $content, $box_class_name = null)
	{
	    if (empty($content)) return  '';
	    $new_content = '';
		$css_file = '';
		switch ($domain_type) {
			case 3:
				$css_file = '/www/site/' . YII_DOMAIN_M . '/assets/disease/css/ios_app_html.css';
				break;
			case 4:
				$css_file = '/www/site/' . YII_DOMAIN_M . '/assets/disease/css/android_app_html.css';
				break;
			default:
				break;
		}
		if (is_file($css_file)) {
			$new_content .= '<style>' . PHP_EOL . file_get_contents($css_file) . PHP_EOL . '</style>' . PHP_EOL;
		}
		$content = str_replace("\t", '', $content);
	    if ($box_class_name) {
	    	$new_content .= '<div class="' . $box_class_name . '" >' . PHP_EOL . $content . PHP_EOL . '</div>';
	    } else {
	    	$new_content .= $content;
	    }
	    return $new_content;
	}
	
	
	//app加载cass样式文件
	public static function appLoadCssFile2($content)
	{
	    if (empty($content)) return  '';
		$domain_type = intval(Yii::app()->request->getParam('domain_type'));
		if($domain_type == 5){
			return $content;
		}
	    $new_content = '';
        $css_file = '/www/site/' . YII_DOMAIN_M . '/assets/disease/css/appstyle.css';
		if (is_file($css_file)) {
			$new_content .= '<style>' . PHP_EOL . file_get_contents($css_file) . PHP_EOL . '</style>' . PHP_EOL;
		}
		$content = str_replace("\t", '', $content);
		$new_content .= '<div>' . PHP_EOL . '<p>' .$content . PHP_EOL .'</p>'.'</div>';
	    return $new_content;
	}
    
	//app加载cass样式文件  医生详情拼上简介字体和样式
	public static function appDoctorLoadCssFile($content)
	{
	    if (empty($content)) return  '';
	    $new_content = '';
        $css_file = '/www/site/' . YII_DOMAIN_M . '/assets/disease/css/appstyle.css';
		$introduc = '<h2>
	            简介<h2/>';
		$domain_type = intval(Yii::app()->request->getParam('domain_type'));
		if($domain_type == 5){
			$new_content = '<div>' . PHP_EOL . '<p>' .$introduc.$content . PHP_EOL .'</p>'.'</div>';
			return $new_content;
		}
		if (is_file($css_file)) {
			$new_content .= '<style>' . PHP_EOL . file_get_contents($css_file) . PHP_EOL . '</style>' . PHP_EOL;
		}
		
		$content = str_replace("\t", '', $content);
		$new_content .= '<div>' . PHP_EOL . '<p>' .$introduc.$content . PHP_EOL .'</p>'.'</div>';
	    return $new_content;
	}
	
	//app加载cass样式文件  基础知识详情拼上简介字体和样式
	public static function appDiseaseLoadCssFile($title,$content,$source)
	{
	    if (empty($content)) return  '';
	    $new_content = '';
        $css_file = '/www/site/' . YII_DOMAIN_M . '/assets/disease/css/appstyle.css';
		$title = "<span style='font-size: 19px;'> $title</span>";
		$source = "$source";
		$domain_type = intval(Yii::app()->request->getParam('domain_type'));
		if($domain_type == 5){
			$new_content = '<div>' . PHP_EOL . '<p>' .$introduc.$content . PHP_EOL .'</p>'.'</div>';
			return $new_content;
		}
		if (is_file($css_file)) {
			$new_content .= '<style>' . PHP_EOL . file_get_contents($css_file) . PHP_EOL . '</style>' . PHP_EOL;
		}
		
		$content = str_replace("\t", '', $content);
		$new_content .= '<div>' . PHP_EOL .$title.'<p style="font-size: 12px; color: rgb(145, 145, 145);" >'.$source.PHP_EOL.$content. PHP_EOL .'</p>'.'</div>';
	    return $new_content;
	}
	
	//\r\n
	public static function fixNl2br($domain_type, $text)
	{
	    if(empty($text)) return $text;
	    
	    return '<p>'.nl2br($text).'</p>';   
	}
	
	//过滤特殊标签，分享内容用
	public static function filterShareStr($str, $length=0)
	{
	    if (empty($str))  return $str;
	    
	    $str = str_replace(array(PHP_EOL, "\r", "\t", '&nbsp;'), ' ', trim(strip_tags($str)));
	    $str = preg_replace('/\s+/', ' ', $str);
	    if (!empty($length)) {
	        $str = !empty($str) ? mb_substr($str, 0, $length, 'utf-8') : $str;
	    }
	    return $str;
	}
	
	//html转成文本
	public static function html2Txt($str)
	{
	    if (empty($str)) return '';
	    
	    return preg_replace("/\n+/", "\n", str_replace(array(PHP_EOL, "\r", "\t"), "\n", trim(strip_tags($str))));
	}
	
	//根据日期计算年龄
	public static function age($YTD)
	{
		$YTD = strtotime($YTD);//int strtotime ( string $time [, int $now ] )
        $year = date('Y', $YTD);
        if(($month = (date('m') - date('m', $YTD))) < 0){
        $year++;
        }else if ($month == 0 && date('d') - date('d', $YTD) < 0){
        $year++;
        }
        return date('Y') - $year;
				
	}
	
	//数组value是null转换成空字符串
	public static function array_null_tostring($arr)
	{		
		foreach ($arr as $key => $value) {
			if($value == null){
				$arr[$key] = '';
			}
		}
		 return $arr;
	}
	
	// this will be used to generate a hash
	public static function password($password) {
		return md5(self::PSW_KEY . '_' . $password);
	}
	
	// this will be used to compare a password against a hash
	public static function checkPassword($password, $hash) {
		$new_hash = self::formatPassword($password);
		return ($hash == $new_hash);
	}
	/**
	 * 检查变量是否设置或为空或空数组
	 * @param $value  
	 * @param $str
	 * @param $str1 
	 * @return 是：返回'',否：返回本身   (注:1个参数 是返回'', 否返回本身; 2个参数 是返回 $str, 否返回本身;)
	 */
	public static function is_set($value, $str = '', $str1 = '')
	{
		if(empty($value)){
			return $str;
		}else{
			return $str1 == '' ? $value : $str1;
		}
	}
	
	//二维数组转化为字符串，中间用,隔开  
	public static function arr_to_str($arr)
	{
		$t = '';
		foreach ($arr as $v){  
			$v = join(",",$v); //可以用implode将一维数组转换为用逗号连接的字符串，join是别名  
			$temp[] = $v;  
		}  
		foreach($temp as $v){  
			$t.=$v.",";  
		}  
		$t=substr($t,0,-1);  //利用字符串截取函数消除最后一个逗号  
		return $t;  
	}  
}

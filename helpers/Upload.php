<?php
#########################################################################
# File Name: Upload.php
# Access: public
# Desc: 
# Author: Loong
# Version 1.0
# Date: 2017-08-27 18:38:07
# Last Modified time: 2017-11-30 23:10:45
#########################################################################
namespace app\helpers;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class Upload
{
	// 用于签名的公钥和私钥
	private static $accessKey = 'jrYn3t5g3kKz2hdWyj0Cdmgmx0G8w-39elytxiJG';
	private static $secretKey = 'xPIo9fBYurHkaJCU_NfASOWa4Ll1I0Mz5y_RwsD5';
	private static $imageDomain = 'http://p0dhd6dx8.bkt.clouddn.com'; //图片
    private static $audioDomain = 'http://p0dhd6dx8.bkt.clouddn.com'; //音频
    private static $videoDomain = 'http://p0dhd6dx8.bkt.clouddn.com'; //音频
    private static $fileDomain = 'http://p0dhd6dx8.bkt.clouddn.com'; //音频


	private static $fsizeMin = 1;
	private static $fsizeLimit = 10;
	private static $deadline = 36000; //上传凭证有效截止时间。
	//$insertOnly = 0;
	//$mimeLimit = 'image/*';//只允许上传图片类型 image/jpeg;image/png表示只允许上传jpg和png类型的图片 !application/json;text/plain表示禁止上传json文本和纯文本。注意最前面的感叹号！


	/**
	 * 获取上传Token
	 */
	public static function getToken($bucket)
	{
 		// 初始化签权对象
	  	$auth = new Auth(self::$accessKey, self::$secretKey);	
	  	$policy = [];
	  	// 生成上传Token
  		$upToken = $auth->uploadToken($bucket,null, self::$deadline, $policy);	
  		return $upToken;
	}
    /**
     * 获取文件上传token
     */
    public static function getFileToken(){
        $bucket = 'file';
        $auth = new Auth(self::$accessKey, self::$secretKey);
        $policy = [];
        $upToken = $auth->uploadToken($bucket, null, self::$deadline, $policy);
        return $upToken;
    }
    /**
     * 获取图片上传token
     */
    public static function getImageToken(){
        $bucket = 'meiyoyo-image';
        $auth = new Auth(self::$accessKey, self::$secretKey);
        $policy = [];
        $upToken = $auth->uploadToken($bucket, null, self::$deadline, $policy);
        return $upToken;
    }
    /**
     * 获取音频上传token
     */
    public static function getAudioToken(){
        $bucket = 'audio';
        $auth = new Auth(self::$accessKey, self::$secretKey);
        //$wmImg = \Qiniu\base64_urlSafeEncode('http://rwxf.qiniudn.com/logo-s.png');
        $pfopOps = "avthumb/m3u8/noDomain/1/segtime/15/vb/440k";
        $notifyUrl = $_SERVER['HTTP_HOST'] . '/common/callback/notify';
        $policy = [
                'persistentPipeline' => 'avvod-pipeline',
                'persistentOps' => $pfopOps,
                'persistentNotifyUrl' => $notifyUrl,
            ];

        $upToken = $auth->uploadToken($bucket, null, self::$deadline, $policy);
        return $upToken;
    }
    /**
     * 获取视频上传token
     */
    public static function getVideoToken(){
        $bucket = 'video';
        $auth = new Auth(self::$accessKey, self::$secretKey);
        //$wmImg = \Qiniu\base64_urlSafeEncode('http://rwxf.qiniudn.com/logo-s.png');
        $pfopOps = "avthumb/m3u8/noDomain/1/segtime/15/vb/440k";
        $notifyUrl = $_SERVER['HTTP_HOST'] . '/common/callback/notify';
        $policy = [
                'persistentPipeline' => 'avvod-pipeline',
                'persistentOps' => $pfopOps,
                'persistentNotifyUrl' => $notifyUrl,
            ];
        $upToken = $auth->uploadToken($bucket, null, self::$deadline, $policy);
        return $upToken;
    }
    /**
     * 获取domain
     */
    public static function getDomain($bucket)
    {
        $domain = [
                'image' => self::$imageDomain,
                'audio' => self::$audioDomain,
                'video' => self::$videoDomain,
                'file' => self::$fileDomain,
            ];
        return $domain[$bucket];
    }
	/**
	 * 上传图片
	 *
	 */
	public static function images($file)
	{
		// 构建 UploadManager 对象
  	$uploadMgr = new UploadManager();
    	// 调用 UploadManager 的 putFile 方法进行文件的上传
    	$token = self::getToken('meiyoyo-image');
    	$key = self::makeName($file['name']);
    	$filePath = $file['tmp_name'];
    	list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
	    if($err !== null) {
	        return $err;
	    } else {
	    	$ret['size'] = filesize($file['tmp_name']);
	    	$info = getimagesize($file['tmp_name']);
	    	$ret['width'] = $info[0];
	    	$ret['height'] = $info[1];
	    	$ret['mime'] = $info['mime'];
	    	$ret['url'] = self::$imageDomain . '/' . $ret['key'];
	    	unset($ret['hash']);
	    	unset($ret['key']);
	       return $ret;
	    }
	    
	}
    /**
     * 上传音频
     *
     */
    public static function audio($file)
    {
        // 构建 UploadManager 对象
        $uploadMgr = new UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传
        $token = self::getToken('audio');
        $key = self::makeName($file['name']);
        $filePath = $file['tmp_name'];
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
        if($err !== null) {
            return $err;
        } else {
            $ret['size'] = filesize($file['tmp_name']);
            $info = getimagesize($file['tmp_name']);
            $ret['mime'] = $info['mime'];
            $ret['url'] = self::$audioDomain . '/' . $ret['key'];
            unset($ret['hash']);
            unset($ret['key']);
           return $ret;
        }
        
    }
    /**
     * 上传视频
     *
     */
    public static function video($file)
    {
        // 构建 UploadManager 对象
        $uploadMgr = new UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传
        $token = self::getToken('video');
        $key = self::makeName($file['name']);
        $filePath = $file['tmp_name'];
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
        if($err !== null) {
            return $err;
        } else {
            $ret['size'] = filesize($file['tmp_name']);
            $info = getimagesize($file['tmp_name']);
            $ret['mime'] = $info['mime'];
            $ret['url'] = self::$videoDomain . '/' . $ret['key'];
            unset($ret['hash']);
            unset($ret['key']);
           return $ret;
        }
        
    }
    /**
     * 上传视频
     *
     */
    public static function file($file)
    {
        // 构建 UploadManager 对象
        $uploadMgr = new UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传
        $token = self::getToken('file');
        $key = self::makeName($file['name']);
        $filePath = $file['tmp_name'];
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
        if($err !== null) {
            return $err;
        } else {
            $ret['size'] = filesize($file['tmp_name']);
            $info = getimagesize($file['tmp_name']);
            $ret['mime'] = $info['mime'];
            $ret['url'] = self::$fileDomain . '/' . $ret['key'];
            unset($ret['hash']);
            unset($ret['key']);
           return $ret;
        }
        
    }
    /**
     * 生成文件名
     * @param   array $_FILES
     * @param   bool $isContent 是否需要读取文件内容
     * @return  读取的文件内容和生成的文件名
     */
    private static function makeName($filePath)
    {
    	$suffix = self::getSuffix($filePath);
        return date('Ymd', time()) . '/' . md5(uniqid(microtime() . mt_rand(1, 5))) .'.'. $suffix;
    }
    /**
     * 获取文件扩展名
     */
    private static function  getSuffix($fileName = '')
    {
        if(!empty($fileName)){
            $suffix = pathinfo($fileName, PATHINFO_EXTENSION);
            return strtolower($suffix);
        }else{
            return '';
        }
    }
    /**
     * 验证图片信息
     */
    private static function verification($file)
    {
        if(isset($file['name'])){
            $suffix = self::getSuffix($file['name']);
            self::$suffix = strtolower($suffix);
        }else{
            throw new Exception("请上传文件", 400);
        }
        if(!in_array(self::$suffix, self::$imageAllowedType)){
            throw new Exception("文件类型违法", 400);
        }else if($file['size'] > self::$maxSize){
            throw new Exception("文件大小不能超过". self::$maxSize, 400);
        }   
        //::setImagesName();//设置文件名 
    }
    
    








}
?>

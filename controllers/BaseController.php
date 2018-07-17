<?php
namespace app\controllers;

use Yii;

use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\rest\Controller; 
use yii\web\HttpException;

/**
 * Base controller
 */
class BaseController extends Controller
{
    // 要不在表单里放一个<input type="hidden" name="_csrf" value="X3VWeTBod2YGMD9IZgU5Nh0AECtzGTIwbic3GFYZGlYxERNNdiA1HA==">
	public $enableCsrfValidation = false; 
	// public $params;//带入参数
	// public $user_id;//用户user_id
	// public $version;//version
	// public $domain_type;//domain_type
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
    
    public function beforeAction($action)
    { 
    	
		// 返回格式未json
    	Yii::$app->response->format = Response::FORMAT_JSON;
		
		//获取头信息
		$headers = Yii::$app->request->headers;
		
     	return true;
    }
    
    //数据返回
	public function responseHelper($arr, $status = '200', $code = '200', $message = '请求成功') 
	{
		Yii::$app->response->statusCode = $status;
		$response['code'] =  $code;
		$response['message'] = $message;
		$response['data']= empty($arr) ? (Object)($arr) : $arr;
		
		return $response;
	} 
	
	public function reponseSuccessful()
	{
		Yii::$app->response->statusCode = 201;
        return NULL;     
	} 
	
	function errToStr($arr){    
		$v2 = '';
        foreach ($arr as $v){    
            $v2 .= $v.","; 
           
        }
            
        return rtrim($v2,',');    
    }

	
}

class successObj
{               
}      
?>

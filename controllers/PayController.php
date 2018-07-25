<?php
namespace app\controllers;
/**
 * 我的
 */
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\HttpException;
use yii\filters\VerbFilter;
use app\helpers\alipay\lib\AlipaySubmit;
use app\helpers\alipay\lib\AlipayNotify;
class PayController extends BaseController
{
	public function actionPay()
	{
        date_default_timezone_set('PRC'); 
        $arr = Yii::$app->request->post();
        // print_r($arr);die;
        // echo $arr['user_id'];die;
        if(empty($arr['user_id'])){
            return $this->responseHelper([], '202', '202', "用户信息不存在");
        }
        $all = 0;
        if(!empty($arr['prop'])){
            foreach ($arr['prop'] as $k => $v) {
            $sql = "select price from ml_prop where id=".$v['id']."";
            $price = Yii::$app->db->createCommand($sql)->queryOne();
            $all += $price['price']*$v['num'];
            }
            $prop = json_encode($arr['prop']);
        // }
        // $prop="";
        $order_id = $this->sp_build_order_no();
        $sql1 = "insert into ml_order (order_id,prop,order_time,user_id,order_status,order_price,pay_type) VALUES ('".$order_id."','".$prop."','".date('Y-m-d H:i:s',time())."','".$arr['user_id']."','1','".$all."','".$arr['type']."')";
        $success= Yii::$app->db->createCommand($sql1)->execute();
        if($success){           
		$html =  $this->generate_pay($order_id, $all);
		return $this->responseHelper($html, '201', '201', "支付成功");
            }
        }
        return $this->responseHelper([], '207', '207', "失败");
	}

	//异步回调
	public function actionNotify()
	{
        date_default_timezone_set('PRC'); 

		$alipayNotify = new AlipayNotify(Yii::$app->params['pc_alipay']);

        $verify_result = $alipayNotify->verifyNotify();

        if($verify_result) {//验证成功
            

            //  本站订单号
            $out_trade_no   = $_POST['out_trade_no'];

            //  支付宝交易号
            $trade_no       = $_POST['trade_no'];

            //  交易状态
            $trade_status   = $_POST['trade_status'];

            //  订单金额
            $total_amount   = $_POST['total_fee'];

            //  实收金额
            $receipt_amount = $_POST['price'];

            //  回调通知的发送时间
            $notify_time    = $_POST['notify_time'];

          if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
            $sql="select * from ml_order where order_id='".$_POST['out_trade_no']."' and  order_status=1";
            $re = Yii::$app->db->createCommand($sql)->queryOne();        
            if(!empty($re)){ 
                $sql = "update ml_order set order_status=2,pay_time='".date('Y-m-d H:i:s',time())."' where order_id ='".$_POST['out_trade_no']."'";
                $success = Yii::$app->db->createCommand($sql)->execute();
                if($success){
                   $prop = json_decode($re['prop'],true);
                   foreach($prop as $k=>$v){
                    $sql2 = "select prop_type,prop_mul from ml_prop where `id`=".$v['id']."";
                     $arr[$v['id']] = Yii::$app->db->createCommand($sql2)->queryOne();
                     $arr[$v['id']]['num'] = $v['num'];
                    }
                    $new=$this->arrangeData($arr);
                    $sql1= "select * from ml_knapsack where user_id=".$re['user_id']."";
                    $res = Yii::$app->db->createCommand($sql1)->queryOne();
                    if(empty($res)){
                            $sql2 = "insert into ml_knapsack (user_id,sku) values (".$re['user_id'].",'".json_encode($new)."')";
                    }else{
                            $old = json_decode($res['sku'],true);
                            foreach($old as $k=>$v){
                                foreach($new as $kk=>$vv){
                                    if($v['id']==$vv['id']){
                                        $old[$k]['num'] = $vv['num']+$v['num'];
                                        unset($new[$kk]);
                                    }
                                }
                            }
                             $update = array_merge($old,$new);
                             $sql2 = "update ml_knapsack set sku='".json_encode($update)."' where user_id=".$re['user_id']."";
                    }
                    Yii::$app->db->createCommand($sql2)->execute();   
                }
            echo "success";		//请不要修改或删除
        }

        } else {

            //验证失败
            echo "fail";	//请不要修改或删除

        	}
    	}

	}

	//同步回调
	public function actionSuccess()
	{
		 $alipayNotify = new AlipayNotify(Yii::$app->params['pc_alipay']);
        $verify_result = $alipayNotify->verifyReturn();
        if($verify_result) {//验证成功
            //商户订单号
            $out_trade_no = htmlspecialchars($_GET['out_trade_no']);

            //收款方id
            $seller_id    = htmlspecialchars($_GET['seller_id']);

            //支付宝交易号
            $trade_no = $_GET['trade_no'];

            //交易状态
            $trade_status = $_GET['trade_status'];

            if($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
                return $this->redirect("https://iontrum.pk4yo.com/good_store");
            } else {
                return $this->redirect("https://iontrum.pk4yo.com/PayFailure");
            }
        }
        else {
            echo "验证失败";
        }
    }
	public function generate_pay($order_id, $money = 0)
    {
        //下面是发送到表单的参数
        $parameter = array(
            "service" => Yii::$app->params['pc_alipay']['service'],//端口方式
            "partner" => '2088721600105567',//合作身份者id
            "seller_id" => '851515466@qq.com',// 收款支付宝账号
            "payment_type" => 1,// 支付类型
            "notify_url" => "http://api.iontrum.pk4yo.com/index.php/pay/notify",// 服务器异步通知页面路径
            "return_url" => "http://api.iontrum.pk4yo.com/index.php/pay/success",// 页面跳转同步通知页面路径 https://app-test.imheixiu.com
            "_input_charset" => trim(strtolower('utf-8')),// 字符编码格式
            "out_trade_no" => $order_id, // 商户网站订单系统中唯一订单号
            "subject" => '道具购买',// 订单名称
            "total_fee" => $money,   // 付款金额             
            // "show_url" => "http://miraila.dev.pk4yo.com/index.php/pay/success",
            "app_pay" => "Y",//启用此参数能唤起钱包APP支付宝
            "body" => 'iontrum-H5支付',// 订单描述 可选
        );
        //建立请求
        $alipaySubmit = new AlipaySubmit(Yii::$app->params['pc_alipay']);
        $html_text = $alipaySubmit->buildRequestForm($parameter, "get", "确认");
        return $html_text;
    }
    //微信支付
    public function actionH5pay()
    {
        date_default_timezone_set('PRC');
        $post = Yii::$app->request->post();
        $HTTP_REFERER =  $_SERVER['HTTP_REFERER'];
        if (empty($HTTP_REFERER)) {
            return $this->responseHelper([], '203', '203', 'fail');
        }
        if(empty($post['user_id'])){
            return $this->responseHelper([], '202', '202', "用户信息不存在");
        }
        $all = 0;
        if(!empty($post['prop'])){
        foreach ($post['prop'] as $k => $v) {
            $sql = "select price from ml_prop where id=".$v['id']."";
            $price = Yii::$app->db->createCommand($sql)->queryOne();
            $all += $price['price']*$v['num'];
        }
        $prop = json_encode($post['prop']);
        // }
        // $prop="";
        $order_id = $this->sp_build_order_no();
        $sql1 = "insert into ml_order (order_id,prop,order_time,user_id,order_status,order_price,pay_type) VALUES ('".$order_id."','".$prop."','".date('Y-m-d H:i:s',time())."','".$post['user_id']."','1','".$all."','".$post['type']."')";
        $success= Yii::$app->db->createCommand($sql1)->execute();
        // $redirect_url="http://h5.miraila.dev.pk4yo.com/PaySuccess";       
        $url='https://api.mch.weixin.qq.com/pay/unifiedorder';  //下单地址  
        $appid='wx865a30f6140b1c65';//公众号appid  
        $appsecret='87bd4380adf06e378ea9aaad0e56712b';  
        $mch_id='1423348902';//商户平台id  
        $nonce_str='qyzf'.rand(100000, 999999);//随机数  
        $out_trade_no=$order_id;  
        $ip=$this->getClientIp();  
        $scene_info='{"h5_info": {"type":"Wap","app_name": "project info","package_name": "wap订单微信支付"}}';
        
        $total_fee_fu =$all*100;
        $trade_type='MWEB';  
        $attach='wap支付';  
        $body='iontrum-H5支付';  
        $notify_url='https://api.iontrum.pk4yo.com/index.php/pay/wnotify';  
        $arr=[  
            'appid'=>$appid,  
            'mch_id'=>$mch_id,  
            'nonce_str'=>$nonce_str,  
            'out_trade_no'=>$out_trade_no,  
            'spbill_create_ip'=>$ip,  
            'scene_info'=>$scene_info,  
//           'openid'=>$openid,  
            'total_fee'=>$total_fee_fu,  
            'trade_type'=>$trade_type, 
            'attach'=>$attach,  
            'body'=>$body,  
            'notify_url'=>$notify_url  
        ];  
        $sign=$this->getSign($arr); 
        //<openid>'.$openid.'</openid>  
       $data='<xml>  
             <appid>'.$appid.'</appid>  
             <attach>'.$attach.'</attach>  
             <body>'.$body.'</body>  
             <mch_id>'.$mch_id.'</mch_id>  
             <nonce_str>'.$nonce_str.'</nonce_str>  
             <notify_url>'.$notify_url.'</notify_url>  
             <out_trade_no>'.$out_trade_no.'</out_trade_no>  
             <spbill_create_ip>'.$ip.'</spbill_create_ip>  
             <total_fee>'.$total_fee_fu.'</total_fee>  
             <trade_type>'.$trade_type.'</trade_type>  
             <scene_info>'.$scene_info.'</scene_info>  
             <sign>'.$sign.'</sign>  
             </xml>';  
        $result=$this->https_request($url,$data);  
       // echo '====================';  
        //var_dump($result);  
        //echo '*******************';  
        //禁止引用外部xml实体  
        libxml_disable_entity_loader(true);  
        $result_info = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        // if(!empty($post['prop'])){
        // $url = $result_info['mweb_url']."&redirect_url=".urlencode($redirect_url);
        // }
        // $url="";
         return $this->responseHelper($result_info['mweb_url'], '201', '201', "操作成功"); 
         }
         return $this->responseHelper([], '207', '207', "失败");  
    }


    public function actionWnotify()
    {
         
        date_default_timezone_set('PRC');
        $xml = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA']:"";
        $data =  $this->xmlToArray($xml);       
        if (!$data) {
            return $this->responseHelper([], '202', '202', "fail");
        }
        $out_trade_no = $data['out_trade_no'];
        $sql="select * from ml_order where order_id='".$out_trade_no."' and  order_status=1";
        $re = Yii::$app->db->createCommand($sql)->queryOne();        
        if(!empty($re)){ 
            $sql = "update ml_order set order_status=2,pay_time='".date('Y-m-d H:i:s',time())."' where order_id ='".$out_trade_no."'";
            $success = Yii::$app->db->createCommand($sql)->execute();
            if($success){
                   $prop = json_decode($re['prop'],true);
                   foreach($prop as $k=>$v){
                    $sql2 = "select prop_type,prop_mul from ml_prop where `id`=".$v['id']."";
                     $arr[$v['id']] = Yii::$app->db->createCommand($sql2)->queryOne();
                     $arr[$v['id']]['num'] = $v['num'];
                    }
                    $new=$this->arrangeData($arr);
                    $sql1= "select * from ml_knapsack where user_id=".$re['user_id']."";
                    $res = Yii::$app->db->createCommand($sql1)->queryOne();
                    if(empty($res)){
                            $sql2 = "insert into ml_knapsack (user_id,sku) values (".$re['user_id'].",'".json_encode($new)."')";
                    }else{
                            $old = json_decode($res['sku'],true);
                            foreach($old as $k=>$v){
                                foreach($new as $kk=>$vv){
                                    if($v['id']==$vv['id']){
                                        $old[$k]['num'] = $vv['num']+$v['num'];
                                        unset($new[$kk]);
                                    }
                                }
                            }
                             $update = array_merge($old,$new);
                             $sql2 = "update ml_knapsack set sku='".json_encode($update)."' where user_id=".$re['user_id']."";
                    }
                    Yii::$app->db->createCommand($sql2)->execute();   
                }
        }
        
    }
	public	function sp_build_order_no()
	{
    //return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
    $uuid = strtoupper(md5(uniqid(rand(), true)));
    // $uuid = substr($charid, 0, 8)
    //     .substr($charid, 8, 4)
    //     .substr($charid,12, 4)
    //     .substr($charid,16, 4)
    //     .substr($charid,20,12);
    return $uuid;
	}
    public function getClientIp()  
    {  
        $cip='unknown';  
        if ($_SERVER['REMOTE_ADDR']){  
            $cip=$_SERVER['REMOTE_ADDR'];  
        }elseif (getenv($_SERVER['REMOTE_ADDR'])){  
            $cip=getenv($_SERVER['REMOTE_ADDR']);  
        }  
        return $cip;  
    }

      public function getSign($Obj)  
    {  
       
        foreach ($Obj as $k => $v)  
        {  
            $Parameters[$k] = $v;  
        }  
        //签名步骤一：按字典序排序参数  
        ksort($Parameters);  
        $String = $this->formatBizQueryParaMap($Parameters, false);  
        //echo '【string1】'.$String.'</br>';  
        //签名步骤二：在string后加入KEY   5a02bd8ecxxxxxxxxxxxxc1aae7d199  这里的秘钥是 商户平台设置的一定要改不然报签名错误  
        $String = $String."&key=PK4yo20173b9a915079d4e71c4ad57ab";  
        //echo "【string2】".$String."</br>";  
        //签名步骤三：MD5加密  
        $String = md5($String);  
        //echo "【string3】 ".$String."</br>";  
        //签名步骤四：所有字符转为大写  
        $result_ = strtoupper($String);  
        //echo "【result】 ".$result_."</br>";  
        return $result_;  
    } 
      public function https_request($url, $data = null) {  
       
        $curl = curl_init ();  
        curl_setopt ( $curl, CURLOPT_URL, $url );  
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );  
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );  
        if (! empty ( $data )) {  
            curl_setopt ( $curl, CURLOPT_POST, 1 );  
            curl_setopt ( $curl, CURLOPT_POSTFIELDS, $data );  
        }  
        curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );  
        $output = curl_exec ( $curl );  
        curl_close ( $curl );  
        return $output;  
    } 

     public function formatBizQueryParaMap($paraMap, $urlencode)  
    {  
//        var_dump($paraMap);//die;  
        $buff = "";  
        ksort($paraMap);  
        foreach ($paraMap as $k => $v)  
        {  
            if($urlencode)  
            {  
                $v = urlencode($v);  
            }  
            //$buff .= strtolower($k) . "=" . $v . "&";  
            $buff .= $k . "=" . $v . "&";  
        }  
        $reqPar='';  
        if (strlen($buff) > 0)  
        {  
            $reqPar = substr($buff, 0, strlen($buff)-1);  
        }  
       
        return $reqPar;  
    } 
    public function xmlToArray($xml){  
    //考虑到xml文档中可能会包含<![CDATA[]]>标签，第三个参数设置为LIBXML_NOCDATA  
    if (file_exists($xml)) {  
        libxml_disable_entity_loader(false);  
        $xml_string = simplexml_load_file($xml,'SimpleXMLElement', LIBXML_NOCDATA);  
    }else{  
        libxml_disable_entity_loader(true);  
        $xml_string = simplexml_load_string($xml,'SimpleXMLElement', LIBXML_NOCDATA);  
    }  
    $result = json_decode(json_encode($xml_string),true);  
    return $result;  
    }

     public function arrangeData($data)
     {
        $singleData = $multipleData = array();
        if(empty($data) && !is_array($data)){
            return false;
        }
        foreach ($data as $key => $value) {
            if(isset($value['prop_type']) && $value['prop_type'] == 1){
                $singleData[$key] = array(
                        'id'    =>  $key,
                        'num'   =>  $value['num']
                    );
            }
            if(isset($value['prop_type']) && $value['prop_type'] == 2){
                $jsonData = json_decode($value['prop_mul'], true);
                if(count($jsonData) == 1){
                    $multipleData[@array_pop(array_keys($jsonData))] = array(
                            'id'    =>  @array_pop(array_keys($jsonData)),
                            'num'   =>  @array_pop(array_values($jsonData))*$value['num']
                        );
                }else{
                    foreach ($jsonData as $jsonKey => $jsonValue) {
                        $arr = array(
                                'id'    =>  $jsonKey,
                                'num'   =>  (int)$jsonValue*(int)$value['num']
                            );
                        if(array_key_exists($jsonKey, $multipleData)){
                            $multipleData[$jsonKey]['num'] = (int)$multipleData[$jsonKey]['num']+(int)$arr['num'];
                        }else{
                            $multipleData[$jsonKey] = $arr;
                        }
                    }
                }
            }
        }
        foreach ($multipleData as $key => $value) {
            if(array_key_exists($value['id'], $singleData)){
                $multipleData[$key]['num'] = (int)$value['num']+(int)$singleData[$value['id']]['num'];
                unset($singleData[$value['id']]);
            }
        }
        return array_merge(array_values($multipleData), array_values($singleData));
    }
    
}

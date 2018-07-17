<?php
namespace app\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\HttpException;
use yii\filters\VerbFilter;
use app\helpers\CurlHelper;

class GuessController extends BaseController
{
	public function actionList()
	{
		date_default_timezone_set("Asia/Shanghai");
		$get = Yii::$app->request->get();
		if (empty($get['user_id'])) {
            return $this->responseHelper([], '201', '201', '用户信息不存在');
      	} 
		$sql = "select * from {{%guessing}}";
		$arr = Yii::$app->db->createCommand($sql)->queryAll();
		if(!empty($arr)){
			foreach($arr as $k=>$v){
			if(time()>$v['close_time'] && $v['status']==1){
				$sql1 = "UPDATE {{%guessing}} SET `status` = 2 WHERE `id` = " . $v['id'];
				\Yii::$app->db->createCommand($sql1)->execute();
			}
		}
		$sql2 = "SELECT * FROM {{%guessing}} WHERE `status` !=0 AND IF(`status`=3,(unix_timestamp(NOW())-prize_time)<=7200,'1=1') ORDER BY `status` ASC,close_time ASC,sort ASC";
		$res = Yii::$app->db->createCommand($sql2)->queryAll();
		foreach ($res as $key => $value) {
				$res[$key]['support1'] = @round(($value['option_one_total'] / $value['option_all_total'])*100);
				$res[$key]['support2'] = @round(($value['option_two_total'] / $value['option_all_total'])*100);
				if($res[$key]['support1']==0 && $res[$key]['support2']==0){
					$res[$key]['support1']=$res[$key]['support1'];
					$res[$key]['support2']=$res[$key]['support2'];
				}else{
						if($res[$key]['support1']!=0){
							$res[$key]['support2'] = 100-$res[$key]['support1'];
						}else if($res[$key]['support2']!=0){
							$res[$key]['support1'] = 100-$res[$key]['support2'];
						}
				}
		}
		if(!isset($get['id'])){
			$sql3 = "select id,name from {{%category}} where pid>=47";
			$cate = Yii::$app->db->createCommand($sql3)->queryAll();
			$all=['id'=>0,'name'=>'全部'];
			array_unshift($cate,$all); 
			$res['cate'] = $cate;
		}	
		$now = strtotime(date('Y-m-d H:i:s',time()));
		$res['now']= $now;
		return $this->responseHelper($res, '200', '200', "success");
	}		
	}

	 //查看用户彩钻
	 public function actionMax()
	 {
	 	$ar = Yii::$app->request->post();
	 	$user_id = $ar['user_id'];
	 	$id=$ar['id'];
	 	$sql = "select color from {{%user}} where user_id=$user_id";
	 	$arr = Yii::$app->db->createCommand($sql)->queryOne();
	 	$sql1= "select max,size from {{%guessing}} where `id`=$id";
	 	$res = Yii::$app->db->createCommand($sql1)->queryOne();
	 	if($res['max']!=0){
	 		$color = $res['max'];
	 		if($arr['color']>=$color){
	 			$colors=$color;
	 		}else{
	 			$colors = sprintf("%.1f",substr(sprintf("%.3f", $arr['color']), 0, -2));
	 		}
	 	}else
	 	{
	 		$colors = sprintf("%.1f",substr(sprintf("%.3f", $arr['color']), 0, -2)); 
	 	}
	 	$res['color'] = $colors;
	 	return $this->responseHelper($res, '200', '200', "success");
	 }


	 //用户点击下注
	 public function actionBets()
	 {
	 	$arr = Yii::$app->request->post();
	 	// print_r($arr);die;
	 	$id = $arr['id'];
	 	$user_id = $arr['user_id'];
	 	$correct = $arr['correct'];
	 	$color = $arr['color'];
	 	$type = $arr['type'];
	 	$sql = " select * from {{%guessing_log}} where user_id=$user_id and guessing_id = $id";
	 	$re = Yii::$app->db->createCommand($sql)->queryAll();
	 	$sql4 = "select * from {{%guessing}} where id=$id";
	 	$answer = Yii::$app->db->createCommand($sql4)->queryOne();
	 	if(count($re)>=3){
	 		return $this->responseHelper([], '202', '202', "超过次数");
	 	}
	 	$sql = "select color,eth_addr from {{%user}} where user_id=$user_id";
	 	$arr = Yii::$app->db->createCommand($sql)->queryOne();
	 	if($arr['color']>=$color){
	 		$post_data['action'] = 'tranDiamond';
    		$post_data['value'] = $color;
    		$post_data['fromuser'] = $arr['eth_addr'];
    		$post_data['touser'] = Yii::$app->params['eth'];
    		$res=CurlHelper::curl_post('',$post_data);
    		$json = json_decode($res,true);
			if($json['code']==200){
				if($correct == $answer['option_one']){
	 			$sql5 = "update {{%guessing}} set option_one_total=(option_one_total+$color),option_all_total = (option_all_total+$color) where id =$id";
	 		}
	 		if($correct == $answer['option_two']){
	 			$sql5 = "update {{%guessing}} set option_two_total=(option_two_total+$color),option_all_total = (option_all_total+$color) where id =$id";
	 		}
	 		$update = Yii::$app->db->createCommand($sql5)->execute();
	 		$after = bcsub($arr['color'],$color,5);
	 		$sql1 = "INSERT INTO `{{%color_log}}` (`user_id`, `type`, `value`,`before`,`after`,  `create_time`,`is_true`) VALUES ('".$user_id."', '".$type."', '".$color."','".$arr['color']."','".$after."', '".time()."','2')";
	 		$insert = Yii::$app->db->createCommand($sql1)->execute();
	 		$sql2 = "update {{%user}} set color=(color-$color) where user_id = $user_id";
	 		$update1=Yii::$app->db->createCommand($sql2)->execute();
	 		$sql3 = "INSERT INTO `{{%guessing_log}}` (`guessing_id`, `user_id`, `correct`,`color_value`,  `create_time`) VALUES ('".$id."', '".$user_id."', '".$correct."','".$color."', '".time()."')";
	 		$insert1=Yii::$app->db->createCommand($sql3)->execute();
	 			if($update && $insert && $update1 && $insert1){
	 				$sql6 = "select * from {{%guessing}} where id =$id";
	 				$result = Yii::$app->db->createCommand($sql6)->queryOne();
					$result['support1'] = @round(($result['option_one_total'] / $result['option_all_total'])*100);
					$result['support2'] = @round(($result['option_two_total'] / $result['option_all_total'])*100);
					if($result['support1']==0 && $result['support2']==0){
						return $this->responseHelper($result, '200', '200', "下注成功");
					}else{
						if($result['support1']!=0){
							$result['support2'] = 100-$result['support1'];
						}else if($result['support2']!=0){
							$result['support1'] = 100-$result['support2'];
						}
						return $this->responseHelper($result, '200', '200', "下注成功"); 
					}
	 				
	 			}
			}else{
				return $this->responseHelper([], '203', '203', "操作失败");	 		

			}
	 	}else
	 	{
	 		return $this->responseHelper([], '201', '201', "余额不足");
	 	}
	}

	//竞猜记录
	//
	public function actionGuesslog(){
		date_default_timezone_set("Asia/Shanghai");
      		$arr = Yii::$app->request->get();
	    
		$sql = " select * from {{%guessing_log}} where user_id=".$arr['user_id']."";
	 	$re = Yii::$app->db->createCommand($sql)->queryAll();
	 	if(!empty($re)){
	 		foreach($re as $k=>$v)
		 	{
		 		$sql1 = "select * from {{%guessing}} where `id`=".$v['guessing_id']."";
		 		$res[] = Yii::$app->db->createCommand($sql1)->queryOne();
		 		foreach($res as $kk=>$vv)
		 		{
		 			if(time()>$vv['close_time'] && $vv['status']==1){
					$sql2 = "UPDATE {{%guessing}} SET `status` = 2 WHERE `id` = " . $vv['id'];
					\Yii::$app->db->createCommand($sql2)->execute();
					}
					if($vv['id']==$v['guessing_id']){
						$re[$k]['guess']=$vv;
						$re[$k]['guess']['color_value']=$v['color_value'];
						$re[$k]['guess']['color_win'] = $v['color_win'];
						$re[$k]['guess']['result']= $v['result'];
					}
		 		}
		 	}
		 	foreach($re as $k=>$v){
		 		$status[$k]=$v['guess']['status'];
		 		$time[$k] = $v['guess']['close_time'];
		 	}
		 	array_multisort($status, SORT_NUMERIC, SORT_ASC,$time,SORT_NUMERIC,SORT_ASC,$re);
		 	$re['now']=strtotime(date('Y-m-d H:i:s',time()));
		 	
		 	return $this->responseHelper($re, '200', '200', "success");
	 	}
	 	
	}

	public function actionConduct()
	{
		date_default_timezone_set("Asia/Shanghai");
      	$arr = Yii::$app->request->get();
	    $sql = " select * from {{%guessing_log}} where user_id=".$arr['user_id']."";
	 	$result = Yii::$app->db->createCommand($sql)->queryAll();
	 	if(!empty($result)){
	 		foreach($result as $k=>$v)
		 	{
		 		$sql1 = "select * from {{%guessing}} where `id`=".$v['guessing_id']." and `status`=1";
		 		$res = Yii::$app->db->createCommand($sql1)->queryAll();
		 		if(!empty($res)){
		 			foreach($res as $kk=>$vv)
		 			{
		 				if(time()>$vv['close_time'] && $vv['status']==1){
						$sql2 = "UPDATE {{%guessing}} SET `status` = 2 WHERE `id` = " . $vv['id'];
						\Yii::$app->db->createCommand($sql2)->execute();
						}
						if($vv['id']==$v['guessing_id']){
							$result[$k]['guess']=$vv;
						}
		 			}
		 		}
		 	}
		 	foreach($result as $k=>$v){
		 		if(!isset($v['guess'])){
		 			unset($result[$k]);
		 		}
		 	}
		 	$results=array_values($result);
			if(!empty($results)){
			
		 	foreach($results as $k=>$v)
		 	{
		 		$time[$k] = $v['guess']['close_time'];
		 	}
		 	array_multisort($time,SORT_NUMERIC,SORT_ASC,$results);
		 $results['now']=strtotime(date('Y-m-d H:i:s',time()));
		}
		 return $this->responseHelper($results, '200', '200', "success");
	 	}
	}


	public function actionResult()
	{
		$arr = Yii::$app->request->get(); 
	    $sql = " select * from {{%guessing_log}} where user_id=".$arr['user_id']." and result=".$arr['status']."";
	 	$result = Yii::$app->db->createCommand($sql)->queryAll();
	 	if(!empty($result)){
	 		foreach($result as $k=>$v){
	 			$sql1 = "select * from {{%guessing}} where `id`=".$v['guessing_id']."";
		 		$res = Yii::$app->db->createCommand($sql1)->queryAll();
		 			foreach($res as $kk=>$vv)
		 			{
						if($vv['id']==$v['guessing_id']){
							$result[$k]['guess']=$vv;
							$result[$k]['guess']['color_value']=$v['color_value'];
							$result[$k]['guess']['color_win'] = $v['color_win'];
							$result[$k]['guess']['result']= $v['result'];
						}
		 			}
	 		}
	 		foreach($result as $k=>$v)
		 	{
		 		$time[$k] = $v['guess']['close_time'];
		 	}
		 	array_multisort($time,SORT_NUMERIC,SORT_ASC,$result);
	 	return $this->responseHelper($result, '200', '200', "success");
	 		
	 	}
	}
  
	public function actionCate()
	{
		date_default_timezone_set("Asia/Shanghai");
		$get = Yii::$app->request->get(); 
		$sql = "select * from {{%guessing}} where cate_id=".$get['id']."";
		$arr = Yii::$app->db->createCommand($sql)->queryAll();
		if(!empty($arr)){
			foreach($arr as $k=>$v){
			if(time()>$v['close_time'] && $v['status']==1){
				$sql1 = "UPDATE {{%guessing}} SET `status` = 2 WHERE `id` = " . $v['id'];
				\Yii::$app->db->createCommand($sql1)->execute();
			}
		}
		$sql2 = "SELECT * FROM {{%guessing}} WHERE `status` !=0 AND `cate_id`=".$get['id']." AND IF(`status`=3,(unix_timestamp(NOW())-prize_time)<=7200,'1=1') ORDER BY `status` ASC,close_time ASC,sort ASC";
		$res = Yii::$app->db->createCommand($sql2)->queryAll();
		foreach ($res as $key => $value) {

			$res[$key]['support1'] = @round(($value['option_one_total'] / $value['option_all_total'])*100);
                                $res[$key]['support2'] = @round(($value['option_two_total'] / $value['option_all_total'])*100);
                                if($res[$key]['support1']==0 && $res[$key]['support2']==0){
                                        $res[$key]['support1']=$res[$key]['support1'];
                                        $res[$key]['support2']=$res[$key]['support2'];
                                }else{
                                                if($res[$key]['support1']!=0){
                                                        $res[$key]['support2'] = 100-$res[$key]['support1'];
                                                }else if($res[$key]['support2']!=0){
                                                        $res[$key]['support1'] = 100-$res[$key]['support2'];
                                                }
                                }

		}	
		$now = strtotime(date('Y-m-d H:i:s',time()));
		$res['now']= $now;
		return $this->responseHelper($res, '200', '200', "success"); 
	}

	}
}

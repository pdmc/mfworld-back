<?php
namespace app\controllers;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\HttpException;
use yii\filters\VerbFilter;

class PropController extends BaseController
{
	//道具商店列表
	public function actionList()
	{
		$sql = "select * from ml_prop";   
		$re = Yii::$app->db->createCommand($sql)->queryAll();
		return $this->responseHelper($re, '203', '203', 'success');
	}


	//用户背包
	public function actionGoods()
	{
		$get = Yii::$app->request->get();
		if(empty($get['user_id'])){
			return $this->responseHelper([], '203', '203', 'fail');
		}
		$sql = "select * from ml_knapsack where user_id=".$get['user_id']."";
		$re = Yii::$app->db->createCommand($sql)->queryOne();
		$arr=[];
		if(!empty($re)){
			if(!empty($re['sku'])){
				$prop = json_decode($re['sku'],true);
				foreach($prop as $k=>$v)
				{
					 $sql2 = "select * from ml_prop where `id`=".$v['id']."";
					 $arr[] = Yii::$app->db->createCommand($sql2)->queryOne();
					 $arr[$k]['num'] = $v['num'];
				}
				return $this->responseHelper($arr, '201', '201', 'success');
			}
			// return $this->responseHelper([], '202', '202', 'success');
		
		}

		return $this->responseHelper([], '202', '202', 'empty');
		// print_r($arr);die;
	}



	//道具使用
	public function actionUse()
	{
		date_default_timezone_set("Asia/Shanghai");
		$arr = Yii::$app->request->post();
		$time = time()-21600;
		// echo $time;die;
		$sql6 = "select propnum from ml_prop where id=".$arr['prop_id']."";
		$physical= Yii::$app->db->createCommand($sql6)->queryOne();
		$sql = "select * from ml_use_history where user_id = ".$arr['user_id']." and use_time>=".$time." and prop_id=".$arr['prop_id']." order by use_time desc";
		// echo $sql;die;
		$re = Yii::$app->db->createCommand($sql)->queryOne();
		// print_r($re);die;
		// $re = [];
		if(empty($re)){
			// echo 111;die;
			$sql3 = "select * from ml_knapsack where user_id=".$arr['user_id']."";	
			$res = Yii::$app->db->createCommand($sql3)->queryOne();
			// print_r($res);die;
			// if($success){
				$prop = json_decode($res['sku'],true);
				// print_r($prop);die;
				if(!empty($prop)){
				$sql2 = "insert into ml_use_history (user_id,prop_id,use_time) values (".$arr['user_id'].",".$arr['prop_id'].",'".time()."')";
				$success= Yii::$app->db->createCommand($sql2)->execute();
				// echo $success;die;
				if($success){
				foreach($prop as $k=>$v){
					if($v['id']==$arr['prop_id']){
						if($v['num']==1){
							unset($prop[$k]);
						}else{
							$prop[$k]['num'] = $v['num']-1;
						}
					}
				}
				$new=[];
					foreach($prop as $k=>$v){
						$new[]=$v;
						}
						// print_r($new);die;				
			$sql4 = "update ml_knapsack set sku='".json_encode($new)."' where user_id=".$arr['user_id']."";
			$re1= Yii::$app->db->createCommand($sql4)->execute();
			// echo $re1;die;
			if($re1){
				// echo 33333;die;
				$sql7 = "select physical from ml_user where user_id=".$arr['user_id']."";
				$physicals= Yii::$app->db->createCommand($sql7)->queryOne();
				// print_r($physicals);die;
				if($physicals['physical']<100){
					if($physicals['physical']+$physical['propnum']<100){
						// echo 111;die;
						$sql5 =	"update ml_user set physical=physical+".$physical['propnum']." where user_id=".$arr['user_id']."";
						$re2= Yii::$app->db->createCommand($sql5)->execute();
						if($re2){
						$sql8 = "select physical from ml_user where user_id=".$arr['user_id']."";
						$strength= Yii::$app->db->createCommand($sql8)->queryOne();
						return $this->responseHelper($strength, '201', '201', 'success');
						}
						}else{
							$sql9 ="update ml_user set physical=100 where user_id=".$arr['user_id']."";
							Yii::$app->db->createCommand($sql9)->execute();
							$sql10 = "select physical from ml_user where user_id=".$arr['user_id']."";
							$strength= Yii::$app->db->createCommand($sql10)->queryOne();
						return $this->responseHelper($strength, '201', '201', 'success');
						}
					}else {
						$strength['physical']=100;
						// print_r($strength);die;
						return $this->responseHelper($strength, '201', '201', 'success');
					}
				}
			}
		}
		}else{
			// echo 2222;die;
			return $this->responseHelper([], '202', '202', 'fail');
		}

	}
}
// }
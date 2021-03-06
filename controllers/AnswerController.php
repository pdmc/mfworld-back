<?php
namespace app\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\HttpException;
use yii\filters\VerbFilter;
use app\helpers\CurlHelper;

class AnswerController extends BaseController
{

    //用户体力每6分钟加1点体力
    public function actionAdd()
    {
        $sql = "select * from ml_user";
        $re = Yii::$app->db->createCommand($sql)->queryAll();
        if (!empty($re)) {
            foreach ($re as $k => $v) {
                if ($v['physical'] < 100) {
                    $sql1 = "update ml_user set `physical` = (`physical`+1) where user_id = '" . $v['user_id'] . "'";
                    Yii::$app->db->createCommand($sql1)->execute();
                }
            }
        }
    }

    public function actionAchievement()
    {
        $get = Yii::$app->request->get();
        // print_r($arr);die;
        if (empty($get['user_id'])) {
            return $this->responseHelper([], '201', '201', 'fail');
        }
        $sql1 = "select distinct (a.problem_cate_id),(SELECT COUNT(*) FROM ml_answer_log b WHERE b.user_id='" . $get['user_id'] . "' AND b.problem_cate_id=a.problem_cate_id) as total_num, (SELECT COUNT(*) FROM ml_answer_log c WHERE c.user_id='" . $get['user_id'] . "' AND c.problem_cate_id=a.problem_cate_id AND c.`result`=1) as true_num FROM ml_answer_log a WHERE a.user_id='" . $get['user_id'] . "'";
        // echo $sql1;die;
        $logData = Yii::$app->db->createCommand($sql1)->queryAll();
        // print_r($logData);die;
	$roundSql = "select count(*) as num from ml_answer_round where user_id=" . $get['user_id'] . "";
        $dataAll = Yii::$app->db->createCommand($roundSql)->queryOne();
        $sql2 = "SELECT * FROM ml_category where `status`=1"; 
        $data = Yii::$app->db->createCommand($sql2)->queryAll();
        // print_r($cateData);die;
        $cateData = $this->getRecursionList($data);
        $cateData = $cateData[0]['child'];
       // $res= $this->cateTree($cateData);
       // print_r($res);die;
        foreach ($logData as $key => $value) {
            $logData[$value['problem_cate_id']] = $value;
            unset($logData[$key]);
        }
        // print_r($logData);die;
        
        $sumData = $trueData = array();
        foreach ($cateData as $key => $val) {
            $total_num_arr = $true_num_arr = array();
            foreach ($val['child'] as $k => $v) {            
                // 处理一级
                if (array_key_exists($val['id'], $logData)) {
                    array_push($total_num_arr, (int)$logData[$val['id']]['total_num']);
                    array_push($true_num_arr,  (int)$logData[$val['id']]['true_num']);
                // 处理二级 
                } elseif(array_key_exists($v['id'], $logData)){
                    @array_push($total_num_arr, (int)$logData[$v['id']]['total_num']);
                    @array_push($true_num_arr,  (int)$logData[$v['id']]['true_num']);   
                } else {
                    $cateData[$key]['total_num'] = 0;
                    $cateData[$key]['true_num'] = 0;
                    $cateData[$key]['win_num'] = 0;
                }
            }
            $cateData[$key]['total_num'] = array_sum($total_num_arr);
            $cateData[$key]['true_num'] = array_sum($true_num_arr);
            $cateData[$key]['win_num'] = @sprintf("%.2f", array_sum($true_num_arr)/array_sum($total_num_arr)*100);
            array_push($sumData, array_sum($total_num_arr));
            array_push($trueData, array_sum($true_num_arr));
        }
        // print_r($cateData);die;
        // $sumData = $trueData = array();
        // foreach ($cateData as $key => $val) {
        //     if (array_key_exists($val['id'], $logData)) {
        //         $cateData[$key]['total_num'] = $logData[$val['id']]['total_num'];
        //         $cateData[$key]['true_num'] = $logData[$val['id']]['true_num'];
        //         $cateData[$key]['win_num'] = round($logData[$val['id']]['true_num'] / $logData[$val['id']]['total_num'], 4) * 100;
        //         array_push($sumData, $logData[$val['id']]['total_num']);
        //         array_push($trueData, $logData[$val['id']]['true_num']);
        //     } else {
        //         $cateData[$key]['total_num'] = 0;
        //         $cateData[$key]['true_num'] = 0;
        //         $cateData[$key]['win_num'] = 0;
        //     }
        // }
        // print_r($sumData);die; 
        $arr = Yii::$app->db->createCommand("select * from ml_task where `status` =1")->queryAll();
        $variable = Yii::$app->db->createCommand("select * from ml_answer_round WHERE user_id='" . $get['user_id'] . "' order by create_time DESC")->queryAll();
        // print_r($variable);die;
        $arrCount = count($arr);
        $value = count($variable);
        for ($i = 0; $i < $arrCount; $i++) {
            if ($arr[$i]['type'] == 1) { //连胜任务处理
                $tmparrar = [];
                //获取round结果
                for ($j = 0; $j < $value; $j++) {
                    $tmparrar[] = $variable[$j]['result'];
                }
                $listRes = array_count_values($tmparrar);
                 $istasksuc = 0;
                //判断任务是否完成
                // print_r($tmparrar);
                for ($a = 0; $a <=($value - $arr[$i]['num']); $a++) {
                    $linedata = array_slice($tmparrar, $a, $arr[$i]['num']);
                    // print_r($linedata);
                    $istasksuc = 0;
                    if (!in_array("2", $linedata)) {
                        $istasksuc = true;
                        break;
                    }
                }
                // echo $istasksuc;die;
                 // $istasksuc = 0;
                //根据任务是否完成作处理
                // print_r($istasksuc);die;
                if (!$istasksuc) {
                    //没完成处理
                    $y = 0;
                    for ($x = 0; $x < count($tmparrar); $x++) {

                        if ($tmparrar[$x] == 1) {
                            ++$y;
                        } else {
                            break;
                        }
                    }
                    // echo $y;die;
                    $arr[$i]['winnum'] = $y;
                } else {
                    $arr[$i]['winnum'] = $arr[$i]['num'];
                }
                $arr[$i]['iswin'] = $istasksuc;

            } else {  //总胜任务处理
                $totalWin = 0;
                for ($j = 0; $j < $value; $j++) {
                    if ($variable[$j]['result'] == 1) {
                        ++$totalWin;
                    }
                }
                $arr[$i]['winnum'] = $totalWin; //胜数
                $arr[$i]['iswin'] = $totalWin >= $arr[$i]['num'] ? 1 : 0; //判断任务是否完成
		//if($arr[$i]['iswin']==1){
		 // $arr[$i]['winnum']=$arr[$i]['num'];
		//}
		//$arr[$i]['winnum'] = $totalWin; //胜数
            }
        }
        //print_r($arr);die;
        foreach ($arr as $k => $v) {
            $iswin[$k] = $v['iswin'];
            $num[$k] = $v['num'];
	     $type[$k] = $v['type'];
        }
        //array_multisort($iswin, SORT_NUMERIC, SORT_ASC, $num, SORT_NUMERIC, SORT_ASC, $arr);
	array_multisort($iswin, SORT_NUMERIC, SORT_ASC,$type,SORT_NUMERIC, SORT_ASC, $num, SORT_NUMERIC, SORT_ASC, $arr);
        $list = array(
            'sum' => array_sum($sumData),
	    'all' =>$dataAll['num'],
            'win' => @round(array_sum($trueData) / array_sum($sumData), 4) * 100,
            'task' => $arr,
            'cate' => $cateData,
        );
        // print_r($list);die;
         if(empty($logData)){
            $list=array(
                'task' => $arr,

            );
            return $this->responseHelper($list, '203', '203', 'success');
        }
        return $this->responseHelper($list, '202', '202', 'success');
    }



    //排行
    //
    public function actionRankings()
    {
        $get = Yii::$app->request->get();

        $sql = "select user_id,user_nick,user_sex from ml_user";
        $res = Yii::$app->db->createCommand($sql)->queryAll();
        // print_r($res);die;
        foreach ($res as $k => $v) {
            $sql = "select count(*) as num from ml_answer_round where user_id=" . $v['user_id'] . " and result=1";
            $re = Yii::$app->db->createCommand($sql)->queryOne();
            $res[$k]['num'] = $re['num'];
        }
	foreach($res as $k=>$v)
	{
	  $sql = "select create_time from {{%answer_round}} where user_id=".$v['user_id']." order by create_time DESC  limit 1";
	 $result = Yii::$app->db->createCommand($sql)->queryOne();
	  $res[$k]['time'] = $result['create_time'];
	}
//	print_r($res);die;
        foreach ($res as $k => $v) {
	    if($v['time']==""){
		unset($res[$k]);	
	    }
            //$num[$k] = $v['num'];
	    //$time[$k] = $v['time'];
        }
	$res = array_values($res);
        //print_r($res);die;
	foreach($res as $k=>$v){
	    $num[$k] = $v['num'];
            $time[$k] = $v['time'];
	}
        array_multisort($num, SORT_NUMERIC, SORT_DESC,$time,SORT_NUMERIC,SORT_ASC, $res);
	//print_r($res);die;
        return $this->responseHelper($res, '202', '202', 'success');
    }

    //分享
    public function actionShare()
    {
        $AppId = 'wx865a30f6140b1c65';
        $nonceStr = $this->sp_random_string(10);
        $jsapi_ticket = $this->getApiTicket();
        $timestamp = time();
        $url = $this->getUrl();
        $sign_string = "jsapi_ticket=$jsapi_ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($sign_string);
        $data['appId'] = $AppId;
        $data['timestamp'] = $timestamp;
        $data['nonceStr'] = $nonceStr;
        $data['signature'] = $signature;
        return $this->responseHelper($data, '201', '201', 'success');
    }


    // public function getAccessTokens()
    // {
    // 	return 1111;
    // }

    //开始答题接口
    public function actionList()
    {
        $session = Yii::$app->session;
        $session->open();
        // //每答一轮题消耗30体力
        $arr = Yii::$app->request->post();
        if (empty($arr['user_id'])) {
            return $this->responseHelper([], '209', '209', '用户信息不存在');
        }
        $i = isset($arr['num']) ? (int)$arr['num'] : 1;
        if ($i < 1 || $i > 10) {
            return $this->responseHelper([], '206', '206', '查无此题');
        }
        $sql = "select physical from ml_user where user_id = '" . $arr['user_id'] . "'";
        $re = Yii::$app->db->createCommand($sql)->queryOne();
        $newUserId = "question_" . $arr['user_id'];
        $sql2 = "select value from {{%config}} where `key`='problemConfig' ";
        $config = Yii::$app->db->createCommand($sql2)->queryOne();
        // print_r($config);die;
        $configData=json_decode($config['value'],true);
        // print_r($configData);die;
        // $session->remove($newUserId);die;
        if ($re['physical'] >= $configData['pro_tili']) {

            $data = $session->get($newUserId);
            if (!empty($data)) {
                $all = json_decode($data, true);
                if ($i == 10) {
                    $session->remove($newUserId);
                }
                return $this->responseHelper($all[$i - 1], '202', '202', 'success');
            }
            $cate = "select id from {{%category}} where web_status=1";
            $category = Yii::$app->db->createCommand($cate)->queryAll();
            // print_r($category);die;
            $str="";
            foreach($category as $kk=>$vv)
            {
                $str.=$vv['id'].",";
            }
            $new_str = substr($str,0,-1);
            // echo $new_str;die;
            $sql1 = "select * from ml_problem where cate_id in (".$new_str.") and status=1 order by RAND() limit 10 ";
            // echo $sql1;die;
            $res = Yii::$app->db->createCommand($sql1)->queryAll();
            // print_r($res);die;

            foreach ($res as $key => $value) {
                $resultList = array();
                $error = explode('/|*|/', $value['error']);
                $arr = array_filter($error);
                array_push($arr, $value['correct']);
                foreach ($arr as $k => $v) {
                    $newItem = array(
                        "id" => $k,
                        "name" => $v
                    );
                    $resultList[] = $newItem;
                }
                $res[$key]['xuan'] = json_encode($resultList);
                unset($res[$key]['correct']);
                unset($res[$key]['error']);
            }
            $answer = json_encode($res);
            if ($session->has($newUserId)) {
                $session->remove($newUserId);
            }
            $session->set($newUserId, $answer);
            return $this->responseHelper($res[$i - 1], '202', '202', 'success');
        } else {
            return $this->responseHelper([], '201', '201', '体力不足');
        }

    }

    public function actionTi()
    {
        $arr = Yii::$app->request->get();
        $sql = "select physical from ml_user where user_id = '" . $arr['user_id'] . "'";
        $re = Yii::$app->db->createCommand($sql)->queryOne();
        if($re['physical']<30){
            return $this->responseHelper([], '201', '201', 'fail'); 
        }
        return $this->responseHelper($re, '200', '200', 'success');  
    }

    //判断答案正确与否
    public function actionCorrect()
    {
        // user_id  用户id
        // id 单题id
        // correct, 用户选择答案
        // time 用户选择单题所用时间
        // num 序号id
        // user_nick 用户昵称
        $arr = Yii::$app->request->post();
        if (count($arr) < 6) {
            return $this->responseHelper([], '205', '205', '参数不足');
        }
        $sql = "select correct,cate_id from ml_problem where id = '" . $arr['id'] . "'";
        $re = Yii::$app->db->createCommand($sql)->queryOne();
        $flag = $success = 2;
        $score = $total_score = $reward = 0;
        $str = "error_hit=error_hit+1";
        if ($re['correct'] == $arr['correct']) {
            $flag = 1;
            $score = $arr['time'] * 1;
            $str = "right_hit=right_hit+1";
        }
        $sb['flag'] = $flag;
        $sb['score'] = $score;
        $sb['correct'] = $re['correct'];
        // print_r($data);die;
        $sql1 = "insert into ml_answer_log (user_id,user_nick,problem_id,problem_cate_id,result,create_time) values (" . $arr['user_id'] . ",'" . $arr['user_nick'] . "'," . $arr['id'] . "," . $re['cate_id'] . "," . $flag . "," . time() . ")";
        // echo $sql1;
        $re1 = Yii::$app->db->createCommand($sql1)->execute();
        $sql2 = "update ml_problem set total_hit=total_hit+1,$str where `id` = " . $arr['id'];
        $re2 = Yii::$app->db->createCommand($sql2)->execute();
        if ($re1 && $re2) {
            $session = Yii::$app->session;
            $session->open();
            $newUserId = "answer_" . $arr['user_id'];
            $data = array(array('question_id' => $arr['id'], 'score' => $score));
            // $session->remove($newUserId);die;
            if ($session->has($newUserId)) {
                $sessData = $session->get($newUserId);
                $data = array_merge($sessData, $data);
            }
            $session->set($newUserId, $data);
            // print_r($data);die;
            if ($arr['num'] == 10) {
                // echo 111;die;
                $allData = $session->get($newUserId);
                // print_r($allData);die;
                $problem_all_id = "";
                foreach ($allData as $key => $value) {
                    $problem_all_id .= $value['question_id'] . ",";
                    $total_score += $value['score'];
                }
                $sql7 = "select value from {{%config}} where `key`='problemConfig' ";
                $config = Yii::$app->db->createCommand($sql7)->queryOne();
                $configData=json_decode($config['value'],true);
                $sql = "select eth_addr,physical from {{%user}} where user_id=".$arr['user_id'];
                $jipeng = Yii::$app->db->createCommand($sql)->queryOne();
                if ($total_score >= $configData['pro_error']) {
                    // echo 33333;die;
                    // $sql = "select eth_addr,physical from {{%user}} where user_id=".$arr['user_id'];
                    // $jipeng = Yii::$app->db->createCommand($sql)->queryOne();
                    $success = 1;
                    $post_data['action'] = 'energy';
                    $post_data['value'] = $configData['pro_energy'];
                    $post_data['user'] = $jipeng['eth_addr'];
                    $res=CurlHelper::curl_post('http://172.16.101.167:8282/node',$post_data);
                    $json = json_decode($res,true);
                    // print_r($json);die;
                    if($json['code']==200){
                        $sql6 = "insert into ml_color_log (user_id,type,value,create_time) VALUES (".$arr['user_id'].",'4',".$configData['pro_energy'].",'".time()."')";
                        $insert = Yii::$app->db->createCommand($sql6)->execute();
                        if($insert){
                           $reward = $configData['pro_energy']; 
                        }                       
                    }
                }
                // echo 44444444;die;
                // echo $reward;die;
                $sba['flag'] = $flag;
                $sba['score'] = $total_score;
                $sba['correct'] = $re['correct'];
                $sba['success'] = $success;
                $sql3 = "insert into ml_answer_round (user_id,user_nick,problem_all_id,total_num,total_score,consume_num,get_reward,result,create_time) values (" . $arr['user_id'] . ",'" . $arr['user_nick'] . "','" . substr($problem_all_id, 0, -1) . "',".$configData['pro_num']."," . $total_score . ",".$configData['pro_tili']."," . $reward . "," . $success . "," . time() . ")";
                // -30 user
		      if($jipeng['physical']-30<0){
		          $sql4 = "update ml_user set physical=0,energy=energy+" . $reward . " where user_id =" . $arr['user_id'] . "";
		      }
                $sql4 = "update ml_user set physical=physical-30,energy=energy+" . $reward . " where user_id =" . $arr['user_id'] . "";
                //$transaction = \Yii::$app->db->beginTransaction();   //开始事务
                // try {
                $re3 = Yii::$app->db->createCommand($sql3)->execute();
                $re4 = Yii::$app->db->createCommand($sql4)->execute();
                // $transaction->commit();//提交事务
                if ($re3 && $re4) {
                    $sql10 = "select physical,eth_addr from ml_user where user_id=" . $arr['user_id'] . "";
                    $strength = Yii::$app->db->createCommand($sql10)->queryOne();
                    $sba['physical'] = $strength['physical'];
                    $taskData = Yii::$app->db->createCommand("select * from ml_task where status = 1")->queryAll();
                    $roundData = Yii::$app->db->createCommand("SELECT * FROM ml_answer_round WHERE user_id =" . $arr['user_id'] . " ORDER BY create_time DESC")->queryAll();
                    $roundcount = count($roundData);
                    if (!empty($roundData)) {
                        foreach ($taskData as $k => $v) {
                            if ($v['type'] == 1) {
                                $useRes = [];
                                $useId = [];
                                for ($i = 0; $i < $roundcount; $i++) {
                                    $useId[] = $roundData[$i]['id'];
                                    $useRes[] = $roundData[$i]['result'];
                                }

                                for ($x = 0; $x <= ($roundcount - $v['num']); $x++) {
                                    $linedata = array_slice($useRes, $x, $v['num']);
                                    if (!in_array("2", $linedata)) {
                                        $upuserid = array_slice($useId, $x, $v['num']);
                                        $string = '';
                                        foreach ($upuserid as $vv) {
                                            $string .= ',' . $vv;
                                        }
                                        $string = substr($string, 1);
                                        $allData = Yii::$app->db->createCommand("SELECT id,task_id FROM ml_answer_round WHERE id in(" . $string . ")")->queryAll();
                                        $x = 0;
                                        foreach ($allData as $vvv) {
                                            $tmpstr = ',' . $v['id'] . ',';
                                            if (is_bool(strpos($vvv['task_id'], $tmpstr))) {
                                                Yii::$app->db->createCommand("update ml_answer_round set task_id = '" . $vvv['task_id'] . "," . $v['id'] . ",' WHERE id = " . $vvv['id'])->execute();
                                                ++$x;
                                            }
                                        }
                                        if ($x == $v['num']) {
                                            $post_data['action'] = 'energy';
                                            $post_data['value'] = $v['reward'];
                                            $post_data['user'] = $strength['eth_addr'];
                                            $res=CurlHelper::curl_post('http://172.16.101.167:8282/node',$post_data);
                                            $json = json_decode($res,true);
                                            if($json['code']==200){
                                                $sql12 = "insert into ml_color_log (user_id,type,value,create_time) VALUES (".$arr['user_id'].",'5','".$v['reward']."','".time()."')";
                                                $insert = Yii::$app->db->createCommand($sql12)->execute();
                                                Yii::$app->db->createCommand("update ml_user set energy=energy+" . $v['reward'] . " where user_id =" . $arr['user_id'])->execute();
                                            }                                           
                                        }
                                        break;
                                    }
                                }
                            } else {
                                $totalWin = 0;
                                $totalwinid = array();
                                for ($j = 0; $j < $roundcount; $j++) {
                                    if ($roundData[$j]['result'] == 1) {
                                        ++$totalWin;
                                        $totalwinid[] = $roundData[$j]['id'];
                                    }
                                    if ($totalWin == $v['num']) {
                                        $str = '';
                                        foreach ($totalwinid as $vs) {
                                            $str .= ',' . $vs;
                                        }
                                        $str = substr($str, 1);
                                        $allData = Yii::$app->db->createCommand("SELECT id,task_id FROM ml_answer_round WHERE id in(" . $str . ")")->queryAll();
                                        $x = 0;
                                        foreach ($allData as $vss) {
                                            $tmpstr = ',' . $v['id'] . ',';
                                            if (is_bool(strpos($vss['task_id'], $tmpstr))) {
                                                Yii::$app->db->createCommand("update ml_answer_round set task_id = '" . $vss['task_id'] . "," . $v['id'] . ",' WHERE id = " . $vss['id'])->execute();
                                                ++$x;
                                            }
                                        }
                                        if ($x == $v['num']) {
                                              $post_data['action'] = 'energy';
                                            $post_data['value'] = $v['reward'];
                                            $post_data['user'] = $strength['eth_addr'];
                                            $res=CurlHelper::curl_post('http://172.16.101.167:8282/node',$post_data);
                                            $json = json_decode($res,true);
                                            if($json['code']==200){
                                                $sql13 = "insert into ml_color_log (user_id,type,value,create_time) VALUES (".$arr['user_id'].",'5','".$v['reward']."','".time()."')";
                                                $insert = Yii::$app->db->createCommand($sql13)->execute();
                                                 Yii::$app->db->createCommand("update ml_user set energy=energy+" . $v['reward'] . " where user_id =" . $arr['user_id'])->execute();
                                            } 
                                           
                                        }
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    if ($success == 1) {
                        $session->remove($newUserId);
                        return $this->responseHelper($sba, '206', '206', '本轮答题成功');
                    } else {
                        $session->remove($newUserId);
                        return $this->responseHelper($sba, '207', '207', '本轮答题失败');
                    }
                    // } catch (Exception $e) {
                    //     $transaction->rollBack();//事务回滚
                }
            }
            return $this->responseHelper($sb, '207', '207', 'success');
        }
    }


    public function sp_random_string($len = 6)
    {
        $chars = array(
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
            "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
            "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
            "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
            "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
            "3", "4", "5", "6", "7", "8", "9"
        );
        $charsLen = count($chars) - 1;
        shuffle($chars);    // 将数组打乱
        $output = "";
        for ($i = 0; $i < $len; $i++) {
            $output .= $chars[mt_rand(0, $charsLen)];
        }
        return $output;
    }

    public function getApiTicket()
    {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $need_request = true;
        if (file_exists("jsapi_ticket.json")) {
            $data = json_decode(file_get_contents("jsapi_ticket.json"), true);
            if ($data['expire_time'] > time()) {
                $need_request = false;
            }
        }
        if ($need_request) {
            // echo 2222;die;
            $accessToken = $this->getAccessToken();
            // echo $accessToken;die;
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = json_decode($this->httpGet($url), true);
            // print_r($res);die;
            $ticket = $res['ticket'];
            if ($ticket) {
                $data['expire_time'] = time() + 7000;
                $data['jsapi_ticket'] = $ticket;
                $fp = fopen("jsapi_ticket.json", "w");
                fwrite($fp, json_encode($data));
                fclose($fp);
            }
        } else {
            $ticket = $data['jsapi_ticket'];
        }
        return $ticket;
    }


    public function getAccessToken()
    {
        $AppId = 'wx865a30f6140b1c65';
        $AppSecret = '87bd4380adf06e378ea9aaad0e56712b';
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $AppId . "&secret=" . $AppSecret . "";
        // echo  $url;die;
        $result = $this->httpGet($url);
        $result = json_decode($result, true);
        // print_r($result);die;
        if (isset($result['access_token'])) {
            $accessToken = $result['access_token'];
        } else {
            $accessToken = '';
        }
        return $accessToken;
    }

    public function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }

    public function getUrl()
    {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : $path_info);
        return $sys_protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $relate_url;
    }


    public function getRecursionList($data, $pid = 0, $level = 0) {
        $arr = array();
        foreach ($data as $val) {
            if ($val['pid'] == $pid) {
                $val['level'] = $level;
                $val['child'] = $this->getRecursionList($data, $val['id'], $level+1);
                $arr[] = $val;
            }
        }
        return $arr;
    }

}

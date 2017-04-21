<?php


/**
 * 抓取新榜数据
 * @param $public_id
 * @return array
 */
function get_newrank_data($public_num){

    $url = 'http://www.newrank.cn/public/info/detail.html?account='.$public_num;
    $data = file_get_contents($url);
    preg_match("/var esbclf =([\s\S]*?)var fgkcdg/",$data,$table);
    $table = str_replace("\n","",$table);
    $table = str_replace("\t","",$table);
    $table = str_replace("</font>","",$table);
    $table = preg_replace("'([rn])[s]+'","",$table);
    $table = preg_replace('/&nbsp;/',"",$table);
    $table = str_replace(";","",$table);

    $json = $table[1];
    $data = json_decode($json,true);

    if($data)
    {
        foreach($data['data'] as $value)
        {
            $array[strtotime($value['rank_date'])] = [
                'timestamp' => strtotime($value['rank_date']),
                'article_count' => $value['article_count'],//条数
                'article_clicks_count' => $value['article_clicks_count'],//总阅读数
                'article_clicks_count_top_line' => $value['article_clicks_count_top_line'],//头条阅读数
                'avg_article_clicks_count' => $value['avg_article_clicks_count'],//平均阅读数
                'max_article_clicks_count'=> $value['max_article_clicks_count'],//最大阅读数
                'article_likes_count' => $value['article_likes_count'],
            ];
        }

        for($i=0; $i<7; $i++)
        {
            $timestamp = strtotime($data['lastUpdateTime'])-$i*24*60*60;
            if($array[$timestamp] == null)
            {
                $array[$timestamp] = array(
                    'timestamp' => $timestamp,
                    'article_count' => '0',
                    'article_clicks_count' => '0',
                    'article_clicks_count_top_line' => '0',
                    'avg_article_clicks_count' => '0',
                    'max_article_clicks_count' => '0',
                    'article_likes_count' => '0',
                );
            }
        }
    }
    return $array;
}


/**
 * 获取日榜指数
 * @param $public_id
 * @param $timestamp
 * @return float
 */
function get_day_score($public_id, $timestamp){

    $r1_max = 7 + 1; // 总篇数
    $r2_max = 93477 + 1; // 总阅读数
    $r3_max = 43000 + 1; // 头条阅读
    $r4_max = 18695 + 1; // 平均阅读
    $r5_max = 43000 + 1; // 最高阅读
    $r6_max = 388 + 1; // 总点赞数
    $r7_max = 1+1;	//每日发布次数
    $p1 = 0.05; // 总篇数
    $p2 = 0.35; // 总阅读数
    $p3 = 0.1; // 头条阅读
    $p4 = 0.2; // 平均阅读
    $p5 = 0.1; // 最高阅读
    $p6 = 0.1; // 总点赞数
    $p7 = 0.1; //每日发布次数
    $e = 2.718281828459;
    $arr = D('Wap/Data')->where(array('public_id'=>$public_id,'timestamp'=>$timestamp))->find();
    if($arr['article_clicks_count'] != 0){
        $article_count = $arr['article_count']+1;
        $article_clicks_count = $arr['article_clicks_count']+1;
        $article_clicks_count_top_line = $arr['article_clicks_count_top_line']+1;
        $avg_article_clicks_count = $arr['avg_article_clicks_count']+1;
        $max_article_clicks_count = $arr['max_article_clicks_count']+1;
        $article_likes_count = $arr['article_likes_count']+1;
        $release_times = 1+1;
    }else{
        $article_count = 1;
        $article_clicks_count = 1;
        $article_clicks_count_top_line = 1;
        $avg_article_clicks_count = 1;
        $max_article_clicks_count = 1;
        $article_likes_count = 1;
        $release_times = 1;
    }
    $r1 = log($article_count,$e)*1000/log($r1_max,$e);
    $r2 = log($article_clicks_count,$e)*1000/log($r2_max,$e);
    $r3 = log($article_clicks_count_top_line,$e)*1000/log($r3_max,$e);
    $r4 = log($avg_article_clicks_count,$e)*1000/log($r4_max,$e);
    $r5 = log($max_article_clicks_count,$e)*1000/log($r5_max,$e);
    $r6 = log($article_likes_count,$e)*1000/log($r6_max,$e);
    $r7 = log($release_times,$e)*1000/log($r7_max,$e);
    // 生成指数
    $score = round ( $r1 * $p1 + $r2 * $p2 + $r3 * $p3 + $r4 * $p5 + $r5 * $p5 + $r6 * $p6 +$r7 * $p7 );
    return $score;
}


/**
 * 获取月榜指数
 * @param $public_id
 * @param $timestamp
 * @return float
 */
function get_month_score($public_id, $timestamp){

    $d = date('d',$timestamp);
    $r1_max = 7*$d + 1; // 总篇数
    $r2_max = 93477*$d + 1; // 总阅读数
    $r3_max = 43000*$d + 1; // 头条阅读
    $r4_max = 18695*$d + 1; // 平均阅读
    $r5_max = 43000*$d + 1; // 最高阅读
    $r6_max = 388*$d + 1; // 总点赞数
    $r7_max = $d +1;			//总发布数
    $p1 = 0.05; // 总篇数
    $p2 = 0.35; // 总阅读数
    $p3 = 0.1; // 头条阅读
    $p4 = 0.2; // 平均阅读
    $p5 = 0.1; // 最高阅读
    $p6 = 0.1; // 总点赞数
    $p7=0.1;//每日发布次数
    $e = 2.718281828459;

    $beginTime = mktime(0,0,0,date('m',$timestamp),1,date('Y',$timestamp));//获取本月一日的时间戳
    $where = array(
        'public_id'=>$public_id,
        'timestamp'=>array('BETWEEN',array($beginTime,$timestamp))
    );

    $times = D('Wap/Data')->where($where)->where(['article_count'=>['NEQ'=>0]])->count()?:1;
    $article_count = D('Wap/Data')->where($where)->Sum('article_count');
    $article_clicks_count = D('Wap/Data')->where($where)->Sum('article_clicks_count');
    $article_clicks_count_top_line = D('Wap/Data')->where($where)->Sum('article_clicks_count_top_line');
    $avg_article_clicks_count = D('Wap/Data')->where($where)->Sum('avg_article_clicks_count');
    $max_article_clicks_count = D('Wap/Data')->where($where)->Sum('max_article_clicks_count');
    $article_likes_count = D('Wap/Data')->where($where)->Sum('article_likes_count');
    $avg_article_clicks_count = $article_clicks_count/$times;			//  每月总阅读数/总次数
    $r1 = log($article_count,$e)*1000/log($r1_max,$e);
    $r2 = log($article_clicks_count,$e)*1000/log($r2_max,$e);
    $r3 = log($article_clicks_count_top_line,$e)*1000/log($r3_max,$e);
    $r4 = log($avg_article_clicks_count,$e)*1000/log($r4_max,$e);
    $r5 = log($max_article_clicks_count,$e)*1000/log($r5_max,$e);
    $r6 = log($article_likes_count,$e)*1000/log($r6_max,$e);
    $r7 = log($times,$e)*1000/log($r7_max,$e);
    //计算当月指数
    $score = round($r1*$p1+$r2*$p2+$r3*$p3+$r4*$p5+$r5*$p5+$r6*$p6+$r7*$p7);
    return $score;

}


/**
 * 获取周榜指数
 * @param $public_id
 * @param $timestamp
 * @return float
 */
function get_week_score($public_id,$timestamp){

    $r1_max = 7*7 + 1; // 总篇数
    $r2_max = 93477*7 + 1; // 总阅读数
    $r3_max = 43000*7 + 1; // 头条阅读
    $r4_max = 18695*7 + 1; // 平均阅读
    $r5_max = 43000*7 + 1; // 最高阅读
    $r6_max = 388*7 + 1; // 总点赞数
    $r7_max = 7 +1; //总发布数
    $p1 = 0.05; // 总篇数
    $p2 = 0.35; // 总阅读数
    $p3 = 0.1; // 头条阅读
    $p4 = 0.2; // 平均阅读
    $p5 = 0.1; // 最高阅读
    $p6 = 0.1; // 总点赞数
    $p7 = 0.1; //每日发布次数
    $e = 2.718281828459;
    $beginTime = strtotime('-6 days',$timestamp);
    $where = array(
        'public_id'=>$public_id,
        'timestamp'=>array('BETWEEN',array($beginTime,$timestamp))
    );
    $times = D('Wap/Data')->where($where)->where(['article_count'=>['NEQ'=>0]])->count()?:1;
    $article_count = D('Wap/Data')->where($where)->Sum('article_count');
    $article_clicks_count = D('Wap/Data')->where($where)->Sum('article_clicks_count');
    $article_clicks_count_top_line = D('Wap/Data')->where($where)->Sum('article_clicks_count_top_line');
    $avg_article_clicks_count = D('Wap/Data')->where($where)->Sum('avg_article_clicks_count');
    $max_article_clicks_count = D('Wap/Data')->where($where)->Sum('max_article_clicks_count');
    $article_likes_count = D('Wap/Data')->where($where)->Sum('article_likes_count');

    $avg_article_clicks_count=$article_clicks_count/$times;
    $r1 = log($article_count,$e)*1000/log($r1_max,$e);
    $r2 = log($article_clicks_count,$e)*1000/log($r2_max,$e);
    $r3 = log($article_clicks_count_top_line,$e)*1000/log($r3_max,$e);
    $r4 = log($avg_article_clicks_count,$e)*1000/log($r4_max,$e);
    $r5 = log($max_article_clicks_count,$e)*1000/log($r5_max,$e);
    $r6 = log($article_likes_count,$e)*1000/log($r6_max,$e);
    $r7 = log($times,$e)*1000/log($r7_max,$e);
    //计算当月指数
    $score = round($r1*$p1+$r2*$p2+$r3*$p3+$r4*$p5+$r5*$p5+$r6*$p6+$r7*$p7);
    return $score;
}


function get_fans($public_id,$timestamp){


    $beginTime = $timestamp-24*60*60*31;
//	$a=date("Y-m-d H:i:s",$timestamp);
    $arr1 = M ( "kdgx_wap_public" )->where ( " public_id = '$public_id' && timestamp>=$beginTime && timestamp<=$timestamp " )->select ();
    return M()->getLastSql();
//    $s=0;
//    $clicks=0;
//    foreach($arr1 as $v){
//        if($v['article_count']!=0){
//            $s++;
//            $clicks=$clicks+$v['avg_article_clicks_count'];
//        }
//    }
//    $clicks=$clicks/$s;
//    $people = $clicks / 0.07;
//    $people = round ( $people );
//    return $people;


    $beginTime = $timestamp-24*60*60*31;
    $where = array(
        'public_id'=>$public_id,
        'timestamp'=>array('BETWEEN',array($beginTime,$timestamp))
    );

    $count = D('Wap/Data')->where($where)->where(array('article_count'=>array('NEQ'=>0)))->count()?:1;
    $clicks = D('Wap/Data')->where($where)->Sum('avg_article_clicks_count');
    return M()->getLastSql();
    
    $clicks = $clicks / $count;
    $people = $clicks / 0.07;
    $people = round ( $people );
    return $people;
}

























<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/4/5
 * Time: 18:11
 */

namespace Wap\Controller;

use Think\Controller;

class MessageController extends Controller
{

    public function index()
    {
        $mediaInfo = D('Wap/Public')->field('alias,user_name,nick_name')->where(array('is_newrank'=>0))->select();

        foreach ($mediaInfo as $value){
            $public_num = $value['alias'] ?: $value['user_name'];
            $data = get_newrank_info($public_num);
            if($data)
            {
                D('Wap/Public')->where(array('user_name'=>$value['user_name']))->save(array('is_newrank'=>1));
            }else{
                print_r($value);
            }
        }
    }


    public function test()
    {
        #公众号排名/公众号总数*100*0.5+公众号关注/平台最大关注数*100*0.5
        $array = D('Wap/Public')->field('user_name')->select();

        foreach($array as $value)
        {
            $publicId = $value['user_name'];
            $publicRank = D('Wap/Data')->getRank($publicId);#公众号排名
            $publicNum = count($array);#公众号总数
            $publicSubCOunt = D('Wap/Public')->where(array('user_name'=>$publicId,'state'=>'1'))->count();#公众号关注人数
            $sql = "SELECT `public_id`,
                        COUNT(`user_id`) as count
                    FROM `pocket`.`kdgx_public_subscribe`
                    WHERE `state`= '1'
                    GROUP BY `public_id`
                    ORDER BY COUNT(`user_id`) DESC LIMIT 0,1";
            $re = M()->query($sql);
            $publicMaxSubCount = $re[0]['count'];#平台最大关注数
            $score = $publicRank/$publicNum*100*0.5+$publicSubCOunt/$publicMaxSubCount*100*0.5;
            $data[] = [
                'publicRank'=>$publicRank,
                'publicNum'=>$publicNum,
                'publicSubCOunt'=>$publicSubCOunt,
                'publicMaxSubCount'=>$publicMaxSubCount,
                'score'=>$score,
            ];

        }


        print_r($data);



    }



}
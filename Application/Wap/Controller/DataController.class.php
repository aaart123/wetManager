<?php


namespace Wap\Controller;

use Think\Controller;

class DataController extends Controller
{

    private $timestamp = 0;
    private $publicId;

    public function index()
    {
        ini_set('max_execution_time', 0);
        $mediaInfo = D('Wap/Public')->field('alias,user_name,nick_name')->select();
        foreach ($mediaInfo as $value) {
            $public_num = $value['alias'] ?: $value['user_name'];
            $data = get_newrank_data($public_num);
            if ($data) {
                $datacount = D('Wap/Data')->field('article_count')->where(array('public_id' => $value['user_name'], 'timestamp' => $data['timestamp']))->find();
                if ($datacount ){
                    D('Wap/Data')->where(array('public_id' => $value['user_name'], 'timestamp' => $data['timestamp']))
                        ->save($data);
                    $day_score = get_day_score($value['user_name'], $data['timestamp']);
                    $week_score = get_week_score($value['user_name'], $data['timestamp']);
                    $month_score = get_month_score($value['user_name'], $data['timestamp']);
                    $fans = get_fans($value['user_name'], $data['timestamp']);
                    D('Wap/Data')->where(array('public_id'=>$value['user_name'],'timestamp'=>$data['timestamp']))
                        ->save(array(
                            'day_score'=>$day_score,
                            'week_score'=>$week_score,
                            'month_score'=>$month_score,
                            'estimate_fans'=>$fans,
                            'update_time'=>date('Y/m/d H:i:s'),
                        ));

                }else {
                    $array = array_merge($data, array(
                        'public_id' => $value['user_name'],
                    ));
                    D('Wap/Data')->add($array);
                    $day_score = get_day_score($array['public_id'], $array['timestamp']);
                    $week_score = get_week_score($array['public_id'], $array['timestamp']);
                    $month_score = get_month_score($array['public_id'], $array['timestamp']);
                    $fans = get_fans($array['public_id'], $array['timestamp']);
                    D('Wap/Data')->where(array('public_id' => $array['public_id'], 'timestamp' => $array['timestamp']))
                        ->save(array(
                            'day_score' => $day_score,
                            'week_score' => $week_score,
                            'month_score' => $month_score,
                            'estimate_fans' => $fans,
                            'update_time' => date('Y/m/d H:i:s'),
                        ));

                    //$this->sendDaily($array['public_id'], $array['timestamp']);
                }
            }
        }
    }


    /**
     * 发送日报模板
     * @param $openid
     */
    public function sendDaily($publicId,$timestamp)
    {
        $user = D('Wap/Data')->getLoginPublicUser($publicId);
        foreach ($user as $v){
            $data = D('Wap/Data')->where(array('public_id'=>$publicId,'timestamp'=>$timestamp))->find();
            $nick_name = D('Wap/Public')->where(array('user_name'=>$publicId))->getField('nick_name');
            $num = D('Wap/Data')->getRank($publicId);
            $array = array(
                'openid'=>$v['openid'],
                'url'=>'http://www.koudaidaxue.com/index.php/Wap/index/index',
                'first'=>"你订阅的".date('m月d日',$timestamp)."的新媒快报如下",
                'keyword1'=>"\t【".$nick_name."】\t快报",
                'keyword2'=>"总阅读数：{$data['article_clicks_count']}↑，点赞数：{$data['article_likes_count']}↑，口袋指数：{$data['day_score']}↑，圈内排名：{$num}↑",
                'remark'=>"还为您精选昨日10条圈内动态。\n点击查看快报详情",
            );
            $obj = new \Base\Controller\WetchatApiController();
            $obj->publicId = 'gh_243fe4c4141f';
            $a = $obj->setBulletinTemplate($array);
        }

    }

}
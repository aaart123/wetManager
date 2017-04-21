<?php


namespace Wap\Controller;

use Think\Controller;

class DataController extends Controller
{

    public function index()
    {
        ini_set('max_execution_time', 0);
        $mediaInfo = M('kdgx_wap_public')->field('alias,user_name')->select();
        //$mediaInfo = [['alias'=>'PocketNewMedia','user_name'=>'gh_243fe4c4141f']];
        foreach ($mediaInfo as $value) {
            $public_num = $value['alias'] ?: $value['user_name'];
            $data = get_newrank_data($public_num);
            if ($data) {
                foreach ($data as $v) {
                    if (!D('Data')->where(array('public_id' => $value['user_name'], 'timestamp' => $v['timestamp']))->getfield('article_count')) {
                        $array = array_merge($v, array(
                            'public_id' => $value['user_name'],
                        ));
                        D('Data')->add($array);
                        $day_score = get_day_score($array['public_id'], $array['timestamp']);
                        $week_score = get_week_score($array['public_id'], $array['timestamp']);
                        $month_score = get_month_score($array['public_id'], $array['timestamp']);
                        $fans = get_fans($array['public_id'], $array['timestamp']);
                        D('Data')->where(array('public_id' => $array['public_id'], 'timestamp' => $array['timestamp']))
                            ->save(array('day_score' => $day_score, 'week_score' => $week_score, 'month_score' => $month_score, 'estimate_fans' => $fans));
                    }
                }
            }else{
                $log = "-----------".$value['user_name']."-------".$value['alias']."\r\n";
                file_put_contents('public.log',$log,FILE_APPEND);
                $arr = D('Public')->field('nick_name,alias,user_name')->where(array('user_name'=>$value['user_name']))->find();
                $wechat = new \Base\Controller\WetchatApiController();
                $wechat->publicId = 'gh_243fe4c4141f';
                $array = array(
                    'openid'=>'oyTk8w-KvFXtWHLlgP9U7RSXWIsE',
                    'url'=>'',
                    'first'=>'新榜公众号错误',
                    'keyword1'=>"\n公众号原始ID:{$arr['user_name']}\n公众号名称：{$arr['nick_name']}\n公众微信号：{$arr['alias']}",
                    'keyword2'=>date('Y-m-d H:i'),
                    'remark'=>'',
                );
                $wechat->setSubscribeTemplate($array);

            }
        }
    }


    public function test()
    {
        $public_num = 'GYBSXT';
        $data = get_newrank_data($public_num);
        foreach ($data as $v){
            $fans = get_fans('gh_db45517db611', $v['timestamp']);
            echo $fans.'<br/>';
        }
    }

}
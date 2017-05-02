<?php
namespace Fans\Controller;

use Base\Controller\CommonController;
use Base\Controller\WetchatApiController;

/**
 * 关注/取关事件处理类
 */
class FansController extends CommonController
{
    public function __construct()
    {
        parent:: __construct();
    }

    public function initOpenid($data)
    {
        if (empty($data['openid']) || empty($data['public_id'])) {
            return false;
        }
        $openidModel = M('kddx_user_openid','','connection');
        $where = [
            'openid' => $data['openid'],
            'public_id' => $data['public_id']
        ];
        if ($data['subscribe']) {
            $save['public_id'] = $data['public_id'];
            $save['openid'] = $data['openid'];
            $save['subscribe'] = $data['subscribe'];
            $save['nickname'] = $data['nickname'];
            $save['sex'] = $data['sex'];
            $save['language'] = $data['language'];
            $save['city'] = $data['city'];
            $save['province'] = $data['province'];
            $save['country'] = $data['country'];
            $save['headimgurl'] = $data['headimgurl']; 
            $save['subscribe_time'] = $data['subscribe_time']; 
            $save['unionid'] = $data['unionid']; 
            $save['remark'] = $data['remark'];
            $save['groupid'] = $data['groupid'];             
        }else {
            $save['subscribe'] = $data['subscribe'];
            $save['unsubscribe_time'] = time();
        }
        if ($openidModel->where($where)->find()) {
            $openidModel->where($where)->save($save);
        }elseif($data['subscribe']) {
            $save['uid'] = 0;
            $openidModel->add($save);
        }
    }

    public function initFans($public_id)
    {
        exit('保护状态下不允许执行');
        $wetApi = new WetchatApiController();
        $openids = $wetApi->getOpenidList($public_id);
        $total = $openids['total'];
        $count = $openids['count'];
        $next = $openids['next_openid'];
        $openids = $openids['data']['openid'];
        $infos = [];
        $temp = [];
        foreach ($openids as $key => $openid) {
            $temp[] = [
                'openid' => $openid,
                'lang' => 'zh-CN'];
            $data['user_list'] = $temp;
            if (($key+1)%100==0) {
                $temp = [];
                echo $key.'--->';
                $data = json_encode($data);
                $info = $wetApi->getOpenidInfoList($public_id, $data);
                $info['next_openid'] = $openid;
                foreach ($info['user_info_list'] as $openData) {
                    $openData['public_id'] = $public_id;
                    $this->initOpenid($openData);
                }
                $data = [];
            }
            if ($key==1500) {
                echo "\n".$openid;
                exit;
            }
        }
    }

    public function getAll($public_id)
    {
        $firstRow = (I('get.page', 1) - 1) * 20; 
        $openidModel = M('kddx_user_openid','','connection');
        $where['public_id'] = $public_id;
        $data = $openidModel->where($where)->limit($firstRow, 20)->select();
        return $data;
    }

}
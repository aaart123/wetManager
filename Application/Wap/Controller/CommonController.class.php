<?php

namespace Wap\Controller;

use Think\Controller;

class CommonController extends Controller
{
    protected $redisObj;
 
    public function __construct()
    {
        parent::__construct();
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }

    public function toji()
    {
        // 分享统计接口(详情页;预览页;个人中心;其他)
            // http://www.koudaidaxue.com/index.php/Wap/Common/toji
        $post = I('post.');
        M('kdgx_social_tongji')->where(['type'=>$post['type'],'to'=>$post['to']])->setInc('count', 1);
        echo json_encode([
            'errcode' => 0,
            'errmsg' => '成功'
        ]);
        eixt;
    }

    /**
     * 判断A用户是否关注B
     * @param int A
     * @param int B
     */
    protected function isSubscribute($user_id, $subcribe)
    {
        $where = [
            'user_id' => $user_id,
            'subscribe_user' => $subcribe,
            'subscribe_state' => '1'
        ];
        if (D('Subscribe')->where($where)->find()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取缓存数据
     * @param int
     */
    protected function getRedisCache($key)
    {
        $datas = $this->redis->get($key);
        return unserialize($datas);
    }

    /**
     * 更新缓存数据
     * @param int 
     * @param array
     */
    protected function upRedisCache($key, $datas)
    {
        if (empty($datas)) {
            return false; 
        }
        $datas = serialize($datas);
        return $this->redis->setex($key, 592, $datas);
    }
}
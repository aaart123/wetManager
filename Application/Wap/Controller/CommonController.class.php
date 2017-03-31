<?php

namespace Wap\Controller;

use Think\Controller;

class CommonController extends Controller
{
    public function __construct()
    {
        parent::__construct();
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
}
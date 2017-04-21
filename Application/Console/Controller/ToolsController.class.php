<?php
namespace Console\Controller;


use Think\Controller;

class ToolsController extends Controller
{
    public function a()
    {
        exit;
        $data = D('Wap/Public')->select();
        foreach ($data as $value) {
            $save['timestamp'] = time();
            $save['public_id'] = $value['user_name'];
            $save['public_name'] = $value['nick_name'];
            $save['alias_id'] = $value['alias'];
            M('kdgx_public')->add($save);
            print_r($save);
        }
    }
}
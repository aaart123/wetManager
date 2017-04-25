<?php
namespace Console\Controller;


use Think\Controller;
use Base\Controller\OauthApiController;
use Base\Model\PublicModel;

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

    public function b()
    {
        $data = M('kdgx_plat_authorizer')->where(['authorization_state'=>'1'])->select();
        $oauth = new OauthApiController();
        foreach ($data as $value) {
            // echo $value['authorizer_appid'];
            $oauth->getPublicInfo($value['authorizer_appid']);
        }
    }
}
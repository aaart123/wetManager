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

        $obj = new \Base\Controller\WetchatApiController();
        $obj->publicId = 'gh_243fe4c4141f';
        $token = $obj->getAccessToken();
        $url = 'https://api.weixin.qq.com/datacube/getarticlesummary?access_token=' . $token;
        $json = '{ 
                    "begin_date": "2017-04-23", 
                    "end_date": "2017-04-23"
                }';
        $data = json_decode(https_request($url, $json));
        print_r($data);

    }



}
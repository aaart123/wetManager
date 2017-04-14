<?php

namespace Wap\Controller;

use Think\Controller;
use Wap\Controller\BaseController;
use Wap\Controller\ArtificialController;

class AuthController extends BaseController
{

    private $user_id;
    private $ArtificialActivity;

    public function __construct()
    {
        parent::__construct();
        $this->user_id = session('plat_user_id');
        $this->ArtificialActivity = new ArtificialController();
    }

    public function show()
    {
        $this->display();
    }

    public function push()
    {
        $post = I('post.');
        // 提交人工审核
            // http://www.koudaidaxue.com/index.php/wap/auth/push
            // $post = [
            //     'nick_name' => '公众号名称',
            //     'alias' => '公众号id',
            //     'fans_size' => 1, #粉丝规模
            // ];
        $post['user_id'] = $this->user_id;
        if ($id = $this->ArtificialActivity->create($post)) {
            echo json_encode([
            'errcode' => 0,
            'errmsg' => $id
            ]);
            exit;
        } else {
            echo json_encode([
            'errcode' => 1001,
            'errmsg' => '失败'
            ]);
            exit;
        }
    }

    public function getArtificialList()
    {
        // 管理员获取人工审核列表
            // http://www.koudaidaxue.com/index.php/wap/auth/getArtificialList
        $list = $this->ArtificialActivity->getArtificialList(0, 1);
        echo json_encode([
        'errcode' => 0,
        'errmsg' => $list
        ]);
        exit;
    }

    public function getList()
    {
        // 个人获取人工审核列表
            // http://www.koudaidaxue.com/index.php/wap/auth/getList
        $list = $this->ArtificialActivity->getArtificialList($this->user_id, 0);
        echo json_encode([
        'errcode' => 0,
        'errmsg' => $list
        ]);
        exit;
    }

    public function editArtificial($id)
    {
        $post = I('post.');
        // 修改人工审核
            // http://www.koudaidaxue.com/index.php/wap/auth/editArtificial?id=1
            // $post = [
            //     'nick_name' => '公众号名称',
            //     'alias' => '公众号id',
            //     'fans_size' => 1, #粉丝规模
            // ];
        $where['id'] = $id;
        if ($id = $this->ArtificialActivity->edit($where, $post)) {
            echo json_encode([
            'errcode' => 0,
            'errmsg' => '成功'
            ]);
            exit;
        } else {
            echo json_encode([
            'errcode' => 1001,
            'errmsg' => '失败'
            ]);
            exit;
        }
    }

    public function unAgreed($id)
    {
        // 不通过人工审核
            // http://www.koudaidaxue.com/index.php/wap/auth/unAgreed?id=1
            // $post = [
            //     'reason' => '这是理由'       
            // ];
        if ($id = $this->ArtificialActivity->sendNotive($id, 0)) {
            echo json_encode([
            'errcode' => 0,
            'errmsg' => '成功'
            ]);
            exit;
        } else {
            echo json_encode([
            'errcode' => 1001,
            'errmsg' => '失败'
            ]);
            exit;
        }
    }

    public function validatePublic()
    {
        $post = I('post.');
        // 验证公众号审核
            // http://www.koudaidaxue.com/index.php/wap/auth/validatePublic
            // $post = [
            //     'secret' => 'e3a9405eefc22911aa5dddec4322f323',
            //     'id' => 10
            // ];
        if ($id = $this->ArtificialActivity->validatePublic($post)) {
            echo json_encode([
            'errcode' => 0,
            'errmsg' => '成功'
            ]);
            exit;
        } else {
            echo json_encode([
            'errcode' => 1001,
            'errmsg' => '失败'
            ]);
            exit;
        } 
    }

    public function sendNotive($id)
    {
        // 发送认证通知
            // http://www.koudaidaxue.com/index.php/wap/auth/sendNotive?id=1
        if ($this->ArtificialActivity->sendNotive($id)) {
            echo json_encode([
            'errcode' => 0,
            'errmsg' => '成功'
            ]);
            exit;
        } else {
            echo json_encode([
            'errcode' => 1001,
            'errmsg' => '失败'
            ]);
            exit;
        } 
    }

}
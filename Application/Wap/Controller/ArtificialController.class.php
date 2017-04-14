<?php

namespace Wap\Controller;

use Wap\Controller\CommonController;
use Wap\Model\PublicTempModel;
use Wap\Model\PublicModel;

/**
 * 人工认证管理
 */

class ArtificialController extends CommonController
{
    private $publicTempModel;
    private $publicModel;
    private $admins = ['10007','10002', '10001'];
    public function __construct()
    {
        parent::__construct();
        $this->publicModel = new PublicModel();
        $this->publicTempModel = new PublicTempModel();
    }

    private function templateNotive($openid, $secret, $nick_name, $type)
    {
        $array=[
            'openid'=> $openid,
            'url'=>'http://www.koudaidaxue.com/index.php/Wap/Index/binding',
            'first'=>"认证验证码已发至{$nick_name}后台,请查收后'复制',继续进行认证
                        ",
            'keyword1'=>'公众号人工认证',
            'keyword2'=>'待验证
            ',
            'remark'=>'点此继续认证'
                ];
        if (!$type) {
            $array['first'] = "{$nick_name} 人工认证失败
            ";
            $array['keyword1'] = "原因:{$_POST['reason']}";
            $array['keyword2'] = '未通过
            ';
            $array['remark'] = '点此重新认证';
        }
        $obj = new \Base\Controller\WetchatApiController();
        $obj->publicId = 'gh_243fe4c4141f';
        $obj->setCheckTemplate($array);
    }

    private function manageNotive($openid)
    {
        $array=[
            'openid'=> $openid,
            'url'=>'http://www.koudaidaxue.com/index.php/Wap/Index/admin',
            'first'=> "嘿,小口袋,有人申请人工认证,请尽快审核
                        ",
            'keyword1'=>'新媒圈人工认证提醒',
            'keyword2'=>'待处理
            ',
            'remark'=>'点此进行处理'
        ];
        $obj = new \Base\Controller\WetchatApiController();
        $obj->publicId = 'gh_243fe4c4141f';
        $obj->setCheckTemplate($array);
    }

    public function create($data)
    {
        $temp = get_newrank_media_info($data['alias']);
        $data['qrcode_url'] = "http://open.weixin.qq.com/qr/code/?username={$data['alias']}";
        $data['head_img'] = "";
        $data['user_name'] = empty($temp['user_name']) ? 0 : $temp['user_name'];
        $data['nick_name'] = empty($temp['nick_name']) ? $data['nick_name'] : $temp['nick_name'];
        $data['secret'] = substr(md5(uniqid()),rand(0, 25), 6);
        if ($id = $this->publicTempModel->addData($data)) {
            foreach ($this->admins as $admin) {
                $openid = D('User')->where(array('user_id'=> $admin))->getField('openid');
                $this->manageNotive($openid);
            }
            return $id;
        } else {
            return false;
        }
    }

    public function edit($where, $data)
    {
        if ($this->publicTempModel->editData($where, $data)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 维护手机端公众号信息表
     * @param array
     */
    public function addPublic($data)
    {
        if (empty($data['user_name'])) {
            return false;
        }
        $publicModel = new \Base\Model\PublicModel();
        $where['user_name'] = $data['user_name'];
        if ($auth = $publicModel->getData($where)) {
            $data['nick_name'] = $auth['nick_name'];
            $data['head_img'] = $auth['head_img'];
            $data['alias'] = $auth['alias'];
            $data['user_name'] = $auth['user_name'];
            $data['qrcode_url'] = $auth['qrcode_url'];
        }
        if ($this->publicModel->addData($data)) {
            A('User/PublicUser')->addPublicList($data['user_name'], $data['user_id']);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 验证验证码
     * @param array
     */
    public function validatePublic($where)
    {
        if ($data = $this->publicTempModel->getData($where)) {
            $wh['id'] = $data['id'];
            $save['state'] = 2;
            if ($this->addPublic($data) && $this->publicTempModel->editData($wh, $save)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 发送短信验证通知
     * @param int
     * @param int 1验证消息;0不通过模板
     */
    public function sendNotive($id, $type = 1)
    {
        $where['id'] = $id;
        $type && $where['state'] = ['in', '0,1'];
        if ($data = $this->publicTempModel->relation('user')->getData($where)) {
            if ($type) {
                $alisms = new \Common\Common\Alisms(C('SMS_ACCESS_KEY_ID'), C('SMS_ACCESS_KEY_SECRET'));
                $templateCode = 'SMS_61730003';
                $paramString = '{"publicname":"'.$data['nick_name'].'公众号后台"}';
                $response = $alisms->smsend($data['user']['phone'], $templateCode, $paramString);
            }
            $this->templateNotive($data['user']['openid'], $data['secret'], $data['nick_name'], $type);
            $wh['id'] = $id;
            $save['state'] = 3;
            $type && $save['state'] = 1;
            $this->publicTempModel->editData($wh, $save);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取某人审核列表
     * @param int 用户id
     * @param int 0用户端;1管理员端
     */
    public function getArtificialList($user_id = 0, $type = 0)
    {
        $where = [];
        !empty($user_id) && $where['user_id'] = $user_id;
        $list = $this->publicTempModel->getAll($where, $type);
        return $list;
    }
}

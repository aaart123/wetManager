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
    public function __construct()
    {
        parent::__construct();
        $this->publicModel = new PublicModel();
        $this->publicTempModel = new PublicTempModel();
    }

    private function templateNotive($openid, $secret)
    {
        $array=[
            'openid'=> $openid,
            'url'=>'http://www.koudaidaxue.com/index.php/Wap/index/index',
            'first'=>'您的提交内容已审核
                        ',
            'keyword1'=>$secret.'
            ',
            'keyword2'=>date('m-d H:i').'更新',
            'remark'=>'
点此查看详情'
                ];
        $obj = new \Base\Controller\WetchatApiController();
        $obj->publicId = 'gh_243fe4c4141f';
        $obj->setCheckTemplate($array);
    }

    public function create($data)
    {
        $temp = get_newrank_media_info($data['alias']);
        $data['qrcode_url'] = "http://open.weixin.qq.com/qr/code/?username={$data['alias']}";
        $data['head_img'] = "http://open.weixin.qq.com/qr/code/?username={$data['alias']}";
        $data['user_name'] = isset($temp['user_name']) ? $temp['user_name'] : 0;
        $data['secret'] = md5(uniqid());
        if ($id = $this->publicTempModel->addData($data)) {
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
        if ($this->publicModel->addData($data)) {
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
            if ($this->addPublic($data)) {
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
     */
    public function sendNotive($id)
    {
        $where['id'] = $id;
        if ($data = $this->publicTempModel->relation('user')->getData($where)) {
            $alisms = new \Common\Common\Alisms(C('SMS_ACCESS_KEY_ID'), C('SMS_ACCESS_KEY_SECRET'));
            $templateCode = 'SMS_61730003';
            $paramString = '{"publicname":"'.$data['nick_name'].'"}';
            $response = $alisms->smsend($data['user']['phone'], $templateCode, $paramString);
            $this->templateNotive($data['user']['openid'], $data['secret']);
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
        !$type && $where['state'] = ['neq',3];
        $list = $this->publicTempModel->getAll($where);
        return $list;
    }
}

<?php
namespace Base\Model;

use Base\Model\BaseModel;

/**
 * 粉丝模型
 */

class UserOpenidModel extends BaseModel
{
    protected $trueTableName = 'kdgx_user_openid';

    protected $_map = [
        'publicId' => 'public_id',
        'nickName' => 'nickname',
        'headImgUrl' => 'headimgurl',
        'groupId' => 'groupid',
        'tagIdList' => 'tagid_list',
        'subscribeTime' => 'subscribe_time'
    ];

    public function __construct(){
        parent::__construct();
    }

    /**
     * 添加数据
     * @param array 数据
     */
    public function addData($data)
    {
        !$this->create($data) && E($this->getError());
        return $this->add();
    }

    /**
     * 修改数据
     * @param array 数据
     */
    public function editData($data)
    {
        !$this->create($data) && E($this->getError());
        return $this->save();
    }

    /**
     * 通过openid修改数据
     * @param string 修改数据
     */
    public function editOpenidData($data)
    {
        if(empty($data['openid'])){
            return false;
        }
        $where = [
            'openid' => $data['openid']
        ];
        !$this->create($data) && E($this->getError());
        return $this->where($where)->save();
    }

    /**
     * 通过openid获取数据
     * @param string openId
     */
    public function getOpenidData($openId)
    {
        $where = [
            'openid' => $openId
        ];
        return $this->where($where)->find();
    }

    /**
     * 通过uid获取数据
     * @param int uid
     */
    public function getUidData($uid)
    {
        $where = [
            'uid' => $uid
        ];
        return $this->where($where)->find();
    }

    /**
     * 获取公众号的粉丝
     * @param str 公众号id
     * @return arr 粉丝数组
     */
    public function getPublicData($publicId)
    {
        $where = [
            'public_id' => $publicId
        ];
        return $this->where($where)->select();
    }

}
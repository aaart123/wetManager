<?php
namespace User\Model;

use Base\Model\AppModel;

/**
 * 用户模型
 */
class UserModel extends AppModel
{
    protected $trueTableName = 'kdgx_plat_user';

    protected $_map = [
        'userId' => 'user_id',
        'limitList' => 'limit_list'
    ];


    public function __construct()
    {
        parent:: __construct();
    }

    /**
     * 添加数据
     * @param array 添加数据
     */
    public function addData($data)
    {
        !$this->create($data) && E($this->getError());
        return $this->add();
    }

    /**
     * 检查用户登录
     * @param array 条件数组
     * @return int userId
     */
    public function checkLogin($where)
    {
        return $this->where($where)->getField('user_id');
    }

    /**
     * 获取权限列表
     * @param int 用户id
     * @return arr 权限数组
     */
    public function getLimitList($userId)
    {
        $where = [
            'user_id' => $userId
        ];
        $limitList = $this->where($where)->getField('limit_list');
        return explode(',', $limitList);
    }

    /**
     * 检查公众号是否有权限
     * @param int 用户id
     * @param int 公众号id
     * @return arr
     */
    public function checkLimit($userId, $publicId)
    {
        $where = [
            'user_id' => $userId,
            'limitList' => ['in',$id]
        ];
        return $this->where($where)->find();
    }


}
<?php
namespace Wap\Model;

use Wap\Model\BaseModel;

/**
 * 违规表模型
 */
class BlackModel extends BaseModel
{
    protected $trueTableName = 'kdgx_black';

    /**
     * 添加数据
     * @param array 数据数组
     */
    public function addData($data)
    {
        !$this->create($data) && E($this->getError());
        return $this->add();
    }

    /**
     * 修改数据
     * @param array 条件数组
     * @param array 包含主键的数组
     */
    public function editData($where, $data)
    {
        !$this->create($data) && E($this->getError());
        return $this->where($where)->save();
    }

}

<?php
namespace Base\Model;

use Base\Model\BaseModel;

class MediaModel extends BaseModel
{
    protected $trueTableName = 'kdgx_plat_media';

    protected $_map = [
        'mediaId' => 'media_id'
    ];

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
     * @param array 修改数组
     */
    public function editData($where, $save)
    {
        if (empty($where)) {
            return false;
        }
        !$this->create($save) && E($this->getError());
        return $this->where($where)->save();
    }

    /**
     * 硬删除数据
     * @param array 条件数据
     */
    public function deleteData($where)
    {
        if (empty($where)) {
            return false;
        }
        return $this->where($where)->delete();
    }

    /**
     * 获取数据
     * @param string 公众号id
     */
    public function getData($where)
    {
        $data = $this->where($where)->select();
        return $this->parseFieldsMap($data);
    }
}

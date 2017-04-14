<?php

namespace Wap\Model;

use Wap\Model\BaseModel;

class PublicModel extends BaseModel
{
    protected $tableName = 'kdgx_wap_public';

    public function addData($data)
    {
        !$this->create($data) && E($this->getError());
        return $this->add('', array(), true);
    }

    public function editData($where, $save)
    {
        !$this->create($save) && E($this->getError());
        return $this->where($where)->save();
    }

    public function getData($where)
    {
        $data = $this->where($where)->find();
        return $data;
    }

    public function getAll($where = array())
    {
        !isset($where['state']) && $where['state'] = ['neq',2];
        $publics = $this->where($where)->order('create_time desc')->select();
        return $publics;
    }


}
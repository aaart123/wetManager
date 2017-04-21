<?php

namespace Wap\Model;

use Wap\Model\BaseModel;

class PublicSubscribeModel extends BaseModel
{
    protected $tableName = 'kdgx_public_subscribe';

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
        $publics = $this->where($where)->select();
        return $publics;
    }


}
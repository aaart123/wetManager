<?php

namespace Base\Model;

use Base\Model\BaseModel;

class ActionModel extends BaseModel
{
    protected $tableName = 'kdgx_plat_action';

    public function addData($data)
    {
        !$this->create($data) && E($this->getError());
        return $this->add();
    }

    public function getData($where)
    {
        $comment = $this->where($where)->find();
        return $comment;
    }

    public function editData($where, $save)
    {
        !$this->create($save) && E($this->getError());
        return $this->where($where)->save();
    }
    
}
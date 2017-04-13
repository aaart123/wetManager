<?php

namespace Wap\Model;

use Wap\Model\BaseModel;

class CommentThumbModel extends BaseModel
{
    protected $tableName = 'kdgx_social_comment_thumb';

    public function addData($data)
    {
        !$this->create($data) && E($this->getError());
        return $this->add();
    }

    public function editData($where, $save)
    {
        !$this->create($save) && E($this->getError());
        return $this->where($where)->save();
    }

    public function getData($where)
    {
        $comment = $this->where($where)->find();
        return $comment;
    }

    public function getCount($where)
    {
        return $this->where($where)->count();
    }


}
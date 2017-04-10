<?php

namespace Wap\Model;

use Wap\Model\BaseModel;

class CommentReadModel extends BaseModel
{
    protected $tableName = 'kdgx_social_comment_read';

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
    
}
<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/3/28
 * Time: 11:57
 */

namespace Wap\Model;

use Wap\Model\BaseModel;

class CommentModel extends BaseModel
{
    protected $tableName = 'kdgx_social_comment';

    public function addData($data)
    {
        !$this->create($data) && E($this->getError());
        return $this->add($data);
    }
 
    public function editData($where, $save)
    {
        return $this->where($where)->save($save);
    }

    public function getAll()
    {
        $where['is_delete'] = '0';
        $articles = $this->where($where)->select();
        return $articles;
    }

    public function getData($comment_id)
    {
        $where['is_delete'] = '0';
        $where['comment_id'] = $comment_id;
        $article = $this->where($where)->find();
        return $article;
    }

    public function Insec($comment_id, $field)
    {
        $where['comment_id'] = $comment_id;
        return $this->where($where)->setInc($field, 1);
    }

    public function Desec($comment_id, $field)
    {
        $where['comment_id'] = $comment_id;
        return $this->where($where)->Desec($field, 1);  
    }
}
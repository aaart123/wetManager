<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/3/28
 * Time: 11:59
 */

namespace Wap\Model;

use Wap\Model\BaseModel;

class ArticleThumbModel extends BaseModel
{
    protected $tableName = 'kdgx_social_article_thumb';

    public function addData($data)
    {
        !$this->create($data) && E($this->getError());
        return $this->add($data);
    }

    public function editData($where, $save)
    {
        return $this->where($where)->save($save);
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
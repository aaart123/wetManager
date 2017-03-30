<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/3/28
 * Time: 11:51
 */

namespace Wap\Model;

use Wap\Model\BaseModel;

class ArticleModel extends BaseModel
{
    protected $tableName = 'kdgx_social_article';

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

    public function getData($article_id)
    {
        $where['is_delete'] = '0';
        $where['article_id'] = $article_id;
        $article = $this->where($where)->find();
        return $article;
    }

    public function Insec($article_id, $field)
    {
        $where['article_id'] = $article_id;
        return $this->where($where)->setInc($field, 1);
    }

    public function Desec($article_id, $field)
    {
        $where['article_id'] = $article_id;
        return $this->where($where)->Desec($field, 1);  
    }

}
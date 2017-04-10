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
        return $this->add();
    }
 
    public function editData($where, $save)
    {
        !$this->create($save) && E($this->getError());
        return $this->where($where)->save();
    }

    public function getAll($where = array(), $page = 1)
    {
        $where['is_delete'] = '0';
        $limit = ($page-1) * 20;
        $articles = $this->where($where)->order('create_time desc')->limit($limit, 20)->select();
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
        return $this->where($where)->setDec($field, 1);  
    }

}
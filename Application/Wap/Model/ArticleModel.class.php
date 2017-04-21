<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/3/28
 * Time: 11:51
 */

namespace Wap\Model;

use Think\Model;

class ArticleModel extends Model
{
    protected $tableName = 'kdgx_social_article';

    protected $_auto = array(
        array('create_time','time', 1, 'function'),
        array('modified_time', 'time', 2, 'function')
    );

    public function addData($data)
    {
        !$this->create($data) && E($this->getError());
        return $this->add();
    }
 
    public function editData($where, $save)
    {
        !($data = $this->create($save)) && E($this->getError());
        return $this->where($where)->save();
    }

    public function getAll($where = array(), $page = 1)
    {
        !isset($where['is_delete']) && $where['is_delete'] = ['neq', 1];
        $limit = ($page-1) * 20;
        $articles = $this->where($where)->order('create_time desc')->limit($limit, 20)->select();
        return $articles;
    }

    public function All($where = array())
    {
        !isset($where['is_delete']) && $where['is_delete'] = ['neq', 1];
        $articles = $this->where($where)->select();
        return $articles;
    }

    public function getData($article_id)
    {
        !isset($where['is_delete']) && $where['is_delete'] = ['neq', 1];
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
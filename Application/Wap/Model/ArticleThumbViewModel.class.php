<?php
namespace Wap\Model;

use Think\Model\ViewModel;

class ArticleThumbViewModel extends ViewModel
{
    public $viewFields = array(
        'Thumb' => [
            '_table' => 'kdgx_social_article_thumb',
            '_as' => 't'
        ],
        'Article' => [
            '_table' => 'kdgx_social_article',
            '_as' => 'a',
            '_on' => 't.article_id=a.article_id',
            'create_time' => 'create_time',
            'user_id',
            'content',
            'img',
            'url',
            'thumb',
            'comment'
        ]
    );

    public function getAll($where = array())
    {
        $where['a.is_delete'] = '0';
        $data = $this->where($where)->order('a.create_time desc')->select();
        return $data;
    }

}
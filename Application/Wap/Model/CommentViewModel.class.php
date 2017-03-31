<?php

namespace Wap\Model;

use Think\Model\ViewModel;

class CommentViewModel extends ViewModel
{
    public $viewFields = array(
        'Comment' => [
            '_table' => 'kdgx_social_comment',
            '_as' => 'c',
            'comment_id',
            'create_time',
            'user_id',
            'content',
            'pid'
        ],
        'Article' => [
            '_table' => 'kdgx_social_article',
            '_as' => 'a',
            '_on' => 'c.article_id=a.article_id',
            'create_time' => 'a_create_time',
            'user_id' => 'a_user_id',
            'content' => 'a_content',
            'img',
            'url',
            'thumb',
            'comment'
        ]
    );

    public function getAll($where = array())
    {
        $where['c.is_delete'] = '0';
        $data = $this->where($where)->order('c.create_time desc')->select();
        foreach ($data as &$value) {
             $value['article'] = [
                 'create_time' => 'a_create_time',
                 'user_id' => 'a_user_id',
                 'content' => 'a_content',
                 'img' => 'img',
                 'url' => 'url',
                 'thumb' => 'thumb',
                 'comment' => 'comment'
              ];
              unset($value['a_create_time']);
              unset($value['a_user_id']);
              unset($value['a_content']);
              unset($value['img']);
              unset($value['url']);
              unset($value['thumb']);
              unset($value['comment']);
        }
        return $data;
    }

}

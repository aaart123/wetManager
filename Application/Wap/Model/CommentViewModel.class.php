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
            'article_id' => 'a_article_id',
            'create_time' => 'a_create_time',
            'user_id' => 'a_user_id',
            'content' => 'a_content',
            'is_delete' => 'a_is_delete',
            'img',
            'url',
            'thumb',
            'comment'
        ]
    );

    public function getAll($where = array(), $page = 1)
    {
        $where['c.is_delete'] = '0';
       $limit = ($page-1) * 20;
        $data = $this->where($where)->order('c.create_time desc')->limit($limit, 20)->select();
        foreach ($data as &$value) {
             $value['article'] = [
                 'create_time' => $value['a_create_time'],
                 'user_id' => $value['a_user_id'],
                 'content' => $value['a_content'],
                 'is_delete' => $value['a_is_delete'],
                 'article_id' => $value['a_article_id']
              ];
              unset($value['a_create_time']);
              unset($value['a_article_id']);
              unset($value['a_user_id']);
              unset($value['a_content']);
              unset($value['a_is_delete']);
              unset($value['img']);
              unset($value['url']);
              unset($value['thumb']);
              unset($value['comment']);
        }
        return $data;
    }

}

<?php

namespace Wap\Controller;

use Base\Controller\BaseController;

/**
 * 评论管理
 */

class CommentController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 创建评论
     * @param array
     */
    public function createCommnet($data)
    {
       if ($comment_id = $this->commentModel->addData($data)) {
           return $comment_id;
       } else {
           return false;
       }
    }

    /**
     * 点赞/取消 评论
     * @param int
     * @param int
     * @return boolean
     */
    public function thumbComment($user_id, $comment_id)
    {
        $data = [
            'user_id' => $user_id,
            'comment_id' => $comment_id
        ];
        if ($is_thumb = $this->commentThumbModel->where($data)->getField('is_delete')) {
            $save['is_delete'] = '1';
            $is_thumb && $save['is_delete'] = '0';
            $this->commentThumbModel->editData($where, $save);
        } elseif ($this->commentThumbModel->addData($data)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 删除评论
     * @param int
     */
    public function deleteComment($comment_id)
    {
        $where['comment_id'] = $comment_id;
        $save['is_delete'] = '1';
        if ($this->articleModel->editData($where, $save)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取某个文章的评论
     * @param int
     */
    public function getComment($article_id)
    {
        $where['article_id'] = $article_id;
        $comments = $this->commentModel->getData($where);
        return $comments;
    }

}
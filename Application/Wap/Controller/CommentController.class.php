<?php

namespace Wap\Controller;

use Base\Controller\BaseController;

use Wap\Model\ArticleModel;
use Wap\Model\CommentModel;
use Wap\Model\CommentThumbModel;


/**
 * 评论管理
 */

class CommentController extends BaseController
{
    protected $articleModel;
    protected $commentModel;
    protected $commentThumbModel;

    public function __construct()
    {
        parent::__construct();
        $this->articleModel = new ArticleModel();
        $this->commentModel = new CommentModel();
        $this->commentThumbModel = new CommentThumbModel();
    }

    /**
     * 创建评论
     * @param array
     */
    public function createComment($data)
    {
       if ($comment_id = $this->commentModel->addData($data)) {
           $this->articleModel->Insec($data['article_id'], 'comment');
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
        if ($thumb = $this->commentThumbModel->getData($data)) {
            $save['is_delete'] = '1';
            $thumb['is_delete'] && $save['is_delete'] = '0';
            $this->commentThumbModel->editData($data, $save);
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
        $comment = $this->commentModel->getData($where);
        $save['is_delete'] = '1';
        if ($this->articleModel->editData($where, $save)) {
            $this->articleModel->Desec($data['article_id'], 'comment');
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取某个文章的评论
     * @param int
     */
    public function getCommentList($article_id)
    {
        $where['article_id'] = $article_id;
        $comments = $this->commentModel->getAll($where);
        foreach ($comments as &$comment) {
            $user = D('UserInfo')->getUserInfo($comment['user_id']);
            $comment['user'] = $user;
            $data['comment_id'] = $comment['comment_id'];
            $comment['thumbs'] = $this->commentThumbModel->getCount($data);
            $data['user_id'] = session('plat_user_id');
            $data['is_delete'] = '0';
            $comment['is_thumb'] = 0;
            $this->commentThumbModel->getData($data) && $comment['is_thumb'] = 1;
            if ($comment['pid']) {
                $comment['pid'] = $this->getCommen($comment['comment_id']);
            }
            unset($comment['modified_time']);
            unset($comment['is_delete']);
        }
        return $comments;
    }

    /**
     * 获取某个评论的信息
     * @param int
     */
    public function getCommen($comment_id)
    {
        $comment = $this->commentModel->getData($comment_id);
        $user = D('UserInfo')->getUserInfo($comment['user_id']);
        $comment['user'] = $user;
        unset($comment['pid']);
        return $comment;
    }

}
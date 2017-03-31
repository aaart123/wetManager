<?php

namespace Wap\Controller;

use Wap\Controller\CommonController;

use Wap\Model\ArticleModel;
use Wap\Model\CommentModel;
use Wap\Model\CommentThumbModel;
use Wap\Model\CommentViewModel;

/**
 * 评论管理
 */

class CommentController extends CommonController
{
    protected $articleModel;
    protected $commentModel;
    protected $commentThumbModel;
    protected $commentView;

    public function __construct()
    {
        parent::__construct();
        $this->articleModel = new ArticleModel();
        $this->commentModel = new CommentModel();
        $this->commentThumbModel = new CommentThumbModel();
        $this->commentView = new CommentViewModel();
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
            $this->dealParam($comment);
        }
        return $comments;
    }
    
    /**
     * 获取个人发表的评论
     * @param int
     */
    public function getSelfList($user_id)
    {
        $where['user_id'] = $user_id;
        $comments = $this->commentModel->relation('article')->getAll($where);
        foreach ($comments as &$comment) {
            $this->dealParam($comment);
        }
        return $comments;
    }

    /**
     * 获取评论个人文章的评论
     * @param int
     */
     public function getIsCommentList($user_id)
     {
         $where['pid'] = 0;
         $where['a.user_id'] = $user_id;
         $comments = $this->commentView->getAll($where);
         foreach ($comments as &$comment) {
            $this->dealParam($comment);  
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
        $this->dealParam($comment);
        return $comment;
    }

    /**
     * 结果数组处理
     * @param int
     */
    private function dealParam(&$data)
    {
        if (isset($data['article'])) {
            $user = D('UserInfo')->getUserInfo($data['user_id']);
            $data['article']['user'] = $user;
        }
        $user = D('UserInfo')->getUserInfo($data['user_id']);
        $data['user'] = $user;
        $where['comment_id'] = $data['comment_id'];
        $data['thumbs'] = $this->commentThumbModel->getCount($where);
        $where['user_id'] = session('plat_user_id');
        $where['is_delete'] = '0';
        $data['is_thumb'] = 0;
        $this->commentThumbModel->getData($where) && $data['is_thumb'] = 1;
        if ($data['pid']) {
            $data['pid'] = $this->getCommen($data['comment_id']);
        }
        unset($data['modified_time']);
        unset($data['is_delete']);
        unset($data['user_id']);
        unset($data['article_id']);
    }

}
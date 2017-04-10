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
    protected $redisObj;
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
            $save['state'] = 1;
            $thumb['state'] && $save['state'] = 0;
            if ($this->commentThumbModel->editData($data, $save)) {
                return true;
            } else {
                return false;
            }
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
        if ($this->commentModel->editData($where, $save)) {
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
    public function getComment($comment_id)
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
        if (empty($data)) {
            return ;
        }
        $user_id = session('plat_user_id');
        if (isset($data['article'])) {
            if($data['article']['is_delete']==1) {
                $data['article'] = -1;
            } else {
                $user = D('UserInfo')->getUserInfo($data['article']['user_id']);
                $data['article']['user'] = $user;
            }
        }
        $data['user'] = D('UserInfo')->getUserInfo($data['user_id']);
        if ($user_id == $data['user_id']) {
            $data['user']['self'] = 1;
        } else {
            $data['user']['self'] = 0;
            if ($this->isSubscribute($user_id, $data['user']['user_id'])) {
                $data['user']['subscribe'] = 1;
            } else {
                $data['user']['subscribe'] = 0;
            }
        }
        $where['comment_id'] = $data['comment_id'];
        $data['thumbs'] = $this->commentThumbModel->getCount($where);
        $where['user_id'] = session('plat_user_id');
        $where['state'] = 1;
        $data['is_thumb'] = 0;
        $this->commentThumbModel->getData($where) && $data['is_thumb'] = 1;
        if ($data['pid']) {
            $data['pid'] = $this->commentModel->getData($data['pid']);
            if (empty($data['pid'])){
                $data['pid'] = -1;
            }else {
                $user = D('UserInfo')->getUserInfo($data['pid']['user_id']);
                $data['pid']['user'] = $user;
            }
        }
        unset($data['modified_time']);
        unset($data['is_delete']);
        unset($data['user_id']);
        unset($data['article_id']);
    }

}
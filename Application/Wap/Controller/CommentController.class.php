<?php

namespace Wap\Controller;

use Wap\Controller\CommonController;

use Wap\Model\PublicModel;
use Wap\Model\ArticleModel;
use Wap\Model\CommentModel;
use Wap\Model\CommentThumbModel;
use Wap\Model\CommentViewModel;
use Wap\Model\CommentReadModel;
use Wap\Model\PublicUserModel;
use Wap\Model\PublicSubscribeModel;


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

    private function sendNotive($openid, $article_id, $nickname)
    {
        $array=[
            'openid'=> $openid,
            'url'=>'http://www.koudaidaxue.com/index.php/Wap/index/index#/detail?id='.$article_id.'&from=share',
            'first'=>'您的动态有精彩评论,快去看看吧!
                    ',
            'keyword1'=>"{$nickname} 的评论",
            'keyword2'=>date('m-d H:i'),
            'remark'=>'
点此查看详情'
        ];
        $obj = new \Base\Controller\WetchatApiController();
        $obj->publicId = 'gh_243fe4c4141f';
        $obj->setSubscribeTemplate($array);
    }

    private function sendReply($openid, $article_id, $nickname, $first)
    {
        $array=[
            'openid'=> $openid,
            'url'=>'http://www.koudaidaxue.com/index.php/Wap/index/index#/detail?id='.$article_id.'&from=share',
            'first'=>$first,
            'keyword1'=>"{$nickname} 的回复",
            'keyword2'=>date('m-d H:i'),
            'remark'=>'
点此查看详情'
        ];
        $obj = new \Base\Controller\WetchatApiController();
        $obj->publicId = 'gh_243fe4c4141f';
        $obj->setSubscribeTemplate($array);
    }

    /**
     * 创建评论
     * @param array
     */
    public function createComment($data)
    {
       if ($comment_id = $this->commentModel->addData($data)) {
           $this->articleModel->Insec($data['article_id'], 'comment');
           $this->replyNotive($comment_id);
           return $comment_id;
       } else {
           return false;
       }
    }

    /**
     * 回复通知
     */
    private function replyNotive($comment_id)
    {
        $comment = $this->commentModel->relation('article')->getData($comment_id);
        if (!$comment['pid']) {
            $this->thumbNotive($comment, true);
        } else {
            $pcomment = $this->commentModel->getData($comment['pid']);
            if (session('plat_user_id') != $pcomment['user_id']) {
                $openid = D('User')->where(['user_id'=>$pcomment['user_id']])->getField('openid');
                $nickname = D('UserInfo')->where(['user_id'=>$comment['user_id']])->getField('nickname');
                $first = '您有新的回复,快去看看吧!
                        ';
                $this->sendReply($openid, $comment['article_id'], $nickname, $first);
            }
        }
    }

    /**
     * 评论/点赞 通知
     */
    private function thumbNotive($comment, $flag = false)
    {
        if (!is_numeric($article['user_id'])) {
            $publicUser = new PublicUserModel();
            $puWh['public_id'] = $comment['article']['user_id'];
            $users = $publicUser->where($puWh)->getField('user_list');
            $users = explode(',', $users);
            foreach ($users as $user) {
                if (session('plat_user_id') == $user) {
                    continue;
                }
                $openid = D('User')->where(['user_id'=>$user])->getField('openid');
                $nickname = D('UserInfo')->where(['user_id'=>$comment['user_id']])->getField('nickname');
                $first = '您被精选的文章，有新的评论
                        ';
                !$flag ? $this->sendNotive($openid, $comment['article_id'], $nickname) : $this->sendReply($openid, $comment['article_id'], $nickname, $first);
                
            }
        } elseif (session('plat_user_id') != $comment['article']['user_id']) {
            $openid = D('User')->where(['user_id'=>$comment['article']['user_id']])->getField('openid');
            $nickname = D('UserInfo')->where(['user_id'=>$comment['user_id']])->getField('nickname');
            $first = '您有新的回复,快去看看吧!+
                    ';
            !$flag ? $this->sendNotive($openid, $comment['article_id'], $nickname) : $this->sendReply($openid, $comment['article_id'], $nickname, $first);
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
            return false;
        } elseif ($this->commentThumbModel->addData($data)) {
            $where['comment_id'] = $comment_id;
            $count = $this->commentThumbModel->getCount($where);
            if (0 && $count == 1) {
                // 发送模板消息
                $comment = $this->commentModel->relation('article')->getData($comment_id);
                $this->thumbNotive($comment);
            }
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
        $comment = $this->commentModel->getData($comment_id);
        $save['is_delete'] = '1';
        if ($this->commentModel->editData($where, $save)) {
            $this->articleModel->Desec($comment['article_id'], 'comment');
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
            $this->isRead($comment);
         }
         return $comments;
     }

     /**
      * 获取回复信息
      * @param int
      */
     public function getReplyCommentList($user_id)
     {
         $sql = "SELECT * FROM `pocket`.`kdgx_social_comment` where is_delete = '0' and `pid` in 
                (select `comment_id` from `kdgx_social_comment`WHERE `user_id`= ".$user_id.")";
        $comments = $this->commentModel->query($sql);
        foreach($comments as &$comment) {
			$this->isRead($comment);
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
	 * 添加消息读记录
	 * @param int
	 */
	public function readComment($comment_id)
	{
		$user_id = session('plat_user_id');
        $commentReadModel = new CommentReadModel();
        $data = [
            'user_id' => $user_id,
            'comment_id' => $comment_id
        ];
		return $commentReadModel->addData($data);
	}

    /**
     * 处理消息是否被读取过
     */
    private function isRead(&$data)
    {        
        if (empty($data) && !$data['pid']) {
            return ;
        }
		
		$data['article'] = $this->articleModel->getData($data['article_id']);
		
        $user_id = session('plat_user_id');
        $commentReadModel = new CommentReadModel();
        $where = [
            'user_id' => $user_id,
            'comment_id' => $data['comment_id']
        ];
        if ($commentReadModel->getData($where)) {
            $data['read'] = 1;
        } else {
            $data['read'] = 0;
        }
    }

    /**
     * 结果数组处理
     * @param int
     */
    private function dealParam(&$data)
    {
        if (empty($data)) {
            return;
        }
        /* --------------- 获取评论文章数据 -------------------*/
        $user_id = session('plat_user_id');
        if (isset($data['article'])) {
            if($data['article']['is_delete']==1) {
                $data['article'] = -1;
            } else if(substr($data['article']['user_id'], 0, 3)=='gh_'){
                $data['article']['type'] = 1;
                $publicModel = new PublicModel();
                $pubWh['user_name'] = $data['article']['user_id'];
                $data['article']['user'] = $publicModel->getData($pubWh);
            } else {
                $user = D('UserInfo')->getUserInfo($data['article']['user_id']);
                $data['article']['user'] = $user;
                $data['article']['type'] = 0;
            }
        }
        /* --------------- 关注状态处理 -------------------*/
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
        /* --------------- 评论点赞状态处理 -------------------*/
        $where['comment_id'] = $data['comment_id'];
        $data['thumbs'] = $this->commentThumbModel->getCount($where);
        $where['user_id'] = $user_id;
        $where['state'] = 1;
        $data['is_thumb'] = 0;
        $this->commentThumbModel->getData($where) && $data['is_thumb'] = 1;
        /* --------------- 获取父评论信息 -------------------*/
        if ($data['pid']) {
            $data['pid'] = $this->commentModel->getData($data['pid']);
            if (empty($data['pid'])){
                $data['pid'] = -1;
            }else {
                $user = D('UserInfo')->getUserInfo($data['pid']['user_id']);
                $data['pid']['user'] = $user;
            }
        }
        /* --------------- 过滤数据 -------------------*/
        unset($data['modified_time']);
        unset($data['is_delete']);
        unset($data['user_id']);
        unset($data['article_id']);
    }

}
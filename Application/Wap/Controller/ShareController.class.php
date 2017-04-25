<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/4/14
 * Time: 18:27
 */

namespace Wap\Controller;

use Wap\Controller\RegController;
use \Wap\Controller\ArticleController;
use \Wap\Controller\CommentController;

class ShareController extends RegController
{

    private $articleActivity;
    private $commentActivity;
    public function __construct()
    {
        parent::__construct();
        $this->articleActivity = new ArticleController();
        $this->commentActivity = new CommentController();
    }



    public function detail()
    {
        $this->display('Index/detail');
    }

    public function usercenter()
    {
        $this->display('Index/usercenter');
    }

    public function preview()
    {
        $this->display('Index/preview');
    }


/* ---------------------------------------- 圈子文章 ------------------------------------------------- */


    public function getArticleData($article_id)
    {
        // 获取动态信息
            // http://www.koudaidaxue.com/index.php/wap/http/getArticleData?article_id=3
        $article = $this->articleActivity->getArticle($article_id, $this->user_id);
        echo json_encode([
            'errcode' => 0,
            'errmsg' => $article
        ]);
        exit;
    }

    public function getArticleList()
    {
        // 获取最新列表
            // http://www.koudaidaxue.com/index.php/wap/http/getArticleList?page=1
        $page = I('get.page',1);
        $list = $this->articleActivity->getNewList($page);
        echo json_encode([
            'errcode' => 0,
            'errmsg' => $list
        ]);
        exit;
    }

    public function getHotList()
    {
        // 获取最热动态
            // http://www.koudaidaxue.com/index.php/wap/http/getHotList
        $list = $this->articleActivity->getHotList();
        echo json_encode([
            'errcode' => 0,
            'errmsg' => $list
        ]);
        exit;
    }

/* ---------------------------------------- 评论管理 -------------------------------------------------- */

    public function getCommentList($article_id)
    {
        // 获取评论列表
            // http://www.koudaidaxue.com/index.php/wap/http/getCommentList?article_id=3
        $list = $this->commentActivity->getCommentList($article_id);
        echo json_encode([
            'errcode' => 0,
            'errmsg' => $list
        ]);
        exit;
    }

	public function readComment($comment_id)
	{
		// 读评论记录
			// http://www.koudaidaxue.com/index.php/wap/http/readComment?comment_id=3
		if ($this->commentActivity->readComment($comment_id) ) {
			echo json_encode([
				'errcode' => 0,
				'errmsg' => '成功'
			]);
			exit;
		} else {
		    echo json_encode([
                'errcode' => 1001,
                'errmsg' => '失败'
            ]);
			exit;
		}
	}

/* ---------------------------------------- 个人相关 -------------------------------------------------- */

    public function selfRelate()
    {
        // 个人相关动态(发布的圈子;发布的评论)无user_id参数则表示自己
            // http://www.koudaidaxue.com/index.php/wap/http/selfRelate?user_id=2
        $user_id = I('get.user_id',$this->user_id);
        $articleList = $this->articleActivity->getSelfList($user_id);
        $commentList = $this->commentActivity->getSelfList($user_id);
        $list = array_merge($articleList, $commentList);
        usort($list, descSort('create_time'));
        echo json_encode([
            'errcode' => 0,
            'errmsg' => $list
        ]);
        exit;
    }

    public function selfMsg()
    {
        // 消息中心
            // http://www.koudaidaxue.com/index.php/wap/http/selfMsg
        $articleIsComments = $this->commentActivity->getIsCommentList($this->user_id);
        $replyComments = $this->commentActivity->getReplyCommentList($this->user_id);
        $list = array_merge($articleIsComments, $replyComments);
        usort($list, descSort('create_time'));
        echo json_encode([
            'errcode' => 0,
            'errmsg' => $list
        ]);
        exit;
    }


    /**
     *
     */
    public function getUserInfo()
    {
        if($userId = $_GET['userid'])
        {
            $data = D('Wap/UserInfo')->getUserInfo($userId);
            $data['sub'] = D('subscribe')->where(array('user_id'=>$userId,'subscribe_state'=>'1'))->count();
            $data['publics'] = D('PublicSubscribe')->where(array('user_id'=>$userId,'state' =>'1'))->count(); // 关注公众号数
            $data['unsub'] = D('subscribe')->where(array('subscribe_user'=>$userId,'subscribe_state'=>'1'))->count();
            echo json_encode(array(
                'errcode'=>0,
                'errmsg'=>$data
            ));
        }else{
            echo json_encode(array(
                'errcode'=>10001,
                'errmsg'=>'参数为空'
            ));
        }
    }



    public function newcomer()
    {
        $this->display('Index/newcomer');
    }


    /**
     * 获取公众号主页信息
     */
    public function getPublicInfo()
    {

        if($publicId = $_POST['public_id'])
        {
            $data = D('Wap/Public')->field('user_name,alias,nick_name,head_img')->where(array('user_name'=>$publicId))->find();
            echo json_encode(array(
                'errmsg'=>0,
                'errmsg'=>$data,
            ));exit;
        }else{
            echo json_encode(array(
                'errcode'=>10001,
                'errmsg'=>'参数错误',
            ));exit;
        }
    }


    public function isReg()
    {
        $wechatInfo = $_SESSION['wechat_info'];
        if($userId = D('Wap/User')->where(array('openid'=>$wechatInfo['openid']))->getField('user_id'))
        {
            echo json_encode(array(
                'errcode'=>0,
                'errmsg'=>$userId
            ));exit;
        }else{
            echo json_encode(array(
               'errcode'=>10001,
                'errmsg'=>'未注册'
            ));exit;
        }

    }


}

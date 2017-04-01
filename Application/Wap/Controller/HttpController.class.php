<?php

namespace Wap\Controller;

use Think\Controller;
use Wap\Controller\BaseController;
use Wap\Controller\ArticleController;
use Wap\Controller\CommentController;

/**
 * 路由管理
 */
class HttpController extends BaseController
{
    private $articleActivity;
    private $commentActivity;
    private $user_id;

    public function __construct()
    {
        parent::__construct();
        // session('plat_user_id', 3);
        $this->user_id = session('plat_user_id');
        $this->articleActivity = new ArticleController();
        $this->commentActivity = new CommentController();
    }

/* ---------------------------------------- 圈子文章 ------------------------------------------------- */

    public function createArticle()
    {
        $post = I('post.');
        // 发布圈子文章
            // http://www.koudaidaxue.com/index.php/wap/http/createArticle
            // $post = [
            //     'content' => '这里是'.$this->user_id.'今天发布的动态',
            //     'imgs' => [
            //         '0' => 'data:image/png;base64,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',
            //         '1' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAABqElEQVRYR+2W7zEEQRBH30WADMiACBABIkAEiAARIAJEgAyIgAzIABFQT+1UrWN2eubq7r5cV+2n3Zl+/et/O2LONpqzfxYAtQqsA5vAcpe6D+AJeGlNZRRgF7gAVoHPnkOBloA34AS4rwUpARjpNSDAA3AJPI458d0BsAPcAIc1ECUApTVqHZSiE0SAuxqIIYAz4BjYqsixEAKYDtUqWg7AqJ+BK0CQGvP7I2ANsEgHLQdg5F4kSPGSMQ/WzXtUhRyA+daxuW+xVC+mpEkBnZvDWvmTM89ZOz5NAF/A+YQAp1Ae9bkUOFhsqUkUUH4HVZMC4Rxmbg+fzylg8TkBVxq6wM557YaRKjYp4CEL0QtsyRqzeA0gLaxmgDTVnO3FSDovSbm9wOj+OVLaBTre71RwKg6ZSrkxtY3o+C4BeJmdYEu5BQVy/9slmvn2/yBNTVe169n0bUcgIgA6sp104sr9z257y0fQMEQUoO/U6SaQUaqEK7u/L3wXhmgBiHREGGJaACltRSWmCRCCmDbAOMSfBTcLgAThYHNK/vrBmRVAtnAXAHNX4BufR1shxBNKTAAAAABJRU5ErkJggg=='
            //     ],
            //     'url' => 'http://www.外链.com'
            // ];
        $post['user_id'] = $this->user_id;
        if ($article_id = $this->articleActivity->createArticle($post)) {
            echo json_encode([
            'errcode' => 0,
            'errmsg' => $article_id
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

    public function deleteArticle($article_id)
    {
        // 删除圈子文章
            // http://www.koudaidaxue.com/index.php/wap/http/deleteArticle?article_id=1
        if ($this->articleActivity->deleteArticle($article_id)) {
            echo json_encode([
                'errcode' => 0,
                'errmsg' => ''
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
            // http://www.koudaidaxue.com/index.php/wap/http/getArticleList
        $list = $this->articleActivity->getNewList($this->user_id);
        echo json_encode([
            'errcode' => 0,
            'errmsg' => $list
        ]);
        exit;
    }

    public function getWeightList()
    {
        // 获取加权列表
            // http://www.koudaidaxue.com/index.php/wap/http/getWeightList
        $list = $this->articleActivity->getWeightList($this->user_id);
        usort($list, descSort('weight'));
        echo json_encode([
            'errcode' => 0,
            'errmsg' => $list
        ]);
        exit;
    }

    public function thumbArticle($article_id)
    {
        // 取消/点赞圈子文章
            // http://www.koudaidaxue.com/index.php/wap/http/thumbArticle?article_id=1
        if ($this->articleActivity->thumbArticle($this->user_id, $article_id)) {
            echo json_encode([
                'errcode' => 0,
                'errmsg' => ''
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

/* ---------------------------------------- 评论管理 -------------------------------------------------- */
    
    public function createComment($article_id)
    {
        $post = I('post.');
        // 发布评论
            // http://www.koudaidaxue.com/index.php/wap/http/createComment?article_id=3
            // $post = [
            //     'content' => '这里是'.$this->user_id.'评论'.$article_id.'的内容',
            //     'pid' => 0 #评论的是哪条评论comment_id;评论文章则为0
            // ];
        $post['user_id'] = session('plat_user_id');
        $post['article_id'] = $article_id;
        if ($article_id = $this->commentActivity->createComment($post)) {
            echo json_encode([
            'errcode' => 0,
            'errmsg' => $article_id
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
    
    public function deleteComment($comment_id)
    {
        // 删除评论
            // http://www.koudaidaxue.com/index.php/wap/http/deleteComment?comment_id=3
        if ($this->commentActivity->deleteComment($comment_id)) {
            echo json_encode([
                'errcode' => 0,
                'errmsg' => ''
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

    public function thumbComment($comment_id)
    {
        // 取消/点赞圈子文章
            // http://www.koudaidaxue.com/index.php/wap/http/thumbComment?comment_id=1
        if ($this->commentActivity->thumbComment($this->user_id, $comment_id)) {
            echo json_encode([
                'errcode' => 0,
                'errmsg' => ''
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
        // 个人相关动态(发布的圈子;发布的评论;文章被评论)无user_id参数则表示自己
            // http://www.koudaidaxue.com/index.php/wap/http/selfRelate?user_id=3
        $user_id = I('get.user_id',$this->user_id);
        $articleList = $this->articleActivity->getSelfList($user_id);
        $isCommentList = $this->commentActivity->getIsCommentList($user_id);
        $commentList = $this->commentActivity->getSelfList($user_id);
        $list = array_merge($articleList, $isCommentList, $commentList);
        usort($list, descSort('create_time'));
        echo json_encode([
            'errcode' => 0,
            'errmsg' => $list
        ]);
        exit;
    }
    
    public function getSelfList()
    {
        // 获取某人发布的动态列表
            // http://www.koudaidaxue.com/index.php/wap/http/getSelfList
        $list = $this->articleActivity->getSelfList($this->user_id);
        echo json_encode([
            'errcode' => 0,
            'errmsg' => $list
        ]);
        exit;
    }

    public function getIsCommentList()
    {
        // 评论个人文章的评论
            // http://www.koudaidaxue.com/index.php/wap/http/getIsCommentList
        $list = $this->commentActivity->getIsCommentList($this->user_id);
        echo json_encode([
            'errcode' => 0,
            'errmsg' => $list
        ]);
        exit;
    }

    public function getSelfCommentList()
    {
        // 获取某人发表的评论
            // http://www.koudaidaxue.com/index.php/wap/http/getSelfCommentList
        $list = $this->commentActivity->getSelfList($this->user_id);
        echo json_encode([
            'errcode' => 0,
            'errmsg' => $list
        ]);
        exit;
    }
}

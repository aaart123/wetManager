<?php

namespace Wap\Controller;

use Wap\Controller\CommonController;

use Wap\Model\ArticleThumbModel;
use Wap\Model\ArticleThumbViewModel;

/**
 * 圈子文章管理
 */

class ArticleController extends CommonController
{
    private $articleModel;
    private $articleThumbModel;
    private $articleThumbView;
    
    public function __construct()
    {
        parent::__construct();
        $this->articleModel = D('Article');
        $this->articleThumbModel = new ArticleThumbModel();
        $this->articleThumbView = new ArticleThumbViewModel();
    }

    /**
     * 发布圈子动态
     * @param array 
     */
    public function createArticle($data)
    {
        if (!empty($data['imgs'])) {
            $data['img'] = '';
            foreach ($data['imgs'] as $baseImg) {
                $img = base64Img($baseImg, './social/article/');
                $data['img'] .= ',' . $img;
            }
            $data['img'] = ltrim($data['img'], ',');
        }
        unset($data['imgs']);
        if ($article_id = $this->articleModel->addData($data)) {
            return $article_id;
        } else {
            return false;
        }
    }

    /**
     * 点赞/取消圈子文章
     * @param int
     * @param int
     * @return boolean
     */
    public function thumbArticle($user_id, $article_id)
    {
        $where = [
            'user_id' => $user_id,
            'article_id' => $article_id
        ];
        if ($thumb = $this->articleThumbModel->getData($where)) {
            $save['state'] = 1;
            $thumb['state'] && $save['state'] = 0;
            if ($this->articleThumbModel->editData($where, $save)) {
                if ($save['state']) {
                    $this->articleModel->Insec($article_id, 'thumb');
                }else {
                    $this->articleModel->Desec($article_id, 'thumb');
                }
                return true;
            } else {
                return false;
            }
        } elseif ($this->articleThumbModel->addData($where) && $this->articleModel->Insec($article_id, 'thumb')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 删除圈子动态
     * @param int
     */
    public function deleteArticle($article_id)
    {
        $where['article_id'] = $article_id;
        $save['is_delete'] = '1';
        if ($this->articleModel->editData($where, $save)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 修改圈子动态
     * @param array
     */
    public function editArticle($data)
    {
        if (!isset($data['article_id'])) {
            return false;
        }
        $where['article_id'] = $data['article_id'];
        if ($this->articleModel->editData($where, $save)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取文章信息
     * @param int 文章id
     * @param int 用户id
     */
    public function getArticle($article_id, $user_id)
    {
        $article = $this->articleModel->getData($article_id);        
        $this->dealParam($article, $user_id);
        return $article;
    }

    public function getSubscribeArticle($user_id)
    {
        $subscribes = D('Subscribe')->field('subscribe_user')
        ->where(array('user_id'=>$user_id,'subscribe_state'=>'1'))->select();
        $subscribes = array_column($subscribes, 'subscribe_user');
        $subscribes = implode(',', $subscribes);
        $where['user_id'] = ['in', $subscribes];
        $articles = $this->articleModel->getAll($where);
        foreach ($articles as &$article) {
            $this->dealParam($article);
        }
        return $articles;
    }

    public function getThumbArticle($user_id)
    {
        $where = [
            't.user_id' => $user_id,
            'state' => 1
        ];
        $articles = $this->articleThumbView->getAll($where);
        foreach ($articles as &$article) {
            $this->dealParam($article);
        }
        return $articles;
    }

    /**
     * 获取个人发表文章
     * @param int
     */
    public function getSelfList($user_id)
    {
        $where['user_id'] = $user_id;
        $articles = $this->articleModel->getAll($where);
        foreach ($articles as &$article) {
            $this->dealParam($article);
        }
        return $articles;
    }

    /**
     * 获取最新动态列表
     * @param int 用户
     */
    public function getNewList($user_id)
    {
        $articles = $this->articleModel->getAll();
        foreach ($articles as &$article) {
            $this->dealParam($article, $user_id);
        }
        return $articles;
    }

    /**
     * 获取加权动态列表
     * @param int 用户
     */
    public function getWeightList($user_id)
    {
        $where['create_time'] = ['between',[strtotime(date('Y-m-d').' -1 week'), time()]];
        $articles = $this->articleModel->getAll($where);
        foreach ($articles as &$article) {
            $article['weight'] = $article['thumb'] * 3 + $article['comment'] * 3 + $article['user']['subscribe'] * 4;
            $this->dealParam($article, $user_id);
        }
        return $articles;
    }

    /**
     * 结果数组处理
     * @param int
     */
    private function dealParam(&$data, $user_id = 0)
    {
        $data['is_thumb'] = 0;
        $where['comment_id'] = $data['comment_id'];
        $where['user_id'] = $user_id;
        $where['is_delete'] = '0';
        $this->articleThumbModel->getData($where) && $data['is_thumb'] = 1;
        $data['user'] = D('UserInfo')->getUserInfo($data['user_id']);
        if ($user_id == $data['user_id']) {
            $data['user']['self'] = 1;
        } else {
            $data['user']['self'] = 0;
        }
        if ($this->isSubscribute($user_id, $data['user']['user_id'])) {
            $data['user']['subscribe'] = 1;
        } else {
            $data['user']['subscribe'] = 0;
        }
        $data['imgs'] = [];
        if (!empty($data['img'])) {
            $imgs = explode(',', $data['img']);
            foreach ($imgs as &$img) {
                $img = 'http://'.C('CDN_SITE').$img;
            }
            $data['imgs'] = $imgs;
        }
        unset($data['modified_time']);
        unset($data['is_delete']);
        unset($data['user_id']);
        unset($data['img']);
    }

}
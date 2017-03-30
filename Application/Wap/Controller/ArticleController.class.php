<?php

namespace Wap\Controller;

use Base\Controller\BaseController;

use Wap\Model\ArticleThumbModel;

/**
 * 圈子文章管理
 */

class ArticleController extends BaseController
{
    private $articleModel;
    private $articleThumbModel;
    public function __construct()
    {
        parent::__construct();
        $this->articleModel = D('Article');
        $this->articleThumbModel = new ArticleThumbModel();
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
        $data = [
            'user_id' => $user_id,
            'article_id' => $article_id
        ];
        if ($thumb = $this->articleThumbModel->getData($data)) {
            $save['is_delete'] = '1';
            $thumb['is_delete'] && $save['is_delete'] = '0';
            $this->articleThumbModel->editData($where, $save);
        } elseif ($this->articleThumbModel->addData($data)) {
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

    public function getArticle($article_id)
    {
        $article = $this->articleModel->getData($article_id);
        $imgs = explode(',', $article['img']);
        $article['imgs'] = $imgs;
        $this->dealParam($article);
        return $article;
    }

    /**
     * 获取个人发表文章
     * @param int
     */
    public function getSelfList($user_id)
    {
        $where['user_id'] = $user_id;
    }

    /**
     * 获取加权推荐列表
     */
    public function getWeightList($user_id)
    {
        $articles = $this->articleModel->getAll();
        foreach ($articles as &$article) {
            $article['is_thumb'] = 0;
            $data['comment_id'] = $article['comment_id'];
            $data['user_id'] = session('plat_user_id');
            $data['is_delete'] = '0';
            $this->articleThumbModel->getData($data) && $article['is_thumb'] = 1;
            $article['user'] = D('UserInfo')->getUserInfo($article['user_id']);
            if ($user_id == $article['user_id']) {
                $article['user']['self'] = 1;
            } else {
                $article['user']['self'] = 0;
            }
            if ($this->isSubscribute($user_id, $article['user']['user_id'])) {
                $article['user']['subscribe'] = 1;
            } else {
                $article['user']['subscribe'] = 0;
            }
            $this->dealParam($article);
        }
        return $articles;
    }

    /**
     * 判断A用户是否关注B
     * @param int A
     * @param int B
     */
    public function isSubscribute($user_id, $subcribe)
    {
        $where = [
            'user_id' => $user_id,
            'subscribe_user' => $subcribe,
            'subscribe_state' => '1'
        ];
        if (D('Subscribe')->where($where)->find()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 结果数组处理
     * @param int
     */
    private function dealParam(&$data)
    {
        $imgs = explode(',', $data['img']);
        $data['imgs'] = $imgs;
        unset($data['modified_time']);
        unset($data['is_delete']);
        unset($data['user_id']);
        unset($data['img']);
    }

}
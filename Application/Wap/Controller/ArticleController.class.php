<?php

namespace Wap\Controller;

use Base\Controller\BaseController;

/**
 * 圈子文章管理
 */

class ArticleController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 发布圈子动态
     * @param array 
     */
    public function createArticle($data)
    {
        if (!empty($data['imgs'])) {
            $data['img'] = [];
            foreach ($data['imgs'] as $baseImg) {
                $img = base64Img($baseImg, './social/article/');
                array_push($data['img'], $img);
            }
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
        if ($is_thumb = $this->articleThumbModel->where($data)->getField('is_delete')) {
            $save['is_delete'] = '1';
            $is_thumb && $save['is_delete'] = '0';
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

    public function getArticle($user_id)
    {
        $where['user_id'] = $user_id;
    }

}
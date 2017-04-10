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

    private function sendNotive($openids, $article_id, $nickname)
    {
        foreach ($openids as $openid) {
            $array=[
                'openid'=> $openid['new_openid'],
                'url'=>'http://www.koudaidaxue.com/index.php/Wap/index/index#/detail?id='.$article_id.'&from=share',
                'first'=>'你关注的媒体人有新动态
                         ',
                'keyword1'=>$nickname.'的动态',
                'keyword2'=>date('m-d H:i').'更新',
                'remark'=>'
点此查看详情'
            ];
            $obj = new \Base\Controller\WetchatApiController();
            $obj->publicId = 'gh_243fe4c4141f';
            $obj->setSubscribeTemplate($array);
        }
    }

    /**
     * 发布圈子动态
     * @param array 
     */
    public function createArticle($data)
    {
        if (!empty($data['url'])) {
            if (strpos($data['url'], 'ttps://')) {
                $http = 'https://';
            } else {
                $http = 'http://';
            }
            $arr = explode($http, $data['url']);
            $data['url'] = $http.array_pop($arr);
        }
        if (!empty($data['imgs'])) {
            $data['img'] = '';
            foreach ($data['imgs'] as $baseImg) {
                $img = base64Img($baseImg, './social/article/');
                $data['img'] .= ',' . $img;
            }
            $data['img'] = ltrim($data['img'], ',');
        }
        unset($data['imgs']);
        if (!empty($data['publicname'])) {
            $where['user_name'] = $data['publicname'];
            $public = D('Base/Public')->where($where)->getField('nick_name');
            $data['publicname'] = $public;
        }
        if ($article_id = $this->articleModel->addData($data)) {
            $openids = D('Conf')->getSubscribeOpenid($data['user_id']);
            $nickname = D('UserInfo')->where(['user_id'=>$data['user_id']])->getField('nickname');
            $this->sendNotive($openids, $article_id, $nickname);
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
        $this->dealParam($article);
        return $article;
    }

    public function getSubscribeArticle($user_id, $page)
    {
        $subscribes = D('Subscribe')->field('subscribe_user')
        ->where(array('user_id'=>$user_id,'subscribe_state'=>'1'))->select();
        $subscribes = array_column($subscribes, 'subscribe_user');
        $subscribes = implode(',', $subscribes);
        $where['user_id'] = ['in', $subscribes];
        $articles = $this->articleModel->getAll($where, $page);
        foreach ($articles as &$article) {
            $this->dealParam($article);
        }
        return $articles;
    }

    public function getThumbArticle($user_id, $page)
    {
        $where = [
            't.user_id' => $user_id,
            'state' => 1
        ];
        $articles = $this->articleThumbView->getAll($where, $page);
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
    public function getNewList($page)
    {
        $articles = $this->articleModel->getAll(array(), $page);
        foreach ($articles as &$article) {
            $this->dealParam($article);
        }
        return $articles;
    }

    /**
     * 获取加权动态列表
     * @param int 用户
     */
    public function getWeightList($user_id, $page = 1)
    {
        $key = $user_id.'->'.$page;
        if ($articles = $this->getRedisCache($key)) {
            return $articles;
        } else {
            $articles = $this->articleModel->getAll($where, $page);
            foreach ($articles as $key => &$article) {
                $article['weight'] = $article['thumb'] * 3 + $article['comment'] * 3 + $article['user']['subscribe'] * 4 + 20-$key;
                $this->dealParam($article);
            }
            $this->upRedisCache($key, $articles);
            return $articles;
        }
    }

    /**
     * 获取热门动态
     */
    public function getHotList()
    {
        $articles = $this->articleModel->getAll($where);
        foreach ($articles as $key => &$article) {
            $this->dealParam($article);
        }
        return $articles;
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
        /* --------------------- 点赞状态处理 -------------------*/
        $user_id = session('plat_user_id');
        $data['is_thumb'] = 0;
        $where['article_id'] = $data['article_id'];
        $where['user_id'] = $user_id;
        $where['state'] = 1;
        $this->articleThumbModel->getData($where) && $data['is_thumb'] = 1;
        /* --------------------- 关注状态处理 -------------------*/
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
        /* --------------------- 图片和公众号处理 -------------------*/
        if (!empty($data['publicname'])) {
            $data['user']['publicname'] = $data['publicname'];
        }
        $data['imgs'] = [];
        if (!empty($data['img'])) {
            $imgs = explode(',', $data['img']);
            foreach ($imgs as &$img) {
                $img = 'http://'.C('CDN_SITE').$img;
            }
            $data['imgs'] = $imgs;
        }
        /* --------------------- 过滤数据 -------------------*/
        unset($data['publicname']);
        unset($data['modified_time']);
        unset($data['is_delete']);
        unset($data['user_id']);
        unset($data['img']);
    }

}
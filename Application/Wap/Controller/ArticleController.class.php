<?php

namespace Wap\Controller;

use Wap\Controller\CommonController;
use Wap\Model\PublicModel;
use Wap\Model\ArticleThumbModel;
use Wap\Model\ArticleThumbViewModel;
use Wap\Model\PublicSubscribeModel;

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

    private function sendNotive($openids, $article_id, $nickname, $content)
    {
        foreach ($openids as $openid) {
            $array=[
                'openid'=> $openid['openid'],
                'url'=>"http://www.koudaidaxue.com/index.php/wap/index/index?page=detail?id={$article_id}",
                'first'=>$nickname.'有更新
                         ',
                'keyword1'=> $content.'...',
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
            $data['publicname'] = session('plat_public_id');
        }
        if ($article_id = $this->articleModel->addData($data)) {
            $openids = D('Conf')->getSubscribeOpenid($data['user_id']);
            $nickname = D('UserInfo')->where(['user_id'=>$data['user_id']])->getField('nickname');
            $this->sendNotive($openids, $article_id, $nickname, mb_substr($data['content'], 0, 10));
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
                } else {
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
            if (is_numeric($article['user_id'])) {
                $this->dealParam($article);
            } else {
                $this->dealWenzhang($article);
            }
        return $article;
    }

    public function getSubscribeArticle($user_id, $page)
    {
        $subscribes = D('Subscribe')->field('subscribe_user')
        ->where(array('user_id'=>$user_id,'subscribe_state'=>'1'))->select();
        $sbWh['user_id'] = $user_id;
        $sbWh['state'] = 1;
        $subPublics = D('PublicSubscribe')->getAll($sbWh);
        $subPublics = array_column($subPublics, 'public_id');
        $subscribes = array_column($subscribes, 'subscribe_user');
        foreach ($subPublics as $value) {
            array_push($subscribes, $value);
        }
        $subscribes = implode(',', $subscribes);
        $where['user_id'] = ['in', $subscribes];
        $where['is_delete'] = 0;
        $articles = $this->articleModel->getAll($where, $page);
        foreach ($articles as &$article) {
            if (is_numeric($article['user_id'])) {
                $this->dealParam($article);
            } else {
                $this->dealWenzhang($article);
            }
        }
        return $articles;
    }

    public function getThumbArticle($user_id, $page)
    {
        $where = [
            't.user_id' => $user_id,
            'state' => 1,
            'a.is_delete' => ['neq', 1]
        ];
        $articles = $this->articleThumbView->getAll($where, $page);
        foreach ($articles as &$article) {
            if (is_numeric($article['user_id'])) {
                $this->dealParam($article);
            } else {
                $this->dealWenzhang($article);
            }
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
            if (is_numeric($article['user_id'])) {
                $this->dealParam($article);
            } else {
                $this->dealWenzhang($article);
            }
        }
        return $articles;
    }

    /**
     * 获取最新动态列表
     * @param int 用户
     */
    public function getNewList($page)
    {
        $where['is_delete'] = 0;
        $articles = $this->articleModel->getAll($where, $page);
        foreach ($articles as &$article) {
                if (is_numeric($article['user_id'])) {
                    $this->dealParam($article);
                } else {
                    $this->dealWenzhang($article);
                }
        }
        return $articles;
    }

    /**
     * 获取加权动态列表
     */
    public function getWeightList($page = 1)
    {
        $key = $page;
        if (0 && $articles = $this->getRedisCache($key)) {
            return $articles;
        } else {
            $where['is_delete'] = 0;
            $articles = $this->articleModel->All($where);
            foreach ($articles as &$article) {
                $article['weight'] = $this->weightParam($article);
                if (is_numeric($article['user_id'])) {
                    $this->dealParam($article);
                } else {
                    $this->dealWenzhang($article);
                }
            }
            usort($articles, descSort('weight'));
            $count = 0;
            $page = 1;
            $data = [];
            foreach ($articles as $value) {
                $data[] = $value;
                if (++$count == 20) {
                    $count = 0;
                    $ky = $page;
                    $this->upRedisCache($ky, $data);
                    $data = [];
                    $page++;
                }
            } 
        }
        return $this->getRedisCache($key);
    }

    /**
     * 获取热门动态
     */
    public function getHotList()
    {
        $where['is_delete'] = 0;
        $where['content'] = ['not like', '大家好%'];
        $articles = $this->articleModel->getAll($where);
        foreach ($articles as &$article) {
            if (is_numeric($article['user_id'])) {
                $this->dealParam($article);
            } else {
                $this->dealWenzhang($article);
            }
        }
        return $articles;
    }

    private function weightParam($article)
    {
        if (empty($article)) {
            return;
        }
        $time_weight = (($article['create_time'] - strtotime(date('Y-m-d')))/86400)*25;
        $gh_weight = 0;
        $img_weight = 5;
        $url_weight = 5;
        (is_numeric($article['user_id'])) && $gh_weight = 2.5;
        empty($article['img']) && $img_weight = 0;
        empty($article['url']) && $url_weight = 0;
        $thumb_weight = $article['thumb'] * 1.5;
        $comment_weight = $article['comment'] * 1;
        $weight = floor($time_weight + $gh_weight + $img_weight + $url_weight + $thumb_weight + $comment_weight);
        return $weight;
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
            $public = D('Public')->where(['user_name'=>$data['publicname']])->find();
            $data['user']['publicname'] = $public['nick_name'];
            $data['user']['public_id'] = $public['user_name'];
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
        $data['type'] = 0;
        unset($data['publicname']);
        unset($data['modified_time']);
        unset($data['is_delete']);
        unset($data['user_id']);
        unset($data['img']);
    }

    /**
     * 公众号结果数组处理
     */
    private function dealWenzhang(&$data)
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
        $publicSubscribe = new PublicSubscribeModel();
        $publicModel = new PublicModel();
        $pubWh['user_name'] = $data['user_id'];
        $data['user'] = $publicModel->getData($pubWh);
        $psWh = [
            'user_id' => $user_id,
            'public_id' => $data['user_id'],
            'state' => 1
        ];
        $data['user']['subscribe'] = D('PublicSubscribe')->where($psWh)->getField('state')?:0;
        $data['user']['self'] = 0;
        /* --------------------- 过滤数据 -------------------*/
        $data['type'] = 1;
        $data['imgs'] = [];
        $data['user']['public_id'] = $data['user']['user_name'];
        $data['user']['publicname'] = $data['user']['nick_name'];
        unset($data['publicname']);
        unset($data['modified_time']);
        unset($data['is_delete']);
        unset($data['user_id']);
        unset($data['img']);
        unset($data['unique_id']);
        unset($data['user']['id']);
        unset($data['user']['user_name']);
        unset($data['user']['nick_name']);
    }
}

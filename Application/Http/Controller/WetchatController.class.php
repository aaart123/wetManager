<?php
namespace Http\Controller;

use Http\Controller\BaseController;
use Base\Controller\WetchatApiController;

use Base\Model\NewsModel;
use Base\Model\MediaModel;

class WetchatController extends BaseController
{
    private $wetApi;
    private $publicId;
    private $newsModel;
    private $mediaModel;
    public function __construct()
    {
        parent::__construct();
        $this->publicId = session('plat_public_id');
        $this->publicId = 'gh_19fb1bed539e';
        $this->wetApi = new WetchatApiController();
        $this->newsModel = new NewsModel();
        $this->mediaModel = new MediaModel();
        $this->mediaModel->publicId = $this->publicId;
        $this->newsModel->publicId = $this->publicId;
        $this->wetApi->publicId = $this->publicId;
    }

    public function getMenu()
    {
        // 获取菜单信息
            // http://www.koudaidaxue.com/index.php/http/wetchat/getMenu
        $data = $this->wetApi->getMenuInfo();
        echo json_encode([
            'errcode' => 0,
            'errmsg' => $data
        ]);
        exit;
    }

    public function createMenu()
    {
        $post = I('post.');
        // 创建菜单
            // http://www.koudaidaxue.com/index.php/http/wetchat/createMenu
            // Array
            // (
            //     [button] => Array
            //         (
            //             [0] => Array
            //                 (
            //                     [name] => 菜单
            //                     [sub_button] => Array
            //                         (
            //                             [0] => Array
            //                                 (
            //                                     [type] => view
            //                                     [name] => 搜索
            //                                     [url] => http://www.soso.com/
            //                                 )

            //                             [1] => Array
            //                                 (
            //                                     [type] => view
            //                                     [name] => 视频
            //                                     [url] => http://v.qq.com/
            //                                 )
            //                         )
            //                 )
            //         )
            // )
        $post = json_encode($post);
        $data = $this->createMenu($post);
        echo josn_encode($data);
        exit;
    }

    public function syncMaterial($type)
    {
        // 同步素材type:image图片;news图文;video视频;voice语音
            // http://www.koudaidaxue.com/index.php/http/wetchat/syncMaterial?type=image
        $arrCount = $this->wetApi->getMediaCount();
        $count = floor(($arrCount["{$type}_count"] + 19) / 20);
        $msg = [];
        while ($count) {
            $data = $this->wetApi->getMediaList($type, ($count-1)*20);
            $items = $data['item'];
            foreach ($items as $item) {
                if ($type=='news') {
                    echo json_encode([
                        'errcode' => 10001,
                        'msg' => '暂时不支持微信图文同步'
                    ]);
                    exit;
                } else {
                    $where['media_id'] = $item['media_id'];
                    $where['public_id'] = $this->publicId;
                    $data = [
                        'public_id' => $this->publicId,
                        'media_id' => $item['media_id'],
                        'name' => $item['name'],
                        'url' => $item['url'],
                        'type' => $type,
                        'update_time' => $item['update_time']
                    ];
                    if ($this->mediaModel->getData($where)) {
                        unset($data['media_id']);
                        unset($data['public_id']);
                        $this->mediaModel->editData($where, $data);
                    } else {
                        $this->mediaModel->addData($data);
                    }
                }
                $msg[] = $item;
            }
            $count--;
        }
        echo json_encode([
            'errcode' => 0,
            'errmsg' => $msg
        ]);
        exit;
    }

    public function createNews()
    {
        $post = I('post.');
        // 创建图文链接
            // http://www.koudaidaxue.com/index.php/http/wetchat/createNews
            // $post = [
            //     '0' => [
            //         'title' => '第一个item的题目',
            //         'description' => '第一个item的描述',
            //         'picurl' => '图片链接',
            //         'url' => '跳转链接'
            //     ],
            //     '1' => [
            //         'title' => '第二个item的题目',
            //         'description' => '第二个item的描述',
            //         'picurl' => '图片链接',
            //         'url' => '跳转链接'
            //     ],
            //     '2' => [
            //         'title' => '第三个item的题目',
            //         'description' => '第三个item的描述',
            //         'picurl' => '图片链接',
            //         'url' => '跳转链接'
            //     ],
            //     '3' => [
            //         'title' => '第四个item的题目',
            //         'description' => '第四个item的描述',
            //         'picurl' => '图片链接',
            //         'url' => '跳转链接'
            //     ],
            //     '4' => [
            //         'title' => '第五个item的题目',
            //         'description' => '第五个item的描述',
            //         'picurl' => '图片链接',
            //         'url' => '跳转链接'
            //     ]
            // ];
        if (count($post, 0) > 10) {
            echo json_encode([
                'errcode' => 20001,
                'errmsg' => '一条图文不能超过10个item'
            ]);
            exit;
        }
        $newsId = $this->newsModel->addNewsItem($post, 0);
        echo json_encode([
            'errcode' => 0,
            'errmsg' => $newsId
        ]);
        exit;
    }

    public function getNewsList()
    {
        // 获取图文链接列表
            // http://www.koudaidaxue.com/index.php/http/wetchat/getNewsList
        $this->newsModel->getNewsList($newsList);
        echo json_encode([
            'errcode' => 0,
            'errmsg' => $newsList
        ]);
        exit;
    }

    public function getNewsData($newsId)
    {
        // 获取图文链接信息
            // http://www.koudaidaxue.com/index.php/http/wetchat/getNewsData?newsId=1
        $this->newsModel->getNewsItem($news, $newsId);
        echo json_encode([
            'errcode' => 0,
            'errmsg' => $news
        ]);
        exit;
    }

    public function updateNews()
    {
        $post = I('post.');
        // 更新图文链接
            // http://www.koudaidaxue.com/index.php/http/wetchat/updateNews
            // $post = [
            //     '0' => [
            //         'title' => '1个item的题目',
            //         'description' => 'item的描述',
            //         'picurl' => '图片链接',
            //         'url' => '跳转链接',
            //         'newsId' => '1',
            //         'lastid' => '0'
            //     ],
            //     '2' => [
            //         'title' => '3个item的题目',
            //         'description' => 'item的描述',
            //         'picurl' => '图片链接',
            //         'url' => '跳转链接'
            //     ],
            //     '3' => [
            //         'title' => '4个item的题目',
            //         'description' => 'item的描述',
            //         'picurl' => '图片链接',
            //         'url' => '跳转链接'
            //     ]
            // ];
        if (count($post, 0) > 10) {
            echo json_encode([
                'errcode' => 20001,
                'errmsg' => '一条图文不能超过10个item'
            ]);
            exit;
        }
        $item = array_shift($post);
        $where['newsId'] = $item['newsId'];
        $this->newsModel->deleteNewsItem($item['newsId']);
        $this->newsModel->editData($where, $item);
        $this->newsModel->addNewsItem($post, $item['newsId']);
        echo json_encode([
            'errcode' => 0,
            'errmsg' => $item['newsId']
        ]);
        exit;
    }

    public function deleteNews($newsId)
    {
        // 删除图文链接
            // http://www.koudaidaxue.com/index.php/http/wetchat/deleteNews?newsId=1
        $where['news_id'] = $newsId;
        $save['lastid'] = -1;
        if ($this->newsModel->editData($where, $save)) {
            echo json_encode([
                'errcode' => 0,
                'errmsg' => '成功'
            ]);
            exit;
        } else {
            echo json_encode([
                'errcode' => 10002,
                'errmsg' => '失败'
            ]);
            exit;
        }
    }
    
}

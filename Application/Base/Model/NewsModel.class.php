<?php
namespace Base\Model;

use Base\Model\BaseModel;

/**
 * 图文模型
 */
class NewsModel extends BaseModel
{
    protected $trueTableName = 'kdgx_plat_news';

    protected $_map = [
        'newsId' => 'news_id',
        'publicId' => 'public_id'
    ];

    protected $publicId;

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    /**
     * 添加数据
     * @param array 数据数组
     */
    public function addData($data)
    {
        !$this->create($data) && E($this->getError());
        return $this->add();
    }

    /**
     * 修改数据
     * @param array 条件数组
     * @param array 包含主键的数组
     */
    public function editData($where, $data)
    {
        if (isset($data['newsId'])) {
            unset($data['newsId']);
        }
        !$this->create($data) && E($this->getError());
        return $this->where($where)->save();
    }

    /**
     * 删除数据
     * @param array 条件数组
     */
    public function deleteData($where)
    {
        return $this->where($where)->delete();
    }

    /**
     * 获取find数组
     * @param array 条件数组
     */
    public function getFind($where)
    {
        if (empty($where)) {
            return false;
        }
        $data = $this->where($where)->find();
        return $this->parseFieldsMap($data);
    }

    /**
     * 获取select数组
     * @param array 条件数组
     */
    public function getSelect($where)
    {
        if (empty($where)) {
            return false;
        }
        $data = $this->where($where)->select();
        return $this->parseFieldsMap($data);
    }
    
    /**
     * 递归添加链式结构的图文
     * @param array 图文数组
     * @param int 上一个item的id
     * @param int 图文id地址
     * @return int 该图文的第一个item的id
     */
    public function addNewsItem(array $news, int $lastid)
    {
        while (!empty($news)) {
            $item = array_shift($news);
            $item['public_id'] = $this->publicId;
            $item['lastid'] = $lastid;
            if (isset($item['newsId'])) {
                unset($item['newsId']);
            }
            $lastid = $this->addData($item);
            $this->addNewsItem($news, $lastid);
            return $lastid;
        }
        return $lastid;
    }

    /**
     * 获取图文链接列表
     * @param int 存储结果值的地址
     */
    public function getNewsList(&$newsList)
    {
        $where = [
            'public' => $this->publicId,
            'lastid' => 0
        ];
        $items = $this->getSelect($where);
        foreach ($items as $item) {
            $this->getNewsItem($newsList[], $item['newsId']);
        }
    }

    /**
     * 递归获取一条图文的item链
     * @param int 存储结果值的地址
     * @param int 上一个item的id
     * @param boolean 是否是第一个item
     */
    public function getNewsItem(&$news, int $lastid, $flag = true)
    {
        $where = [
            'public_id' => $this->publicId,
        ];
        if ($flag) {
            $where['news_id'] = $lastid;
        } else {
            $where['lastid'] = $lastid;
        }
        $item = $this->getFind($where);
        while (!empty($item)) {
            $news[] = $item;
            return $this->getNewsItem($news, $item['newsId'], false);
        }
    }

    /**
     * 删除图文item
     * @param int 上一个item的id
     */
    public function deleteNewsItem(int $lastid)
    {
        $where = [
            'public_id' => $this->publicId,
            'lastid' => $lastid
        ];
        $item = $this->getFind($where);
        while (!empty($item)) {
            $this->deleteData($where);
            return $this->deleteNewsItem($item['newsId']);
        }
        return true;
    }

}

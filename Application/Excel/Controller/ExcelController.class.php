<?php

namespace Excel\Controller;
use Think\Controller;
use Excel\Model\InfoModel;
use Excel\Model\PublicViewModel;

class ExcelController extends Controller
{
    private $publicModel;
    private $infoModel;
    private $publicViewModel;
    public function __construct()
    {
        parent::__construct();
        $this->infoModel = new InfoModel();
        $this->publicViewModel = new PublicViewModel();
    }

    public function main()
    {
        $this->display();
    }

    public function docm()
    {
        $this->display();
    }

    public function docmls()
    {
        $this->display();
    }

    public function mobileList()
    {
        // 获取资源表格
            // http://www.koudaidaxue.com/index.php/Excel/excel/mobileList?id=1
        $where = [];
        if (!empty($_GET['id'])) {
            $where['id'] = $_GET['id']; 
        }
        $where['is_connect'] = ['neq', 0];
        $list = $this->publicViewModel->where($where)->order('sort desc')->select();
        foreach ($list as $key => $value) {
            $temp = $this->infoModel->where(['pid'=>$value['id']])->find();
            $data = [
                'province' => $temp['province'],
                'city' => $temp['city'],
                'school' => $temp['school'],
                'area' => $temp['area'],
                'type' => $temp['type'],
                'number' => $temp['number'],
                'fans' => $temp['fans'],
                'price_one' => $temp['price_one'],
                'price_two' => $temp['price_two'],
                'level' => $temp['level']
            ];
            unset($value['description']);
            unset($value['is_connect']);
            unset($value['is_media']);
            unset($value['is_newrank']);
            unset($value['timestamp']);
            unset($value['modified_time']);
            unset($value['owner']);
            $list[$key] = array_merge($value, $data);
        }
        echo json_encode([
            'errcode' => 0,
            'errmsg' => $list
        ]);
    }

    public function getList()
    {
        // 获取资源表格
            // http://www.koudaidaxue.com/index.php/Excel/excel/getList?id=1&page=1
        $content = $_POST['content'];
        $where = [];
        if (!empty($content)) {
            $content = '%'.$content.'%';
            $wh = [
                'public_name' => ['like', $content],
                'public_id' => ['like', $content],
                'alias_id' => ['like', $content],
                '_logic' => 'or'
            ];
            $where['_complex'] = $wh;
        }
        $firstRow = (I('get.page',1) - 1) * 40;
        if (!empty($_GET['id'])) {
            $where['id'] = $_GET['id']; 
        }
        $list = $this->publicViewModel->where($where)->order('sort desc')->limit($firstRow, 40)->select();
        foreach ($list as $key => $value) {
            $temp = $this->infoModel->where(['pid'=>$value['id']])->find();
            $data = [
                'province' => $temp['province'],
                'city' => $temp['city'],
                'school' => $temp['school'],
                'area' => $temp['area'],
                'type' => $temp['type'],
                'number' => $temp['number'],
                'fans' => $temp['fans'],
                'price_one' => $temp['price_one'],
                'price_two' => $temp['price_two'],
                'f_owner' => $temp['owner'],
                'u_timestamp' => $temp['timestamp'],
                'level' => $temp['level']
            ];
            $list[$key] = array_merge($value, $data);
        }
        echo json_encode([
            'errcode' => 0,
            'errmsg' => $list
        ]);
    }

    public function add()
    {
        $post = I('post.');
        // 添加资源表格
            // http://www.koudaidaxue.com/index.php/Excel/excel/add
            // $post = [
            //     'public_id' => '授权方公众号的原始ID',
            //     'public_name' => '公众号昵称',
            //     'alias_id' => '公众号所设置的微信号',
            //     'description' => '简介',
            //     'owner' => '公众号的主体名称',
            //     'status' => 0, #认证状态0未认证;1认证
            //     'is_connect' => 0, #是否联系
            //     'is_media' => 0, #是否新媒
            //     'sort' => 0 #排序
            //     'province' => 1, #省份
            //     'city' =>24, #城市
            //     'school' =>12, #学校
            //     'area' => '大学城',
            //     'type' => 0, #类型
            //     'number' =>1000, #在校生
            //     'fans' => 123, #粉丝数量
            //     'price_one'=>1233, #头条报价
            //     'price_two' =>1222, #次条报价
            //     'f_owner' => 18852963711, #联系方式
            //     'level' => 1 #质量
            // ];
        $publicData = [
            'public_id' => $post['public_id'],
            'public_name' => $post['public_name'],
            'alias_id' => $post['alias_id'],
            'description' => $post['description'],
            'owner' => $post['owner'],
            'status' => $post['status'],
            'is_connect' => $post['is_connect'],
            'is_media' => $post['is_media'],
            'sort' => $post['sort']
        ];
        $infoData = [
            'province' => $post['province'],
            'city' => $post['city'],
            'school' => $post['school'],
            'area' => $post['area'],
            'type' => $post['type'],
            'number' => $post['number'],
            'fans' => $post['fans'],
            'price_one' => $post['price_one'],
            'price_two' => $post['price_two'],
            'owner' => $post['f_owner'],
            'level' => $post['level']
        ];
        foreach ($publicData as $key => $value) {
            if ($value == null) {
                unset($publicData[$key]);
            }
        }
        if (!empty($publicData)) {
            $publicData['timestamp'] = time();
            $this->publicViewModel->create($publicData);
            $id = $this->publicViewModel->add();
        }
        foreach ($infoData as $k => $v) {
            if ($v == null) {
                unset($infoData[$k]);
            }
        }
        if (!empty($infoData)) {
            $infoData['timestamp'] = time();
            $infoData['pid'] = $id;
            $this->infoModel->create($infoData);
            $this->infoModel->add();
        }
        echo json_encode([
            'errcode' => 0,
            'errmsg' => $id
        ]);
    }

    public function edit($id)
    {
        $post = I('post.');
        // 更新资源表格
            // http://www.koudaidaxue.com/index.php/Excel/excel/edit?id=102
            // $post = [
                // 'public_id' => '授权方公众号的原始ID',
                // 'public_name' => '公众号昵称改',
                // 'alias_id' => '公众号所设置的微信号',
                // 'description' => '简介',
                // 'owner' => '公众号的主体名11称',
                // 'status' => 0, #认证状态0未认证;1认证
                // 'is_connect' => 0, #是否联系
                // 'is_media' => 0, #是否新媒
                // 'sort' => 12 #排序
                // 'province' => 11, #省份
                // 'city' =>24, #城市
                // 'school' =>12, #学校
                // 'area' => '大学城改',
                // 'type' => 0, #类型
                // 'number' =>1000, #在校生
                // 'fans' => 123, #粉丝数量
                // 'price_one'=>1233, #头条报价
                // 'price_two' =>1222, #次条报价
                // 'f_owner' => 18852963711, #联系方式
                // 'level' => 66 #质量
            // ];
        $publicData = [
            'public_id' => $post['public_id'],
            'public_name' => $post['public_name'],
            'alias_id' => $post['alias_id'],
            'description' => $post['description'],
            'owner' => $post['owner'],
            'status' => $post['status'],
            'is_connect' => $post['is_connect'],
            'is_media' => $post['is_media'],
            'sort' => $post['sort']
        ];
        $infoData = [
            'province' => $post['province'],
            'city' => $post['city'],
            'school' => $post['school'],
            'area' => $post['area'],
            'type' => $post['type'],
            'number' => $post['number'],
            'fans' => $post['fans'],
            'price_one' => $post['price_one'],
            'price_two' => $post['price_two'],
            'owner' => $post['f_owner'],
            'level' => $post['level']
        ];
        foreach ($publicData as $key => $value) {
            if ($value == null) {
                unset($publicData[$key]);
            }
        }
        if (!empty($publicData)) {
            $publicData['id'] = $id;
            $data  = $this->publicViewModel->create($publicData);
            $this->publicViewModel->save();
        }
        foreach ($infoData as $k => $v) {
            if ($v == null) {
                unset($infoData[$k]);
            }
        }
        if (!empty($infoData)) {
            $infoData['timestamp'] = time();
            $where['pid'] = $id;
            $data = $this->infoModel->create($infoData);
            if ($this->infoModel->where($where)->find()){
                $this->infoModel->where($where)->save($data);
            }else {
                $data['pid'] = $id;
                $this->infoModel->add($data);
            }
            
        }
        echo json_encode([
            'errcode' => 0,
            'errmsg' => ok
        ]);
    }

}
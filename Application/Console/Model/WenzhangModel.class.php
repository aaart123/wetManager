<?php

namespace Console\Model;

use Think\Model\RelationModel;

class WenzhangModel extends RelationModel
{
    protected $tableName = 'kdgx_wap_wenzhang';
    
    protected $_auto = array(
        array('modified_time', 'time', self::MODEL_UPDATE, 'function')
    );

    protected $_link = [
        'user' => [
            'mapping_type' => self::BELONGS_TO,
            'mapping_name' => 'user',
            'mapping_fields' => 'phone, openid',
            'class_name'   => 'User',
            'foreign_key'   => 'user_id'
        ]
    ];

    public function setCondition(array $where)
    {
        foreach ($where as $k => $v) {
            if ($this->_link[$k]) {
                $this->_link[$k]['condition'] = $v;
            }
        }
        return $this->_link;
    }


    public function addData($data)
    {
        !$this->create($data) && E($this->getError());
        var_dump($this->data);
        return $this->add('', array(), true);
    }
 
    public function editData($where, $save)
    {
        !$this->create($save) && E($this->getError());
        return $this->where($where)->save();
    }

    public function getAll($where = array(), $page = 1)
    {
        $where['is_delete'] = '0';
        $limit = ($page-1) * 20;
        $articles = $this->where($where)->order('create_time desc')->limit($limit, 20)->select();
        return $articles;
    }

    public function getData($comment_id, $all = true)
    {
        $all && $where['is_delete'] = '0';
        $where['comment_id'] = $comment_id;
        $article = $this->where($where)->find();
        return $article;
    }

    public function Insec($comment_id, $field)
    {
        $where['comment_id'] = $comment_id;
        return $this->where($where)->setInc($field, 1);
    }

    public function Desec($comment_id, $field)
    {
        $where['comment_id'] = $comment_id;
        return $this->where($where)->setDec($field, 1);  
    }
}
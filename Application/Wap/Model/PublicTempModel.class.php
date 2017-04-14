<?php

namespace Wap\Model;

use Think\Model\RelationModel;

class PublicTempModel extends RelationModel
{
    protected $tableName = 'kdgx_wap_public_temp';

    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
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

    public function addData($data)
    {
        !$this->create($data) && E($this->getError());
        return $this->add();
    }

    public function editData($where, $save)
    {
        !$this->create($save) && E($this->getError());
        return $this->where($where)->save();
    }

    public function getData($where)
    {
        $data = $this->where($where)->find();
        return $data;
    }

    public function getCount($where)
    {
        return $this->where($where)->count();
    }

    public function getAll($where = array(), $type = 0)
    {
        $hidden = [modified_time,is_delete];
        if (!$type) {
            array_push($hidden, 'secret');
        }
        $where['is_delete'] = 0;
        $publics = $this->field($hidden,true)->where($where)->order('create_time desc')->select();
        return $publics;
    }
}

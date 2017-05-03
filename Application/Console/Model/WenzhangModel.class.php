<?php

namespace Console\Model;

use Think\Model;

class WenzhangModel extends Model
{
    protected $tableName = 'kdgx_wap_wenzhang';
    
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('modified_time', 'time', self::MODEL_UPDATE, 'function')
    );

    public function addData($data)
    {
        !($data= $this->create($data)) && E($this->getError());
        return $this->add('', array(), true);
    }
 
    public function editData($where, $save)
    {
        !($data= $this->create($save)) && E($this->getError());
        return $this->where($where)->save($save);
    }

    public function getAll($where = array())
    {
        $articles = $this->where($where)->order('like_count desc')->select();
        return $articles;
    }

}
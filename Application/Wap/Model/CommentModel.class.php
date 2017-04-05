<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/3/28
 * Time: 11:57
 */

namespace Wap\Model;

use Think\Model\RelationModel;

class CommentModel extends RelationModel
{
    protected $tableName = 'kdgx_social_comment';
    
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('modified_time', 'time', self::MODEL_UPDATE, 'function')
    );

    protected $_link = [
        'article' => [
            'mapping_type' => self::BELONGS_TO,
            'mapping_name' => 'article',
            'mapping_fields' => 'article_id, create_time, user_id, content',
            'class_name'   => 'Article',
            'foreign_key'   => 'article_id'
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
        return $this->add();
    }
 
    public function editData($where, $save)
    {
        !$this->create($save) && E($this->getError());
        return $this->where($where)->save();
    }

    public function getAll($where = array())
    {
        $where['is_delete'] = '0';
        $articles = $this->where($where)->order('create_time desc')->select();
        return $articles;
    }

    public function getData($comment_id)
    {
        $where['is_delete'] = '0';
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
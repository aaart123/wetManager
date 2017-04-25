<?php

namespace Wap\Model;

use Wap\Model\BaseModel;

class PublicSubscribeModel extends BaseModel
{
    protected $tableName = 'kdgx_public_subscribe';

    public function addData($data)
    {
        !$this->create($data) && E($this->getError());
        return $this->add('', array(), true);
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

    public function getAll($where = array())
    {
        $publics = $this->where($where)->select();
        return $publics;
    }

    /**
     * 获取推荐的公众号信息
     * @param $userId
     * @param int $page
     * @return mixed
     */
    public function getRecommendPublic($userId,$page=0)
    {
        $limit = $page
            ?' LIMIT '.(($page-1)*20).',20'
            :'';
        $sql = 'SELECT COUNT(s.`user_id`) AS num,
                       p.`user_name` AS public_id,
                       p.`alias`,
                       p.`nick_name` AS publicname,
                       p.`head_img`
                 FROM `kdgx_wap_public` AS p
                 LEFT JOIN `kdgx_public_subscribe` AS s ON s.`public_id`= p.`user_name`
                 WHERE p.`user_name` IN(
                    SELECT `user_name`
                    FROM `kdgx_wap_public`
                    WHERE `user_name` NOT IN(
                        SELECT public_id
                        FROM `kdgx_public_subscribe`
                        WHERE `user_id`= '.$userId.'
                        AND state= 1))
                 GROUP BY p.`user_name`
                 ORDER BY COUNT(s.`user_id`) DESC'.$limit;
        $data = $this->query($sql);
        return $data;
    }
    
}
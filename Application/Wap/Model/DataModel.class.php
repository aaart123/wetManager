<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/4/22
 * Time: 11:18
 */

namespace Wap\Model;

use Wap\Model\BaseModel;

class DataModel extends BaseModel
{
    protected $tableName = 'kdgx_media_data';


    /**
     * 查看排行数据
     * @return mixed
     */
    public function getRankData()
    {
        $sql = 'SELECT d.`month_score`,
                       p.`user_name`,
                       p.`alias`,
                       p.`nick_name`,
                       p.`head_img`
                FROM `kdgx_media_data` AS d
                INNER JOIN `kdgx_wap_public` AS p ON d.`public_id`= p.`user_name`
                WHERE `timestamp`=(
                    SELECT MAX(`timestamp`)
                    FROM `kdgx_media_data`)
                    AND `month_score`!= 0
                    ORDER BY `month_score` DESC';
        $data = $this->query($sql);
        return $data;
    }


    /**
     * 查看公众号排行名次
     * @param $publicId
     * @return mixed
     */
    public function getRank($publicId,$timestamp=null)
    {
        if($timestamp)
        {
            $sql = "SELECT COUNT(*)+ 1 as num
              FROM `kdgx_media_data`
             WHERE `timestamp`= $timestamp
               AND `month_score`>(
            SELECT `month_score`
              FROM `kdgx_media_data`
             WHERE `timestamp`= $timestamp
               AND `public_id`= '$publicId')";
        }else{
            $sql = "SELECT COUNT(*)+1 as num
              FROM `kdgx_media_data`
             WHERE `timestamp`=(
            SELECT MAX(`timestamp`)
              FROM `kdgx_media_data`)
               AND `month_score`>(
            SELECT `month_score`
              FROM `kdgx_media_data`
             WHERE `timestamp`=(
            SELECT MAX(`timestamp`)
              FROM `kdgx_media_data`)
               AND `public_id`= '$publicId')";
        }

        $data = $this->query($sql);
        $num = $data[0]['num'];
        return $num;
    }


    public function getLoginPublicUser($publicId)
    {
        $sql = "SELECT u.`openid` 
                FROM `kdgx_wap_conf` AS c
                INNER JOIN `kdgx_plat_user` AS u ON c.`user_id`= u.`user_id`
                WHERE  c.`is_daily` = '1' AND c.`login_public`= '$publicId'";
        $data = $this->query($sql);
        return $data;
    }


    public function getMediaData($publicId)
    {
        $sql = "SELECT d.`article_count`,
                   d.`article_clicks_count`,
                   d.`article_likes_count`,
                   d.`month_score`,
                   d.`timestamp`,
                   p.`user_name`,
                   p.`nick_name`,
                   p.`alias`,
                   p.`head_img`
              FROM `pocket`.`kdgx_media_data` AS d
              INNER JOIN `kdgx_wap_public` AS p ON d.`public_id`= p.`user_name`
             WHERE d.`public_id`= '$publicId'
             ORDER BY d.`timestamp` DESC
             LIMIT 0,7";
        $data = $this->query($sql);
        return $data;
    }

}
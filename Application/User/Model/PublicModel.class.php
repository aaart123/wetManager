<?php
namespace User\Model;

use Base\Model\AppModel;

/**
 * 公众号信息模型
 */
class PublicModel extends AppModel
{
	protected $tureTableName = 'kdgx_plat_public';

	public function __construct()
	{
		parent::__construct();
	}


    /**
     * 获取公众号信息
     * @param $publicId
     * @return mixed
     */
	Public function getPublicInfo($publicId )
	{
		return $this->where(array('public_id'=>$publicId))->find();
	}


}

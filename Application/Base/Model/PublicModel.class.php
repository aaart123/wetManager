<?php
namespace Base\Model;

use Base\Model\BaseModel;

class PublicModel extends BaseModel
{
    protected $trueTableName = 'kdgx_plat_public';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param arr 数据数组
     */
    public function addData($data)
    {
        !$this->create($data) && E($this->getError());
        return $this->add('', '', true);
    }

    public function getData($where)
    {
        $data = $this->where($where)->find();
        return $this->parseFieldsMap($data);
    }

}
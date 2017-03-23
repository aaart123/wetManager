<?php
namespace Base\Model;

use Base\Model\BaseModel;

class TextModel extends BaseModel
{

    protected $trueTableName = 'kdgx_plat_text';
    protected $_map = [
        'textId' => 'text_id',
        'createTime' => 'create_time',
        'modifiedTime' => 'modified_time'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function addData($data)
    {
        !$this->create($data) && E($this->getError());
        return $this->add();
    }

    public function editData($data)
    {
        !$this->create($data) && E($this->getError());
        return $this->save();
    }

    public function getData($textId)
    {
        $where = [
            'text_id' => $textId
        ];
        $data = $this->where($where)->find();
        return $this->parseFieldsMap($data);
    }

}
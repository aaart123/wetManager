<?php

namespace Wap\Model;

use Think\Model;

class BaseModel extends Model{

    protected $tablePrefix = 'kdgx_social_';


    protected $_auto = array(
        array('create_time','time','function'),
        array('modified_time','time',2,'function'),
    );


}


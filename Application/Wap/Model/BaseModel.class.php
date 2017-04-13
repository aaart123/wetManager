<?php

namespace Wap\Model;

use Think\Model;

class BaseModel extends Model{
    
    protected $_auto = array(
        array('create_time','time',self::MODEL_INSERT,'function'),
        array('modified_time','time',self::MODEL_UPDATE,'function'),
    );


}


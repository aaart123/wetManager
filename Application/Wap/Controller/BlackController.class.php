<?php

namespace Wap\Controller;

use Wap\Controller\CommonController;

use Wap\Model\BlackModel;
use Wap\Model\BlackInfoModel;

class BlackController extends CommonController
{
    protected $blackModel;
    protected $blackInfoModel;
    public function __construct()
    {
        parent::__construct();
        $this->blackModel = new BlackModel();
        $this->blackInfoModel = new BlackInfoModel();
    }

    public function changeBlack($black, $user_id)
    {
        $data = [
            'black_id' => $black,
            'user_id' => $user_id
        ];
        if ($info = $this->blackInfoModel->where($data)->find()) {
            $save['state'] = 1;
            $info['state'] && $save['state'] = 0;
            $this->blackInfoModel->editData($data, $save);
        } else {
            $data['state'] = 1;
            $this->blackInfoModel->addData($data);
        }
        $count = $this->blackInfoModel->where($data)->count();
        if ($count>9 && ($data['state'] || $save['state'])) {
            $this->dealBlack($count, $black);
        }
        return true;
    }

    public function dealBlack($count, $black)
    {
        switch ($count) {
            case 10:
                $times = 1;
                $time = strtotime('+7 days');
                break;
            case 20:
                $times = 2;
                $time = strtotime('+10 days');
                break;
            case 30:
                $times = 3;
                $time = -1;
                break;
        }
        $data = [
            'black' => $black,
            'end_time' => $time,
            'count' => $times
        ];
        if ($black = $this->blackModel->where(['black'=>$black])->find()) {
            if ($count==10) {
                return true;
            }elseif($count==20 && $black['count'] > 1) {
                return true;
            }
            $this->blackModel->editData(['black'=>$black], $data);
            return true;
        } else {
            $data['count'] = 1;
            $this->blackModel->addData($data);
            return true;
        }
    }

    public function isBlack($black)
    {
        if ($black = $this->blackModel->where(['black'=>$black])->find()) {
            if ($black['end_time'] == -1) {
                return true;
            } elseif($black['end_time'] > time()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
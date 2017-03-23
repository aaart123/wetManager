<?php
namespace Common\Common;

use \Think\Controller;

class AuthBaseController extends Controller
{

    protected $config = array(
        'component_appid'=>'wx19961250208a65e8',
        'component_appsecret'=>'add64778f6ed9983582f227ed77d49aa',
        );

    public function __construct($cfg = array())
    {
        parent::__construct();
        $this->config = array_merge($this->config, $cfg);
    }

    private function httpRequest($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
    private function get_php_file($filename)
    {
        return trim(substr(file_get_contents($filename), 15));
    }
    private function set_php_file($filename, $content)
    {
        $fp = fopen($filename, "w");
        fwrite($fp, "<?php exit();?>" . $content);
        fclose($fp);
    }

    private function getComponent_access_token()
    {
        $data = json_decode($this->get_php_file("./Auth/api_component_token.php"));
        if ($data->expire_time < time()) {
            $url = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';
            $postData = array(
                'component_appid'=>$this->config['component_appid'],
                'component_appsecret'=>$this->config['component_appsecret'],
                );
        }
    }
}

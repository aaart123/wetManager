<?php

// 发送http请求
function httpRequest($url, $data = null, $header = array())
{
    $curl = curl_init();
    if (empty($header)) {
        $header = array("content-type: application/x-www-form-urlencoded;charset=UTF-8");
    }
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    if (!empty($data)) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    if ($output === false) {
        echo curl_error($curl);
        curl_close($curl);
        return false;
    }
    curl_close($curl);
    return $output;
}





/**
 * http请求
 * @param unknown $url
 * @param string $data
 * @return mixed
 */
function https_request($url, $data = null)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}





/**
 * 输出xml字符
 * @throws WxPayException
**/
function arr2Xml($values)
{
    $xml = "<xml>";
    foreach ($values as $key => $val) {
        if (is_numeric($val)) {
            $xml.="<".$key.">".$val."</".$key.">";
        } else {
            $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
        }
    }
    $xml.="</xml>";
    return $xml;
}
  
/**
 * 将xml转为array
 * @param string $xml
 * @throws WxPayException
 */
function xml2Arr($xml)
{
    //将XML转为array
    $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $values;
}

// unicode转utf8
function unicode2Utf8($c)
{
    if ($c < 0x80) {
         $utf8char = chr ( $c );
    } elseif ($c < 0x800) {
         $utf8char = chr (0xC0 | $c >> 0x06). chr (0x80 | $c & 0x3F);
    } elseif ($c < 0x10000) {
         $utf8char = chr (0xE0 | $c >> 0x0C). chr (0x80 | $c >> 0x06 & 0x3F). chr (0x80 | $c & 0x3F);
    } //因为UCS-2只有两字节，所以后面的情况是不可能出现的，这里只是说明unicode HTML实体编码的用法。
    else {
         $utf8char = "&#{$c};" ;
    }
    return $utf8char ;
}

// utf8转unicode
function utf82Unicode($str)
{
    $unicode = array();
    $values = array();
    $lookingFor = 1;
    for ($i = 0; $i < strlen( $str ); $i++) {
        $thisValue = ord( $str[ $i ] );
        if ($thisValue < ord('A')) {
            // exclude 0-9
            if ($thisValue >= ord('0') && $thisValue <= ord('9')) {
                 // number
                 $unicode[] = chr($thisValue);
            } else {
                 $unicode[] = '%'.dechex($thisValue);
            }
        } else {
            if ($thisValue < 128) {
                $unicode[] = $str[ $i ];
            } else {
                if (count( $values ) == 0) {
                    $lookingFor = ( $thisValue < 224 ) ? 2 : 3;
                }
                $values[] = $thisValue;
                if (count( $values ) == $lookingFor) {
                    $number = ( $lookingFor == 3 ) ?
                        ( ( $values[0] % 16 ) * 4096 ) + ( ( $values[1] % 64 ) * 64 ) + ( $values[2] % 64 ):
                        ( ( $values[0] % 32 ) * 64 ) + ( $values[1] % 64 );
                    $number = dechex($number);
                    $unicode[] = (strlen($number)==3)?"\u0".$number:"\u".$number;
                    $values = array();
                    $lookingFor = 1;
                } // if
            } // if
        }
    } // for
    return implode("", $unicode);
}


/**
    * 随机生成16位字符串
    * @return string 生成的字符串
    */
function getRandomStr()
{
    $str = "";
    $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $max = strlen($str_pol) - 1;
    for ($i = 0; $i < 16; $i++) {
        $str .= $str_pol[mt_rand(0, $max)];
    }
    return $str;
}


/**
 * 上传base64位图片
 */
function base64Img($base, $rootPath)
{
    $cfg['rootPath'] = $rootPath;
    $upload = new \Org\Util\UploadBase64($cfg);
    $info = $upload->upload($base);
    if (empty($info)) {
        return false;
    } else {
        $info = substr($info, 1);
        return $info;
    }
}

function ismobile() {
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
        return true;
    
    //此条摘自TPM智能切换模板引擎，判断是否为客户端
    if(isset ($_SERVER['HTTP_CLIENT']) &&'PhoneClient'==$_SERVER['HTTP_CLIENT'])
        return true;
    //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
        //找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
    //判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array(
            'nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'
        );
        //从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    //协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}

/**
 * 按关键字比较两个数组大小
 * @param array 第一个数组
 * @param array 第二个数组
 * @param string 关键字
 */
function descSort($key)
{
    return function ($x, $y) use ($key) {
        if($x[$key] > $y[$key]){
            return false;
        }elseif ($x[$key] < $y[$key]) {
            return true;
        }else{
            return 0;
        }
    };
}
<?php 

// 定义程序文件路径
define('APP_PATH', './Application/');

// 开启开发者调试模式
define('APP_DEBUG',true);
// 开发模式
define('DEV',false);

// 定义模板主题
define('DEFAULT_THEME','default');

// 定义模板文件默认目录
define('TMPL_PATH','./Template/'.DEFAULT_THEME.'/');

include './ThinkPHP/ThinkPHP.php';
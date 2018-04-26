<?php



spl_autoload_register(function ($class) { 

    $class_arr = explode("\\", $class);

    /* 根据类名确定文件名 */
    $file = end($class_arr).".php";

    /* 引入相关文件 */
    if (file_exists($file)) {
        include $file;
    }
});

\Chat\Login::deal();

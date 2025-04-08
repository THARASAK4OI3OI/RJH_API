<?php

/* ปิดการแสดง error */
	error_reporting(0);

/* เปิดการแสดง error */
	// error_reporting(E_ALL);

/* เรียกใช้งาน timezone */
	date_default_timezone_set("Asia/Bangkok");

/* เรียกใช้งาน router */
    require_once('./router/register/register.php');

/* function เช็คการเรียกใช้ class */
    function __autoload($class_name) {
        if (file_exists('./controllers/'.$class_name.'.php')) {
			require_once './controllers/'.$class_name.'.php';
		}else if (file_exists('./models/'.$class_name.'.php')) {
			require_once './models/'.$class_name.'.php';
		}else if (file_exists('./router/'.$class_name.'.php')) {
			require_once './router/'.$class_name.'.php';
		}else if (file_exists('./views/'.$class_name.'.php')) {
			require_once './views/'.$class_name.'.php';
		}
    }

?>
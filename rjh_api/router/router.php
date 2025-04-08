<?php 

    class router{
        public static $validroutes = array();
        public static function set($route,$function){
            require_once('header_access.php');
            require_once('db.php');
            $validroutes[] = $route;
            if($_GET['url'] == $route){
                $function->__invoke();
            }
        }
    }

?>
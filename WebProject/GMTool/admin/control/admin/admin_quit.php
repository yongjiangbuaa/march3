<?php
!defined('IN_ADMIN') && exit('Access Denied');
logout();
session_destroy();
header("Location:admincp.php?mod=admin&act=login");

?>
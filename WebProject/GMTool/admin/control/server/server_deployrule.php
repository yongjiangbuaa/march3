<?php
!defined('IN_ADMIN') && exit('Access Denied');
$headLine = "发布 规则、流程";


include( renderTemplate("{$module}/{$module}_{$action}") );
?>
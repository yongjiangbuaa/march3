<?php
!defined('IN_ADMIN') && exit('Access Denied');


include( renderTemplate("{$module}/{$module}_{$action}") );
?>
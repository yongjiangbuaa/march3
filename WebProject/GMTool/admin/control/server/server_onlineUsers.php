<?php
/**
 * Created by PhpStorm.
 * User: shushenglin
 * Date: 16/5/16
 * Time: 15:12
 */

!defined('IN_ADMIN') && exit('Access Denied');


include( renderTemplate("{$module}/{$module}_{$action}") );

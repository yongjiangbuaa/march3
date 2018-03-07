<?php
import('module.event.XApplicationEvent');
/**
 * XingCloudClosedEvent
 * 
 * context closed event
 * 
 * 框架关闭事件类
 * 
 * <b>通过监听该事件，可以在程序结束之前得到通知，处理一些诸如释放资源等处理操作</b>
 * 
 * <b>该事件的发起者是XingCloudApp</b>
 * 
 * @author Tianwei
 * @package event
 */
class XingCloudClosedEvent extends XApplicationEvent{
}
?>
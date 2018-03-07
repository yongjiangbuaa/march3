<?php
import('module.event.XApplicationEvent');
/**
 * ActionStartedEvent
 * 
 * action started event
 * 
 * action开始事件。每个Action处理之前会发布该事件。
 * 若一次请求中包含多个Action，则会发布多个ActionStartedEvent。
 * 
 * @author Tianwei
 * @package action
 */
class ActionStartedEvent extends XApplicationEvent{	
}
?>
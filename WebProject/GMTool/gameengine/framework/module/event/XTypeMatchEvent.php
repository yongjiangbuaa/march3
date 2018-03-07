<?php
import('module.event.XTypeFilterEvent');
/**
 * XTypeMatchEvent
 * 
 * type preg match event
 * 
 * 类型正则匹配事件，提供复杂事件逻辑
 * 
 * <b>可以用类型来正则匹配多种事件，只要这些事件都是XTypeMatchEvent的实例或子类实例，并满足同样的正则匹配</b>
 * 
 * @author Tianwei
 * @package event
 */
class XTypeMatchEvent extends XTypeFilterEvent{
}
?>
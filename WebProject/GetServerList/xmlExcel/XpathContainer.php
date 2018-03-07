<?php

/**
 * XpathContener
 *
 * @category   XmlTools
 * @author	   wuyuqun
 * @copyright  Copyright (c) 2006 - 2011 Elex
 */
class XpathContainer  {
	
	/**
	 * Full xpath of current node
	 *
	 * @var string
	 */
	public  $fullXpath;
	
	
	/**
	 * Xpath object of parent node.
	 *
	 * @var  XpathContainer
	 */
	public $parentXpath;
	
	
	/**
	 * Node name of the element
	 *
	 * @var string
	 */
	public  $nodeName;

	/**
	 * Attributes
	 *
	 * @var string
	 */
	public  $attributes = array();
 

	public function __construct() {
		
	}
}

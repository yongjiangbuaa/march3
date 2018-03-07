<?php
/**
 * PHPExcel
 *
 * Copyright (c) 2006 - 2011 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2011 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    ##VERSION##, ##DATE##
 * @author     wuyuqun
 * @link       bluesky.wyq@gmail.com
 */


/**
 * VbaProject
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2011 PHPExcel (http://www.codeplex.com/PHPExcel)
 */
class PHPExcel_VbaProject implements PHPExcel_IComparable
{
	/**
	 * Is this excel have some micro
	 *
	 * @var boolean
	 */
	private $_haveMicro = false;

	/**
	 * Path of vbaProject.bin
	 *
	 * @var string
	 */
	private $_target = '';

    /**
     * Create a new VbaProject
     *
     * @throws	Exception
     */
    public function __construct()
    {
    	// Initialise variables
    }

    /**
     * Get HaveMicro
     *
     * @return string
     */
    public function getHaveMicro() {
    	return $this->_haveMicro;
    }

    /**
     * Set HaveMicro
     *
     * @param string $pValue
     */
	public function setHaveMicro($pValue = false) {
		$this->_haveMicro = $pValue;
	}

    /**
     * Get path of vbaProject.bin
     *
     * @return string
     */
    public function getTarget() {
    	return $this->_target;
    }

    /**
     * Set path of vbaProject.bin
     *
     * @param string $pValue
     */
    public function setTarget($pValue = '') {
    	$this->_target = $pValue;
    }
 
	/**
	 * Get hash code
	 *
	 * @return string	Hash code
	 */
	public function getHashCode() {
    	return md5(
    		  ($this->_visible ? 1 : 0)
    		. $this->_target
    		. __CLASS__
    	);
    }

	/**
	 * Implement PHP __clone to create a deep clone, not just a shallow copy.
	 */
	public function __clone() {
		$vars = get_object_vars($this);
		foreach ($vars as $key => $value) {
			if (is_object($value)) {
				$this->$key = clone $value;
			} else {
				$this->$key = $value;
			}
		}
	}
}

<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010 - 2012
 * @license GNU General Public License version 3
 * @package LiteMVC
 * @version 0.2.0
 */
namespace LiteMVC\View;

use LiteMVC\View as View;

class HTML extends View implements View\View
{

	/**
	 * Render the page and layout
	 *
	 * @return string
	 */
	public function render()
	{
		// Render page
		if (!is_null($this->_page)) {
			if ($this->_pageMode == 'include') {
				ob_start();
				include $this->_page;
				$this->_pageRendered = ob_get_clean();
			}
			if ($this->_pageMode == 'replace') {
				$page = $this->_page;
				foreach ($this as $key => $value) {
					if (substr($key, 0, 1) != '_') {
						$page = str_ireplace('[' . $key . ']', $value, $page);
					}
				}
				$this->_pageRendered = $page;
			}
		}
		// Render layout
		if (!is_null($this->_layout)) {
			ob_start();
			include $this->_layout;
			$this->_rendered = ob_get_clean();
		}
	}

}
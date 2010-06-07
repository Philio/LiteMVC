<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC\View
 * @version 0.1.0
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
				include $this->_path . $this->_module . '/View/Pages/' . $this->_page . '.phtml';
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
			include $this->_path . $this->_module . '/View/Layouts/' . $this->_layout . '.phtml';
			$this->_rendered = ob_get_clean();
		}
	}

}
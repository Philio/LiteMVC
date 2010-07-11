<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC
 * @version 0.1.0
 */
namespace LiteMVC;

class Captcha
{

	/**
	 * Width of the output image
	 *
	 * @var int
	 */
	protected $_width = 200;

	/**
	 * Height of the output image
	 *
	 * @var int
	 */
	protected $_height = 50;

	/**
	 * Number of characters to use in the captcha
	 *
	 * @var int
	 */
	protected $_charCount = 8;

	/**
	 * Relative path to font folder
	 *
	 * @var string
	 */
	protected $_fontPath;

	/**
	 * Relative path to backgrouds folder
	 *
	 * @var string
	 */
	protected $_bgPath;

	/**
	 * Relative path to output folder
	 *
	 * @var string
	 */
	protected $_imgPath;

	/**
	 * Image url
	 *
	 * @var string
	 */
	protected $_imgUrl = '/images/captcha';

	/**
	 * Constructor
	 *
	 * @param App $app
	 * @return void
	 */
	public function __construct(App $app)
	{
		$config = $app->getResource('Config')->Captcha;
		if (!is_null($config)) {
			// Set config options from config file
			foreach ($config as $key => $value) {
				switch ($key) {
					// Image width (optional)
					case 'width':
						$this->_width = $value;
						break;
					// Imge height (optional)
					case 'height':
						$this->_height = $value;
						break;
					// Number of character (optional)
					case 'count':
						$this->_charCount = $value;
						break;
					// Font options
					case 'font':
						// Font path
						if (isset($value['path'])) {
							$this->_fontPath = $value['path'];
						}
						break;
					// Background options (optional)
					case 'background':
						// Background path
						if (isset($value['path'])) {
							$this->_bgPath = $value['path'];
						}
						break;
					// Image options
					case 'image':
						// Image path
						if (isset($value['path'])) {
							$this->_imgPath = $value['path'];
						}
						// Image url
						if (isset($value['url'])) {
							$this->_imgUrl = $value['url'];
						}
						break;
				}
			}
		}
	}

	/**
	 * Set size of output image
	 *
	 * @param int $width
	 * @param int $height
	 */
	public function setSize(int $width, int $height)
	{
		$this->_width = $width;
		$this->_height = $height;
	}

	/**
	 * Set number of characters to use
	 *
	 * @param int $count
	 */
	public function setCharCount(int $count)
	{
		$this->_charCount = $count;
	}

	/**
	 * Set relative font path
	 *
	 * @param string $path
	 */
	public function setFontPath($path)
	{
		$this->_fontPath = $path;
	}

	/**
	 * Set relative background image path
	 *
	 * @param string $path
	 */
	public function setBackgroundPath($path)
	{
		$this->_bgPath = $path;
	}

	/**
	 * Set relative image output path
	 *
	 * @param string $path
	 */
	public function setImagePath($path)
	{
		$this->_imgPath = $path;
	}

	/**
	 * Set image url
	 *
	 * @param string $url
	 */
	public function setImageUrl($url)
	{
		$this->_imgUrl = $url;
	}

}
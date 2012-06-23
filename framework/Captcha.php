<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010 - 2012
 * @license GNU General Public License version 3
 * @package LiteMVC
 * @version 0.2.1
 */
namespace LiteMVC;

class Captcha extends Resource\Loadable
{

	/**
	 * App instance
	 *
	 * @var App
	 */
	protected $_app;

	/**
	 * Config data
	 *
	 * @var array
	 */
	protected $_config = array();

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
	protected $_charCount = 6;

	/**
	 * Relative path to font folder
	 *
	 * @var string
	 */
	protected $_fontPath = '/framework/Captcha/Fonts';

	/**
	 * A list of available fonts
	 *
	 * @var array
	 */
	protected $_fontList = array();

	/**
	 * Relative path to backgrouds folder
	 *
	 * @var string
	 */
	protected $_bgPath;

	/**
	 * A list of available background images
	 *
	 * @var array
	 */
	protected $_bgList = array();

	/**
	 * Relative path to output folder
	 *
	 * @var string
	 */
	protected $_imgPath = '/public/images/captcha';

	/**
	 * Image url
	 *
	 * @var string
	 */
	protected $_imgUrl = '/images/captcha';

	/**
	 * Namespace in session
	 *
	 * @var string
	 */
	const SESS_NS		= 'Captcha';
	const SESS_NS_IMG	= 'Image';
	const SESS_NS_CODE	= 'Code';

	/**
	 * How long to keep the image for before it is deleted
	 *
	 * @var int
	 */
	const IMAGE_EXPIRES = 3600;

	/**
	 * Configuration keys
	 *
	 * @var string
	 */
	const CONF_WIDTH		= 'width';
	const CONF_HEIGHT		= 'height';
	const CONF_COUNT		= 'count';
	const CONF_FONT			= 'font';
	const CONF_FONT_PATH	= 'path';
	const CONF_BG			= 'background';
	const CONF_BG_PATH		= 'path';
	const CONF_IMG			= 'image';
	const CONF_IMG_PATH		= 'path';
	const CONF_IMG_URL		= 'url';

	/**
	 * Character set
	 *
	 *
	 * @var string
	 */
	const CHARACTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

	/**
	 * Constructor
	 *
	 * @param App $app
	 * @return void
	 */
	public function __construct(App $app)
	{
		// Set app
		$this->_app = $app;

		// Set config
		$this->_config = $app->getResource(self::RES_CONFIG)->captcha;
	}

	/**
	 * Initialise from config
	 *
	 * @return void
	 */
	public function init()
	{
		// Check config
		if (!is_array($this->_config)) {
			return;
		}

		// Set config options from config file
		foreach ($this->_config as $key => $value) {
			switch ($key) {
				// Image width (optional)
				case self::CONF_WIDTH:
					$this->_width = $value;
					break;

				// Imge height (optional)
				case self::CONF_HEIGHT:
					$this->_height = $value;
					break;

				// Number of character (optional)
				case self::CONF_COUNT:
					$this->_charCount = $value;
					break;

				// Font options
				case self::CONF_FONT:
					// Font path
					if (isset($value[self::CONF_FONT_PATH])) {
						$this->_fontPath = $value[self::CONF_FONT_PATH];
					}
					break;

				// Background options (optional)
				case self::CONF_BG:
					// Background path
					if (isset($value[self::CONF_BG_PATH])) {
						$this->_bgPath = $value[self::CONF_BG_PATH];
					}
					break;

				// Image options
				case self::CONF_IMG:
					// Image path
					if (isset($value[self::CONF_IMG_PATH])) {
						$this->_imgPath = $value[self::CONF_IMG_PATH];
					}
					// Image url
					if (isset($value[self::CONF_IMG_URL])) {
						$this->_imgUrl = $value[self::CONF_IMG_URL];
					}
					break;
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

	/**
	 * Render the image and output the HTML to display it
	 *
	 * @return string
	 */
	public function __toString()
	{
		if (!$this->_isRendered()) {
			$this->_render();
		}
		return '<img src="' . $_SESSION[self::SESS_NS][self::SESS_NS_IMG] . '" width="' .
			$this->_width . '" height="' . $this->_height . '" alt="Captcha" class="captcha" />';
	}

	/**
	 * Refresh the stored captcha image
	 *
	 * @return void
	 */
	public function refresh()
	{
		$this->_render();
	}

	/**
	 * Get the image url
	 *
	 * @return string
	 */
	public function getImage()
	{
		if (!$this->_isRendered()) {
			$this->_render();
		}
		return $_SESSION[self::SESS_NS][self::SESS_NS_IMG];
	}

	/**
	 * Get the image code
	 *
	 * @return string
	 */
	public function getCode()
	{
		if (!$this->_isRendered()) {
			$this->_render();
		}
		return $_SESSION[self::SESS_NS][self::SESS_NS_CODE];
	}

	/**
	 * Validate code
	 *
	 * @param strign $code
	 * @return bool
	 */
	public function checkCode($code)
	{
		if (strtolower($_SESSION[self::SESS_NS][self::SESS_NS_CODE]) == strtolower($code)) {
			return true;
		}
		return false;
	}

	/**
	 * Check if the image has been rendered
	 *
	 * @return bool
	 */
	protected function _isRendered()
	{
		if (!isset($_SESSION[self::SESS_NS][self::SESS_NS_IMG]) || !isset($_SESSION[self::SESS_NS][self::SESS_NS_CODE])) {
			return false;
		}
		return true;
	}

	/**
	 * Render captcha image
	 *
	 * @return string
	 */
	protected function _render()
	{
		// Garbage collection
		if (mt_rand(1, 100) == 50) {
			$this->_cleanup();
		}

		// Load fonts
		$this->_loadFontList();

		// Load backgrounds
		$this->_loadBackgroundList();

		// Create image
		$img = imagecreatetruecolor($this->_width, $this->_height);

		// Add background
		if (count($this->_bgList)) {
			$rand = mt_rand(1, count($this->_bgList)) - 1;
			// Create background image
			$bg = imagecreatefromstring(file_get_contents($this->_bgList[$rand]));
			// Copy into image
			imagecopyresampled(
				$img, $bg, 0, 0, 0, 0,
				$this->_width,
				$this->_height,
				imagesx($bg),
				imagesy($bg)
			);
		} else {
			// Make background transparent
			imagesavealpha($img, true);
			$transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
			imagefill($img, 0, 0, $transparent);
		}

		// Add some random characters
		for ($i = 0; $i < mt_rand(10, 20); $i ++) {
			// Assign a colour fairly close to white
			$colour = imagecolorallocate(
				$img,
				mt_rand(200, 255),
				mt_rand(200, 255),
				mt_rand(200, 255)
			);
			// Draw random letter
			$letter = substr(self::CHARACTERS, mt_rand(0, strlen(self::CHARACTERS) - 1), 1);
			$res = imagettftext(
				$img,
				mt_rand(10, 50),
				mt_rand(-10, 10),
				mt_rand(0, $this->_width),
				mt_rand(0, $this->_height),
				$colour,
				$this->_fontList[mt_rand(0, count($this->_fontList) - 1)],
				$letter
			);
		}

		// Calc restraints
		$charSizeY = $this->_height;
		$charSizeX = floor($this->_width * 0.9 / $this->_charCount);
		$ptSize = floor($charSizeY * 0.9 / 2);

		// Add characters
		$code = '';
		for ($i = 0; $i < $this->_charCount; $i ++) {
			// Assign a random colour fairly close to black
			$colour = imagecolorallocatealpha(
				$img,
				mt_rand(0, 100),
				mt_rand(0, 100),
				mt_rand(0, 100),
				mt_rand(0,50)
			);
			// Draw random letter
			$letter = substr(self::CHARACTERS, mt_rand(0, strlen(self::CHARACTERS) - 1), 1);
			$code .= $letter;
			imagettftext(
				$img,
				mt_rand($ptSize - 5, $ptSize),
				mt_rand(-10, 10),
				$i * $charSizeX + mt_rand(10, 15),
				$charSizeY - mt_rand(10, 20),
				$colour,
				$this->_fontList[mt_rand(0, count($this->_fontList) - 1)],
				$letter
			);
		}

		// Generate a unique filename
		do {
			$filename = md5(microtime()) . '.png';
			$filepath = \PATH . rtrim($this->_imgPath, '/') . '/' . $filename;
		} while (file_exists($filepath));

		// Save to session
		$_SESSION[self::SESS_NS][self::SESS_NS_IMG] = rtrim($this->_imgUrl, '/') . '/' . $filename;
		$_SESSION[self::SESS_NS][self::SESS_NS_CODE] = $code;

		// Save to disk
		imagepng($img, $filepath);
	}

	/**
	 * Load a list of fonts in the font directory
	 *
	 * @retun void
	 */
	protected function _loadFontList()
	{
		if (!is_null($this->_fontPath)) {
			$this->_fontList = $this->_scanDirectory($this->_fontPath);
		}
	}

	/**
	 * Load a lost of background images available
	 *
	 * @return void
	 */
	protected function _loadBackgroundList()
	{
		if (!is_null($this->_bgPath)) {
			$this->_bgList = $this->_scanDirectory($this->_bgPath);
		}
	}

	/**
	 * Read all files in a directory
	 *
	 * @param string $dir
	 */
	protected function _scanDirectory($dir)
	{
		$files = array();
		foreach (new \DirectoryIterator(\PATH . $dir) as $file) {
			if ($file->isFile()) {
				$files[] = $file->getPathname();
			}
		}
		return $files;
	}

	/**
	 * Cleanup old captcha images
	 *
	 * @return void
	 */
	protected function _cleanup()
	{
		// If image path not set cannot complete
		if (is_null($this->_imgPath)) {
			return;
		}

		// Loop through directory and unlink any file not accessed for 24 hrs
		foreach (new \DirectoryIterator(\PATH . $this->_imgPath) as $file) {
			if ($file->isFile() && $file->getATime() < time() - self::IMAGE_EXPIRES) {
				unlink($file->getPathname());
			}
		}
	}

}
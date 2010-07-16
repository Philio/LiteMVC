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
	protected $_imgPath;

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
	const Sess_Namespace = 'Captcha';

	/**
	 * Character set
	 *
	 *
	 * @var string
	 */
	const Characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

	/**
	 * Constructor
	 *
	 * @param App $app
	 * @return void
	 */
	public function __construct(App $app)
	{
		$config = $app->getResource('Config')->captcha;
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
	
	/**
	 * Render the image and output the HTML to display it
	 * 
	 * @return string
	 */
	public function __toString()
	{
		if (!isset($_SESSION[self::Sess_Namespace]['Image']) || !isset($_SESSION[self::Sess_Namespace]['Code'])) {
			$this->_render();
		}
		return '<img src="' . $_SESSION[self::Sess_Namespace]['Image'] . '" alt="Captcha" class="captcha" />';
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
	 * Validate code
	 *
	 * @param strign $code
	 * @return bool
	 */
	public function checkCode($code)
	{
		if (strtolower($_SESSION[self::Sess_Namespace]['Code']) == strtolower($code)) {
			return true;
		}
		return false;
	}

	/**
	 * Render captcha image
	 *
	 * @return string
	 */
	protected function _render()
	{
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
			imagecopyresampled($img, $bg, 0, 0, 0, 0, $this->_width, $this->_height, imagesx($bg), imagesy($bg));
		} else {
			// Make background transparent
			imagesavealpha($img, true);
			$transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
			imagefill($img, 0, 0, $transparent);
		}
		// Add some random characters
		for ($i = 0; $i < mt_rand(10, 20); $i ++) {
			// Assign a colour fairly close to white
			$colour = imagecolorallocate($img, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255));
			// Draw random letter
			$letter = substr(self::Characters, mt_rand(0, strlen(self::Characters) - 1), 1);
			imagettftext(
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
			$colour = imagecolorallocatealpha($img, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100), mt_rand(0,50));
			// Draw random letter
			$letter = substr(self::Characters, mt_rand(0, strlen(self::Characters) - 1), 1);
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
		$_SESSION[self::Sess_Namespace]['Image'] = rtrim($this->_imgUrl, '/') . '/' . $filename;
		$_SESSION[self::Sess_Namespace]['Code'] = $code;
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
		foreach(new \DirectoryIterator(\PATH . $dir) as $file) {
			if ($file->isFile()) {
				$files[] = $file->getPathname();
			}
		}
		return $files;
	}

}
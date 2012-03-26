<?php

require_once 'Swat/SwatString.php';
require_once 'NewsFlash/NewsFlashItem.php';

/**
 * @package   NewsFlash
 * @copyright 2012 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class NewsFlashSniftrItem extends NewsFlashItem
{
	// {{{ protected properties

	/**
	 * @var SniftrPost
	 */
	protected $post = null;

	// }}}
	// {{{ public function __construct()

	public function __construct(SniftrPost $post)
	{
		$this->post = $post;
	}

	// }}}
	// {{{ public function getTitle()

	public function getTitle()
	{
		return $this->post->getTitle();
	}

	// }}}
	// {{{ public function getBody()

	public function getBody()
	{
		return $this->post->getBody();
	}

	// }}}
	// {{{ public function getLink()

	public function getLink()
	{
		return $this->post->getLink();
	}

	// }}}
	// {{{ public function getType()

	public function getType()
	{
		return 'sniftr-'.$this->post->getType();
	}

	// }}}
	// {{{ public function getIcon()

	public function getIcon($secure = false, $size = 32)
	{
		switch ($size) {
		case 72:
			$icon = 'packages/news-flash/images/tumblr72.png';
			break;

		case 48:
			$icon = 'packages/news-flash/images/tumblr48.png';
			break;

		case 32:
		default:
			$icon = 'packages/news-flash/images/tumblr32.png';
			break;
		}

		return $icon;
	}

	// }}}
	// {{{ public function getDate()

	public function getDate()
	{
		return $this->post->getDate();
	}

	// }}}
}

?>

<?php

require_once 'NewsFlash/NewsFlashSource.php';
require_once 'NewsFlash/NewsFlashSniftrItem.php';
require_once 'Sniftr/SniftrReader.php';

/**
 * @package   NewsFlash
 * @copyright 2012 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class NewsFlashSniftrSource extends NewsFlashSource
{
	// {{{ protected properties
	/**
	 * @var string
	 */
	protected $username = '';

	/**
	 * @var SniftrReader
	 */
	protected $reader = null;

	/**
	 * @var array
	 */
	protected $items = null;

	// }}}
	// {{{ public function __construct()

	public function __construct(SiteApplication $app, $username)
	{
		parent::__construct($app);
		$this->username = $username;
	}

	// }}}
	// {{{ public function setReader()

	public function setReader(SniftrReader $reader)
	{
		$this->reader = $reader;
	}

	// }}}
	// {{{ public function getItems()

	public function getItems($max_length = 10, $force_cache_update = false)
	{
		if ($this->items === null) {
			$this->items = array();
			$count = 0;
			$posts = $this->getReader()->getPosts($force_cache_update);
			foreach ($posts as $post) {
				$count++;
				if ($count > $max_length) {
					break;
				}

				$this->items[] = new NewsFlashSniftrItem($post);
			}
		}

		return $this->items;
	}

	// }}}
	// {{{ protected function getReader()

	protected function getReader()
	{
		if (!($this->reader instanceof SniftrReader)) {
			$this->reader = new SniftrReader($this->app, $this->username);
		}

		return $this->reader;
	}

	// }}}
	// {{{ protected function getCacheKey()

	protected function getCacheKey($max_length = 10)
	{
		return 'nf-sniftr-'.$this->username.intval($max_length);
	}

	// }}}
}

?>

<?php

/**
 * @package   NewsFlash
 * @copyright 2012-2016 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
abstract class NewsFlashSource
{
	// {{{ protected properties

	/**
	 * @var SiteApplication
	 */
	protected $app = null;

	// }}}
	// {{{ public function __construct()

	public function __construct(SiteApplication $app)
	{
		$this->app = $app;
	}

	// }}}
	// {{{ abstract public function getItems()

	abstract public function getItems(
		$max_length = 10,
		$force_cache_update = false
	);

	// }}}
	// {{{ abstract protected function getCacheKey()

	abstract protected function getCacheKey($max_length = 10);

	// }}}
}

?>

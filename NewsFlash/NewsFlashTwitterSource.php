<?php

require_once 'NewsFlash/NewsFlashSource.php';
require_once 'NewsFlash/NewsFlashTwitterItem.php';
require_once 'HTTP/Request2.php';
require_once 'Services/Twitter.php';

/**
 * @package   NewsFlash
 * @copyright 2012 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class NewsFlashTwitterSource extends NewsFlashSource
{
	// {{{ class constants

	const URI_ENDPOINT = 'http://twitter.com';

	// }}}
	// {{{ protected properties

	/**
	 * @var string
	 */
	protected $username = '';

	/**
	 * @var array
	 */
	protected $items = null;

	/**
	 * @var Services_Twitter
	 */
	protected $twitter = null;

	// }}}
	// {{{ public function __construct()

	public function __construct(SiteApplication $app, $username)
	{
		parent::__construct($app);
		$this->username = $username;
	}

	// }}}
	// {{{ public function setTwitter()

	public function setTwitter(Services_Twitter $twitter)
	{
		$this->twitter = $twitter;
	}

	// }}}
	// {{{ public function getItems()

	public function getItems($max_length = 10, $force_clear_cache = false)
	{
		if ($this->items === null) {
			foreach ($this->getTimeline() as $status) {
				$this->items[] = new NewsFlashTwitterItem(
					$this->username,
					$status
				);
			}
		}
		return $this->items;
	}

	// }}}
	// {{{ protected function getTimeline()

	protected function getTimeline()
	{
		$twitter = $this->getTwitter();
		$params = array('id' => $this->username);
		$timeline = $twitter->statuses->user_timeline($params);
		return $timeline;
	}

	// }}}
	// {{{ protected function getTwitter()

	protected function getTwitter()
	{
		if (!($this->twitter instanceof Services_Twitter)) {
			$request = new HTTP_Request2();
			$request->setConfig(
				array(
					'connect_timeout' => 1,
					'timeout'         => 3,
				)
			);

			$this->twitter = new Services_Twitter(
				null,
				null,
				array(
					'format' => Services_Twitter::OUTPUT_JSON
				)
			);
			$this->twitter->setRequest($request);
		}

		return $this->twitter;
	}

	// }}}
}

?>

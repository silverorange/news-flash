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

	/**
	 * The amount of time in minutes we wait before we update the cache
	 */
	const UPDATE_THRESHOLD = 5;

	/**
	 * The amount of time in minutes we wait before we try updating the cache
	 * again if the cache failed to update
	 */
	const UPDATE_RETRY_THRESHOLD = 2;

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

	public function getItems($max_length = 10, $force_cache_update = false)
	{
		if ($this->items === null) {
			$this->items = array();
			$count = 0;
			$statuses = $this->getTimeline($max_length, $force_cache_update);
			foreach ($statuses as $status) {
				$count++;
				if ($count > $max_length) {
					break;
				}

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

	protected function getTimeline($max_length, $force_cache_update)
	{
		$timeline = array();
		$loaded = false;
		$last_update = 0;

		$cache_key = $this->getCacheKey($max_length);

		$cached_value = $this->app->getCacheValue($cache_key);
		if ($cached_value !== false) {
			$cached_value = unserialize($cached_value);
			if (isset($cached_value['last_update']) &&
				isset($cached_value['timeline'])) {
				$last_update = $cached_value['last_update'] +
					60 * self::UPDATE_THRESHOLD;
			} else {
				$cached_value = false;
			}
		}

		// try to update the cache if the value is stale
		if (time() > $last_update || $force_cache_update) {
			try {
				$twitter = $this->getTwitter();
				$params = array(
					'screen_name'     => $this->username,
					'exclude_replies' => true,
				);

				$timeline = $twitter->statuses->user_timeline($params);
				$loaded = true;

				$value = array(
					'timeline'    => $timeline,
					'last_update' => time(),
				);

				$this->app->addCacheValue(serialize($value), $cache_key);
			} catch (Services_Twitter_Exception $e) {
				// We want to ignore any exceptions that occur because
				// HTTP_Request2 either times out receiving the response or
				// because we were unable to actually connect to Twitter.
				// The only way to distinguish HTTP_Request2_Exceptions is to
				// look at the exception's message.
				$ignore = array(
					'^Request timed out after [0-9]+ second\(s\)$',
					'^Unable to connect to',
					'^Rate limit exceeded.',
					'^Internal Server Error$',
				);

				$regexp = sprintf('/%s/u', implode('|', $ignore));

				if (preg_match($regexp, $e->getMessage()) === 1) {
					// update the last update time on existing cached value so
					// we rate-limit retries
					if ($cached_value) {
						$cached_value['last_update'] +=
							(60 * (self::UPDATE_RETRY_THRESHOLD -
							self::UPDATE_THRESHOLD));

						$this->app->addCacheValue(
							serialize($cached_value),
							$cache_key
						);
					}
				} elseif ($e->getCause() instanceof Exception) {
					// Services_Twitter wraps all generated exceptions around
					// their own Services_Twitter_Exception. You can retrieve
					// the parent exception by using the
					// PEAR_Exception::getCause() method.
					$exception = new SwatException($e->getCause());
					$exception->processAndContinue();
				} else {
					$exception = new SwatException($e);
					$exception->processAndContinue();
				}
			}
		}

		// Use cached version if it is valid, or if we failed to update from
		// the live feed.
		if ($cached_value && !$loaded) {
			$timeline = $cached_value['timeline'];
		}

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
	// {{{ protected function getCacheKey()

	protected function getCacheKey($max_length = 10)
	{
		return 'nf-twitter-'.$this->username.'-'.intval($max_length);
	}

	// }}}
}

?>

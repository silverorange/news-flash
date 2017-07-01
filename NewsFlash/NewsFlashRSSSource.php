<?php

/**
 * @package   NewsFlash
 * @copyright 2012-2016 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class NewsFlashRSSSource extends NewsFlashSource
{
	// {{{ protected properties

	/**
	 * @var string
	 */
	protected $uri = '';

	/**
	 * @var array
	 */
	protected $items = null;

	/**
	 * @var HTTP_Request2
	 */
	protected $request = null;

	// }}}
	// {{{ public function __construct()

	public function __construct(SiteApplication $app, $uri)
	{
		parent::__construct($app);
		$this->uri = $uri;
	}

	// }}}
	// {{{ public function setRequest()

	public function setRequest(HTTP_Request2 $request)
	{
		$this->request = $request;
	}

	// }}}
	// {{{ public function getItems()

	public function getItems($max_length = 10, $force_cache_update = false)
	{
		if ($this->items === null) {
			$this->items = array();
			$xml = $this->getXML($max_length, $force_cache_update);
			if ($xml != '') {
				$errors = libxml_use_internal_errors(true);

				try {
					$document = new DOMDocument();
					$document->loadXML($xml);
					$xpath = new DOMXPath($document);
					$this->registerXPathNamespaces($xpath);

					$count = 0;
					$elements = $xpath->query('//rss/channel/item');
					foreach ($elements as $element) {
						$count++;
						if ($count > $max_length) {
							break;
						}

						$this->items[] = new NewsFlashRSSItem($xpath, $element);
					}
				} catch (Exception $e) {
					// ignore XML parsing exception, just return no items.
				}

				libxml_clear_errors();
				libxml_use_internal_errors($errors);
			}
		}

		return $this->items;
	}

	// }}}
	// {{{ protected function getXML()

	protected function getXML($max_length, $force_cache_update)
	{
		$cache_key  = $this->getCacheKey($max_length);
		$expiry_key = $this->getCacheExpiryKey($max_length);

		$expired = ($force_cache_update ||
			$this->app->getCacheValue($expiry_key));

		if ($expired === false) {
			// expiry key expired, check for updated content on live site
			$xml = $this->getLiveXML();
			if ($xml === false) {
				// RSS feed is down, try long cached value
				$xml = $this->app->getCacheValue($cache_key);
				if ($xml === false) {
					// RSS feed is down, but we have no cached value
					$xml = '';
				}
			} else {
				// update long cache with new RSS content
				$this->app->addCacheValue($xml, $cache_key, null, 7200);
			}
			// update expiry cache value
			$this->app->addCacheValue('1', $expiry_key, null, 300);
		} else {
			// expiry key not expired, check for long cached value
			$xml = $this->app->getCacheValue($cache_key);
			if ($xml === false) {
				// long cached version expired or does not exist, check
				// for content on live site
				$xml = $this->getLiveXML();
				if ($xml === false) {
					// RSS feed is down, but we have no cached value
					$xml = '';
					$this->app->deleteCacheValue($expiry_key);
				} else {
					// update long cache with new RSS content
					$this->app->addCacheValue($xml, $cache_key, null, 7200);
				}
			}
		}

		return $xml;
	}

	// }}}
	// {{{ protected function getLiveXML()

	protected function getLiveXML()
	{
		$xml = false;

		$request = $this->getRequest();
		$request->setUrl($this->uri);
		try {
			$response = $request->send();
			$xml = $response->getBody();
			$xml = $this->filterLiveXML($xml);
		} catch (HTTP_Request2_Exception $e) {
		}

		return $xml;
	}

	// }}}
	// {{{ protected function filterLiveXML()

	protected function filterLiveXML($xml)
	{
		// filter out Wordpress tracking gifs
		$xml = preg_replace(
			'/<img alt="" border="0" '.
			'src="https?:\/\/stats.wordpress.com\/[sb].gif[^"]+"[^>]+\/>/',
			'',
			$xml
		);

		// filter out the crappy Wordpress comment links
		$xml = preg_replace(
			'/<a [^>]*href='.
			'"https?:\/\/feeds.wordpress.com\/[0-9\.]+\/gocomments[^"]+"[^>]*>'.
			'<img [^>]*\/><\/a>/',
			'',
			$xml
		);

		return $xml;
	}

	// }}}
	// {{{ protected function registerXPathNamespaces()

	protected function registerXPathNamespaces(DOMXPath $xpath)
	{
		$namespaces = array(
			'atom'    => 'http://www.w3.org/2005/Atom',
			'media'   => 'http://search.yahoo.com/mrss/',
			'dc'      => 'http://purl.org/dc/elements/1.1/',
			'content' => 'http://purl.org/rss/1.0/modules/content/',
			'slash'   => 'http://purl.org/rss/1.0/modules/slash/',
		);

		foreach ($namespaces as $prefix => $namespace) {
			$xpath->registerNamespace($prefix, $namespace);
		}
	}

	// }}}
	// {{{ protected function getRequest()

	protected function getRequest()
	{
		if (!($this->request instanceof HTTP_Request2)) {
			$this->request = new HTTP_Request2();
			$this->request->setConfig(
				array(
					'connect_timeout' => 1,
					'timeout'         => 3,
				)
			);
		}

		return clone $this->request;
	}

	// }}}
	// {{{ protected function getCacheKey()

	protected function getCacheKey($max_length = 10)
	{
		return 'nf-rss-'.md5($this->uri).intval($max_length);
	}

	// }}}
	// {{{ protected function getCacheExpiryKey()

	protected function getCacheExpiryKey($max_length = 10)
	{
		return 'nf-rss-exp-'.md5($this->uri).intval($max_length);
	}

	// }}}
}

?>

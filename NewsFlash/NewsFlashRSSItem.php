<?php

require_once 'NewsFlash/NewsFlashItem.php';

/**
 * @package   NewsFlash
 * @copyright 2012 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class NewsFlashRSSItem extends NewsFlashItem
{
	// {{{ protected properties

	/**
	 * @var DOMXPath
	 */
	protected $xpath = null;

	/**
	 * @var DOMElement
	 */
	protected $element = null;

	// }}}
	// {{{ public function __construct()

	public function __construct(DOMXPath $xpath, DOMElement $element)
	{
		$this->xpath   = $xpath;
		$this->element = $element;
	}

	// }}}
	// {{{ public function getTitle()

	public function getTitle()
	{
		$title = $this->xpath->evaluate(
			"string(title)",
			$this->element
		);

		return ($title == '') ? null : $title;
	}

	// }}}
	// {{{ public function getBody()

	public function getBody()
	{
		$description = $this->xpath->evaluate(
			"string(description)",
			$this->element
		);

		return ($description == '') ? null : $description;
	}

	// }}}
	// {{{ public function getLink()

	public function getLink()
	{
		return $this->xpath->evaluate(
			"string(link)",
			$this->element
		);
	}

	// }}}
	// {{{ public function getType()

	public function getType()
	{
		return 'rss-item';
	}

	// }}}
	// {{{ public function getIcon()

	public function getIcon($secure = false)
	{
		$uri = null;

		// check for gravatar
		$media = $this->xpath->evaluate(
			"string(media:content[".
				"@medium='image' and contains(@url,'gravatar.com')]/@url)",
			$this->element
		);

		if ($media != '') {
			list($base, $query) = explode('?', $media, 2);
			$hash = array_pop(explode('/', $base));
			if ($secure) {
				$uri = 'https://secure.gravatar.com/avatar/'.$hash.'?s=32';
			} else {
				$uri = 'http://www.gravatar.com/avatar/'.$hash.'?s=32';
			}
		}

		return $uri;
	}

	// }}}
	// {{{ public function getDate()

	public function getDate()
	{
		$date_string = $this->xpath->evaluate(
			"string(pubDate)",
			$this->element
		);
		$unix_time = strtotime($date_string);
		$date = new SwatDate();
		$date->setTimestamp($unix_time);
		$date->toUTC();
		return $date;
	}

	// }}}
}

?>

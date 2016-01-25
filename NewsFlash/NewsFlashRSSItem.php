<?php

require_once 'NewsFlash/NewsFlashItem.php';

/**
 * @package   NewsFlash
 * @copyright 2012-2016 silverorange
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

	public function getBody($secure = false)
	{
		$description = $this->xpath->evaluate(
			"string(content:encoded)",
			$this->element
		);

		if ($secure) {
			$description = preg_replace(
				'/(<img[^>]+src=")http:/',
				'$1https:',
				$description
			);
		}

		if ($description == '') {
			$description = $this->xpath->evaluate(
				"string(description)",
				$this->element
			);
		}

		// Remove trailing <br>'s from sloppy blog platforms.
		$description = preg_replace(
			'!(\s*<br\s*/?\>\s*)*$!u',
			'',
			$description
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

	public function getIcon($secure = false, $size = 32)
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
			$base = ($secure) ? 'https://secure.' : 'http://www.';
			$uri = $base.'gravatar.com/avatar/'.$hash.'?s='.intval($size);
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

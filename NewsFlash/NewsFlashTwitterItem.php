<?php

require_once 'Swat/SwatString.php';
require_once 'NewsFlash/NewsFlashItem.php';

/**
 * @package   NewsFlash
 * @copyright 2012 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class NewsFlashTwitterItem extends NewsFlashItem
{
	// {{{ protected properties

	/**
	 * @var stdClass
	 */
	protected $status = null;

	/**
	 * @var string
	 */
	protected $username = '';

	// }}}
	// {{{ public function __construct()

	public function __construct($username, stdClass $status)
	{
		$this->username = $username;
		$this->status   = $status;
	}

	// }}}
	// {{{ public function getTitle()

	public function getTitle()
	{
		return null;
	}

	// }}}
	// {{{ public function getBody()

	public function getBody()
	{
		return SwatString::linkify($this->status->text);
	}

	// }}}
	// {{{ public function getLink()

	public function getLink()
	{
		// Use id_str instead of id, as id sometimes returns a float.
		return sprintf(
			'%s/%s/status/%s',
			NewsFlashTwitterSource::URI_ENDPOINT,
			$this->username,
			$this->status->id_str
		);
	}

	// }}}
	// {{{ public function getType()

	public function getType()
	{
		return 'twitter-tweet';
	}

	// }}}
	// {{{ public function getIcon()

	public function getIcon($secure = false)
	{
		return 'packages/news-flash/images/twitter32.png';
	}

	// }}}
	// {{{ public function getDate()

	public function getDate()
	{
		$unix_time = strtotime($this->status->created_at);
		$date = new SwatDate();
		$date->setTimestamp($unix_time);
		$date->toUTC();
		return $date;
	}

	// }}}
}

?>

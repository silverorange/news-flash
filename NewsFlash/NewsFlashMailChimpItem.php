<?php

require_once 'NewsFlash/NewsFlashItem.php';

/**
 * @package   NewsFlash
 * @copyright 2012-2016 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class NewsFlashMailChimpItem extends NewsFlashItem
{
	// {{{ protected properties

	/**
	 * @var array
	 */
	protected $campaign = array();

	// }}}
	// {{{ public function __construct()

	public function __construct(array $campaign)
	{
		$this->campaign = $campaign;
	}

	// }}}
	// {{{ public function getTitle()

	public function getTitle()
	{
		return $this->campaign['subject'];
	}

	// }}}
	// {{{ public function getBody()

	public function getBody($secure = false)
	{
		return null;
	}

	// }}}
	// {{{ public function getLink()

	public function getLink()
	{
		return $this->campaign['archive_url'];
	}

	// }}}
	// {{{ public function getType()

	public function getType()
	{
		return 'mailchimp-campaign';
	}

	// }}}
	// {{{ public function getIcon()

	public function getIcon($secure = false, $size = 32)
	{
		switch ($size) {
		case 72:
			$icon = 'packages/news-flash/images/mailchimp72.png';
			break;

		case 48:
			$icon = 'packages/news-flash/images/mailchimp48.png';
			break;

		case 32:
		default:
			$icon = 'packages/news-flash/images/mailchimp32.png';
			break;
		}

		return $icon;
	}

	// }}}
	// {{{ public function getDate()

	public function getDate()
	{
		$unix_time = strtotime($this->campaign['send_time']);
		$date = new SwatDate();
		$date->setTimestamp($unix_time);
		$date->toUTC();
		return $date;
	}

	// }}}
}

?>

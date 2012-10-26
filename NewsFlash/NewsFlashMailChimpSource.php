<?php

require_once 'NewsFlash/NewsFlashSource.php';
require_once 'NewsFlash/NewsFlashMailChimpItem.php';
require_once 'Deliverance/DeliveranceMailChimpList.php';

/**
 * @package   NewsFlash
 * @copyright 2012 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class NewsFlashMailChimpSource extends NewsFlashSource
{
	// {{{ class constants

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
	protected $list_name = '';

	/**
	 * @var string
	 */
	protected $groups = array();

	/**
	 * @var array
	 */
	protected $items = null;

	/**
	 * @var DeliveranceMailChimpList
	 */
	protected $list = null;

	// }}}
	// {{{ public function __construct()

	public function __construct(SiteApplication $app, $list_name,
		$groups = array())
	{
		parent::__construct($app);

		$this->list_name = $list_name;
		$this->groups    = $groups;
	}

	// }}}
	// {{{ public function setList()

	public function setList(DeliveranceMailChimpList $list)
	{
		$this->list = $list;
	}

	// }}}
	// {{{ public function getItems()

	public function getItems($max_length = 10, $force_cache_update = false)
	{
		if ($this->items === null) {
			$this->items = array();
			$count = 0;
			$campaigns = $this->getCampaigns($max_length, $force_cache_update);
			foreach ($campaigns as $campaign) {
				$count++;
				if ($count > $max_length) {
					break;
				}

				$this->items[] = new NewsFlashMailChimpItem($campaign);
			}
		}

		return $this->items;
	}

	// }}}
	// {{{ protected function getCampaigns()

	protected function getCampaigns($max_length = 10,
		$force_cache_update = false)
	{
		$group_campaigns = array();

		$map = array();
		$interests = $this->getList()->getInterests();
		foreach ($interests as $grouping) {
			foreach ($grouping['groups'] as $group) {
				if (in_array($group['name'], $this->groups)) {
					$map[$group['name']] = $group['bit'];
				}
			}
		}

		$campaigns = $this->getList()->getCampaigns(
			array(
				'status' => 'sent,sending'
			)
		);

		// filter by interest group
		if (count($this->groups) > 0) {
			foreach ($campaigns as $campaign) {
				if (isset($campaign['segment_opts']) &&
					isset($campaign['segment_opts']['conditions'])) {
					$conditions = $campaign['segment_opts']['conditions'];
					foreach ($conditions as $condition) {
						if (strncmp($condition['field'], 'interests-', 10) === 0 &&
							$condition['op'] == 'one') {
							foreach ($condition['value'] as $value) {
								if (in_array($value, $map)) {
									$group_campaigns[] = $campaign;
									break 2;
								}
							}
						}
					}
				}
			}
		} else {
			$group_campaigns = $campaigns;
		}

		return $group_campaigns;
	}

	// }}}
	// {{{ protected function getList()

	protected function getList()
	{
		if (!($this->list instanceof DeliveranceMailChimpList)) {
			$this->list = new DeliveranceMailChimpList(
				$this->app,
				$this->list_name
			);
		}

		return $this->list;
	}

	// }}}
	// {{{ protected function getCacheKey()

	protected function getCacheKey($max_length = 10)
	{
		$groups = $this->groups;
		sort($groups);
		$groups = implode('-', $groups);

		return sprintf(
			'nf-mailchimp-%s-%s-%s',
			$this->list_name,
			$groups,
			intval($max_length)
		);
	}

	// }}}
}

?>

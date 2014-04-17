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
		$campaigns = array();
		$loaded = false;
		$last_update = 0;

		$cache_key = $this->getCacheKey($max_length);

		$cached_value = $this->app->getCacheValue($cache_key);
		if ($cached_value !== false) {
			$cached_value = unserialize($cached_value);
			if (isset($cached_value['last_update']) &&
				isset($cached_value['campaigns'])) {
				$last_update = $cached_value['last_update'] +
					60 * self::UPDATE_THRESHOLD;
			} else {
				$cached_value = false;
			}
		}

		// try to update the cache if the value is stale
		if (time() > $last_update || $force_cache_update) {
			try {
				// get map of group names to group bits
				$map = array();

				if (count($this->groups) > 0) {
					$interests = $this->getList()->getInterestGroupings();
					foreach ($interests as $grouping) {
						foreach ($grouping['groups'] as $group) {
							if (in_array($group['name'], $this->groups)) {
								$map[$group['name']] = $group['bit'];
							}
						}
					}
				}

				$campaigns = $this->getList()->getCampaigns(
					array(
						'status' => 'sent,sending'
					)
				);

				$campaigns = $this->filterCampaignsByInterestGroups(
					$campaigns,
					$map
				);

				$loaded = true;

				$value = array(
					'campaigns'   => $campaigns,
					'last_update' => time(),
				);

				$this->app->addCacheValue(serialize($value), $cache_key);
			} catch (DeliveranceAPIConnectionException $connection_exception) {
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

			} catch (DeliveranceException $exception) {
				$exception->processAndContinue();
			}
		}

		// Use cached version if it is valid, or if we failed to update from
		// the live feed.
		if ($cached_value && !$loaded) {
			$campaigns = $cached_value['campaigns'];
		}

		return $campaigns;
	}

	// }}}
	// {{{ protected function filterCampaignsByInterestGroups()

	protected function filterCampaignsByInterestGroups(array $campaigns,
		array $group_map)
	{
		$group_campaigns = array();

		// filter by interest group
		if (count($this->groups) === 0) {
			$group_campaigns = $campaigns;
		} else {
			foreach ($campaigns as $campaign) {
				if (isset($campaign['segment_opts']) &&
					isset($campaign['segment_opts']['conditions'])) {
					$conditions = $campaign['segment_opts']['conditions'];
					foreach ($conditions as $condition) {
						$field = $condition['field'];
						if (strncmp($field, 'interests-', 10) === 0 &&
							$condition['op'] == 'one') {
							foreach ($condition['value'] as $value) {
								if (in_array($value, $group_map)) {
									$group_campaigns[] = $campaign;
									break 2;
								}
							}
						}
					}
				}
			}
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

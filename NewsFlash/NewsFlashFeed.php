<?php

/**
 * @package   NewsFlash
 * @copyright 2012-2016 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class NewsFlashFeed
{
	// {{{ protected properties

	/**
	 * @var array
	 */
	protected $sources = array();

	// }}}
	// {{{ public function addSource()

	public function addSource(NewsFlashSource $source, $max_length = 10)
	{
		$this->sources[] = array(
			'source'     => $source,
			'max_length' => $max_length,
		);
	}

	// }}}
	// {{{ public function getItems()

	public function getItems($max_length = 10, $force_cache_update = false)
	{
		$items = array();

		foreach ($this->sources as $source) {
			$source_items = $source['source']->getItems(
				$source['max_length'],
				$force_cache_update
			);

			foreach ($source_items as $item) {
				// flatten dates to timestamps before sorting for speed
				$items[] = array(
					'item'      => $item,
					'timestamp' => $item->getDate()->getTimestamp(),
				);
			}
		}

		// sort item list
		usort($items, array($this, 'compareItems'));

		// truncate item list
		$items = array_slice($items, 0, $max_length);

		// return just the items, not the flattened timestamps
		$return = array();
		foreach ($items as $item) {
			$return[] = $item['item'];
		}

		return $return;
	}

	// }}}
	// {{{ protected function compareItems()

	protected function compareItems(array $a, array $b)
	{
		if ($a['timestamp'] > $b['timestamp']) {
			return -1;
		}

		if ($a['timestamp'] < $b['timestamp']) {
			return 1;
		}

		return 0;
	}

	// }}}
}

?>

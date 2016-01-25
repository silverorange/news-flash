<?php

/**
 * @package   NewsFlash
 * @copyright 2012-2016 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
abstract class NewsFlashItem
{
	// {{{ abstract public function getTitle()

	abstract public function getTitle();

	// }}}
	// {{{ abstract public function getBody()

	abstract public function getBody($secure = false);

	// }}}
	// {{{ abstract public function getLink()

	abstract public function getLink();

	// }}}
	// {{{ abstract public function getType()

	abstract public function getType();

	// }}}
	// {{{ abstract public function getIcon()

	abstract public function getIcon($secure = false, $size = 32);

	// }}}
	// {{{ abstract public function getDate()

	abstract public function getDate();

	// }}}
}

?>

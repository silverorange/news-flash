<?php

/**
 * Container for package wide static methods
 *
 * @package   NewsFlash
 * @copyright 2012-2017 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class NewsFlash
{
	// {{{ constants

	/**
	 * The gettext domain for this package
	 *
	 * This is used to support multiple locales.
	 */
	const GETTEXT_DOMAIN = 'news-flash';

	// }}}
	// {{{ private properties

	/**
	 * Whether or not this package is initialized
	 *
	 * @var boolean
	 */
	private static $is_initialized = false;

	// }}}
	// {{{ public static function _()

	/**
	 * Translates a phrase
	 *
	 * This is an alias for {@link Site::gettext()}.
	 *
	 * @param string $message the phrase to be translated.
	 *
	 * @return string the translated phrase.
	 */
	public static function _($message)
	{
		return self::gettext($message);
	}

	// }}}
	// {{{ public static function gettext()

	/**
	 * Translates a phrase
	 *
	 * This method relies on the php gettext extension and uses dgettext()
	 * internally.
	 *
	 * @param string $message the phrase to be translated.
	 *
	 * @return string the translated phrase.
	 */
	public static function gettext($message)
	{
		return dgettext(self::GETTEXT_DOMAIN, $message);
	}

	// }}}
	// {{{ public static function ngettext()

	/**
	 * Translates a plural phrase
	 *
	 * This method should be used when a phrase depends on a number. For
	 * example, use ngettext when translating a dynamic phrase like:
	 *
	 * - "There is 1 new item" for 1 item and
	 * - "There are 2 new items" for 2 or more items.
	 *
	 * This method relies on the php gettext extension and uses dngettext()
	 * internally.
	 *
	 * @param string $singular_message the message to use when the number the
	 *                                  phrase depends on is one.
	 * @param string $plural_message the message to use when the number the
	 *                                phrase depends on is more than one.
	 * @param integer $number the number the phrase depends on.
	 *
	 * @return string the translated phrase.
	 */
	public static function ngettext($singular_message, $plural_message, $number)
	{
		return dngettext(self::GETTEXT_DOMAIN,
			$singular_message, $plural_message, $number);
	}

	// }}}
	// {{{ public static function setupGettext()

	public static function setupGettext()
	{
		bindtextdomain(self::GETTEXT_DOMAIN, __DIR__.'/../locale');
		bind_textdomain_codeset(self::GETTEXT_DOMAIN, 'UTF-8');
	}

	// }}}
	// {{{ public static function getConfigDefinitions()

	/**
	 * Gets configuration definitions used by this package
	 *
	 * Applications should add these definitions to their config module before
	 * loading the application configuration.
	 *
	 * @return array the configuration definitions used by this package.
	 *
	 * @see SiteConfigModule::addDefinitions()
	 */
	public static function getConfigDefinitions()
	{
		return array(
		);
	}

	// }}}
	// {{{ public static function init()

	public static function init()
	{
		if (self::$is_initialized) {
			return;
		}

		Swat::init();
		Site::init();

		self::setupGettext();

		self::$is_initialized = true;
	}

	// }}}
	// {{{ private function __construct()

	/**
	 * Prevent instantiation of this static class
	 */
	private function __construct()
	{
	}

	// }}}
}

?>

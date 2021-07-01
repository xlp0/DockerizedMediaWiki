<?php

use MediaWiki\MediaWikiServices;

/**
 * Register the <html> tag in certain namespaces
 *
 * @author Ike Hecht
 */
class NamespaceHTML {

	/**
	 * Checks is raw HTML allowed wiki-wide.
	 * If it is allowed, extension won't do anything.
	 */
	public static function onRegistration() : void {
		$config = self::getConfig();

		if ( !$config->get( 'RawHtml' ) ) {
			$config->get( 'Hooks' )['ParserFirstCallInit'][] = 'NamespaceHTML::addNamespaceHTML';
		}
	}

	/**
	 * Where secure and relevant, adds support for <html> tag
	 *
	 * @param Parser $parser
	 */
	public static function addNamespaceHTML( Parser $parser ) {
		$parser->setHook( 'html', [ __CLASS__, 'html' ] );
	}

	/**
	 * Pass everything to core parser tag hook function for 'html'.
	 * Enabled when $wgRawHtml is disabled.
	 *
	 * This is potentially unsafe and should be used only in protected
	 * namespaces, as the contents are emitted as raw HTML.
	 *
	 * Uses undocumented extended tag hook return values, introduced in r61913.
	 *
	 * @param string $content
	 * @param array $attributes
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string[]|string Raw or escaped HTML
	 */
	public static function html( $content, array $attributes, Parser $parser, PPFrame $frame ) {
		$title = $parser->getTitle();

		if ( !isset( $title ) ) {
			return htmlspecialchars( Html::rawElement( 'html', $attributes, $content ) );
		}

		$titleNamespace = $title->getNamespace();
		$frameNamespace = $frame->getTitle()->getNamespace();

		# Ideally, this check should take place in function 'addNamespaceHTML'
		# but for some reason, $parser->getTitle() often returns null there.
		$config = self::getConfig();
		$allowedNamespaces = array_intersect(
			$config->get( 'RawHtmlNamespaces' ), [ $titleNamespace, $frameNamespace ]
		);

		if ( (bool)$allowedNamespaces ) {
			// copied from CoreTagHooks::html
			return [
				$content,
				'markerType' => 'nowiki'
			];
		}

		# raw HTML not allowed here so send out escaped text
		return htmlspecialchars( Html::rawElement( 'html', $attributes, $content ) );
	}

	/**
	 * Get a Config object
	 * @return Config
	 */
	private static function getConfig() : Config {
		return MediaWikiServices::getInstance()->getMainConfig();
	}
}

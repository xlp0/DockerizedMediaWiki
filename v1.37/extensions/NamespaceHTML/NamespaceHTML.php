<?php
/**
 * NamespaceHTML - allows raw HTML in specified namespaces
 *
 * To activate this extension, add the following into your LocalSettings.php file:
 * wfLoadExtension( 'NamespaceHTML' );
 * $wgRawHtmlNamespaces = []; # must be set!
 *
 * @ingroup Extensions
 * @author Ike Hecht
 * @version 0.2
 * @link https://www.mediawiki.org/wiki/Extension:NamespaceHTML Documentation
 * @license GPL-2.0-or-later
 */
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'NamespaceHTML' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['NamespaceHTML'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for the NamespaceHTML extension. ' .
		'Please use wfLoadExtension() instead, ' .
		'see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the NamespaceHTML extension requires MediaWiki 1.29+' );
}

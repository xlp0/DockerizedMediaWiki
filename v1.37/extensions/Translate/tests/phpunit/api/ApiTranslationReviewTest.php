<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;

/** @group Database */
class ApiTranslationReviewTest extends MediaWikiIntegrationTestCase {
	protected function setUp(): void {
		parent::setUp();

		$this->setMwGlobals( [
			'wgGroupPermissions' => [
				'sysop' => [
					'translate-messagereview' => true,
				],
			],
			'wgTranslateMessageNamespaces' => [ NS_MEDIAWIKI ],
		] );
		$this->setTemporaryHook( 'TranslatePostInitGroups', [ $this, 'getTestGroups' ] );

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => new HashBagOStuff() ] ) );
		$mg->recache();

		MessageIndex::setInstance( new HashMessageIndex() );
		MessageIndex::singleton()->rebuild();
	}

	public function getTestGroups( &$list ) {
		$messages = [
			'ugakey1' => 'value1',
			'ugakey2' => 'value2',
		];

		$list['testgroup'] = new MockWikiMessageGroup( 'testgroup', $messages );

		return false;
	}

	public function testgetReviewBlockers() {
		$superUser1 = $this->getMutableTestUser( [ 'sysop', 'bureaucrat' ] )->getUser();

		$superUser2 = $this->getMutableTestUser( [ 'sysop', 'bureaucrat' ] )->getUser();

		$plainUser = $this->getMutableTestUser()->getUser();

		$title = Title::makeTitle( NS_MEDIAWIKI, 'Ugakey1/fi' );
		$content = ContentHandler::makeContent( 'trans1', $title );

		WikiPage::factory( $title )->doUserEditContent(
			$content,
			$superUser1,
			__METHOD__
		);

		$title = Title::makeTitle( NS_MEDIAWIKI, 'Ugakey2/fi' );
		$content = ContentHandler::makeContent( '!!FUZZY!!trans2', $title );

		WikiPage::factory( $title )->doUserEditContent(
			$content,
			$superUser2,
			__METHOD__
		);

		$title = Title::makeTitle( NS_MEDIAWIKI, 'Ugakey3/fi' );
		$content = ContentHandler::makeContent( 'unknown message', $title );
		WikiPage::factory( $title )->doUserEditContent(
			$content,
			$superUser1,
			__METHOD__
		);

		$testcases = [
			[
				'permissiondenied',
				$plainUser,
				'Ugakey1/fi',
				'Unpriviledged user is not allowed to change state'
			],
			[
				'owntranslation',
				$superUser1,
				'Ugakey1/fi',
				'Cannot approve own translation'
			],
			[
				'fuzzymessage',
				$superUser1,
				'Ugakey2/fi',
				'Cannot approve fuzzy translation'
			],
			[
				'unknownmessage',
				$superUser1,
				'Ugakey3/fi',
				'Cannot approve unknown translation'
			],
			[
				'',
				$superUser2,
				'Ugakey1/fi',
				'Can approve non-fuzzy known non-own translation'
			],
		];

		foreach ( $testcases as $case ) {
			list( $expected, $user, $page, $comment ) = $case;
			$revRecord = MediaWikiServices::getInstance()
				->getRevisionLookup()
				->getRevisionByTitle( new TitleValue( NS_MEDIAWIKI, $page ) );
			$ok = ApiTranslationReview::getReviewBlockers( $user, $revRecord );
			$this->assertEquals( $expected, $ok, $comment );
		}
	}
}

<?php
/**
 * Unit tests for message group state change api.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;

/** @group Database */
class ApiGroupReviewTest extends MediaWikiIntegrationTestCase {
	public function testGetSetState() {
		$group = new WikiMessageGroup( 'testgroup', 'wewgweg' );

		$ok = ApiGroupReview::changeState( $group, 'fi', 'newstate', FuzzyBot::getUser() );
		$this->assertTrue( $ok, 'state was changed' );

		$state = ApiGroupReview::getState( $group, 'fi' );
		$this->assertEquals( 'newstate', $state, 'state was changed to expected value' );

		$ok = ApiGroupReview::changeState( $group, 'fi', 'newstate', FuzzyBot::getUser() );
		$this->assertFalse( $ok, 'state was not changed again' );
	}
}

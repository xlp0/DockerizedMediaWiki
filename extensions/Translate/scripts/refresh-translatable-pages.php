<?php
/**
 * Script to ensure all translation pages are up to date.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @file
 */

// Standard boilerplate to define $IP
use MediaWiki\MediaWikiServices;

if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = __DIR__;
	$IP = "$dir/../../..";
}
require_once "$IP/maintenance/Maintenance.php";

/**
 * Script to ensure all translation pages are up to date
 * @since 2013-04
 */
class RefreshTranslatablePages extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Ensure all translation pages are up to date.' );
		$this->setBatchSize( 300 );
		$this->addOption( 'jobqueue', 'Use JobQueue (asynchronous)' );
		$this->requireExtension( 'Translate' );
	}

	public function execute() {
		$groups = MessageGroups::singleton()->getGroups();
		$lbFactory = MediaWikiServices::getInstance()->getDBLoadBalancerFactory();
		$counter = 0;
		$useJobQueue = $this->hasOption( 'jobqueue' );

		/** @var MessageGroup $group */
		foreach ( $groups as $group ) {
			if ( !$group instanceof WikiPageMessageGroup ) {
				continue;
			}

			$counter++;
			if ( ( $counter % $this->mBatchSize ) === 0 ) {
				$lbFactory->waitForReplication();
			}

			$page = TranslatablePage::newFromTitle( $group->getTitle() );
			$jobs = TranslationsUpdateJob::getRenderJobs( $page );
			if ( $useJobQueue ) {
				JobQueueGroup::singleton()->push( $jobs );
			} else {
				foreach ( $jobs as $job ) {
					$job->run();
				}
			}
		}

		if ( $useJobQueue ) {
			$this->output( "Queued refresh for $counter translatable pages.\n" );
		} else {
			$this->output( "Refreshed $counter translatable pages.\n" );
		}
	}
}

$maintClass = RefreshTranslatablePages::class;
require_once RUN_MAINTENANCE_IF_MAIN;

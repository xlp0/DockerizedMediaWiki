<?php
/**
 * Command line script to mark translations fuzzy (similar to gettext fuzzy).
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2007-2013, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0-or-later
 */

// Standard boilerplate to define $IP
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use Wikimedia\Rdbms\IResultWrapper;

if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = __DIR__;
	$IP = "$dir/../../..";
}
require_once "$IP/maintenance/Maintenance.php";

# Override the memory limit for wfShellExec, 100 MB appears to be too little
$wgMaxShellMemory = 1024 * 200;

class Fuzzy extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Fuzzy bot command line script.' );
		$this->addArg(
			'arg',
			'Title pattern or username if user option is provided.'
		);
		$this->addOption(
			'really',
			'(optional) Really fuzzy, no dry-run'
		);
		$this->addOption(
			'skiplanguages',
			'(optional) Skip some languages (comma separated)',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'comment',
			'(optional) Comment for updating',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'user',
			'(optional) Fuzzy the translations made by user given as an argument.',
			false, /*required*/
			false /*has arg*/
		);
		$this->requireExtension( 'Translate' );
	}

	public function execute() {
		$skipLanguages = [];
		if ( $this->hasOption( 'skiplanguages' ) ) {
			$skipLanguages = array_map(
				'trim',
				explode( ',', $this->getOption( 'skiplanguages' ) )
			);
		}

		if ( $this->hasOption( 'user' ) ) {
			$user = User::newFromName( $this->getArg( 0 ) );
			$pages = FuzzyScript::getPagesForUser( $user, $skipLanguages );
		} else {
			$pages = FuzzyScript::getPagesForPattern( $this->getArg( 0 ), $skipLanguages );
		}

		$bot = new FuzzyScript( $pages );
		$bot->comment = $this->getOption( 'comment' );
		$bot->dryrun = !$this->hasOption( 'really' );
		$bot->setProgressCallback( [ $this, 'myOutput' ] );
		$bot->execute();
	}

	/**
	 * Public alternative for protected Maintenance::output() as we need to get
	 * messages from the ChangeSyncer class to the commandline.
	 * @param string $text The text to show to the user
	 * @param string|null $channel Unique identifier for the channel.
	 */
	public function myOutput( $text, $channel = null ) {
		$this->output( $text, $channel );
	}
}

/**
 * Class for marking translation fuzzy.
 */
class FuzzyScript {
	/** @var bool Check for configuration problems. */
	private $allclear = true;
	/** @var callable Function to report progress updates */
	protected $progressCallback;
	/** @var bool Dont do anything unless confirmation is given */
	public $dryrun = true;
	/** @var string Edit summary. */
	public $comment;
	/** @var array[] */
	public $pages;

	/** @param array $pages */
	public function __construct( $pages ) {
		$this->pages = $pages;
	}

	public function setProgressCallback( $callback ) {
		$this->progressCallback = $callback;
	}

	/// @see Maintenance::output for param docs
	protected function reportProgress( $text, $channel ) {
		if ( is_callable( $this->progressCallback ) ) {
			call_user_func( $this->progressCallback, $text, $channel );
		}
	}

	public function execute() {
		if ( !$this->allclear ) {
			return;
		}

		$msgs = $this->pages;
		$count = count( $msgs );
		$this->reportProgress( "Found $count pages to update.", 'pagecount' );

		foreach ( $msgs as $phpIsStupid ) {
			[ $title, $text ] = $phpIsStupid;
			$this->updateMessage( $title, TRANSLATE_FUZZY . $text, $this->dryrun, $this->comment );
			unset( $phpIsStupid );
		}
	}

	/**
	 * Gets the message contents from database rows.
	 * @param IResultWrapper $rows
	 * @return array containing page titles and the text content of the page
	 */
	private static function getMessageContentsFromRows( $rows ) {
		$revStore = MediaWikiServices::getInstance()->getRevisionStore();
		$messagesContents = [];
		$slots = $revStore->getContentBlobsForBatch( $rows, [ SlotRecord::MAIN ] )->getValue();
		foreach ( $rows as $row ) {
			$title = Title::makeTitle( $row->page_namespace, $row->page_title );
			if ( isset( $slots[$row->rev_id] ) ) {
				$text = $slots[$row->rev_id][SlotRecord::MAIN]->blob_data;
			} else {
				$text = $revStore->newRevisionFromRow( $row, IDBAccessObject::READ_NORMAL, $title )
					->getContent( SlotRecord::MAIN )
					->getNativeData();
			}
			$messagesContents[] = [ $title, $text ];
		}
		return $messagesContents;
	}

	/// Searches pages that match given patterns
	public static function getPagesForPattern( $pattern, $skipLanguages = [] ) {
		global $wgTranslateMessageNamespaces;
		$dbr = wfGetDB( DB_REPLICA );

		$search = [];
		foreach ( (array)$pattern as $title ) {
			$title = Title::newFromText( $title );
			$ns = $title->getNamespace();
			if ( !isset( $search[$ns] ) ) {
				$search[$ns] = [];
			}
			$search[$ns][] = 'page_title' . $dbr->buildLike( $title->getDBkey(), $dbr->anyString() );
		}

		$title_conds = [];
		foreach ( $search as $ns => $names ) {
			if ( $ns === NS_MAIN ) {
				$ns = $wgTranslateMessageNamespaces;
			}
			$titles = $dbr->makeList( $names, LIST_OR );
			$title_conds[] = $dbr->makeList( [ 'page_namespace' => $ns, $titles ], LIST_AND );
		}

		$conds = [
			'page_latest=rev_id',
			$dbr->makeList( $title_conds, LIST_OR ),
		];

		if ( count( $skipLanguages ) ) {
			$skiplist = $dbr->makeList( $skipLanguages );
			$conds[] = "substring_index(page_title, '/', -1) NOT IN ($skiplist)";
		}

		$revStore = MediaWikiServices::getInstance()->getRevisionStore();
		$queryInfo = $revStore->getQueryInfo( [ 'page' ] );
		$rows = $dbr->select(
			$queryInfo['tables'],
			$queryInfo['fields'],
			$conds,
			__METHOD__,
			[],
			$queryInfo['joins']
		);
		$messagesContents = self::getMessageContentsFromRows( $rows );
		$rows->free();
		return $messagesContents;
	}

	public static function getPagesForUser( User $user, $skipLanguages = [] ) {
		global $wgTranslateMessageNamespaces;
		$dbr = wfGetDB( DB_REPLICA );

		$revWhere = ActorMigration::newMigration()->getWhere( $dbr, 'rev_user', $user );
		$conds = [
			'page_latest=rev_id',
			$revWhere['conds'],
			'page_namespace' => $wgTranslateMessageNamespaces,
			'page_title' . $dbr->buildLike( $dbr->anyString(), '/', $dbr->anyString() ),
		];
		if ( count( $skipLanguages ) ) {
			$skiplist = $dbr->makeList( $skipLanguages );
			$conds[] = "substring_index(page_title, '/', -1) NOT IN ($skiplist)";
		}

		$revStore = MediaWikiServices::getInstance()->getRevisionStore();
		$queryInfo = $revStore->getQueryInfo( [ 'page', 'user' ] );
		$rows = $dbr->select(
			$queryInfo['tables'],
			$queryInfo['fields'],
			$conds,
			__METHOD__,
			[],
			$queryInfo['joins'] + $revWhere['joins']
		);

		$messagesContents = self::getMessageContentsFromRows( $rows );
		$rows->free();
		return $messagesContents;
	}

	/**
	 * Does the actual edit if possible.
	 * @param Title $title
	 * @param string $text
	 * @param bool $dryrun Whether to really do it or just show what would be done.
	 * @param string|null $comment Edit summary.
	 */
	private function updateMessage( $title, $text, $dryrun, $comment = null ) {
		global $wgTranslateDocumentationLanguageCode;

		$this->reportProgress( "Updating {$title->getPrefixedText()}... ", $title );
		if ( !$title instanceof Title ) {
			$this->reportProgress( 'INVALID TITLE!', $title );

			return;
		}

		$items = explode( '/', $title->getText(), 2 );
		if ( isset( $items[1] ) && $items[1] === $wgTranslateDocumentationLanguageCode ) {
			$this->reportProgress( 'IGNORED!', $title );

			return;
		}

		if ( $dryrun ) {
			$this->reportProgress( 'DRY RUN!', $title );

			return;
		}

		$wikipage = new WikiPage( $title );
		$content = ContentHandler::makeContent( $text, $title );
		$status = TranslateUtils::doPageEdit(
			$wikipage,
			$content,
			FuzzyBot::getUser(),
			$comment ?: 'Marking as fuzzy',
			EDIT_FORCE_BOT | EDIT_UPDATE
		);

		$success = $status === true || ( is_object( $status ) && $status->isOK() );
		$this->reportProgress( $success ? 'OK' : 'FAILED', $title );
	}
}

$maintClass = Fuzzy::class;
require_once RUN_MAINTENANCE_IF_MAIN;

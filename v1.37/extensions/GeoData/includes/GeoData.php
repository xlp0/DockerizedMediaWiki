<?php

namespace GeoData;

use MediaWiki\MediaWikiServices;
use Title;
use Wikimedia\Rdbms\Database;

class GeoData {
	/**
	 * Returns primary coordinates of the given page, if any
	 * @param Title $title
	 * @return Coord|bool Coordinates or false
	 */
	public static function getPageCoordinates( Title $title ) {
		$coords = self::getAllCoordinates( $title->getArticleID(), [ 'gt_primary' => 1 ] );
		if ( $coords ) {
			return $coords[0];
		}
		return false;
	}

	/**
	 * Retrieves all coordinates for the given page id
	 *
	 * @param int $pageId ID of the page
	 * @param array $conds Conditions for Database::select()
	 * @param int $dbType Database to select from DB_MASTER or DB_REPLICA
	 * @return Coord[]
	 */
	public static function getAllCoordinates( $pageId, $conds = [], $dbType = DB_REPLICA ) {
		$db = self::getDB( $dbType );
		$conds['gt_page_id'] = $pageId;
		$res = $db->select( 'geo_tags', Coord::getColumns(), $conds, __METHOD__ );
		$coords = [];
		foreach ( $res as $row ) {
			$coords[] = Coord::newFromRow( $row );
		}
		return $coords;
	}

	/**
	 * @param int $dbType DB_MASTER or DB_REPLICA
	 * @return Database
	 */
	private static function getDB( $dbType ) {
		return MediaWikiServices::getInstance()
			->getDBLoadBalancer()
			->getConnection( $dbType );
	}
}

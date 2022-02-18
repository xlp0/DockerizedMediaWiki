<?php
/**
 * Displays an interface to let users recreate data via the Cargo
 * extension.
 *
 * @author Yaron Koren
 * @ingroup Cargo
 */

class CargoPageValues extends IncludableSpecialPage {
	public $mTitle;

	public function __construct( $title = null ) {
		parent::__construct( 'PageValues' );

		$this->mTitle = $title;
	}

	public function execute( $subpage = null ) {
		global $wgCargoPageDataColumns, $wgCargoFileDataColumns;

		if ( $subpage ) {
			// Allow inclusion with e.g. {{Special:PageValues/Book}}
			$this->mTitle = Title::newFromText( $subpage );
		}

		// If no title, or a nonexistent title, was set, just exit out.
		// @TODO - display an error message.
		if ( $this->mTitle == null || !$this->mTitle->exists() ) {
			return true;
		}

		$out = $this->getOutput();

		$this->setHeaders();

		$pageName = $this->mTitle->getPrefixedText();
		$out->setPageTitle( $this->msg( 'cargo-pagevaluesfor', $pageName )->text() );

		$text = '';

		$dbw = wfGetDB( DB_MASTER );

		$tableNames = [];

		// Make _pageData and _fileData the first two tables, if
		// either of them hold any real data.
		if ( count( $wgCargoPageDataColumns ) > 0 ) {
			$tableNames[] = '_pageData';
		}
		if ( count( $wgCargoFileDataColumns ) > 0 ) {
			$tableNames[] = '_fileData';
		}

		$res = $dbw->select(
			'cargo_pages', 'table_name', [ 'page_id' => $this->mTitle->getArticleID() ] );
		foreach ( $res as $row ) {
			$tableNames[] = $row->table_name;
		}

		$toc = Linker::tocIndent();
		$tocLength = 0;

		foreach ( $tableNames as $tableName ) {
			try {
				$queryResults = $this->getRowsForPageInTable( $tableName );
			} catch ( Exception $e ) {
				// Most likely this is because the _pageData
				// table doesn't exist.
				continue;
			}
			$numRowsOnPage = count( $queryResults );

			// Hide _fileData if it's empty - we do this only for _fileData,
			// as another table having 0 rows can indicate an error, and we'd
			// like to preserve that information for debugging purposes.
			if ( $numRowsOnPage === 0 && $tableName === '_fileData' ) {
				continue;
			}

			$tableLink = $this->getTableLink( $tableName );

			$tableSectionHeader = $this->msg( 'cargo-pagevalues-tablevalues', $tableLink )->text();
			$tableSectionTocDisplay = $this->msg( 'cargo-pagevalues-tablevalues', $tableName )->text();
			$tableSectionAnchor = $this->msg( 'cargo-pagevalues-tablevalues', $tableName )->escaped();
			$tableSectionAnchor = Sanitizer::escapeIdForAttribute( $tableSectionAnchor );

			// We construct the table of contents at the same time
			// as the main text.
			$toc .= Linker::tocLine( $tableSectionAnchor, $tableSectionTocDisplay,
				$this->getLanguage()->formatNum( ++$tocLength ), 1 ) . Linker::tocLineEnd();

			$h2 = Html::rawElement( 'h2', null,
				Html::rawElement( 'span', [ 'class' => 'mw-headline', 'id' => $tableSectionAnchor ], $tableSectionHeader ) );

			$text .= Html::rawElement( 'div', [ 'class' => 'cargo-pagevalues-tableinfo' ],
				$h2 . $this->msg( "cargo-pagevalues-tableinfo-numrows", $numRowsOnPage )
			);

			foreach ( $queryResults as $rowValues ) {
				$tableContents = '';
				$fieldInfo = $this->getInfoForAllFields( $tableName );
				foreach ( $rowValues as $field => $value ) {
					// @HACK - this check should ideally
					// be done earlier.
					if ( strpos( $field, '__precision' ) !== false ) {
						continue;
					}
					$tableContents .= $this->printRow( $field, $value, $fieldInfo[ $field ] );
				}
				$text .= $this->printTable( $tableContents );
			}
		}

		// Show table of contents only if there are enough sections.
		if ( count( $tableNames ) >= 3 ) {
			$toc = Linker::tocList( $toc );
			$out->addHTML( $toc );
			$out->addModuleStyles( 'mediawiki.toc.styles' );
		}

		$out->addHTML( $text );
		$out->addModules( 'ext.cargo.pagevalues' );

		return true;
	}

	private function getTableLink( $tableName ) {
		$originalTableName = str_replace( '__NEXT', '', $tableName );
		$isReplacementTable = substr( $tableName, -6 ) == '__NEXT';
		$viewURL = SpecialPage::getTitleFor( 'CargoTables' )->getFullURL() . "/$originalTableName";
		if ( $isReplacementTable ) {
			$viewURL .= strpos( $viewURL, '?' ) ? '&' : '?';
			$viewURL .= "_replacement";
		}

		return Html::element( 'a', [ 'href' => $viewURL ], $tableName );
	}

	/**
	 * Used to get the information about field type and the list
	 * of allowed values(if any) of all fields of a table
	 *
	 */
	private function getInfoForAllFields( $tableName ) {
		$tableSchemas = CargoUtils::getTableSchemas( [ $tableName ] );
		$fieldDescriptions = $tableSchemas[ $tableName ]->mFieldDescriptions;
		$fieldInfo = [];
		foreach ( $fieldDescriptions as $fieldName => $fieldDescription ) {
			$typeDesc = $fieldDescription->mType;
			if ( $fieldDescription->mIsList ) {
				$typeDesc = $this->msg( 'cargo-cargotables-listof', $typeDesc )->parse();
			}
			$fieldInfo[ $fieldName ][ 'field type' ] = $typeDesc;
			$delimiter = strlen( $fieldDescription->getDelimiter() ) ? $fieldDescription->getDelimiter() : ',';
			if ( is_array( $fieldDescription->mAllowedValues ) ) {
				$fieldInfo[ $fieldName ][ 'allowed values' ] = implode( $delimiter . " ", $fieldDescription->mAllowedValues );
			} else {
				$fieldInfo[ $fieldName ][ 'allowed values' ] = '';
			}
		}
		return $fieldInfo;
	}

	public function getRowsForPageInTable( $tableName ) {
		$cdb = CargoUtils::getDB();

		$sqlQuery = new CargoSQLQuery();
		$sqlQuery->mAliasedTableNames = [ $tableName => $tableName ];

		$tableSchemas = CargoUtils::getTableSchemas( [ $tableName ] );
		$sqlQuery->mTableSchemas = $tableSchemas;

		$aliasedFieldNames = [];
		foreach ( $tableSchemas[$tableName]->mFieldDescriptions as $fieldName => $fieldDescription ) {
			if ( $fieldDescription->mIsHidden ) {
				// @TODO - do some custom formatting
			}

			// $fieldAlias = str_replace( '_', ' ', $fieldName );
			$fieldAlias = $fieldName;

			if ( $fieldDescription->mIsList ) {
				$aliasedFieldNames[$fieldAlias] = $fieldName . '__full';
			} elseif ( $fieldDescription->mType == 'Coordinates' ) {
				$aliasedFieldNames[$fieldAlias] = $fieldName . '__full';
			} else {
				$aliasedFieldNames[$fieldAlias] = $fieldName;
			}
		}

		$sqlQuery->mAliasedFieldNames = $aliasedFieldNames;
		$sqlQuery->mOrigAliasedFieldNames = $aliasedFieldNames;
		$sqlQuery->setDescriptionsAndTableNamesForFields();
		$sqlQuery->handleDateFields();
		$sqlQuery->mWhereStr = $cdb->addIdentifierQuotes( '_pageID' ) . " = " . $this->mTitle->getArticleID();

		$queryResults = $sqlQuery->run();
		$queryDisplayer = CargoQueryDisplayer::newFromSQLQuery( $sqlQuery );
		$formattedQueryResults = $queryDisplayer->getFormattedQueryResults( $queryResults );
		return $formattedQueryResults;
	}

	/**
	 * Based on MediaWiki's InfoAction::addRow()
	 */
	public function printRow( $name, $value, $fieldInfo ) {
		if ( $name == '_fullText' && strlen( $value ) > 300 ) {
			$value = substr( $value, 0, 300 ) . ' ...';
		}
		return Html::rawElement( 'tr', [],
			Html::rawElement( 'td',
			[
				'class' => 'cargo-pagevalues-fieldinfo',
				'data-field-type' => $fieldInfo[ 'field type' ],
				'data-allowed-values' => $fieldInfo[ 'allowed values' ]
			], $name . "&nbsp;&nbsp;&nbsp;" ) .
			Html::rawElement( 'td', [], $value )
		);
	}

	/**
	 * Based on MediaWiki's InfoAction::addTable()
	 */
	public function printTable( $tableContents ) {
		return Html::rawElement( 'table', [ 'class' => 'wikitable mw-page-info' ],
			$tableContents ) . "\n";
	}

	/**
	 * Don't list this in Special:SpecialPages.
	 */
	public function isListed() {
		return false;
	}
}

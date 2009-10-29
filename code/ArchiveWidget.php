<?php
/**
 * Shows a widget with viewing blog entries
 * by months or years.
 * 
 * @package blog
 */
class ArchiveWidget extends Widget {
	
	static $db = array(
		'DisplayMode' => 'Varchar'
	);
	static $has_one = array();
	
	static $has_many = array();
	
	static $many_many = array();
	
	static $belongs_many_many = array();
	
	static $defaults = array(
		'DisplayMode' => 'month'
	);
	
	static $title = 'Browse by Date';

	static $cmsTitle = 'Blog Archive';
	
	static $description = 'Show a list of months or years in which there are blog posts, and provide links to them.';
	
	function getCMSFields() {
		return new FieldSet(
			new OptionsetField(
				'DisplayMode',
				_t('ArchiveWidget.DispBY', 'Display by'),
				array(
					'month' => _t('ArchiveWidget.MONTH', 'month'),
					'year' => _t('ArchiveWidget.YEAR', 'year')
				)
			)
		);
	}
	
	function Dates() {
		Requirements::themedCSS('archivewidget');
		
		$results = new DataObjectSet();
		$container = BlogTree::current();
		$ids = $container->BlogHolderIDs();
		
		$stage = Versioned::current_stage();
		$suffix = (!$stage || $stage == 'Stage') ? "" : "_$stage";

		if($this->DisplayMode == 'month') {
			if(defined('DB::USE_ANSI_SQL')) {
				$sqlResults = DB::query("
					SELECT DISTINCT MONTH(\"Date\") AS \"Month\", YEAR(\"Date\") AS \"Year\"
					FROM \"SiteTree$suffix\" INNER JOIN \"BlogEntry$suffix\" ON \"SiteTree$suffix\".\"ID\" = \"BlogEntry$suffix\".\"ID\"
					WHERE \"ParentID\" IN (" . implode(', ', $ids) . ")
					ORDER BY \"Year\" DESC, \"Month\" DESC;"
				);
			} else {
				$sqlResults = DB::query("
					SELECT DISTINCT MONTH(`Date`) AS `Month`, YEAR(`Date`) AS `Year` 
					FROM `SiteTree$suffix` NATURAL JOIN `BlogEntry$suffix` 
					WHERE `ParentID` IN (" . implode(', ', $ids) . ")
					ORDER BY `Year` DESC, `Month` DESC;"
				);
			}
		} else {
			if(defined('DB::USE_ANSI_SQL')) {
				$sqlResults = DB::query("
					SELECT DISTINCT YEAR(\"Date\") AS \"Year\" 
					FROM \"SiteTree$suffix\" INNER JOIN \"BlogEntry$suffix\" ON \"SiteTree$suffix\".\"ID\" = \"BlogEntry$suffix\".\"ID\"
					WHERE \"ParentID\" IN (" . implode(', ', $ids) . ")
					ORDER BY \"Year\" DESC"
				);
			} else {
				$sqlResults = DB::query("
					SELECT DISTINCT YEAR(`Date`) AS `Year` 
					FROM `SiteTree$suffix` NATURAL JOIN `BlogEntry$suffix` 
					WHERE `ParentID` in (".implode(', ',$ids).")
					ORDER BY `Year` DESC"
				);
			}
		}
		
		if($sqlResults) foreach($sqlResults as $sqlResult) {
			$isMonthDisplay = $this->DisplayMode == 'month';
			
			$monthVal = (isset($sqlResult['Month'])) ? (int) $sqlResult['Month'] : 1;
			$month = ($isMonthDisplay) ? $monthVal : 1;
			$year = ($sqlResult['Year']) ? (int) $sqlResult['Year'] : date('Y');
			
			$date = DBField::create('Date', array(
				'Day' => 1,
				'Month' => $month,
				'Year' => $year
			));
			
			if($isMonthDisplay) {
				$link = $container->Link($sqlResult['Year']) . '/' . sprintf("%'02d", $monthVal);
			} else {
				$link = $container->Link($sqlResult['Year']);
			}
			
			$results->push(new ArrayData(array(
				'Date' => $date,
				'Link' => $link
			)));
		}
		
		return $results;
	}
	
}

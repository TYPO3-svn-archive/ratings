<?php
if (!defined ('TYPO3_MODE')) die('Access denied.');

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_ratings_pi1.php', '_pi1', 'list_type', 1);

// eID
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['tx_ratings_ajax'] = 'EXT:ratings/class.tx_ratings_ajax.php';

// Extra markers hook for tt_news
if (t3lib_extMgm::isLoaded('tt_news')) {
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['extraItemMarkerHook'][$_EXTKEY] = 'EXT:ratings/class.tx_ratings_ttnews.php:&tx_ratings_ttnews';
}
?>
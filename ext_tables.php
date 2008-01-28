<?php
if (!defined ('TYPO3_MODE')) die('Access denied.');
$TCA['tx_ratings_data'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:ratings/locallang_db.xml:tx_ratings_data',
		'label'     => 'reference',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate DESC',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_ratings_data.gif',
	),
);

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1'] = 'layout,select_key';

t3lib_extMgm::addPlugin(array('LLL:EXT:ratings/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');

if (TYPO3_MODE=='BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_ratings_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_ratings_pi1_wizicon.php';
//	t3lib_extMgm::insertModuleFunction(
//		'web_info',
//		'tx_ratings_modfunc1',
//		t3lib_extMgm::extPath($_EXTKEY).'modfunc1/class.tx_ratings_modfunc1.php',
//		'LLL:EXT:ratings/locallang_db.xml:moduleFunction.tx_ratings_modfunc1'
//	);
}

t3lib_extMgm::addStaticFile($_EXTKEY,'static/Ratings/', 'Ratings');
?>
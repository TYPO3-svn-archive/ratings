<?php
if (!defined ('TYPO3_MODE')) die('Access denied.');

$TCA['tx_ratings_data'] = array (
	'ctrl' => $TCA['tx_ratings_data']['ctrl'],
//	'interface' => array (
//		'showRecordFieldList' => 'reference,value'
//	),
//	'feInterface' => $TCA['tx_ratings_data']['feInterface'],
	'columns' => array (
		'reference' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:ratings/locallang_db.xml:tx_ratings_data.reference',
			'config' => array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'NO_TABLE_NAME_AVAILABLE',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'value' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:ratings/locallang_db.xml:tx_ratings_data.value',
			'config' => array (
				'type'     => 'input',
				'size'     => '4',
				'max'      => '4',
				'eval'     => 'int',
				'checkbox' => '0',
				'range'    => array (
					'upper' => '1000',
					'lower' => '10'
				),
				'default' => 0
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'reference;;;;1-1-1, value')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);
?>
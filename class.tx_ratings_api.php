<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Dmitry Dulepov [netcreators] <dmitry@typo3.org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(t3lib_extMgm::extPath('lang', 'lang.php'));

/**
 * This class contains API for ratings. There are two ways to use this API:
 * <ul>
 * <li>Call {@link getRatingValueForRef} to obtain rating value and process it yourself</li>
 * <li>Call {@link getRatingDisplay} to format and display rating value along with a control to change rating</li>
 * </ul>
 *
 * @author	Dmitry Dulepov [netcreators] <dmitry@typo3.org>
 * @package	TYPO3
 * @subpackage	tx_ratings
 */
class tx_ratings_api {

	/**
	 * Instance of tslib_cObj
	 *
	 * @var	tslib_cObj
	 */
	protected $cObj;

	/**
	 * Creates an instance of this class
	 */
	public function __construct() {
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
	}

	/**
	 * Fetches data and calculates rating value for $ref. Rating values are from
	 * 0 to 100.
	 *
	 * @param	string	$ref	Reference to item in TYPO3 "datagroup" format (like tt_content_10)
	 * @param	array	$conf	Configuration array
	 * @return	float	Rating value (from 0 to 100)
	 */
	public function getRatingValueForRef($ref, $conf = null) {
		if (is_null($conf)) {
			$conf = $this->getDefaultConfig();
		}
		$rating = $this->getRatingInfo($ref, $conf);
		return max(0, 100*(floatval($rating['rating'])-intval($conf['minValue']))/(intval($conf['maxValue'])-intval($conf['minValue'])));
	}

	/**
	 * Retrieves default configuration of ratings.
	 * Uses plugin.tx_ratings_pi1 from page TypoScript template
	 *
	 * @return	array	TypoScript configuration for ratings
	 */
	public function getDefaultConfig() {
		return $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_ratings_pi1.'];
	}

	/**
	 * Generates HTML code for displaying ratings.
	 *
	 * @param	string	$ref	Reference
	 * @param	array	$conf	Configuration array
	 * @return	string	HTML content
	 */
	public function getRatingDisplay($ref, $conf = null) {
		if (is_null($conf)) {
			$conf = $this->getDefaultConfig();
		}

		// Get template
		$template = $this->cObj->fileResource($conf['templateFile']);
		if (!$template) {
			t3lib_div::devLog('Unable to load template code from "' . $conf['templateFile'] . '"', 'ratings', 3);
			return '';
		}

		$this->addHeaderParts($template);
		return $this->generateRatingContent($template, $conf);
	}


	/**
	 * Adds header parts from the template to the TSFE.
	 * It fetches subpart identified by ###HEADER_PARTS### and replaces ###SITE_REL_PATH### with site-relative part to the extension.
	 *
	 * @param	string	$subpart	Subpart from template to add.
	 */
	protected function addHeaderParts($template) {
		$subPart = $this->cObj->getSubpart($template, '###HEADER_PARTS###');
		$key = 'tx_ratings_' . md5($subPart);
		if (!isset($GLOBALS['TSFE']->additionalHeaderData[$key])) {
			$GLOBALS['TSFE']->additionalHeaderData[$key] =
				$this->cObj->substituteMarker($template, '###SITE_REL_PATH###', t3lib_extMgm::siteRelPath('ratings'));
		}
	}


	/**
	 * Fetches rating information for $ref
	 *
	 * @param	string	$ref	Reference in TYPO3 "datagroup" format (i.e. tt_content_10)
	 * @param	array	$conf	Configuration array
	 * @return	array	Array with two values: rating and count, which is calculated rating value and number of votes respectively
	 */
	protected function getRatingInfo($ref, array &$conf) {
		list($rating) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('SUM(value)/COUNT(value) AS rating, COUNT(value) as count', 'tx_ratings_data',
				'reference=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($ref, 'tx_ratings_data') .
				$this->cObj->enableFields('tx_ratings_data'));
		return $rating;
	}

	/**
	 * Generates rating content for given $ref using $template HTML template
	 *
	 * @param	string	$ref	Reference in TYPO3 "datagroup" format (i.e. tt_content_10)
	 * @param	string	$template	HTML template to use
	 * @param	array	$conf	Configuration array
	 * @return	string	Generated content
	 */
	protected function generateRatingContent($ref, $template, array &$conf) {
		$language = t3lib_div::makeInstance('language');
		/* @var $language language */
		$rating = $this->getRatingInfo($ref, $conf);
		$markers = array(
			'###MIN_RATING###' => $this->conf['minValue'],
			'###MAX_RATING###' => $this->conf['maxValue'],
			'###RATING###' => htmlspecialchars(sprintf($this->conf['numericFormat'], $this->getRatingValueForRef($ref, $conf))),
			'###VOTES###' => $rating['count'],
			'###VOTES_STR###' => htmlspecialchars(sprintf($language->sL('LLL:EXT:ratings/locallang.xml:api_votes_str'), $rating['count'])),
			'###TEXT_RATING###' => htmlspecialchars($this->pi_getLL('api_rating_str')),
			'###TEXT_YOUR_VOTE###' => htmlspecialchars($this->pi_getLL('api_your_vote')),
			'###PERCENT###' => $percent,
			'###PERCENT_INT###' => intval($percent),
			'###PID###' => $GLOBALS['TSFE']->id,
			'###REF###' => htmlspecialchars($ref),
		);
		$subTemplate = $this->cObj->getSubpart('###TEMPLATE_RATING###');
		$voteSub = $this->cObj->getSubpart($subTemplate, '###VOTE_SUB###');
		$options = '';
		for ($i = $conf['minValue']; $i <= $conf['maxValue']; $i++) {
			$options .= $this->cObj->substituteMarkerArray($voteSub, array(
				'###VOTE###' => $i,
				'###REF###' => $ref,
				'###PID###' => $GLOBALS['TSFE']->id,
				'###CHECK###' => md5($ref . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'])
			));
		}

		return $this->cObj->substituteMarkerArrayCached($subTemplate, $markers, array(
			'###VOTE_SUB###' => $options,
		));
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/irfaq/api/class.tx_irfaq_api.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/irfaq/class.tx_irfaq_api.php']);
}

?>
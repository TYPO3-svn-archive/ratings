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
 * <li>Call {@link getRatingValue} to obtain rating value and process it yourself</li>
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
	public function getRatingValue($ref, $conf = null) {
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
		return $this->generateRatingContent($ref, $template, $conf);
	}

	/**
	 * Retrieves current IP address
	 *
	 * @return	string	Current IP address
	 */
	protected function getCurrentIp() {
		if (preg_match('/^\d{2,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $_SERVER['HTTP_X_FORWARDED_FOR'])) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		return $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * Checks if item was already voted by current user
	 *
	 * @param	string	$ref	Reference
	 * @return	boolean	true if item was voted
	 */
	protected function isVoted($ref) {
		list($rec) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('COUNT(*) AS t',
					'tx_ratings_iplog',
					'reference=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($ref, 'tx_ratings_iplog') .
					' AND ip='. $GLOBALS['TYPO3_DB']->fullQuoteStr($this->getCurrentIp(), 'tx_ratings_iplog') .
					$this->cObj->enableFields('tx_ratings_iplog'));
		return ($rec['t'] > 0);
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
				$this->cObj->substituteMarker($subPart, '###SITE_REL_PATH###', t3lib_extMgm::siteRelPath('ratings'));
		}
	}

	/**
	 * Calculates image bar width
	 *
	 * @param	int	$rating	Rating value
	 * @param	array	$conf	Configuration
	 * @return	int
	 */
	protected function getBarWidth($rating, $conf) {
		return intval($conf['ratingImageWidth']*$conf['maxValue']*$rating/($conf['maxValue'] - $conf['minValue']));
	}

	/**
	 * Fetches rating information for $ref
	 *
	 * @param	string	$ref	Reference in TYPO3 "datagroup" format (i.e. tt_content_10)
	 * @param	array	$conf	Configuration array
	 * @return	array	Array with two values: rating and count, which is calculated rating value and number of votes respectively
	 */
	protected function getRatingInfo($ref, array &$conf) {
		$recs = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('rating,vote_count',
					'tx_ratings_data',
					'reference=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($ref, 'tx_ratings_data') . $this->cObj->enableFields('tx_ratings_data'));
		return (count($recs) ? $recs[0] : array('rating' => 0, 'vote_count' => 0));
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
		if ($rating['vote_count'] > 0) {
			$rating_value = (($rating['rating']/$rating['vote_count']) - $conf['minValue'])/($conf['maxValue'] - $conf['minValue']);
			$rating_str = sprintf($language->sL('LLL:EXT:ratings/locallang.xml:api_rating'), $rating_value, $conf['maxValue'], $rating['vote_count']);
		}
		else {
			$rating_str = $language->sL('LLL:EXT:ratings/locallang.xml:api_not_rated');
		}
		$siteRelPath = t3lib_extMgm::siteRelPath('ratings');
		$markers = array(
			'###PID###' => $GLOBALS['TSFE']->id,
			'###REF###' => htmlspecialchars($ref),
			'###TEXT_SUBMITTING###' => $language->sL('LLL:EXT:ratings/locallang.xml:api_submitting'),
			'###TEXT_ALREADY_RATED###' => $language->sL('LLL:EXT:ratings/locallang.xml:api_already_rated'),
			'###BAR_WIDTH###' => $this->getBarWidth($rating_value, $conf),
			'###RATING###' => $rating_str,
			'###TEXT_RATING_TIP###' => $language->sL('LLL:EXT:ratings/locallang.xml:api_tip'),
			'###SITE_REL_PATH###' => $siteRelPath,
		);
		if ($this->isVoted($ref)) {
			$subTemplate = $this->cObj->getSubpart($template, '###TEMPLATE_RATING_STATIC###');
			$subParts = array();
		}
		else {
			$subTemplate = $this->cObj->getSubpart($template, '###TEMPLATE_RATING###');
			$voteSub = $this->cObj->getSubpart($subTemplate, '###VOTE_SUB###');
			$options = '';
			for ($i = $conf['minValue']; $i <= $conf['maxValue']; $i++) {
				$options .= $this->cObj->substituteMarkerArray($voteSub, array(
					'###VALUE###' => $i,
					'###REF###' => $ref,
					'###PID###' => $GLOBALS['TSFE']->id,
					'###CHECK###' => md5($ref . $i . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']),
					'###SITE_REL_PATH###' => $siteRelPath,
				));
			}
			$subParts = array('###VOTE_SUB###' => $options);
		}
		return $this->cObj->substituteMarkerArrayCached($subTemplate, $markers, $subParts);
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/irfaq/api/class.tx_irfaq_api.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/irfaq/class.tx_irfaq_api.php']);
}

?>
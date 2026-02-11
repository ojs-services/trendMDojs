<?php

/**
 * @file plugins/generic/trendMDojs/TrendMDOjsSettingsForm.inc.php
 *
 * Copyright (c) 2026 OJS Services
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class TrendMDOjsSettingsForm
 * @ingroup plugins_generic_trendMDojs
 *
 * @brief Settings form for the TrendMD Widget Plugin.
 */

import('lib.pkp.classes.form.Form');

class TrendMDOjsSettingsForm extends Form {

	/** @var int */
	var $_contextId;

	/** @var TrendMDOjsPlugin */
	var $_plugin;

	/** @var array Valid position keys */
	var $_validPositions = array('article_main', 'article_detail', 'article_footer');

	/**
	 * Constructor.
	 *
	 * @param $plugin TrendMDOjsPlugin
	 * @param $contextId int
	 */
	function __construct($plugin, $contextId) {
		$this->_contextId = (int) $contextId;
		$this->_plugin = $plugin;

		parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));

		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
	}

	/**
	 * @copydoc Form::initData()
	 */
	function initData() {
		$this->_data = array(
			'trendMDIdentifier' => $this->_plugin->getSetting($this->_contextId, 'trendMDIdentifier'),
			'widgetPosition'    => $this->_plugin->getSetting($this->_contextId, 'widgetPosition') ?: 'article_main',
			'customCssClass'    => $this->_plugin->getSetting($this->_contextId, 'customCssClass'),
		);
	}

	/**
	 * @copydoc Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('trendMDIdentifier', 'widgetPosition', 'customCssClass'));
	}

	/**
	 * @copydoc Form::fetch()
	 */
	function fetch($request, $template = null, $display = false) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign(array(
			'pluginName'  => $this->_plugin->getName(),
			'trendMDJsUrl' => TRENDMD_JS_URL,
		));
		return parent::fetch($request, $template, $display);
	}

	/**
	 * @copydoc Form::execute()
	 */
	function execute(...$functionArgs) {
		$identifier = strtolower(trim($this->getData('trendMDIdentifier')));
		$this->_plugin->updateSetting($this->_contextId, 'trendMDIdentifier', $identifier, 'string');

		$position = $this->getData('widgetPosition');
		if (!in_array($position, $this->_validPositions)) {
			$position = 'article_main';
		}
		$this->_plugin->updateSetting($this->_contextId, 'widgetPosition', $position, 'string');

		$cssClass = preg_replace('/[^a-zA-Z0-9\-_ ]/', '', trim($this->getData('customCssClass')));
		$this->_plugin->updateSetting($this->_contextId, 'customCssClass', $cssClass, 'string');

		parent::execute(...$functionArgs);
	}
}

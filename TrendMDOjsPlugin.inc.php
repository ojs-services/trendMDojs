<?php

/**
 * @file plugins/generic/trendMDojs/TrendMDOjsPlugin.inc.php
 *
 * Copyright (c) 2026 OJS Services
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class TrendMDOjsPlugin
 * @ingroup plugins_generic_trendMDojs
 *
 * @brief Integrates the TrendMD article recommendation widget into OJS 3.3+.
 *
 * Each journal receives a unique identifier (UUID) from TrendMD. This plugin
 * stores that identifier per journal context, loads the TrendMD JavaScript
 * library with the correct configuration, and places the widget container
 * at a configurable position on article pages.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

define('TRENDMD_JS_URL', 'https://js.trendmd.com/trendmd.min.js');
define('TRENDMD_UUID_PATTERN', '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/');

class TrendMDOjsPlugin extends GenericPlugin {

	/**
	 * Position identifiers mapped to OJS template hooks.
	 * @var array
	 */
	private $_positionHooks = array(
		'article_main'   => 'Templates::Article::Main',
		'article_detail' => 'Templates::Article::Details',
		'article_footer' => 'Templates::Article::Footer::PageFooter',
	);

	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path, $mainContextId = null) {
		$success = parent::register($category, $path, $mainContextId);

		if ($success) {
			$this->addLocaleData();
		}

		if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) {
			return true;
		}

		if ($success && $this->getEnabled($mainContextId)) {
			$contextId = $this->_getContextId($mainContextId);
			$identifier = $this->getSetting($contextId, 'trendMDIdentifier');

			// Always bind the widget hook to show either the widget or a placeholder
			$this->_bindWidgetHook($contextId);

			// Only inject the head script when a valid identifier is configured
			if (!empty($identifier)) {
				HookRegistry::register('TemplateManager::display', array($this, 'injectHeadScript'));
			}
		}

		return $success;
	}

	/**
	 * @copydoc Plugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.generic.trendMDojs.displayName');
	}

	/**
	 * @copydoc Plugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.trendMDojs.description');
	}

	/**
	 * @copydoc Plugin::getActions()
	 */
	function getActions($request, $verb) {
		$router = $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		return array_merge(
			$this->getEnabled() ? array(
				new LinkAction(
					'settings',
					new AjaxModal(
						$router->url($request, null, null, 'manage', null, array(
							'verb' => 'settings',
							'plugin' => $this->getName(),
							'category' => 'generic',
						)),
						$this->getDisplayName()
					),
					__('manager.plugins.settings'),
					null
				),
			) : array(),
			parent::getActions($request, $verb)
		);
	}

	/**
	 * @copydoc Plugin::manage()
	 */
	function manage($args, $request) {
		switch ($request->getUserVar('verb')) {

			case 'settings':
				$context = $request->getContext();
				AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON, LOCALE_COMPONENT_PKP_MANAGER);
				$this->addLocaleData();

				$templateMgr = TemplateManager::getManager($request);
				$templateMgr->register_function('plugin_url', array($this, 'smartyPluginUrl'));

				$this->import('TrendMDOjsSettingsForm');
				$form = new TrendMDOjsSettingsForm($this, $context->getId());

				if ($request->getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						return new JSONMessage(true);
					}
				} else {
					$form->initData();
				}
				return new JSONMessage(true, $form->fetch($request));

			case 'validateIdentifier':
				$identifier = trim($request->getUserVar('trendMDIdentifier'));
				$result = $this->validateIdentifier($identifier);
				return new JSONMessage(true, json_encode($result));
		}

		return parent::manage($args, $request);
	}

	// ------------------------------------------------------------------
	//  Front-end hooks
	// ------------------------------------------------------------------

	/**
	 * Inject the TrendMD script tag into the page head.
	 *
	 * The script uses the data-trendmdconfig attribute to pass the
	 * journal's website_id and target element selector as required
	 * by TrendMD's JavaScript library.
	 *
	 * @param $hookName string
	 * @param $args array
	 * @return bool
	 */
	function injectHeadScript($hookName, $args) {
		$templateMgr = $args[0];
		$template = $args[1];

		if (strpos($template, 'rticle') === false) {
			return false;
		}

		static $injected = false;
		if ($injected) {
			return false;
		}
		$injected = true;

		$request = $this->getRequest();
		$context = $request->getContext();
		if (!$context) {
			return false;
		}

		$identifier = trim($this->getSetting((int) $context->getId(), 'trendMDIdentifier'));
		if (empty($identifier)) {
			return false;
		}

		$config = json_encode(array(
			'website_id' => $identifier,
			'element'    => '#trendmd-suggestions',
		), JSON_UNESCAPED_SLASHES);

		$templateMgr->addHeader(
			'trendMDScript',
			'<script defer src="' . TRENDMD_JS_URL . '"'
				. " data-trendmdconfig='" . htmlspecialchars($config, ENT_QUOTES, 'UTF-8') . "'"
				. '></script>'
		);

		return false;
	}

	/**
	 * Render the TrendMD widget container at the chosen position.
	 *
	 * When no identifier is configured the plugin renders a visible
	 * placeholder so administrators can verify the position is correct
	 * before entering their TrendMD UUID.
	 *
	 * @param $hookName string
	 * @param $params array
	 * @return bool
	 */
	function renderWidget($hookName, $params) {
		$smarty =& $params[1];
		$output =& $params[2];

		$request = $this->getRequest();
		$context = $request->getContext();
		if (!$context) {
			return false;
		}

		$contextId = (int) $context->getId();

		static $rendered = array();
		if (isset($rendered[$contextId])) {
			return false;
		}
		$rendered[$contextId] = true;

		$identifier = trim($this->getSetting($contextId, 'trendMDIdentifier'));
		$cssClass = preg_replace('/[^a-zA-Z0-9\-_ ]/', '', trim($this->getSetting($contextId, 'customCssClass')));
		$wrapperClass = 'trendmd_ojs_widget' . (!empty($cssClass) ? ' ' . $cssClass : '');

		$html = '<div class="' . htmlspecialchars($wrapperClass, ENT_QUOTES, 'UTF-8') . '">';

		if (!empty($identifier)) {
			$html .= '<div id="trendmd-suggestions"></div>';
		} else {
			$html .= '<div id="trendmd-suggestions" style="'
				. 'background:#fff3e0;border:1px solid #ffcc80;border-radius:4px;'
				. 'padding:12px 16px;margin:10px 0;font-size:13px;color:#e65100;'
				. '"><strong>TrendMD Widget</strong><br>'
				. __('plugins.generic.trendMDojs.placeholder.message')
				. '</div>';
		}

		$html .= '</div>';
		$output .= $html;

		return false;
	}

	// ------------------------------------------------------------------
	//  Validation
	// ------------------------------------------------------------------

	/**
	 * Validate a TrendMD journal identifier.
	 *
	 * @param $identifier string
	 * @return array Associative array with 'valid' (bool) and 'messages' (array)
	 */
	function validateIdentifier($identifier) {
		$identifier = trim($identifier);

		if (empty($identifier)) {
			return array(
				'valid'    => false,
				'messages' => array(__('plugins.generic.trendMDojs.validate.empty')),
			);
		}

		if (!preg_match(TRENDMD_UUID_PATTERN, $identifier)) {
			$messages = array(__('plugins.generic.trendMDojs.validate.invalidFormat'));
			if (strpos($identifier, ' ') !== false) {
				$messages[] = __('plugins.generic.trendMDojs.validate.hasSpaces');
			}
			if (strlen($identifier) !== 36) {
				$messages[] = __('plugins.generic.trendMDojs.validate.wrongLength');
			}
			return array('valid' => false, 'messages' => $messages);
		}

		return array(
			'valid'    => true,
			'messages' => array(__('plugins.generic.trendMDojs.validate.success')),
		);
	}

	// ------------------------------------------------------------------
	//  Private helpers
	// ------------------------------------------------------------------

	/**
	 * Bind the widget rendering hook for the configured position.
	 *
	 * @param $contextId int
	 */
	private function _bindWidgetHook($contextId) {
		$position = $this->getSetting($contextId, 'widgetPosition');
		if (empty($position) || !isset($this->_positionHooks[$position])) {
			$position = 'article_main';
		}
		HookRegistry::register($this->_positionHooks[$position], array($this, 'renderWidget'));
	}

	/**
	 * Resolve the context ID from a parameter or the current request.
	 *
	 * @param $mainContextId int|null
	 * @return int
	 */
	private function _getContextId($mainContextId = null) {
		if ($mainContextId !== null) {
			return (int) $mainContextId;
		}
		$request = Application::get()->getRequest();
		$context = $request->getContext();
		return $context ? (int) $context->getId() : CONTEXT_SITE;
	}
}

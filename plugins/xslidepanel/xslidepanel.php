<?php
/**
 * @package     XAP.Plugin
 * @subpackage  System.xSlidePanel
 *
 * @copyright   Copyright (C) 1997 - 2013 devXive - research and development. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! Page Cache Plugin
 *
 * @package     XAP.Plugin
 * @subpackage  System.xSlidePanel
 */
class PlgSystemxSlidePanel extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param   object	$subject The object to observe
	 * @param   array  $config  An array that holds the plugin configuration
	 * @since   1.0
	 */
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);

		// Load plugin parameters
		$this->_plugin = JPluginHelper::getPlugin('system', 'xslidepanel');
		$this->_params = new JRegistry($this->_plugin->params);

		// Load plugin language
		$this->loadLanguage('plg_system_xslidepanel', JPATH_ADMINISTRATOR);

	}

	function onBeforeRender()
	{
		// Required objects
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();

		// Hide from admin
		if (!$app->isAdmin())
		{
			// xslidepanel CSS - loaded in header
			$doc->addStyleSheet('/plugins/system/xslidepanel/assets/css/xslidepanel.css');

			// jpanelmenu JS - loaded before body ending
			$doc->addScript('/plugins/system/xslidepanel/assets/js/jquery.jpanelmenu.js');

			// xslidepanel JS - loaded before body ending
			$doc->addScript('/plugins/system/xslidepanel/assets/js/xslidepanel.js');
		}
	}

}

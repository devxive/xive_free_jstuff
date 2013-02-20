<?php
/**
 * ExtendsSolvedButton
 * www.devxive.com
 *
 * @package    plg_kunena_yagsolved
 * @copyright  (C) 1997-2013 devXive - research and development.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 */
defined('_JEXEC') or die;

jimport('joomla.form.formfield');
jimport('joomla.filesystem.file');

class JFormFieldTopicIcons extends JFormField
{
	protected $type = 'TopicIcons';
	
	protected function getInput()
	{
		$plugin = JPluginHelper::getPlugin('kunena', 'oneclicksolved');
		if(!$plugin) {
			$paramIconId = '0';
			echo '<font style="color: red; font-weight: bold; font-size: 14px; line-height: 20px;">' . JText::_('PLG_KUNENA_ONECLICKSOLVED_ENABLE_PLUGIN') . '</font>';
		} else {
			$params = new JParameter($plugin->params);
			$paramIconId = $params->get('set_icon_id', '8');
		
			$file = JPATH_SITE . '/media/kunena/topicicons/default/topicicons.xml';
			$xml = JFactory::getXML($file);
			
			$fieldList = '<div>';
			$fieldList .= '<select id="' . $this->id . '" name="' . $this->name . '">';
			
			foreach($xml->icons->icon as $icon) {
				if($paramIconId == $icon->attributes()->id) {
					$img_src = $icon->attributes()->src;
					$icon_default = 'selected';
				} else {
					$icon_default = '';
				}
				$fieldList .= '<option value="' . $icon->attributes()->id . '" ' . $icon_default . '>' . JText::_($icon->attributes()->title) . ' (' . $icon->attributes()->src . ')</option>';
			}
			
			$fieldList .= '</select>';
			$fieldList .= '<img src="/media/kunena/topicicons/default/' . $img_src . '" style="zoom: 66%;">';
			$fieldList .= '</div>';
			
			return($fieldList);
		}
	}
}
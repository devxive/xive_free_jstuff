<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.xivewysihtml5
 *
 * @copyright   Copyright (C) 1997 - 2013 devXive - research and development. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Plain Textarea Editor Plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Editors.xivewysihtml5
 * @since       3.0
 */
class PlgEditorXiveWysihtml5 extends JPlugin
{
	/**
	 * Method to handle the onInitEditor event.
	 *  - Initialises the Editor
	 *
	 * @return  string	JavaScript Initialization string
	 * @since 3.0
	 */
	public function onInit()
	{
	}

	/**
	 * Copy editor content to form field.
	 *
	 * Not applicable in this editor.
	 *
	 * @return  void
	 */
	public function onSave()
	{
		return;
	}

	/**
	 * Get the editor content.
	 *
	 * @param   string	$id		The id of the editor field.
	 *
	 * @return  string
	 */
	public function onGetContent($id)
	{
		return "document.getElementById('$id').value;\n";
	}

	/**
	 * Set the editor content.
	 *
	 * @param   string	$id		The id of the editor field.
	 * @param   string	$html	The content to set.
	 *
	 * @return  string
	 */
	public function onSetContent($id, $html)
	{
		return "document.getElementById('$id').value = $html;\n";
	}

	/**
	 * @param   string	$id
	 *
	 * @return  string
	 */
	public function onGetInsertMethod($id)
	{
		static $done = false;

		// Do this only once.
		if (!$done)
		{
			$doc = JFactory::getDocument();
			$js = "\tfunction jInsertEditorText(text, editor)
			{
/**
				insertAtCursor(document.getElementById(editor), text); 
**/
				var wysihtml5Editor = \$n('#$id').data(\"wysihtml5\").editor;
			}";
			$doc->addScriptDeclaration($js);
		}

		return true;
	}

	/**
	 * Display the editor area.
	 *
	 * @param   string	$name		The control name.
	 * @param   string	$html		The contents of the text area.
	 * @param   string	$width		The width of the text area (px or %).
	 * @param   string	$height		The height of the text area (px or %).
	 * @param   integer  $col		The number of columns for the textarea.
	 * @param   integer  $row		The number of rows for the textarea.
	 * @param   boolean	$buttons	True and the editor buttons will be displayed.
	 * @param   string	$id			An optional ID for the textarea (note: since 1.6). If not supplied the name is used.
	 * @param   string	$asset
	 * @param   object	$author
	 * @param   array  $params		Associative array of editor parameters.
	 *
	 * @return  string
	 */
	public function onDisplay($name, $content, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null, $params = array())
	{
		if (empty($id))
		{
			$id = $name;
		}

		// Only add "px" to width and height if they are not given as a percentage
		if (is_numeric($width))
		{
			$width .= 'px';
		}

		if (is_numeric($height))
		{
			$height .= 'px';
		}

		$buttons = $this->_displayButtons($id, $buttons, $asset, $author);

		$editor  = '';
		$editor  .= '<link rel="stylesheet" type="text/css" href="/plugins/editors/wysihtml5/bootstrap-wysihtml5.css"></link>';
		$editor  .= '<link rel="stylesheet" type="text/css" href="//netdna.bootstrapcdn.com/font-awesome/3.1.1/css/font-awesome.css"></link>';

		$editor  .= '<div class="hero-unit">' . $buttons;
		$editor  .= '<textarea name="' . $name . '" id="' . $id . '" class="textarea" cols="' . $col . '" rows="' . $row . '" style="width: ' . $width . '; height: ' . $height . ';" placeholder="Enter text ...">' . $content . '</textarea>';
		$editor  .= '</div>';

//		$editor  .= '<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>';
//		$editor  .= '<script src="http://raw.github.com/twitter/bootstrap/master/docs/assets/js/bootstrap.js"></script>';
		$editor  .= '<script src="/plugins/editors/wysihtml5/wysihtml5-0.3.0.js"></script>';
		$editor  .= '<script src="/plugins/editors/wysihtml5/bootstrap-wysihtml5.js"></script>';

		$editor  .= '<script>';
		$editor  .= 'var $n = jQuery.noConflict();';
		$editor  .= '$n(\'.textarea\').wysihtml5({
							"image": true, 
							"link": false, 
							"speech": true
		});';
		$editor  .= '</script>';

		return $editor;
	}

	public function _displayButtons($name, $buttons, $asset, $author)
	{
		// Load modal popup behavior
		JHtml::_('behavior.modal', 'a.modal-button');

		$args['name'] = $name;
		$args['event'] = 'onGetInsertMethod';

		$return = '';
		$results[] = $this->update($args);

		foreach ($results as $result)
		{
			if (is_string($result) && trim($result))
			{
				$return .= $result;
			}
		}

		if (is_array($buttons) || (is_bool($buttons) && $buttons))
		{
			$results = $this->_subject->getButtons($name, $buttons, $asset, $author);

			// This will allow plugins to attach buttons or change the behavior on the fly using AJAX
			$return .= "\n<div id=\"editor-xtd-buttons\" class=\"btn-toolbar pull-left\">\n";
			$return .= "\n<div class=\"btn-toolbar\">\n";

			foreach ($results as $button)
			{
				// Results should be an object
				if ($button->get('name'))
				{
					$modal		= ($button->get('modal')) ? 'class="modal-button btn"' : null;
					$href		= ($button->get('link')) ? 'class="btn" href="'.JURI::base().$button->get('link').'"' : null;
					$onclick	= ($button->get('onclick')) ? 'onclick="'.$button->get('onclick').'"' : null;
					$title      = ($button->get('title')) ? $button->get('title') : $button->get('text');
					$return .= "<a ".$modal." title=\"".$title."\" ".$href." ".$onclick." rel=\"".$button->get('options')."\"><i class=\"icon-".$button->get('name')."\"></i> ".$button->get('text')."</a>\n";
				}
			}

			$return .= "</div>\n";
			$return .= "</div>\n";
			$return .= "<div class=\"clearfix\"></div>\n";
		}

		return $return;
	}
}

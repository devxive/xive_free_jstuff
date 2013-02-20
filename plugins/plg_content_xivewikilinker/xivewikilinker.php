<?php
/**
  * @info			$Id$ - $Revision$
  * @package		$XApp: XInstaller $
  * @subpackage		plgXiveWikiLinker
  * @check			$Date$ || $Result: devXive AntiMal...OK, no malware found $
  * @author			$Author$ @ devXive - research and development
  * @copyright		Copyright (C) 1997 - 2013 devXive - research and development (http://www.devxive.com)
  * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  * @assetsLicense	devXive Proprietary Use License (http://www.devxive.com/license)
  */

defined('_JEXEC') or die ('Restricted access');

jimport('joomla.plugin.plugin');

class plgContentXiveWikiLinker extends JPlugin
{
	function onContentPrepare( $context, &$row, &$params, $page=0 )
	{
	// should we process further?
	if (JString::strpos($row->text, '[[') === false)
		{return true;}

	$enabled		= $this->params->get('state');
	$lang			= $this->params->get('lang');
	$target			= $this->params->get('ltarget');
	$color			= $this->params->get('color');
	$custom_site	= $this->params->get('custom_site');
	$custom_sitename= $this->params->get('custom_sitename');
	$wikipath		= $this->params->get('wikipath');

	$wikisite	= "wikipedia";
	$target		= ($target=="new") ? 'target="_blank"' : '';
	$websitename = ($custom_sitename=="") ? $wikisite : $custom_sitename;
	$wikipath = ($wikipath == 'direct') ? '' : 'Special:Search/';

	if ($lang == "se") // se is Sweden according to ISO, but not according to Wikipedia!
		{$lang = "sv";}
	if ($lang == "du") // du is Dutch according to ISO, but not according to Wikipedia!
		{$lang = "nl";}

	$regex = "#\[\[(.*?)\]\]#s";

	preg_match_all( $regex, $row->text, $matches );
	for($x=0; $x<count($matches[0]); $x++)
		{
		$match=$matches[1][$x];

		$temp1 = explode('|', $match);
		$case = count($temp1);
		if ($case==1)
			{
			$name=$page=$temp1[0];
			}
		else
			{
			$page=$temp1[0];
			$name=$temp1[1];
			}
		$readypage = str_replace(' ', '_', $page);

		$name = ($color=="") ? $name : "<font color='$color'>$name</font>";
		$website = ($custom_site == "") ? "http://".$lang.".".$wikisite.".org/wiki" : $custom_site;

		$link='<a href="'.$website.'/'.$wikipath.$readypage.'" title="'.$websitename.': '.$page.'" '.$target.'>'.$name.'</a>';
		$output = ($enabled) ? $link : $name;

		$row->text = str_replace($matches[0][$x], $output, $row->text);
		}
	}
}
?>

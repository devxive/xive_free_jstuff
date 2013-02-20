<?php
/** 
 * @Enterprise: Yagendoo Media GmbH
 * @author: Yagendoo Team
 * @url: http://www.yagendoo.com
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

class plgKunenaOneClickSolved extends JPlugin
{
	public function __construct(&$subject, $config)
	{
		// Do not load if Kunena version is not supported or Kunena is offline
		if(!(class_exists('KunenaForum') && KunenaForum::isCompatible('2.0') && KunenaForum::installed()))
		{
			return;
		}
		
		parent::__construct ($subject, $config);		

		// load language file:		
		$this->loadLanguage('plg_kunena_oneclicksolved.sys', JPATH_ADMINISTRATOR);
	}
	
	public function onAfterRoute()
	{		
		$JInput = JFactory::getApplication()->input;
		
		// solved-link clicked?
		$app = $JInput->getString('option', null);		
		$task = $JInput->getString('ocstask', null);
		if($app !== 'com_kunena' || $task !== 'solved')
		{
			return;
		}
		
		// topic and category-id given?
		$topicId = $JInput->getInt('id', 0);
		$catId = $JInput->getInt('catid', 0);
		if(empty($topicId) || empty($catId))
		{
			return;
		}
		
		// user authorized to perform solved action?
		$User = JFactory::getUser();		
		if($this->_userIsAuthorized() === false)
		{
			return;
		}
		
		// topic-id valid?
		$Dbo = JFactory::getDBO();
		$Dbo->setQuery('SELECT kt.subject, kt.id
			FROM #__kunena_topics kt
			WHERE kt.id = ' . $topicId);
		$Dbo->query();		
		$topicData = $Dbo->loadAssoc();		
		if(empty($topicData) || (int)$topicData['id'] === 0)
		{
			return;
		}		

		// topic already marked as solved?
		$topicSolvedText = $this->params->get('topic_solved_text', '[SOLVED]');
		$topicSolvedReplyText = $this->params->get('topic_solved_reply_text', 'Problem has been solved!');
		$doSolvedReply = (int)$this->params->get('do_solved_reply', 1);		
		$setIcon_id = (int)$this->params->get('set_icon_id', '8');		
		if(stripos($topicData['subject'], $topicSolvedText) !== false)
		{
			return;
		}
		
		// add '[SOLVED]' to topic:
		$Dbo->setQuery("UPDATE #__kunena_topics SET subject = " . $Dbo->quote($topicSolvedText . ' ' . $topicData['subject'], true) . " WHERE id = " . (int)$topicData['id'] . " LIMIT 1");
		$Dbo->query();

		// post a "solved" message:	
		if($doSolvedReply === 1)
		{
			$Dbo->setQuery("INSERT INTO #__kunena_messages (parent, thread, catid, name, userid, email, subject, time, ip, topic_emoticon)
				VALUES(0, " . (int)$topicData['id'] . ", " . $catId . ", ".$Dbo->quote($User->username, true).", " . (int)$User->id . ", " . $Dbo->quote($User->email, true) . ", ".$Dbo->quote($topicSolvedText . ' ' . $topicData['subject'], true) . ", " . JFactory::getDate('now')->toUnix() . ", " . $Dbo->quote($_SERVER['REMOTE_ADDR'], true) . ", 0)");
			$Dbo->query();
			$messageId = $Dbo->insertid();

			$Dbo->setQuery("INSERT INTO #__kunena_messages_text (mesid, message) VALUES (".(int)$messageId.", ". $Dbo->quote($topicSolvedReplyText, true).")");
			$Dbo->query();

			if(!empty($messageId))
			{
				$Dbo->setQuery("UPDATE #__kunena_topics SET
					icon_id = " . $setIcon_id . ",
					last_post_id = " . $messageId . ",
					last_post_time = '" . JFactory::getDate('now')->toUnix() . "',
					last_post_userid = " . (int)$User->id . ",
					last_post_message = " . $Dbo->quote($topicSolvedReplyText, true).",
					last_post_guest_name = " . $Dbo->quote($User->username, true) . "
					WHERE id = " . (int)$topicData['id'] . " LIMIT 1");
				$Dbo->query();
			}
		}
	}
	
	public function onAfterRender()
	{		
		$JInput = JFactory::getApplication()->input;
		
		// kunena topic view loaded?
		$app = $JInput->getString('option', null);
		$view = $JInput->getString('view', null);		
		if(empty($app) || empty($view))
		{
			return;
		}		
		if($app !== 'com_kunena' && $view !== 'topic')
		{
			return;
		}		
		
		// category-id and topic-id given?	    
		$catId = $JInput->getInt('catid', 0);
		$topicId = $JInput->getInt('id', 0);	    
	    if(empty($catId) || empty($topicId))
	    {
	    	return;
	    }
		
		// html view?
		$document = JFactory::getDocument();
	    $doctype = $document->getType();
	    if($doctype !== 'html')
	    {
	    	return;	        
	    }
		
		// user authorized to see/use solved button?
	    if($this->_userIsAuthorized() === false)
	    {
	    	return;
	    }

		// insert button-code into html:
		$buttonCode = '
			<div class="yagSolved" style="float:left; margin: 4px 5px 0px 0px;">
				<a class="kicon-button kbuttonuser btn-left" href="{$solved_link}">
					<span class="layout-flat">
						<span>{$solved_text}</span>
					</span>
				</a>
			</div>
		';
		if(empty($buttonCode))
		{
			return;
		}
		$buttonCode = str_replace('{$solved_text}', JText::_('PLG_KUNENA_ONECLICKSOLVED_BUTTONTEXT'), $buttonCode);
		$buttonCode = str_replace('{$solved_link}', KunenaRoute::_('index.php?option=com_kunena&view=topic&ocstask=solved&catid='.$catId.'&id='.$topicId), $buttonCode);		
	    $body = JResponse::getBody();
	    preg_match_all('#<div class="kmessage-buttons-cover">\s+<div class="kmessage-buttons-row">.*</div>\s+</div>#Us', $body, $matches);	    
	    if(empty($matches[0]))
	    {
	    	return;
	    }
	    foreach($matches[0] as $originalText)
	    {
	    	$body = str_replace($originalText, $originalText . $buttonCode, $body);
	    }
	    JResponse::setBody($body);
	}
	
	private function _userIsAuthorized()
	{	
		$Dbo = JFactory::getDBO();
		$User = JFactory::getUser();		
		$JInput = JFactory::getApplication()->input;
		
		$userId = (int)$User->id;		
		$topicId = $JInput->getInt('id', 0);
		$catId = $JInput->getInt('catid', 0);
		
		$isKunenaAdmin = KunenaAccess::getInstance()->isAdmin($User, $catId);
		$isKunenaModerator = KunenaAccess::getInstance()->isModerator($User, $catId);
		
		if(empty($userId) || empty($topicId) || empty($catId))
		{
			return false;
		}
		
		$enableForAdmins = (int)$this->params->get('enable_for_admin', 0);
		$enableForModerators = (int)$this->params->get('enable_for_moderator', 0);
		$enableForTopicStarter = (int)$this->params->get('enable_for_topic_starter', 0);
		
		// authorize user if kunena-administrator:
		if($enableForAdmins === 1)
		{
			if($isKunenaAdmin === true)
			{
				return true;				
			}
		}
		
		// authorize user if kunena-moderator:
		if($enableForModerators === 1)
		{				
			if($isKunenaModerator === true)
			{
				return true;
			}			
		}
		
		// authorize user if topic-starter:
		if($enableForTopicStarter === 1)
		{
			$Dbo->setQuery("SELECT kt.first_post_userid
				FROM #__kunena_topics kt
				WHERE kt.id = " . $topicId);
			$Dbo->query();		
			$authorId = (int)$Dbo->loadResult();		
			if($authorId === $userId)
			{
				return true;
			}
		}
		
		return false;
	}
}
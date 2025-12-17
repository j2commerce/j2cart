<?php
/**
 *  @package     FrameworkOnFramework
 *  @subpackage  include
 *  @copyright   Copyright (C) 2010-2015 Nicholas K. Dionysopoulos
 *  @license     GNU General Public License version 2, or later
 *
 *  Initializes F0F
 */

defined('_JEXEC') or die();

if (!defined('F0F_INCLUDED'))
{
    define('F0F_INCLUDED', 'revAC17962');

	// Register the F0F autoloader
    require_once __DIR__ . '/autoloader/fof.php';
	F0FAutoloaderFof::init();

	// Register a debug log
	if (defined('JDEBUG') && JDEBUG)
	{
		// F0FPlatform::getInstance() may return null if no platform integration
		// is enabled (for example when this file is included before Joomla's
		// framework is fully loaded). Guard against that to avoid fatal errors.
		$fofPlatform = F0FPlatform::getInstance();
		if (is_object($fofPlatform)) {
			$fofPlatform->logAddLogger('fof.log.php');
		}
	}
}

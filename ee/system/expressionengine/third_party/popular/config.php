<?php

/**
* Popular configuration
*
* @package        popular
* @author         Notebleu <itsnicehere@notebleu.com>
* @link           https://notebleu.com/expressionengine/popular
* @copyright      Copyright 2014, Notebleu Design, Inc
*
*/

if ( ! defined('POPULAR_NAME'))
{
	define('POPULAR_NAME',        'Popular');
	define('POPULAR_PACKAGE',     'popular');
	define('POPULAR_VERSION',     '1.1');
	define('POPULAR_DOCS',        'https://notebleu.com/software/popular');
	define('POPULAR_DEBUG',       FALSE);
    define('POPULAR_DESCRIPTION', 'An Add-On for advanced view counting');
}

# for EE < 2.6.0
if ( ! function_exists('ee'))
{
	function ee()
	{
		static $EE;
		if ( ! $EE) $EE = get_instance();
		return $EE;
	}
}

/**
 * NSM Addon Updater
 */
$config['name']    = POPULAR_NAME;
$config['version'] = POPULAR_VERSION;
$config['nsm_addon_updater']['versions_xml'] = POPULAR_DOCS . '/xml';

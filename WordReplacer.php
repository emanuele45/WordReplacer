<?php

/**
 * Word Replacer
 * From time to time is better to separate word censoring and word replacement
 * Replaces text only in the bodies
 *
 * @package WRR
 * @author emanuele
 * @copyright 2014 emanuele
 * @license   BSD http://opensource.org/licenses/BSD-3-Clause
 *
 * Part of the code is:
 * @copyright 2011 Simple Machines
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 */

if (!defined('ELK'))
	die('No access...');

function WordReplacerLoad(&$admin_areas)
{
	global $txt;

	loadLanguage('Wordreplacer');
	$admin_areas['config']['areas']['addonsettings']['subsections']['wordreplacer'] = array($txt['wordreplacer']);
}

function WordReplacerSubAct(&$subActions)
{
	$subActions['wordreplacer'] = array('function' => 'WordReplacerSet', 'permission' => 'admin_forum');
}

function WordReplacer_pre_parse(&$message, &$smileys, &$cache_id, &$parse_tags)
{
	$message = wordReplacer($message);
}

// Set the censored words.
function WordReplacerSet()
{
	global $txt, $modSettings, $context, $smcFunc;

	if (!isset($modSettings['wordreplacer_vulgar']))
		$modSettings['wordreplacer_vulgar'] = '';
	if (!isset($modSettings['wordreplacer_proper']))
		$modSettings['wordreplacer_proper'] = '';

	if (!empty($_POST['save_censor']))
	{
		// Make sure censoring is something they can do.
		checkSession();

		$censored_vulgar = array();
		$censored_proper = array();

		// Rip it apart, then split it into two arrays.
		if (isset($_POST['censortext']))
		{
			$_POST['censortext'] = explode("\n", strtr($_POST['censortext'], array("\r" => '')));

			foreach ($_POST['censortext'] as $c)
				list ($censored_vulgar[], $censored_proper[]) = array_pad(explode('=', trim($c)), 2, '');
		}
		elseif (isset($_POST['censor_vulgar'], $_POST['censor_proper']))
		{
			if (is_array($_POST['censor_vulgar']))
			{
				foreach ($_POST['censor_vulgar'] as $i => $value)
				{
					if (trim(strtr($value, '*', ' ')) == '')
						unset($_POST['censor_vulgar'][$i], $_POST['censor_proper'][$i]);
				}

				$censored_vulgar = $_POST['censor_vulgar'];
				$censored_proper = $_POST['censor_proper'];
			}
			else
			{
				$censored_vulgar = explode("\n", strtr($_POST['censor_vulgar'], array("\r" => '')));
				$censored_proper = explode("\n", strtr($_POST['censor_proper'], array("\r" => '')));
			}
		}

		// Set the new arrays and settings in the database.
		$updates = array(
			'wordreplacer_vulgar' => implode("\n", $censored_vulgar),
			'wordreplacer_proper' => implode("\n", $censored_proper),
			'wordreplacerWholeWord' => empty($_POST['censorWholeWord']) ? '0' : '1',
			'wordreplacerIgnoreCase' => empty($_POST['censorIgnoreCase']) ? '0' : '1',
		);

		updateSettings($updates);
	}

	if (isset($_POST['censortest']))
	{
		$censorText = htmlspecialchars($_POST['censortest'], ENT_QUOTES);
		$context['censor_test'] = strtr(wordReplacer($censorText), array('"' => '&quot;'));
	}

	// Set everything up for the template to do its thang.
	$censor_vulgar = explode("\n", $modSettings['wordreplacer_vulgar']);
	$censor_proper = explode("\n", $modSettings['wordreplacer_proper']);

	$context['censored_words'] = array();
	for ($i = 0, $n = count($censor_vulgar); $i < $n; $i++)
	{
		if (empty($censor_vulgar[$i]))
			continue;

		// Skip it, it's either spaces or stars only.
		if (trim(strtr($censor_vulgar[$i], '*', ' ')) == '')
			continue;

		$context['censored_words'][htmlspecialchars(trim($censor_vulgar[$i]))] = isset($censor_proper[$i]) ? htmlspecialchars($censor_proper[$i]) : '';
	}

	loadTemplate('WordReplacer');
	$context['sub_template'] = 'edit_wordreplacer';
	$context['page_title'] = $txt['admin_censored_words'];
}

function wordReplacer($text)
{
	global $modSettings, $options, $settings, $txt;
	static $censor_vulgar = null, $censor_proper;

	// If they haven't yet been loaded, load them.
	if ($censor_vulgar == null)
	{
		if (!isset($modSettings['wordreplacer_vulgar']) || !isset($modSettings['wordreplacer_proper']))
			return $text;
		$censor_vulgar = explode("\n", $modSettings['wordreplacer_vulgar']);
		$censor_proper = explode("\n", $modSettings['wordreplacer_proper']);

		// Quote them for use in regular expressions.
		for ($i = 0, $n = count($censor_vulgar); $i < $n; $i++)
		{
			$censor_vulgar[$i] = strtr(preg_quote($censor_vulgar[$i], '/'), array('\\\\\\*' => '[*]', '\\*' => '[^\s]*?', '&' => '&amp;'));
			$censor_vulgar[$i] = (empty($modSettings['wordreplacerWholeWord']) ? '/' . $censor_vulgar[$i] . '/' : '/(?<=^|\W)' . $censor_vulgar[$i] . '(?=$|\W)/') . (empty($modSettings['wordreplacerIgnoreCase']) ? '' : 'i') . ((empty($modSettings['global_character_set']) ? $txt['lang_character_set'] : $modSettings['global_character_set']) === 'UTF-8' ? 'u' : '');

			if (strpos($censor_vulgar[$i], '\'') !== false)
			{
				$censor_proper[count($censor_vulgar)] = $censor_proper[$i];
				$censor_vulgar[count($censor_vulgar)] = strtr($censor_vulgar[$i], array('\'' => '&#039;'));
			}
		}
	}

	// Censoring isn't so very complicated :P.
	$text = preg_replace($censor_vulgar, $censor_proper, $text);
	return $text;
}
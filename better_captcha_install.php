<?php
/**
 * Better CAPTCHA installer / non-destructive uninstaller.
 *
 * @package Better CAPTCHA
 * @version 0.1.0
 */

if (!defined('SMF'))
	die('No direct access...');

global $context, $sourcedir, $modSettings;

require_once($sourcedir . '/Subs.php');

$hooks = array(
	'integrate_actions' => 'BetterCaptcha_Actions',
	'integrate_admin_areas' => 'BetterCaptcha_AdminAreas',
	'integrate_load_theme' => 'BetterCaptcha_LoadTheme',
	'integrate_create_control_verification_pre' => 'BetterCaptcha_VerificationPre',
	'integrate_create_control_verification_test' => 'BetterCaptcha_VerificationTest',
	'integrate_create_control_verification_post' => 'BetterCaptcha_VerificationPost',
	'integrate_buffer' => 'BetterCaptcha_Buffer',
);
$hook_file = '$sourcedir/BetterCaptcha.php';

if (!empty($context['uninstalling']))
{
	foreach ($hooks as $hook => $function)
	{
		remove_integration_function($hook, $function, true, $hook_file);
		remove_integration_function($hook, $function, true);
	}
	return;
}

foreach ($hooks as $hook => $function)
{
	// dev4d: aggressively normalize both historical unqualified registrations and
	// canonical file-qualified registrations before adding the one canonical entry.
	// This repairs installs where only part of the verification lifecycle was stored.
	remove_integration_function($hook, $function, true, $hook_file);
	remove_integration_function($hook, $function, true);
	add_integration_function($hook, $function, true, $hook_file);
}

// dev4e: shorten the built-in preset lockouts without overriding a real Custom
// configuration. Upgrade only values that exactly match the previous preset default.
$current_preset = isset($modSettings['better_captcha_preset']) ? (string) $modSettings['better_captcha_preset'] : 'balanced';
$current_lockout = isset($modSettings['better_captcha_lockout']) ? (int) $modSettings['better_captcha_lockout'] : null;
if ($current_preset === 'balanced' && $current_lockout === 300)
	updateSettings(array('better_captcha_lockout' => 90));
elseif ($current_preset === 'privacy' && $current_lockout === 180)
	updateSettings(array('better_captcha_lockout' => 120));
elseif ($current_preset === 'high' && $current_lockout === 600)
	updateSettings(array('better_captcha_lockout' => 300));

$defaults = array(
	'better_captcha_enabled' => 1,
	'better_captcha_preset' => 'balanced',
	'better_captcha_visual_level' => 3,
	'better_captcha_min_seconds' => 2,
	'better_captcha_lifetime' => 600,
	'better_captcha_max_failures' => 5,
	'better_captcha_lockout' => 90,
	'better_captcha_adaptive_enabled' => 1,
	'better_captcha_adaptive_step1' => 2,
	'better_captcha_adaptive_step2' => 4,
	'better_captcha_adaptive_time_boost' => 1,
	'better_captcha_adaptive_max_visual' => 5,
	'better_captcha_protect_registration' => 1,
	'better_captcha_protect_other' => 1,
	'better_captcha_show_level_notice' => 1,
	'better_captcha_collect_stats' => 1,
	'better_captcha_puzzle_enabled' => 1,
	'better_captcha_stat_attempts' => 0,
	'better_captcha_stat_passes' => 0,
	'better_captcha_stat_failures' => 0,
	'better_captcha_stat_escalations' => 0,
	'better_captcha_stat_lockouts' => 0,
	'better_captcha_stat_too_fast' => 0,
	'better_captcha_stat_expired' => 0,
	'better_captcha_stat_session_errors' => 0,
);

foreach ($defaults as $setting => $value)
	if (!isset($modSettings[$setting]))
		updateSettings(array($setting => $value));

<?php
/**
 * Better CAPTCHA - self-hosted adaptive anti-bot protection for SMF 2.1.x.
 *
 * dev4a adds the first local drag-and-drop jigsaw challenge prototype while retaining
 * the validated adaptive engine, privacy-safe counters, visual/audio fallback and
 * polished administrator controls. No external CAPTCHA service is contacted and
 * no visitor IP address is persisted by Better CAPTCHA.
 *
 * @package Better CAPTCHA
 * @version 0.1.0
 */

if (!defined('SMF'))
	die('No direct access...');

function BetterCaptcha_LoadLanguage()
{
	global $txt;

	if (!isset($txt['better_captcha_admin_title']))
		loadLanguage('BetterCaptcha');
}

function BetterCaptcha_Defaults($preset)
{
	$presets = array(
		'balanced' => array(
			'visual_level' => 3,
			'min_seconds' => 2,
			'lifetime' => 600,
			'max_failures' => 5,
			'lockout' => 90,
			'adaptive_enabled' => 1,
			'adaptive_step1' => 2,
			'adaptive_step2' => 4,
			'adaptive_time_boost' => 1,
			'adaptive_max_visual' => 5,
			'protect_registration' => 1,
			'protect_other' => 1,
			'show_level_notice' => 1,
			'collect_stats' => 1,
		),
		'privacy' => array(
			'visual_level' => 2,
			'min_seconds' => 2,
			'lifetime' => 600,
			'max_failures' => 6,
			'lockout' => 120,
			'adaptive_enabled' => 1,
			'adaptive_step1' => 3,
			'adaptive_step2' => 5,
			'adaptive_time_boost' => 1,
			'adaptive_max_visual' => 4,
			'protect_registration' => 1,
			'protect_other' => 1,
			'show_level_notice' => 1,
			'collect_stats' => 1,
		),
		'high' => array(
			'visual_level' => 4,
			'min_seconds' => 3,
			'lifetime' => 300,
			'max_failures' => 3,
			'lockout' => 300,
			'adaptive_enabled' => 1,
			'adaptive_step1' => 1,
			'adaptive_step2' => 2,
			'adaptive_time_boost' => 2,
			'adaptive_max_visual' => 5,
			'protect_registration' => 1,
			'protect_other' => 1,
			'show_level_notice' => 1,
			'collect_stats' => 1,
		),
	);

	return isset($presets[$preset]) ? $presets[$preset] : $presets['balanced'];
}

function BetterCaptcha_Settings()
{
	global $modSettings;

	$preset = isset($modSettings['better_captcha_preset']) ? (string) $modSettings['better_captcha_preset'] : 'balanced';
	$defaults = BetterCaptcha_Defaults($preset === 'custom' ? 'balanced' : $preset);

	return array(
		'enabled' => !empty($modSettings['better_captcha_enabled']),
		'preset' => $preset,
		'visual_level' => isset($modSettings['better_captcha_visual_level']) ? max(1, min(5, (int) $modSettings['better_captcha_visual_level'])) : $defaults['visual_level'],
		'min_seconds' => isset($modSettings['better_captcha_min_seconds']) ? max(0, min(30, (int) $modSettings['better_captcha_min_seconds'])) : $defaults['min_seconds'],
		'lifetime' => isset($modSettings['better_captcha_lifetime']) ? max(60, min(3600, (int) $modSettings['better_captcha_lifetime'])) : $defaults['lifetime'],
		'max_failures' => isset($modSettings['better_captcha_max_failures']) ? max(1, min(20, (int) $modSettings['better_captcha_max_failures'])) : $defaults['max_failures'],
		'lockout' => isset($modSettings['better_captcha_lockout']) ? max(30, min(3600, (int) $modSettings['better_captcha_lockout'])) : $defaults['lockout'],
		'adaptive_enabled' => isset($modSettings['better_captcha_adaptive_enabled']) ? !empty($modSettings['better_captcha_adaptive_enabled']) : !empty($defaults['adaptive_enabled']),
		'adaptive_step1' => isset($modSettings['better_captcha_adaptive_step1']) ? max(1, min(20, (int) $modSettings['better_captcha_adaptive_step1'])) : $defaults['adaptive_step1'],
		'adaptive_step2' => isset($modSettings['better_captcha_adaptive_step2']) ? max(2, min(20, (int) $modSettings['better_captcha_adaptive_step2'])) : $defaults['adaptive_step2'],
		'adaptive_time_boost' => isset($modSettings['better_captcha_adaptive_time_boost']) ? max(0, min(10, (int) $modSettings['better_captcha_adaptive_time_boost'])) : $defaults['adaptive_time_boost'],
		'adaptive_max_visual' => isset($modSettings['better_captcha_adaptive_max_visual']) ? max(1, min(5, (int) $modSettings['better_captcha_adaptive_max_visual'])) : $defaults['adaptive_max_visual'],
		'protect_registration' => isset($modSettings['better_captcha_protect_registration']) ? !empty($modSettings['better_captcha_protect_registration']) : !empty($defaults['protect_registration']),
		'protect_other' => isset($modSettings['better_captcha_protect_other']) ? !empty($modSettings['better_captcha_protect_other']) : !empty($defaults['protect_other']),
		'show_level_notice' => isset($modSettings['better_captcha_show_level_notice']) ? !empty($modSettings['better_captcha_show_level_notice']) : !empty($defaults['show_level_notice']),
		'collect_stats' => isset($modSettings['better_captcha_collect_stats']) ? !empty($modSettings['better_captcha_collect_stats']) : !empty($defaults['collect_stats']),
		'puzzle_enabled' => isset($modSettings['better_captcha_puzzle_enabled']) ? !empty($modSettings['better_captcha_puzzle_enabled']) : true,
	);
}

function BetterCaptcha_ContextEnabled($id, $settings)
{
	$id = (string) $id;
	if ($id === 'register')
		return !empty($settings['protect_registration']);

	return !empty($settings['protect_other']);
}

function BetterCaptcha_RateState($id)
{
	$id = (string) $id;
	if (!isset($_SESSION['better_captcha_rate']) || !is_array($_SESSION['better_captcha_rate']))
		$_SESSION['better_captcha_rate'] = array();
	if (!isset($_SESSION['better_captcha_rate'][$id]) || !is_array($_SESSION['better_captcha_rate'][$id]))
		$_SESSION['better_captcha_rate'][$id] = array('failures' => 0, 'locked_until' => 0);

	// dev3: once a temporary lockout has genuinely expired, start clean instead
	// of immediately carrying the old failure count into another lockout.
	if (!empty($_SESSION['better_captcha_rate'][$id]['locked_until']) && (int) $_SESSION['better_captcha_rate'][$id]['locked_until'] <= time())
		$_SESSION['better_captcha_rate'][$id] = array('failures' => 0, 'locked_until' => 0);

	return $_SESSION['better_captcha_rate'][$id];
}

function BetterCaptcha_AdaptiveProfile($id, $settings)
{
	$visual = (int) $settings['visual_level'];
	$min_seconds = (int) $settings['min_seconds'];
	$tier = 0;
	$rate = BetterCaptcha_RateState($id);
	$failures = isset($rate['failures']) ? max(0, (int) $rate['failures']) : 0;

	if (!empty($settings['adaptive_enabled']))
	{
		$step1 = max(1, (int) $settings['adaptive_step1']);
		$step2 = max($step1 + 1, (int) $settings['adaptive_step2']);

		if ($failures >= $step2)
			$tier = 2;
		elseif ($failures >= $step1)
			$tier = 1;

		$visual = min((int) $settings['adaptive_max_visual'], $visual + $tier);
		$min_seconds = min(30, $min_seconds + ($tier * (int) $settings['adaptive_time_boost']));
	}

	return array(
		'tier' => $tier,
		'failures' => $failures,
		'visual_level' => max(1, min(5, $visual)),
		'min_seconds' => max(0, min(30, $min_seconds)),
	);
}

function BetterCaptcha_StatKeys()
{
	return array('attempts', 'passes', 'failures', 'escalations', 'lockouts', 'too_fast', 'expired', 'session_errors');
}

function BetterCaptcha_StatIncrement($key, $amount = 1)
{
	global $modSettings;

	$settings = BetterCaptcha_Settings();
	if (empty($settings['collect_stats']) || !in_array($key, BetterCaptcha_StatKeys(), true))
		return;

	$setting = 'better_captcha_stat_' . $key;
	$current = isset($modSettings[$setting]) ? max(0, (int) $modSettings[$setting]) : 0;
	updateSettings(array($setting => $current + max(0, (int) $amount)));
}

function BetterCaptcha_Stats()
{
	global $modSettings;

	$stats = array();
	foreach (BetterCaptcha_StatKeys() as $key)
		$stats[$key] = isset($modSettings['better_captcha_stat_' . $key]) ? max(0, (int) $modSettings['better_captcha_stat_' . $key]) : 0;

	return $stats;
}

function BetterCaptcha_ResetStats()
{
	$reset = array();
	foreach (BetterCaptcha_StatKeys() as $key)
		$reset['better_captcha_stat_' . $key] = 0;
	updateSettings($reset);
}

function BetterCaptcha_ProtectionLabel($settings)
{
	BetterCaptcha_LoadLanguage();
	global $txt;

	if (empty($settings['enabled']))
		return $txt['better_captcha_level_disabled'];

	switch ($settings['preset'])
	{
		case 'privacy': return $txt['better_captcha_level_privacy'];
		case 'high': return $txt['better_captcha_level_high'];
		case 'custom': return $txt['better_captcha_level_custom'];
		default: return $txt['better_captcha_level_balanced'];
	}
}

function BetterCaptcha_Actions(&$actionArray)
{
	if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'verificationcode' && isset($_REQUEST['betterpuzzle']))
		$actionArray['verificationcode'] = array('BetterCaptcha.php', 'BetterCaptcha_PuzzleImageAction');
}

function BetterCaptcha_PuzzleIsActive($id, $settings, $profile = null)
{
	if (empty($settings['puzzle_enabled']) || !extension_loaded('gd'))
		return false;

	if ($profile === null)
		$profile = BetterCaptcha_AdaptiveProfile($id, $settings);

	// dev4a: puzzle is the tier-2 challenge. The existing visual/audio challenge
	// remains available as an explicit accessibility fallback.
	return !empty($settings['adaptive_enabled']) && (int) $profile['tier'] >= 2;
}

function BetterCaptcha_PuzzleEnsure($id, $settings)
{
	$id = (string) $id;
	if (!isset($_SESSION[$id . '_vv']) || !is_array($_SESSION[$id . '_vv']))
		$_SESSION[$id . '_vv'] = array();

	$state = &$_SESSION[$id . '_vv'];
	$token = isset($state['better_captcha_token']) ? (string) $state['better_captcha_token'] : '';
	$valid = !empty($state['better_captcha_puzzle'])
		&& is_array($state['better_captcha_puzzle'])
		&& !empty($state['better_captcha_puzzle']['nonce'])
		&& isset($state['better_captcha_puzzle']['token'])
		&& hash_equals((string) $state['better_captcha_puzzle']['token'], $token);

	if (!$valid)
	{
		try
		{
			$nonce = bin2hex(random_bytes(16));
		}
		catch (Exception $e)
		{
			$nonce = hash('sha256', uniqid((string) mt_rand(), true) . session_id());
		}

		$piece = 64;
		$width = 320;
		$height = 180;
		$state['better_captcha_puzzle'] = array(
			'nonce' => $nonce,
			'token' => $token,
			'x' => mt_rand(105, $width - $piece - 18),
			'y' => mt_rand(38, $height - $piece - 22),
			'piece' => $piece,
			'width' => $width,
			'height' => $height,
			'tolerance' => 14,
			'issued' => time(),
		);
	}

	return $state['better_captcha_puzzle'];
}

function BetterCaptcha_PuzzleMask($size)
{
	$mask = imagecreatetruecolor($size, $size);
	imagealphablending($mask, false);
	imagesavealpha($mask, true);
	$transparent = imagecolorallocatealpha($mask, 0, 0, 0, 127);
	$solid = imagecolorallocatealpha($mask, 255, 255, 255, 0);
	imagefill($mask, 0, 0, $transparent);

	$pad = 9;
	imagefilledrectangle($mask, $pad, $pad, $size - $pad - 1, $size - $pad - 1, $solid);
	// Two outward knobs and two inward sockets give the loose piece a clear jigsaw shape.
	imagefilledellipse($mask, (int) ($size / 2), $pad, 20, 20, $solid);
	imagefilledellipse($mask, $size - $pad, (int) ($size / 2), 20, 20, $solid);
	imagefilledellipse($mask, $pad, (int) ($size / 2), 18, 18, $transparent);
	imagefilledellipse($mask, (int) ($size / 2), $size - $pad, 18, 18, $transparent);

	return $mask;
}

function BetterCaptcha_PuzzleScene($width, $height)
{
	$img = imagecreatetruecolor($width, $height);
	for ($y = 0; $y < $height; $y++)
	{
		$t = $y / max(1, $height - 1);
		$r = (int) (36 + 74 * $t);
		$g = (int) (119 + 73 * $t);
		$b = (int) (190 + 45 * $t);
		$line = imagecolorallocate($img, $r, $g, $b);
		imageline($img, 0, $y, $width, $y, $line);
	}

	$sun = imagecolorallocate($img, 255, 220, 96);
	imagefilledellipse($img, 268, 38, 42, 42, $sun);
	$hill1 = imagecolorallocate($img, 51, 112, 81);
	$hill2 = imagecolorallocate($img, 39, 88, 68);
	imagefilledpolygon($img, array(0,130,70,83,145,128,205,72,320,127,320,180,0,180), 7, $hill1);
	imagefilledpolygon($img, array(0,151,75,109,155,153,235,101,320,145,320,180,0,180), 7, $hill2);
	$water = imagecolorallocate($img, 40, 135, 174);
	imagefilledrectangle($img, 0, 145, $width, $height, $water);
	$cloud = imagecolorallocatealpha($img, 245, 250, 255, 18);
	imagefilledellipse($img, 62, 40, 48, 20, $cloud);
	imagefilledellipse($img, 83, 38, 42, 24, $cloud);
	imagefilledellipse($img, 102, 43, 50, 18, $cloud);
	$tree = imagecolorallocate($img, 24, 65, 44);
	$trunk = imagecolorallocate($img, 92, 65, 42);
	for ($x = 20; $x < 315; $x += 43)
	{
		$base = 135 + (($x / 43) % 2) * 6;
		imagefilledrectangle($img, $x, $base - 17, $x + 4, $base + 7, $trunk);
		imagefilledpolygon($img, array($x-10,$base-13,$x+2,$base-42,$x+14,$base-13), 3, $tree);
	}
	return $img;
}

function BetterCaptcha_PuzzleImageAction()
{
	$settings = BetterCaptcha_Settings();
	$id = isset($_GET['id']) ? preg_replace('~[^A-Za-z0-9_-]~', '', (string) $_GET['id']) : '';
	$kind = isset($_GET['kind']) ? (string) $_GET['kind'] : 'bg';
	$nonce = isset($_GET['n']) ? (string) $_GET['n'] : '';

	if (!$settings['enabled'] || $id === '' || !BetterCaptcha_ContextEnabled($id, $settings) || !extension_loaded('gd'))
	{
		send_http_status(404);
		die();
	}

	$profile = BetterCaptcha_AdaptiveProfile($id, $settings);
	if (!BetterCaptcha_PuzzleIsActive($id, $settings, $profile))
	{
		send_http_status(404);
		die();
	}

	$puzzle = BetterCaptcha_PuzzleEnsure($id, $settings);
	if ($nonce === '' || !hash_equals((string) $puzzle['nonce'], $nonce))
	{
		send_http_status(403);
		die();
	}

	$scene = BetterCaptcha_PuzzleScene((int) $puzzle['width'], (int) $puzzle['height']);
	$mask = BetterCaptcha_PuzzleMask((int) $puzzle['piece']);
	$pieceSize = (int) $puzzle['piece'];
	$x0 = (int) $puzzle['x'];
	$y0 = (int) $puzzle['y'];

	if ($kind === 'piece')
	{
		$out = imagecreatetruecolor($pieceSize, $pieceSize);
		imagealphablending($out, false);
		imagesavealpha($out, true);
		$transparent = imagecolorallocatealpha($out, 0, 0, 0, 127);
		imagefill($out, 0, 0, $transparent);
		for ($y = 0; $y < $pieceSize; $y++)
			for ($x = 0; $x < $pieceSize; $x++)
			{
				$rgba = imagecolorsforindex($mask, imagecolorat($mask, $x, $y));
				if ($rgba['alpha'] < 64)
					imagesetpixel($out, $x, $y, imagecolorat($scene, $x0 + $x, $y0 + $y));
			}
		$border = imagecolorallocatealpha($out, 255, 255, 255, 28);
		imagerectangle($out, 7, 7, $pieceSize - 8, $pieceSize - 8, $border);
		$render = $out;
	}
	else
	{
		$hole1 = imagecolorallocate($scene, 21, 36, 47);
		$hole2 = imagecolorallocate($scene, 35, 55, 68);
		for ($y = 0; $y < $pieceSize; $y++)
			for ($x = 0; $x < $pieceSize; $x++)
			{
				$rgba = imagecolorsforindex($mask, imagecolorat($mask, $x, $y));
				if ($rgba['alpha'] < 64)
					imagesetpixel($scene, $x0 + $x, $y0 + $y, ((int) (($x + $y) / 8) % 2) ? $hole1 : $hole2);
			}
		$render = $scene;
	}

	header('Content-Type: image/png');
	header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
	header('Pragma: no-cache');
	imagepng($render);
	if (isset($out) && is_resource($out)) imagedestroy($out);
	imagedestroy($mask);
	imagedestroy($scene);
	die();
}

function BetterCaptcha_AdminAreas(&$admin_areas)
{
	global $txt;

	BetterCaptcha_LoadLanguage();

	$admin_areas['config']['areas']['bettercaptcha'] = array(
		'label' => $txt['better_captcha_admin_title'],
		'file' => 'BetterCaptcha.php',
		'function' => 'BetterCaptcha_Admin',
		'icon' => 'better_captcha.png',
		'permission' => array('admin_forum'),
	);
}

function BetterCaptcha_Admin()
{
	global $context, $txt;

	isAllowedTo('admin_forum');
	BetterCaptcha_LoadLanguage();
	loadTemplate('BetterCaptchaAdmin');

	$context['page_title'] = $txt['better_captcha_admin_title'];
	if (!empty($context['admin_menu_name']))
	{
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['better_captcha_admin_title'],
			'description' => $txt['better_captcha_admin_desc'],
		);
	}

	if (isset($_POST['resetstats']))
	{
		checkSession('post');
		BetterCaptcha_ResetStats();
		redirectexit('action=admin;area=bettercaptcha;statsreset');
	}

	if (isset($_POST['save']))
	{
		checkSession('post');

		$allowed_presets = array('balanced', 'privacy', 'high', 'custom');
		$preset = isset($_POST['better_captcha_preset']) && in_array($_POST['better_captcha_preset'], $allowed_presets, true)
			? $_POST['better_captcha_preset']
			: 'balanced';

		if ($preset === 'custom')
		{
			$step1 = max(1, min(20, (int) ($_POST['better_captcha_adaptive_step1'] ?? 2)));
			$step2 = max($step1 + 1, min(20, (int) ($_POST['better_captcha_adaptive_step2'] ?? 4)));
			$values = array(
				'visual_level' => max(1, min(5, (int) ($_POST['better_captcha_visual_level'] ?? 3))),
				'min_seconds' => max(0, min(30, (int) ($_POST['better_captcha_min_seconds'] ?? 2))),
				'lifetime' => max(60, min(3600, (int) ($_POST['better_captcha_lifetime'] ?? 600))),
				'max_failures' => max(1, min(20, (int) ($_POST['better_captcha_max_failures'] ?? 5))),
				'lockout' => max(30, min(3600, (int) ($_POST['better_captcha_lockout'] ?? 300))),
				'adaptive_enabled' => !empty($_POST['better_captcha_adaptive_enabled']) ? 1 : 0,
				'adaptive_step1' => $step1,
				'adaptive_step2' => $step2,
				'adaptive_time_boost' => max(0, min(10, (int) ($_POST['better_captcha_adaptive_time_boost'] ?? 1))),
				'adaptive_max_visual' => max(1, min(5, (int) ($_POST['better_captcha_adaptive_max_visual'] ?? 5))),
				'protect_registration' => !empty($_POST['better_captcha_protect_registration']) ? 1 : 0,
				'protect_other' => !empty($_POST['better_captcha_protect_other']) ? 1 : 0,
				'show_level_notice' => !empty($_POST['better_captcha_show_level_notice']) ? 1 : 0,
				'collect_stats' => !empty($_POST['better_captcha_collect_stats']) ? 1 : 0,
			);
		}
		else
			$values = BetterCaptcha_Defaults($preset);

		updateSettings(array(
			'better_captcha_enabled' => !empty($_POST['better_captcha_enabled']) ? 1 : 0,
			'better_captcha_preset' => $preset,
			'better_captcha_visual_level' => $values['visual_level'],
			'better_captcha_min_seconds' => $values['min_seconds'],
			'better_captcha_lifetime' => $values['lifetime'],
			'better_captcha_max_failures' => $values['max_failures'],
			'better_captcha_lockout' => $values['lockout'],
			'better_captcha_adaptive_enabled' => $values['adaptive_enabled'],
			'better_captcha_adaptive_step1' => $values['adaptive_step1'],
			'better_captcha_adaptive_step2' => $values['adaptive_step2'],
			'better_captcha_adaptive_time_boost' => $values['adaptive_time_boost'],
			'better_captcha_adaptive_max_visual' => $values['adaptive_max_visual'],
			'better_captcha_protect_registration' => $values['protect_registration'],
			'better_captcha_protect_other' => $values['protect_other'],
			'better_captcha_show_level_notice' => $values['show_level_notice'],
			'better_captcha_collect_stats' => $values['collect_stats'],
			'better_captcha_puzzle_enabled' => !empty($_POST['better_captcha_puzzle_enabled']) ? 1 : 0,
		));

		redirectexit('action=admin;area=bettercaptcha;saved');
	}

	$context['better_captcha'] = BetterCaptcha_Settings();
	$context['better_captcha_saved'] = isset($_GET['saved']);
	$context['better_captcha_stats_reset'] = isset($_GET['statsreset']);
	$context['better_captcha_stats'] = BetterCaptcha_Stats();
	$context['better_captcha_protection_label'] = BetterCaptcha_ProtectionLabel($context['better_captcha']);
	$context['better_captcha_status'] = array(
		'php' => PHP_VERSION,
		'gd' => extension_loaded('gd'),
		'session' => session_status() === PHP_SESSION_ACTIVE,
		'random_bytes' => function_exists('random_bytes'),
		'external_services' => false,
		'adaptive' => !empty($context['better_captcha']['adaptive_enabled']),
		'stats' => !empty($context['better_captcha']['collect_stats']),
		'puzzle' => !empty($context['better_captcha']['puzzle_enabled']) && extension_loaded('gd'),
	);
	$context['sub_template'] = 'better_captcha_admin';
}

/**
 * dev4d: verify that all Better CAPTCHA verification lifecycle hooks are present.
 *
 * The live dev4c trace proved that integrate_create_control_verification_test was
 * registered while the matching pre/post hooks were absent. Because LoadTheme is
 * itself a proven working hook and runs before normal verification controls are
 * built, we use it as a self-healing checkpoint. We preserve unrelated callbacks,
 * remove stale Better CAPTCHA variants, and persist one canonical file-qualified
 * callback for each lifecycle hook.
 */
function BetterCaptcha_EnsureVerificationHooks($persist = true)
{
	global $modSettings;

	$targets = array(
		'integrate_create_control_verification_pre' => 'BetterCaptcha_VerificationPre',
		'integrate_create_control_verification_test' => 'BetterCaptcha_VerificationTest',
		'integrate_create_control_verification_post' => 'BetterCaptcha_VerificationPost',
	);
	$changed = false;

	foreach ($targets as $hook => $function)
	{
		$current = !empty($modSettings[$hook]) ? explode(',', (string) $modSettings[$hook]) : array();
		$kept = array();

		foreach ($current as $entry)
		{
			$entry = trim((string) $entry);
			if ($entry === '')
				continue;

			$enabled = rtrim($entry, '!');
			$callable = strpos($enabled, '|') !== false ? substr($enabled, strrpos($enabled, '|') + 1) : $enabled;
			$callable = rtrim($callable, '#');

			if ($callable === $function)
				continue;

			$kept[] = $entry;
		}

		$canonical = '$sourcedir/BetterCaptcha.php|' . $function;
		$kept[] = $canonical;
		$kept = array_values(array_unique($kept));
		$new_value = implode(',', $kept);

		if (!isset($modSettings[$hook]) || (string) $modSettings[$hook] !== $new_value)
		{
			$modSettings[$hook] = $new_value;
			$changed = true;
			if ($persist)
				updateSettings(array($hook => $new_value));
		}
	}

	if (!isset($_SESSION['better_captcha_trace']) || !is_array($_SESSION['better_captcha_trace']))
		$_SESSION['better_captcha_trace'] = array();
	$_SESSION['better_captcha_trace']['_hook_repair'] = array(
		'checked' => time(),
		'changed' => $changed ? 1 : 0,
	);

	return $changed;
}

function BetterCaptcha_HookRegistered($hook, $function)
{
	global $modSettings;
	if (empty($modSettings[$hook]))
		return false;

	foreach (explode(',', (string) $modSettings[$hook]) as $entry)
	{
		$enabled = rtrim(trim((string) $entry), '!');
		$callable = strpos($enabled, '|') !== false ? substr($enabled, strrpos($enabled, '|') + 1) : $enabled;
		if (rtrim($callable, '#') === $function)
			return true;
	}

	return false;
}

function BetterCaptcha_LoadTheme()
{
	global $modSettings;

	// dev4d self-heal: ensure pre/test/post are available later in this request.
	BetterCaptcha_EnsureVerificationHooks(true);

	$settings = BetterCaptcha_Settings();
	if (!$settings['enabled'])
		return;

	$visual = (int) $settings['visual_level'];

	if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'verificationcode' && !empty($_GET['vid']))
	{
		$id = (string) $_GET['vid'];
		if (BetterCaptcha_ContextEnabled($id, $settings) && !empty($_SESSION[$id . '_vv']['better_captcha_visual_level']))
			$visual = (int) $_SESSION[$id . '_vv']['better_captcha_visual_level'];
	}

	$modSettings['visual_verification_type'] = max(1, min(5, $visual));
	$modSettings['recaptcha_enabled'] = 0;
}

function BetterCaptcha_VerificationPre(&$verificationOptions, $do_test)
{
	global $context, $txt;

	$settings = BetterCaptcha_Settings();
	if (!$settings['enabled'] || empty($verificationOptions['id']))
		return;

	BetterCaptcha_LoadLanguage();
	$id = (string) $verificationOptions['id'];
	if (!BetterCaptcha_ContextEnabled($id, $settings))
		return;

	if (!isset($_SESSION['better_captcha_trace']) || !is_array($_SESSION['better_captcha_trace']))
		$_SESSION['better_captcha_trace'] = array();
	$_SESSION['better_captcha_trace'][$id] = isset($_SESSION['better_captcha_trace'][$id]) && is_array($_SESSION['better_captcha_trace'][$id]) ? $_SESSION['better_captcha_trace'][$id] : array();
	$_SESSION['better_captcha_trace'][$id]['pre_seen'] = time();
	$_SESSION['better_captcha_trace'][$id]['pre_do_test'] = !empty($do_test) ? 1 : 0;

	if (!isset($context['controls']['verification'][$id]))
		return;

	$context['controls']['verification'][$id]['show_visual'] = true;
	$context['controls']['verification'][$id]['empty_field'] = true;
	$context['controls']['verification'][$id]['can_recaptcha'] = false;

	if (!isset($_SESSION[$id . '_vv']))
		$_SESSION[$id . '_vv'] = array();

	if (!$do_test)
	{
		$state = &$_SESSION[$id . '_vv'];
		$profile = BetterCaptcha_AdaptiveProfile($id, $settings);
		$_SESSION['better_captcha_trace'][$id]['render_failures'] = (int) $profile['failures'];
		$_SESSION['better_captcha_trace'][$id]['render_tier'] = (int) $profile['tier'];
		$_SESSION['better_captcha_trace'][$id]['render_puzzle_active'] = BetterCaptcha_PuzzleIsActive($id, $settings, $profile) ? 1 : 0;
		$expired = !empty($state['better_captcha_issued']) && time() - (int) $state['better_captcha_issued'] > $settings['lifetime'];

		if (empty($state['better_captcha_token']) || empty($state['better_captcha_issued']) || $expired)
		{
			try
			{
				$token = bin2hex(random_bytes(24));
			}
			catch (Exception $e)
			{
				$token = hash('sha256', uniqid((string) mt_rand(), true) . session_id());
			}

			$state['better_captcha_token'] = $token;
			$state['better_captcha_issued'] = time();
		}

		$state['better_captcha_tier'] = $profile['tier'];
		$state['better_captcha_visual_level'] = $profile['visual_level'];
		$state['better_captcha_min_seconds'] = $profile['min_seconds'];

		if (!isset($context['better_captcha_tokens']))
			$context['better_captcha_tokens'] = array();
		$context['better_captcha_tokens'][$id] = $state['better_captcha_token'];

		if (!empty($settings['show_level_notice']) && $profile['tier'] > 0)
		{
			if (!isset($context['better_captcha_notices']))
				$context['better_captcha_notices'] = array();
			$context['better_captcha_notices'][$id] = $profile['tier'] >= 2 ? $txt['better_captcha_notice_tier2'] : $txt['better_captcha_notice_tier1'];
		}

		if (BetterCaptcha_PuzzleIsActive($id, $settings, $profile))
		{
			$puzzle = BetterCaptcha_PuzzleEnsure($id, $settings);
			if (!isset($context['better_captcha_puzzles']))
				$context['better_captcha_puzzles'] = array();
			$context['better_captcha_puzzles'][$id] = $puzzle;
			$_SESSION['better_captcha_trace'][$id]['render_puzzle_state'] = !empty($puzzle['nonce']) ? 1 : 0;
		}
		else
			$_SESSION['better_captcha_trace'][$id]['render_puzzle_state'] = 0;
	}
}

function BetterCaptcha_VerificationTest($thisVerification, &$verification_errors)
{
	$settings = BetterCaptcha_Settings();
	if (!$settings['enabled'] || empty($thisVerification['id']))
		return;

	BetterCaptcha_LoadLanguage();
	$id = (string) $thisVerification['id'];
	if (!BetterCaptcha_ContextEnabled($id, $settings))
		return;

	if (!isset($_SESSION['better_captcha_trace']) || !is_array($_SESSION['better_captcha_trace']))
		$_SESSION['better_captcha_trace'] = array();
	$_SESSION['better_captcha_trace'][$id] = isset($_SESSION['better_captcha_trace'][$id]) && is_array($_SESSION['better_captcha_trace'][$id]) ? $_SESSION['better_captcha_trace'][$id] : array();
	$_SESSION['better_captcha_trace'][$id]['test_seen'] = time();
	$_SESSION['better_captcha_trace'][$id]['test_errors_enter'] = implode(',', array_values(array_unique((array) $verification_errors)));

	BetterCaptcha_StatIncrement('attempts');

	$state = isset($_SESSION[$id . '_vv']) && is_array($_SESSION[$id . '_vv']) ? $_SESSION[$id . '_vv'] : array();
	$rate = BetterCaptcha_RateState($id);
	$now = time();

	if (!empty($rate['locked_until']) && (int) $rate['locked_until'] > $now)
	{
		$verification_errors[] = 'better_captcha_rate_limited';
		return;
	}

	if (empty($state['better_captcha_issued']) || empty($state['better_captcha_token']))
	{
		$verification_errors[] = 'better_captcha_session';
		BetterCaptcha_StatIncrement('session_errors');
		return;
	}

	$age = $now - (int) $state['better_captcha_issued'];
	$effective_min = isset($state['better_captcha_min_seconds']) ? (int) $state['better_captcha_min_seconds'] : (int) $settings['min_seconds'];
	if ($age < $effective_min)
	{
		$verification_errors[] = 'better_captcha_too_fast';
		BetterCaptcha_StatIncrement('too_fast');
	}

	if ($age > $settings['lifetime'])
	{
		$verification_errors[] = 'better_captcha_expired';
		BetterCaptcha_StatIncrement('expired');
	}

	// dev4b: rely on SMF's session-bound verification control plus Better CAPTCHA's
	// server-side issued challenge state. The puzzle keeps its own opaque nonce.
	// This avoids false integrity failures caused by theme/form markup differences.

	// Tier-2 puzzle can replace the typed visual code for this attempt. The native
	// visual/audio method remains available when the visitor explicitly selects it.
	$profile = BetterCaptcha_AdaptiveProfile($id, $settings);
	if (BetterCaptcha_PuzzleIsActive($id, $settings, $profile) && !empty($state['better_captcha_puzzle']))
	{
		$method = isset($_REQUEST['better_captcha_method'][$id]) ? (string) $_REQUEST['better_captcha_method'][$id] : 'puzzle';
		if ($method !== 'visual')
		{
			$verification_errors = array_values(array_diff($verification_errors, array('wrong_verification_code')));
			$puzzle = $state['better_captcha_puzzle'];
			$px = isset($_REQUEST['better_captcha_puzzle_x'][$id]) ? (int) $_REQUEST['better_captcha_puzzle_x'][$id] : -9999;
			$py = isset($_REQUEST['better_captcha_puzzle_y'][$id]) ? (int) $_REQUEST['better_captcha_puzzle_y'][$id] : -9999;
			$pn = isset($_REQUEST['better_captcha_puzzle_nonce'][$id]) ? (string) $_REQUEST['better_captcha_puzzle_nonce'][$id] : '';
			$tol = isset($puzzle['tolerance']) ? max(4, (int) $puzzle['tolerance']) : 14;
			$nonce_ok = $pn !== '' && isset($puzzle['nonce']) && hash_equals((string) $puzzle['nonce'], $pn);
			$position_ok = abs($px - (int) $puzzle['x']) <= $tol && abs($py - (int) $puzzle['y']) <= $tol;
			if (!$nonce_ok || !$position_ok)
				$verification_errors[] = 'better_captcha_puzzle';
		}
	}

	$verification_errors = array_values(array_unique($verification_errors));
	$_SESSION['better_captcha_trace'][$id]['test_errors_exit'] = implode(',', $verification_errors);
}

function BetterCaptcha_VerificationPost(&$verification_errors, $do_test)
{
	global $context;

	$settings = BetterCaptcha_Settings();
	if (!$settings['enabled'] || !$do_test || empty($context['controls']['verification']))
		return;

	foreach ($context['controls']['verification'] as $id => $verification)
	{
		if (!is_array($verification) || empty($verification['id']))
			continue;

		$id = (string) $verification['id'];
		if (!BetterCaptcha_ContextEnabled($id, $settings))
			continue;

		BetterCaptcha_RateState($id);
		$rate = &$_SESSION['better_captcha_rate'][$id];
		$old_tier = !empty($_SESSION[$id . '_vv']['better_captcha_tier']) ? (int) $_SESSION[$id . '_vv']['better_captcha_tier'] : 0;

		if (!empty($verification_errors))
		{
			// dev4e: a visitor retrying while already locked must not restart the
			// countdown. Only a genuine verification failure can advance failures
			// or create a fresh lockout window.
			$already_locked = in_array('better_captcha_rate_limited', $verification_errors, true)
				&& !empty($rate['locked_until']) && (int) $rate['locked_until'] > time();

			if (!$already_locked)
			{
				BetterCaptcha_StatIncrement('failures');
				$rate['failures'] = isset($rate['failures']) ? (int) $rate['failures'] + 1 : 1;

				$new_profile = BetterCaptcha_AdaptiveProfile($id, $settings);
				if ((int) $new_profile['tier'] > $old_tier)
					BetterCaptcha_StatIncrement('escalations');

				if ($rate['failures'] >= $settings['max_failures'])
				{
					$rate['locked_until'] = time() + $settings['lockout'];
					BetterCaptcha_StatIncrement('lockouts');
					if (!in_array('better_captcha_rate_limited', $verification_errors, true))
						$verification_errors[] = 'better_captcha_rate_limited';
				}
			}
		}
		else
		{
			BetterCaptcha_StatIncrement('passes');
			$rate = array('failures' => 0, 'locked_until' => 0);
		}

		if (!isset($_SESSION['better_captcha_trace']) || !is_array($_SESSION['better_captcha_trace']))
			$_SESSION['better_captcha_trace'] = array();
		$_SESSION['better_captcha_trace'][$id] = isset($_SESSION['better_captcha_trace'][$id]) && is_array($_SESSION['better_captcha_trace'][$id]) ? $_SESSION['better_captcha_trace'][$id] : array();
		$_SESSION['better_captcha_trace'][$id]['post_seen'] = time();
		$_SESSION['better_captcha_trace'][$id]['post_errors'] = implode(',', array_values(array_unique((array) $verification_errors)));
		$_SESSION['better_captcha_trace'][$id]['post_failures'] = isset($rate['failures']) ? (int) $rate['failures'] : 0;
		$post_profile = BetterCaptcha_AdaptiveProfile($id, $settings);
		$_SESSION['better_captcha_trace'][$id]['post_tier'] = (int) $post_profile['tier'];

		if (isset($_SESSION[$id . '_vv']))
		{
			unset($_SESSION[$id . '_vv']['better_captcha_token']);
			unset($_SESSION[$id . '_vv']['better_captcha_issued']);
			unset($_SESSION[$id . '_vv']['better_captcha_tier']);
			unset($_SESSION[$id . '_vv']['better_captcha_visual_level']);
			unset($_SESSION[$id . '_vv']['better_captcha_min_seconds']);
			unset($_SESSION[$id . '_vv']['better_captcha_puzzle']);
		}
	}
}

function BetterCaptcha_Buffer($buffer)
{
	global $context;

	$settings = BetterCaptcha_Settings();
	if (!$settings['enabled'] || !is_string($buffer) || stripos($buffer, '<form') === false)
		return $buffer;

	$action = isset($_REQUEST['action']) ? (string) $_REQUEST['action'] : '';
	if (in_array($action, array('verificationcode', 'dlattach', 'viewsmfile', 'xmlhttp'), true) || isset($_REQUEST['xml']))
		return $buffer;

	foreach (headers_list() as $header)
	{
		if (stripos($header, 'Content-Type:') === 0 && stripos($header, 'text/html') === false && stripos($header, 'application/xhtml+xml') === false)
			return $buffer;
	}

	// dev4f: puzzle-mode polish removes the last stock visual instruction/separator chrome while preserving the explicit visual/audio fallback.

	// dev4b: no generic hidden-token injection is required.

	// dev4b puzzle prototype: inject a local drag/drop challenge near the verification control.
	// Prefer the normal code input as the anchor, with the verification image as a fallback.
	// The normal visual/audio challenge remains available as an explicit fallback.
	if (!empty($context['better_captcha_puzzles']))
	{
		global $scripturl, $txt;
		BetterCaptcha_LoadLanguage();
		foreach ($context['better_captcha_puzzles'] as $id => $puzzle)
		{
			$id_s = htmlspecialchars((string) $id, ENT_QUOTES, 'UTF-8');
			$id_q = preg_quote((string) $id, '~');
			$nonce = htmlspecialchars((string) $puzzle['nonce'], ENT_QUOTES, 'UTF-8');
			$bg = htmlspecialchars($scripturl . '?action=verificationcode;betterpuzzle=1;id=' . rawurlencode((string) $id) . ';kind=bg;n=' . rawurlencode((string) $puzzle['nonce']), ENT_QUOTES, 'UTF-8');
			$piece = htmlspecialchars($scripturl . '?action=verificationcode;betterpuzzle=1;id=' . rawurlencode((string) $id) . ';kind=piece;n=' . rawurlencode((string) $puzzle['nonce']), ENT_QUOTES, 'UTF-8');
			$puzzle_html = '<div class="better-captcha-puzzle" data-id="' . $id_s . '" style="margin:12px auto;padding:12px;border:1px solid #c7d2dc;border-radius:7px;background:rgba(255,255,255,.55);max-width:344px;text-align:center">'
				. '<strong>' . htmlspecialchars($txt['better_captcha_puzzle_title'], ENT_QUOTES, 'UTF-8') . '</strong>'
				. '<div class="smalltext" style="margin:4px 0 9px">' . htmlspecialchars($txt['better_captcha_puzzle_desc'], ENT_QUOTES, 'UTF-8') . '</div>'
				. '<div class="better-captcha-puzzle-stage" style="position:relative;width:320px;height:180px;max-width:100%;margin:0 auto;touch-action:none;overflow:hidden;border-radius:6px;box-shadow:0 1px 4px rgba(0,0,0,.22);background:#233747">'
				. '<img src="' . $bg . '" width="320" height="180" alt="" style="display:block;width:320px;height:180px;max-width:none">'
				. '<img class="better-captcha-puzzle-piece" src="' . $piece . '" width="64" height="64" alt="' . htmlspecialchars($txt['better_captcha_puzzle_piece_alt'], ENT_QUOTES, 'UTF-8') . '" draggable="false" style="position:absolute;left:12px;top:102px;width:64px;height:64px;cursor:grab;filter:drop-shadow(0 2px 3px rgba(0,0,0,.55));user-select:none">'
				. '</div>'
				. '<input type="hidden" name="better_captcha_method[' . $id_s . ']" value="puzzle" class="better-captcha-method">'
				. '<input type="hidden" name="better_captcha_puzzle_x[' . $id_s . ']" value="-9999" class="better-captcha-puzzle-x">'
				. '<input type="hidden" name="better_captcha_puzzle_y[' . $id_s . ']" value="-9999" class="better-captcha-puzzle-y">'
				. '<input type="hidden" name="better_captcha_puzzle_nonce[' . $id_s . ']" value="' . $nonce . '">'
				. '<div class="smalltext better-captcha-puzzle-status" style="margin-top:7px">' . htmlspecialchars($txt['better_captcha_puzzle_ready'], ENT_QUOTES, 'UTF-8') . '</div>'
				. '<button type="button" class="button better-captcha-use-visual" style="margin-top:8px">' . htmlspecialchars($txt['better_captcha_puzzle_use_visual'], ENT_QUOTES, 'UTF-8') . '</button>'
				. '</div>';
			$input_pattern = '~(<input\b[^>]*name=["\']' . $id_q . '_vv\[code\]["\'][^>]*>)~i';
			$buffer = preg_replace($input_pattern, $puzzle_html . '$1', $buffer, 1, $inserted);
			if (empty($inserted))
			{
				$image_pattern = '~(<img\b[^>]*id=["\']verification_image_' . $id_q . '["\'][^>]*>)~i';
				$buffer = preg_replace($image_pattern, $puzzle_html . '$1', $buffer, 1, $inserted);
			}

			// Puzzle mode replaces the typed code for this attempt, so remove HTML5 required.
			if (!empty($inserted))
				$buffer = preg_replace_callback($input_pattern, function ($m) { return preg_replace('~\srequired(?:=["\'][^"\']*["\'])?~i', '', $m[1]); }, $buffer, 1);
		}

		$script = '<script>(function(){document.querySelectorAll(".better-captcha-puzzle").forEach(function(root){var stage=root.querySelector(".better-captcha-puzzle-stage"),piece=root.querySelector(".better-captcha-puzzle-piece"),xInput=root.querySelector(".better-captcha-puzzle-x"),yInput=root.querySelector(".better-captcha-puzzle-y"),method=root.querySelector(".better-captcha-method"),status=root.querySelector(".better-captcha-puzzle-status"),fallback=root.querySelector(".better-captcha-use-visual"),drag=false,dx=0,dy=0,hidden=[];if(!stage||!piece)return;function codeInput(){return document.querySelector("[name=\""+root.dataset.id+"_vv[code]\"]");}function hideNode(n){if(!n||n===root||root.contains(n)||hidden.some(function(x){return x.node===n;}))return;hidden.push({node:n,display:n.style.display});n.style.display="none";}function hideVisual(){var code=codeInput(),img=document.getElementById("verification_image_"+root.dataset.id);if(code){code.required=false;hideNode(code);var cp=code.parentElement;if(code.id){var label=document.querySelector("label[for=\""+code.id+"\"]");hideNode(label);}if(cp){Array.prototype.forEach.call(cp.querySelectorAll("label"),function(n){if(!root.contains(n))hideNode(n);});Array.prototype.forEach.call(cp.childNodes,function(n){if(n.nodeType===3&&n.textContent.trim()){var span=document.createElement("span");span.textContent=n.textContent;n.parentNode.replaceChild(span,n);hideNode(span);}});}}if(img){hideNode(img);var p=img.parentElement;if(p){Array.prototype.forEach.call(p.querySelectorAll("a"),hideNode);Array.prototype.forEach.call(p.childNodes,function(n){if(n.nodeType===3&&n.textContent.trim()){var span=document.createElement("span");span.textContent=n.textContent;n.parentNode.replaceChild(span,n);hideNode(span);}});var pp=p.parentElement;if(pp){Array.prototype.forEach.call(pp.childNodes,function(n){if(n.nodeType===3&&/^\s*\/\s*$/.test(n.textContent||"")){var span=document.createElement("span");span.textContent=n.textContent;n.parentNode.replaceChild(span,n);hideNode(span);}});}}}}function showVisual(){hidden.forEach(function(x){x.node.style.display=x.display;});hidden=[];var code=codeInput();if(code){code.style.display="";code.required=true;code.focus();code.scrollIntoView({behavior:"smooth",block:"center"});}}function point(e){var p=e.touches&&e.touches[0]?e.touches[0]:e;return{x:p.clientX,y:p.clientY};}function start(e){var p=point(e),r=piece.getBoundingClientRect();drag=true;dx=p.x-r.left;dy=p.y-r.top;piece.style.cursor="grabbing";if(e.cancelable)e.preventDefault();}function move(e){if(!drag)return;var p=point(e),r=stage.getBoundingClientRect(),scaleX=320/r.width,scaleY=180/r.height,left=(p.x-r.left-dx)*scaleX,top=(p.y-r.top-dy)*scaleY;left=Math.max(-8,Math.min(264,left));top=Math.max(-8,Math.min(124,top));piece.style.left=left+"px";piece.style.top=top+"px";xInput.value=Math.round(left);yInput.value=Math.round(top);method.value="puzzle";if(status)status.textContent=' . json_encode($txt['better_captcha_puzzle_moved']) . ';if(e.cancelable)e.preventDefault();}function end(){drag=false;piece.style.cursor="grab";}hideVisual();piece.addEventListener("pointerdown",function(e){piece.setPointerCapture&&piece.setPointerCapture(e.pointerId);start(e);});piece.addEventListener("pointermove",move);piece.addEventListener("pointerup",end);piece.addEventListener("pointercancel",end);piece.addEventListener("touchstart",start,{passive:false});piece.addEventListener("touchmove",move,{passive:false});piece.addEventListener("touchend",end);if(fallback)fallback.addEventListener("click",function(){method.value="visual";root.style.display="none";showVisual();if(status)status.textContent=' . json_encode($txt['better_captcha_puzzle_visual_selected']) . ';});});})();</script>';
		$buffer = str_ireplace('</body>', $script . '</body>', $buffer);
	}

	// dev3 user-facing polish: place a short escalation notice directly before
	// the matching SMF verification code input. No JavaScript is required.
	if (!empty($context['better_captcha_notices']))
	{
		foreach ($context['better_captcha_notices'] as $id => $notice)
		{
			$id_q = preg_quote((string) $id, '~');
			$notice_html = '<div class="information better_captcha_notice"><strong>' . htmlspecialchars((string) $notice, ENT_QUOTES, 'UTF-8') . '</strong></div>';
			$buffer = preg_replace('~(<input\\b[^>]*name=["\\\']' . $id_q . '_vv\\[code\\]["\\\'][^>]*>)~i', $notice_html . '$1', $buffer, 1);
		}
	}

	return $buffer;
}

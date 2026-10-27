<?php
/** Better CAPTCHA admin template. @version 0.1.0 */
if (!defined('SMF'))
	die('No direct access...');

function template_better_captcha_admin()
{
	global $context, $txt, $scripturl;

	$s = $context['better_captcha'];
	$status = $context['better_captcha_status'];
	$stats = $context['better_captcha_stats'];
	$balanced = BetterCaptcha_Defaults('balanced');
	$privacy = BetterCaptcha_Defaults('privacy');
	$high = BetterCaptcha_Defaults('high');

	if (!empty($context['better_captcha_saved']))
		echo '<div class="infobox">', $txt['better_captcha_saved'], '</div>';
	if (!empty($context['better_captcha_stats_reset']))
		echo '<div class="infobox">', $txt['better_captcha_stats_reset'], '</div>';

	echo '
	<style>
		.better-captcha-panel { margin: 0 0 1em; }
		.better-captcha-panel > .cat_bar,
		.better-captcha-panel > .title_bar { margin-bottom: 0 !important; }
		.better-captcha-panel > .cat_bar,
		.better-captcha-panel > .title_bar,
		.better-captcha-panel > .cat_bar .catbg,
		.better-captcha-panel > .title_bar .titlebg { border-radius: 6px 6px 0 0 !important; }
		.better-captcha-panel > .windowbg,
		.better-captcha-panel > .roundframe {
			margin-top: 0 !important;
			border-top: 0 !important;
			border-radius: 0 0 6px 6px !important;
		}
		.better-captcha-custom-only { display: none; }
		.better-captcha-preset-summary { margin: .75em 0 1em; }
	</style>

	<div class="better-captcha-panel">
		<div class="cat_bar"><h3 class="catbg">', $txt['better_captcha_admin_title'], '</h3></div>
		<div class="windowbg">
			<p class="description">', $txt['better_captcha_admin_desc'], '</p>
			<div class="information"><strong>', $txt['better_captcha_level_label'], ':</strong> ', $context['better_captcha_protection_label'], '<br><span class="smalltext">', $txt['better_captcha_level_desc'], '</span></div>
			<form action="', $scripturl, '?action=admin;area=bettercaptcha" method="post" accept-charset="UTF-8">
				<dl class="settings">
					<dt><strong>', $txt['better_captcha_enabled'], '</strong><br><span class="smalltext">', $txt['better_captcha_enabled_desc'], '</span></dt>
					<dd><input type="checkbox" name="better_captcha_enabled" value="1"', $s['enabled'] ? ' checked' : '', '></dd>
					<dt><strong>', $txt['better_captcha_preset'], '</strong></dt>
					<dd><select name="better_captcha_preset" id="better_captcha_preset">
						<option value="balanced"', $s['preset'] === 'balanced' ? ' selected' : '', '>', $txt['better_captcha_preset_balanced'], '</option>
						<option value="privacy"', $s['preset'] === 'privacy' ? ' selected' : '', '>', $txt['better_captcha_preset_privacy'], '</option>
						<option value="high"', $s['preset'] === 'high' ? ' selected' : '', '>', $txt['better_captcha_preset_high'], '</option>
						<option value="custom"', $s['preset'] === 'custom' ? ' selected' : '', '>', $txt['better_captcha_preset_custom'], '</option>
					</select><br><span class="smalltext">', $txt['better_captcha_preset_desc'], '</span></dd>
				</dl>

				<div id="better_captcha_preset_summary" class="information better-captcha-preset-summary"
					data-balanced="', sprintf($txt['better_captcha_preset_summary_format'], $balanced['visual_level'], $balanced['adaptive_step1'], $balanced['adaptive_step2'], $balanced['min_seconds'], $balanced['max_failures'], $balanced['lockout']), '"
					data-privacy="', sprintf($txt['better_captcha_preset_summary_format'], $privacy['visual_level'], $privacy['adaptive_step1'], $privacy['adaptive_step2'], $privacy['min_seconds'], $privacy['max_failures'], $privacy['lockout']), '"
					data-high="', sprintf($txt['better_captcha_preset_summary_format'], $high['visual_level'], $high['adaptive_step1'], $high['adaptive_step2'], $high['min_seconds'], $high['max_failures'], $high['lockout']), '"
					data-custom="', $txt['better_captcha_preset_summary_custom'], '">
					<strong>', $txt['better_captcha_preset_summary_label'], ':</strong> <span id="better_captcha_preset_summary_text"></span>
				</div>

				<div class="better-captcha-panel">
					<div class="title_bar"><h3 class="titlebg">', $txt['better_captcha_context_title'], '</h3></div>
					<div class="roundframe">
						<p class="smalltext">', $txt['better_captcha_context_desc'], '</p>
						<dl class="settings">
							<dt><strong>', $txt['better_captcha_protect_registration'], '</strong><br><span class="smalltext">', $txt['better_captcha_protect_registration_desc'], '</span></dt>
							<dd><input type="checkbox" name="better_captcha_protect_registration" value="1"', $s['protect_registration'] ? ' checked' : '', '></dd>
							<dt><strong>', $txt['better_captcha_protect_other'], '</strong><br><span class="smalltext">', $txt['better_captcha_protect_other_desc'], '</span></dt>
							<dd><input type="checkbox" name="better_captcha_protect_other" value="1"', $s['protect_other'] ? ' checked' : '', '></dd>
						</dl>
					</div>
				</div>

				<div class="better-captcha-panel better-captcha-custom-only">
					<div class="title_bar"><h3 class="titlebg">', $txt['better_captcha_adaptive_title'], '</h3></div>
					<div class="roundframe">
						<p class="smalltext">', $txt['better_captcha_adaptive_desc'], '</p>
						<dl class="settings">
							<dt><strong>', $txt['better_captcha_adaptive_enabled'], '</strong></dt>
							<dd><input type="checkbox" name="better_captcha_adaptive_enabled" value="1"', $s['adaptive_enabled'] ? ' checked' : '', '></dd>
							<dt><strong>', $txt['better_captcha_show_level_notice'], '</strong><br><span class="smalltext">', $txt['better_captcha_show_level_notice_desc'], '</span></dt>
							<dd><input type="checkbox" name="better_captcha_show_level_notice" value="1"', $s['show_level_notice'] ? ' checked' : '', '></dd>
							<dt>', $txt['better_captcha_adaptive_step1'], '</dt>
							<dd><input type="number" min="1" max="20" name="better_captcha_adaptive_step1" value="', (int) $s['adaptive_step1'], '"></dd>
							<dt>', $txt['better_captcha_adaptive_step2'], '</dt>
							<dd><input type="number" min="2" max="20" name="better_captcha_adaptive_step2" value="', (int) $s['adaptive_step2'], '"></dd>
							<dt>', $txt['better_captcha_adaptive_time_boost'], '</dt>
							<dd><input type="number" min="0" max="10" name="better_captcha_adaptive_time_boost" value="', (int) $s['adaptive_time_boost'], '"></dd>
							<dt>', $txt['better_captcha_adaptive_max_visual'], '</dt>
							<dd><input type="number" min="1" max="5" name="better_captcha_adaptive_max_visual" value="', (int) $s['adaptive_max_visual'], '"></dd>
						</dl>
						<p class="smalltext">', $txt['better_captcha_adaptive_note'], '</p>
					</div>
				</div>

				<div class="better-captcha-panel">
					<div class="title_bar"><h3 class="titlebg">', $txt['better_captcha_puzzle_admin_title'], '</h3></div>
					<div class="roundframe">
						<p class="smalltext">', $txt['better_captcha_puzzle_admin_desc'], '</p>
						<dl class="settings">
							<dt><strong>', $txt['better_captcha_puzzle_enabled'], '</strong><br><span class="smalltext">', $txt['better_captcha_puzzle_enabled_desc'], '</span></dt>
							<dd><input type="checkbox" name="better_captcha_puzzle_enabled" value="1"', $s['puzzle_enabled'] ? ' checked' : '', '></dd>
						</dl>
					</div>
				</div>

				<div class="better-captcha-panel">
					<div class="title_bar"><h3 class="titlebg">', $txt['better_captcha_visibility_title'], '</h3></div>
					<div class="roundframe">
						<p class="smalltext">', $txt['better_captcha_visibility_desc'], '</p>
						<dl class="settings">
							<dt><strong>', $txt['better_captcha_collect_stats'], '</strong><br><span class="smalltext">', $txt['better_captcha_collect_stats_desc'], '</span></dt>
							<dd><input type="checkbox" name="better_captcha_collect_stats" value="1"', $s['collect_stats'] ? ' checked' : '', '></dd>
						</dl>
					</div>
				</div>

				<div class="better-captcha-panel better-captcha-custom-only">
					<div class="title_bar"><h3 class="titlebg">', $txt['better_captcha_custom_title'], '</h3></div>
					<div class="roundframe">
						<p class="smalltext">', $txt['better_captcha_custom_desc'], '</p>
						<dl class="settings">
							<dt>', $txt['better_captcha_visual_level'], '</dt>
							<dd><input type="number" min="1" max="5" name="better_captcha_visual_level" value="', (int) $s['visual_level'], '"></dd>
							<dt>', $txt['better_captcha_min_seconds'], '</dt>
							<dd><input type="number" min="0" max="30" name="better_captcha_min_seconds" value="', (int) $s['min_seconds'], '"></dd>
							<dt>', $txt['better_captcha_lifetime'], '</dt>
							<dd><input type="number" min="60" max="3600" name="better_captcha_lifetime" value="', (int) $s['lifetime'], '"></dd>
							<dt>', $txt['better_captcha_max_failures'], '</dt>
							<dd><input type="number" min="1" max="20" name="better_captcha_max_failures" value="', (int) $s['max_failures'], '"></dd>
							<dt>', $txt['better_captcha_lockout'], '</dt>
							<dd><input type="number" min="30" max="3600" name="better_captcha_lockout" value="', (int) $s['lockout'], '"></dd>
						</dl>
					</div>
				</div>

				<input type="submit" name="save" value="', $txt['save'], '" class="button">
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
			</form>
		</div>
	</div>

	<div class="better-captcha-panel">
		<div class="cat_bar"><h3 class="catbg">', $txt['better_captcha_stats_title'], '</h3></div>
		<div class="windowbg">
			<p class="description">', $txt['better_captcha_stats_desc'], '</p>
			<dl class="settings">
				<dt>', $txt['better_captcha_stat_attempts'], '</dt><dd><strong>', (int) $stats['attempts'], '</strong></dd>
				<dt>', $txt['better_captcha_stat_passes'], '</dt><dd><strong>', (int) $stats['passes'], '</strong></dd>
				<dt>', $txt['better_captcha_stat_failures'], '</dt><dd><strong>', (int) $stats['failures'], '</strong></dd>
				<dt>', $txt['better_captcha_stat_escalations'], '</dt><dd><strong>', (int) $stats['escalations'], '</strong></dd>
				<dt>', $txt['better_captcha_stat_lockouts'], '</dt><dd><strong>', (int) $stats['lockouts'], '</strong></dd>
				<dt>', $txt['better_captcha_stat_too_fast'], '</dt><dd><strong>', (int) $stats['too_fast'], '</strong></dd>
				<dt>', $txt['better_captcha_stat_expired'], '</dt><dd><strong>', (int) $stats['expired'], '</strong></dd>
				<dt>', $txt['better_captcha_stat_session_errors'], '</dt><dd><strong>', (int) $stats['session_errors'], '</strong></dd>
			</dl>
			<form action="', $scripturl, '?action=admin;area=bettercaptcha" method="post">
				<input type="submit" name="resetstats" value="', $txt['better_captcha_stats_reset_button'], '" class="button">
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
			</form>
		</div>
	</div>

	<div class="better-captcha-panel">
		<div class="cat_bar"><h3 class="catbg">', $txt['better_captcha_status_title'], '</h3></div>
		<div class="windowbg">
			<dl class="settings">
				<dt>', $txt['better_captcha_status_php'], '</dt><dd><strong>', htmlspecialchars($status['php'], ENT_QUOTES, 'UTF-8'), '</strong></dd>
				<dt>', $txt['better_captcha_status_gd'], '</dt><dd>', $status['gd'] ? $txt['better_captcha_status_ok'] : $txt['better_captcha_status_missing'], '</dd>
				<dt>', $txt['better_captcha_status_session'], '</dt><dd>', $status['session'] ? $txt['better_captcha_status_ok'] : $txt['better_captcha_status_missing'], '</dd>
				<dt>', $txt['better_captcha_status_random'], '</dt><dd>', $status['random_bytes'] ? $txt['better_captcha_status_ok'] : $txt['better_captcha_status_fallback'], '</dd>
				<dt>', $txt['better_captcha_status_adaptive'], '</dt><dd>', $status['adaptive'] ? $txt['better_captcha_status_enabled'] : $txt['better_captcha_status_disabled'], '</dd>
				<dt>', $txt['better_captcha_status_stats'], '</dt><dd>', $status['stats'] ? $txt['better_captcha_status_enabled'] : $txt['better_captcha_status_disabled'], '</dd>
				<dt>', $txt['better_captcha_status_puzzle'], '</dt><dd>', $status['puzzle'] ? $txt['better_captcha_status_enabled'] : $txt['better_captcha_status_disabled'], '</dd>
				<dt>', $txt['better_captcha_status_remote'], '</dt><dd><strong>', $txt['better_captcha_status_none'], '</strong></dd>
			</dl>
			<p class="smalltext">', $txt['better_captcha_status_note'], '</p>
		</div>
	</div>

	<script>
	(function () {
		var preset = document.getElementById("better_captcha_preset");
		var summary = document.getElementById("better_captcha_preset_summary");
		var summaryText = document.getElementById("better_captcha_preset_summary_text");
		var customSections = document.querySelectorAll(".better-captcha-custom-only");

		function refreshBetterCaptchaPresetUI() {
			if (!preset)
				return;

			var isCustom = preset.value === "custom";
			for (var i = 0; i < customSections.length; i++)
				customSections[i].style.display = isCustom ? "block" : "none";

			if (summary && summaryText) {
				var text = summary.getAttribute("data-" + preset.value) || "";
				summaryText.textContent = text;
			}
		}

		if (preset) {
			preset.addEventListener("change", refreshBetterCaptchaPresetUI);
			refreshBetterCaptchaPresetUI();
		}
	}());
	</script>';
}

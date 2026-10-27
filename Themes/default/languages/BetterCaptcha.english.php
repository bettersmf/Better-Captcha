<?php
/** Better CAPTCHA English language. @version 0.1.0 */

$txt['better_captcha_admin_title'] = 'Better CAPTCHA';
$txt['better_captcha_admin_desc'] = 'Self-hosted anti-bot protection for SMF. Better CAPTCHA keeps verification local and adds adaptive challenge strength, timing, session integrity, honeypot protection, challenge lifetime, failure throttling and privacy-safe aggregate diagnostics.';
$txt['better_captcha_enabled'] = 'Enable Better CAPTCHA';
$txt['better_captcha_enabled_desc'] = 'Uses SMF\'s local visual/audio challenge and disables remote reCAPTCHA at runtime while Better CAPTCHA is enabled.';
$txt['better_captcha_preset'] = 'Protection preset';
$txt['better_captcha_preset_desc'] = 'Presets apply tested protection values automatically. Choose Custom Settings to reveal and edit the adaptive and base protection values yourself.';
$txt['better_captcha_preset_balanced'] = 'Balanced (recommended)';
$txt['better_captcha_preset_privacy'] = 'Privacy First';
$txt['better_captcha_preset_high'] = 'High Security';
$txt['better_captcha_preset_custom'] = 'Custom Settings';

$txt['better_captcha_preset_summary_label'] = 'Preset values';
$txt['better_captcha_preset_summary_format'] = 'Visual level %d · Tier 1 after %d failure(s) · Tier 2 after %d · Minimum %d second(s) · Lockout after %d failures for %d seconds';
$txt['better_captcha_preset_summary_custom'] = 'Manual configuration is enabled. Adaptive and base protection controls are shown below.';

$txt['better_captcha_level_label'] = 'Protection level';
$txt['better_captcha_level_desc'] = 'A quick summary of the currently selected policy. Adaptive protection can temporarily strengthen individual challenges without changing this base preset.';
$txt['better_captcha_level_disabled'] = 'Disabled';
$txt['better_captcha_level_balanced'] = 'Balanced';
$txt['better_captcha_level_privacy'] = 'Privacy First';
$txt['better_captcha_level_high'] = 'High Security';
$txt['better_captcha_level_custom'] = 'Custom Settings';

$txt['better_captcha_context_title'] = 'Protected verification contexts';
$txt['better_captcha_context_desc'] = 'Choose where Better CAPTCHA adds its extra protections. Turning one off does not disable SMF\'s own verification for that form; it only stops Better CAPTCHA from enhancing it.';
$txt['better_captcha_protect_registration'] = 'Registration';
$txt['better_captcha_protect_registration_desc'] = 'Apply Better CAPTCHA to the public registration verification control.';
$txt['better_captcha_protect_other'] = 'Other SMF verification forms';
$txt['better_captcha_protect_other_desc'] = 'Apply Better CAPTCHA to other SMF forms that use the standard verification control.';

$txt['better_captcha_adaptive_title'] = 'Custom adaptive protection';
$txt['better_captcha_adaptive_desc'] = 'Progressively strengthen the local challenge after failed attempts. Adaptive state is stored only in the visitor\'s SMF session.';
$txt['better_captcha_adaptive_enabled'] = 'Enable adaptive challenge escalation';
$txt['better_captcha_show_level_notice'] = 'Show escalation notice to the visitor';
$txt['better_captcha_show_level_notice_desc'] = 'When protection escalates, show a short message explaining that additional verification is required. No technical or tracking details are exposed.';
$txt['better_captcha_adaptive_step1'] = 'Failures before escalation tier 1';
$txt['better_captcha_adaptive_step2'] = 'Failures before escalation tier 2';
$txt['better_captcha_adaptive_time_boost'] = 'Extra minimum seconds per escalation tier';
$txt['better_captcha_adaptive_max_visual'] = 'Maximum local visual challenge level (1-5)';
$txt['better_captcha_adaptive_note'] = 'Tier 1 raises the visual challenge by one level. Tier 2 raises it by two levels. Each tier can also add extra minimum-submit time. The challenge never exceeds the configured maximum visual level.';
$txt['better_captcha_notice_tier1'] = 'Additional verification required. Please complete the updated challenge below.';
$txt['better_captcha_notice_tier2'] = 'Enhanced verification required after repeated failed attempts. Please complete the new challenge below.';

$txt['better_captcha_visibility_title'] = 'Diagnostics and visibility';
$txt['better_captcha_visibility_desc'] = 'Better CAPTCHA can maintain anonymous aggregate counters so administrators can see whether protection is actually being exercised. These counters contain no usernames, session identifiers or IP addresses.';
$txt['better_captcha_collect_stats'] = 'Collect anonymous aggregate protection counters';
$txt['better_captcha_collect_stats_desc'] = 'Stores only running totals for attempts, passes, failures, escalations, lockouts and validation events. No visitor-level history is created.';

$txt['better_captcha_custom_title'] = 'Custom base protection values';
$txt['better_captcha_custom_desc'] = 'These values are used only with Custom Settings. They define the starting challenge and failure-throttling behavior.';
$txt['better_captcha_visual_level'] = 'Base local visual challenge level (1-5)';
$txt['better_captcha_min_seconds'] = 'Base minimum submit time (seconds)';
$txt['better_captcha_lifetime'] = 'Challenge lifetime (seconds)';
$txt['better_captcha_max_failures'] = 'Failures before temporary lockout';
$txt['better_captcha_lockout'] = 'Temporary lockout duration (seconds)';
$txt['better_captcha_saved'] = 'Better CAPTCHA settings saved.';

$txt['better_captcha_stats_title'] = 'Protection activity';
$txt['better_captcha_stats_desc'] = 'Anonymous running totals since the counters were last reset. Better CAPTCHA does not retain which visitor produced an event.';
$txt['better_captcha_stat_attempts'] = 'Verification attempts';
$txt['better_captcha_stat_passes'] = 'Successful verifications';
$txt['better_captcha_stat_failures'] = 'Failed verifications';
$txt['better_captcha_stat_escalations'] = 'Adaptive escalations';
$txt['better_captcha_stat_lockouts'] = 'Temporary lockouts';
$txt['better_captcha_stat_too_fast'] = 'Too-fast submissions';
$txt['better_captcha_stat_expired'] = 'Expired challenges';
$txt['better_captcha_stat_session_errors'] = 'Better CAPTCHA challenge/session integrity failures';
$txt['better_captcha_stats_reset_button'] = 'Reset protection counters';
$txt['better_captcha_stats_reset'] = 'Better CAPTCHA protection counters reset.';

$txt['better_captcha_status_title'] = 'Environment status';
$txt['better_captcha_status_php'] = 'PHP version';
$txt['better_captcha_status_gd'] = 'GD image support';
$txt['better_captcha_status_session'] = 'PHP session';
$txt['better_captcha_status_random'] = 'Cryptographic random generator';
$txt['better_captcha_status_adaptive'] = 'Adaptive protection';
$txt['better_captcha_status_stats'] = 'Anonymous aggregate counters';
$txt['better_captcha_status_remote'] = 'External CAPTCHA services';
$txt['better_captcha_status_ok'] = 'Available';
$txt['better_captcha_status_missing'] = 'Not available';
$txt['better_captcha_status_fallback'] = 'Fallback available';
$txt['better_captcha_status_enabled'] = 'Enabled';
$txt['better_captcha_status_disabled'] = 'Disabled';
$txt['better_captcha_status_none'] = 'None - self-hosted only';
$txt['better_captcha_status_note'] = 'Better CAPTCHA dev4f keeps visitor-specific challenge, adaptive and throttling state only in the visitor\'s SMF session. Optional persistent diagnostics are aggregate counters only; no IP address, username, session identifier or visitor score is stored.';

$txt['error_better_captcha_too_fast'] = 'That verification was completed too quickly. Please wait a moment and try the new challenge.';
$txt['error_better_captcha_expired'] = 'That verification challenge has expired. Please complete the new challenge shown below.';
$txt['error_better_captcha_session'] = 'The verification session could not be confirmed. Please reload the page and try again.';
$txt['error_better_captcha_rate_limited'] = 'There have been too many failed verification attempts. Please wait a little while before trying again.';

$txt['better_captcha_puzzle_admin_title'] = 'Drag-and-drop puzzle challenge';
$txt['better_captcha_puzzle_admin_desc'] = 'At adaptive tier 2, the local jigsaw-style drag-and-drop puzzle replaces the typed visual code. The standard SMF visual/audio challenge remains available as an accessibility fallback.';
$txt['better_captcha_puzzle_enabled'] = 'Enable tier-2 drag-and-drop puzzle';
$txt['better_captcha_puzzle_enabled_desc'] = 'Requires PHP GD. No image, coordinates, tracking data or challenge result is sent to an external service.';
$txt['better_captcha_puzzle_title'] = 'Puzzle verification';
$txt['better_captcha_puzzle_desc'] = 'Grab the loose puzzle piece and place it into the matching cutout in the picture.';
$txt['better_captcha_puzzle_piece_alt'] = 'Draggable puzzle piece';
$txt['better_captcha_puzzle_ready'] = 'Drag the piece into the matching opening.';
$txt['better_captcha_puzzle_moved'] = 'Piece positioned. Submit the form when it looks correct.';
$txt['better_captcha_puzzle_use_visual'] = 'Use visual/audio challenge instead';
$txt['better_captcha_puzzle_visual_selected'] = 'Visual/audio fallback selected. Complete the normal verification below.';
$txt['better_captcha_status_puzzle'] = 'Tier-2 puzzle challenge';
$txt['error_better_captcha_puzzle'] = 'The puzzle piece was not placed in the correct location. Please try the new challenge.';

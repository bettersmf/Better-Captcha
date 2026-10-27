0.1.0a — Better SMF Branded Admin Icon

- Uses the branded Better SMF icon in the Configuration hover menu.
- Uses the same branded icon on Admin Home → Configuration.
- Installs native SMF small (16px) and large (32px) admin icon assets.

# Better CAPTCHA 0.1.0

Better CAPTCHA is a self-hosted anti-bot protection package for SMF 2.1.x. It keeps verification local while adding adaptive challenge strength, privacy-aware throttling, session integrity checks, timing protection, diagnostics, and a drag-and-drop puzzle challenge.

## Highlights
- Fully self-hosted; no Google reCAPTCHA, Turnstile, remote CAPTCHA API, or external tracking service.
- Protection presets: Balanced, Privacy First, High Security, and Custom Settings.
- Adaptive escalation after failed attempts.
- Tier 2 drag-and-drop puzzle challenge with server-side validation.
- Puzzle OR visual/audio fallback, rather than forcing both challenges at once.
- Mouse, pointer, and touch puzzle interaction.
- Session-bound challenge state, challenge lifetime checks, minimum-submit timing, and failure throttling.
- SMF honeypot protection retained and integrated.
- Registration and other SMF verification contexts can be protected separately.
- Privacy-safe aggregate diagnostics with no stored usernames, IP addresses, session identifiers, or visitor-level scores.
- Environment status checks for PHP, GD, session support, and cryptographic randomness.
- Self-healing verification hook registration for the SMF verification lifecycle.
- Short, preset-based temporary lockouts with proper expiry/reset behavior.
- Better SMF uninstall behavior: files/hooks are removed on uninstall; package-manager Remove data controls destructive settings/data cleanup.

## Puzzle flow
At the highest adaptive tier, Better CAPTCHA replaces the normal letter challenge with a centered drag-and-drop puzzle. The correct target coordinates and challenge state remain server-side. If the user cannot use the puzzle, **Use visual/audio challenge instead** restores SMF's local visual/audio CAPTCHA as the accessibility fallback.

## Presets
- **Balanced (recommended):** moderate escalation and a 90-second temporary lockout.
- **Privacy First:** conservative escalation with minimal retained information and a 120-second temporary lockout.
- **High Security:** rapid escalation and a 300-second temporary lockout.
- **Custom Settings:** exposes the individual adaptive and base protection values for manual tuning.

## Compatibility
- SMF 2.1.x

## Upgrade
Direct upgrade paths are included from all Better CAPTCHA 0.1.0 development builds through dev4f.

## Uninstall
A normal uninstall removes Better CAPTCHA files and integration hooks while preserving Better CAPTCHA settings/data. Selecting SMF Package Manager's **Remove data** option performs destructive Better CAPTCHA cleanup.

## Release status
Version 0.1.0 is the first public release baseline built from the live-tested dev4f code line.

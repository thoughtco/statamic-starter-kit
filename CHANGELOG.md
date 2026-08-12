# Changelog

All notable changes to this starter kit will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Added

- GitHub Actions CI workflow that runs `composer validate` and Laravel Pint (style check) on every push/PR.
- `app/Providers/HorizonServiceProvider.php` now ships with the Thought Collective `viewHorizon` gate pre-configured (restricts non-local Horizon access to `@thoughtcollective.com` emails), so it no longer needs to be added by hand after every install.
- `description` and `license` fields to `composer.json` so `composer validate` passes cleanly.

### Changed

- `config/horizon.php` is no longer exported by the starter kit (`starter-kit.yaml`); Horizon's own defaults are used instead, avoiding a stale config file being copied into every new project.
- `resources/blueprints/collections/news/news.yaml`: bumped `earliest_date` to a more current date.
- `resources/blueprints/globals/social_media.yaml`: renamed the `Pinterest` replicator set key to `pinterest` for consistency with the other lowercase set keys.
- `app/Console/Commands/PanelUsage.php`: fixed a check that skipped pages with zero panels (`< 1` didn't correctly handle non-numeric/empty values); now uses `empty()`.
- `resources/js/components/maps.js`: fixed a marker `infoWindow` scoping bug where the info window instance wasn't available inside the click listener; fixed marker scale precedence.
- `app/Console/Commands/ScrapeGoogleUrls.php`: explicitly passed the `$escape` parameter to `fputcsv()` to avoid a PHP 8.4 deprecation warning.
- `README.md`: reworded the Slider Settings section to point to swiper.js's own API docs instead of an outdated description.
- `.gitignore`: trimmed down to just `.idea` and `.DS_Store`.

### Fixed

- `app/Listeners/GlobalListener.php`: geocoding lookups that fail (bad address, API key issue, etc.) no longer throw uncaught exceptions — they're now logged and surfaced to the editor as a Statamic CP toast notification instead.
- `app/Console/Commands/TestNotification.php`: added a `class_exists()` guard so passing a non-existent `--class` fails with a clear error instead of a fatal error.

### Removed

- `app/Console/Commands/ImportRedirects.php` and its references (superseded / unused).
- `app/Listeners/RecacheUrl.php` and its `UrlInvalidated` event mapping in `EventServiceProvider.php` (unused).
- Static Cache Manager references (`duncanmcclean/static-cache-manager`) from the README and the `access static-cache-manager utility` permission in `resources/users/roles.yaml` (package not used by this kit).
- Stale `image:` front-matter reference on `content/collections/pages/home.md` pointing at an asset that no longer applies.

[Unreleased]: https://github.com/thoughtco/statamic-starter-kit/compare/main...HEAD

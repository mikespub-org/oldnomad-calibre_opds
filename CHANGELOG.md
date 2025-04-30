# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [0.0.4] - 2025-04-29

### Fixed

- Further improvements in iterator caching.

### Changed

- Compatibility updated to minimum Nextcloud v29, up to v32.

## [0.0.3] - 2023-10-29

### Fixed

- Nextcloud supports only numeric log levels (in violation of PSR-3).
- Less aggressive iterator caching, will matter once we implement pagination.

## [0.0.2] - 2023-10-14

### Changed

- Search is now diacritics-agnostic if the pattern doesn't contain diacritics.

### Fixed

- Book search fails with "Iterator does not support rewinding".
- Author URI is emitted even when it's empty.

## [0.0.1] - 2023-10-12

Initial release.

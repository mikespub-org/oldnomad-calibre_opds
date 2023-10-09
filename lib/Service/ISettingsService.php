<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\Service;

use OCP\Files\Folder;

interface ISettingsService {
	/**
	 * Get application ID.
	 *
	 * @return string application ID.
	 */
	public function getAppId(): string;

	/**
	 * Get application version.
	 *
	 * @return string application version.
	 */
	public function getAppVersion(): string;

	/**
	 * Get application name (human-readable).
	 *
	 * @return string application name.
	 */
	public function getAppName(): string;

	/**
	 * Get application website URL.
	 *
	 * @return string application website URL.
	 */
	public function getAppWebsite(): string;

	/**
	 * Transform application route to URL.
	 *
	 * @param string $route route name.
	 * @param array $parameters route parameters.
	 *
	 * @return string URL.
	 */
	public function getAppRouteLink(string $route, array $parameters = []): string;

	/**
	 * Transform image file path to URL.
	 *
	 * @param string $path path to image file.
	 *
	 * @return string image URL.
	 */
	public function getAppImageLink(string $path): string;

	/**
	 * Get language name for ISO-639 language code.
	 *
	 * @param string $code language code.
	 *
	 * @return string language name.
	 */
	public function getLanguageName(string $code): string;

	/**
	 * Get all user settings for current user.
	 *
	 * @return array user settings.
	 */
	public function getSettings(): array;

	/**
	 * Get Calibre library folder for current user.
	 *
	 * @return Folder|null root folder for Calibre library, or `null` if not found.
	 */
	public function getLibraryFolder(): ?Folder;

	/**
	 * Get path to Calibre library for current user.
	 *
	 * @return string|null relative path to root library folder, or `null` on error.
	 */
	public function getLibrary(): ?string;

	/**
	 * Set path to Calibre library for current user.
	 *
	 * @param string $libraryRoot relative path to root library folder.
	 * @return bool `true` if set, `false` on error.
	 */
	public function setLibrary(string $libraryRoot): bool;
}

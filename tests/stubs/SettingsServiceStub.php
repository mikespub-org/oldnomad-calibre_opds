<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Stubs;

use OCA\Calibre2OPDS\Service\ISettingsService;

trait SettingsServiceStub {
	protected const SETTINGS_APP_ID = 'app-id';
	protected const SETTINGS_APP_VERSION = '3.1416';
	protected const SETTINGS_APP_NAME = 'app-name';
	protected const SETTINGS_APP_WEBSITE = 'app-website';

	protected ISettingsService $settings;

	protected function initSettingsService(): void {
		$settings = $this->createStub(ISettingsService::class);
		$settings->method('getAppId')->willReturn(self::SETTINGS_APP_ID);
		$settings->method('getAppVersion')->willReturn(self::SETTINGS_APP_VERSION);
		$settings->method('getAppName')->willReturn(self::SETTINGS_APP_NAME);
		$settings->method('getAppWebsite')->willReturn(self::SETTINGS_APP_WEBSITE);
		$settings->method('getAppRouteLink')->willReturnCallback(function (string $route, array $parameters) {
			return 'app-route:'.$route.'?'.implode('&', array_map(fn ($k, $v) => $k.'='.$v, array_keys($parameters), array_values($parameters)));
		});
		$settings->method('getAppImageLink')->willReturnCallback(function (string $path) {
			return 'app-img:'.$path;
		});
		$settings->method('getLanguageName')->willReturnCallback(function (string $code) {
			return '@'.$code;
		});
		$this->settings = $settings;
	}
}

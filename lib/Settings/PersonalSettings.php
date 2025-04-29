<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\Settings;

use OCA\Calibre2OPDS\Service\ISettingsService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;

class PersonalSettings implements ISettings {
	public function __construct(
		private ISettingsService $settings,
	) {
	}

	public function getForm(): TemplateResponse {
		$data = $this->settings->getSettings();
		return new TemplateResponse($this->settings->getAppId(), 'settings.personal', $data);
	}

	public function getSection(): string {
		return 'sharing';
	}

	public function getPriority(): int {
		return 90;
	}
}

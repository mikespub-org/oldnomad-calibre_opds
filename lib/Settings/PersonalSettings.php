<?php

declare(strict_types=1);

namespace OCA\Calibre2OPDS\Settings;

use OCA\Calibre2OPDS\Service\ISettingsService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;

class PersonalSettings implements ISettings {
	public function __construct(private ISettingsService $settings) {
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

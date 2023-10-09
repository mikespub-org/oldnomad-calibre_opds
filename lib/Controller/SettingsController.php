<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\Controller;

use OCA\Calibre2OPDS\Service\ISettingsService;
use OCP\AppFramework\Controller;
use OCP\IRequest;
use OCP\PreConditionNotMetException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use UnexpectedValueException;

class SettingsController extends Controller {
	public function __construct(IRequest $request, private ISettingsService $settings, private LoggerInterface $logger) {
		parent::__construct($settings->getAppId(), $request);
	}

	/**
	 * @NoAdminRequired
	 */
	public function settings(string $libraryRoot): array {
		try {
			$this->settings->setLibrary($libraryRoot);
		} catch (PreConditionNotMetException|UnexpectedValueException $e) {
			$this->logger->log(LogLevel::ERROR, 'Exception in '.__FUNCTION__, [ 'exception' => $e ]);
		}
		return [
			'libraryRoot' => $this->settings->getLibrary()
		];
	}
}

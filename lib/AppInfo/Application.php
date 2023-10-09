<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\AppInfo;

use OCA\Calibre2OPDS\Service\CalibreService;
use OCA\Calibre2OPDS\Service\ICalibreService;
use OCA\Calibre2OPDS\Service\IOpdsFeedService;
use OCA\Calibre2OPDS\Service\ISettingsService;
use OCA\Calibre2OPDS\Service\OpdsFeedService;
use OCA\Calibre2OPDS\Service\SettingsService;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use Psr\Container\ContainerInterface;

class Application extends App implements IBootstrap {
	public const APP_ID = 'calibre_opds';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function boot(IBootContext $context): void {
		// Nothing to do
	}

	public function register(IRegistrationContext $context): void {
		include_once __DIR__.'/../../vendor/autoload.php';

		$context->registerService(ICalibreService::class, function (ContainerInterface $c): ICalibreService {
			/** @var ICalibreService */
			return $c->get(CalibreService::class);
		});
		$context->registerService(IOpdsFeedService::class, function (ContainerInterface $c): IOpdsFeedService {
			/** @var IOpdsFeedService */
			return $c->get(OpdsFeedService::class);
		});
		$context->registerService(ISettingsService::class, function (ContainerInterface $c): ISettingsService {
			/** @var ISettingsService */
			return $c->get(SettingsService::class);
		});
	}
}

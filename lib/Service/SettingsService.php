<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\Service;

use OCA\Calibre2OPDS\AppInfo\Application;
use OCP\App\IAppManager;
use OCP\Files\FileInfo;
use OCP\Files\Folder;
use OCP\Files\InvalidPathException;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;

/**
 * Service for managing user settings for this app.
 */
class SettingsService implements ISettingsService {
	/**
	 * Default values for parameters.
	 *
	 * @var array<string,string>
	 */
	private const DEFAULTS = [
		'library' => 'Books'
	];

	/**
	 * Application info array, or `null` if not retrieved yet.
	 *
	 * @var null|array<string,mixed>
	 */
	private ?array $appInfo;

	public function __construct(private LoggerInterface $logger, private IConfig $config, private IUserSession $userSession,
		private IRootFolder $rootFolder, private IURLGenerator $urlGenerator, private IAppManager $appManager, private IL10N $l) {
		$this->appInfo = null;
	}

	/**
	 * Get application info array.
	 *
	 * @return array<string,mixed> application info array.
	 */
	private function getAppInfo(): array {
		if (!is_null($this->appInfo)) {
			return $this->appInfo;
		}
		/** @var array<string,mixed> */
		$this->appInfo = $this->appManager->getAppInfo(Application::APP_ID);
		return $this->appInfo;
	}

	public function getAppId(): string {
		return Application::APP_ID;
	}

	public function getAppVersion(): string {
		/** @var string */
		return $this->getAppInfo()['version'];
	}

	public function getAppName(): string {
		/** @var string */
		return $this->getAppInfo()['name'];
	}

	public function getAppWebsite(): string {
		/** @var string */
		return $this->getAppInfo()['website'];
	}

	public function getAppRouteLink(string $route, array $parameters = []): string {
		return $this->urlGenerator->linkToRoute(Application::APP_ID.'.opds.'.$route, $parameters);
	}

	public function getAppImageLink(string $path): string {
		return $this->urlGenerator->imagePath(Application::APP_ID, $path);
	}

	public function getLanguageName(string $code): string {
		$pref_lang = $this->l->getLocaleCode();
		return locale_get_display_name($code, $pref_lang) ?: '@'.$code;
	}

	public function getSettings(): array {
		$user = $this->userSession->getUser();
		if (is_null($user)) {
			return [];
		}
		$uid = $user->getUID();
		$keys = array_keys(self::DEFAULTS);
		return array_combine($keys, array_map(fn ($k) => $this->config->getUserValue($uid, Application::APP_ID, $k, self::DEFAULTS[$k]), $keys));
	}

	public function getLibraryFolder(): ?Folder {
		$user = $this->userSession->getUser();
		$libPath = $this->getLibrary();
		if (is_null($libPath) || is_null($user)) {
			return null;
		}
		try {
			$root = $this->rootFolder->getUserFolder($user->getUID())->get($libPath);
			if (!$root->isReadable() || $root->getType() !== FileInfo::TYPE_FOLDER || !($root instanceof Folder)) {
				throw new NotFoundException('Library root is not a readable folder');
			}
			return $root;
		} catch (InvalidPathException|NotFoundException|NotPermittedException $e) {
			$this->logger->error($e->getMessage(), [ 'exception' => $e ]);
			return null;
		}
	}

	public function getLibrary(): ?string {
		$user = $this->userSession->getUser();
		if (is_null($user)) {
			return null;
		}
		return $this->config->getUserValue($user->getUID(), Application::APP_ID, 'library', self::DEFAULTS['library']);
	}

	public function setLibrary(string $libraryRoot): bool {
		$user = $this->userSession->getUser();
		if (is_null($user)) {
			return false;
		}
		$this->config->setUserValue($user->getUID(), Application::APP_ID, 'library', $libraryRoot);
		return true;
	}
}

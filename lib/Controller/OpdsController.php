<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\Controller;

use Exception;
use OCA\Calibre2OPDS\Calibre\ICalibreDB;
use OCA\Calibre2OPDS\Calibre\Types\CalibreAuthor;
use OCA\Calibre2OPDS\Calibre\Types\CalibreAuthorPrefix;
use OCA\Calibre2OPDS\Calibre\Types\CalibreBook;
use OCA\Calibre2OPDS\Calibre\Types\CalibreBookCriteria;
use OCA\Calibre2OPDS\Calibre\Types\CalibreBookFormat;
use OCA\Calibre2OPDS\Calibre\Types\CalibreLanguage;
use OCA\Calibre2OPDS\Calibre\Types\CalibrePublisher;
use OCA\Calibre2OPDS\Calibre\Types\CalibreSeries;
use OCA\Calibre2OPDS\Calibre\Types\CalibreTag;
use OCA\Calibre2OPDS\Opds\OpenSearchResponse;
use OCA\Calibre2OPDS\Service\ICalibreService;
use OCA\Calibre2OPDS\Service\IOpdsFeedService;
use OCA\Calibre2OPDS\Service\ISettingsService;
use OCA\Calibre2OPDS\Util\MimeTypes;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Http\StreamResponse;
use OCP\Files\Folder;
use OCP\IL10N;
use OCP\IRequest;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class OpdsController extends Controller {
	private const DEFAULT_PREFIX_LENGTH = 1;

	private ICalibreService $calibre;
	private IOpdsFeedService $feed;
	private ISettingsService $settings;
	private IL10N $l;
	private LoggerInterface $logger;

	public function __construct(IRequest $request, ICalibreService $calibre, IOpdsFeedService $feed, ISettingsService $settings, IL10N $l, LoggerInterface $logger) {
		parent::__construct($settings->getAppId(), $request);
		$this->calibre = $calibre;
		$this->feed = $feed;
		$this->settings = $settings;
		$this->l = $l;
		$this->logger = $logger;
	}

	/**
	 * Wrapper for controller routes.
	 *
	 * @param callable(Folder,ICalibreDB):Response $func function to wrap.
	 * @return Response route response.
	 */
	private function methodWrapper(callable $func): Response {
		try {
			if (!$this->settings->isLoggedIn()) {
				return (new Response())->setStatus(Http::STATUS_UNAUTHORIZED)->addHeader(
					'WWW-Authenticate',
					'Basic realm="Nextcloud authentication needed"'
				);
			}
			$libPath = $this->settings->getLibraryFolder();
			if (is_null($libPath)) {
				return (new Response())->setStatus(Http::STATUS_NOT_FOUND);
			}
			$lib = $this->calibre->getDatabase($libPath);
			return call_user_func($func, $libPath, $lib);
		} catch (Exception $e) {
			$this->logger->log(LogLevel::ERROR, 'Exception in '.__FUNCTION__, [ 'exception' => $e ]);
			return (new Response())->setStatus(Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function index(): Response {
		return $this->methodWrapper(function (Folder $libPath, ICalibreDB $lib): Response {
			$builder = $this->feed->createBuilder('index', $this->request->getParams(), $this->l->t('Nextcloud OPDS Library'));
			$builder->addSubsectionItem('authors', 'author_prefixes', $this->l->t('Authors'), $this->l->t('All authors'));
			$builder->addSubsectionItem('publishers', 'publishers', $this->l->t('Publishers'), $this->l->t('All publishers'));
			$builder->addSubsectionItem('languages', 'languages', $this->l->t('Languages'), $this->l->t('All languages'));
			$builder->addSubsectionItem('series', 'series', $this->l->t('Series'), $this->l->t('All series'));
			$builder->addSubsectionItem('tags', 'tags', $this->l->t('Tags'), $this->l->t('All tags'));
			$builder->addSubsectionItem('books', 'books', $this->l->t('Books'), $this->l->t('All books'));
			return $builder->getResponse();
		});
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function authors(string $prefix = ''): Response {
		return $this->methodWrapper(function (Folder $libPath, ICalibreDB $lib) use ($prefix): Response {
			if ($prefix === '') {
				$title = $this->l->t('Authors');
			} else {
				$title = $this->l->t('Authors with name starting on %1$s', [$prefix]);
			}
			$builder = $this->feed->createBuilder('authors', $this->request->getParams(), $title);
			foreach (CalibreAuthor::getByPrefix($lib, $prefix) as $item) {
				$builder->addNavigationEntry($item);
			}
			return $builder->getResponse();
		});
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function authorPrefixes(int $length = self::DEFAULT_PREFIX_LENGTH): Response {
		$length = $length > 0 ? $length : 1;
		return $this->methodWrapper(function (Folder $libPath, ICalibreDB $lib) use ($length): Response {
			$builder = $this->feed->createBuilder('author_prefixes', $this->request->getParams(), $this->l->t('Authors by prefix'));
			foreach (CalibreAuthorPrefix::getAll($lib, $length) as $item) {
				$builder->addNavigationEntry($item);
			}
			return $builder->getResponse();
		});
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function publishers(): Response {
		return $this->methodWrapper(function (Folder $libPath, ICalibreDB $lib): Response {
			$builder = $this->feed->createBuilder('publishers', $this->request->getParams(), $this->l->t('Publishers'));
			foreach (CalibrePublisher::getAll($lib) as $item) {
				$builder->addNavigationEntry($item);
			}
			return $builder->getResponse();
		});
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function languages(): Response {
		return $this->methodWrapper(function (Folder $libPath, ICalibreDB $lib): Response {
			$builder = $this->feed->createBuilder('languages', $this->request->getParams(), $this->l->t('Languages'));
			foreach (CalibreLanguage::getAll($lib) as $item) {
				$builder->addNavigationEntry($item);
			}
			return $builder->getResponse();
		});
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function series(): Response {
		return $this->methodWrapper(function (Folder $libPath, ICalibreDB $lib): Response {
			$builder = $this->feed->createBuilder('series', $this->request->getParams(), $this->l->t('Series'));
			foreach (CalibreSeries::getAll($lib) as $item) {
				$builder->addNavigationEntry($item);
			}
			return $builder->getResponse();
		});
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function tags(): Response {
		return $this->methodWrapper(function (Folder $libPath, ICalibreDB $lib): Response {
			$builder = $this->feed->createBuilder('tags', $this->request->getParams(), $this->l->t('Tags'));
			foreach (CalibreTag::getAll($lib) as $item) {
				$builder->addNavigationEntry($item);
			}
			return $builder->getResponse();
		});
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function books(string $criterion = '', string $id = ''): Response {
		return $this->methodWrapper(function (Folder $libPath, ICalibreDB $lib) use ($criterion, $id): Response {
			$title = $this->l->t('All books');
			$upRoute = null;
			$upParams = [];
			$critCase = CalibreBookCriteria::tryFrom($criterion);
			switch ($critCase) {
				case 'search':
					$title = $this->l->t('All books matching: /%1$s/', [$id]);
					break;
				case 'author':
					$author = CalibreAuthor::getById($lib, $id);
					if (is_null($author)) {
						return (new Response())->setStatus(Http::STATUS_NOT_FOUND);
					}
					$title = $this->l->t('All books by author: %1$s', [$author->name]);
					$upRoute = 'authors';
					/** @var string $author->sort */
					$upParams = [ 'prefix' => substr($author->sort, 0, intval(self::DEFAULT_PREFIX_LENGTH)) ];
					break;
				case 'publisher':
					$publisher = CalibrePublisher::getById($lib, $id);
					if (is_null($publisher)) {
						return (new Response())->setStatus(Http::STATUS_NOT_FOUND);
					}
					$title = $this->l->t('All books by publisher: %1$s', [$publisher->name]);
					$upRoute = 'publishers';
					break;
				case 'language':
					$language = CalibreLanguage::getById($lib, $id);
					if (is_null($language)) {
						return (new Response())->setStatus(Http::STATUS_NOT_FOUND);
					}
					/** @var string $language->code */
					$language->setName($this->settings->getLanguageName($language->code));
					$title = $this->l->t('All books in language: %1$s', [$language->name]);
					break;
				case 'series':
					$series = CalibreSeries::getById($lib, $id);
					if (is_null($series)) {
						return (new Response())->setStatus(Http::STATUS_NOT_FOUND);
					}
					$title = $this->l->t('All books in series: %1$s', [$series->name]);
					break;
				case 'tag':
					$tag = CalibreTag::getById($lib, $id);
					if (is_null($tag)) {
						return (new Response())->setStatus(Http::STATUS_NOT_FOUND);
					}
					$title = $this->l->t('All books with tag: %1$s', [$tag->name]);
					break;
			}
			$builder = $this->feed->createBuilder('books', $this->request->getParams(), $title, $upRoute, $upParams);
			foreach (CalibreBook::getByCriterion($lib, $critCase, $id) as $item) {
				$builder->addBookEntry($item);
			}
			return $builder->getResponse();
		});
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function searchXml(): Response {
		return $this->methodWrapper(function (Folder $libPath, ICalibreDB $lib): Response {
			$resp = new OpenSearchResponse(
				/// TRANSLATORS: No more than 16 characters
				$this->l->t('Search'),
				$this->l->t('Search books'),
				$this->l->t('Search books with matching titles, descriptions, authors, series, or tags.'),
				$this->settings->getAppImageLink('icon.ico'),
				$this->settings->getAppRouteLink('books', [
					'criterion' => CalibreBookCriteria::SEARCH->value,
					'id' => OpenSearchResponse::PLACEHOLDER_SEARCH_TERMS
				])
			);
			return $resp;
		});
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function bookData(string $id, string $type): Response {
		return $this->methodWrapper(function (Folder $libPath, ICalibreDB $lib) use ($id, $type): Response {
			$format = CalibreBookFormat::getByBookAndType($lib, $id, $type);
			if (is_null($format)) {
				return (new Response())->setStatus(Http::STATUS_NOT_FOUND);
			}
			$file = $format->getDataFile($libPath);
			if (is_null($file)) {
				return (new Response())->setStatus(Http::STATUS_NOT_FOUND);
			}
			return (new StreamResponse($file->fopen('r')))->addHeader('Content-Type', MimeTypes::getMimeType($type));
		});
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function bookCover(string $id): Response {
		return $this->methodWrapper(function (Folder $libPath, ICalibreDB $lib) use ($id): Response {
			$book = CalibreBook::getById($lib, $id);
			if (is_null($book)) {
				return (new Response())->setStatus(Http::STATUS_NOT_FOUND);
			}
			$file = $book->getCoverFile($libPath);
			if (is_null($file)) {
				return (new Response())->setStatus(Http::STATUS_NOT_FOUND);
			}
			return (new StreamResponse($file->fopen('r')))->addHeader('Content-Type', MimeTypes::getMimeType('jpg'));
		});
	}
}

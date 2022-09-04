<?php

declare(strict_types=1);

namespace OCA\Calibre2OPDS\Controller;

use Exception;
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
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index(): Response {
		try {
			$libPath = $this->settings->getLibraryFolder();
			if (is_null($libPath)) {
				return (new Response())->setStatus(Http::STATUS_NOT_FOUND);
			}
			$lib = $this->calibre->getDatabase($libPath);
			$builder = $this->feed->createBuilder('index', $this->request->getParams(), $this->l->t('Nextcloud OPDS Library'));
			$builder->addSubsectionItem('authors', 'author_prefixes', $this->l->t('Authors'), $this->l->t('All authors'));
			$builder->addSubsectionItem('publishers', 'publishers', $this->l->t('Publishers'), $this->l->t('All publishers'));
			$builder->addSubsectionItem('languages', 'languages', $this->l->t('Languages'), $this->l->t('All languages'));
			$builder->addSubsectionItem('series', 'series', $this->l->t('Series'), $this->l->t('All series'));
			$builder->addSubsectionItem('tags', 'tags', $this->l->t('Tags'), $this->l->t('All tags'));
			$builder->addSubsectionItem('books', 'books', $this->l->t('Books'), $this->l->t('All books'));
			return $builder->getResponse();
		} catch (Exception $e) {
			$this->logger->log(LogLevel::ERROR, 'Exception in '.__FUNCTION__, [ 'exception' => $e ]);
			return (new Response())->setStatus(Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function authors(string $prefix = ''): Response {
		try {
			$libPath = $this->settings->getLibraryFolder();
			if (is_null($libPath)) {
				return (new Response())->setStatus(Http::STATUS_NOT_FOUND);
			}
			$lib = $this->calibre->getDatabase($libPath);
			if ($prefix === '') {
				$title = $this->l->t('Authors');
			} else {
				$title = $this->l->t('Authors by prefix %1$s', [$prefix]);
			}
			$builder = $this->feed->createBuilder('authors', $this->request->getParams(), $title);
			foreach (CalibreAuthor::getByPrefix($lib, $prefix) as $item) {
				$builder->addNavigationEntry($item);
			}
			return $builder->getResponse();
		} catch (Exception $e) {
			$this->logger->log(LogLevel::ERROR, 'Exception in '.__FUNCTION__, [ 'exception' => $e ]);
			return (new Response())->setStatus(Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function authorPrefixes(int $length = self::DEFAULT_PREFIX_LENGTH): Response {
		try {
			$libPath = $this->settings->getLibraryFolder();
			if (is_null($libPath)) {
				return (new Response())->setStatus(Http::STATUS_NOT_FOUND);
			}
			$lib = $this->calibre->getDatabase($libPath);
			$length = $length > 0 ? $length : 1;
			$builder = $this->feed->createBuilder('author_prefixes', $this->request->getParams(), $this->l->t('Authors by prefix'));
			foreach (CalibreAuthorPrefix::getAll($lib, $length) as $item) {
				$builder->addNavigationEntry($item);
			}
			return $builder->getResponse();
		} catch (Exception $e) {
			$this->logger->log(LogLevel::ERROR, 'Exception in '.__FUNCTION__, [ 'exception' => $e ]);
			return (new Response())->setStatus(Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function publishers(): Response {
		try {
			$libPath = $this->settings->getLibraryFolder();
			if (is_null($libPath)) {
				return (new Response())->setStatus(Http::STATUS_NOT_FOUND);
			}
			$lib = $this->calibre->getDatabase($libPath);
			$builder = $this->feed->createBuilder('publishers', $this->request->getParams(), $this->l->t('Publishers'));
			foreach (CalibrePublisher::getAll($lib) as $item) {
				$builder->addNavigationEntry($item);
			}
			return $builder->getResponse();
		} catch (Exception $e) {
			$this->logger->log(LogLevel::ERROR, 'Exception in '.__FUNCTION__, [ 'exception' => $e ]);
			return (new Response())->setStatus(Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function languages(): Response {
		try {
			$libPath = $this->settings->getLibraryFolder();
			if (is_null($libPath)) {
				return (new Response())->setStatus(Http::STATUS_NOT_FOUND);
			}
			$lib = $this->calibre->getDatabase($libPath);
			$builder = $this->feed->createBuilder('languages', $this->request->getParams(), $this->l->t('Languages'));
			foreach (CalibreLanguage::getAll($lib) as $item) {
				$builder->addNavigationEntry($item);
			}
			return $builder->getResponse();
		} catch (Exception $e) {
			$this->logger->log(LogLevel::ERROR, 'Exception in '.__FUNCTION__, [ 'exception' => $e ]);
			return (new Response())->setStatus(Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function series(): Response {
		try {
			$libPath = $this->settings->getLibraryFolder();
			if (is_null($libPath)) {
				return (new Response())->setStatus(Http::STATUS_NOT_FOUND);
			}
			$lib = $this->calibre->getDatabase($libPath);
			$builder = $this->feed->createBuilder('series', $this->request->getParams(), $this->l->t('Series'));
			foreach (CalibreSeries::getAll($lib) as $item) {
				$builder->addNavigationEntry($item);
			}
			return $builder->getResponse();
		} catch (Exception $e) {
			$this->logger->log(LogLevel::ERROR, 'Exception in '.__FUNCTION__, [ 'exception' => $e ]);
			return (new Response())->setStatus(Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function tags(): Response {
		try {
			$libPath = $this->settings->getLibraryFolder();
			if (is_null($libPath)) {
				return (new Response())->setStatus(Http::STATUS_NOT_FOUND);
			}
			$lib = $this->calibre->getDatabase($libPath);
			$builder = $this->feed->createBuilder('tags', $this->request->getParams(), $this->l->t('Tags'));
			foreach (CalibreTag::getAll($lib) as $item) {
				$builder->addNavigationEntry($item);
			}
			return $builder->getResponse();
		} catch (Exception $e) {
			$this->logger->log(LogLevel::ERROR, 'Exception in '.__FUNCTION__, [ 'exception' => $e ]);
			return (new Response())->setStatus(Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function books(string $criterion = '', string $id = ''): Response {
		try {
			$libPath = $this->settings->getLibraryFolder();
			if (is_null($libPath)) {
				return (new Response())->setStatus(Http::STATUS_NOT_FOUND);
			}
			$lib = $this->calibre->getDatabase($libPath);
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
		} catch (Exception $e) {
			$this->logger->log(LogLevel::ERROR, 'Exception in '.__FUNCTION__, [ 'exception' => $e ]);
			return (new Response())->setStatus(Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function searchXml(): Response {
		try {
			$libPath = $this->settings->getLibraryFolder();
			if (is_null($libPath)) {
				return (new Response())->setStatus(Http::STATUS_NOT_FOUND);
			}
			$lib = $this->calibre->getDatabase($libPath);
			$resp = new OpenSearchResponse(
				/// TRANSLATORS: No more than 16 characters
				$this->l->t('Search'),
				$this->l->t('Search books'),
				$this->l->t('Search books with matching titles, authors, series, or tags.'),
				$this->settings->getAppImageLink('icon.ico'),
				$this->settings->getAppRouteLink('books', [
					'criterion' => CalibreBookCriteria::SEARCH->value,
					'id' => OpenSearchResponse::PLACEHOLDER_SEARCH_TERMS
				])
			);
			return $resp;
		} catch (Exception $e) {
			$this->logger->log(LogLevel::ERROR, 'Exception in '.__FUNCTION__, [ 'exception' => $e ]);
			return (new Response())->setStatus(Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function bookData(string $id, string $type): Response {
		try {
			$libPath = $this->settings->getLibraryFolder();
			if (is_null($libPath)) {
				return (new Response())->setStatus(Http::STATUS_NOT_FOUND);
			}
			$lib = $this->calibre->getDatabase($libPath);
			$format = CalibreBookFormat::getByBookAndType($lib, $id, $type);
			if (is_null($format)) {
				return (new Response())->setStatus(Http::STATUS_NOT_FOUND);
			}
			$file = $format->getDataFile($libPath);
			if (is_null($file)) {
				return (new Response())->setStatus(Http::STATUS_NOT_FOUND);
			}
			return (new StreamResponse($file->fopen('r')))->addHeader('Content-Type', MimeTypes::getMimeType($type));
	
		} catch (Exception $e) {
			$this->logger->log(LogLevel::ERROR, 'Exception in '.__FUNCTION__, [ 'exception' => $e ]);
			return (new Response())->setStatus(Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function bookCover(string $id): Response {
		try {
			$libPath = $this->settings->getLibraryFolder();
			if (is_null($libPath)) {
				return (new Response())->setStatus(Http::STATUS_NOT_FOUND);
			}
			$lib = $this->calibre->getDatabase($libPath);
			$book = CalibreBook::getById($lib, $id);
			if (is_null($book)) {
				return (new Response())->setStatus(Http::STATUS_NOT_FOUND);
			}
			$file = $book->getCoverFile($libPath);
			if (is_null($file)) {
				return (new Response())->setStatus(Http::STATUS_NOT_FOUND);
			}
			return (new StreamResponse($file->fopen('r')))->addHeader('Content-Type', MimeTypes::getMimeType('jpg'));
	
		} catch (Exception $e) {
			$this->logger->log(LogLevel::ERROR, 'Exception in '.__FUNCTION__, [ 'exception' => $e ]);
			return (new Response())->setStatus(Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}
}

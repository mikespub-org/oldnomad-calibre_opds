<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\FeedBuilder;

use DateTimeInterface;
use Exception;
use OCA\Calibre2OPDS\Calibre\CalibreItem;
use OCA\Calibre2OPDS\Calibre\Types\CalibreAuthor;
use OCA\Calibre2OPDS\Calibre\Types\CalibreAuthorPrefix;
use OCA\Calibre2OPDS\Calibre\Types\CalibreBookCriteria;
use OCA\Calibre2OPDS\Calibre\Types\CalibreBookFormat;
use OCA\Calibre2OPDS\Calibre\Types\CalibreBookId;
use OCA\Calibre2OPDS\Calibre\Types\CalibreLanguage;
use OCA\Calibre2OPDS\Calibre\Types\CalibrePublisher;
use OCA\Calibre2OPDS\Calibre\Types\CalibreSeries;
use OCA\Calibre2OPDS\Calibre\Types\CalibreTag;
use OCA\Calibre2OPDS\Opds\OpdsApp;
use OCA\Calibre2OPDS\Opds\OpdsAttribute;
use OCA\Calibre2OPDS\Opds\OpdsAuthor;
use OCA\Calibre2OPDS\Opds\OpdsCategory;
use OCA\Calibre2OPDS\Opds\OpdsEntry;
use OCA\Calibre2OPDS\Opds\OpdsLink;
use OCA\Calibre2OPDS\Opds\OpdsResponse;
use OCA\Calibre2OPDS\Opds\OpenSearchResponse;
use OCA\Calibre2OPDS\Service\ISettingsService;
use OCA\Calibre2OPDS\Util\MimeTypes;
use OCP\IL10N;

class OpdsFeedBuilder implements IOpdsFeedBuilder {
	private OpdsResponse $response;

	public function __construct(private ISettingsService $settings, private IL10N $l,
		string $selfRoute, array $selfParams, string $title, ?string $upRoute, array $upParams) {
		unset($selfParams['_route']);
		$id = $selfRoute;
		/** @var scalar $value */
		foreach ($selfParams as $key => $value) {
			$id .= ':'.$key.'='.$value;
		}
		$app = new OpdsApp($this->settings->getAppId(), $this->settings->getAppName(), $this->settings->getAppVersion(), $this->settings->getAppWebsite());
		$this->response = new OpdsResponse($app, $id, $title, $this->settings->getAppImageLink('icon.ico'));
		$this->response->addLink($this->getRouteLink('start', null, 'index'));
		$this->response->addLink($this->getRouteLink('search', OpenSearchResponse::MIME_TYPE_OPENSEARCH, 'search_xml'));
		$this->response->addLink($this->getRouteLink('self', null, $selfRoute, $selfParams));
		if (!is_null($upRoute)) {
			$this->response->addLink($this->getRouteLink('up', null, $upRoute, $upParams));
		}
	}

	private function getRouteLink(string $rel, ?string $mimeType, string $route, array $parameters = []): OpdsLink {
		return new OpdsLink($rel, $this->settings->getAppRouteLink($route, $parameters), $mimeType ?? OpdsResponse::MIME_TYPE_ATOM);
	}

	public function addSubsectionItem(string $id, string $route, string $title, ?string $summary): self {
		$this->response->addEntry((new OpdsEntry($id, $title, $summary))->addLink($this->getRouteLink('subsection', null, $route)));
		return $this;
	}

	public function addNavigationEntry(CalibreItem $item): self {
		/**
		 * @var string|int $item->id
		 * @var string $item->name
		 * @var int $item->count
		 */
		$rel = 'subsection';
		/** @var ?CalibreBookCriteria */
		$criterion = $item::CRITERION;
		if (is_null($criterion)) {
			if (!($item instanceof CalibreAuthorPrefix)) {
				throw new Exception('invalid navigation item call with class '.get_class($item));
			}
			$routeName = 'authors';
			$routeArgs = [ 'prefix' => $item->prefix ];
			$summary = $this->l->t('Authors: %1$d', [$item->count]);
		} else {
			if ($criterion === CalibreBookCriteria::LANGUAGE) {
				/** @var string $item->code */
				$item->setName($this->settings->getLanguageName($item->code));
			}
			$routeName = 'books';
			$routeArgs = [ 'criterion' => $criterion->value, 'id' => $item->id ];
			$summary = $this->l->t('Books: %1$d', [$item->count]);
		}
		/** @var string */
		$uriPrefix = $item::URI;
		$this->response->addEntry((new OpdsEntry($uriPrefix.':'.$item->id, $item->name, $summary))->addLink(
			$this->getRouteLink($rel, null, $routeName, $routeArgs)
		));
		return $this;
	}

	public function addBookEntry(CalibreItem $item): self {
		/**
		 * @var int $item->id
		 * @var string $item->title
		 * @var ?string $item->comment
		 * @var ?DateTimeInterface $item->last_modified
		 * @var ?DateTimeInterface $item->pubdate
		 * @var ?DateTimeInterface $item->timestamp
		 * @var ?string $item->uuid
		 */
		$entry = new OpdsEntry('book:'.$item->id, $item->title, $item->comment);
		$entry->setUpdated($item->last_modified);
		if (!is_null($item->pubdate)) {
			$entry->addAttribute(new OpdsAttribute('dc', 'issued', $item->pubdate));
		}
		if (!is_null($item->timestamp)) {
			$entry->addAttribute(new OpdsAttribute(null, 'published', $item->timestamp));
		}
		if (!is_null($item->uuid) && $item->uuid !== '') {
			$entry->addAttribute(new OpdsAttribute('dc', 'identifier', 'urn:uuid:'.$item->uuid));
		}
		/** @var CalibreBookId $ident */
		foreach ($item->identifiers as $ident) {
			/**
			 * @var string $ident->type
			 * @var string $ident->value
			 */
			if (in_array($ident->type, OpdsResponse::LITERAL_IDENTIFIER_TYPES)) {
				$value = $ident->value;
			} else {
				$value = 'urn:'.$ident->type.':'.$ident->value;
			}
			$entry->addAttribute(new OpdsAttribute('dc', 'identifier', $value));
		}
		/** @var CalibreAuthor $author */
		foreach ($item->authors as $author) {
			/**
			 * @var string $author->name
			 * @var ?string $author->uri
			 */
			$entry->addAuthor(new OpdsAuthor($author->name, $author->uri));
		}
		/** @var CalibrePublisher $publisher */
		foreach ($item->publishers as $publisher) {
			/** @var string $publisher->name */
			$entry->addAttribute(new OpdsAttribute('dc', 'publisher', $publisher->name));
		}
		/** @var CalibreLanguage $lang */
		foreach ($item->languages as $lang) {
			/** @var string $lang->code */
			$entry->addAttribute(new OpdsAttribute('dc', 'language', $lang->code));
		}
		/** @var CalibreSeries $series */
		foreach ($item->series as $series) {
			/** @var string $series->id */
			$seriesUrl = $this->settings->getAppRouteLink('books', [ 'criterion' => 'series', 'id' => $series->id ]);
			$entry->addAttribute(new OpdsAttribute('dc', 'isPartOf', $seriesUrl));
		}
		// TODO: series, series_index
		/** @var CalibreTag $tag */
		foreach ($item->tags as $tag) {
			/** @var string $tag->name */
			$entry->addCategory(new OpdsCategory($tag->name));
		}
		if ($item->has_cover) {
			$entry->addLink($this->getRouteLink(
				'http://opds-spec.org/image',
				MimeTypes::getMimeType('jpg'),
				'book_cover', [ 'id' => $item->id ]
			));
		}
		/** @var CalibreBookFormat $fmt */
		foreach ($item->formats as $fmt) {
			/** @var string $fmt->format */
			$format = $fmt->format;
			$entry->addLink($this->getRouteLink(
				'http://opds-spec.org/acquisition',
				MimeTypes::getMimeType($format),
				'book_data', [ 'id' => $item->id, 'type' => $format ]
			));
		}
		$this->response->addEntry($entry);
		return $this;
	}

	public function getResponse(): OpdsResponse {
		return $this->response;
	}
}

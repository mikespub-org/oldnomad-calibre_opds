<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\Opds;

use OCP\AppFramework\Http\Response;
use XMLWriter;

/**
 * Implementation of OpenSearch descriptor response for Nextcloud.
 *
 * @template-extends Response<int, array<string, mixed>>
 */
class OpenSearchResponse extends Response {
	/**
	 * OpenSearch MIME type.
	 */
	public const MIME_TYPE_OPENSEARCH = 'application/opensearchdescription+xml';
	/**
	 * Placeholder for search terms.
	 */
	public const PLACEHOLDER_SEARCH_TERMS = '{searchTerms}';

	/**
	 * Construct an instance.
	 *
	 * @param string $shortName short search name (up to 16 characters).
	 * @param string|null $longName long search name, or `null` if not defined.
	 * @param string $description search description.
	 * @param string|null $icon search icon URL, or `null` if not defined.
	 * @param string $template search URL template.
	 */
	public function __construct(
		private string $shortName,
		private ?string $longName,
		private string $description,
		private ?string $icon,
		private string $template,
	) {
		parent::__construct();
	}

	public function render(): string {
		$xml = new XMLWriter();
		$xml->openMemory();
		$xml->setIndent(true);
		$xml->startDocument('1.0', 'utf-8');
		$xml->startElementNs(null, 'OpenSearchDescription', 'http://a9.com/-/spec/opensearch/1.1/');
		$xml->writeElement('ShortName', $this->shortName);
		if (!is_null($this->longName)) {
			$xml->writeElement('LongName', $this->longName);
		}
		$xml->writeElement('Description', $this->description);
		if (!is_null($this->icon)) {
			$xml->writeElement('Image', $this->icon);
		}
		$xml->startElement('Url');
		$xml->writeAttribute('type', OpdsResponse::MIME_TYPE_ATOM);
		$xml->writeAttribute('template', $this->template);
		$xml->endElement(); // </Url>
		$xml->endElement(); // </OpenSearchDescription>
		$xml->endDocument();
		return $xml->outputMemory();
	}
}

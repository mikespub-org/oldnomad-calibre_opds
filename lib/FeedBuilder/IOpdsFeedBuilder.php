<?php

declare(strict_types=1);

namespace OCA\Calibre2OPDS\FeedBuilder;

use OCA\Calibre2OPDS\Calibre\CalibreItem;
use OCA\Calibre2OPDS\Opds\OpdsResponse;

/**
 * Interface for OPDS feed builder.
 */
interface IOpdsFeedBuilder {
	/**
	 * Add subsection feed item.
	 *
	 * @param string $id item ID.
	 * @param string $route route to which this item leads.
	 * @param string $title item title.
	 * @param string|null $summary optional item summary.
	 *
	 * @return self
	 */
	public function addSubsectionItem(string $id, string $route, string $title, ?string $summary): self;

	/**
	 * Add navigation feed item.
	 *
	 * @param CalibreItem $item Calibre metadata item to use for this item.
	 *
	 * @return self
	 */
	public function addNavigationEntry(CalibreItem $item): self;

	/**
	 * Add book acquisition feed item.
	 *
	 * @param CalibreItem $item Calibre book item to use for this item.
	 *
	 * @return self
	 */
	public function addBookEntry(CalibreItem $item): self;

	/**
	 * Get collected response.
	 *
	 * @return OpdsResponse OPDS feed response.
	 */
	public function getResponse(): OpdsResponse;
}

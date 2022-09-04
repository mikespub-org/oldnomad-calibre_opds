<?php

declare(strict_types=1);

namespace OCA\Calibre2OPDS\Opds;

/**
 * Container for Atom link tag.
 */
class OpdsLink {
	/**
	 * Construct an instance.
	 *
	 * @param string $rel link relation.
	 * @param string $url link URL.
	 * @param string $mimeType link MIME type.
	 */
	public function __construct(private string $rel, private string $url, private string $mimeType) {
	}

	/**
	 * Get link relation.
	 *
	 * @return string link relation.
	 */
	public function getRel(): string {
		return $this->rel;
	}

	/**
	 * Get link URL.
	 *
	 * @return string link URL.
	 */
	public function getURL(): string {
		return $this->url;
	}

	/**
	 * Get link MIME type.
	 *
	 * @return string link MIME type.
	 */
	public function getMimeType(): string {
		return $this->mimeType;
	}
}

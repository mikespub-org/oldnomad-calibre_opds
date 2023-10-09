<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\Opds;

/**
 * Container for Atom author object.
 */
class OpdsAuthor {
	/**
	 * Construct an instance.
	 *
	 * @param string $name author name.
	 * @param string|null $uri author URI, or `null` if not known.
	 * @param string|null $email author e-mail, or `null` if not known.
	 */
	public function __construct(private string $name, private ?string $uri = null, private ?string $email = null) {
	}

	/**
	 * Get author name.
	 *
	 * @return string author name.
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Get author URI.
	 *
	 * @return string|null author URI, or `null` if not known.
	 */
	public function getURI(): ?string {
		return $this->uri;
	}

	/**
	 * Get author e-mail.
	 *
	 * @return string|null author e-mail, or `null` if not known.
	 */
	public function getEMail(): ?string {
		return $this->email;
	}
}

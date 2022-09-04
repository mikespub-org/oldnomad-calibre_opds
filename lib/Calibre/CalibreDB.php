<?php

declare(strict_types=1);

namespace OCA\Calibre2OPDS\Calibre;

use OCP\Files\FileInfo;
use OCP\Files\Folder;
use OCP\Files\NotFoundException;
use PDO;
use PDOException;
use Traversable;

/**
 * Class for Calibre database.
 */
class CalibreDB implements ICalibreDB {
	/**
	 * Calibre database name.
	 *
	 * @var string
	 */
	private const METADATA_DB = 'metadata.db';

	/**
	 * Database PDO interface.
	 */
	private PDO $database;

	/**
	 * Construct an instance of Calibre database.
	 *
	 * @param string $dsn DSN for Calibre database (without `sqlite:` prefix).
	 * @param bool $readOnly flag to open database as read-only.
	 *
	 * @throws PDOException on failure.
	 */
	private function __construct(string $dsn, bool $readOnly) {
		$attr = [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ];
		if ($readOnly) {
			$attr[PDO::SQLITE_ATTR_OPEN_FLAGS] = PDO::SQLITE_OPEN_READONLY;
		}
		$this->database = new PDO('sqlite:'.$dsn, null, null, $attr);
		if (!$readOnly) {
			// Following functions are used by some triggers
			/** @psalm-suppress TooManyArguments -- Psalm is mistaken about PDO::sqliteCreateFunction() (has 4 args since 7.1.4) */
			$this->database->sqliteCreateFunction('title_sort', function (string $name): string {
				return preg_replace('/^(A|The|An)\s+(.*)$/i', '${2}, ${1}', $name, 1);
			}, 1, PDO::SQLITE_DETERMINISTIC);
			$this->database->sqliteCreateFunction('uuid4', function (): string {
				$data = random_bytes(16);
				$data[6] = chr(ord($data[6]) & 0x0f | 0x40);
				$data[8] = chr(ord($data[8]) & 0x3f | 0x80);
				return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
			}, 0);
		}
	}

	/**
	 * Open database for a Calibre library.
	 *
	 * @param Folder $root library root folder.
	 * @param bool $readOnly flag to open database as read-only.
	 *
	 * @return ICalibreDB database object.
	 *
	 * @throws NotFoundException if database file is not found.
	 * @throws PDOException on database failure.
	 */
	public static function fromFolder(Folder $root, bool $readOnly = true): ICalibreDB {
		$meta = $root->get(self::METADATA_DB);
		if (!$meta->isReadable() || $meta->getType() !== FileInfo::TYPE_FILE) {
			throw new NotFoundException('Library metadata is not a readable file');
		}
		$meta_local = $meta->getStorage()->getLocalFile($meta->getInternalPath());
		if (!is_string($meta_local)) {
			throw new NotFoundException('Library metadata cannot be found');
		}
		return new self($meta_local, $readOnly);
	}

	/**
	 * Get database PDO interface.
	 *
	 * This method should be used only for tests.
	 *
	 * @return PDO PDO interface.
	 */
	public function getDatabase(): PDO {
		return $this->database;
	}

	/**
	 * Query the database for a list of result rows.
	 *
	 * @param string $sql SQL statement.
	 * @param array $parameters SQL statement parameters.
	 *
	 * @return Traversable<array> list of result rows (associative arrays).
	 * @throws PDOException on failure.
	 */
	public function query(string $sql, array $parameters = []): Traversable {
		$stmt = $this->database->prepare($sql);
		if (!$stmt->execute($parameters)) {
			throw new PDOException('execute failure');
		}
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		return $stmt;
	}

	/**
	 * Query the database for a single result row.
	 *
	 * @param string $sql SQL statement.
	 * @param array $parameters SQL statement parameters.
	 *
	 * @return array<string,mixed>|null result row (associative array), or `null` if not found.
	 * @throws PDOException on failure.
	 */
	public function querySingle(string $sql, array $parameters = []): ?array {
		$stmt = $this->database->prepare($sql);
		if (!$stmt->execute($parameters)) {
			throw new PDOException('execute failure');
		}
		/** @var false|array<string,mixed> */
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return ($row === false) ? null : $row;
	}
}

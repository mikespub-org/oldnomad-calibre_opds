-- SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
-- SPDX-License-Identifier: CC0-1.0
BEGIN TRANSACTION;

INSERT INTO books (id, title, pubdate, path, has_cover, series_index, timestamp, last_modified) VALUES
	(11, 'The Theory and Practice of Oligarchical Collectivism', '1984-11-07', 'oligarchical_collectivism', 0,      1.0, '',                 '1949-06-08'),
	(12, 'Cicero for Dummies',                                   '2012-12-12', 'dummies_cicero',            1, 100500.0, '2022-02-24 04:00', '2023-09-30 17:18'),
	(13, 'Whores of Eroticon 6',                                 '1978-03-08', 'whores_eroticon6',          1,      1.0, '0101-01-01 00:00', '2001-05-11 00:00'),
	(14, 'Plato for Dummies',                                    '2011-11-11', 'dummies_plato',             1, 100499.0, '',                 '2023-09-30 17:18');
INSERT INTO comments (id, book, text) VALUES
	(21, 12, 'Simple explanation of Cicero for imbeciles.');
INSERT INTO identifiers (id, book, type, val) VALUES
	(31, 12, 'isbn', '978-0140440997');
INSERT INTO data (id, book, format, uncompressed_size, name) VALUES
	(41, 12, 'EPUB', 123456, 'cicero_for_dummies'),
	(42, 12, 'FB2',  456789, 'cicero_for_dummies');

INSERT INTO authors (id, name, sort, link) VALUES
	(51, 'Aaron Zeroth',       'Zeroth, Aaron',        ''),
	(52, 'Beth Wildgoose',     'Wildgoose, Beth',      'http://example.com/'),
	(53, 'Emmanuel Goldstein', 'Goldstein, Emmanuel',  ''),
	(54, 'Conrad Trachtenberg','Trachtenberg, Conrad', '');
INSERT INTO books_authors_link (id, book, author) VALUES
	(61, 11, 53),
	(62, 12, 52),
	(63, 12, 54),
	(64, 13, 51),
	(65, 14, 52);

INSERT INTO languages (id, lang_code) VALUES
	(71, 'en'),
	(72, 'ru'),
	(73, 'uk'),
	(74, 'enm'),
	(75, 'la');
INSERT INTO books_languages_link (id, book, lang_code) VALUES
	(81, 11, 71),
	(82, 12, 71),
	(83, 12, 75);

INSERT INTO publishers (id, name) VALUES
	(91, 'Megadodo Publications'),
	(92, 'Big Brother Books');
INSERT INTO books_publishers_link (id, book, publisher) VALUES
	(101, 11, 92),
	(102, 13, 91);

INSERT INTO series (id, name) VALUES
	(111, 'Philosophy For Dummies');
INSERT INTO books_series_link (id, book, series) VALUES
	(122, 12, 111),
	(123, 14, 111);

INSERT INTO tags (id, name) VALUES
	(131, 'Political theory'),
	(132, 'Translations');
INSERT INTO books_tags_link (id, book, tag) VALUES
	(141, 11, 131),
	(142, 12, 131),
	(143, 12, 132);

COMMIT;

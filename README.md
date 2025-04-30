<!--
SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
SPDX-License-Identifier:  CC0-1.0
-->
# Calibre2OPDS app

[![pipeline status](https://gitlab.com/oldnomad/calibre_opds/badges/master/pipeline.svg)](https://gitlab.com/oldnomad/calibre_opds/-/commits/master) [![coverage report](https://gitlab.com/oldnomad/calibre_opds/badges/master/coverage.svg)](https://gitlab.com/oldnomad/calibre_opds/-/commits/master) [![Latest Release](https://gitlab.com/oldnomad/calibre_opds/-/badges/release.svg)](https://gitlab.com/oldnomad/calibre_opds/-/releases)


The Calibre2OPDS app provides access to user's [Calibre](https://calibre-ebook.com/) library
stored in Nextcloud via [OPDS](https://specs.opds.io/opds-1.2).

[OpenSearch](https://github.com/dewitt/opensearch) is supported for searching in the library.

The source code is [available on GitLab](https://gitlab.com/oldnomad/calibre_opds/).

## About this fork

This fork was created to support nextcloud version 31 with a simple update of appinfo and composer - no code changes for upgrading from 26 to 31.

The upstream repo [oldnomad/calibre_opds](https://github.com/oldnomad/calibre_opds) has now been updated to support nextcloud versions 29 to 32, so please use that instead...

## Usage

This app is intended to be used in situation where you are storing your whole
Calibre library directory in your Nextcloud instance.

The app exposes Calibre library contents as OPDS feeds. If your Nextcloud is at URL
`https://example.com/index.php`, then the root OPDS feed is available at URL
`https://example.com/index.php/apps/calibre_opds/`. Note that accessing your OPDS feed
requires authentication in Nextcloud.

Correspondence between Calibre metadata and OPDS fields is described in [a separate document](OPDS.md).

### Settings

This app has no administrator settings.

Personal settings for this app are in settings section "Sharing". The only parameter that
can be modified at the moment is path to Calibre library folder (by default `Books`).

## Installation

Since this app is not in the Nextcloud App Store yet, you'll have to install it manually:

First, clone this repository:

```sh
git clone https://gitlab.com/oldnomad/calibre_opds.git
```

Then run Composer and create a tarball:

```sh
composer update --no-dev
make appstore
```

The tarball is created in subdirectory `build/artifacts/appstore`. Copy it to your Nextcloud
instance and unpack into your apps directory (typically, subdirectory `apps` under your Nextcloud
root directory).

Now you can go to your Nextcloud apps manager and enable this app.

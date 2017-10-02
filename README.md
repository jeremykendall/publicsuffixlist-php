# Public Suffix List: PHP

## EXPERIMENTAL - DO NOT USE - NOT INTENDED FOR RELEASE

**Public Suffix List: PHP** is a [Public Suffix List](http://publicsuffix.org/) based
domain parser implemented in PHP.

[![Build Status](https://travis-ci.org/jeremykendall/publicsuffixlist-php.png?branch=master)](https://travis-ci.org/jeremykendall/publicsuffixlist-php)
[![Total Downloads](https://poser.pugx.org/jeremykendall/publicsuffixlist-php/downloads.png)](https://packagist.org/packages/jeremykendall/publicsuffixlist-php)
[![Latest Stable Version](https://poser.pugx.org/jeremykendall/publicsuffixlist-php/v/stable.png)](https://packagist.org/packages/jeremykendall/publicsuffixlist-php)

## Motivation

While there are plenty of excellent URL parsers and builders available, there
are very few projects that can accurately parse a domain into its component
registrable domain and public suffix parts.

Consider the domain www.pref.okinawa.jp.  In this domain, the
*public suffix* portion is **okinawa.jp** and the *registrable domain* is
**pref.okinawa.jp**. You can't regex that.

## Installation

The only supported method of installation is via [Composer](http://getcomposer.org).

```
composer require jeremykendall/publicsuffixlist-php
```

## Usage

### Refreshing the Public Suffix List

While a cached PHP copy of the Public Suffix List is provided for you in the
`data` directory, that copy may or may not be up to date (Mozilla provides an
[Atom change feed](http://hg.mozilla.org/mozilla-central/atom-log/default/netwerk/dns/effective_tld_names.dat)
to keep up with changes to the list). Please use the provided vendor binary to
refresh your cached copy of the Public Suffix List.

From the root of your project, simply call:

``` bash
$ ./vendor/bin/update-psl
```

You may verify the update by checking the timestamp on the files located in the
`data` directory.

**Important**: The vendor binary `update-psl` depends on an internet connection to
update the cached Public Suffix List.

## Attribution

The HTTP adapter interface and the cURL HTTP adapter were inspired by (er,
lifted from) Will Durand's excellent
[Geocoder](https://github.com/willdurand/Geocoder) project.  His MIT license and
copyright notice are below.

```
Copyright (c) 2011-2013 William Durand <william.durand1@gmail.com>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is furnished
to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
```

Portions of the PublicSuffixListManager are derivative works of the PHP
[registered-domain-libs](https://github.com/usrflo/registered-domain-libs).
Those parts of this codebase are heavily commented, and I've included a copy of
the Apache Software Foundation License 2.0 in this project.

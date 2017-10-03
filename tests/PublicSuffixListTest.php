<?php

declare(strict_types=1);

/**
 * Public Suffix List PHP: Public Suffix List based URL parsing.
 *
 * @see http://github.com/jeremykendall/publicsuffixlist-php for the canonical source repository
 *
 * @copyright Copyright (c) 2017 Jeremy Kendall (http://jeremykendall.net)
 * @license   http://github.com/jeremykendall/publicsuffixlist-php/blob/master/LICENSE MIT License
 */
namespace Psl\Tests;

use PHPUnit\Framework\TestCase;
use Psl\Domain;
use Psl\MatchedDomain;
use Psl\NullDomain;
use Psl\PublicSuffixList;
use Psl\UnmatchedDomain;

class PublicSuffixListTest extends TestCase
{
    /**
     * @var PublicSuffixList
     */
    private $list;

    protected function setUp()
    {
        parent::setUp();
        $this->list = new PublicSuffixList();
    }

    public function testNullWillReturnNullDomain()
    {
        $domain = $this->list->query('COM');
        $this->assertFalse($domain->isValid());
        $this->assertInstanceOf(NullDomain::class, $domain);
        $this->assertInstanceOf(Domain::class, $domain);
        $this->assertNull($domain->getDomain());
    }

    public function testIsSuffixValidFalse()
    {
        $domain = $this->list->query('www.example.faketld');
        $this->assertFalse($domain->isValid());
        $this->assertInstanceOf(Domain::class, $domain);
        $this->assertInstanceOf(UnmatchedDomain::class, $domain);
    }

    public function testIsSuffixValidTrue()
    {
        $domain = $this->list->query('www.example.com');
        $this->assertTrue($domain->isValid());
        $this->assertInstanceOf(Domain::class, $domain);
        $this->assertInstanceOf(MatchedDomain::class, $domain);
    }

    /**
     * @dataProvider parseDataProvider
     */
    public function testGetRegistrableDomain($publicSuffix, $registrableDomain, $domain, $expectedDomain)
    {
        $this->assertSame($registrableDomain, $this->list->query($domain)->getRegistrableDomain());
    }

    /**
     * @dataProvider parseDataProvider
     */
    public function testGetPublicSuffix($publicSuffix, $registrableDomain, $domain, $expectedDomain)
    {
        $this->assertSame($publicSuffix, $this->list->query($domain)->getPublicSuffix());
    }

    /**
     * @dataProvider parseDataProvider
     */
    public function testGetDomain($publicSuffix, $registrableDomain, $domain, $expectedDomain)
    {
        $this->assertSame($expectedDomain, $this->list->query($domain)->getDomain());
    }

    public function parseDataProvider()
    {
        return [
            // public suffix, registrable domain, domain
            // BEGIN https://github.com/jeremykendall/php-domain-parser/issues/16
            'com tld' => ['com', 'example.com', 'us.example.com', 'us.example.com'],
            'na tld' => ['na', 'example.na', 'us.example.na', 'us.example.na'],
            'us.na tld' => ['us.na', 'example.us.na', 'www.example.us.na', 'www.example.us.na'],
            'org tld' => ['org', 'example.org', 'us.example.org', 'us.example.org'],
            'biz tld (1)' => ['biz', 'broken.biz', 'webhop.broken.biz', 'webhop.broken.biz'],
            'biz tld (2)' => ['webhop.biz', 'broken.webhop.biz', 'www.broken.webhop.biz', 'www.broken.webhop.biz'],
            // END https://github.com/jeremykendall/php-domain-parser/issues/16
            // Test ipv6 URL
            'IP (1)' => [null, null, '[::1]', null],
            'IP (2)' => [null, null, '[2001:db8:85a3:8d3:1319:8a2e:370:7348]', null],
            'IP (3)' => [null, null, '[2001:db8:85a3:8d3:1319:8a2e:370:7348]', null],
            // Test IP address: Fixes #43
            'IP (4)' => [null, null, '192.168.1.2', null],
            // Link-local addresses and zone indices
            'IP (5)' => [null, null, '[fe80::3%25eth0]', null],
            'IP (6)' => [null, null, '[fe80::1%2511]', null],
            'fake tld' => ['faketld', 'example.faketld', 'example.faketld', 'example.faketld'],
        ];
    }

    public function testGetPublicSuffixHandlesWrongCaseProperly()
    {
        $publicSuffix = 'рф';
        $domain = 'Яндекс.РФ';

        $this->assertSame($publicSuffix, $this->list->query($domain)->getPublicSuffix());
    }

    /**
     * @dataProvider publicSuffixSpecProvider
     * @param  string      $domain
     * @param  string|null $expected
     */
    public function testPublicSuffixSpecification($domain, $expected)
    {
        $this->checkPublicSuffix($domain, $expected);
    }

    /**
     * Checks PublicSuffixList can return proper registrable domain.
     *
     * "You will need to define a checkPublicSuffix() function which takes as a
     * parameter a domain name and the public suffix, runs your implementation
     * on the domain name and checks the result is the public suffix expected."
     *
     * @see http://publicsuffix.org/list/
     *
     * @param string $domain
     * @param string $expected
     */
    private function checkPublicSuffix($domain, $expected)
    {
        $this->assertSame($expected, $this->list->query($domain)->getRegistrableDomain());
    }

    public function publicSuffixSpecProvider()
    {
        // Test data from Rob Stradling at Comodo
        // http://mxr.mozilla.org/mozilla-central/source/netwerk/test/unit/data/test_psl.txt?raw=1
        // Any copyright is dedicated to the Public Domain.
        // http://creativecommons.org/publicdomain/zero/1.0/

        return [
            'null' => [null, null],
            'single label' => ['COM', null],
            'mixed case (1)' => ['example.COM', 'example.com'],
            'mixed case (2)' => ['WWW.example.COM', 'example.com'],
            'leading dot (1)' => ['.com', null],
            'leading dot (2)' => ['.example', null],
            'leading dot (3)' => ['.example.com', null],
            'leading dot (4)' => ['.example.example', null],
            'unlisted TLD (1)' => ['example', null],
            'unlisted TLD (2)' => ['example.example', 'example.example'],
            'unlisted TLD (3)' => ['b.example.example', 'example.example'],
            'unlisted TLD (4)' => ['a.b.example.example', 'example.example'],
            'tld with 1 rule (1)' => ['biz', null],
            'tld with 1 rule (2)' => ['domain.biz', 'domain.biz'],
            'tld with 1 rule (3)' => ['a.domain.biz', 'domain.biz'],
            'tld with 1 rule (4)' => ['a.b.domain.biz', 'domain.biz'],
            'tld with some 2-level rules (1)' => ['com', null],
            'tld with some 2-level rules (2)' => ['example.com', 'example.com'],
            'tld with some 2-level rules (3)' => ['a.example.com', 'example.com'],
            'tld with some 2-level rules (4)' => ['a.b.example.com', 'example.com'],
            'tld with some 2-level rules (5)' => ['uk.com', null],
            'tld with some 2-level rules (6)' => ['example.uk.com', 'example.uk.com'],
            'tld with some 2-level rules (7)' => ['b.example.uk.com', 'example.uk.com'],
            'tld with some 2-level rules (8)' => ['a.b.example.uk.com', 'example.uk.com'],
            'tld with some 2-level rules (9)' => ['test.ac', 'test.ac'],
            'tld with only 1 (wildcard) rule (1)' => ['mm', null],
            'tld with only 1 (wildcard) rule (2)' => ['c.mm', null],
            'tld with only 1 (wildcard) rule (3)' => ['b.c.mm', 'b.c.mm'],
            'tld with only 1 (wildcard) rule (4)' => ['a.b.c.mm', 'b.c.mm'],
            'more complex tld (1)' => ['jp', null],
            'more complex tld (2)' => ['test.jp', 'test.jp'],
            'more complex tld (3)' => ['www.test.jp', 'test.jp'],
            'more complex tld (4)' => ['ac.jp', null],
            'more complex tld (5)' => ['test.ac.jp', 'test.ac.jp'],
            'more complex tld (6)' => ['www.test.ac.jp', 'test.ac.jp'],
            'more complex tld (7)' => ['kyoto.jp', null],
            'more complex tld (8)' => ['test.kyoto.jp', 'test.kyoto.jp'],
            'more complex tld (9)' => ['ide.kyoto.jp', null],
            'more complex tld (10)' => ['b.ide.kyoto.jp', 'b.ide.kyoto.jp'],
            'more complex tld (11)' => ['a.b.ide.kyoto.jp', 'b.ide.kyoto.jp'],
            'more complex tld (12)' => ['c.kobe.jp', null],
            'more complex tld (13)' => ['b.c.kobe.jp', 'b.c.kobe.jp'],
            'more complex tld (14)' => ['a.b.c.kobe.jp', 'b.c.kobe.jp'],
            'more complex tld (15)' => ['city.kobe.jp', 'city.kobe.jp'],
            'more complex tld (16)' => ['www.city.kobe.jp', 'city.kobe.jp'],
            'tld with a wildcard rule and exceptions (1)' => ['ck', null],
            'tld with a wildcard rule and exceptions (2)' => ['test.ck', null],
            'tld with a wildcard rule and exceptions (3)' => ['b.test.ck', 'b.test.ck'],
            'tld with a wildcard rule and exceptions (4)' => ['a.b.test.ck', 'b.test.ck'],
            'tld with a wildcard rule and exceptions (5)' => ['www.ck', 'www.ck'],
            'tld with a wildcard rule and exceptions (6)' => ['www.www.ck', 'www.ck'],
            'us k12 (1)' => ['us', null],
            'us k12 (2)' => ['test.us', 'test.us'],
            'us k12 (3)' => ['www.test.us', 'test.us'],
            'us k12 (4)' => ['ak.us', null],
            'us k12 (5)' => ['test.ak.us', 'test.ak.us'],
            'us k12 (6)' => ['www.test.ak.us', 'test.ak.us'],
            'us k12 (7)' => ['k12.ak.us', null],
            'us k12 (8)' => ['test.k12.ak.us', 'test.k12.ak.us'],
            'us k12 (9)' => ['www.test.k12.ak.us', 'test.k12.ak.us'],
            'idn labels (1)' => ['食狮.com.cn', '食狮.com.cn'],
            'idn labels (2)' => ['食狮.公司.cn', '食狮.公司.cn'],
            'idn labels (3)' => ['www.食狮.公司.cn', '食狮.公司.cn'],
            'idn labels (4)' => ['shishi.公司.cn', 'shishi.公司.cn'],
            'idn labels (5)' => ['公司.cn', null],
            'idn labels (6)' => ['食狮.中国', '食狮.中国'],
            'idn labels (7)' => ['www.食狮.中国', '食狮.中国'],
            'idn labels (8)' => ['shishi.中国', 'shishi.中国'],
            'idn labels (9)' => ['中国', null],
            'punycoded labels (1)' => ['xn--85x722f.com.cn', 'xn--85x722f.com.cn'],
            'punycoded labels (2)' => ['xn--85x722f.xn--55qx5d.cn', 'xn--85x722f.xn--55qx5d.cn'],
            'punycoded labels (3)' => ['www.xn--85x722f.xn--55qx5d.cn', 'xn--85x722f.xn--55qx5d.cn'],
            'punycoded labels (4)' => ['shishi.xn--55qx5d.cn', 'shishi.xn--55qx5d.cn'],
            'punycoded labels (5)' => ['xn--55qx5d.cn', null],
            'punycoded labels (6)' => ['xn--85x722f.xn--fiqs8s', 'xn--85x722f.xn--fiqs8s'],
            'punycoded labels (7)' => ['www.xn--85x722f.xn--fiqs8s', 'xn--85x722f.xn--fiqs8s'],
            'punycoded labels (8)' => ['shishi.xn--fiqs8s', 'shishi.xn--fiqs8s'],
            'punycoded labels (9)' => ['xn--fiqs8s', null],
        ];
    }
}

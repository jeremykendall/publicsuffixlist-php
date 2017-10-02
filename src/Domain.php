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
namespace Psl;

final class Domain
{
    use LabelsTrait;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var string
     */
    private $publicSuffix;

    /**
     * @var bool
     */
    private $isValid;

    public function __construct(string $domain = null, string $publicSuffix = null, bool $isValid = false)
    {
        $this->domain = $domain;
        $this->publicSuffix = $publicSuffix;
        $this->isValid = $isValid;
    }

    /**
     * @return string|null
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return string|null
     */
    public function getPublicSuffix()
    {
        return $this->publicSuffix;
    }

    /**
     * Does the domain have a matching rule in the Public Suffix List?
     *
     * WARNING: "Some people use the PSL to determine what is a valid domain name
     * and what isn't. This is dangerous, particularly in these days where new
     * gTLDs are arriving at a rapid pace, if your software does not regularly
     * receive PSL updates, because it will erroneously think new gTLDs are not
     * valid. The DNS is the proper source for this information. If you must use
     * it for this purpose, please do not bake static copies of the PSL into your
     * software with no update mechanism."
     *
     * @see https://publicsuffix.org/learn/
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * Get registrable domain.
     *
     * Algorithm #7: The registered or registrable domain is the public suffix
     * plus one additional label.
     *
     * This method should return null if the domain provided is a public suffix,
     * per the test cases provided by Mozilla.
     *
     * @see https://publicsuffix.org/list/
     * @see https://raw.githubusercontent.com/publicsuffix/list/master/tests/test_psl.txt
     *
     * @return string|null registrable domain
     */
    public function getRegistrableDomain()
    {
        if ($this->hasRegistrableDomain($this->publicSuffix) === false) {
            return null;
        }

        $publicSuffixLabels = $this->getLabels($this->publicSuffix);
        $domainLabels = $this->getLabels($this->domain);
        $additionalLabel = $this->getAdditionalLabel($domainLabels, $publicSuffixLabels);

        return implode('.', array_merge($additionalLabel, $publicSuffixLabels));
    }

    private function hasRegistrableDomain($publicSuffix): bool
    {
        return !($publicSuffix === null || $this->domain === $publicSuffix || !$this->hasLabels($this->domain));
    }

    private function getAdditionalLabel($domainLabels, $publicSuffixLabels): array
    {
        $additionalLabel = array_slice(
            $domainLabels,
            count($domainLabels) - count($publicSuffixLabels) - 1,
            1
        );

        return $additionalLabel;
    }
}

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

final class PublicSuffixList
{
    use LabelsTrait;

    /**
     * @var Rules
     */
    private $rules;

    /**
     * New instance
     * @param Rules|null $rules PSL rules
     */
    public function __construct(Rules $rules = null)
    {
        $this->rules = $rules ?? new Rules;
    }

    /**
     * Returns PSL public info for a given domain
     *
     * @param string|null $domain
     *
     * @return Domain
     */
    public function query(string $domain = null): Domain
    {
        if (!$this->isMatchable($domain)) {
            return new NullDomain();
        }

        list($publicSuffix, $isValid) = $this->rules->parse($domain);
        if (!$this->isPunycoded($domain) && null !== $publicSuffix) {
            $publicSuffix = idn_to_utf8($publicSuffix, 0, INTL_IDNA_VARIANT_UTS46);
        }

        if ($isValid) {
            return new MatchedDomain($domain, $publicSuffix, $isValid);
        }

        return new UnmatchedDomain($domain, $publicSuffix, $isValid);
    }

    /**
     * Tell whether the given domain is valid
     *
     * @param  string|null $domain
     *
     * @return bool
     */
    private function isMatchable($domain): bool
    {
        if ($domain === null) {
            return false;
        }

        if ($this->hasLeadingDot($domain)) {
            return false;
        }

        if ($this->isSingleLabelDomain($domain)) {
            return false;
        }

        if ($this->isIpAddress($domain)) {
            return false;
        }

        return true;
    }

    /**
     * Tell whether the submitted domain is an IP address
     *
     * @param string $domain
     *
     * @return bool
     */
    private function isIpAddress(string $domain): bool
    {
        return filter_var($domain, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Tell whether the submitted domain is punycoded
     *
     * @param string $domain
     *
     * @return bool
     */
    private function isPunycoded(string $domain): bool
    {
        return strpos($domain, 'xn--') !== false;
    }

    /**
     * Tell whether the submitted domain starts with a dot
     *
     * @param string $domain
     *
     * @return bool
     */
    private function hasLeadingDot($domain): bool
    {
        return strpos($domain, '.') === 0;
    }
}

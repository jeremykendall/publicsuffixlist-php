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

final class Rules
{
    use LabelsTrait;

    /**
     * @var array
     */
    private $rules;

    /**
     * New instance
     *
     * @param array $rules PSL ruling array
     */
    public function __construct(array $rules = null)
    {
        $this->rules = $rules ?? include dirname(__DIR__) . '/data/public-suffix-list.php';
    }

    /**
     * Returns the public suffix info determined from the PSL rules array
     *
     * The returned array is composed of two value:
     *
     * - the fist index contains the detected public suffix or null
     * - the second index tells whether the return public suffix is valid or not
     *
     * @param string $input
     *
     * @return array
     */
    public function parse(string $input): array
    {
        $domain = strtolower(idn_to_ascii($input, 0, INTL_IDNA_VARIANT_UTS46));
        $matchingLabels = $this->findMatchingLabels($this->getLabelsReverse($domain));
        if (empty($matchingLabels)) {
            $labels = $this->getLabels($input);

            return [array_pop($labels), false];
        }

        return [implode('.', array_filter($matchingLabels, 'strlen')), true];
    }

    /**
     * Returns the matchin labels according to the PSL rules
     *
     * @param array $labels
     *
     * @return string[]
     */
    private function findMatchingLabels(array $labels): array
    {
        $matches = [];
        $rules = $this->rules;
        foreach ($labels as $label) {
            if ($this->isExceptionRule($label, $rules)) {
                break;
            }

            if ($this->isWildcardRule($rules)) {
                array_unshift($matches, $label);
                break;
            }

            if ($this->matchExists($label, $rules)) {
                array_unshift($matches, $label);
                $rules = $rules[$label];
                continue;
            }

            // Avoids improper parsing when $domain's subdomain + public suffix ===
            // a valid public suffix (e.g. domain 'us.example.com' and public suffix 'us.com')
            //
            // Added by @goodhabit in https://github.com/jeremykendall/php-domain-parser/pull/15
            // Resolves https://github.com/jeremykendall/php-domain-parser/issues/16
            break;
        }

        return $matches;
    }

    /**
     * Tell whether a PSL exception rule is found
     *
     * @param  string $label
     * @param  array  $rules
     *
     * @return bool
     */
    private function isExceptionRule(string $label, array $rules): bool
    {
        return $this->matchExists($label, $rules)
            && array_key_exists('!', $rules[$label]);
    }

    /**
     * Tell whether a PSL wildcard rule is found
     *
     * @param array $rules
     *
     * @return bool
     */
    private function isWildcardRule(array $rules): bool
    {
        return array_key_exists('*', $rules);
    }

    /**
     * Tell whether a PSL rule is found for a given domain label
     *
     * @param string $label
     * @param array  $rules
     *
     * @return bool
     */
    private function matchExists(string $label, array $rules): bool
    {
        return array_key_exists($label, $rules);
    }
}

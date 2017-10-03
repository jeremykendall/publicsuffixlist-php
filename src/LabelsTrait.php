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

trait LabelsTrait
{
    /**
     * Returns domain labels
     *
     * @param string $input
     *
     * @return string[]
     */
    private function getLabels(string $input): array
    {
        return explode('.', $input);
    }

    /**
     * Returns domains labesl in reverse
     *
     * @param string $input
     *
     * @return string[]
     */
    private function getLabelsReverse(string $input): array
    {
        return array_reverse($this->getLabels($input));
    }

    /**
     * Tell whether the submit domain contains more than one label
     *
     * @param string $input
     *
     * @return bool
     */
    private function hasLabels(string $input): bool
    {
        return strpos($input, '.') !== false;
    }

    /**
     * Tell whether the submitted domain is composed of a single label
     *
     * @param string $input
     *
     * @return bool
     */
    private function isSingleLabelDomain(string $input): bool
    {
        return !$this->hasLabels($input);
    }
}

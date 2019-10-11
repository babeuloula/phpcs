<?php

declare(strict_types=1);

namespace BaBeuloula\PhpCS\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\{
    Files\File,
    Sniffs\Sniff
};

/**
 * Fork of https://github.com/steevanb/php-code-sniffs
 *
 * Force declare(strict_types=1)
 */
class StrictTypesSniff implements Sniff
{
    /** @var bool[] */
    protected $strictTypes = [];

    /** @return int[] */
    public function register(): array
    {
        return [T_DECLARE, T_NAMESPACE];
    }

    /** @param int $stackPtr */
    public function process(File $phpcsFile, $stackPtr): void
    {
        if (T_DECLARE === $phpcsFile->getTokens()[$stackPtr]['code']) {
            $this->strictTypes[$phpcsFile->getFilename()] = true;
        } elseif (T_NAMESPACE === $phpcsFile->getTokens()[$stackPtr]['code']
            && false === \array_key_exists($phpcsFile->getFilename(), $this->strictTypes)
        ) {
            $phpcsFile->addError(
                'File should have "declare(strict_types=1);" before namespace',
                $stackPtr,
                'StrictTypesRequired'
            );
        }
    }
}

<?php

/**
 * @author BaBeuloula
 * Fork of https://github.com/steevanb/php-code-sniffs
 */

declare(strict_types=1);

namespace BaBeuloula\PhpCS\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\{
    Files\File,
    Sniffs\Sniff
};

/**
 * Force declare(strict_types=1)
 */
class StrictTypesSniff implements Sniff
{
    /** @return int[] */
    public function register(): array
    {
        return [T_DECLARE, T_NAMESPACE];
    }

    /** @param int $stackPtr */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $currentPtr = $phpcsFile->getTokens()[$stackPtr];

        if ('T_NAMESPACE' === $currentPtr['type']) {
            $declarePtr = $phpcsFile->findPrevious(
                [T_DECLARE],
                $stackPtr,
                $phpcsFile->findPrevious([T_OPEN_TAG], $stackPtr)
            );

            if (false === \is_int($declarePtr)) {
                $isFixable = $phpcsFile->addFixableError(
                    'File should have "declare(strict_types=1);" before namespace',
                    $stackPtr,
                    'StrictTypesRequired'
                );

                if (true === $isFixable) {
                    $phpcsFile->fixer->beginChangeset();
                    $phpcsFile->fixer->replaceToken(
                        $stackPtr,
                        'declare(strict_types=1);' . $phpcsFile->eolChar . $currentPtr['content']
                    );
                    $phpcsFile->fixer->endChangeset();
                }
            }
        } else {
            $strictTypePtr = $phpcsFile->findNext(
                [T_STRING],
                $stackPtr,
                $phpcsFile->findNext([T_CLOSE_PARENTHESIS], $stackPtr)
            );

            $valuePtr = $phpcsFile->findNext(
                [T_LNUMBER],
                $stackPtr,
                $phpcsFile->findNext([T_CLOSE_PARENTHESIS], $stackPtr)
            );

            if (true === \is_int($strictTypePtr)
                && 'strict_types' === $phpcsFile->getTokens()[$strictTypePtr]['content']
                && true === \is_int($valuePtr)
                && '0' === $phpcsFile->getTokens()[$valuePtr]['content']
            ) {
                $isFixable = $phpcsFile->addFixableError(
                    '"strict_types" must be equals to 1, not 0.',
                    $valuePtr,
                    'BadValueStrictTypes'
                );

                if (true === $isFixable) {
                    $phpcsFile->fixer->beginChangeset();
                    $phpcsFile->fixer->replaceToken($valuePtr, '1');
                    $phpcsFile->fixer->endChangeset();
                }
            }
        }
    }
}

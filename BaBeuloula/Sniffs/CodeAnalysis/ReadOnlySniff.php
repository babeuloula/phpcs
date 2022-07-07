<?php

/**
 * @author BaBeuloula
 */

declare(strict_types=1);

namespace BaBeuloula\PhpCS\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\{
    Files\File,
    Sniffs\Sniff
};

/**
 * Reorder readonly
 */
class ReadOnlySniff implements Sniff
{
    protected const TOKEN_VISIBILITY = ['T_PUBLIC', 'T_PRIVATE', 'T_PROTECTED'];

    /** @return int[] */
    public function register(): array
    {
        return [T_READONLY];
    }

    /** @param int $stackPtr */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $previous = $phpcsFile->getTokens()[$stackPtr - 2];
        $next = $phpcsFile->getTokens()[$stackPtr + 2];

        if (true === \in_array($previous['type'], static::TOKEN_VISIBILITY, true)
            || 'T_STRING' === $next['type']
        ) {
            return;
        }

        $isFixable = $phpcsFile->addFixableError(
            'readonly must be after the property visibility.',
            $stackPtr + 2,
            'ReadOnlyAfterVisibility'
        );

        if (false === $isFixable) {
            return;
        }

        $phpcsFile->fixer->beginChangeset();
        $phpcsFile->fixer->replaceToken(
            $stackPtr,
            ''
        );
        $phpcsFile->fixer->replaceToken(
            $stackPtr + 1,
            ''
        );
        $phpcsFile->fixer->replaceToken(
            $stackPtr + 2,
            $this->getVisibility($next['type']) . ' readonly'
        );
        $phpcsFile->fixer->endChangeset();
    }

    protected function getVisibility(string $tokenVisibility): string
    {
        switch ($tokenVisibility) {
            case 'T_PUBLIC':
                return 'public';
            case 'T_PRIVATE':
                return 'private';
            case 'T_PROTECTED':
                return 'protected';
            default:
                throw new \InvalidArgumentException("Unknown visibility: $tokenVisibility.");
        }
    }
}

<?php

/**
 *
 * @fork https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/PSR12/Sniffs/Properties/ConstantVisibilitySniff.php
 *
 * Verifies that all class constants have their visibility set.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2019 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 */

declare(strict_types=1);

namespace BaBeuloula\PhpCS\Sniffs\Properties;

use PHP_CodeSniffer\{
    Files\File,
    Sniffs\Sniff,
    Util\Tokens
};

class ConstantVisibilitySniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_CONST];
    }


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in the
     *                                               stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Make sure this is a class constant.
        if ($phpcsFile->hasCondition($stackPtr, Tokens::$ooScopeTokens) === false) {
            return;
        }

        $prev = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($stackPtr - 1), null, true);
        // phpcs:ignore
        if (isset(Tokens::$scopeModifiers[$tokens[$prev]['code']]) === true) {
            return;
        }

        $error = 'Visibility must be declared on all constants if your project supports PHP 7.1 or later';
        $fix = $phpcsFile->addFixableWarning($error, $stackPtr, 'NotFound');

        if (true === $fix) {
            $phpcsFile->fixer->beginChangeset();
            $phpcsFile->fixer->replaceToken($stackPtr, 'public const');
            $phpcsFile->fixer->endChangeset();
        }
    }
}

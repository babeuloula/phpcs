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
 * Throw a warning if root-namespace functions does not have backslash before
 *
 * Ex: array_key_exists => \array_key_exists
 * @see https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/master/src/Fixer/FunctionNotation/NativeFunctionInvocationFixer.php#L358-L398
 */
class BackslashSniff implements Sniff
{
    /** @var string[] root-namespace functions to check */
    public $functions = [
        // @see https://github.com/php/php-src/blob/PHP-7.4/Zend/zend_compile.c "zend_try_compile_special_func"
        'array_key_exists',
        'array_slice',
        'assert',
        'boolval',
        'call_user_func',
        'call_user_func_array',
        'chr',
        'count',
        'defined',
        'doubleval',
        'floatval',
        'func_get_args',
        'func_num_args',
        'get_called_class',
        'get_class',
        'gettype',
        'in_array',
        'intval',
        'is_array',
        'is_bool',
        'is_double',
        'is_float',
        'is_int',
        'is_integer',
        'is_long',
        'is_null',
        'is_object',
        'is_real',
        'is_resource',
        'is_string',
        'ord',
        'strlen',
        'strval',
        // @see https://github.com/php/php-src/blob/php-7.2.6/ext/opcache/Optimizer/pass1_5.c
        'constant',
        'define',
        'dirname',
        'extension_loaded',
        'function_exists',
        'is_callable',
    ];

    public $excludeFunctions = [
        'Assert',
    ];

    /** @var string[] */
    protected $excludeType = [
        'T_NS_SEPARATOR',
        'T_OBJECT_OPERATOR',
        'T_OPEN_USE_GROUP',
        'T_USE',
        'T_DOUBLE_COLON',
    ];

    public function register()
    {
        return [T_STRING];
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        $originalPtr = $stackPtr;
        $current = $phpcsFile->getTokens()[$stackPtr];
        $functionName = $current['content'];

        $previousType = $phpcsFile->getTokens()[--$stackPtr]['type'];

        $prev = $phpcsFile->findPrevious([T_OPEN_CURLY_BRACKET], $stackPtr);
        $findPreviousPtr = $phpcsFile->findPrevious(
            [T_USE, T_OPEN_USE_GROUP],
            $stackPtr,
            (true === \is_int($prev)) ? $prev : null
        );
        $findPreviousType = "";

        if (true === \is_int($findPreviousPtr)) {
            $findPreviousType = $phpcsFile->getTokens()[$findPreviousPtr]['type'];
        }

        // If the function name is not on the functions array
        // Or if the previous token is on the excluded list
        if (false === \in_array(strtolower($functionName), $this->functions, true)
            || true === \in_array($functionName, $this->excludeFunctions, true)
            || true === \in_array($previousType, $this->excludeType, true)
            || true === \in_array($findPreviousType, $this->excludeType, true)
            || 'T_FUNCTION' === $phpcsFile->getTokens()[--$stackPtr]['type']
        ) {
            return;
        }

        $previousPtr = $phpcsFile->findPrevious(
            [T_NS_SEPARATOR],
            $stackPtr,
            $phpcsFile->findPrevious(
                [T_WHITESPACE, T_OPEN_PARENTHESIS],
                $stackPtr
            )
        );

        if (false === $previousPtr) {
            $isFixable = $phpcsFile->addFixableWarning(
                "For better performance, use \\$functionName instead of $functionName.",
                $originalPtr,
                'MissingBackSlash'
            );

            if (true === $isFixable) {
                $phpcsFile->fixer->beginChangeset();
                $phpcsFile->fixer->replaceToken($originalPtr, '\\' . $functionName);
                $phpcsFile->fixer->endChangeset();
            }
        }
    }
}

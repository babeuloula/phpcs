<?php

declare(strict_types=1);

namespace BaBeuloula\PhpCS\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\{
    Files\File,
    Sniffs\Sniff
};

/**
 * Throw a warning if root-namespace functions does not have backslash before
 *
 * Ex: array_keys => \array_keys
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

    public function register()
    {
        return [T_STRING];
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        $functionName = $phpcsFile->getTokens()[$stackPtr]['content'];

        if (false === \in_array(\strtolower($functionName), $this->functions, true)) {
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
            $phpcsFile->addWarning(
                "For better performance, use \\$functionName instead of $functionName.",
                $stackPtr,
                'MissingBackSlash'
            );
        }
    }
}

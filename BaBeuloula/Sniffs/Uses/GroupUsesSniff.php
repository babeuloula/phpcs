<?php

/**
 * @author BaBeuloula
 *
 * Fork of https://github.com/steevanb/php-code-sniffs
 */

declare(strict_types=1);

namespace BaBeuloula\PhpCS\Sniffs\Uses;

use PHP_CodeSniffer\{
    Files\File,
    Sniffs\Sniff
};

/**
 * Group use on 1st, 2nd or 3rd level
 * Example:
 * use App\{
 *     Entity\Foo
 *     Repository\FooRepository
 * };
 * use Symfony\Component\HttpFoundation\{
 *     Request,
 *     Response
 * };
 */
class GroupUsesSniff implements Sniff
{
    /** @var string[] */
    public $thirdLevelPrefixs = [
        'Symfony\\Component\\',
        'Symfony\\Bundle\\',
        'Sensio\\Bundle\\',
        'Doctrine\\Common\\',
    ];

    /** @var string[] */
    protected $uses = [];

    /** @return mixed[] */
    public function register(): array
    {
        return [T_USE, T_OPEN_USE_GROUP, T_CLOSE_USE_GROUP];
    }

    /** @param int $stackPtr */
    public function process(File $phpcsFile, $stackPtr): void
    {
        switch ($phpcsFile->getTokens()[$stackPtr]['type']) {
            case 'T_USE':
                $useGroupPrefix = $this->getUseGroupPrefix($phpcsFile, $stackPtr);

                if (true === \is_string($useGroupPrefix)) {
                    $this->validateUseGroupPrefixName($phpcsFile, $stackPtr, $useGroupPrefix);
                } else {
                    $currentUse = $this->getCurrentUse($phpcsFile, $stackPtr);

                    if (true === \is_string($currentUse)) {
                        $this->validateUse($phpcsFile, $stackPtr, $currentUse);
                    }
                }
                break;

            case 'T_OPEN_USE_GROUP':
                $comaPtr = $phpcsFile->findNext(
                    [T_COMMA],
                    $stackPtr,
                    $phpcsFile->findNext([T_CLOSE_USE_GROUP], ++$stackPtr)
                );
                $errorLines = [];

                while (true === \is_int($comaPtr)) {
                    $nextToken = $phpcsFile->getTokens()[++$comaPtr];

                    if ('T_WHITESPACE' === $nextToken['type']
                        && false === \strpos($nextToken['content'], "\n")
                        && false === \in_array($nextToken['line'], $errorLines)
                    ) {
                        $phpcsFile->addError(
                            'Only one use per line allowed.',
                            ++$comaPtr,
                            'OneUsePerLine'
                        );

                        $errorLines[] = $nextToken['line'];
                    }

                    $comaPtr = $phpcsFile->findNext(
                        [T_COMMA],
                        ++$comaPtr,
                        $phpcsFile->findNext([T_CLOSE_USE_GROUP], $comaPtr)
                    );
                }
                break;

            case 'T_CLOSE_USE_GROUP':
                $previousPtr = $phpcsFile->findPrevious(
                    [T_STRING],
                    $stackPtr
                );

                if ($phpcsFile->getTokens()[$previousPtr]['line'] === $phpcsFile->getTokens()[$stackPtr]['line']) {
                    $phpcsFile->addError(
                        'The close braket must be on the next line.',
                        ++$stackPtr,
                        'CloseUseGroupNextLine'
                    );
                }
                break;
        }
    }

    protected function getCurrentUse(File $phpcsFile, int $stackPtr): ?string
    {
        $startUse = $phpcsFile->findNext(T_STRING, $stackPtr);
        $tokenEndLine = $phpcsFile->findNext(T_SEMICOLON, ++$startUse, null, false, ';');
        $return = null;

        for ($index = $startUse; $index < $tokenEndLine; ++$index) {
            $currentToken = $phpcsFile->getTokens()[$index];

            if (T_OPEN_USE_GROUP === $currentToken['code']) {
                $return = null;
                break;
            }

            $return .= $currentToken['content'];
        }

        return $return;
    }

    protected function getUseGroupPrefix(File $phpcsFile, int $stackPtr): ?string
    {
        $return = null;
        $nextStackPtr = $stackPtr;
        $currentUseString = null;

        do {
            ++$nextStackPtr;
            $currentToken = $phpcsFile->getTokens()[$nextStackPtr];

            if (true === \is_array($currentToken) && T_OPEN_USE_GROUP === $currentToken['code']) {
                $return = $currentUseString;
                break;
            }

            $currentUseString .= $currentToken['content'];
        } while (T_SEMICOLON !== $currentToken['code']);

        return (true === \is_string($return)) ? \trim(\rtrim($return, '\\')) : null;
    }

    protected function validateUseGroupPrefixName(File $phpcsFile, int $stackPtr, string $prefix): self
    {
        $is3parts = false;

        foreach ($this->thirdLevelPrefixs as $usePrefix3part) {
            if (\substr($usePrefix3part, 0, \strlen($prefix)) === $prefix) {
                $phpcsFile->addError(
                    'Use group "'
                    . $prefix
                    . '" is invalid, you must group at 3rd level for '
                    . implode(', ', $this->thirdLevelPrefixs),
                    $stackPtr,
                    'GroupAt3rdLevel'
                );
            } elseif ($usePrefix3part === \substr($prefix, 0, \strlen($usePrefix3part))) {
                $is3parts = true;
                $countBackSlash = \substr_count($prefix, '\\');

                if (1 === $countBackSlash || 2 < $countBackSlash) {
                    $allowedPrefix = \substr($prefix, 0, \strpos($prefix, '\\', \strlen($usePrefix3part)) + 1);

                    $phpcsFile->addError(
                        '"' . $prefix . '" use group is invalid, use "' . $allowedPrefix . '" instead.',
                        $stackPtr,
                        'BadRegroupment'
                    );
                    break;
                }
            }
        }

        if (false === $is3parts && 1 < \substr_count($prefix, '\\')) {
            $allowedPrefix = \substr($prefix, 0, \strpos($prefix, '\\', \strpos($prefix, '\\') + 1) + 1);

            $phpcsFile->addError(
                '"' . $prefix . '" use group is invalid, use "' . \rtrim($allowedPrefix, '\\') . '" instead',
                $stackPtr,
                'BadRegroupment'
            );
        }

        return $this;
    }

    protected function validateUse(File $phpcsFile, int $stackPtr, string $useToValidate): self
    {
        foreach ($this->uses[$phpcsFile->getFilename()] ?? [] as $use) {
            $prefix = null;

            foreach ($this->thirdLevelPrefixs as $usePrefix3part) {
                if (\substr($use, 0, \strlen($usePrefix3part)) === $usePrefix3part) {
                    $prefix = \substr($use, 0, \strpos($use, '\\', \strlen($usePrefix3part)) + 1);
                    break;
                }
            }

            if (false === \is_string($prefix)) {
                $useParts = \explode('\\', $use);

                if (2 < \count($useParts)) {
                    $prefix = \implode('\\', \array_slice($useParts, 0, \count($useParts) - 1)) . '\\';
                }
            }

            if (true === \is_string($prefix) && $prefix === \substr($useToValidate, 0, \strlen($prefix))) {
                $phpcsFile->addError(
                    'You must group the use "' . $useToValidate . '" in "' . \rtrim($prefix, '\\') . '".',
                    $stackPtr,
                    'BadRegroupment'
                );
                break;
            }
        }

        if (false === \array_key_exists($phpcsFile->getFilename(), $this->uses)) {
            $this->uses[$phpcsFile->getFilename()] = [];
        }

        $this->uses[$phpcsFile->getFilename()][] = $useToValidate;

        return $this;
    }
}

<?php

/**
 * @author BaBeuloula
 *
 * Fork of https://github.com/steevanb/php-code-sniffs
 */

declare(strict_types=1);

namespace BaBeuloula\PhpCS\Sniffs\Uses;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

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
    /** @var array[] */
    protected $uses = [];

    /** @return mixed[] */
    public function register(): array
    {
        return [T_USE, T_CLASS];
    }

    /** @param int $stackPtr */
    public function process(File $phpcsFile, $stackPtr): void
    {
        if ($phpcsFile->getFilename() !== __FILE__) {
            return;
        }

        switch ($phpcsFile->getTokens()[$stackPtr]['type']) {
            case 'T_USE':
                $tokenEndLine = $phpcsFile->findNext([T_SEMICOLON], ++$stackPtr);
                $namespace = [];

                for ($index = $stackPtr; $index < $tokenEndLine; ++$index) {
                    $token = $phpcsFile->getTokens()[$index];

                    if ('T_STRING' === $token['type']) {
                        $namespace[] = $token['content'];
                    }
                }

                if (false === \array_key_exists($phpcsFile->getFilename(), $this->uses)) {
                    $this->uses[$phpcsFile->getFilename()] = [];
                }

                $this->uses[$phpcsFile->getFilename()][] = implode('\\', $namespace);

                natcasesort($this->uses[$phpcsFile->getFilename()]);
                break;

            case 'T_CLASS':
                $this->startCheck();
                break;
        }
    }

    protected function startCheck()
    {
        foreach ($this->uses as $use) {

        }
    }
}

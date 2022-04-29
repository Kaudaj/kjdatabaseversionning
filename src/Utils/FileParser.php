<?php
/**
 * Copyright since 2019 Kaudaj
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@kaudaj.com so we can send you a copy immediately.
 *
 * @author    Kaudaj <info@kaudaj.com>
 * @copyright Since 2019 Kaudaj
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

declare(strict_types=1);

namespace Kaudaj\Module\DBVCS\Utils;

use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\Parser;
use RuntimeException;

class FileParser
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var Node[]
     */
    private $ast;

    public function __construct(string $code)
    {
        $this->parseCode($code);
    }

    private function parseCode(string $code): void
    {
        $lexer = new Lexer\Emulative([
            'usedAttributes' => [
                'comments',
                'startFilePos', 'endFilePos',
                'startLine', 'endLine',
                'startTokenPos', 'endTokenPos',
            ],
        ]);
        $parser = new Parser\Php7($lexer);

        $ast = $parser->parse($code);
        if ($ast === null) {
            throw new RuntimeException('Failed to parse the code.');
        }

        $this->code = $code;
        $this->ast = $ast;
    }

    /**
     * @return string[]
     */
    public function getUseImports(int $importOffset = 0): array
    {
        $nodeFinder = new NodeFinder();

        /** @var Node\Stmt\Use_[] */
        $importsNodes = $nodeFinder->find($this->ast, function (Node $node) {
            return $node instanceof Node\Stmt\Use_;
        });

        $importsNames = [];
        foreach ($importsNodes as $importNode) {
            if (!$importNode->uses) {
                continue;
            }

            foreach ($importNode->uses as $use) {
                $importsNames[] = $use->name->toString();
            }
        }

        return array_slice($importsNames, $importOffset);
    }

    /**
     * @return string
     */
    public function getClassMethodContent(string $methodName): string
    {
        $nodeFinder = new NodeFinder();

        /** @var Node\Stmt\ClassMethod|null */
        $methodNode = $nodeFinder->findFirst($this->ast, function (Node $node) use ($methodName) {
            return $node instanceof Node\Stmt\ClassMethod
                && $node->name->toString() === $methodName;
        });

        if (!$methodNode || !$methodNode->stmts) {
            return '';
        }

        $startPos = $methodNode->getStartFilePos();
        $endPos = $methodNode->getEndFilePos();

        $methodContent = substr($this->code, $startPos, $endPos - $startPos);

        return ltrim(substr($methodContent, strpos($methodContent, '{') + 1));
    }
}

<?php
/**
 * This file is part of PPC.
 *
 * Copyright © 2020 Julien Bianchi <contact@jubianchi.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace jubianchi\PPC\Tests\Combinators;

use function jubianchi\PPC\Combinators\separated;
use jubianchi\PPC\Parser\Result\Failure;
use jubianchi\PPC\Parser\Result\Success;
use function jubianchi\PPC\Parsers\char;
use jubianchi\PPC\Stream\Char;
use PHPUnit\Framework\TestCase;

/**
 * @small
 *
 * @internal
 */
final class SeparatedTest extends TestCase
{
    /**
     * @test
     * @small
     */
    public function singleCharMatch(): void
    {
        $stream = new Char('a');
        $parser = separated(char('b'), char('a'));

        $result = $parser($stream);

        self::assertThat($result, self::isInstanceOf(Success::class));
        self::assertIsArray($result->result());
        self::assertEquals(['a'], $result->result());

        self::assertEquals(1, $stream->tell());
        self::assertEquals(Char::EOS, $stream->current());
    }

    /**
     * @test
     * @small
     */
    public function singleMatch(): void
    {
        $stream = new Char('abc');
        $parser = separated(char('b'), char('a'));

        $result = $parser($stream);

        self::assertThat($result, self::isInstanceOf(Success::class));
        self::assertIsArray($result->result());
        self::assertEquals(['a'], $result->result());

        self::assertEquals(1, $stream->tell());
        self::assertEquals('b', $stream->current());
    }

    /**
     * @test
     * @small
     */
    public function manyMatch(): void
    {
        $stream = new Char('ababab');
        $parser = separated(char('b'), char('a'));

        $result = $parser($stream);

        self::assertThat($result, self::isInstanceOf(Success::class));
        self::assertIsArray($result->result());
        self::assertEquals(['a', 'a', 'a'], $result->result());

        self::assertEquals(5, $stream->tell());
        self::assertEquals('b', $stream->current());
    }

    /**
     * @test
     * @small
     */
    public function manyNotSeparatedMatch(): void
    {
        $stream = new Char('aab');
        $parser = separated(char('b'), char('a'));

        $result = $parser($stream);

        self::assertThat($result, self::isInstanceOf(Success::class));
        self::assertIsArray($result->result());
        self::assertEquals(['a'], $result->result());

        self::assertEquals(1, $stream->tell());
        self::assertEquals('a', $stream->current());
    }

    /**
     * @test
     * @small
     */
    public function noMatch(): void
    {
        $stream = new Char('baba');
        $parser = separated(char('b'), char('a'));

        $result = $parser($stream);

        self::assertThat($result, self::isInstanceOf(Failure::class));

        try {
            $result->result();

            self::fail();
        } catch (Failure $failure) {
            self::assertEquals('char: Expected "a", got "b" at line 1 offset 0', $failure->getMessage());
        }

        self::assertEquals(0, $stream->tell());
        self::assertEquals('b', $stream->current());
    }
}

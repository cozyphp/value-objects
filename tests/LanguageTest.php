<?php

namespace Cozy\ValueObjects\Tests;

use ArgumentCountError;
use Cozy\ValueObjects\Language;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LanguageTest extends TestCase
{
    public function testCreateFromAlpha2WithoutArguments(): void
    {
        $this->expectException(ArgumentCountError::class);
        /** @noinspection PhpParamsInspection */
        Language::createFromAlpha2();
    }

    public function testCreateFromAlpha2WithInvalidCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Language::createFromAlpha2('esr');
    }

    public function testGetByAlpha2WithUnregisteredLanguage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Language::createFromAlpha2('xr');
    }

    public function testCreateFromAlpha2(): Language
    {
        $example = Language::createFromAlpha2('fr');

        self::assertEquals('fr', $example);

        return $example;
    }

    public function testCreateFromAlpha3WithoutArguments(): void
    {
        $this->expectException(ArgumentCountError::class);
        /** @noinspection PhpParamsInspection */
        Language::createFromAlpha3();
    }

    public function testCreateFromAlpha3WithInvalidCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Language::createFromAlpha3('es');
    }

    public function testGetByAlpha3WithUnregisteredLanguage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Language::createFromAlpha3('enl');
    }

    public function testCreateFromAlpha3(): void
    {
        $example = Language::createFromAlpha3('ENG');
        self::assertEquals('en', $example);
    }

    /**
     * @depends testCreateFromAlpha2
     * @param Language $example
     */
    public function testGetAlpha2(Language $example): void
    {
        self::assertSame('fr', $example->getAlpha2());
    }

    /**
     * @depends testCreateFromAlpha2
     * @param Language $example
     */
    public function testGetAlpha3(Language $example): void
    {
        self::assertSame('fra', $example->getAlpha3());
    }

    /**
     * @depends testCreateFromAlpha2
     */
    public function testGetEnglishName(): void
    {
        $example = Language::createFromAlpha2('en');
        self::assertSame('English', $example->getEnglishName());

        $example = Language::createFromAlpha2('fr');
        self::assertSame('French', $example->getEnglishName());

        $example = Language::createFromAlpha2('DE');
        self::assertSame('German', $example->getEnglishName());

        $example = Language::createFromAlpha2('es');
        self::assertStringContainsString('Spanish', $example->getEnglishName());
    }

    /**
     * @depends testCreateFromAlpha2
     */
    public function testGetNativeName(): void
    {
        $example = Language::createFromAlpha2('EN');
        self::assertSame('English', $example->getNativeName());

        $example = Language::createFromAlpha2('fr');
        self::assertStringContainsString('Français', $example->getNativeName());

        $example = Language::createFromAlpha2('de');
        self::assertSame('Deutsch', $example->getNativeName());

        $example = Language::createFromAlpha2('es');
        self::assertSame('Español', $example->getNativeName());
    }

    /**
     * @depends testCreateFromAlpha2
     * @depends testCreateFromAlpha3
     */
    public function testEquals(): void
    {
        $a = Language::createFromAlpha2('en');
        $b = Language::createFromAlpha2('es');
        $c = Language::createFromAlpha3('ENG');

        self::assertNotTrue($a->equals($b));
        self::assertNotTrue($b->equals($c));
        self::assertNotTrue($c->equals($b));
        self::assertTrue($c->equals($a));
    }
}
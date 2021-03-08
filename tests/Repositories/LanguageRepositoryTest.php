<?php

namespace Cozy\ValueObjects\Tests\Repositories;

use Cozy\ValueObjects\Language;
use Cozy\ValueObjects\Repositories\LanguageRepository;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LanguageRepositoryTest extends TestCase
{
    public function testGetAll(): void
    {
        $languages = LanguageRepository::getAll();

        self::assertIsIterable($languages);

        foreach ($languages as $commonLanguage) {
            self::assertInstanceOf(Language::class, $commonLanguage);
        }
    }

    public function testFindAlpha2WithInvalidCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        LanguageRepository::findAlpha2('esr');
    }

    public function testFindAlpha2WithAnUnregisteredLanguage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        LanguageRepository::findAlpha2('xr');
    }

    /**
     * @depends testFindAlpha2WithInvalidCode
     * @depends testFindAlpha2WithAnUnregisteredLanguage
     */
    public function testFindAlpha2(): void
    {
        $expectedValue = [
            'name_en' => 'English',
            'name' => 'English',
            'alpha2' => 'en',
            'alpha3' => 'eng',
        ];

        self::assertSame($expectedValue, LanguageRepository::findAlpha2('en'));

        $expectedValue = [
            'name_en' => 'Spanish',
            'name' => 'Español',
            'alpha2' => 'es',
            'alpha3' => 'spa',
        ];

        self::assertSame($expectedValue, LanguageRepository::findAlpha2('es'));
    }

    public function testFindAlpha3WithInvalidCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        LanguageRepository::findAlpha3('es');
    }

    public function testFindAlpha3WithAnUnregisteredLanguage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        LanguageRepository::findAlpha3('enl');
    }

    /**
     * @depends testFindAlpha3WithInvalidCode
     * @depends testFindAlpha3WithAnUnregisteredLanguage
     */
    public function testFindAlpha3(): void
    {
        $expectedValue = [
            'name_en' => 'English',
            'name' => 'English',
            'alpha2' => 'en',
            'alpha3' => 'eng',
        ];

        self::assertSame($expectedValue, LanguageRepository::findAlpha3('ENG'));

        $expectedValue = [
            'name_en' => 'Spanish',
            'name' => 'Español',
            'alpha2' => 'es',
            'alpha3' => 'spa',
        ];

        self::assertSame($expectedValue, LanguageRepository::findAlpha3('spa'));
    }
}
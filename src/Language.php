<?php

/**
 * Cozy.ValueObjects
 *
 * (c) Nestor Picado <info@nestorpicado.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cozy\ValueObjects;

use Cozy\Contracts\Equatable;
use Cozy\ValueObjects\Repositories\LanguageRepository;

/**
 * Class Language.
 * @package Cozy\ValueObjects
 */
class Language implements Equatable
{
    private string $alpha2;
    private string $alpha3;
    private string $englishName;
    private string $nativeName;

    private function __construct(string $alpha2, string $alpha3, string $english_name, string $native_name)
    {
        $this->englishName = $english_name;
        $this->nativeName = $native_name;
        $this->alpha2 = $alpha2;
        $this->alpha3 = $alpha3;
    }

    public static function createFromAlpha2(string $alpha2): Language
    {
        $record = LanguageRepository::findAlpha2($alpha2);

        return new self(
            $record['alpha2'],
            $record['alpha3'],
            $record['name_en'],
            $record['name']
        );
    }

    public static function createFromAlpha3(string $alpha3): Language
    {
        $record = LanguageRepository::findAlpha3($alpha3);

        return new self(
            $record['alpha2'],
            $record['alpha3'],
            $record['name_en'],
            $record['name']
        );
    }

    public function getEnglishName(): string
    {
        return $this->englishName;
    }

    public function getNativeName(): string
    {
        return $this->nativeName;
    }

    public function getAlpha2(): string
    {
        return $this->alpha2;
    }

    public function getAlpha3(): string
    {
        return $this->alpha3;
    }

    /**
     * @inheritDoc
     */
    public function equals($other): bool
    {
        return ($other instanceof self)
            && $this->getAlpha2() === $other->getAlpha2()
            && $this->getAlpha3() === $other->getAlpha3();
    }

    public function __toString(): string
    {
        return $this->alpha2;
    }
}

<?php

namespace Cozy\ValueObjects\Tests;

use ArgumentCountError;
use Cozy\ValueObjects\Matrix;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MatrixTest extends TestCase
{
    public function testCreationWithoutArguments(): void
    {
        $this->expectException(ArgumentCountError::class);
        /** @noinspection PhpParamsInspection */
        new Matrix();
    }

    public function testCreationWithInvalidMatrix(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Matrix(
            [
                [1, 3],
                [5, 9, 2],
            ]
        );
    }

    public function testCreationWithInvalidArray(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Matrix(
            [
                'row1' => [1, 2, 3],
                'row2' => [4, 5, 6],
            ]
        );
    }

    public function testCreationWithVector(): Matrix
    {
        $example = new Matrix([1, 2, 3]);

        self::assertInstanceOf(Matrix::class, $example);

        return $example;
    }

    /**
     * @depends testCreationWithoutArguments
     * @depends testCreationWithInvalidMatrix
     * @depends testCreationWithInvalidArray
     */
    public function testCreation(): Matrix
    {
        $example = new Matrix(
            [
                [4, -2],
                [-3, 0],
                [3, 5],
            ]
        );

        self::assertInstanceOf(Matrix::class, $example);

        return $example;
    }

    /**
     * @depends testCreation
     * @param Matrix $example
     */
    public function testGetRowsCount(Matrix $example): void
    {
        self::assertSame(3, $example->getRowsCount());
    }

    /**
     * @depends testCreation
     * @param Matrix $example
     */
    public function testGetColumnsCount(Matrix $example): void
    {
        self::assertSame(2, $example->getColumnsCount());
    }

    /**
     * @depends testCreation
     * @param Matrix $example
     */
    public function testGetCellValueWithInvalidRowNumber(Matrix $example): void
    {
        $this->expectException(InvalidArgumentException::class);
        $example->getCellValue(-1, 1);
    }

    /**
     * @depends testCreation
     * @param Matrix $example
     */
    public function testGetCellValueWithInvalidColumnNumber(Matrix $example): void
    {
        $this->expectException(InvalidArgumentException::class);
        $example->getCellValue(2, -2);
    }

    /**
     * @depends testCreation
     * @param Matrix $example
     */
    public function testGetCellValueWithExcessiveRowNumber(Matrix $example): void
    {
        $this->expectException(InvalidArgumentException::class);
        $example->getCellValue(4, 1);
    }

    /**
     * @depends testCreation
     * @param Matrix $example
     */
    public function testGetCellValueWithExcessiveColumnNumber(Matrix $example): void
    {
        $this->expectException(InvalidArgumentException::class);
        $example->getCellValue(2, 5);
    }

    /**
     * @depends testCreation
     * @param Matrix $example
     */
    public function testGetCellValue(Matrix $example): void
    {
        self::assertSame(5, $example->getCellValue(3, 2));
    }

    public function testCreateFilledMatrixWithoutDimension(): void
    {
        $this->expectException(ArgumentCountError::class);
        /** @noinspection PhpParamsInspection */
        Matrix::createFilledMatrix(10);
    }

    public function testCreateFilledMatrixWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Matrix::createFilledMatrix('2', 3, 1);
    }

    public function testCreateFilledMatrixWithInvalidRowNumber(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Matrix::createFilledMatrix(0, -2, 1);
    }

    public function testCreateFilledMatrixWithInvalidColumnNumber(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Matrix::createFilledMatrix(0, 2, -1);
    }

    /**
     * @depends testGetRowsCount
     * @depends testGetColumnsCount
     * @depends testGetCellValue
     * @depends testCreateFilledMatrixWithoutDimension
     * @depends testCreateFilledMatrixWithInvalidValue
     * @depends testCreateFilledMatrixWithInvalidRowNumber
     * @depends testCreateFilledMatrixWithInvalidColumnNumber
     */
    public function testCreateFilledMatrix(): void
    {
        $example = Matrix::createFilledMatrix(1.5, 3, 4);

        self::assertSame(3, $example->getRowsCount());
        self::assertSame(4, $example->getColumnsCount());
        self::assertSame(1.5, $example->getCellValue(1, 1));
        self::assertSame(1.5, $example->getCellValue(2, 2));
        self::assertSame(1.5, $example->getCellValue(3, 3));
        self::assertSame(1.5, $example->getCellValue(3, 4));

        $example = Matrix::createFilledMatrix(1.5, 3);
        self::assertSame($example->getColumnsCount(), $example->getRowsCount());
    }

    /**
     * @depends testCreation
     * @param Matrix $example
     */
    public function testGetRowValues(Matrix $example): void
    {
        self::assertSame([4, -2], $example->getRowValues(1));
    }

    /**
     * @depends testCreation
     * @param Matrix $example
     */
    public function testGetColumnValues(Matrix $example): void
    {
        self::assertSame([-2, 0, 5], $example->getColumnValues(2));
    }

    public function testIsSquare(): void
    {
        $a = new Matrix(
            [
                [1, 2],
                [3, 4],
            ]
        );
        $b = new Matrix(
            [
                [1, 2, 3],
                [4, 5, 6],
            ]
        );
        $c = new Matrix([1, 2, 3]);

        self::assertTrue($a->isSquare());
        self::assertNotTrue($b->isSquare());
        self::assertNotTrue($c->isSquare());
    }

    /**
     * @depends testCreationWithVector
     * @param Matrix $example
     */
    public function testIsVector(Matrix $example): void
    {
        $a = new Matrix(
            [
                [1, 2, 3],
                [4, 5, 6],
            ]
        );

        self::assertNotTrue($a->isVector());
        self::assertTrue($example->isVector());
    }
}

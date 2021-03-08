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
use InvalidArgumentException;
use RuntimeException;

/**
 * An immutable Value Object that represents a rectangular array or table of numbers arranged in rows and columns.
 */
class Matrix implements Equatable
{
    private array $matrix;
    private int $columnsCount;
    private int $rowsCount;
    private float $determinant;

    // PRIVATE METHODS

    /**
     * Validate that the given row number is correct and exists within the array.
     *
     * @param  int $row number of row
     * @return int returns the same given number of row
     */
    private function validateRow(int $row): int
    {
        if ($row < 1) {
            throw new InvalidArgumentException('The given row number is invalid.');
        }

        if ($row > $this->rowsCount) {
            throw new InvalidArgumentException('The given row number exceeds matrix size.');
        }

        return $row;
    }

    /**
     * Validate that the given column number is correct and exists within the array.
     *
     * @param  int $column number of column
     * @return int returns the same given number of column
     */
    private function validateColumn(int $column): int
    {
        if ($column < 1) {
            throw new InvalidArgumentException('The given column number is invalid.');
        }

        if ($column > $this->columnsCount) {
            throw new InvalidArgumentException('The given column number exceeds matrix size.');
        }

        return $column;
    }

    /**
     * Validate that the matrices have the same dimensions.
     *
     * @param  self                     $input matrix being compared.
     * @throws InvalidArgumentException
     */
    private function validateMatchingDimensions(self $input): void
    {
        if (($this->rowsCount !== $input->getRowsCount()) || ($this->columnsCount !== $input->getColumnsCount())) {
            throw new InvalidArgumentException('Matrices have mismatched dimensions.');
        }
    }

    /**
     * Compare the dimensions of the matrices being operated on to see if they are valid for multiplication/division.
     *
     * @param  self                     $input The second Matrix object on which the operation will be performed.
     * @throws InvalidArgumentException
     */
    private function validateReflectingDimensions(self $input): void
    {
        if ($this->columnsCount !== $input->getRowsCount()) {
            throw new InvalidArgumentException('Matrices have mismatched dimensions');
        }
    }

    // PUBLIC COMMON METHODS

    public function __construct(array $input, bool $validate = true)
    {
        if (!isset($input[0])) {
            throw new InvalidArgumentException(
                'The input matrix is using invalid indexes on the rows. These must be consecutive integers starting from 0.'
            );
        }

        if (!is_array($input[0])) {
            $this->rowsCount = 1;
            $this->columnsCount = count($input);
            $input = [$input];
        } else {
            $this->rowsCount = count($input);
            $this->columnsCount = count($input[0]);
        }

        // Validate the complete structure of the matrix if required

        if ($validate) {
            $consecutiveRowIndex = 0;
            $columnType = null;

            foreach ($input as $rowIndex => $row) {
                if (!is_numeric($rowIndex) || (int)$rowIndex !== $consecutiveRowIndex) {
                    throw new InvalidArgumentException(
                        "The row index [{$rowIndex}] is not a valid consecutive integer starting from 0."
                    );
                }

                if (!is_array($row)) {
                    throw new InvalidArgumentException(
                        "Row [{$rowIndex}] is not a valid array."
                    );
                }

                $consecutiveRowIndex++;

                // Validating columns of multidimensional matrix

                if (count($row) !== $this->columnsCount) {
                    throw new InvalidArgumentException(
                        "Row [{$rowIndex}] has the wrong number of columns."
                    );
                }

                $consecutiveColumnIndex = 0;

                foreach ($row as $columnIndex => $column) {
                    if (!is_numeric($columnIndex) || (int)$columnIndex !== $consecutiveColumnIndex) {
                        throw new InvalidArgumentException(
                            "The column index [{$rowIndex}][{$columnIndex}] is not a valid consecutive integer starting from 0."
                        );
                    }

                    if (!isset($columnType)) {
                        $columnType = gettype($column);
                    }

                    if (gettype($column) !== $columnType) {
                        throw new InvalidArgumentException(
                            "The column [{$rowIndex}][{$columnIndex}] has a different type of value."
                        );
                    }

                    $consecutiveColumnIndex++;
                }
            }
        }

        $this->matrix = $input;
    }

    /**
     * @inheritDoc
     */
    public function equals($other): bool
    {
        if (
            !($other instanceof self)
            || $this->rowsCount !== $other->getRowsCount()
            || $this->columnsCount !== $other->getColumnsCount()
        ) {
            return false;
        }

        $b = $other->toArray();

        for ($i = 0; $i < $this->rowsCount; ++$i) {
            for ($j = 0; $j < $this->columnsCount; ++$j) {
                if ($this->matrix[$i][$j] !== $b[$i][$j]) {
                    return false;
                }
            }
        }

        return true;
    }

    public function toArray(): array
    {
        return $this->matrix;
    }

    public function getRowsCount(): int
    {
        return $this->rowsCount;
    }

    public function getColumnsCount(): int
    {
        return $this->columnsCount;
    }

    /**
     * Check if the matrix has only one dimension.
     *
     */
    public function isVector(): bool
    {
        return $this->columnsCount === 1 || $this->rowsCount === 1;
    }

    public function isSquare(): bool
    {
        return $this->columnsCount === $this->rowsCount;
    }

    /**
     * Returns the value of a cell inside the matrix according to the given row and column numbers.
     *
     * @param  int       $row    row number starting from 1.
     * @param  int       $column column number starting from 1.
     * @return int|float
     */
    public function getCellValue(int $row, int $column)
    {
        $row = $this->validateRow($row);
        $column = $this->validateColumn($column);

        return $this->matrix[$row - 1][$column - 1];
    }

    /**
     * Returns a row vector (array) containing the values of the given row.
     *
     * @param  int           $row row number starting from 1.
     * @return int[]|float[]
     */
    public function getRowValues(int $row): array
    {
        $row = $this->validateRow($row);

        return $this->matrix[$row - 1];
    }

    /**
     * Returns a row vector (array) containing the values of the given column.
     *
     * @param  int           $column column number starting from 1.
     * @return int[]|float[]
     */
    public function getColumnValues(int $column): array
    {
        $column = $this->validateColumn($column);

        return array_column($this->matrix, $column - 1);
    }

    /**
     * Returns a new matrix excluding the given row and column.
     *
     * @param  int  $row    The row number starting from 1.
     * @param  int  $column The column number starting from 1.
     * @return self new instance
     */
    public function crossOut(int $row, int $column): self
    {
        $row = $this->validateRow($row);
        $column = $this->validateColumn($column);

        $new = $this->matrix;

        array_splice($new, $row - 1, 1);

        for ($i = 0; $i < $this->rowsCount - 1; $i++) {
            array_splice($new[$i], $column - 1, 1);
        }

        return new self($new, false);
    }

    /**
     * Returns the determinant of the matrix using the Bareiss algorithm.
     *
     * @return float|int
     */
    public function getDeterminant()
    {
        if (isset($this->determinant)) {
            return $this->determinant;
        }

        $new = $this->matrix;
        $sign = 1;
        $m = 0;

        for ($k = 0; $k < $this->rowsCount - 1; $k++) {
            //Pivot - row swap needed
            if ($new[$k][$k] === 0) {
                for ($m = $k + 1; $m < $this->rowsCount; $m++) {
                    if ($new[$m][$k] !== 0) {
                        $tmp = $new[$m];
                        $new[$m] = $new[$k];
                        $new[$k] = $tmp;
                        $sign = -$sign;
                        break;
                    }
                }

                // No entries != 0 found in column k -> det = 0
                if ($m === $this->rowsCount) {
                    return 0;
                }
            }

            //Apply formula
            for ($i = $k + 1; $i < $this->rowsCount; $i++) {
                for ($j = $k + 1; $j < $this->rowsCount; $j++) {
                    $new[$i][$j] = $new[$k][$k] * $new[$i][$j] - $new[$i][$k] * $new[$k][$j];
                    if ($k !== 0) {
                        $new[$i][$j] /= $new[$k - 1][$k - 1];
                    }
                }
            }
        }

        return $this->determinant = $sign * $new[$this->rowsCount - 1][$this->rowsCount - 1];
    }

    /**
     * Check if the matrix is not invertible.
     *
     */
    public function isSingular(): bool
    {
        return $this->getDeterminant() === 0;
    }

    /**
     * Returns the diagonal identity of the matrix.
     */
    public function identity(): self
    {
        $array = array_fill(0, $this->rowsCount, array_fill(0, $this->columnsCount, 0));
        for ($i = 0; $i < $this->rowsCount; ++$i) {
            $array[$i][$i] = 1;
        }

        return new self($array, false);
    }

    /**
     * Returns the minors of the matrix.
     *
     * The minor of a matrix A is the determinant of some smaller square matrix, cut down from A by removing one or
     * more of its rows or columns. Minors obtained by removing just one row and one column from square matrices
     * (first minors) are required for calculating matrix cofactors, which in turn are useful for computing both the
     * determinant and inverse of square matrices.
     *
     * @throws RuntimeException
     **@link https://en.wikipedia.org/wiki/Minor_(linear_algebra)
     */
    public function minors(): self
    {
        if (!$this->isSquare()) {
            throw new RuntimeException('Minors can only be calculated if this matrix is square.');
        }

        $minors = $this->toArray();

        if ($this->rowsCount > 1) {
            for ($i = 0; $i < $this->rowsCount; ++$i) {
                for ($j = 0; $j < $this->rowsCount; ++$j) {
                    $minor = $this->crossOut($i + 1, $j + 1)->getDeterminant();
                    $minors[$i][$j] = $minor;
//                    $minors[$i][$j] = $this->getDeterminantSegment($i, $j);
                }
            }
        }

        return new self($minors, false);
    }

    /**
     * Returns the cofactors of the matrix.
     *
     * @link https://en.wikipedia.org/wiki/Minor_(linear_algebra)
     */
    public function cofactors(): self
    {
        if (!$this->isSquare()) {
            throw new RuntimeException('Matrix must be square to calculate the Cofactors.');
        }

        $cofactors = $this->minors()->toArray();

        $cof = 1;
        for ($i = 0; $i < $this->rowsCount; ++$i) {
            $cofs = $cof;
            for ($j = 0; $j < $this->rowsCount; ++$j) {
                $cofactors[$i][$j] *= $cofs;
                $cofs = -$cofs;
            }
            $cof = -$cof;
        }

        return new self($cofactors, false);
    }

    /**
     * Returns the adjoint of the matrix.
     *
     * The adjugate, classical adjoint, or adjunct of a square matrix is the transpose of its cofactor matrix.
     * According to Wikipedia, the adjugate has sometimes been called the "adjoint", but today the "adjoint" of a
     * matrix normally refers to its corresponding adjoint operator, which is its conjugate transpose.
     *
     * @throws RuntimeException
     **@link https://en.wikipedia.org/wiki/Adjugate_matrix
     */
    public function adjoint(): self
    {
        if (!$this->isSquare()) {
            throw new RuntimeException('Matrix must be square to calculate the Adjoint.');
        }

        return $this->cofactors()->transpose();
    }

    // OPERATIONS

    /**
     * Returns the transpose of the matrix.
     *
     */
    public function transpose(): self
    {
        if ($this->rowsCount === 1) {
            $new = array_map(
                static function ($el): array {
                    return [$el];
                },
                $this->matrix[0]
            );
        } else {
            $new = array_map(null, ...$this->matrix);
        }

        return new self($new, false);
    }

    /**
     * Returns the inverse of the matrix.
     *
     * @throws RuntimeException
     **@link https://en.wikipedia.org/wiki/Invertible_matrix
     */
    public function inverse(): self
    {
        if (!$this->isSquare()) {
            throw new RuntimeException('Matrix must be square to calculate the Inverse.');
        }

        if ($this->isSingular()) {
            throw new RuntimeException('Matrix must be non-singular to calculate the Inverse.');
        }

        if ($this->rowsCount === 1) {
            return new self([[1 / $this->getCellValue(1, 1)]]);
        }

        return $this->adjoint()->multiplyScalar(1 / $this->getDeterminant());
    }

    /**
     * Multiplies the matrix by another matrix.
     *
     */
    public function multiply(self $input): self
    {
        $this->validateReflectingDimensions($input);

        $array1 = $this->toArray();
        $array2 = $input->toArray();

        $product = [];
        foreach ($array1 as $row => $rowData) {
            for ($col = 0; $col < $input->getColumnsCount(); ++$col) {
                $columnData = array_column($array2, $col);
                $sum = 0;
                foreach ($rowData as $key => $valueData) {
                    $sum += $valueData * $columnData[$key];
                }

                $product[$row][$col] = $sum;
            }
        }

        return new self($product, false);
    }

    /**
     * Divides the matrix by another matrix.
     *
     */
    public function divide(self $input): self
    {
        $this->validateReflectingDimensions($input);

        return $this->multiply($input->inverse());
    }

    /**
     * Multiplies the matrix by a scalar value.
     *
     * @param float|int $value
     */
    public function multiplyScalar($value): self
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException('The input value is not a valid number.');
        }

        $new = $this->matrix;

        for ($i = 0; $i < $this->rowsCount; ++$i) {
            for ($j = 0; $j < $this->columnsCount; ++$j) {
                $new[$i][$j] *= $value;
            }
        }

        return new self($new, false);
    }

    /**
     * Divides the matrix by a scalar value.
     *
     * @param float|int $value
     */
    public function divideScalar($value): self
    {
        return $this->multiplyScalar(1 / $value);
    }

    /**
     * Sums a scalar value to the matrix.
     *
     * @param float|int $value
     */
    public function sumScalar($value): self
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException('The input value is not a valid number.');
        }

        $new = $this->matrix;

        for ($i = 0; $i < $this->rowsCount; ++$i) {
            for ($j = 0; $j < $this->columnsCount; ++$j) {
                $new[$i][$j] += $value;
            }
        }

        return new self($new, false);
    }

    /**
     * Subtracts a scalar value to the matrix.
     *
     * @param float|int $value
     */
    public function subtractScalar($value): self
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException('The input value is not a valid number.');
        }

        $new = $this->matrix;

        for ($i = 0; $i < $this->rowsCount; ++$i) {
            for ($j = 0; $j < $this->columnsCount; ++$j) {
                $new[$i][$j] -= $value;
            }
        }

        return new self($new, false);
    }

    /**
     * Sums or subtracts another matrix to this matrix depending on the given sign parameter.
     */
    public function sum(self $input, int $sign = 1): self
    {
        $this->validateMatchingDimensions($input);

        $a1 = $this->toArray();
        $a2 = $input->toArray();

        for ($i = 0; $i < $this->rowsCount; ++$i) {
            for ($k = 0; $k < $this->columnsCount; ++$k) {
                $a1[$i][$k] += $sign * $a2[$i][$k];
            }
        }

        return new self($a1, false);
    }

    /**
     * Subtracts another matrix to this matrix.
     */
    public function subtract(self $input): self
    {
        return $this->sum($input, -1);
    }

    // PUBLIC STATIC METHODS

    /**
     * Create a new matrix filled with identical values according to given dimensions.
     * Ignore the columns argument to create a square matrix based on the number of rows.
     *
     * @param  int|float $value
     * @param  int       $rows    number of rows
     * @param  int|null  $columns number of columns
     * @return self      new instance of matrix
     */
    public static function createFilledMatrix($value, int $rows, int $columns = null): self
    {
        if (!is_int($value) && !is_float($value)) {
            throw new InvalidArgumentException('The given value is not numeric.');
        }

        if ($rows < 1) {
            throw new InvalidArgumentException('The given number of rows is invalid.');
        }

        if ($columns === null) {
            $columns = $rows;
        } elseif ($columns < 1) {
            throw new InvalidArgumentException('The given number of columns is invalid.');
        }

        return new self(array_fill(0, $rows, array_fill(0, $columns, $value)));
    }
}

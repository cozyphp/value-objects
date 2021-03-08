<?php

namespace Cozy\ValueObjects\Tests;

use ArgumentCountError;
use Cozy\ValueObjects\TreeStorage;
use InvalidArgumentException;
use stdClass;
use PHPUnit\Framework\TestCase;

class TreeStorageTest extends TestCase
{
    public function testCreationWithoutArguments(): void
    {
        $this->expectException(ArgumentCountError::class);
        /** @noinspection PhpParamsInspection */
        new TreeStorage();
    }

    public function testCreationWithWrongArguments(): void
    {
        $this->expectException(InvalidArgumentException::class);
        /** @noinspection PhpParamsInspection */
        new TreeStorage(123);
    }

    /**
     * @depends testCreationWithoutArguments
     * @depends testCreationWithWrongArguments
     */
    public function testCreation(): TreeStorage
    {
        $key2_object = new stdClass();
        $key2_object->subkey1 = 'subvalue1';
        $key2_object->subkey2 = 'subvalue2';
        $key2_object->contact2 = [
            'first_name' => 'Nestor',
            'sub_name' => 'Daniel',
            'last_name' => 'Picado',
        ];
        $mixed_data = [
            'key1' => 'val1',
            'key2' => $key2_object,
            'key3' => 321,
        ];

        $mixed_tree = new TreeStorage($mixed_data);
        self::assertInstanceOf(TreeStorage::class, $mixed_tree);

        return $mixed_tree;
    }

    /**
     * @depends testCreation
     * @param TreeStorage $example
     */
    public function testExists(TreeStorage $example): void
    {
        self::assertTrue($example->exists('key1'));
        self::assertTrue($example->exists('key2'));
        self::assertTrue($example->exists('key3'));
        self::assertTrue($example->exists('key2.subkey1'));
        self::assertTrue($example->exists('key2.subkey2'));
        self::assertTrue($example->exists('key2.contact2'));
        self::assertTrue($example->exists('key2.contact2.first_name'));
        self::assertTrue($example->exists('key2.contact2.last_name'));
        self::assertFalse($example->exists('unknown'));
    }

    /**
     * @depends testCreation
     * @param TreeStorage $example
     */
    public function testGet(TreeStorage $example): void
    {
        self::assertSame(321, $example->get('key3'));

        self::assertSame('val1', $example->get('key1'));

        self::assertSame('subvalue1', $example->get('key2.subkey1'));

        self::assertSame('Nestor', $example->get('key2.contact2.first_name'));

        self::assertNull($example->get('unknown'));
    }

    /**
     * @depends testCreation
     * @param TreeStorage $example
     */
    public function testList(TreeStorage $example): void
    {
        $list = $example->list();
        self::assertCount(9, $list);

        $expected_list = [
            0 => 'key1',
            1 => 'key2',
            2 => 'key2.subkey1',
            3 => 'key2.subkey2',
            4 => 'key2.contact2',
            5 => 'key2.contact2.first_name',
            6 => 'key2.contact2.sub_name',
            7 => 'key2.contact2.last_name',
            8 => 'key3',
        ];
        self::assertSame($expected_list, $list);
    }

    /**
     * @depends testCreation
     * @param TreeStorage $example
     */
    public function testMatch(TreeStorage $example): void
    {
        /*
         * Match one static key.
         */
        $matched = $example->match('key1');

        self::assertCount(1, $matched);
        self::assertSame(['key1' => 'val1'], $matched);

        /*
         * Match one key excluding intermediate nodes with trees.
         * According to data, key2 is a sub tree.
         */
        $matched = $example->match('key2', true);
        self::assertCount(0, $matched);

        /*
         * Match first level of keys, converting sub trees into arrays.
         */
        $matched = $example->match('?', false, TreeStorage::FORMAT_ARRAY);
        self::assertCount(3, $matched);
        self::assertSame(
            [
                'key1' => 'val1',
                'key2' => [
                    'subkey1' => 'subvalue1',
                    'subkey2' => 'subvalue2',
                    'contact2' => [
                        'first_name' => 'Nestor',
                        'sub_name' => 'Daniel',
                        'last_name' => 'Picado',
                    ],
                ],
                'key3' => 321,
            ],
            $matched
        );

        /*
         * Match one level with any key inside 'key2' excluding intermediate nodes with trees.
         * According to data, 'key2.contact2' is a sub tree.
         */
        $matched = $example->match('key2.?', true);
        self::assertCount(2, $matched);
        self::assertSame(
            [
                'key2.subkey1' => 'subvalue1',
                'key2.subkey2' => 'subvalue2',
            ],
            $matched
        );

        /*
         * Match one level with any key inside 'key2'.
         */
        $matched = $example->match('key2.?');
        self::assertCount(3, $matched);
        self::assertSame(
            [
                'key2.subkey1' => 'subvalue1',
                'key2.subkey2' => 'subvalue2',
                'key2.contact2' => [
                    'first_name' => 'Nestor',
                    'sub_name' => 'Daniel',
                    'last_name' => 'Picado',
                ],
            ],
            $matched
        );

        /*
         * Match one level with any key inside 'key2', converting sub trees into objects.
         */
        $matched = $example->match('key2.?', false, TreeStorage::FORMAT_OBJECT);
        self::assertCount(3, $matched);
        self::assertEquals(
            [
                'key2.subkey1' => 'subvalue1',
                'key2.subkey2' => 'subvalue2',
                'key2.contact2' => (object)[
                    'first_name' => 'Nestor',
                    'sub_name' => 'Daniel',
                    'last_name' => 'Picado',
                ],
            ],
            $matched
        );

        /*
         * Match without level limitation any key starting with 'key2.'.
         */
        $matched = $example->match('key2.*');
        self::assertCount(6, $matched);
        self::assertSame(
            [
                'key2.subkey1' => 'subvalue1',
                'key2.subkey2' => 'subvalue2',
                'key2.contact2' => [
                    'first_name' => 'Nestor',
                    'sub_name' => 'Daniel',
                    'last_name' => 'Picado',
                ],
                'key2.contact2.first_name' => 'Nestor',
                'key2.contact2.sub_name' => 'Daniel',
                'key2.contact2.last_name' => 'Picado',
            ],
            $matched
        );

        /*
         * Match without level limitation any key starting with 'key2.', excluding intermediate nodes with trees.
         * According to data, 'key2.contact2' is a sub tree.
         */
        $matched = $example->match('key2.*', true);
        self::assertCount(5, $matched);
        self::assertSame(
            [
                'key2.subkey1' => 'subvalue1',
                'key2.subkey2' => 'subvalue2',
                'key2.contact2.first_name' => 'Nestor',
                'key2.contact2.sub_name' => 'Daniel',
                'key2.contact2.last_name' => 'Picado',
            ],
            $matched
        );

        /*
         * Match without level limitation any key starting with 'key2.' and ending with 'name'.
         */
        $matched = $example->match('key2.*name');
        self::assertCount(3, $matched);
        self::assertSame(
            [
                'key2.contact2.first_name' => 'Nestor',
                'key2.contact2.sub_name' => 'Daniel',
                'key2.contact2.last_name' => 'Picado',
            ],
            $matched
        );

        /*
         * Match without level limitation any key starting with 'key2.' and containing the string 'sub'.
         */
        $matched = $example->match('key2.*sub*');
        self::assertCount(3, $matched);
        self::assertSame(
            [
                'key2.subkey1' => 'subvalue1',
                'key2.subkey2' => 'subvalue2',
                'key2.contact2.sub_name' => 'Daniel',
            ],
            $matched
        );
    }

    /**
     * @depends testCreation
     * @param TreeStorage $example
     */
    public function testToArray(TreeStorage $example): void
    {
        $result = $example->toArray();
        $expected_result = [
            'key1' => 'val1',
            'key2' =>
                [
                    'subkey1' => 'subvalue1',
                    'subkey2' => 'subvalue2',
                    'contact2' =>
                        [
                            'first_name' => 'Nestor',
                            'sub_name' => 'Daniel',
                            'last_name' => 'Picado',
                        ],
                ],
            'key3' => 321,
        ];

        self::assertSame($expected_result, $result);
    }

    /**
     * @depends testCreation
     * @param TreeStorage $example
     */
    public function testToObject(TreeStorage $example): void
    {
        $result = $example->toObject();
        $expected_result = (object)[
            'key1' => 'val1',
            'key2' =>
                (object)[
                    'subkey1' => 'subvalue1',
                    'subkey2' => 'subvalue2',
                    'contact2' =>
                        (object)[
                            'first_name' => 'Nestor',
                            'sub_name' => 'Daniel',
                            'last_name' => 'Picado',
                        ],
                ],
            'key3' => 321,
        ];

        self::assertEquals($expected_result, $result);
    }
}

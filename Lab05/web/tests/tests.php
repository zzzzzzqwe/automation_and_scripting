<?php

require_once __DIR__ . '/testframework.php';

$tests = new TestFramework();

function testAddition()
{
    $result = 2 + 2;
    return assertExpression(
        $result === 4,
        'Addition: 2 + 2 = 4 (ok)',
        'Addition: 2 + 2 != 4 (fail)'
    );
}

function testStringConcat()
{
    $str = 'Hello' . ' ' . 'World';
    return assertExpression(
        $str === 'Hello World',
        'String concat: ok',
        'String concat: fail'
    );
}

function testStringLength()
{
    $str = 'Привет';
    return assertExpression(
        mb_strlen($str) === 6,
        'String length: ok',
        'String length: fail'
    );
}

function testArrayContains()
{
    $arr = [1, 2, 3, 4, 5];
    return assertExpression(
        in_array(3, $arr, true),
        'Array contains: ok',
        'Array contains: fail'
    );
}

function testArraySort()
{
    $arr = [5, 3, 4, 1, 2];
    sort($arr);
    return assertExpression(
        $arr === [1, 2, 3, 4, 5],
        'Array sort: ok',
        'Array sort: fail'
    );
}

$tests->add('Addition', 'testAddition');
$tests->add('String concat', 'testStringConcat');
$tests->add('String length', 'testStringLength');
$tests->add('Array contains', 'testArrayContains');
$tests->add('Array sort', 'testArraySort');

$tests->run();

echo $tests->getResult();

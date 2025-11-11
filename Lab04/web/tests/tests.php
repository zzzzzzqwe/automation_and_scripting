<?php

require_once __DIR__ . '/testframework.php';

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../modules/database.php';
require_once __DIR__ . '/../modules/page.php';

$tests = new TestFramework();

/// DATABASE TESTS

// test 1: check database connection
function testDbConnection()
{
    global $config;
    $db = new Database($config['db']['path']);
    return assertExpression($db instanceof Database, 'database connection: success ', 'database connection :fail');
}

// test 2: test count method
function testDbCount()
{
    global $config;
    $db = new Database($config['db']['path']);
    $db->Execute("DELETE FROM page");
    return assertExpression($db->Count("page") === 0, 'Count = 0: ok', 'Count: fail');
}

// test 3: test create method
function testDbCreate()
{
    global $config;
    $db = new Database($config['db']['path']);
    $id = $db->Create("page", ["title" => "Test", "content" => "Content"]);
    return assertExpression(is_int($id) && $id > 0, 'Create: ok', 'Create: fail');
}

// test 4: test read method
function testDbRead()
{
    global $config;
    $db = new Database($config['db']['path']);
    $id = $db->Create("page", ["title" => "ReadTest", "content" => "Content"]);
    $data = $db->Read("page", $id);
    return assertExpression($data['title'] === "ReadTest", 'Read: ok', 'Read: fail');
}

// test 5: test update method
function testDbUpdate()
{
    global $config;
    $db = new Database($config['db']['path']);
    $id = $db->Create("page", ["title" => "Old", "content" => "Old content"]);
    $db->Update("page", $id, ["title" => "New", "content" => "New content"]);
    $data = $db->Read("page", $id);
    return assertExpression($data['title'] === "New", 'Update: ok', 'Update: fail');
}

// test 6: test delete method
function testDbDelete()
{
    global $config;
    $db = new Database($config['db']['path']);
    $id = $db->Create("page", ["title" => "ToDelete", "content" => "Bye"]);
    $db->Delete("page", $id);
    return assertExpression($db->Read("page", $id) === null, 'Delete: ok', 'Delete: fai;');
}

// test 7: test fetch method
function testDbFetch()
{
    global $config;
    $db = new Database($config['db']['path']);
    $db->Execute("DELETE FROM page");
    $db->Create("page", ["title" => "F1", "content" => "C1"]);
    $db->Create("page", ["title" => "F2", "content" => "C2"]);
    $rows = $db->Fetch("SELECT * FROM page");
    return assertExpression(count($rows) >= 2, 'Fetch OK', 'Fetch FAIL');
}

/// PAGE TESTS

// test 8: test render method
function testPageRender()
{
    $tplPath = '/var/www/html/templates/index.tpl';
    file_put_contents($tplPath, "<h1>{{title}}</h1><p>{{content}}</p>");

    $page = new Page($tplPath);
    $html = $page->Render([
        'title' => 'Заголовок',
        'content' => 'Текст'
    ]);

    unlink($tplPath);

    return assertExpression(
        str_contains($html, 'Заголовок') && str_contains($html, 'Текст'),
        'Page render: ok',
        'Page render:  fail'
    );
}

// add tests
$tests->add('Database connection', 'testDbConnection');
$tests->add('table count', 'testDbCount');
$tests->add('data create', 'testDbCreate');
$tests->add('Read method', 'testDbRead');
$tests->add('Update method', 'testDbUpdate');
$tests->add('Delete method', 'testDbDelete');
$tests->add('Fetch method', 'testDbFetch');
$tests->add('Page render', 'testPageRender');

// run tests
$tests->run();

echo $tests->getResult();
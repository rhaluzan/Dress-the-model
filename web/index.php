<?php
date_default_timezone_set("Europe/Ljubljana");
header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"'); //fix for IE

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../vendor/facebook/php-sdk/src/facebook.php';

//use PointOut\CastingController;
use Silex\Application;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Silex\Provider;
use Silex\Provider\FormServiceProvider;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


$app = new Application();
$app['debug'] = true;

// ==============
// Register stuff
// ==============
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\FormServiceProvider(), array());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'translator.messages' => array(),
));
$app->register(new Tobiassjosten\Silex\Provider\FacebookServiceProvider(), array(
    'facebook.app_id'     => '',
    'facebook.secret'     => ''
));
$app->register(new Silex\Provider\SessionServiceProvider());


// ========
// Database
// ========
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options'    => array(
    'driver'        => 'pdo_mysql',
    'host'          => 'localhost',
    'dbname'        => '',
    'user'          => '',
    'password'      => '',
    'charset'       => 'utf8',
    'driverOptions' => array(1002 => 'SET NAMES utf8',),
  ),
));


// =========
// DB TABLES
// =========
$schema = $app['db']->getSchemaManager();

if (!$schema->tablesExist('users')) {
    $users = new Table('users');
    $users->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
    $users->setPrimaryKey(array('id'));
    $users->addColumn('fbid', 'string', array('length' => 200));
    $users->addUniqueIndex(array('fbid'));
    $users->addColumn('name', 'string', array('length' => 80));
    $users->addColumn('link', 'string', array('length' => 200, 'default' => ''));
    $users->addColumn('username', 'string', array('length' => 80, 'default' => ''));
    $users->addColumn('gender', 'string', array('length' => 10, 'default' => ''));
    $users->addColumn('email', 'string', array('length' => 120, 'default' => ''));
    $users->addColumn('notifications', 'string', array('int' => 1, 'default' => 0));
    $users->addUniqueIndex(array('email'));
    $users->addColumn('verified', 'string', array('length' => 20, 'default' => ''));

    $schema->createTable($users);
}

if (!$schema->tablesExist('games')) {
    $games = new Table('games');
    $games->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
    $games->setPrimaryKey(array('id'));
    $games->addColumn('user_fbid', 'string', array('length' => 200));
    $games->addColumn('environment', 'string');
    $games->addColumn('pointsGames', 'float');
    // $games->addColumn('pointsInv', 'float');
    // $games->addColumn('totalPoints', 'float');
    $games->addColumn('timeplayed', 'datetime', array('default' => date('Y-m-d H:i:s')));


    $schema->createTable($users);
}

if (!$schema->tablesExist('invited')) {
    $invited = new Table('invited');
    $invited->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
    $invited->setPrimaryKey(array('id'));
    $invited->addColumn('game_id', 'integer');
    $invited->addColumn('user_fbid', 'string', array('length' => 200));
    $invited->addColumn('fbid', 'string', array('length' => 200));
    $invited->addColumn('name', 'string', array('length' => 80));


    $schema->createTable($invited);
}


// =======
// Routing
// =======
$app->match('/', 'notigo\Controllers\IndexController::indexAction')->method('GET|POST')->bind('IndexController');
$app->match('/environment/{env}', 'notigo\Controllers\IndexController::environmentAction')->method('GET|POST')->bind('environmentAction');

$app->match('/success/{env}', 'notigo\Controllers\IndexController::successAction')->method('GET|POST')->bind('successAction');

$app->match('/leaderboard/{page}', 'notigo\Controllers\IndexController::leaderboardAction')->method('GET|POST')->bind('leaderboardAction')->value('page', '1');


// =======
// API
// =======
$app->match('/api/addGame/{env}/{pointsGames}', 'notigo\Controllers\GameController::addGamePlayAction')->method('GET|POST')->bind('apiAddGame');

$app->match('/api/fetchPlayedEnvsActions', 'notigo\Controllers\GameController::fetchPlayedEnvsActions')->method('GET|POST')->bind('fetchPlayedEnvsActions');

$app->match('/api/addInvites/{invites}', 'notigo\Controllers\GameController::addInvitesAction')->method('GET|POST')->bind('addInvitesAction');

$app->match('/redirect/', function () use ($app) {
    return $app['twig']->render('redirect.twig', array('pagestyle' => 'env-smucanje'));
})->method('GET|POST');

$app->match('/fetchLeaderboardAdmn/', 'notigo\Controllers\GameController::fetchLeaderboardActionAdmn')->method('GET|POST')->bind('fetchLeaderboardAdmn')->value('page', '1');

// =======
// Run app
// =======
$app->run();

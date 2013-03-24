<?php
namespace Werkint;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Igorw\Silex\ConfigServiceProvider,
    Silex\Provider\DoctrineServiceProvider,
    Silex\Provider\TwigServiceProvider,
    Werkint\Service\Packages;

/**
 * Основной класс для настройки приложения
 */
class Bootstrap
{
    protected $app;
    protected $debug;
    protected $respath;

    public function __construct(
        Application $app,
        $respath, $debug = true
    ) {
        $this->app = $app;
        $this->respath = $respath;
        $this->debug = $debug;
    }

    public function init()
    {
        // Настройки проекта
        $config = $this->respath . '/config';
        $config = glob($config . '/*.yml');
        foreach ($config as $file) {
            $this->app->register(new ConfigServiceProvider($file));
        }

        // База данных (настройки в yaml)
        $this->app->register(new DoctrineServiceProvider());

        // Twig
        $this->app->register(new TwigServiceProvider(), [
            'twig.path' => $this->respath . '/views',
        ]);

        $this->initServices();
        $this->initControllers();
        return $this->app;
    }

    /**
     * Инициализирует контроллеры
     */
    protected function initControllers()
    {
        // Главная страница
        $this->app->get('/', function () {
            return $this->app['twig']->render('index.twig');
        });

        // Компиляция по хуку
        $this->app->get('/compile', function () {
            $curdir = realpath(dirname(__FILE__) . '/..');
            $compiler = new ScriptCompiler(
                $curdir . '/src',
                $curdir . '/packages',
                __DIR__ . '/templates'
            );
            $res = $compiler->process();
            echo $res ? 'processed ' . $res . ' packages' : 'error';
        });

        // Список пакетов
        $this->app->get('/packages', function () {
            $list = $this->app['webapp.packages'];
            return $this->app['twig']->render('packages.twig', array(
                'packages' => $list,
            ));
        });

        // Login and registration
        $this->app->get('/login', function () {
            return $this->app['twig']->render('login.twig');
        });
    }

    /**
     * Инициализирует основные службы
     */
    protected function initServices()
    {
        // Пакеты в репозитарии
        $this->app['webapp.packages'] = $this->app->share(function (Application $app) {
            new Packages($app, $this->respath . '/packages');
        });
    }
}
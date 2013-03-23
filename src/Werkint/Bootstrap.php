<?php
namespace Werkint;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Igorw\Silex\ConfigServiceProvider,
    Silex\Provider\DoctrineServiceProvider,
    Silex\Provider\TwigServiceProvider;

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
            return $this->app['twig']->render('packages.twig', array(
                'packages' => [['class' => 'werkint.jquery', 'title' => 'jQuery plugin full']],
            ));
        });
    }

    /**
     * Инициализирует сервисы
     */
    protected function initServices()
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
    }
}
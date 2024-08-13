<?php
declare(strict_types=1);

namespace MA\PHPQUICK;

use Closure;
use MA\PHPQUICK\MVC\View;
use MA\PHPQUICK\Session\Session;
use MA\PHPQUICK\Database\Database;
use MA\PHPQUICK\Http\Responses\Response;
use MA\PHPQUICK\Contracts\ContainerInterface as App;

class Bootstrap
{
    public function __construct(
        private Closure $initializeServices,
        private Closure $initializeRepositories,
        private Closure $middlewareAliases,
        private Closure $middlewareGlobal,
        private ?Closure $exceptionHandler = null,
        private ?Closure $initializeDomain = null,
        private ?Closure $initializeDatabase = null,
        private ?Closure $initializeConfig = null,
        private ?Closure $httpExceptionHandler = null,
        private ?Closure $customBoot = null
    ) {}

    /**
     * Bootstraps the application by executing the initialization methods.
     *
     * @param Container $container
     * @return Container
     */
    public function boot(App $app): Container
    {
        $this->setExceptionHandler($app);
        $this->registerCoreInstances($app);
        $this->initializeConfig($app);
        $this->initializeDatabase($app);
        $this->initializeDomain($app);
        $this->initializeRepositories($app);
        $this->initializeServices($app);
        $this->initializeErrorViews($app);
        $this->setHttpExceptionHandler($app);
        $this->initializeSession($app);
        $this->initializeMiddleware($app);
        $this->customBootMethods($app);
        return $app;
    }

    /**
     * Initializes the configuration by loading the config file and injecting it into the container.
     *
     * @param Container $container
     * @return void
     */
    private function initializeConfig(App $app): void
    {
        $config = new Config(require base_path('config/config.php'));
        $app->instance('config', $config);
        $app->instance(Config::class, $config);
        if($this->initializeConfig) {
            ($this->initializeConfig)($config);
        }
    }

    /**
     * Initializes the database connection and injects it into the container.
     *
     * @param Container $container
     * @return void
     */
    private function initializeDatabase(App $app): void
    {
        $app->singleton(\PDO::class, fn() => Database::getConnection());
        if($this->initializeDatabase){
            ($this->initializeDatabase)($app->get(\PDO::class));
        }
    }

    private function initializeDomain(App $app): void
    {
        if($this->initializeDomain){
            ($this->initializeDomain)($app);
        }
    }

    /**
     * Initializes the repositories by invoking the provided closure.
     *
     * @param Container $container
     * @return void
     */
    private function initializeRepositories(App $app): void
    {
        ($this->initializeRepositories)($app);
    }

    /**
     * Initializes the services by invoking the provided closure.
     *
     * @param Container $container
     * @return void
     */
    private function initializeServices(App $app): void
    {
        ($this->initializeServices)($app);
    }

    /**
     * Initializes the error views by invoking the provided closure.
     *
     * @param Container $container
     * @return void
     */
    private function initializeErrorViews(App $app): void
    {
        $bindings = $app->get('config')->get('error_pages', []);
        foreach ($bindings as $id => $view) {
            if($view) $app->bind((string)$id, fn() => View::make($view));
        }
    }

    /**
     * Sets the HTTP exception handler in the application.
     *
     * @param Container $container
     * @return void
     */
    private function setHttpExceptionHandler(App $app): void
    {
        $app->instance('http.exception.handler', $this->httpExceptionHandler);
    }

    /**
     * Registers core instances such as Container, Application, and Response in the container.
     *
     * @param Container $container
     * @return void
     */
    private function registerCoreInstances(App $app): void
    {
        $app->instance(Container::class, $app);
        $app->instance(Application::class, $app);
        $app->instance(Response::class, new Response());
    }

    private function initializeSession(App $app): void
    {
        $app->instance('session', new Session());
    }

    private function customBootMethods(App $app): void
    {
        if ($this->customBoot) {
            ($this->customBoot)($app);
        }
    }
    
    private function initializeMiddleware(App $app){
        // Set global middleware
        $app->instance('middleware.global', ($this->middlewareGlobal)());
        
        // Set middleware aliases
        $app->instance('middleware.aliases', ($this->middlewareAliases)());
    }

    private function setExceptionHandler(App $app): void
    {
        set_exception_handler($this->exceptionHandler);
    }
}

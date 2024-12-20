<?php

namespace auroch\phpmvc;

use auroch\phpmvc\db\Database;
use auroch\phpmvc\db\DbModel;

class Application
{
    public static string $ROOT_DIR;

    public string $layout = 'main';
    public string $userClass;

    public Router $router;
    public Request $request;
    public Response $response;

    public Database $db;
    public ?DbModel $user;

    public static Application $app;

    public ?Controller $controller = null;

    public Session $session;

    public View $view;

    public function __construct(string $rootPath, array $config)
    {
        $this->userClass = $config['userClass'];
        self::$ROOT_DIR = $rootPath;
        self::$app = $this;
        $this->request = new Request();
        $this->response = new Response();
        $this->session = new Session();
        $this->view = new View();
        $this->db = new Database($config['db']);
        $this->router = new Router(
            request: $this->request,
            response: $this->response
        );

        $primaryValue = $this->session->get('user');
        if ($primaryValue) {
            $primaryKey = (new $this->userClass)->primaryKey();
            $this->user = $this->userClass::findOne([$primaryKey => $primaryValue]);
        } else {
            $this->user = null;
        }

    }

    public static function isGuest(): bool
    {
        return self::$app->user == null;
    }

    public function run()
    {
        try {
            echo $this->router->resolve();
        } catch (\Exception $e)
        {
            $this->response->setStatusCode($e->getCode());
            echo $this->view->renderView('_error', ['exception' => $e]);
        }
    }

    public function getController(): Controller
    {
        return $this->controller;
    }

    public function setController(Controller $controller): void
    {
        $this->controller = $controller;
    }

    public function login(DbModel $user)
    {
        $this->user = $user;
        $primaryKey = $user->primaryKey();

        $primaryValue = $user->{$primaryKey};

        $this->session->set('user', $primaryValue);

        return true;
    }

    public function logout()
    {
        $this->user = null;
        $this->session->remove('user');
    }
}
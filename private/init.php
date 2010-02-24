<?php

    /* $Id$ */

    /* Определяем текущую директорию */
    $cur_dir = dirname(__FILE__);
    /* И загружаем класс инициализации */
    require_once $cur_dir . '/lib/Init.php';
    require_once $cur_dir . '/lib/Init/Exception.php';

    /* Устанавливаем основные пути в системе */
    Init::define('DS',          DIRECTORY_SEPARATOR);
    Init::define('ROOT',        dirname(realpath(__FILE__)));
    Init::define('APP',         ROOT . DS . 'app');
    Init::define('LIB',         ROOT . DS . 'lib');
    Init::define('THIRD_PARTY', ROOT . DS . 'third_party');
    Init::define('TMP',         ROOT . DS . 'tmp');
    Init::define('LOGS',        TMP . DS . 'logs');
    Init::define('VIEWS',       APP . DS . 'View');
    Init::define('LAYOUTS',     VIEWS . DS . 'layouts');
    Init::define('ELEMENTS',    VIEWS . DS . 'elements');

    /**
    * Включаем загрузку файлов из директорий приложения, библиотеки и сторонних
    * скриптов
    */
    Init::setIncludePath( array(ROOT, APP, LIB, THIRD_PARTY) );

    Init::define('CR',   "\r");
    Init::define('LF',   "\n");
    Init::define('CRLF', CR . LF);

    Init::setLocale('ru_RU.UTF8');
    Init::setTimezone('Europe/Moscow');

    /* Подключаем зендовский автозагрузчик классов */
    require_once 'Zend/Loader/Autoloader.php';
    Zend_Loader_Autoloader::getInstance()
        ->setFallbackAutoloader(true)
        ->suppressNotFoundWarnings(true);

    /* Загружаем конфигурацию */
    $config = require_once 'config.php';
    $routes = require_once 'short_routes.php';
    
    /* Добавляем в конфиг права доступа */
    $config['permissions'] = $routes['permissions'];
    
    /* Перебираем маршруты и приводим их к полному виду */
    foreach ($routes['routes'] as $route)
    {
        /* Очищаем переменные, чтобы не осталось данных от других маршрутов */
        $pattern = null;
        $handler = null;
        
        /* Берём последний элемент - тип маршрута */
        $type = array_pop($route); 
        
        /* В зависимости от типа выбираем шаблон */
        switch ($type)
        {
            case Mvc_Router::ROUTE_STATIC:
                $pattern = array_shift($route);
                break;
                
            case Mvc_Router::ROUTE_REGEX:
                $pattern['regex']  = array_shift($route);
                $pattern['params'] = array_shift($route);
                break;
        }   
        
        /* Выбираем параметры обработчика */
        $handler['controller'] = array_shift($route);
        $handler['action']     = array_shift($route);
        
        /* Если осталось больше двух элементов, сейчас - параметры запроса */
        if (sizeof($route) > 1) {
            $handler['params'] = array_shift($route);
        }
        
        /* Берём алиас */
        $alias = array_shift($route);
        
        /* Сохраняем маршрут в конфиге */
        $config['routes'][] = array(
            'alias'   => $alias,
            'type'    => $type,
            'pattern' => $pattern,
            'handler' => $handler
        );
    }
    
    switch ($config['mode']) {
        /* Если включён режим отладки, то... */
        case 'debug':
            /* Включаваем вывод всех-всех ошибок */
            Init::setErrorReporting(E_ALL | E_STRICT);
            /* Включаем вывод ошибок */
            Init::displayErrors(true);
            /* И отключаем запись ошибок в логи */
            Init::logErrors(false);

            /**
            * Подключаем перехватчик ошибок, который будет выводить их в удобном
            * виде
            */
            Init::setupErrorHandler();
            /* Включаем assert'ы */
            Init::enableAssertions();
            break;

        /* Если мы в работчем режиме, то... */
        case 'production':
            /* Включаем запись ошибок в лог */
            Init::logErrors(true);
            /* Устанавливаем, куда записывать ошибки */
            Init::setErrorLog(LOGS . DS . 'php_error_log.txt');

            /* Оставляем перехват только важных ошибок */
            Init::setErrorReporting(E_ALL ^ E_NOTICE);
            /* И выключаем вывод ошибок */
            Init::displayErrors(false);

            /**
            * @todo Устанавливать user-friendly перехватчик ошибок?
            */
            break;
    }

    /* Инициализируем менеджер ресурсов */
    Resources::create($config);

?>
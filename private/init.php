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
    $routes = require_once 'routes.php';

    /* Приводим маршруты к полном виду */
    $config['routes'] = Mvc_Router::expandRoutes($routes['routes']);
    /* Добавляем в конфиг права доступа */
    $config['permissions'] = $routes['permissions'];

    switch ($config['mode']) {
        /* Если включён режим отладки, то... */
        case 'debug':
            /* Включаваем вывод всех-всех ошибок */
            Init::setErrorReporting(E_ALL);
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
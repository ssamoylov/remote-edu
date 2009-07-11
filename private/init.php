<?php

    /* $Id$ */
    
    $cur_dir = dirname(__FILE__);
    require_once $cur_dir . '/lib/Init.php';
    require_once $cur_dir . '/lib/Init/Exception.php';
        
    Init::define('DS',          DIRECTORY_SEPARATOR);
    Init::define('ROOT',        dirname(realpath(__FILE__)));
    Init::define('APP',         ROOT . DS . 'app');
    Init::define('VIEWS',       APP . DS . 'View');
    Init::define('LIB',         ROOT . DS . 'lib');
    Init::define('THIRD_PARTY', ROOT . DS . 'third_party');
          
    Init::setIncludePath( array(APP, LIB, THIRD_PARTY) );
    Init::setErrorReporting(E_ALL | E_STRICT);
    /**
    * @todo Стратегия вывода ошибок: режим отладки вкл/выкл?
    */
    Init::setupErrorHandler();
    Init::enableAssertions();

    Init::define('CR',   "\r");
    Init::define('LF',   "\n");   
    Init::define('CRLF', CR . LF);
    
    Init::setLocale('ru_RU.UTF8');
    Init::setTimezone('Europe/Moscow');
    
    require_once 'Zend/Loader/Autoloader.php';
    Zend_Loader_Autoloader::getInstance()
        ->setFallbackAutoloader(true)
        ->suppressNotFoundWarnings(false);
        
    /**
    * @todo Как подгружать настройки?
    * @todo Использовать ли реестр объектов для хранения конфигурации, объекта
    *       для работы с БД и т.д.?
    */
    
    $config = require_once 'config.php';
    Resources::create($config);
            
?>
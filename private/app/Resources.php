<?php

    /* $Id$ */
    
    /**
    * Класс менеджера ресурсов приложения.
    */
    class Resources extends Resources_Abstract {
        /**
        * Создание экземпляра класса.
        * 
        * @param  array $config Массив с конфигурацией.
        * @return Resources
        */
        public static function create(array $config = array()) {
            $instance = new self($config);
            return self::setInstance($instance);
        }
        
        /**
        * Инициализация диспетчера запросов.
        * 
        * @return Mvc_Dispatcher
        */
        protected function get_dispatcher() {
            $dispatcher = Mvc_Dispatcher::create();
            
            return $dispatcher;
        }
        
        /**
        * Инициализация роутера.
        * 
        * @return Mvc_Router
        */
        protected function get_router() {
            $config = $this->_config['routes'];
            
            $router = Mvc_Router::create($config);
            
            return $router;
        }
        
        /**
        * Инициализация объекта для работы с БД.
        * 
        * @return Db_Pdo
        */
        protected function get_db() {
            $config = $this->_config['db'];
            
            $db = Db_Pdo::create(
                $config['dsn'], $config['user'], $config['passwd'],
                $config['options']
            );
            
            $db->exec('SET NAMES utf8');
            
            return $db;
        }
        
        /**
        * Инициализация объекта авторизации.
        * 
        * @return Auth
        */
        protected function get_auth() {
            $config = $this->_config['auth'];
            
            $auth = Auth::create($config['salt']);
            
            return $auth;    
        }
        
        protected function get_postman() {
            $config = $this->_config['postman'];
            
            $postman = Postman::create(
                $this->_config['base_url'],
                $config['from_email'], $config['from_name']
            );   
            
            return $postman; 
        }
        
        /**
        * Инициализация обработчика шаблонов.
        * 
        * @return Mvc_View
        */
        protected function get_view() {
            $view = Mvc_View::create(VIEWS . DS, LAYOUTS . DS, ELEMENTS . DS);
            return $view;
        }
    }
        
?>
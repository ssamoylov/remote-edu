<?php                               

    /* $Id$ */
    
    return array(
        'db' => array
        (
            'dsn'     => 'mysql:host=localhost;dbname=remote-edu',
            'user'    => 'root',
            'passwd'  => '',
            'options' => array(
                /* ����� ������ ���������� ��� ������� */
                Db_Pdo::ATTR_ERRMODE => Db_Pdo::ERRMODE_EXCEPTION
            )
        ),
        
        'routes' => array
        (
            array
            (
                'type'    => Mvc_Router::ROUTE_STATIC,
                'pattern' => '/static-route',
                'handler' => array
                (
                    'controller' => 'index',
                    'action'     => 'static',
                    'params'     => array()
                )
            ),
            
            array
            (
                'type'    => Mvc_Router::ROUTE_REGEX,
                'pattern' => array
                (
                    'regex'  => '/regex-route-([0-9]+).*',
                    'params' => array('number')
                ),
                'handler' => array
                (
                    'controller' => 'index',
                    'action'     => 'regex',
                    'params'     => array('default-param' => 'default-value')
                )
            )
        )
    );
            
?>
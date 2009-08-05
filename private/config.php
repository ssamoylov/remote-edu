<?php                               

    /* $Id$ */
    
    return array(
        /* ����� ������ (debug/production) */
        'mode' => 'debug',
        
        /* ������� ����� */
        'base_url' => 'http://remote-edu',
        
        /* ��������� ���������� � �� */
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
        
        /* ��������� ����������� */
        'auth' => array
        (
            /* ��������� ������������������ �������� ��� ���������� */
            'salt' => 'Ix8i8AQrEfFtgi14XupbT4kxHM511ZDFA'
        ),
        
        /* �������� */
        'routes' => array
        (
            /* ��������� ��������� */
            array(
                'type'    => Mvc_Router::ROUTE_REGEX,
                'pattern' => array
                (
                    'regex'  => '/activate_student/([0-9]+)/([0-9a-z]{32}).*',
                    'params' => array('user_id', 'code')
                ),
                'handler' => array
                (
                    'controller' => 'users',
                    'action'     => 'activate_student'
                )
            ),
            
            /* ��������� ���������� */
            array(
                'type'    => Mvc_Router::ROUTE_REGEX,
                'pattern' => array
                (
                    'regex'  => '/activate_employee/([0-9]+)/([0-9a-z]{32}).*',
                    'params' => array('user_id', 'code')
                ),
                'handler' => array
                (
                    'controller' => 'users',
                    'action'     => 'activate_employee'
                )
            )
        ),
        
        /* ��������� �������� �������� ��������� */
        'postman' => array
        (
            /* ����� ����������� ����� */
            'from_email' => 'robot@remote-edu.localhost',
            /* ��� ����������� */
            'from_name'  => '�����' 
        )
    );
            
?>
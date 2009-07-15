<?php

    /* $Id$ */

    /**
    * ����� ��� ������ � ��. �������� ������� ��� PDO.
    * 
    * @link http://www.php.net/manual/en/book.pdo.php
    */
    class Db_Pdo extends PDO {
        /**
        * �����-����������� ������.
        * 
        * @param  string $dsn            ���������� ��� ���������� � ��.
        * @param  string $username       ��� ������������.
        * @param  string $password       ������.
        * @param  array  $driver_options �����, ����������� ��� ���������.
        * @return void
        */
        public function __construct
        (
            $dsn, $username = null, $password = null,
            array $driver_options = array()
        )
        {
            /**
            * � ����� �������� ��������� �������� - ��� ������ ��������, �����
            * ������������� ������ ��� ������������ ������ PDOStatement.
            */
            $driver_options[self::ATTR_STATEMENT_CLASS] = array(
                'Db_Pdo_Statement'
            );
            parent::__construct($dsn, $username, $password, $driver_options);
        }
        
        /**
        * �������� ���������� ������.
        * 
        * @param  string $dsn            ���������� ��� ���������� � ��.
        * @param  string $username       ��� ������������.
        * @param  string $password       ������.
        * @param  array  $driver_options �����, ����������� ��� ���������.
        * @return Db_Pdo
        */
        public static function create
        (
            $dsn, $username = null, $password = null,
            array $driver_options = array()
        )
        {
            return new self($dsn, $username, $password, $driver_options);
        }
    }

?>
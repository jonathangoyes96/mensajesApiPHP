<?php

class DbConnect
{
    //Variable para almacenar la ruta a la base de datos
    private $con;

    function __construct()
    {

    }

    /*
     * Metodo que retorna la conexion establecida con la base de datos
     */
    function connect()
    {
        //Incluyendo el archivo Constans.php que contiene los datos de configuracion de la base de datos
        include_once dirname(__FILE__) . '/Constants.php';

        // Conectando a la base de datos mysql
        $this->con = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

        // Verificando si ocurrio un error mientras se conectaba a la base de datos
        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
            return null;
        }

        // Retornar la conexion a la base de datos
        return $this->con;
    }

}
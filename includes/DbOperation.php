<?php

class DbOperation
{
    private $con;

    /*
     * Almacenando la conexion de la base de datos en la variable $con
     */
    function __construct()
    {
        require_once dirname(__FILE__) . '/DbConnect.php';
        $db = new DbConnect();
        $this->con = $db->connect();
    }

    // Metodo para registrar un nuevo usuario en la base de datos
    function registerUser($name, $email, $pass, $gender)
    {
        if (!$this->isUserExist($email)) {
            $password = md5($pass);
            $stmt = $this->con->prepare("INSERT INTO users (name, email, password, gender) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $password, $gender);
            if ($stmt->execute()){
                return USER_CREATED;
            }
            return USER_CREATION_FAILED;
        }
        // Si no entro en el if significa que el usuario ya fue creado en la base de datos
        return USER_EXIST;
    }

    //Method for user login
    function userLogin($email, $pass)
    {
        $password = md5($pass);
        $stmt = $this->con->prepare("SELECT id FROM users WHERE email = ? AND password = ?");
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $stmt->store_result();
        // num_rows retorna 1 si se encontro el usuario en la base de datos
        return $stmt->num_rows > 0;
    }

    /*
     * Metodo para enviar un mensaje a otro usuario
     * Si se envio el mensaje retorna true de lo contrario retorna false
     */
    function sendMessage($from, $to, $title, $message)
    {
        $stmt = $this->con->prepare("INSERT INTO messages (from_users_id, to_users_id, title, message) VALUES (?, ?, ?, ?);");
        $stmt->bind_param("iiss", $from, $to, $title, $message);
        if ($stmt->execute())
            return true;
        return false;
    }

    //Method to update profile of user
    function updateProfile($id, $name, $email, $pass, $gender)
    {
        $password = md5($pass);
        $stmt = $this->con->prepare("UPDATE users SET name = ?, email = ?, password = ?, gender = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $email, $password, $gender, $id);
        if ($stmt->execute())
            return true;
        return false;
    }

    /**
     * Metodo para obtener todos los mensajes de un usuario por id
     * @param $messages[] retorna un array de elementos que contiene todos los datos del mensaje
     */
    function getMessages($userid)
    {
        $stmt = $this->con->prepare("SELECT messages.id, (SELECT users.name FROM users WHERE users.id = messages.from_users_id) as `from`, (SELECT users.name FROM users WHERE users.id = messages.to_users_id) as `to`, messages.title, messages.message, messages.sentat FROM messages WHERE messages.to_users_id = ?;");
        $stmt->bind_param("i", $userid);
        $stmt->execute();
        $stmt->bind_result($id, $from, $to, $title, $message, $sent);

        $messages = array();

        while ($stmt->fetch()) {
            $temp = array();

            $temp['id'] = $id;
            $temp['from'] = $from;
            $temp['to'] = $to;
            $temp['title'] = $title;
            $temp['message'] = $message;
            $temp['sent'] = $sent;

            array_push($messages, $temp);
        }

        return $messages;
    }

    //Method to get user by email
    function getUserByEmail($email)
    {
        $stmt = $this->con->prepare("SELECT id, name, email, gender FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($id, $name, $email, $gender);
        $stmt->fetch();
        $user = array();
        $user['id'] = $id;
        $user['name'] = $name;
        $user['email'] = $email;
        $user['gender'] = $gender;
        return $user;
    }

    //Method to get all users
    function getAllUsers(){
        $stmt = $this->con->prepare("SELECT id, name, email, gender FROM users");
        $stmt->execute();
        $stmt->bind_result($id, $name, $email, $gender);
        $users = array();
        while($stmt->fetch()){
            $temp = array();
            $temp['id'] = $id;
            $temp['name'] = $name;
            $temp['email'] = $email;
            $temp['gender'] = $gender;
            array_push($users, $temp);
        }
        return $users;
    }

    // Metodo que verifica si el email ya existe en la base de datos
    function isUserExist($email)
    {
        $stmt = $this->con->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        // num_rows devuleve un conjunto de filas que retorno la consulta hecha en la base dde datos
        return $stmt->num_rows > 0;
    }
}
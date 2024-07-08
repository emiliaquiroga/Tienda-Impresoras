<?php

class Usuario{
    public $id;
    public $mail;
    public $usuario;
    public $password;
    public $perfil;
    public $foto;
    public $fecha_de_alta;
    public $fecha_de_baja;
    public static $perfilesValidos = ['cliente', 'empleado', 'admin'];

    public function crearUsuario(){
        // Validar perfil
        if (!in_array($this->perfil, self::$perfilesValidos)) {
            throw new Exception('Perfil de usuario no válido.');
        }
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $claveHash = password_hash($this->password, PASSWORD_DEFAULT);
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (mail, usuario, password, perfil, foto) VALUES (:mail, :usuario, :password, :perfil, :foto)");
        $consulta->bindValue(':mail', $this->mail, PDO::PARAM_STR); 
        $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
        $consulta->bindValue(':password', $claveHash, PDO::PARAM_STR); 
        $consulta->bindValue(':perfil', $this->perfil, PDO::PARAM_STR); 
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
        $consulta->execute();
        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function GuardarImagenUsuario($ruta, $uploadedFile, $usuario, $perfil, $fecha) {
        if (!file_exists($ruta)) {
            mkdir($ruta, 0777, true);
        }

        $filename = $usuario . "_" . $perfil ."_".$fecha."." . pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $filepath = $ruta . DIRECTORY_SEPARATOR . $filename;

        $uploadedFile->moveTo($filepath);

        return $filename;
    }
}
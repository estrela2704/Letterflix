<?php 


    require_once('globals.php');
    require_once('db.php');
    require_once("models/User.php");
    require_once("models/Message.php");
    require_once("dao/UserDAO.php");

    $message = new Message($BASE_URL);

    $userDao = new UserDAO($conn, $BASE_URL);

    $type = filter_input(INPUT_POST, "type");

    if($type === "update"){

        $user = $userDao->verifyToken();

        $name = filter_input(INPUT_POST, "name");
        $lastname = filter_input(INPUT_POST, "lastname");
        $email = filter_input(INPUT_POST, "email");
        $bio = filter_input(INPUT_POST, "bio");

        $user->name = $name;
        $user->lastname = $lastname;
        $user->email = $email;
        $user->bio = $bio;

        if(isset($_FILES["image"]) && !empty($_FILES["image"]["tmp_name"])){

            $image = $_FILES["image"];
            $imageTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            $jpgArray = ['image/jpeg', 'image/jpg'];

            if(in_array($image["type"], $imageTypes)){

                if(in_array($image["type"], $jpgArray)){
                    $imageFile = imagecreatefromjpeg($image["tmp_name"]);
                } else {
                    $imageFile = imagecreatefrompng($image["tmp_name"]);
                }

                $imageName = $user->generateImageName();
            
                imagejpeg($imageFile, "img/users/" . $imageName, 100);

                $user->image = $imageName;
            } else {
                $message->setMessage("Tipo de imagem não suportada, insira png ou jpg!", "error", "back");
            }

        }

        $userDao->update($user);


    } else if($type === "changepassword"){

        $userData = $userDao->verifyToken();

        $password = filter_input(INPUT_POST, "password");
        $confirmpassword = filter_input(INPUT_POST, "confirmpassword");

        if($password === $confirmpassword){

            $user = new User();

            $finalPassword = $user->generatePassword($password);

            $user->password = $finalPassword;
            $user->id = $userData->id;

            $userDao->changePassword($user);

        } else {
            $message->setMessage("As senhas não são iguais", "error", "back");
        }

    } else {
        $message->setMessage("Informações inválidas", "error", "index.php");

    }
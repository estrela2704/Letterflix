<?php 


    require_once('globals.php');
    require_once('db.php');
    require_once("models/Movie.php");
    require_once("models/Message.php");
    require_once("dao/MovieDAO.php");
    require_once("dao/UserDAO.php");

    $message = new Message($BASE_URL);

    $movieDAO = new MovieDAO($conn, $BASE_URL);
    $userDao = new UserDAO($conn, $BASE_URL);

    $type = filter_input(INPUT_POST, "type"); 

    $userData = $userDao->verifyToken();

    if($type === "create") {

        $title = filter_input(INPUT_POST, "title"); 
        $description = filter_input(INPUT_POST, "description"); 
        $trailer = filter_input(INPUT_POST, "trailer"); 
        $category = filter_input(INPUT_POST, "category"); 
        $length = filter_input(INPUT_POST, "length"); 
        $id = $userData->id;

        $movie = new Movie();

        if(!empty($title) && !empty($description) && !empty($category)) {

            $movie->title = $title;
            $movie->description = $description;
            $movie->trailer = $trailer;
            $movie->category = $category;
            $movie->length = $length;
            $movie->users_id = $id;

            
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
    
                    $imageName = $movie->generateImageName();
                
                    imagejpeg($imageFile, "img/movies/" . $imageName, 100);
    
                    $movie->image = $imageName;
                } else {
                    return $message->setMessage("Tipo de imagem não suportada, insira png ou jpg!", "error", "back");
                }
    
            }
            $movieDAO->create($movie);

        } else {
            $message->setMessage("Você precisa adicionar no minimo o título, descrição e categoria!", "error", "back");
        }


    } else if($type === "delete") {

        $id = filter_input(INPUT_POST, "id"); 

        $movie = $movieDAO->findById($id);

        if($movie){
            if($movie->users_id === $userData->id){
                $movieDAO->destroy($id);
            } else {
                $message->setMessage("Informações inválidas", "error", "index.php");
            }
        } else {
            $message->setMessage("Filme não encontrado", "error", "back");
        }

    } else if($type === "update") {

        
        
        $id = filter_input(INPUT_POST, "id");
        $title = filter_input(INPUT_POST, "title"); 
        $description = filter_input(INPUT_POST, "description"); 
        $trailer = filter_input(INPUT_POST, "trailer"); 
        $category = filter_input(INPUT_POST, "category"); 
        $length = filter_input(INPUT_POST, "length"); 
        $movieData = $movieDAO->findById($id);

        $movie = new Movie();

        $movie->title = $title;
        $movie->description = $description;
        $movie->trailer = $trailer;
        $movie->category = $category;
        $movie->length = $length;
        $movie->id = $id;
                
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

                $imageName = $movie->generateImageName();
            
                imagejpeg($imageFile, "img/movies/" . $imageName, 100);

                $movie->image = $imageName;
            } else {
                return $message->setMessage("Tipo de imagem não suportada, insira png ou jpg!", "error", "back");
            }

        } else {
            $movie->image = $movieData->image;
        }

        $movieDAO->update($movie);

    } else {
        $message->setMessage("Informações inválidas", "error", "index.php");
    }
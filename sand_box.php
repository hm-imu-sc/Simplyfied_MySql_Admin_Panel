<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>SAND BOX</title>
        <style>
            * {
                padding: 0;
                margin: 0;
                background-color: black;
                color: aqua;
                font-family: 'Courier New', Courier, monospace;
                font-size: 20px;
                text-decoration: none;
            }
        </style>
    </head>
    <body>
        <?php

            use App\Controllers\Table;
            use App\Support\MySql;

            include_once "vendor/autoload.php";

            echo "<pre>";
            
            $database = new MySql();

            $file_name = "sand_box.php";
            $to = MEDIAFILES_DIR."/{$file_name}";
            echo BASE_DIR."/".$to;

            echo "</pre>";  
        ?>
        <!-- <input type="date" value="14-12-2021"> -->
    </body>
</html>
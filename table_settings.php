<?php
    include_once "vendor/autoload.php";

    use App\Controllers\Table;
    use App\Support\MySql;

    $table_name = null;
    $table = null;
    $database = new MySql();

    if (isset($_GET["table_name"]) && !empty($_GET["table_name"])) {
        $table_name = $_GET["table_name"];
        $table = new Table($table_name);
    }
    else {
        header("Location:index.php");
    }

    if (isset($_POST["save"])) {
        $form_data = $table->extract_form(post:$_POST);
        $table->mark_file_fields($table_name, $form_data);
        header("Location:index.php");
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="assets/fonts/font-awesome/css/all.css">
        <link rel="stylesheet" href="assets/css/table_settings.css">
        <title>Table Settings</title>
    </head>
    <body>
        
        <a class="back" href="index.php">
            <i class="fad fa-long-arrow-left"></i>
            <!-- <span>Back</span>  -->
        </a>

        <h1>
            Settings:
            <span><?php echo $table_name; ?></span>
        </h1>

        <div class="hr"></div>

        <form action="table_settings.php?table_name=<?php echo $table_name; ?>" method="POST">

            <input type="submit" name="save" value="Save">

            <?php
                $table_structure = $table->get_structure();

                foreach ($table_structure as $field_name => $field_structure) {
                    echo "<div class=\"input_group\">";
                    
                    $status = "";
                    $value = 0;

                    if ($field_structure["type"] == "file") {
                        $status = "active";
                        $value = 1;
                    }

                    echo "
                        <label for=\"{$field_name}\">
                            <span class=\"status {$status}\" for=\"{$field_name}\"></span>
                            <span class=\"field_name\">{$field_name}</span>
                        </label>
                        <input id=\"{$field_name}\" type=\"number\" name=\"{$field_name}\" value=\"$value\" hidden>
                    ";

                    echo "</div>";
                }
            ?>

        </form>

        <script src="assets/js/jquery-3.6.0.min.js"></script>
        <script src="assets/js/table_settings.js"></script>
    </body>
</html>
<?php
    include_once "vendor/autoload.php";
    use App\Controllers\Table;

    $table = null;
    $table_name = null;

    if (isset($_GET["table_name"]) && !empty($_GET["table_name"])) {
        $table_name = $_GET["table_name"];
        $table = new Table($table_name);
    }

    if ($table == null) {
        header("Location:index.php");
        exit();
    }

    if (isset($_POST["insert"]) && $table != null) {
        $form_data = $table->extract_form(post:$_POST);
        $primary_info = $table->insert($form_data);
        if (Table::has_file($_FILES)) {
            $table->upload($_FILES, $primary_info);
        }
        header("Location:show_table.php?table_name={$table_name}");
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="assets/fonts/font-awesome/css/all.css">
        <link rel="stylesheet" href="assets/css/insert.css">
        <title>Insert: <?php echo $table_name; ?></title>
    </head>
    <body>

        <a class="back" href="show_table.php?table_name=<?php echo $table_name; ?>">
            <i class="fad fa-long-arrow-left"></i>
            <!-- <span>Back</span>  -->
        </a>

        <h1>Insert Into: <?php echo $table_name ?></h1> 

        <div class="hr"></div>

        <form action="insert.php?table_name=<?php echo $table_name ?>" method="POST" enctype="multipart/form-data">
            
            <?php
                $table_structure = $table->get_structure();

                $hidden = [
                    "id"=> true,
                    "time_created"=> true,
                    "time_updated"=> true,
                    "trash"=> true,
                    "status"=> true,
                ];

                foreach ($table_structure as $field_name => $field_structure) {
                    if (isset($hidden[$field_name])) {
                        continue;
                    }
                    $type = $field_structure["type"];
                    $foreign_key_values = null;

                    // echo "{$field_name}: {$field_structure['type']}<br>";

                    if ($field_structure["foreign_key"]["status"]) {
                        $type = "select";
                        $foreign_key_values = $table->get_foreign_key_values();
                    }
                    else {
                        $numbers = [
                            "int",
                            "tinyint",
                            "decimal",
                            "float",
                        ];

                        foreach ($numbers as $number_type) {
                            if ($field_structure["type"] == $number_type) {
                                $type = "number";
                            }
                        }

                        if ($field_structure["type"] == "varchar" || $field_structure["type"] == "double") {
                            $type = "text";
                        }
                        else if ($field_structure["type"] == "text") {
                            $type = "textarea";
                        }
                        else if ($field_structure["type"] == "date" || $field_structure["type"] == "time" || $field_structure["type"] == "datetime") {
                            $type = "date";
                        }                 
                    }

                    // echo "{$field_name}: {$type}<br>";

                    ?>
                    <div class="input_group">
                        <label for="<?php echo $field_name; ?>"><?php echo $field_name.":"; ?></label>
                        <?php
                            if ($type == "number" || $type == "text") {
                                echo "<input id=\"{$field_name}\" type=\"{$type}\" name=\"{$field_name}\" ";
                                echo " value=\"{$field_structure['default_value']}\"";
                                echo $field_structure["nullable"] ? ">" : "required>";
                            }
                            else if ($type == "date") {
                                echo "<input id=\"{$field_name}\" type=\"{$type}\" name=\"{$field_name}\" ";
                                echo $field_structure["nullable"] ? ">" : "required>";
                            }
                            else if ($type == "textarea") {
                                echo "<textarea id=\"{$field_name}\" name=\"{$field_name}\" row=\"3\"></textarea>";
                            }
                            else if ($type == "select") {
                                echo "<select name=\"{$field_name}\">";
                                foreach ($foreign_key_values[$field_name] as $field_value) {
                                    echo "<option value=\"{$field_value}\">{$field_value}</option>";
                                }
                                echo "</select>";
                            }
                            else {
                                echo "<input type=\"{$type}\" name=\"{$field_name}\">";
                            }
                        ?>
                    </div>
                    <?php
                }
            ?>

            <!-- <input type="file" name="photo"> -->

            <input type="submit" name="insert" value="Insert">
        </form>
        
        <script src="assets/js/insert.js"></script>
        <script src="assets/js/jquery-3.6.0.min.js"></script>
    </body>
</html>
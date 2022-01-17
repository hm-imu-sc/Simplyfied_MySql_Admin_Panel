<?php

    use App\Controllers\Table;

    include_once "vendor/autoload.php";

    if (isset($_GET["table_name"])) {

        $table_name = $_GET["table_name"];
        $pri_info = [];
    
        foreach ($_GET as $field => $value) {
            if ($field != "table_name") {
                $pri_info[$field] = $value;
            }
        }

        $table = new Table($table_name);
        $table->delete($pri_info);

        header("Location:show_table.php?table_name={$table_name}");
    }
    else {
        header("Location:index.php");
    }

?>
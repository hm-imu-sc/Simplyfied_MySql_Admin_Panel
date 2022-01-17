
<?php
    include_once "vendor/autoload.php";  
    use App\Controllers\Table;

    $table_name = null;

    if (isset($_GET["table_name"])) {
        $table_name = $_GET["table_name"];
    }
    
    if ($table_name == null) {
        header("Location:index.php");
    }

    $table = new Table($table_name);
    $table_data = $table->get_all();
    $hidden = [
        "time_created" => true,
        "time_updated" => true,
        "trash" => true,
        "status" => true,
    ]
?>

<!DOCTYPE html>
<html lang="en">
    
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="assets/fonts/font-awesome/css/all.css">
        <link rel="stylesheet" href="assets/css/show_table.css">
        <title><?php echo $table_name ?></title>
    </head>
    <body>

        <a class="back" href="index.php">
            <i class="fad fa-long-arrow-left"></i>
            <!-- <span>Back</span>  -->
        </a>
    
        <a class="insert" href="insert.php?table_name=<?php echo $table_name ?>">
            <i class="far fa-plus"></i>
            <!-- <span>Insert</span>  -->
        </a>

        <h1>Table name: <span><?php echo $table_name ?></span></h1>
        
        <div class="hr"></div>
        
        <div class="table">
            <table border="0" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <?php
                            foreach ($table_data["field_names"] as $field_name) {
                                if (isset($hidden[$field_name])) {
                                    continue;
                                }
                                echo "<th style=\"text-transform: uppercase; padding: 10px\">{$field_name}</th>";
                            }
                        ?>
                        <th style="text-transform: uppercase; padding: 10px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach ($table_data["data"] as $row) {
                            echo "<tr>";

                            foreach ($table_data["field_names"] as $field_name) {
                                if (isset($hidden[$field_name])) {
                                    continue;
                                }
                                echo "<td style=\"padding: 10px;\">{$row[$field_name]}</td>";
                            }

                            $url = Table::for_url($table->get_primary_info($row));

                            echo "
                                <td class=\"action\" style=\"padding: 10px;\">
                                    <a class=\"edit\" href=\"edit.php?table_name={$table_name}&{$url}\"><i class=\"fas fa-pen-alt\"></i></a>
                                    <a class=\"delete\" href=\"delete_row.php?table_name={$table_name}&{$url}\"><i class=\"fas fa-trash\"></i></a>
                                </td>
                            ";

                            echo "</tr>";
                        }
                    ?>
                </tbody>
            </table>
        </div>

    </body>
</html>
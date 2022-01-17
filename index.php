
<?php
    include_once "vendor/autoload.php";  
    use App\Controllers\Table;
    
    $table_names = Table::get_table_names();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="assets/fonts/font-awesome/css/all.css">
        <link rel="stylesheet" href="assets/css/index.css">
        <title>ADMIN PANEL</title>
    </head>
    <body>
        <h1>Admin Panel: <span><?php echo DATABASE; ?></span></h1>
        <div class="hr"></div>
        <div class="tables">
            <?php
                foreach ($table_names as $table_name) {
                    echo "
                        <div class=\"table_action\">
                            <span>{$table_name}</span>

                            <a class=\"show_tables\" href=\"show_table.php?table_name={$table_name}\">
                                <i class=\"fad fa-table\"></i>
                            </a>
                            
                            <a class=\"table_settings\" href=\"table_settings.php?table_name={$table_name}\">
                                <i class=\"fad fa-tools\"></i>
                            </a>
                        </div>
                    ";
                }
            ?>
        </div>
    </body>
</html>
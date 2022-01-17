<?php
    namespace App\Utility;
    use App\Support\MySql;

    trait DBTools {
        public static function get_table_names() {
            $database = new MySql();
            $raw_table_names = $database->show_tables();
            $table_names = [];

            foreach ($raw_table_names as $table_name) {
                array_push($table_names, $table_name[0]);
            }

            return $table_names;
        }

        public function mark_file_fields($table_name, $field_values) {
            $table_description = $this->database->describe($table_name);
            
            foreach ($table_description as $field_description) {
                $field_name = $field_description["Field"];
                $type = $field_description["Type"];
                
                $query = "
                    alter table {$table_name} 
                    change {$field_name} {$field_name} {$type}
                ";
                
                if ($field_values[$field_name] == 1) {
                    $query .= " comment '{$field_name}-file'";
                }
                else {
                    $query .= " comment 'normal'";
                }
                
                $this->database->query($query);
            }
        }
    }
?>
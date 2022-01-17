<?php
    namespace App\Controllers;
    use App\Support\MySql;
    use App\Utility\DBTools;
use App\Utility\FileTools;
use App\Utility\FormTools;

class Table {
        use DBTools;
        use FormTools;
        use FileTools;

        private $table_name = null;
        private $database = null;
        private $primary_keys = null;
        private $description = null;
        private $structure = null;

        public function __construct($table_name) {
            $this->table_name = $table_name;
            $this->database = new MySql();
        }

        /**
         * takes files and the primary information of the entity
         * uploads the files to the configured path and updates the database with the absolute
         * path of the file
         * 
         * ** requirements for this method to work
         *      file columns must have comment with following format
         *      comment format:
         *        <column_name>-file
         */
        public function upload($files, $primary_info) {
            $new_name_common_prefix = $this->table_name;
            foreach ($primary_info as $value) {
                $new_name_common_prefix .= "_{$value}";
            } 

            foreach ($files as $field => $file) {
                $files[$field]["new_name"] = $new_name_common_prefix."_{$field}";
                // echo "new_name: {$files[$field]['new_name']}<br>";
            }

            $uploaded_files = $this->save_file($files);
            $this->database->update($this->table_name, $uploaded_files, $primary_info);
        }

        /**
         * returns all the primary keys as array ex: ["primary_key_1", "primary_key_2", ... ]
         */
        public function get_primary_keys() {
            if ($this->primary_keys == null) {
                $this->primary_keys = [];
                $desc = $this->get_description();
                foreach ($desc as $field_desc) {
                    if ($field_desc["Key"] == "PRI") {
                        array_push($this->primary_keys, $field_desc["Field"]);
                    }
                }
            }
            return $this->primary_keys;
        }

        /**
         * returns the result of query "destribe table_name" as associative array
         */
        public function get_description() {
            if ($this->description == null) {
                $this->description = $this->database->describe($this->table_name);
            }
            return $this->description;
        }

        /** 
         * returns structure of this table as associative array
         * ex: [
         *     "field_name_1"=> [
         *         "type"=> "int"
         *         "primary_key=> true,
         *         "foreign_key"=> [
         *              "status"=> false,
         *         ],
         *         "default_value"=> "0"      
         *         "nullable"=> false,
         *     ],
         *     "field_name_2"=> [
         *         "type"=> "varchar",
         *         "primary_key=> false,
         *         "foreign_key"=> [
         *              "status"=> true,
         *              "table_name"=> "table_name",
         *              "field_name"=> "field_name"
         *         ],
         *         "default_value"=> "test_string"
         *         "nullable"=> true,
         *     ]
         * ]
         */
        public function get_structure() {
            if ($this->structure == null) {

                $this->structure = [];

                $desc = $this->get_description();

                foreach ($desc as $field_desc) {
                    $this->structure[$field_desc["Field"]] = [];
                    $this->structure[$field_desc["Field"]]["type"] = explode("(", $field_desc["Type"])[0];
                    $this->structure[$field_desc["Field"]]["primary_key"] = $field_desc["Key"] == "PRI" ? true : false;
                    $this->structure[$field_desc["Field"]]["foreign_key"] = [];
                    $this->structure[$field_desc["Field"]]["foreign_key"]["status"] = false;
                    $this->structure[$field_desc["Field"]]["default_value"] = $field_desc["Default"];
                    $this->structure[$field_desc["Field"]]["nullable"] = $field_desc["Null"] == "NO" ? false : true;
                }

                $meta_data = $this->database->get_meta_data($this->table_name);
                $foreign_key_pattern = "/(CONSTRAINT).*(FOREIGN KEY)\s\S\S(.*)\S\S\s(REFERENCES)\s\S(.*)\S\s\S\S(.*)`./";
                $foreign_keys_info = [];
                preg_match_all($foreign_key_pattern, $meta_data, $foreign_keys_info);

                for ($i = 0; $i < sizeof($foreign_keys_info[3]); $i++) {
                    $field_name = $foreign_keys_info[3][$i];
                    $this->structure[$field_name]["foreign_key"]["status"] = true;
                    $this->structure[$field_name]["foreign_key"]["table_name"] = $foreign_keys_info[5][$i];;
                    $this->structure[$field_name]["foreign_key"]["field_name"] = $foreign_keys_info[6][$i];;
                }
                $file_types = [];
                preg_match_all("/'(.*)-file'/", $this->database->get_meta_data($this->table_name), $file_types);
                
                foreach ($file_types[1] as $file_type) {
                    $this->structure[$file_type]["type"] = "file";
                }
            }   
            return $this->structure;
        }

        /**
         * takes a row_of_data of this table as associative array
         * ex: [
         *     "field_1"=> value_of_field_1,
         *     "field_2"=> value_of_field_2,
         * ]
         * returns an associative array containing primary information of that row 
         * ex: [
         *     "primary_key_1"=> value_of_primary_key_1_in_that_row,
         *     "primary_key_2"=> value_of_primary_key_2_in_that_row,
         *     ...
         *     ...
         * ]
         */
        public function get_primary_info($row) {
            $primary_info = [];
            
            $p_keys = $this->get_primary_keys();

            foreach ($p_keys as $primary_key) {
                $primary_info[$primary_key] = $row[$primary_key];
            }

            return $primary_info;
        }

        /**
         * returns all possible values for the foreign keys of this table
         * ex: [
         *     "foreign_key_1"=> ["value_1", "value_2", ... ...]
         *     "foreign_key_2"=> ["value_1", "value_2", ... ...]
         *     "foreign_key_3"=> ["value_1", "value_2", ... ...]
         *     ... ...
         * ]
         */
        public function get_foreign_key_values() {
            $foreign_key_values = [];
            $structure = $this->get_structure();

            foreach ($structure as $field => $field_structure) {
                if ($field_structure["foreign_key"]["status"]) {

                    $table_name = $field_structure["foreign_key"]["table_name"];
                    $field_name = $field_structure["foreign_key"]["field_name"];

                    $values = $this->database->get($table_name, [$field_name]);
                    $foreign_key_values[$field] = [];

                    foreach ($values as $row) {
                        array_push($foreign_key_values[$field], $row[$field_name]);
                    }
                }
            }
            return $foreign_key_values;
        }

        /**
         * returns all row_data of this table as associative array
         * ex: [
         *     [ "field_name_1"=> value_of_field_name_1, "field_name_2"=> value_of_field_name_2, ... ],
         *     [ "field_name_1"=> value_of_field_name_1, "field_name_2"=> value_of_field_name_2, ... ],
         *     [ "field_name_1"=> value_of_field_name_1, "field_name_2"=> value_of_field_name_2, ... ],
         *     ... ...
         *     ... ...
         * ]
         */
        public function get_all() {
            $table_desc = $this->get_description();
            
            $field_names = [];
            foreach ($table_desc as $field_desc) {
                array_push($field_names, $field_desc["Field"]);
            }
            
            $data = $this->database->get($this->table_name);

            return [
                "field_names"=> $field_names,
                "data"=> $data
            ];
        }

        public function get($primary_info) {
            return $this->database->get($this->table_name, conditions:$primary_info)[0];
        }

        /**
         * takes $_POST, $_GET, $_FILE
         * returns an associative array according to the fields of this table
         */
        public function extract_form($get=null, $post=null, $file=null) {
            if ($post != null) {
                $data = [];
                $desc = $this->get_description();
                foreach ($desc as $field_desc) {
                    if (isset($post[$field_desc["Field"]])) {
                        $data[$field_desc["Field"]] = $post[$field_desc["Field"]];
                    }
                }
                return $data;
            }

            return null;
        }

        /**
         * takes an associative array containing primary_information
         * ex: [
         *     "primary_key_1"=> value_of_primary_key_1,
         *     "primary_key_2"=> value_of_primary_key_2,
         *     ...
         *     ...
         * ]
         * deletes a row based on the primary_information given
         */
        public function delete($pri_info) {
            $this->database->delete($this->table_name, $pri_info);
        }

        /**
         * takes as associative array and insert them into the this table
         * returns the primary key values as associative array
         * ex: [
         *     "premary_key_1"=> "value",
         *     "premary_key_2"=> "value",
         *     "premary_key_3"=> "value",
         *     ... ...
         * ]
         */
        public function insert($data) {
            $field_names = [];
            $values = [];

            foreach ($data as $field_name => $value) {
                array_push($field_names, $field_name);
                array_push($values, $value);
            }

            $this->database->insert($this->table_name, $field_names, [$values]);

            return $this->database->get($this->table_name, $this->get_primary_keys(), $data)[0];
        }

        public function update($data, $primary_info) {
            $this->database->update($this->table_name, $data, $primary_info);
        }
    }
?>
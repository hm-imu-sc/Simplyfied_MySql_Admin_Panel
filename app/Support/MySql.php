<?php
    namespace App\Support;

    use App\Exceptions\CountMissmatch;
    use App\Exceptions\ConnectionFailed;
    use App\Exceptions\QueryExecutionFailed;
    use mysqli;

    class MySql {

        private $host, $username, $password, $db_name, $connection;

        public function __construct() {
            $this->host = HOST;
            $this->username = USER;
            $this->password = PASS;
            $this->db_name = DATABASE;

            $this->connect();
        }
        
        public function exists($table, $conditions = null, $condition_connector = "and", $other_clauses = []) {
            $count = $this->count($table, $conditions, $condition_connector, $other_clauses);
            if ($count == 0) {
                return false;
            }
            return true;
        }

        public function find($table, $conditions = null, $condition_connector = "and", $other_clauses = []) {
            $count = $count = $this->count($table, $conditions, $condition_connector, $other_clauses);
            return [
                "count"=> $count,
                "data"=> $this->get(
                    $table, 
                    conditions:$conditions, 
                    condition_connector:$condition_connector, 
                    other_clauses:$other_clauses
                )
            ];
        }

        public function count($table, $conditions = null, $condition_connector = "and", $other_clauses = []) {
            $prepared_conditions = $this->prepare_conditions($conditions, $condition_connector);
            $query = "select count(*) as 'count' from {$table}";
            
            if ($conditions != null) {
                $prepared_conditions = $this->prepare_conditions($conditions, $condition_connector);
                $query = "{$query} where {$prepared_conditions}";
            }

            foreach ($other_clauses as $clause) {
                $query .= " {$clause}";
            }
            
            $q = $this->query($query);
            return $q->fetch_assoc()["count"];
        }

        public function get($table, $fields=["*"], $conditions = null, $condition_connector="and", $other_clauses=[]) {

            $query = "select ";

            $size = sizeof($fields);
            for ($i = 0; $i < $size; $i++) {
                $query .= $fields[$i];
                if ($i+1 < $size) {
                    $query .= ", ";
                }
            }

            $query .= " from {$table}";

            if ($conditions != null) {
                $prepared_conditions = $this->prepare_conditions($conditions, $condition_connector);
                $query = "{$query} where {$prepared_conditions}";
            }

            foreach ($other_clauses as $clause) {
                $query .= " {$clause}";
            }

            return $this->prepare_table($this->query($query));
        }

        public function update($table, $field_value, $conditions = null, $condition_connector = 'and', $other_clauses=[]) {
            
            if (empty($field_value) || $field_value == null) {
                return;
            }

            $query = "update {$table} set ";
            
            $fields = array_keys($field_value);
            $size = sizeof($fields);

            for ($i = 0; $i < $size; $i++) {
                $value = $field_value[$fields[$i]];
                $type = gettype($value);

                if ($type == "string") {
                    $value = $this->prepare_string($value);
                    $value = "'{$value}'";
                }

                $query .= "{$fields[$i]} = {$value}";

                if ($i+1 < $size) {
                    $query .= ", ";
                }
            }

            if ($conditions != null) {
                $prepared_conditions = $this->prepare_conditions($conditions, $condition_connector);
                $query = "{$query} where {$prepared_conditions}";
            }

            foreach ($other_clauses as $clause) {
                $query .= " {$clause}";
            }

            $this->query($query);
        }

        public function insert($table, $fields, $values) {
            $query = "insert into {$table} (";

            $num_fields = sizeof($fields);

            for ($i = 0; $i < $num_fields; $i++) {

                $query .= $fields[$i];

                if ($i + 1 < $num_fields) {
                    $query .= ", ";
                }
            }

            $query .= ")";

            $query .= " values ";

            $num_values = sizeof($values);

            for ($j = 0; $j < $num_values; $j++) {
                $value = $values[$j];
                $size = sizeof($value);

                if ($size != $num_fields) {
                    // echo "<pre>";
                    // print_r($value);
                    // print_r($fields);
                    // echo "</pre>";
                    throw new CountMissmatch("value count doesn't match with field count");
                }

                $query .= "(";

                for ($i = 0; $i < $size; $i++) {

                    $v = $value[$i];
                    $t = gettype($v);

                    if ($t == "string") {
                        $v = $this->prepare_string($v);
                        $v = "'{$v}'";
                    }

                    $query .= "{$v}";

                    if ($i+1 < $size) {
                        $query .= ", ";
                    }           
                }
                
                $query .= ")";

                if ($j+1 < $num_values) {
                    $query .= ", ";
                }

            }

            // print($query);

            $this->query($query);
        }

        public function delete($table, $primary_info, $condition_connector = "and") {
            $prepared_conditions = $this->prepare_conditions($primary_info, $condition_connector);
            $query = "delete from {$table} where {$prepared_conditions}";
            $this->query($query);
        }

        public function disconnect() {
            $this->connection->close(); 
        }

        /**
         * returns all the table names as array ex: [table_name_1, table_name_2, ... ...]
         */
        public function show_tables() {
            return $this->prepare_table($this->query("show tables"), MYSQLI_NUM);
        }

        /**
         * takes table_name as string
         * returns table create query as string
         */
        public function get_meta_data($table) {
            $query = "show create table {$table}";
            return $this->prepare_query($query)[0]["Create Table"];
        }

        /**
         * takes table_name as string
         * returns the result of query "destribe table_name" as associative array
         */
        public function describe($table) {
            return $this->prepare_query("describe {$table}");
        }

        /**
         * takes sql query as string
         * returns the result of query
         */   
        public function query($query) {
            $r = $this->connection->query($query);
            
            if (!$r) {
                throw new QueryExecutionFailed($query);
            }

            return $r;
        }

        private function prepare_string($str) {
            $str_parts = explode("'", $str);
            $size = sizeof($str_parts);

            $str = "";
            for ($i = 0; $i < $size; $i++) {
                $str .= $str_parts[$i];
                if ($i + 1 < $size) {
                    $str .= "\'";
                }
            }

            $str_parts = explode("\"", $str);
            $size = sizeof($str_parts);

            $str = "";
            for ($i = 0; $i < $size; $i++) {
                $str .= $str_parts[$i];
                if ($i + 1 < $size) {
                    $str .= "\\\"";
                }
            }

            return $str;
        }

        /**
         * takes sql query as string
         * returns the result of query as associative array
         */ 
        private function prepare_query($query) {
            return $this->prepare_table($this->query($query));
        }

        private function prepare_conditions ($conditions, $condition_connector) {
            $fields = array_keys($conditions);
            $size = sizeof($fields);
            $condition = "";

            for ($i = 0; $i < $size; $i++) {
                $value = $conditions[$fields[$i]];
                $type = gettype($value);
                
                if ($type == "string") {
                    $value = $this->prepare_string($value);
                    $value = " = '{$value}'";
                }
                else if ($type == "array") {
                    $value = $this->prepare_subconditions($value);
                }
                else {
                    $value = " = {$value}";
                }

                $condition .= "{$fields[$i]}{$value}";
                
                if ($i+1 < $size) {
                    $condition .= " {$condition_connector} ";
                }
            }

            return $condition;
        }

        private function prepare_subconditions($values) {
            $condition = " in(";
            $size = sizeof($values);
            for ($i = 0; $i < $size; $i++) {
                $value = $values[$i];
                $type = gettype($value);
                if ($type == "string") {
                    $value = $this->prepare_string($value);
                    $value = "'{$value}'";
                }
                $condition .= "{$values[$i]}";
                if ($i+1 < $size) {
                    $condition .= ", ";
                }
            }
            return $condition.")";
        }

        private function prepare_table($result, $preparation_type=MYSQLI_ASSOC) {
            return $result->fetch_all($preparation_type);
        }

        private function connect() {
            $this->connection = new mysqli($this->host, $this->username, $this->password, $this->db_name);
            
            if ($this->connection->connect_error) {
                throw new ConnectionFailed();
            }
        }
    }
?>
<?php
    namespace App\Exceptions;
    use Exception;

    class QueryExecutionFailed extends Exception {
        public function __construct($message) {
            $this->message = $message;
        }

        public function __toString() {
            return ":
                 [!] Failed to execute the following sql:\n\t\t\t'{$this->message}'
            ";
        }
    }
?>
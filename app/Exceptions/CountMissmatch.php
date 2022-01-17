<?php
    namespace App\Exceptions;
    use Exception;

    class CountMissmatch extends Exception {

        public function __construct($message) {
            $this->message = $message;
        }

        public function toString() {
            return ":
                 [!] Missmatch:\n\t\t\t'{$this->message}'
            ";
        }
    }

?>
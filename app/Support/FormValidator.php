<?php
    namespace App\Support;
    
    class FormValidator {

        public function __construct() {
            
        }

        public function validate($key, $data) {
            if (empty($data)) {
                return $key == "password_strength" ? 0 : false;
            }
            switch ($key) {
            case "email":
                return $this->__email($data);
                break;
            case "phone":
                return $this->__phone($data);
                break;
            case "username":
                return $this->__username($data);
                break;
            case "password":
                return $this->__password($data);
                break;
            case "password_strength":
                return $this->__password_strength($data);
                break;
            }
        }
        
        private function __is_allowed_spec_char($char, $allowed_spec_chars=["_"]) {
            foreach ($allowed_spec_chars as $s_char) {
                if ($char === $s_char) {
                    return true;
                }
            }
            return false;
        }

        private function __is_upper_case($char) {
            $c_ord = ord($char);
            return ($c_ord >= ord("A") && $c_ord <= ord("Z")) ? true : false;
        }
        
        private function __is_lower_case($char) {
            $c_ord = ord($char);
            return ($c_ord >= ord("a") && $c_ord <= ord("z")) ? true : false;
        }

        private function __is_digit($char) {
            $c_ord = ord($char);
            return ($c_ord >= ord("0") && $c_ord <= ord("9")) ? true : false;
        }

        private function __is_valid_char($char) {
            return $this->__is_lower_case($char) || $this->__is_upper_case($char) || $this->__is_digit($char) || $this->__is_allowed_spec_char($char) ? true : false;
        }

        private function __username($data) {
            for ($i=0; $i<strlen($data); $i++) {
                if (!$this->__is_valid_char($data[$i])) {
                    return false;
                }
            }
            return true;
        }

        private function __email($data) {
            $init_email_parts = explode("@", $data);
            if (sizeof($init_email_parts) !== 2) {
                return false;
            }

            $email_parts = [$init_email_parts[0]];
            $init_email_parts = explode(".", $init_email_parts[1]);
            if (sizeof($init_email_parts) !== 2) {
                return false;
            }

            foreach ($init_email_parts as $part) {
                array_push($email_parts, $part);
            }

            foreach (explode(".", $email_parts[0]) as $part) {
                if (!$this->__username($part)) {
                    return false;
                }
            }

            $allowed_email_vendors = ["gmail", "yahoo", "hotmail", "outlook", "boldazonly"];
            $valid = false;
            
            foreach ($allowed_email_vendors as $vendor) {
                if ($vendor === $email_parts[1]) {
                    $valid = true;
                    break;
                }
            }
            
            if (!$valid) {
                return false;
            }

            $allowed_domains = ["com", "org", "net"]; 
            $valid = false;
            
            foreach ($allowed_domains as $domain) {
                if ($domain === $email_parts[2]) {
                    $valid = true;
                    break;
                }
            }
            
            return $valid;
        }
    
        private function __phone($data) { // make it generic for each country
            if (strlen($data) !== 11) {
                return false;
            }

            if ($data[0] !== "0" || $data[1] !== "1") {
                return false;
            }

            for ($i=2; $i<11; $i++) {
                if (!$this->__is_digit($data[$i])) {
                    return false;
                }
            }

            return true;
        }

        private function __password($data) {
            return (strlen($data) < 8) ? false : true; 
        }

        private function __password_strength($data) {
            $strength = [
                "lower_case"=> false,
                "upper_case"=> false,
                "digit"=> false,
                "special_char"=> false
            ];
            
            for ($i=0; $i<strlen($data); $i++) {
                if ($this->__is_digit($data[$i])){
                    $strength["digit"] = true;
                }
                else if ($this->__is_lower_case($data[$i])) {
                    $strength["lower_case"] = true;
                }
                else if ($this->__is_upper_case($data[$i])) {
                    $strength["upper_case"] = true;
                }
                else {
                    $strength["special_char"] = true;
                }
            }

            $strength_level = 0;

            foreach ($strength as $s) {
                if ($s) {
                    $strength_level++;
                }
            }

            return $strength_level;
        }
    }

?>
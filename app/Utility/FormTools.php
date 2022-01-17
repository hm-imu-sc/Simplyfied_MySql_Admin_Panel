<?php
    namespace App\Utility;
    
    trait FormTools {
        public static function for_url($data) {
            $url = "";

            $size = sizeof($data);
            $i = 0;

            foreach ($data as $field => $value) {
                $url .= "{$field}={$value}";
                if ($i+1 < $size) {
                    $url .= "&";
                    $i++;
                }
            }

            return $url;
        }
    }
?>
<?php
    namespace App\Utility;

    trait FileTools {
        protected function save_file($files) {
            $ret = [];
            foreach ($files as $field_name => $file) {
                $file_name = $file["name"];

                if (isset($file["new_name"]) && !empty($file["new_name"])) {
                    $file_parts = explode(".", $file["name"]);
                    $file_name = $file["new_name"].".".end($file_parts);
                }

                $to = MEDIAFILES_DIR."{$file_name}";
                move_uploaded_file($file["tmp_name"], BASE_DIR."/".$to);

                $ret[$field_name] = $to;
            }
            return $ret;
        }

        public static function is_image($filename) {
            $image_types = [
                "jpg",
                "jpeg",
                "png",
            ];

            $file_parts = explode(".", $filename);
            $ext = end($file_parts);
                
            foreach ($image_types as $image_type) {
                if (strtolower($ext) == strtolower($image_type)) {
                    return true;
                }
            }

            return false;
        }

        public static function is_null($filename) {
            if ($filename == null || strtolower($filename) == "null" || empty($filename)) {
                return true;
            }
            return false;
        }

        public static function refine_files($files) {
            $refined_files = [];
            
            foreach ($files as $file_field => $file) {
                if ($file["size"] > 0) {
                    $refined_files[$file_field] = $file;
                }
            }

            return $refined_files;
        }

        public static function has_file($files) {
            if (empty($files)) {
                return false;    
            }

            foreach ($files as $file) {
                if ($file["size"] > 0) {
                    return true;
                }
            }

            return false;
        }
    }
?>
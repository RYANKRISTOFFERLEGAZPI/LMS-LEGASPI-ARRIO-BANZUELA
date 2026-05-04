<?php
namespace App\Helpers;

use Exception;

class EnvParser{
    private $variables = [];
    
    public function load($path){
        if (!file_exists($path)) {
            throw new Exception(".env file not found at: " . $path);
        }
        
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            $this->parseLine($line);
        }
        
        return $this;
    }
    
    private function parseLine($line){
        $equalsPos = strpos($line, '=');
        if ($equalsPos === false) {
            return;
        }
        
        $key = trim(substr($line, 0, $equalsPos));
        $value = trim(substr($line, $equalsPos + 1));
        
        $value = $this->sanitizeValue($value);
        
        $this->variables[$key] = $value;
        
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
    
    private function sanitizeValue($value){
        if (strlen($value) > 1) {
            if (($value[0] === '"' && $value[strlen($value) - 1] === '"') ||
                ($value[0] === "'" && $value[strlen($value) - 1] === "'")) {
                $value = substr($value, 1, -1);
            }
        }
        
        $value = str_replace('\\n', "\n", $value);
        $value = str_replace('\\r', "\r", $value);
        $value = str_replace('\\t', "\t", $value);
        
        return $value;
    }

    public function get($key, $default = null){
        return $this->variables[$key] ?? $default;
    }
    
    public function all(){
        return $this->variables;
    }
}
?>

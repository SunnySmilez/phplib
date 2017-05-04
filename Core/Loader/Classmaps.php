<?php
namespace Core\Loader;

use S\Exception;

class Classmaps {
    protected $maps = array();

    public function register() {
        spl_autoload_register(array($this, 'loadClass'));
    }

    public function addClassMap($className, $filePath) {
        if(in_array($className, $this->maps)){
            throw new Exception("$className is in maps");
        }
        $this->maps[$className] = $filePath;
        return true;
    }

    public function loadClass($class) {
        $file = $this->maps[$class];
        if (file_exists($file)) {
            require $file;
            return true;
        }else{
            return false;
        }
    }
}
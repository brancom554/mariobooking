<?php

class AweBooking
{
    public function __construct()
    {
        $this->_load('Hooks');
        $this->_load('Abstracts');
        $this->_load('Helpers');
        $this->_load('Libraries');
        $this->_load('Payments');
    }

    public function _load($folder)
    {
        $path = dirname(__FILE__);
        $app = $path . '/app/';
        $files = glob($app . $folder . "/*");
        if (!empty($files)) {
            foreach ($files as $key => $file) {
                if (is_file($file)) {
                    $filename = $this->_path_info($file);
                    if (strlen($filename) >= 5) {
                        if (substr($filename, -4) == '.php') {
                            $name = substr($filename, 0, -4);
                            require_once($file);
                            if (class_exists($name)) {
                                $testClass = new \ReflectionClass($name);
                                if (!$testClass->isAbstract()) {
                                    if (method_exists($name, 'get_inst')) {
                                        $name::get_inst();
                                    } elseif (method_exists($name, 'not')) {

                                    } else {
                                        new $name();
                                    }
                                }
                            }
                        }
                    }
                } elseif (is_dir($file)) {
                    $dir = $folder . '/' . $this->_path_info($file);
                    $this->_load($dir);
                }
            }
        }
    }

    public function _path_info($path = '', $return = '')
    {
        if ($return == 'dir') {
            $pathinfo = pathinfo($path);
            $result = $pathinfo['dirname'];
        } else {
            $pathinfo = pathinfo($path);
            $result = $pathinfo['basename'];
        }

        return $result;
    }
}

new AweBooking();


global $hh_filter, $hh_actions, $hh_current_filter, $hh_fonts, $post, $booking, $old_booking, $hh_lazyload, $hh_extensions, $hh_rtl;

if ($hh_filter) {
    $hh_filter = Hook::build_preinitialized_hooks($hh_filter);
} else {
    $hh_filter = array();
}

if (!isset($hh_actions)) {
    $hh_actions = array();
}

if (!isset($hh_current_filter)) {
    $hh_current_filter = array();
}


<?php

spl_autoload_register('custom_autoloader');

set_error_handler('exceptions_error_handler');

function custom_autoloader($class) {
  include 'core/' . $class . '.php';
}

function exceptions_error_handler($severity, $message, $filename, $lineno) {
  if (error_reporting() == 0) {
      return;
  }
  if (error_reporting() & $severity) {
      throw new ErrorException($message, 0, $severity, $filename, $lineno);
  }
}

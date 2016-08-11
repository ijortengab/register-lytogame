<?php

namespace IjorTengab;

require 'RegisterLytoID.php';
require 'AbstractProcessSteps.php';
require 'ProcessSteps.php';

if (PHP_SAPI != 'cli') {
    throw new Exception('Sorry. Run for CLI Only.');
}
new ProcessSteps();

#!/usr/bin/php
<?php

/**
* This file is part of the funique package.
*
* (c) Doug Harple <dharple@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

Phar::mapPhar('funique.phar');
include 'phar://funique.phar/bin/funique';
__HALT_COMPILER();


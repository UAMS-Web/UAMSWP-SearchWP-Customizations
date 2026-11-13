<?php

declare(strict_types=1);

/*
Plugin Name: UAMSWP SearchWP Customizations
Plugin URI: https://www.uams.edu
Description: Customizations for SearchWP
Version: 1.0.0
Requires PHP: 8.2
Author: UAMS Web Services
Author URI: https://communications.uams.edu/web/
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Copyright 2022 UAMS Web Services
*/

require_once __DIR__.'/includes/functions.php';
require_once __DIR__.'/includes/hooks.php';

uamswp_searchwp_customizations_register_hooks();

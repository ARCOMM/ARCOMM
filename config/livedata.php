<?php

return [
    /**
     * The live database connection to pull from.
     * Connection defined in config\database.php.
     */
    'live' => 'mysql_live',

    /**
     * The local database connection to pull to.
     * Connection defined in config\database.php.
     * If null will use the app's default configuration.
     */
    'local' => null
];

<?php

declare(strict_types = 1);

/**
 * Christophe Avonture.
 *
 * Description
 * Quick debug helper
 */

namespace cavo789\Helpers;

use \cavo789\Classes\App as App;

class Debug
{
    /**
     * Enable the debug mode. Once enabled, PHP will display notices,
     * warnings, ... on the page and will give enough information's to
     * the developer for knowing where is the problem like the filename
     * where the error has occurred, the line number, the error code and
     * error message.
     *
     * @return void
     */
    public static function enable()
    {
        $app = App::getInstance(true);
    }
}

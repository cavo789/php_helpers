<?php

/**
 * Quick script that will show the list of .php files and allow
 * the user to click on a script to execute it.
 */
$arrExtensions = ['.php'];

$dir = dir(__DIR__);

while ($file = $dir->read()) {
    if (basename(__FILE__) !== $file) {
        $ext = substr($file, strrpos($file, '.'));
        if (in_array($ext, $arrExtensions)) {
            if (('.' != $file) && ('..' != $file)) {
                $key         = filemtime($file);
                $files[$key] = $file;
            }
        }
    }
}

ksort($files);

$TOC = '';

foreach ($files as $file) {
    $TOC .= '<LI><a href="' . $file . '">' . $file . '</a>';
}

echo '<ul>' . $TOC . '</ul>';

?>


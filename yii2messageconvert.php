<?php

if (isset($argv[2])) {
    checkFileIsExist($argv[2]);
    switch ($argv[1]) {
        case 'po2array':
            po2array($argv[2]);
            break;
        case 'array2po':
            $category = isset($argv[3]) ? $argv[3] : 'user';
            array2po($argv[2], $category);
            break;
        default :
            usage($argv);
    }
} else {
    usage($argv);
}

function usage($argv) {
    echo <<<END
Usage:
Convert yii2 message to gettext po format:
    $argv[0] po2array [inputfile]
    $argv[0] po2array [inputfile] >output.php
Convert gettext po format to yii2 message format:
    $argv[0] array2po [inputfile] <category>
    $argv[0] array2po [inputfile] <category> >output.po
            
END;
    exit;
}

function checkFileIsExist($filename) {
    if (!file_exists($filename)) {
        echo "$filename is not exist\n\n";
        exit;
    }
}

function po2array($filename) {
    $limit = 5;
    $lines = explode("\n", file_get_contents($filename, FILE_USE_INCLUDE_PATH));
    $result = [];

    foreach ($lines as $_line => $__line) {
        if (preg_match("/^msgid/i", $__line)) {
            $msgid = preg_filter("/^msgid \"/i", '', $__line);
            $msgid = preg_filter("/\"$/", '', $msgid);
            $msgstr = '';
            for ($nextLineNum = $_line; $nextLineNum < ($_line + $limit); $nextLineNum++) {
                if (isset($lines[$nextLineNum]) && preg_match("/^msgstr/i", $lines[$nextLineNum])) {
                    $msgstr = preg_filter("/^msgstr \"/i", '', $lines[$nextLineNum]);
                    $msgstr = preg_filter("/\"$/", '', $msgstr);
                    break;
                }
            }
            $result[$msgid] = $msgstr;
        }
    }
    echo "<?php ";
    echo "return ";
    echo var_export($result);
    echo ";";
    echo "\n";
}

function array2po($filename, $category = 'user') {
    try {
        ob_start();
        $data = require $filename;
        ob_clean();
    } catch (Error $ex) {
        echo "Bad format array\n";
        exit;
    }

    if (!is_array($data)) {
        echo "Bad format array\n";
        exit;
    }
    foreach ($data as $_data => $__data) {
        echo "msgctxt \"$category\"\n";
        echo "msgid \"" . $_data . "\"\n";
        echo "msgstr \"$__data\"\n";
        echo "\n";
    }
    echo "\n";
}

<?
if (!function_exists('Search')) {
    function Search($path)
    {
        if (is_dir($path)) // dir
        {
            $dir = opendir($path);
            while ($item = readdir($dir)) {
                if ($item == '.' || $item == '..')
                    continue;

                Search($path . '/' . $item);
            }
            closedir($dir);
        } else // file
        {
            if ((substr($path, -3) == '.js' || substr($path, -4) == '.php' || basename($path) == 'trigram') && $path != __FILE__)
                Process($path);
        }
    }
}

if (!function_exists('Process')) {
    function Process($file)
    {
        $content = file_get_contents($file);

        if ($charset = SberbankGetStringCharset($content) == 'utf8')
            return;

        if ($content === false)
            Error('Could not read file: ' . $file);

        if (file_put_contents($file, mb_convert_encoding($content, 'utf8', 'cp1251')) === false)
            Error('Could not save file: ' . $file);
    }
}

if (!function_exists('SberbankGetStringCharset')) {
    function SberbankGetStringCharset($str)
    {
        global $APPLICATION;
        if (preg_match("/[\xe0\xe1\xe3-\xff]/", $str))
            return 'cp1251';
        $str0 = $APPLICATION->ConvertCharset($str, 'utf8', 'cp1251');
        if (preg_match("/[\xe0\xe1\xe3-\xff]/", $str0, $regs))
            return 'utf8';
        return 'ascii';

    }
}

if (!function_exists('Errod')) {
    function Errod($text)
    {
        die('<font color=red>' . $text . '</font>');
    }
}

?>
<?php
// mod Gemorroj

require 'moduls/config.php';
define('DIRECTORY', str_replace(array('\\', '//'), '/', dirname($_SERVER['PHP_SELF']) . '/'));

// Проверка переменных
$id = intval($_GET['id']);
// Получаем инфу о файле
$d = mysql_fetch_row(mysql_query('SELECT `path` FROM `files` WHERE `id` = ' . $id, $mysql));


if (file_exists($d[0])) {
    mysql_query('UPDATE `files` SET `loads`=`loads` + 1, `timeload` = ' . $_SERVER['REQUEST_TIME'] . ' WHERE `id` = ' . $id, $mysql);

    $nm = array_reverse(explode('.', basename($d[0])));
    $nm = $nm[1];
    $tmp = $setup['jpath'] . '/' . str_replace('/', '--', mb_substr(strstr($d[0], '/'), 1)) . '.jar';

    if (!file_exists($tmp)) {
        $f = str_to_utf8(file_get_contents($d[0]));

        copy('moduls/book.zip', $tmp);
        copy('moduls/props.ini', $setup['jpath'] . '/props.ini');
        copy('moduls/MANIFEST.MF', $setup['jpath'] . '/MANIFEST.MF');

        $arr = str_split($f, 25600);
        $all = sizeof($arr);
        $ar = file('moduls/props.ini');

        $ar[] = chr(0) . chr(10) . chr(0) . wordwrap('J/textfile.txt.label=1', 1, chr(0), true);
        for ($i = 1; $i < $all; ++$i) {
            $ar[] = chr(10) . chr(0) . wordwrap('J/textfile' . $i . '.txt.label=' . ($i + 1), 1, chr(0), true);
        }
        $ar[] = chr(10);

        file_put_contents($setup['jpath'] . '/props.ini', $ar);
        file_put_contents($setup['jpath'] . '/MANIFEST.MF',
'Manifest-Version: 1.0
MicroEdition-Configuration: CLDC-1.0
MicroEdition-Profile: MIDP-1.0
MIDlet-Name: ' . $nm . '
MIDlet-Vendor: Gemor Reader
MIDlet-1: ' . $nm . ', /icon.png, br.BookReader
MIDlet-Version: 1.6
MIDlet-Info-URL: http://' . $_SERVER['HTTP_HOST'] . '
MIDlet-Delete-Confirm: GoodBye =)');

        include 'moduls/PEAR/pclzip.lib.php';
        $zip = new PclZip(dirname(__FILE__) . '/' . $tmp);
        //echo 'ERROR : '.$zip->errorInfo(true);

        $zip->add(dirname(__FILE__) . '/' . $setup['jpath'] . '/props.ini', PCLZIP_OPT_REMOVE_ALL_PATH);
        //echo 'ERROR : '.$zip->errorInfo(true);

        $zip->add(dirname(__FILE__) . '/' . $setup['jpath'] . '/MANIFEST.MF', PCLZIP_OPT_REMOVE_ALL_PATH, PCLZIP_OPT_ADD_PATH, 'META-INF');
        //echo 'ERROR : '.$zip->errorInfo(true);

        file_put_contents($setup['jpath'] . '/textfile.txt', $arr[0]);

        $zip->add(dirname(__FILE__) . '/' . $setup['jpath'] . '/textfile.txt', PCLZIP_OPT_REMOVE_ALL_PATH);
        //echo 'ERROR : '.$zip->errorInfo(true);

        unlink($setup['jpath'] . '/textfile.txt');

        for ($i = 1; $i < $all; ++$i) {
            file_put_contents($setup['jpath'] . '/textfile' . $i . '.txt', $arr[$i]);

            $zip->add(dirname(__FILE__) . '/' . $setup['jpath'] . '/textfile' . $i . '.txt', PCLZIP_OPT_REMOVE_ALL_PATH);
            //echo 'ERROR : '.$zip->errorInfo(true);
            unlink($setup['jpath'] . '/textfile' . $i . '.txt');
        }

        unlink($setup['jpath'] . '/MANIFEST.MF');
        unlink($setup['jpath'] . '/props.ini');

        chmod($tmp, 0644);
    }

    header('Location: http://' . $_SERVER['HTTP_HOST'] . DIRECTORY . str_replace('%2F', '/', rawurlencode($tmp)), true, 301);
} else {
    echo $setup['hackmess'];
}

?>

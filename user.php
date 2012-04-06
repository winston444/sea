<?php
#-----------------------------------------------------#
#     ============ЗАГРУЗ-ЦЕНТР=============           #
#                  Автор  :  Sea                      #
#               E-mail  :  x-sea-x@ya.ru              #
#                  ICQ  :  355152215                  #
#   Вы не имеете права распространять данный скрипт.  #
#           По всем вопросам пишите в ICQ.            #
#-----------------------------------------------------#

// mod Gemorroj

require 'moduls/config.php';
require 'moduls/header.php';


if (isset($_POST['lib'])) {
    $_SESSION['lib'] = intval($_POST['lib']);
}
$id = intval($_GET['id']);
$title .= $language['settings'];

###############Сортировка#########################
if ($setup['eval_change']) {
    $eval = ',<a href="' . DIRECTORY . 'sort/eval/' . $id . '">' . $language['rating'] . '</a>';
} else {
    $eval = '';
}

$sort = isset($_SESSION['sort']) ? $_SESSION['sort'] : '';


$sortlink = '<a href="' . DIRECTORY . 'sort/data/' . $id . '">' . $language['date'] . '</a>,<a href="' . DIRECTORY . 'sort/size/' . $id . '">' . $language['size'] . '</a>,<a href="' . DIRECTORY . 'sort/load/' . $id . '">' . $language['popularity'] . '</a>' . $eval;
if ($sort == 'data') {
    $sortlink = '<a href="' . DIRECTORY . 'sort/name/' . $id . '">' . $language['name'] . '</a>,<a href="' . DIRECTORY . 'sort/size/' . $id . '">' . $language['size'] . '</a>,<a href="' . DIRECTORY . 'sort/load/' . $id . '">' . $language['popularity'] . '</a>' . $eval;
} else if ($sort == 'size') {
    $sortlink = '<a href="' . DIRECTORY . 'sort/data/' . $id . '">' . $language['date'] . '</a>,<a href="' . DIRECTORY . 'sort/name/' . $id . '">' . $language['name'] . ',<a href="' . DIRECTORY . 'sort/load/' . $id . '">' . $language['popularity'] . '</a></a>' . $eval;
} else if ($sort == 'load') {
    $sortlink = '<a href="' . DIRECTORY . 'sort/data/' . $id . '">' . $language['date'] . '</a>,<a href="' . DIRECTORY . 'sort/name/' . $id . '">' . $language['name'] . ',<a href="' . DIRECTORY . 'sort/size/' . $id . '">' . $language['size'] . '</a>' . $eval;
} else if ($sort == 'eval' && $setup['eval_change']) {
    $sortlink = '<a href="' . DIRECTORY . 'sort/data/' . $id . '">' . $language['date'] . '</a>,<a href="' . DIRECTORY . 'sort/name/' . $id . '">' . $language['name'] . ',<a href="' . DIRECTORY . 'sort/size/' . $id . '">' . $language['size'] . '</a>,<a href="' . DIRECTORY . 'sort/load/' . $id . '">' . $language['popularity'] . '</a>';
}

echo '<div class="mainzag">' . $language['downloads'] . ' &#187; ' . $language['settings'] . '</div><div class="row">' . $language['sort'] . ': ' . $sortlink . '</div>';
###############Дополнительная инфа###############
if ($setup['onpage_change']) {
    echo '<div class="row">' . $language['files on page'] . ': ';
    for ($i = 5; $i < 35; $i += 5) {
        if (isset($_SESSION['onpage']) && $_SESSION['onpage'] == $i) {
            echo '<strong>[' . $i . ']</strong>';
        } else {
            echo '[<a href="' . DIRECTORY . 'onpage/' . $i . '/' . $id . '">' . $i . '</a>]';
        }
    }
    echo '</div>';
}
if ($setup['preview_change']) {
    echo '<div class="row">' . $language['preview'] . ': ';
    if (isset($_SESSION['prew']) && $_SESSION['prew']) {
        echo '<strong>[On]</strong>[<a href="' . DIRECTORY . 'prew/0/' . $id . '">Off</a>]';
    } else {
        echo '[<a href="' . DIRECTORY . 'prew/1/' . $id . '">On</a>]<strong>[Off]</strong>';
    }
    echo '</div>';
}

if ($setup['lib_change']) {
    echo '<form action="' . DIRECTORY . 'user/' . $id . '" method="post"><div class="row">' . $language['lib'] . ':<br/><input class="enter" type="text" name="lib" value="' . (isset($_SESSION['lib']) ? $_SESSION['lib'] : $setup['lib']) . '"/><br/><input type="submit" value="' . $language['go'] . '"/></div></form>';
}


// язык
echo '<form action="' . DIRECTORY . 'user/' . $id . '" method="post"><div class="row">' . $language['language'] . ':<br/>';
echo Language::getInstance()->selectLangpacks(Language::getInstance()->getLangpack());
echo '<input type="submit" value="' . $language['go'] . '"/></div></form>';



// стиль
if ($setup['style_change']) {
    // переменная для поля ввода при сервисе
    $css = '&amp;style=' . $_SERVER['HTTP_HOST'] . DIRECTORY . $setup['css'] . '.css';
    echo '<form action="' . DIRECTORY . 'user/' . $id . '" method="post"><div class="row">' . $language['style'] . ':<br/><select class="enter" name="style">';

    foreach (glob('*.css', GLOB_NOESCAPE) as $var) {
        $value = $_SERVER['HTTP_HOST'] . DIRECTORY . $var;
        echo '<option value="' . $value . '" ' . sel($value, (isset($_POST['style']) ? $_POST['style'] : (isset($_COOKIE['style']) ? $_COOKIE['style'] : $style))) . '>' . htmlspecialchars(pathinfo($var, PATHINFO_FILENAME), ENT_NOQUOTES) . '</option>';
    }

    echo '</select><input type="submit" value="' . $language['go'] . '"/></div></form>';
} else {
    $css = '';
}

if ($setup['service_change']) {
    echo '<form action="' . DIRECTORY.'user/' . $id . '" method="post"><div class="row">' . $language['service'] . ':<br/><input class="enter" type="text" value="http://' . $_SERVER['HTTP_HOST'] . DIRECTORY . '?url=somebody.com' . $css . '"/></div></form>';
}

if ($setup['service_change_advanced']) {
    echo '<div class="row"><a href="' . DIRECTORY . 'service.php">' . $language['advanced service'] . '</a><br/></div>';
}


echo '<div class="iblock">
- <a href="' . DIRECTORY . $id . '">' . $language['back'] . '</a><br/>
- <a href="' . DIRECTORY . '">' . $language['downloads'] . '</a><br/>
- <a href="' . $setup['site_url'] . '">' . $language['home'] . '</a></div>';

require 'moduls/foot.php';

?>

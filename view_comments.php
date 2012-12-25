<?php
/**
 * Copyright (c) 2012, Gemorroj
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 *
 * @author Sea, Gemorroj
 */
/**
 * Sea Downloads
 *
 * @author  Sea, Gemorroj
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

require 'core/header.php';

// Если комментарии выключены
if (!$setup['comments_change']) {
    error('Not found');
}

// Получаем инфу о файле
$v = getFileInfo($id);
if (!is_file($v['path'])) {
    error('File not found!');
}



$sql_dir = mysql_real_escape_string($v['infolder'], $mysql);
// Директория
$directory = mysql_fetch_assoc(mysql_query('SELECT *, ' . Language::getInstance()->buildFilesQuery() . ' FROM `files` WHERE `path` = "' . $sql_dir . '" LIMIT 1', $mysql));



$seo = unserialize($v['seo']);
if (!$seo['title']) {
    $seo['title'] = $v['name'];
}
$seo['title'] .= ' - ' . $language['comments'];


$template->setTemplate('comments.tpl');
$template->assign('breadcrumbs', array(
    $directory['id'] => $directory['name'],
    'view/' . $id => $v['name'],
    'view_comments/' . $id => $language['comments']
));

$template->assign('comments_module', 'view_comments');
$template->assign('comments_module_backlink', DIRECTORY . 'view/' . $id);
$template->assign('comments_module_backname', $v['name']);


$onpage = get2ses('onpage');
$page = isset($_GET['page']) ? abs($_GET['page']) : 0;

if ($onpage < 3) {
    $onpage = $setup['onpage'];
}

// всего комментариев
$all = mysql_result(mysql_query('SELECT COUNT(1) FROM `comments` WHERE `file_id` = ' . $id, $mysql), 0);

$pages = ceil($all / $onpage);
if (!$pages) {
    $pages = 1;
}
if ($page > $pages || $page < 1) {
    $page = 1;
}

$start = ($page - 1) * $onpage;
if ($start > $all || $start < 0) {
    $start = 0;
}


$query = mysql_query(
    '
    SELECT *
    FROM `comments`
    WHERE `file_id` = ' . $id . '
    ORDER BY `id` DESC
    LIMIT ' . $start . ', ' . $onpage
    ,
    $mysql
);
$comments = array();
while ($row = mysql_fetch_assoc($query)) {
    $comments[] = $row;
}


###############Запись###########################
if ($_POST) {
    //Проверка на ошибки
    if (!$_POST['msg'] || !$_POST['name']) {
        error($language['not_filled_one_of_the_fields']);
    }
    if (mb_strlen($_POST['msg']) < 4) {
        error($language['you_have_not_written_a_comment_or_he_is_too_short']);
    }

    if ($setup['comments_captcha']) {
        if (!isset($_SESSION['captcha_keystring']) || $_SESSION['captcha_keystring'] != $_POST['keystring']) {
            unset($_SESSION['captcha_keystring']);
            error($language['not_a_valid_code']);
        }
        unset($_SESSION['captcha_keystring']);
    }

    $msgSql = mysql_real_escape_string($_POST['msg'], $mysql);
    $nameSql = mysql_real_escape_string($_POST['name'], $mysql);

    if (mysql_fetch_row(
        mysql_query("SELECT 1 FROM `comments` WHERE `file_id` = ' . $id . ' AND `text` = '" . $msgSql . "' LIMIT 1", $mysql)
    )
    ) {
        error($language['why_repeat_myself']);
    }

    //Если нет ошибок пишем в базу
    setcookie('sea_name', $_POST['name'], time() + 86400000);

    $q = mysql_query(
        "
        INSERT INTO `comments` (
            `file_id`, `name`, `text`, `time`
        ) VALUES (
            " . $id . ", '" . $nameSql . "', '" . $msgSql . "', " . $_SERVER['REQUEST_TIME'] . "
        )
    ",
        $mysql
    );

    if (!$q) {
        error($language['error']);
    }

    message($language['your_comment_has_been_successfully_added']);
}

$template->assign('allItemsInDir', $all);
$template->assign('page', $page);
$template->assign('pages', $pages);
$template->assign('comments', $comments);
$template->send();

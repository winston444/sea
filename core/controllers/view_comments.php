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


require_once SEA_CORE_DIRECTORY . '/header.php';

if (!Config::get('comments_change')) {
    Http_Response::getInstance()->renderError(Language::get('not_available'));
}

$id = intval(Http_Request::get('id'));
$v = Files::getFileInfo($id);
if (!$v || !is_file($v['path'])) {
    Http_Response::getInstance()->renderError(Language::get('not_found'));
}


$db = Db_Mysql::getInstance();


Seo::unserialize($v['seo']);
//Seo::addTitle($v['name']);
//Seo::addTitle(Language::get('comments'));


Breadcrumbs::init($v['path']);
Breadcrumbs::add('view_comments/' . $id, Language::get('comments'));


// Запись
if (Http_Request::isPost()) {
    if (!Http_Request::post('msg') || !Http_Request::post('name')) {
        Http_Response::getInstance()->renderError(Language::get('not_filled_one_of_the_fields'));
    }
    if (mb_strlen(Http_Request::post('msg')) < 4) {
        Http_Response::getInstance()->renderError(Language::get('you_have_not_written_a_comment_or_he_is_too_short'));
    }

    if (Config::get('comments_captcha')) {
        if (!isset($_SESSION['captcha_keystring']) || $_SESSION['captcha_keystring'] != Http_Request::post('keystring')) {
            unset($_SESSION['captcha_keystring']);
            Http_Response::getInstance()->renderError(Language::get('not_a_valid_code'));
        }
        unset($_SESSION['captcha_keystring']);
    }

    $q = $db->prepare('SELECT 1 FROM `comments` WHERE `file_id` = ? AND `text` = ? LIMIT 1');
    $q->execute(array($id, Http_Request::post('msg')));

    if ($q->rowCount() > 0) {
        Http_Response::getInstance()->renderError(Language::get('why_repeat_myself'));
    }

    //Если нет ошибок пишем в базу
    setcookie('sea_name', Http_Request::post('name'), $_SERVER['REQUEST_TIME'] + 86400000, SEA_PUBLIC_DIRECTORY, Http_Request::getHost(), false, true);

    $q = $db->prepare('
        INSERT INTO `comments` (
            `file_id`, `name`, `text`, `time`
        ) VALUES (
            ?, ?, ?, UNIX_TIMESTAMP()
        )
    ');
    $result = $q->execute(array($id, Http_Request::post('name'), Http_Request::post('msg')));

    if (!$result) {
        Http_Response::getInstance()->renderError(Language::get('error'));
    }

    Http_Response::getInstance()->renderMessage(Language::get('your_comment_has_been_successfully_added'));
}


$template = Http_Response::getInstance()->getTemplate();
$template->setTemplate('comments.tpl');
$template->assign('comments_module', 'view_comments');
$template->assign('comments_module_backlink', SEA_PUBLIC_DIRECTORY . 'view/' . $id);
$template->assign('comments_module_backname', $v['name']);

// всего комментариев
$q = $db->prepare('SELECT COUNT(1) FROM `comments` WHERE `file_id` = ?');
$q->execute(array($id));
$all = $q->fetchColumn();

$paginatorConf = Helper::getPaginatorConf($all);

// Постраничная навигация
$template->assign('paginatorConf', $paginatorConf);


$query = $db->prepare('
    SELECT *
    FROM `comments`
    WHERE `file_id` = ?
    ORDER BY `id` DESC
    LIMIT ?, ?
');
$query->bindValue(1, $id, PDO::PARAM_INT);
$query->bindValue(2, $paginatorConf['start'], PDO::PARAM_INT);
$query->bindValue(3, $paginatorConf['onpage'], PDO::PARAM_INT);

$query->execute();
$comments = $query->fetchAll();


$template->assign('comments', $comments);
Http_Response::getInstance()->render();

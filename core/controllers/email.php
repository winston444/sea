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

if (!Config::get('send_email')) {
    Http_Response::getInstance()->renderError(Language::get('not_available'));
}


$id = intval(Http_Request::get('id'));
$v = Files::getFileInfo($id);
if (!$v || !is_file($v['path'])) {
    Http_Response::getInstance()->renderError(Language::get('not_found'));
}

Seo::unserialize($v['seo']);
//Seo::addTitle(Language::get('send_a_link_to_email'));
//Seo::addTitle($v['name']);

Http_Response::getInstance()->getTemplate()
    ->setTemplate('email.tpl')
    ->assign('file', $v);

Breadcrumbs::init($v['path']);
Breadcrumbs::add('email/' . $id, Language::get('send_a_link_to_email'));


if (Http_Request::isPost()) {
    if (!Helper::isValidEmail(Http_Request::post('email'))) {
        Http_Response::getInstance()->renderError(Language::get('email_incorrect'));
    }

    setcookie('sea_email', Http_Request::post('email'), $_SERVER['REQUEST_TIME'] + 86400000, SEA_PUBLIC_DIRECTORY, Http_Request::getHost(), false, true);
    if (Helper::sendEmail(
        Http_Request::post('email'),
        str_replace('%file%', $v['name'], Language::get('link_to_file')),
        str_replace(
            array('%file%', '%url%', '%link%'),
            array($v['name'], Config::get('site_url'), Helper::getUrl() . SEA_PUBLIC_DIRECTORY . 'view/' . $id),
            Language::get('email_message')
        )
    )) {
        Http_Response::getInstance()->renderMessage(Language::get('email_sent_successfully'));
    } else {
        Http_Response::getInstance()->renderError(Language::get('sending_email_error_occurred'));
    }
}


Http_Response::getInstance()->render();

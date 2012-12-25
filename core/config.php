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


//error_reporting(0);
// данные для соединения с БД
$mysql = mysql_connect('127.0.0.1', 'mysql', 'mysql') or die('Could not connect');
mysql_select_db('sea', $mysql) or die('Could not db');
mysql_set_charset('utf8', $mysql);

session_name('sea');
session_start() or die('Can not start session');

$q = mysql_query('SELECT * FROM setting', $mysql);
$setup = array();
while ($set = mysql_fetch_assoc($q)) {
    $setup[$set['name']] = $set['value'];
}

define('IS_ADMIN', (isset($_SESSION['authorise']) && $_SESSION['authorise'] == $setup['password']));


define('CORE_DIRECTORY', dirname(__FILE__));
if (defined('APANEL') === true) {
    define('DIRECTORY', str_replace(array('\\', '//'), '/', dirname(dirname($_SERVER['PHP_SELF'])) . '/'));
} else {
    define('DIRECTORY', str_replace(array('\\', '//'), '/', dirname($_SERVER['PHP_SELF']) . '/'));
}


set_include_path(
    get_include_path() . PATH_SEPARATOR .
        CORE_DIRECTORY . DIRECTORY_SEPARATOR . 'PEAR'
);


require_once CORE_DIRECTORY . '/inc/functions.php';
require_once CORE_DIRECTORY . '/inc/Language.php';


require_once CORE_DIRECTORY . '/Smarty/libs/Smarty.class.php';
require_once CORE_DIRECTORY . '/inc/Template.php';


// Подключаем модуль партнерки
//require CORE_CORE_DIRECTORY '/../partner/inc.php';
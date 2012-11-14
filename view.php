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


require 'moduls/header.php';

// Проверка переменных
$id = intval($_GET['id']);
//is_num($_GET['eval'], 'eval');
$out = '';

// Получаем инфу о файле
$v = mysql_fetch_assoc(
    mysql_query(
        '
    SELECT *,
    ' . Language::getInstance()->buildFilesQuery() . '
    FROM `files`
    WHERE `id` = ' . $id . '
    AND `hidden` = "0"
',
        $mysql
    )
);

if (!is_file($v['path'])) {
    error('File not found');
}


// Всего комментариев
$all_komments = mysql_result(mysql_query('SELECT COUNT(1) FROM `komments` WHERE `file_id` = ' . $id, $mysql), 0);

// Система голосований
if (isset($_GET['eval']) && $setup['eval_change']) {
    if (strpos($v['ips'], $_SERVER['REMOTE_ADDR']) === false) {
        $vote = 1;
        if (!$v['ips']) {
            $ipp = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipp = $v['ips'] . "\n" . $_SERVER['REMOTE_ADDR'];
        }

        if ($_GET['eval'] < 1) {
            $v['no'] += 1;
            mysql_unbuffered_query(
                'UPDATE `files` SET `no`=`no` + 1,`ips` = "' . $ipp . '" WHERE `id` = ' . $v['id'],
                $mysql
            );
        } else {
            $v['yes'] += 1;
            mysql_unbuffered_query(
                'UPDATE `files` SET `yes`=`yes` + 1,`ips` = "' . $ipp . '" WHERE `id` = ' . $v['id'],
                $mysql
            );
        }
    } else {
        $vote = 2;
    }
} else {
    $vote = 0;
}

#######Получаем имя файла и обратный каталог#####
$filename = pathinfo($v['path']);
$ext = strtolower($filename['extension']);
$dir = $filename['dirname'] . '/';
$basename = $filename['basename'];
$seo = unserialize($v['seo']);


if ($seo['title']) {
    $title .= htmlspecialchars($seo['title'], ENT_NOQUOTES);
} else {
    $title .= htmlspecialchars($v['name'], ENT_NOQUOTES);
}

$sql_dir = mysql_real_escape_string($dir, $mysql);

$back = mysql_fetch_assoc(mysql_query("SELECT `id` FROM `files` WHERE `path` = '" . $sql_dir . "' LIMIT 1", $mysql));


if ($setup['send_email'] && isset($_GET['email'])) {
    echo'<div><div class="mblock">' . $language['file information'] . ' "<strong>' . htmlspecialchars(
        $v['name'],
        ENT_NOQUOTES
    ) . '</strong>"</div>';

    if (isset($_POST['mail'])
        && preg_match(
            '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i',
            $_POST['mail']
        )
    ) {
        if (mail(
            $_POST['mail'],
            '=?utf-8?B?' . base64_encode(str_replace('%file%', $v['name'], $language['link to file'])) . '?=',
            str_replace(
                array('%file%', '%url%', '%link%'),
                array($v['name'], $setup['site_url'], 'http://' . $_SERVER['HTTP_HOST'] . DIRECTORY . 'view/' . $id),
                $language['email message']
            ),
            "From: robot@" . $_SERVER['HTTP_HOST'] . "\r\nContent-type: text/plain; charset=UTF-8"
        )
        ) {
            echo '<div class="yes">' . $language['email sent successfully'] . '</div>';
        } else {
            echo '<div class="no">' . $language['sending email error occurred'] . '</div>';
        }
    } else {
        echo'<form action="' . DIRECTORY . 'view/' . $id
            . '/email" method="post"><div class="row">Email: <input type="text" name="mail" class="enter"/><br/><input type="submit" class="buttom" value="'
            . $language['go'] . '"/></div></form>';
    }

    echo'</div><div class="iblock">- <a href="' . DIRECTORY . 'view/' . $id . '">'
        . $language['go to the description of the file'] . '</a><br/>- <a href="' . DIRECTORY . $back['id'] . '">'
        . $language['back'] . '</a><br/>- <a href="' . DIRECTORY . '">' . $language['downloads']
        . '</a><br/>- <a href="' . $setup['site_url'] . '">' . $language['home'] . '</a></div>';
    exit;
}


###############Красивый размер###################
$v['size'] = size($v['size']);

// Вывод
$out
    .=
    '<div class="mblock">' . $language['file information'] . ' "<strong>' . htmlspecialchars($v['name'], ENT_NOQUOTES) . '</strong>"</div><div class="row">
<strong>' . $language['size'] . ':</strong> ' . $v['size'] . '<br/>
<strong>' . $language['downloaded'] . ':</strong> ' . $v['loads'] . ' ' . $language['times'] . '<br/>';

###############Недавнее скачивание###################
if ($v['timeload']) {
    $out .= '<strong>' . $language['recent'] . ':</strong><br/>' . tm($v['timeload']) . '<br/>';
}
###############Время добавления######################
$out .= '<strong>' . $language['time additions'] . ':</strong><br/>' . tm($v['timeupload']);

// убираем папку с загрузками
$screen = strstr($v['path'], '/');
$prev_pic = str_replace('/', '--', mb_substr($screen, 1));


if ($ext == 'gif' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'jpe' || $ext == 'png' || $ext == 'bmp') {
    $out .= '<hr class="hr"/>';
    if ($setup['screen_file_change']) {
        if (file_exists($setup['picpath'] . '/' . $prev_pic . '.gif')) {
            $out .= '<img src="' . DIRECTORY . $setup['picpath'] . '/' . htmlspecialchars($prev_pic)
                . '.gif" alt=""/><br/>';
        } else {
            $out .= '<img src="' . DIRECTORY . 'im/' . $id . '" alt=""/><br/>';
        }
    }

    $size = getimagesize($v['path']);

    $out .= $size[0] . 'x' . $size[1] . '<br/><strong>' . $language['custom size'] . ':</strong>';
    foreach (explode(',', $setup['view_size']) as $val) {
        $wh = explode('*', $val);
        if (file_exists($setup['picpath'] . '/' . $wh[0] . 'x' . $wh[1] . '_' . $prev_pic . '.gif')) {
            $out .= ' <a href="' . DIRECTORY . $setup['picpath'] . '/' . $wh[0] . 'x' . $wh[1] . '_' . htmlspecialchars(
                $prev_pic
            ) . '.gif">' . $val . '</a>';
        } else {
            $out
                .= ' <a href="' . DIRECTORY . 'im.php?id=' . $id . '&amp;W=' . $wh[0] . '&amp;H=' . $wh[1] . '">' . $val
                . '</a>';
        }
    }
    $out
        .=
        '<form action="' . DIRECTORY . 'im.php?" method="post"><div class="row"><input type="hidden" name="id" value="'
            . $id
            . '"/><input type="text" size="3" name="W"/>x<input type="text" size="3" name="H"/><br/><input class="buttom" type="submit" value="'
            . $language['download'] . '"/></div></form>';
} else if ($ext == 'mp3' || $ext == 'wav' || $ext == 'ogg') {
    $tmpa = getMusicInfo($id, $v['path']);

    $out .= '<hr class="hr"/><strong>' . $language['info'] . ':</strong><br/>' . $language['channels'] . ': '
        . $tmpa['channels'] . '<br/>' . $language['framerate'] . ': ' . $tmpa['sampleRate'] . ' Hz<br/>'
        . $language['byterate'] . ': ' . round($tmpa['avgBitrate'] / 1024) . ' Kbps<br/>' . $language['length'] . ': '
        . date('H:i:s', mktime(0, 0, $tmpa['streamLength'])) . '<br/>';

    if ($tmpa['comments']['TITLE']) {
        $out .= $language['name'] . ': ' . htmlspecialchars($tmpa['comments']['TITLE'], ENT_NOQUOTES) . '<br/>';
    }
    if ($tmpa['comments']['ARTIST']) {
        $out .= $language['artist'] . ': ' . htmlspecialchars($tmpa['comments']['ARTIST'], ENT_NOQUOTES) . '<br/>';
    }
    if ($tmpa['comments']['ALBUM']) {
        $out .= $language['album'] . ': ' . htmlspecialchars($tmpa['comments']['ALBUM'], ENT_NOQUOTES) . '<br/>';
    }
    if ($tmpa['comments']['DATE']) {
        $out .= $language['year'] . ': ' . htmlspecialchars($tmpa['comments']['DATE'], ENT_NOQUOTES) . '<br/>';
    }
    if ($tmpa['comments']['GENRE']) {
        $out .= $language['genre'] . ': ' . htmlspecialchars($tmpa['comments']['GENRE'], ENT_NOQUOTES) . '<br/>';
    }
    if ($tmpa['comments']['COMMENT']) {
        $out .= $language['comments'] . ': ' . htmlspecialchars($tmpa['comments']['COMMENT'], ENT_NOQUOTES) . '<br/>';
    }
    if ($tmpa['comments']['APIC']) {
        $out .= '<img src="' . DIRECTORY . 'apic/' . $id . '" alt=""/>';
    }
} else if (($ext == '3gp' || $ext == 'avi' || $ext == 'mp4' || $ext == 'flv') && extension_loaded('ffmpeg')) {
    $tmpa = getVideoInfo($id, $v['path']);

    if ($setup['screen_file_change']) {
        $frame = isset($_GET['frame']) ? abs($_GET['frame']) : $setup['ffmpeg_frame'];
        if (file_exists($setup['ffmpegpath'] . '/' . $prev_pic . '_frame_' . $frame . '.gif')) {
            $out .= '<br/><img src="' . DIRECTORY . $setup['ffmpegpath'] . '/' . htmlspecialchars($prev_pic) . '_frame_'
                . $frame . '.gif" alt=""/><br/>';
        } else {
            $out .= '<br/><img src="' . DIRECTORY . 'ffmpeg/' . $id . '/' . $frame . '" alt=""/><br/>';
        }
        $i = 0;
        foreach (explode(',', $setup['ffmpeg_frames']) as $fr) {
            $out .= '<a href="' . DIRECTORY . 'view/' . $id . '/frame' . $fr . '">[' . (++$i) . ']</a>, ';
        }
        $out = rtrim($out, ', ') . '<hr class="hr"/>';
    }

    $out .= $language['codec'] . ': ' . htmlspecialchars($tmpa['getVideoCodec'], ENT_NOQUOTES) . '<br/>'
        . $language['screen resolution'] . ': ' . intval($tmpa['GetFrameWidth']) . ' x ' . intval(
        $tmpa['GetFrameHeight']
    ) . '<br/>' . $language['time'] . ': ' . date('H:i:s', mktime(0, 0, round($tmpa['getDuration']))) . '<br/>';


    if ($tmpa['getBitRate']) {
        $out .= $language['bitrate'] . ': ' . ceil($tmpa['getBitRate'] / 1024) . ' Kbps<br/>';
    }
} else if ($ext == 'thm' || $ext == 'nth' || $ext == 'utz' || $ext == 'sdt' || $ext == 'scs' || $ext == 'apk') {
    if ($setup['screen_file_change']) {
        if (file_exists($setup['tpath'] . '/' . $prev_pic . '.gif')) {
            $out
                .=
                '<br/><img src="' . DIRECTORY . $setup['tpath'] . '/' . htmlspecialchars($prev_pic) . '.gif" alt=""/>';
        } else if (file_exists($setup['tpath'] . '/' . $prev_pic . '.gif.swf')) {
            $out .= '<br/><object style="width:128px; height:128px;"><param name="movie" value="' . DIRECTORY
                . $setup['tpath'] . '/' . htmlspecialchars($prev_pic) . '.gif.swf"><embed src="' . DIRECTORY
                . $setup['tpath'] . '/' . htmlspecialchars($prev_pic)
                . '.gif.swf" style="width:128px; height:128px;"></embed></param></object>';
        } else {
            $out .= '<br/><img src="' . DIRECTORY . 'theme/' . $id . '" alt=""/>';
        }
    }

    $thm = thm($id, $v['path']);

    if ($ext == 'thm' && $thm) {
        $out .= '<br/>' . $thm;
    }
} else if ($setup['swf_file_change'] && $ext == 'swf') {
    $out
        .= '<br/><object style="width:128px; height:128px;"><param name="movie" value="' . DIRECTORY . htmlspecialchars(
        $v['path']
    ) . '"><embed src="' . DIRECTORY . htmlspecialchars($v['path'])
        . '" style="width:128px; height:128px;"></embed></param></object>';
} else if ($setup['jar_file_change'] && $ext == 'jar') {
    if (file_exists($setup['ipath'] . '/' . $prev_pic . '.png')) {
        $out .= '<br/><img style="margin: 1px;" src="' . DIRECTORY . $setup['ipath'] . '/' . htmlspecialchars($prev_pic)
            . '.png" alt=""/>';
    } else if (jar_ico($v['path'], $setup['ipath'] . '/' . $prev_pic . '.png')) {
        $out .= '<br/><img style="margin: 1px;" src="' . DIRECTORY . $setup['ipath'] . '/' . htmlspecialchars($prev_pic)
            . '.png" alt=""/>';
    }
}

// Скиншот
if (file_exists($setup['spath'] . $screen . '.gif')) {
    $out .= '<hr class="hr"/><strong>' . $language['screenshot'] . ':</strong><br/><img style="margin: 1px;" src="'
        . DIRECTORY . $setup['spath'] . htmlspecialchars($screen) . '.gif" alt=""/>';
} else if (file_exists($setup['spath'] . $screen . '.jpg')) {
    $out .= '<hr class="hr"/><strong>' . $language['screenshot'] . ':</strong><br/><img style="margin: 1px;" src="'
        . DIRECTORY . $setup['spath'] . htmlspecialchars($screen) . '.jpg" alt=""/>';
}

###############Описание#############################
if (file_exists($setup['opath'] . '/' . $screen . '.txt')) {
    $out .= '<hr class="hr"/><strong>' . $language['description'] . ':</strong><br/>' . trim(
        file_get_contents($setup['opath'] . '/' . $screen . '.txt')
    );
} else if ($setup['lib_desc'] && $ext == 'txt') {
    $fp = fopen($v['path'], 'r');
    $out .= '<hr class="hr"/><strong>' . $language['description'] . ':</strong><br/>' . trim(fgets($fp, 1024));
    fclose($fp);
}


if ($v['attach']) {
    $attach = unserialize($v['attach']);
    if ($attach) {
        $out .= '<hr class="hr"/><strong>Вложения:</strong><br/>';
        foreach ($attach as $k => $val) {
            $out .= '<a href="' . htmlspecialchars(
                DIRECTORY . $setup['apath'] . dirname($screen) . '/' . $id . '_' . $k . '_' . $val
            ) . '">' . htmlspecialchars($val, ENT_NOQUOTES) . '</a><br/>';
        }
        $out .= '<hr class="hr"/>';
    }
}


// предыдущий/следующий файл
if ($setup['prev_next']) {
    $count = mysql_result(
        mysql_query(
            '
        SELECT COUNT(1)
        FROM `files`
        WHERE `infolder` = "' . $sql_dir . '"
        AND `dir` = "0"
        AND `hidden` = "0"
    ',
            $mysql
        ),
        0
    );

    if ($count > 1) {
        $next = mysql_fetch_row(
            mysql_query(
                '
            SELECT MIN(`id`), COUNT(`id`)
            FROM `files`
            WHERE `infolder` = "' . $sql_dir . '"
            AND `dir` = "0"
            AND `hidden` = "0"
            AND `id` > ' . $id
                ,
                $mysql
            )
        );

        $prev = mysql_fetch_row(
            mysql_query(
                '
            SELECT MAX(`id`), COUNT(`id`)
            FROM `files`
            WHERE `infolder` = "' . $sql_dir . '"
            AND `dir` = "0"
            AND `hidden` = "0"
            AND `id` < ' . $id
                ,
                $mysql
            )
        );


        $out .= '<div class="row">';
        if ($prev[0]) {
            $out .= '&#171; (' . $prev[1] . ')<a href="' . DIRECTORY . 'view/' . $prev[0] . '">' . $language['prev']
                . '</a>';
        }
        $out .= ' [' . $count . '] ';
        if ($next[0]) {
            $out .= '<a href="' . DIRECTORY . 'view/' . $next[0] . '">' . $language['next'] . '</a>(' . $next[1]
                . ') &#187;';
        }
        $out .= '<br/></div>';
    }
}


###############Голосование###########################
if ($setup['eval_change']) {
    $i = $v['yes'] + $v['no'];
    $i = $i ? round($v['yes'] / $i * 100, 0) : 50;

    $out .= '<hr class="hr"/><strong>' . $language['rating'] . '</strong>: (<span class="yes">+' . $v['yes']
        . '</span>/<span class="no">-' . $v['no'] . '</span>)<br/><img src="' . DIRECTORY . 'rate/' . $i
        . '" alt="" style="margin: 1px;"/><br/>';
    if (!$vote) {
        $out
            .=
            $language['net'] . ': <span class="yes"><a href="' . DIRECTORY . 'view/' . $id . '/1">' . $language['yes']
                . '</a></span>/<span class="no"><a href="' . DIRECTORY . 'view/' . $id . '/0">' . $language['no']
                . '</a></span>';
    } else if ($vote == 1) {
        $out .= $language['true voice'];
    } else if ($vote == 2) {
        $out .= $language['false voice'];
    }
}

$out .= '</div><div class="iblock">';

if ($setup['komments_view']) {
    // Последние комментарии
    $q = mysql_query(
        'SELECT `name`, `text`, `time` FROM `komments` WHERE `file_id` = ' . $id . ' ORDER BY `id` DESC LIMIT '
            . intval($setup['komments_view']),
        $mysql
    );
    if ($q && mysql_num_rows($q)) {
        $out .= '<strong>' . $language['recent_comments'] . '</strong><br/>';
        while ($row = mysql_fetch_assoc($q)) {
            $out
                .=
                '<strong>' . htmlspecialchars($row['name'], ENT_NOQUOTES) . '</strong> (' . tm($row['time']) . ')<br/>'
                    . str_replace("\n", '<br/>', $row['text']) . '<br/>';
        }
        $out .= '</div><div class="iblock">';
    }
}


if ($setup['komments_change']) {
    // Комментарии
    $out .= '<strong><a href="' . DIRECTORY . 'komm/' . $id . '">' . $language['comments'] . ' [' . $all_komments
        . ']</a></strong><br/>';
}


if ($setup['cut_change'] && $ext == 'mp3') {
    // Нарезка MP3
    $out .= '<strong><a href="' . DIRECTORY . 'cut/' . $id . '">' . $language['splitting'] . '</a></strong><br/>';
}
if ($setup['audio_player_change'] && $ext == 'mp3') {
    // Аудио плеер
    $out .= '<object type="application/x-shockwave-flash" data="' . DIRECTORY . 'moduls/flash/player_mp3_maxi.swf" width="180" height="20">
    <param name="FlashVars" value="mp3=' . DIRECTORY . str_replace('%2F', '/', rawurlencode($v['path'])) . '&amp;width=180&amp;volume=50&amp;showvolume=1&amp;buttonwidth=20&amp;sliderheight=8&amp;volumewidth=50&amp;volumeheight=8" />
</object><br/>';
}
if ($setup['video_player_change'] && ($ext == 'flv' || $ext == 'mp4')) {
    // Видео плеер
    $out .= '<object type="application/x-shockwave-flash" data="' . DIRECTORY . 'moduls/flash/player_flv_maxi.swf" width="240" height="180">
       <param name="allowFullScreen" value="true" />
       <param name="FlashVars" value="flv=' . DIRECTORY . str_replace('%2F', '/', rawurlencode($v['path']))
        . '&amp;title=' . htmlspecialchars(htmlspecialchars($v['name'])) . '&amp;startimage=' . DIRECTORY . 'ffmpeg/'
        . $id . '/' . $setup['ffmpeg_frame'] . '&amp;width=240&amp;height=180&amp;margin=3&amp;volume=100&amp;showvolume=1&amp;showtime=1&amp;showplayer=always&amp;showloading=always&amp;showfullscreen=1&amp;showiconplay=1" />
   </object><br/>';
}
if ($setup['zip_change'] && $ext == 'zip') {
    // ZIP архивы
    $out .= '<strong><a href="' . DIRECTORY . 'zip/' . $id . '">' . $language['view archive'] . '</a></strong><br/>';
}


// txt файлы
if ($ext == 'txt') {
    if ($setup['lib_change']) {
        echo '<strong><a href="' . DIRECTORY . 'read/' . $id . '">' . $language['read'] . '</a></strong><br/>';
    }

    $out .= '<a href="' . DIRECTORY . 'txt_zip/' . $id . '">' . $language['download'] . ' [ZIP]</a><br/><a href="'
        . DIRECTORY . 'txt_jar/' . $id . '">' . $language['download'] . ' [JAR]</a><br/>';
}


// Меню закачек
$out .= '<strong><a href="' . DIRECTORY . 'load/' . $id . '">' . $language['download'] . ' [' . strtoupper($ext)
    . ']</a></strong><br/>';
if ($setup['jad_change'] && $ext == 'jar') {
    $out .= '<strong><a href="' . DIRECTORY . 'jad/' . $id . '">' . $language['download'] . ' [JAD]</a></strong><br/>';
}

$out .= '<input class="enter" size="50" type="text" value="http://' . $_SERVER['HTTP_HOST'] . DIRECTORY . str_replace(
    '%2F',
    '/',
    rawurlencode($v['path'])
) . '"/><br/>';

if ($setup['send_email']) {
    $out .= '<a href="' . DIRECTORY . 'view/' . $id . '/email">' . $language['send a link to email'] . '</a><br/>';
}

if ($setup['abuse_change']) {
    if (isset($_GET['abuse'])) {
        $out .= '<div class="yes">' . $language['complaint sent to the administration'] . '<br/></div>';
        mail(
            $setup['zakaz_email'],
            '=?utf-8?B?' . base64_encode('Жалоба на файл') . '?=',
            'Получена жалоба на файл http://' . $_SERVER['HTTP_HOST'] . DIRECTORY . 'view/' . $id . "\r\n" .
                'Браузер: ' . $_SERVER['HTTP_USER_AGENT'] . "\r\n" .
                'IP: ' . $_SERVER['REMOTE_ADDR'],
            "From: robot@" . $_SERVER['HTTP_HOST'] . "\r\nContent-type: text/plain; charset=UTF-8"
        );
    } else {
        $out .= '<a href="' . DIRECTORY . 'view/' . $id . '/abuse">' . $language['complain about a file'] . '</a>';
    }
}

echo $out . '</div><div class="iblock">- <a href="' . DIRECTORY . $back['id'] . '">' . $language['back']
    . '</a><br/>- <a href="' . DIRECTORY . '">' . $language['downloads'] . '</a><br/>- <a href="' . $setup['site_url']
    . '">' . $language['home'] . '</a></div>';

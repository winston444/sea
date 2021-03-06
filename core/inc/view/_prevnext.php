<?php
// предыдущий/следующий файл

//$prevNext = array('prev' => array(), 'next' => array());
if (Config::get('prev_next')) {
    $db = Db_Mysql::getInstance();

    $q = $db->prepare('
        SELECT COUNT(1)
        FROM `files`
        WHERE `infolder` = ?
        AND `dir` = "0"
        ' . (SEA_IS_ADMIN !== true ? 'AND `hidden` = "0"' : '')
    );
    $q->execute(array($directory['path']));
    $prevNext['count'] = $q->fetchColumn();

    if ($prevNext['count'] > 1) {
        $q = $db->prepare('
            SELECT MIN(`id`) AS `min`, COUNT(`id`) AS `count`
            FROM `files`
            WHERE `id` > ?
            AND `infolder` = ?
            AND `dir` = "0"
            ' . (SEA_IS_ADMIN !== true ? 'AND `hidden` = "0"' : '')
        );
        $q->execute(array($id, $directory['path']));
        $next = $q->fetch();

        $q = $db->prepare('
            SELECT MAX(`id`) AS `max`, COUNT(`id`) AS `count`
            FROM `files`
            WHERE `id` < ?
            AND `infolder` = ?
            AND `dir` = "0"
            ' . (SEA_IS_ADMIN !== true ? 'AND `hidden` = "0"' : '')
        );
        $q->execute(array($id, $directory['path']));
        $prev = $q->fetch();


        if ($prev && $prev['max'] && $prev['count']) {
            $prevNext['prev'] = array(
                'index' => $prev['count'],
                'id' => $prev['max'],
            );
        }
        if ($next && $next['min'] && $next['count']) {
            $prevNext['next'] = array(
                'index' => $next['count'],
                'id' => $next['min'],
            );
        }
    }
}

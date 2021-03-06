{extends file='sys/layout.tpl'}


{block header}
    <div class="iblock">
        {$language.splitting} "<strong>{$file.name}</strong>"
    </div>
{/block}

{* нарезка *}
{block content}
    <div class="row">
        {$language.size}: {$file.size|sizeFormatExtended}<br/>
        {$language.length}: {mktime(0, 0, $file.info.streamLength)|date_format:'H:i:s'}<br/>
    </div>

    {if $cut}
        <div class="row">
            {$language.the_file_has_been_successfully_cut}<br/>
            <a href="{$cut.link}">{$language.download}</a> ({$cut.size|sizeFormatExtended})<br/>
        </div>
    {/if}

    <form action="{$smarty.const.SEA_PUBLIC_DIRECTORY}cut/{Http_Request::get('id')}" method="post">
        <div class="row">
            {$language.method_slicing}:<br/>
            <label><input class="enter" type="radio" name="way" value="time" {(!$smarty.post || ($smarty.post && $smarty.post.way == 'time')) ? 'checked="checked"' : ''}/> {$language.time}</label>
            <label><input class="enter" type="radio" name="way" value="size" {($smarty.post && $smarty.post.way == 'size') ? 'checked="checked"' : ''}/> {$language.size}</label>
            <br/>

            <label>
                {$language.start_slicing}:
                <input maxlength="5" size="6" class="enter" type="number" name="s" required="required" min="1" max="65536" value="{($smarty.post) ? $smarty.post.s : ''}"/><br/>
            </label>
            <label>
                {$language.stop_slicing}:
                <input maxlength="5" size="6" class="enter" type="number" name="p" required="required" min="1" max="65536" value="{($smarty.post) ? $smarty.post.p : ''}"/><br/>
            </label>

            <input class="buttom" type="submit" name="a" value="{$language.go}"/>
        </div>
    </form>
{/block}


{block footer}
    <ul class="iblock">
        <li><a href="{$smarty.const.SEA_PUBLIC_DIRECTORY}view/{Http_Request::get('id')}">{$file.name}</li>
        <li><a href="{$smarty.const.SEA_PUBLIC_DIRECTORY}settings/{Http_Request::get('id')}">{$language.settings}</a></li>
        <li><a href="{$smarty.const.SEA_PUBLIC_DIRECTORY}">{$language.downloads}</a></li>
        <li><a href="http://{$setup.site_url}">{$language.home}</a></li>
    </ul>
{/block}
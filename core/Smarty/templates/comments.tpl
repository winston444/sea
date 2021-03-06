{extends file='sys/layout.tpl'}


{* комментарии *}
{block content}
    {if $paginatorConf.items < 1}
        <strong>[{$language.empty}]</strong>
    {else}
        {foreach $comments as $comment}
            <div class="{cycle values="row,row2"}">

                {if $smarty.const.SEA_IS_ADMIN}
                    <a href="{$smarty.const.SEA_PUBLIC_DIRECTORY}apanel/apanel.php?comment={$comment.id}&amp;action=del_comment_{$comments_module}" title="Удалить" class="no" onclick="return window.confirm('Удалить комментарий?');">[X]</a>
                {/if}

                <strong>{$comment.name}</strong> ({$comment.time|dateFormatExtended})<br/>
                <span class="comment">{$comment.text|bbcode nofilter}</span><br/>
            </div>
        {/foreach}
    {/if}

    {* пагинация *}
    {paginationExtended page=$paginatorConf.page pages=$paginatorConf.pages url="{$smarty.const.SEA_PUBLIC_DIRECTORY}{$comments_module}/{Http_Request::get('id')}"}

    <form action="{$smarty.const.SEA_PUBLIC_DIRECTORY}{$comments_module}/{Http_Request::get('id')}" method="post">
        <div class="row">
            <label>
                {$language.your_name}:<br/>
                <input class="enter" name="name" type="text" required="required" maxlength="255" value="{(isset($smarty.cookies.sea_name)) ? $smarty.cookies.sea_name : ''}"/><br/>
            </label>
            <label>
                {$language.message}:<br/>
                <textarea class="enter" cols="40" rows="5" name="msg" maxlength="65536" required="required"></textarea><br/>
            </label>

            {if $setup.comments_captcha}
                <label>
                    <img onclick="this.src=this.src+'&amp;'" alt="" src="{$smarty.const.SEA_PUBLIC_DIRECTORY}kcaptcha?{session_name()}={session_id()}" /><br/>
                    {$language.code}: <input class="enter" type="number" min="0" max="9999" required="required" name="keystring" size="5" maxlength="4"/><br/>
                </label>
            {/if}

            <input class="buttom" type="submit" value="{$language.go}"/>
        </div>
    </form>
{/block}


{block footer}
    <ul class="iblock">
        <li><a href="{$comments_module_backlink}">{$comments_module_backname}</a></li>
        <li><a href="{$smarty.const.SEA_PUBLIC_DIRECTORY}settings/{Http_Request::get('id')}">{$language.settings}</a></li>
        <li><a href="{$smarty.const.SEA_PUBLIC_DIRECTORY}">{$language.downloads}</a></li>
        <li><a href="http://{$setup.site_url}">{$language.home}</a></li>
    </ul>
{/block}
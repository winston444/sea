{extends file='sys/layout.tpl'}


{* просмотр zip архива *}
{block content}

    {if $action == 'preview'}
        <strong>{$language.file}: <a href="{$smarty.const.DIRECTORY}zip/down/{$id}/{$zipFileName|rawurlencode|replace:'%2F':'/'}">{$zipFileName}</a></strong><br/>

        {if $zipFileType == 'image'}
            <img src="{$zipFileData}" alt=""/>
        {/if}

        {if $zipFileType == 'text'}
            {if $setup.lib_str}
                <pre>{$zipFileData|wordwrap:$setup.lib_str:"\n":false}</pre>
            {else}
                <pre>{$zipFileData}</pre>
            {/if}

            {* пагинация *}
            {paginationExtended page=$page pages=$pages url="{$smarty.const.DIRECTORY}zip/preview/{$id}/{$zipFileName|rawurlencode|replace:'%2F':'/'}"}
        {/if}

    {else}
        {$language.all_files}: {$allItemsInDir}<br/>
        {$language.the_unpacked_archive}: {$allItemsSize|sizeFormatExtended}<br/>

        {if $allItemsInDir < 1}
        <strong>[{$language.empty}]</strong>
            {else}
            {foreach $zipFiles as $zipFile}
            <div class="{cycle values="row,row2"}">

                <a href="{$smarty.const.DIRECTORY}zip/preview/{$id}/{$zipFile.filename|rawurlencode|replace:'%2F':'/'}/">{$zipFile.filename}</a> ({$zipFile.size|sizeFormatExtended})<br/>

            </div>
            {/foreach}
        {/if}

        {* пагинация *}
        {paginationExtended page=$page pages=$pages url="{$smarty.const.DIRECTORY}zip/{$id}"}
    {/if}
{/block}


{block footer}
<ul class="iblock">
    <li><a href="{$smarty.const.DIRECTORY}view/{$id}">{$file.name}</a></li>
    <li><a href="{$smarty.const.DIRECTORY}{$directory.id}">{$language.go_to_the_category}</a></li>
    <li><a href="{$smarty.const.DIRECTORY}settings/{$id}">{$language.settings}</a></li>
    <li><a href="{$smarty.const.DIRECTORY}">{$language.downloads}</a></li>
    <li><a href="http://{$setup.site_url}">{$language.home}</a></li>
</ul>
{/block}
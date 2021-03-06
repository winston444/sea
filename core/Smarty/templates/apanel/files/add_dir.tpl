{extends file='../../sys/apanel/layout.tpl'}


{block content}
<h3>Создание директории</h3>

<form action="apanel.php?action=add_dir" method="post">
    <div data-role="fieldcontain">
        <label for="topath">Создать в:</label>
        {html_options id='topath' name='topath' options=$dirs}
    </div>

    <div data-role="fieldcontain">
        <label for="realname">Реальное имя [A-Za-z0-9_-]:</label>
        <input placeholder="Реальное имя директории" name="realname" type="text" pattern="^[A-Za-z0-9_\-]+$" required="required" id="realname" />
    </div>

    {foreach $langpacks as $langpack}
        <div data-role="fieldcontain">
            <label for="dir_{$langpack}">{$langpack}:</label>
            <input placeholder="Отображаемое имя директории" type="text" required="required" name="dir[{$langpack}]" id="dir_{$langpack}" />
        </div>
    {/foreach}

    <input type="submit" value="Сохранить"/>
</form>
{/block}
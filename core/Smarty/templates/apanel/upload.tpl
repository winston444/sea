{extends file='../sys/apanel/layout.tpl'}


{block content}
<h3>Загрузка файлов</h3>

<form action="apanel.php?action=upload" method="post" enctype="multipart/form-data" data-ajax="false">
    <div data-role="fieldcontain">
        <label for="topath">Сохранить в:</label>
        {html_options id='topath' name='topath' options=$dirs}
    </div>

    <div data-role="fieldcontain">
        <label for="userfile">Загрузка файлов (max {ini_get('upload_max_filesize')})</label>
        <input required="required" id="userfile" name="userfile[]" type="file" multiple="multiple" />
    </div>

    <input type="submit" value="Сохранить"/>
</form>
{/block}
{* сервис *}

<form action="{$smarty.const.SEA_PUBLIC_DIRECTORY}service" method="get">
    <div class="row">
        <input type="hidden" name="act" value="enter"/>
        <label>
            ID:<br/>
            <input class="enter" type="number" name="id" required="required" min="1"/><br/>
        </label>
        <label>
            {$language.pass}:<br/>
            <input class="enter" type="password" name="pass" required="required"/><br/>
        </label>
        <input type="submit" value="{$language.go}" class="buttom"/>
    </div>
</form>
<div class="iblock">
    <a href="{$smarty.const.SEA_PUBLIC_DIRECTORY}service?act=registration">{$language.registration}</a>
</div>
<form action="{$smarty.const.SEA_PUBLIC_DIRECTORY}service?act=pass" method="post">
    <div class="row">
        <label>
            {$language.lost_password}<br/>
            ID:<input class="enter" type="number" name="id" required="required" min="1"/> <input type="submit" value="{$language.go}" class="buttom"/>
        </label>
    </div>
</form>
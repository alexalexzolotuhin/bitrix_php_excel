<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Выгрузки");


include_once ($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/classes/general/captcha.php");
$cpt= new CCAptcha();
$captchaPass=COption::GetOptionString('main',"captcha_password","");
if(strlen($capchaPass)<=0){
    $captchaPass=randString(10);
    COption::SetOptionString('main',"captcha_password",$captchaPass);
}
$cpt->SetCodeCrypt($captchaPass);
?>
<form action="/load/ajax.php" class="get_upload_file" method="post">

<p> Для загрузи файла необходимо ввести символы с картинки</p>
<div class="col-30">
    <input type="hidden" name="captcha_code" value="<?=htmlspecialcharsbx($cpt->GetCodeCrypt()) ?>" >
    <img src="/bitrix/tools/captcha.php?captcha_code=<?=htmlspecialcharsbx($cpt->GetCodeCrypt());?>" alt="">
    <input type="text" placeholder="Введите код с картинки" name="captcha_word" type="text">
</div>
    <br/>
 <button>Отправить</button>

</form>
<div class="link_for_upload">

</div>

<script src="/load/upload.js" type="text/javascript"></script>
<script>


</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>

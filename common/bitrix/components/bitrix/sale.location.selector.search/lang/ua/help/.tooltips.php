<?
$MESS["PROVIDE_LINK_BY_TIP"] = "Параметр визначає, що буде передано в цільовий input форми при виборі місцеположення: CODE або ID";
$MESS["JS_CONTROL_GLOBAL_ID_TIP"] = "Рядковий ідентифікатор, який дозволяє безпосередньо звернутися до javascript-об'єкта селектора ззовні, використовуючи об'єкт window.BX.locationSelectors";
$MESS["JS_CALLBACK_TIP"] = "JavaScript-функція, яка викликається при кожній зміні значення селектора. Функція  повинна бути визначена в контексті об'єкта window, наприклад:<br />
вікна.функція locationUpdated = (ідентифікатор)<br />
window.locationUpdated = function(id)<br />
{<br />
&nbsp;console.log(arguments);<br />
&nbsp;console.log(this.getNodeByLocationId(id));<br />
}";
?>
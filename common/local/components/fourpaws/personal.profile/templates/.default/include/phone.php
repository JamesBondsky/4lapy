<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<input type="hidden" name="action" value="get">
<input type="hidden" name="step" value="confirm">
<div class="b-registration__step b-registration__step--one">
    <div class="b-input-line b-input-line--popup-authorization b-input-line--referal">
        <div class="b-input-line__label-wrapper">
            <label class="b-input-line__label" for="edit-phone">Мобильный</label>
        </div>
        <input class="b-input b-input--registration-form"
               type="tel"
               id="edit-phone"
               name="PERSONAL_PHONE"
               value="<?= $phone ?>"
               placeholder="" />
    </div>
</div>

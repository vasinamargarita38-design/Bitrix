<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="contact-form">
    <div class="contact-form__head">
        <div class="contact-form__head-title">Связаться</div>
        <div class="contact-form__head-text">Наши сотрудники помогут выполнить подбор услуги и расчет цены с учетом ваших требований</div>
    </div>

    <?php echo $arResult["FORM_HEADER"]?>

        <div class="contact-form__form-inputs">
            
            
            <div class="input contact-form__input">
                <label class="input__label">
                    <div class="input__label-text">Ваше имя*</div>
                    <?php echo $arResult["QUESTIONS"]["SIMPLE_QUESTION_757"]["HTML_CODE"]?>
                </label>
            </div>


            <div class="input contact-form__input">
                <label class="input__label">
                    <div class="input__label-text">Компания/Должность*</div>
                    <?php echo $arResult["QUESTIONS"]["SIMPLE_QUESTION_920"]["HTML_CODE"]?>
                </label>
            </div>


            <div class="input contact-form__input">
                <label class="input__label">
                    <div class="input__label-text">Email*</div>
                    <?php echo $arResult["QUESTIONS"]["SIMPLE_QUESTION_323"]["HTML_CODE"]?>
                </label>
            </div>


            <div class="input contact-form__input">
                <label class="input__label">
                    <div class="input__label-text">Номер телефона*</div>
                    <?php echo $arResult["QUESTIONS"]["SIMPLE_QUESTION_775"]["HTML_CODE"]?>
                </label>
            </div>
        </div>


        <div class="contact-form__form-message">
            <div class="input">
                <label class="input__label">
                    <div class="input__label-text">Сообщение</div>
                    <?php echo $arResult["QUESTIONS"]["SIMPLE_QUESTION_527"]["HTML_CODE"]?>
                </label>
            </div>
        </div>


        <div class="contact-form__bottom">
            <div class="contact-form__bottom-policy">
                Нажимая «Отправить», Вы подтверждаете, что ознакомлены, полностью согласны и принимаете условия «Согласия на обработку персональных данных».
            </div>
              <button class="form-button contact-form__bottom-button" type="submit" name="web_form_submit" value="Y">
                     <div class="form-button__title">Оставить заявку</div>
              </button>

        </div>

    <?php echo $arResult["FORM_FOOTER"]?>
</div>


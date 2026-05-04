<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
?>

<div class="news-list" style="max-width: 650px; margin: 0 auto; box-sizing: border-box; font-family: sans-serif;">
    <?foreach($arResult["ITEMS"] as $arItem):?>
        
        <div class="news-list__item" style="margin-bottom: 35px; border-bottom: 1px solid #eee; padding-bottom: 20px;">
            
 
            <h2 class="news-list__title" style="font-size: 22px; color: #002B49; font-weight: normal; margin-bottom: 5px; line-height: 1.3;">
                <a href="<?=$arItem["DETAIL_PAGE_URL"]?>" style="color: #002B49; text-decoration: none;">
                    <?=$arItem["NAME"]?>
                </a>
            </h2>
            
            
            <div class="news-list__date" style="color: #999; font-size: 13px; margin-bottom: 15px;">
                <?=$arItem["DISPLAY_ACTIVE_FROM"]?>
            </div>
            
          
            <div style="display: flex; gap: 20px; align-items: flex-start;">
                
              
                <?if(is_array($arItem["PREVIEW_PICTURE"])):?>
                    <div style="flex: 0 0 150px;">
                        <a href="<?=$arItem["DETAIL_PAGE_URL"]?>">
                            <img src="<?=$arItem["PREVIEW_PICTURE"]["SRC"]?>" alt="<?=$arItem["NAME"]?>" style="width: 100%; height: auto; display: block;" />
                        </a>
                    </div>
                <?endif;?>
                
             
                <div class="news-list__preview" style="flex: 1; color: #555; font-size: 14px; line-height: 1.5;">
                    <?=$arItem["PREVIEW_TEXT"]?>
                    
                    <div style="margin-top: 10px;">
                        <a href="<?=$arItem["DETAIL_PAGE_URL"]?>" style="color: #002B49; text-decoration: underline; font-size: 13px;">
                            Подробнее
                        </a>
                    </div>
                </div>
                
            </div>
            
        </div>
    <?endforeach;?>
</div>


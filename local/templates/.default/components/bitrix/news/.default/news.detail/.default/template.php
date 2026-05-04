<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */

$APPLICATION->SetPageProperty("show_sidebar", "N");
$APPLICATION->SetTitle(""); 
?><?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */

$APPLICATION->SetTitle(""); 
?>

<div class="article-card" style="max-width: 1000px !important; margin: 0 auto !important; font-family: 'Arial', sans-serif !important; box-sizing: border-box !important; padding: 20px !important; background: #fff !important;">

    <div class="article-card__title" style="font-size: 28px !important; color: #002B49 !important; font-weight: normal !important; margin-bottom: 8px !important; line-height: 1.3 !important; text-align: left !important;">
        <?=$arResult["NAME"]?>
    </div>
    

    <div class="article-card__date" style="color: #999 !important; font-size: 14px !important; margin-bottom: 30px !important; text-align: left !important;">
        <?=$arResult["DISPLAY_ACTIVE_FROM"]?>
    </div>
    
   
    <div class="article-card__content" style="display: flex !important; gap: 40px !important; align-items: flex-start !important; flex-direction: row !important; flex-wrap: nowrap !important; width: 100% !important;">
        
    
        <div class="article-card__image" style="flex: 0 0 400px !important; max-width: 400px !important; width: 400px !important;">
            <?if(is_array($arResult["DETAIL_PICTURE"])):?>
                <img src="<?=$arResult["DETAIL_PICTURE"]["SRC"]?>" alt="<?=$arResult["NAME"]?>" style="width: 100% !important; height: auto !important; display: block !important; object-fit: cover !important;" />
            <?endif;?>
        </div>
        
     
        <div class="article-card__text" style="flex: 1 !important; display: flex !important; flex-direction: column !important; min-height: 250px !important; text-align: left !important;">
            
          
            <div class="block-content" style="line-height: 1.6 !important; color: #333 !important; font-size: 15px !important; text-align: left !important;">
                <?=$arResult["DETAIL_TEXT"]?>
            </div>
            
     
            <div style="margin-top: 40px !important;">
                <a class="article-card__button" href="/nov/" style="text-decoration: underline !important; text-transform: uppercase !important; color: #333 !important; font-size: 13px !important; font-weight: bold !important; letter-spacing: 0.5px !important; display: inline-block !important;">
                    Назад к новостям
                </a>
            </div>
            
        </div>
        
    </div>
</div>

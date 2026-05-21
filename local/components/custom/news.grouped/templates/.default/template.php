<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="grouped-news">
	<?if(empty($arResult["ITEMS"])):?>
		<p>Элементы не найдены.</p>
	<?else:?>
		<?foreach($arResult["ITEMS"] as $iblockId => $arItems):?>
			<div class="iblock-block" style="margin-bottom: 30px;">
				<h2><?=$arResult["IBLOCKS"][$iblockId] ?? "IBlock #".$iblockId?></h2>
				
				<div class="news-list">
					<?foreach($arItems as $arItem):?>
						<div class="news-item" id="<?=$arItem['ID']?>">
							<h3>
								<a href="<?=$arItem['DETAIL_PAGE_URL']?>"><?=$arItem['NAME']?></a>
							</h3>
							<?if($arItem['PREVIEW_TEXT']):?>
								<p><?=$arItem['PREVIEW_TEXT']?></p>
							<?endif;?>
						</div>
					<?endforeach;?>
				</div>
			</div>
		<?endforeach;?>
	<?endif;?>
</div>

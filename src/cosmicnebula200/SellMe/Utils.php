<?php

namespace cosmicnebula200\SellMe;

use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;

class Utils
{

    public static function sellItem(Player $player, Item $item): bool
    {
        $amount = self::getAmount($item);
        if ($amount === 0)
        return false;
	SellMe::getInstance()->getEconomyProvider()->addToMoney($player, $amount * $item->getCount(), [
		"item" => $item->getVanillaName(),
		"amount" => $item->getCount(),
	]);
        return true;
    }

    public static function getAmount(Item $item): int
    {
        if (SellMe::$prices->getNested("prices.{$item->getTypeId()}:{$item->getStateId()}") != false)
        return (int)SellMe::$prices->getNested("prices.{$item->getTypeId()}:{$item->getStateId()}");
       if (SellMe::$prices->getNested("prices.{$item->getTypeId()}") !== false)
       return (int)SellMe::$prices->getNested("prices.{$item->getTypeId()}");
        return 0;
    }

    public static function getName(string $data): string
    {
        $id = explode(":", $data)[0] ?? $data;
        $meta = explode(":", $data)[1] ?? 0;
        $idmeta = $id . ":" . $meta;
        $item = StringToItemParser::getInstance()->parse($idmeta);
        
    }
    
    public static function addToPrices(Player $player, int $price, bool $overwrite = false): void
    {
        $item = $player->getInventory()->getItemInHand();
        if (!$overwrite){
            if (Utils::getAmount($item) !== 0) {
                $player->sendMessage(SellMe::$messages->getMessage(
                    'sell.error-adding',
                    [],
                    "The item already exits in prices, you can use '/sell overwrite' command to overwrite the price"
                ));
                return;
            }
        }
        if ($price <= 0)
        {
            $player->sendMessage(SellMe::$messages->getMessage(
                'sell.non-positive',
                [],
                "The price can not be a non positive integer"
            ));
            return;
        }
        $string = $item->getStateId() == 0 ? "{$item->getTypeId()}" : "{$item->getTypeId()}:{$item->getStateId()}";
        SellMe::$prices->setNested("prices.$string", $price);
        SellMe::$prices->save();
        SellMe::$prices->reload();
        $player->sendMessage(SellMe::$messages->getMessage(
            'sell.added',
            [
                'item' => $item->getName(),
                'amount' => $price
            ],
            'Added {ITEM} for {AMOUNT} to the list of prices'
        ));
    }
    
}

<?php

namespace xBeastMode\RainbowArmor\Task;

use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use xBeastMode\RainbowArmor\RainbowArmor;

class ArmorTickTask extends Task{

        /** @var RainbowArmor */
        protected $plugin;
        /** @var Player */
        protected $player;
        /** @var array */
        protected $options = [];
        /** @var bool */
        protected $increasing = false;
        /** @var int */
        protected $step = 0;
        /** @var int */
        protected $colorLen = 0;
        /** @var array */
        protected $generatedColors = [];

        /**
         *
         * AcrobatTask constructor.
         *
         * @param RainbowArmor $rainbowArmor
         * @param Player       $player
         * @param array        $options
         *
         */
        public function __construct(RainbowArmor $rainbowArmor, Player $player, array $options){
                $this->plugin = $rainbowArmor;
                $this->player = $player;
                $this->options = $options;
                $this->generateColors();
        }

        /**
         *
         * @return void
         *
         */
        public function generateColors(): void{
                $colors = (int) $this->options["color-amount"];
                switch($this->options["type"]){
                        case "smooth":
                                $k = mt_rand(0xFF, 0xFFFFFF);
                                for($i = 0; $i <= $colors; ++$i){
                                        $k = $k + 0xFF;
                                        $this->generatedColors[] = $k;
                                }
                                break;
                        case "flash":
                                $k = mt_rand(0xFFFF, 0xFFFFFF);
                                for($i = 0; $i <= $colors; ++$i){
                                        $k = $k + $i;
                                        $this->generatedColors[] = $k;
                                }
                                break;
                }

                $this->colorLen = count($this->generatedColors) - 1;
        }

        /**
         *
         * @return int
         *
         */
        public function getNextColor(): int{
                if($this->step >= $this->colorLen){
                        $this->increasing = false;
                }elseif($this->step <= 0){
                        $this->increasing = true;
                }

                if($this->increasing){
                        $this->step++;
                }else{
                        $this->step--;
                }
                return $this->generatedColors[$this->step];
        }

        /**
         *
         * @param int $currentTick
         *
         */
        public function onRun(int $currentTick){
                if(!$this->player->isOnline() || !$this->plugin->hasRGBArmorEnabled($this->player)){
                        $this->getHandler()->cancel();
                        $this->player->getArmorInventory()->clearAll(true);
                        return;
                }

                $helmet = Item::get(Item::LEATHER_HELMET, 0, 1);
                $chest = Item::get(Item::LEATHER_CHESTPLATE, 0, 1);
                $legs = Item::get(Item::LEATHER_PANTS, 0, 1);
                $feet = Item::get(Item::LEATHER_BOOTS, 0, 1);

                $nbt = new CompoundTag("", []);

                $nbt->setInt("customColor", $this->getNextColor());

                $helmet->setCompoundTag($nbt);
                $chest->setCompoundTag($nbt);
                $legs->setCompoundTag($nbt);
                $feet->setCompoundTag($nbt);

                $this->player->getArmorInventory()->setContents([$helmet, $chest, $legs, $feet]);
        }
}
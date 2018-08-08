<?php
namespace xBeastMode\RainbowArmor;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\TextFormat;
use xBeastMode\RainbowArmor\Forms\CustomForm;
use xBeastMode\RainbowArmor\Task\ArmorTickTask;

class RainbowArmor extends PluginBase{
        /** @var bool */
         protected $rgbArmorEnabled = [];
         /** @var TaskHandler[] */
         protected $taskHandlers = [];
         /** @var int */
         protected $formId = 0;

        public function onEnable(){
                $this->saveDefaultConfig();
                $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

                $this->formId = mt_rand(11111, 999999);
        }

        /**
         *
         * @param Player $player
         *
         * @return bool
         *
         */
        public function hasRGBArmorEnabled(Player $player): bool{
                return isset($this->rgbArmorEnabled[spl_object_hash($player)]) && $this->rgbArmorEnabled[spl_object_hash($player)];
        }

        /**
         *
         * @param Player $player
         * @param bool   $enabled
         *
         */
        public function setRGBArmorEnabled(Player $player, bool $enabled = true){
                $this->rgbArmorEnabled[spl_object_hash($player)] = $enabled;
        }

        /**
         *
         * @param Player $player
         *
         */
        public function toggleRGBArmor(Player $player){
                $this->rgbArmorEnabled[spl_object_hash($player)] = !$this->rgbArmorEnabled[spl_object_hash($player)];
        }

        /**
         *
         * @param Player $player
         *
         */
        public function sendToggleForm(Player $player){
                if(count($player->getArmorInventory()->getContents(false)) > 0 && !$this->hasRGBArmorEnabled($player)){
                        $player->sendMessage(TextFormat::colorize($this->getConfig()->get("empty-armor")));
                        return;
                }
                $form = new CustomForm();
                $form->setId($this->formId);
                $form->setTitle(TextFormat::colorize("&cR&aG&bB &5ARMOR"));
                $form->setToggle(TextFormat::colorize("&aEnable RGB Armor?"), $this->hasRGBArmorEnabled($player));
                $form->setDropdown(TextFormat::colorize("&aType of display"), ["Smooth", "Flashy"], 0);
                $form->send($player);
        }

        /**
         *
         * @param Player $player
         * @param int    $formId
         * @param array  $response
         *
         */
        public function handleFormResponse(Player $player, int $formId, $response){
                switch($formId){
                        case $this->formId:
                                if($response[0]){
                                        if(isset($this->taskHandlers[spl_object_hash($player)])){
                                                $this->taskHandlers[spl_object_hash($player)]->cancel();
                                                $player->getArmorInventory()->clearAll(true);
                                        }
                                        $options = $this->getConfig()->get("options");
                                        $options["type"] = ($response[1] === 0) ? "smooth" : "flash";
                                        $task = new ArmorTickTask($this, $player, $options);
                                        $this->taskHandlers[spl_object_hash($player)] = $this->getScheduler()->scheduleRepeatingTask($task, (int) $this->getConfig()->get("interval"));
                                }
                                $this->setRGBArmorEnabled($player, $response[0]);
                                $player->sendMessage(TextFormat::colorize($this->getConfig()->get("toggle-message")));
                                break;
                }
        }

        /**
         *
         * @param CommandSender $sender
         * @param Command       $command
         * @param string        $label
         * @param array         $args
         *
         * @return bool
         *
         */
        public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
                switch(strtolower($command->getName())){
                        case "rgbarmor":
                                if($sender instanceof Player){
                                        if($sender->hasPermission($command->getPermission())){
                                                $this->sendToggleForm($sender);
                                        }else{
                                                $sender->sendMessage($command->getPermissionMessage());
                                        }
                                }else{
                                        $sender->sendMessage(TextFormat::RED . "Please use command in-game.");
                                }
                                break;
                }
                return false;
        }
}
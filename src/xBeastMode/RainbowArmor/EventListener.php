<?php
namespace xBeastMode\RainbowArmor;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

class EventListener implements Listener{
        /** @var RainbowArmor */
        protected $plugin;

        public function __construct(RainbowArmor $rainbowArmor){
                $this->plugin = $rainbowArmor;
        }

        /**
         *
         * @param DataPacketReceiveEvent $event
         *
         */
        public function onDataPacketReceiveEvent(DataPacketReceiveEvent $event) {
                $pk = $event->getPacket();
                $player = $event->getPlayer();

                if($pk instanceof ModalFormResponsePacket) {
                        $data = json_decode($pk->formData, true);
                        if($data !== null){
                                $this->plugin->handleFormResponse($player, $pk->formId, $data);
                        }
                }
        }
}
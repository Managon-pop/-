<?php

namespace Managon;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\network\protocol\UseItemPacket;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\entity\Entity;
use pocketmine\entity\FishingHook;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\math\Vector2;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\block\Block;

class rittai extends PluginBase implements Listener{
     public $speed = 1.5;
     private $c = 1;
     //重力は後で考えます
     public function onEnable(){
     	Server::getInstance()->getPluginManager()->registerEvents($this, $this);
     }

     public function onRecive(DataPacketReceiveEvent $event){
     	$packet = $event->getPacket();
     	$player = $event->getPlayer();
     	if($packet instanceof UseItemPacket){
         if($player->getInventory()->getItemInHand()->getId() === 280){
           $x = $player->x;
           $y = $player->y + 1.5;//目の高さ
           $z = $player->z;
           $yaw = $player->getYaw();
           $pitch = $player->getPitch();
           $this->moveHook($x,$y,$z,$yaw,$pitch,$player->getLevel(), $player);
     }
  }
}

     public function moveHook($px,$py,$pz,$yaw, $pitch, Level $level, Player $player){
      $this->c++;
      if($this->c > 100){ $player->sendTip("Miss"); $this->c = 0;return;}
       $x = cos(deg2rad($yaw+90))*$this->speed;
       $z = sin(deg2rad($yaw+90))*$this->speed;
       $y = tan(deg2rad(-$pitch))*$this->speed;
       $pos = new Vector3(floor($x + $px), floor($y + $py), floor($z + $pz));
       $block = $level->getBlock($pos);
       if($block->getId() !== 0){
        $this->c = 0;
          $pos2 = new Vector3($pos->x, $pos->y + 1, $pos->z);
          $block2 = $level->getBlock($pos2);
          if($block2->getId() === 0){
            $this->teleC($player, $pos2);
          }else{
            $player->sendTip("Miss...");
          }
       }else{
        $this->moveHook($x + $px, $y + $py, $z + $pz, $yaw, $pitch, $level, $player);
       }
     }

    public function teleC(Player $player, Vector3 $pos){//計算関数
      $px = $player->x;
      $py = $player->y;
      $pz = $player->z;
      $bx = $pos->x;
      $by = $pos->y;
      $bz = $pos->z;
      $dx = abs($px - $bx);
      $dz = abs($pz - $bz);
      $dtypo = ($dx**2+$dz**2)**(1/2);
      $dy = abs($py - $by);
      $d = ($dtypo**2+$dy**2)**(1/2);
      if($d < 0.05){
        return true;
      }else{
      $player->teleport($pos);
      //$this->tele($player, $d, $player->getYaw(), $player->getPitch());
    }
}

    public function tele(Player $player, $d, $yaw, $pitch){
      $x = cos(deg2rad($yaw))*$this->speed;
      $z = sin(deg2rad($yaw))*$this->speed;
      $dt = ($x**2 + $z**2)**(0.5);
      $y = tan(deg2rad(-$pitch))*$dt;
      $px = $player->x;
      $py = $player->y;
      $pz = $player->z;
      $pos = new Vector3($x+$px, $y+$py, $z+$pz);
      $player->teleport($pos);
      $this->teleC($player, $pos);
    }
}

<?php

/*
__PocketMine Plugin__
name=EntityPlugin
description=
version=Alpha 0.2
author=KoMC
class=EntityPlugin
apiversion=11,12,13
*/


class EntityPlugin implements Plugin{
	private $api;
	private $server;
	public function __construct(ServerAPI $api, $server = false){
		$this->spawn = 0;
		$this->api = $api;
		$this->server = ServerAPI::request();
	}
	public function __destruct(){}
	public function init(){
		$this->api->addHandler("entity.death", array($this, "han"));
		$this->api->addHandler("player.interact", array($this, "han"));
		$this->conf = new Config($this->api->plugin->configPath($this)."config.yml",CONFIG_YAML, array(
			"스폰시간" => 15,//20이 1초
			"몬스터최대수" => 25,
		));
		$this->api->schedule($this->conf->get("스폰시간"), array($this, "han"), array(), true, "Monster.spawn");
	}
	
	public function han($data, $event){
		switch($event){
			case "player.interact":
				$entity = $data["targetentity"];
				$player = $data["entity"]->player;
				if($data["action"] == 1){
					$slot = $player->getSlot($player->slot);
					if($slot->getID() == BUCKET and $entity->type == COW){
						$player->setSlot($player->slot,new Item(BUCKET,1,1),false);
					}
					return false;
				}
				break;
			case "Monster.Spawn":
				$level = $this->api->level->getDefault();
				if($this->api->time->getPhase($level) == "night"){//밤일시에 몬스터 스폰
					if($this->spawn < $this->conf->get("몬스터최대수")){
						$mobrand = mt_rand(32,36);
						$data = array(
							"x" => mt_rand(0,25580)/100,
							"y" => mt_rand(50,127),
							"z" => mt_rand(0,25580)/100,
						);
						$block = $level->getBlock(new Vector3($data["x"],$data["y"],$data["z"]));
						$blockd = $level->getBlock(new Vector3($data["x"],$data["y"]-1,$data["z"]));
						if(($block->getID() == 0 or !$block->isFullBlock)
						and $blockd->getID() != 0 and $blockd->isFullBlock){
							$this->spawn++;
							$e = $this->api->entity->add($level, ENTITY_MOB, $mobrand, $data);
							$this->api->entity->spawnToAll($e);
						}
					}
				}
				if($this->spawn < $this->conf->get("몬스터최대수")){
					$mobrand = mt_rand(10,13);
					$data = array(
						"x" => mt_rand(0,25580)/100,
						"y" => mt_rand(50,127),
						"z" => mt_rand(0,25580)/100,
					);
					$block = $level->getBlock(new Vector3($data["x"],$data["y"],$data["z"]));
					$blockd = $level->getBlock(new Vector3($data["x"],$data["y"]-1,$data["z"]));
					if(($block->getID() == 0 or !$block->isFullBlock)
					and $blockd->getID() != 0 and $blockd->isFullBlock){
						$this->spawn++;
						$e = $this->api->entity->add($level, ENTITY_MOB, $mobrand, $data);
						$this->api->entity->spawnToAll($e);
					}
				}
				break;
			case "entity.death":
				$this->spawn--;
				break;
		}
	}
}

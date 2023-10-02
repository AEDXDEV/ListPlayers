<?php

/*
by AEDXDV

Youtube: @AEDXDEV
Discord: aedxdev

*/
namespace AEDXDEV\ListPlayers;

use JaxkDev\DiscordBot\Models\Messages\Embed\Embed;
use JaxkDev\DiscordBot\Models\Messages\Embed\Field;
use JaxkDev\DiscordBot\Models\Messages\Embed\Footer;
use JaxkDev\DiscordBot\Plugin\Events\MessageSent;
use JaxkDev\DiscordBot\Plugin\Api;
use JaxkDev\DiscordBot\Plugin\Main as DiscordBot;
use JaxkDev\DiscordBot\Plugin\ApiResultion;
use JaxkDev\DiscordBot\Plugin\ApiRejection;
use JaxkDev\DiscordBot\Models\Messages\Message;

use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class Main extends PluginBase implements Listener{
  
  public DiscordBot $discord;
  
  public Config $config;
  
  public function onEnable() : void{
    $discord = $this->getServer()->getPluginManager()->getPlugin("DiscordBot");
    if (!$discord instanceof DiscordBot){
      $this->getLogger()->info("Incompatible dependency 'DiscordBot' detected, see https://github.com/DiscordBot-PMMP/DiscordBot/releases for the correct plugin.");
      $this->getServer()->getPluginManager()->disablePlugin($this);
      return;
    }
    $this->discord = $discord;
    $this->config = new Config($this->getDataFolder() . "config.yml", 2, ["ServerId" => 1234567890]);
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }
  
  public function MessageSent(MessageSent $event){
    $api = $this->discord->getApi();
    $message = $event->getMessage();
    $content = $message->getContent();
    $channel_id = $message->getChannelId();
    $args = explode(" ", $content);
    // command
    if(isset($args[0]) && $args[0] == "!list"){
      $onlines = $this->getServer()->getOnlinePlayers();
      $p = count($onlines) . "/" . $this->getServer()->getMaxPlayers();
      $players =  implode("\n", array_map(fn(Player $player) => $player->getName(), $onlines));
      $server_id = $this->config->get("ServerId", 1234567890);
      if ($server_id == 1234567890){
        $this->getLogger()->info("§cPlease Add Discord Server Id In config.yml");
        $this->getServer()->getPluginManager()->disablePlugin($this);
        return false;
      }
      $api->sendMessage($server_id, $channel_id, null, $message->getId(), [new Embed(
        "List Players",
        count($onlines) === 0 ? "There Are No Players In The Server" : $p,
        null, time(), null,
        new Footer("List Players v" . $this->getDescription()->getVersion()),
        null, null, null, null, null,
        count($onlines) === 0 ? [] : [new Field("Players", $players, true)]
      )])->otherwise(function(ApiRejection $rejection){
        $this->getLogger()->error("Failed to send command response: " . $rejection->getMessage());
      });
    }
  }
}

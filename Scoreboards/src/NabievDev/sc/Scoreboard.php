<?php

declare(strict_types=1);

namespace NabievDev\sc;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\Player;

/**
 * Class Scoreboard
 * @package NabievDev\sc
 */
class Scoreboard
{
    /**
     * @var array
     */
    public $scoreboards = [];

    /**
     * Scoreboard constructor.
     * @param Player $player
     * @param string $objectiveName
     * @param string $displayName
     */
    public function __construct(Player $player, string $objectiveName, string $displayName)
    {
        if (isset($this->scoreboards[$player->getName()])) {
            $this->remove($player);
        }
        $pk = new SetDisplayObjectivePacket();
        $pk->displaySlot = "sidebar";
        $pk->objectiveName = $objectiveName;
        $pk->displayName = $displayName;
        $pk->criteriaName = "dummy";
        $pk->sortOrder = 0;
        $player->sendDataPacket($pk);
        $this->scoreboards[$player->getName()] = $objectiveName;
    }

    /**
     * @param Player $player
     */
    public function remove(Player $player)
    {
        $pk = new RemoveObjectivePacket();
        $pk->objectiveName = $this->getObjectiveName($player);;
        $player->sendDataPacket($pk);
        unset($this->scoreboards[$player->getName()]);
    }

    /**
     * @param Player $player
     * @param int $score
     * @param string $line
     */
    public function setLine(Player $player, int $score, string $line)
    {
        if (!isset($this->scoreboards[$player->getName()])) {
            return;
        }
        if ($score > 15 or $score < 0) {
            return;
        }
        $entry = new ScorePacketEntry();
        $entry->objectiveName = $this->getObjectiveName($player);
        $entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
        $entry->customName = $line;
        $entry->score = $score;
        $entry->scoreboardId = $score;
        $pk = new SetScorePacket();
        $pk->type = SetScorePacket::TYPE_CHANGE;
        $pk->entries[] = $entry;
        $player->sendDataPacket($pk);
    }

    /**
     * @param Player $player
     * @param int $line
     */
    public function setEmptyLine(Player $player, int $line)
    {
        $this->setLine($player, $line, "");
    }

    /**
     * @param Player $player
     * @return string|null
     */
    public function getObjectiveName(Player $player): ?string
    {
        return isset($this->scoreboards[$player->getName()]) ? $this->scoreboards[$player->getName()] : null;
    }
}
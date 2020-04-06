<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Elements;
use aieuo\ip\form\Form;
use aieuo\ip\utils\Language;
use pocketmine\level\particle\AngryVillagerParticle;
use pocketmine\level\particle\BlockForceFieldParticle;
use pocketmine\level\particle\BubbleParticle;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\level\particle\EnchantmentTableParticle;
use pocketmine\level\particle\EnchantParticle;
use pocketmine\level\particle\ExplodeParticle;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\HappyVillagerParticle;
use pocketmine\level\particle\HeartParticle;
use pocketmine\level\particle\HugeExplodeParticle;
use pocketmine\level\particle\HugeExplodeSeedParticle;
use pocketmine\level\particle\InkParticle;
use pocketmine\level\particle\InstantEnchantParticle;
use pocketmine\level\particle\LavaDripParticle;
use pocketmine\level\particle\LavaParticle;
use pocketmine\level\particle\Particle;
use pocketmine\level\particle\PortalParticle;
use pocketmine\level\particle\RainSplashParticle;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\level\particle\SmokeParticle;
use pocketmine\level\particle\SplashParticle;
use pocketmine\level\particle\SporeParticle;
use pocketmine\level\particle\WaterDripParticle;
use pocketmine\level\particle\WaterParticle;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\Server;

class AddParticleRange extends Process {

    protected $id = self::ADD_PARTICLE_RANGE;
    protected $name = "@process.addParticleRange.name";
    protected $description = "@process.addParticleRange.description";

    public function getDetail(): string {
        $pos1 = $this->getPosition1();
        $pos2 = $this->getPosition2();
        $particle = $this->getValues()[2] ?? "";
        if (!($pos1 instanceof Position) or $pos1->level === null or !($pos2 instanceof Position) or empty($particle)) return false;
        return Language::get("process.addParticle.detail", [
            $pos1->x, $pos1->y, $pos1->z, $pos1->level->getFolderName(), $pos2->x, $pos2->y, $pos2->z, $particle
        ]);
    }

    public function execute() {
        $player = $this->getPlayer();
        $pos1 = $this->getPosition1();
        $pos2 = $this->getPosition2();
        $particle = $this->getParticle($pos1);
        if (!($pos1 instanceof Position) or !($pos2 instanceof Position)) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return;
        }

        $isPMMPParticle = $particle instanceof Particle;
        $level = $pos1->level;

        $sx = min($pos1->x, $pos2->x);
        $sy = min($pos1->y, $pos2->y);
        $sz = min($pos1->z, $pos2->z);
        $ex = max($pos1->x, $pos2->x);
        $ey = max($pos1->y, $pos2->y);
        $ez = max($pos1->z, $pos2->z);
        for ($x=$sx; $x<=$ex; $x++) {
            for ($y=$sy; $y<=$ey; $y++) {
                for ($z=$sz; $z<=$ez; $z++) {
                    if ($isPMMPParticle) {
                        $particle->setComponents($x, $y, $z);
                        $level->addParticle($particle);
                    } else {
                        $pk = new SpawnParticleEffectPacket();
                        $pk->position = new Position($x, $y, $z, $level);
                        $pk->particleName = $this->getValues()[2] ?? "";
                        Server::getInstance()->broadcastPacket($level->getPlayers(), $pk);
                    }
                }
            }
        }
    }

    public function getPosition1(): ?Position {
        return $this->getValues()[0] ?? null;
    }

    public function getPosition2(): ?Position {
        return $this->getValues()[1] ?? null;
    }

    public function getParticle(Position $pos, string $name = null): ?Particle {
        $name = $name ?? $this->getValues()[2] ?? "";
        switch($name) {
            case "explode":
                return new ExplodeParticle($pos);
            case "hugeexplosion":
                return new HugeExplodeParticle($pos);
            case "hugeexplosionseed":
                return new HugeExplodeSeedParticle($pos);
            case "bubble":
                return new BubbleParticle($pos);
            case "splash":
                return new SplashParticle($pos);
            case "wake":
            case "water":
                return new WaterParticle($pos);
            case "crit":
                return new CriticalParticle($pos);
            case "smoke":
                return new SmokeParticle($pos);
            case "spell":
                return new EnchantParticle($pos);
            case "instantspell":
                return new InstantEnchantParticle($pos);
            case "dripwater":
                return new WaterDripParticle($pos);
            case "driplava":
                return new LavaDripParticle($pos);
            case "townaura":
            case "spore":
                return new SporeParticle($pos);
            case "portal":
                return new PortalParticle($pos);
            case "flame":
                return new FlameParticle($pos);
            case "lava":
                return new LavaParticle($pos);
            case "reddust":
                return new RedstoneParticle($pos);
            case "heart":
                return new HeartParticle($pos);
            case "ink":
                return new InkParticle($pos);
            case "droplet":
                return new RainSplashParticle($pos);
            case "enchantmenttable":
                return new EnchantmentTableParticle($pos);
            case "happyvillager":
                return new HappyVillagerParticle($pos);
            case "angryvillager":
                return new AngryVillagerParticle($pos);
            case "forcefield":
                return new BlockForceFieldParticle($pos);
            default:
                return null;
        }
    }

    public function parse(string $content) {
        $positions = explode("[position2]", $content);
        if (!isset($positions[1])) return false;
        $position1 = $this->parsePosition($positions[0]);
        if ($position1 === false) return false;
        $particles = explode("[particle]", $positions[1]);
        if (!isset($particles[1])) return false;
        $position2 = $this->parsePosition($particles[0]);
        if ($position2 === false) return false;
        $particle = $particles[1];
        return [$position1, $position2, $particle];
    }

    public function parsePosition(string $pos) {
        if (!preg_match("/\s*(-?[0-9]+\.?[0-9]*)\s*,\s*(-?[0-9]+\.?[0-9]*)\s*,\s*(-?[0-9]+\.?[0-9]*)\s*,?\s*(.*)\s*/", $pos, $matches)) return false;
        if (empty($matches[4])) $matches[4] = "world";
        return new Position((float)$matches[1], (float)$matches[2], (float)$matches[3], Server::getInstance()->getLevelByName($matches[4]));
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $settings = $this->parse($default);
        $positions = explode("[position2]", $default);
        $position1 = $positions[0];
        $particles = explode("[particle]", $positions[1] ?? $default);
        $position2 = $particles[0];
        $particle = $particles[1] ?? $positions[1] ?? $default;
        if ($settings !== false) {
            $position1 = $settings[0]->x.",".$settings[0]->y.",".$settings[0]->z.",".$settings[0]->level->getFolderName();
            $position2 = $settings[1]->x.",".$settings[1]->y.",".$settings[1]->z;
            $particle = $settings[2];
        } elseif ($default !== "") {
            $mes .= Language::get("form.error");
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.addParticleRange.form.pos1"), Language::get("input.example", ["1,15,30,world"]), $position1),
                Elements::getInput(Language::get("process.addParticleRange.form.pos2"), Language::get("input.example", ["1,15,30,world"]), $position2),
                Elements::getInput(Language::get("process.addParticle.form.particle"), Language::get("input.example", ["explode"]), $particle),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        return Form::encodeJson($data);
    }

    public function parseFormData(array $data) {
        $status = true;
        if ($data[1] === "" or $data[2] === "" or $data[3] === "") {
            $status = null;
        } else {
            $pos1 = $this->parsePosition($data[1]);
            $pos2 = $this->parsePosition($data[2]);
            if ($pos1 === false or $pos2 === false) $status = false;
        }
        return ["status" => $status, "contents" => $data[1]."[position2]".$data[2]."[particle]".$data[3], "delete" => $data[4], "cancel" => $data[5]];
    }

}
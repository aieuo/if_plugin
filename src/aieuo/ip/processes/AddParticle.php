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

class AddParticle extends Process {

    protected $id = self::ADD_PARTICLE;
    protected $name = "@process.addParticle.name";
    protected $description = "@process.addParticle.description";

    public function getDetail(): string {
        $pos = $this->getPosition();
        $particle = $this->getValues()[1] ?? "";
        $amount = $this->getAmount() ?? 1;
        if (!($pos instanceof Position) or $pos->level === null or empty($particle)) return false;
        return Language::get("process.addParticle.detail", [$pos->x, $pos->y, $pos->z, $pos->level->getFolderName(), $particle, $amount]);
    }

    public function execute() {
        $player = $this->getPlayer();
        $pos = $this->getPosition();
        $particle = $this->getParticle();
        $amount = $this->getAmount() ?? 1;
        if (!($pos instanceof Position)) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return;
        }

        $isPMMPParticle = $particle instanceof Particle;
        for ($i=0; $i<$amount; $i++) {
            if ($isPMMPParticle) {
                $pos->level->addParticle($particle);
            } else {
                $pk = new SpawnParticleEffectPacket();
                $pk->position = $pos;
                $pk->particleName = $this->getValues()[1] ?? "";
                Server::getInstance()->broadcastPacket($pos->level->getPlayers(), $pk);
            }
        }
    }

    public function getPosition(): ?Position {
        return $this->getValues()[0] ?? null;
    }

    public function getParticle(string $name = null, Position $pos = null): ?Particle {
        $name = $name ?? $this->getValues()[1] ?? "";
        $pos = $pos ?? $this->getPosition();
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

    public function getAmount(): ?int {
        return $this->getValues()[2] ?? null;
    }

    public function parse(string $content) {
        $positions = explode("[particle]", $content);
        if (!isset($positions[1])) return false;
        $position = $this->parsePosition($positions[0]);
        if ($position === false) return false;
        $particles = explode("[amount]", $positions[1]);
        $particle = $particles[0];
        $amount = $particles[1] ?? 1;
        return [$position, $particle, (int)$amount];
    }

    public function parsePosition(string $pos) {
        if (!preg_match("/\s*(-?[0-9]+\.?[0-9]*)\s*,\s*(-?[0-9]+\.?[0-9]*)\s*,\s*(-?[0-9]+\.?[0-9]*)\s*,?\s*(.*)\s*/", $pos, $matches)) return false;
        if (empty($matches[4])) $matches[4] = "world";
        return new Position((float)$matches[1], (float)$matches[2], (float)$matches[3], Server::getInstance()->getLevelByName($matches[4]));
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $settings = $this->parse($default);
        $positions = explode("[particle]", $default);
        $position = $positions[0];
        $particles = explode("[amount]", $positions[1] ?? $default);
        $particle = $particles[0];
        $amount = $particles[1] ?? $positions[1] ?? $default;
        if ($settings !== false) {
            $position = $settings[0]->x.",".$settings[0]->y.",".$settings[0]->z.",".$settings[0]->level->getFolderName();
            $particle = $settings[1];
            $amount = $settings[2];
        } elseif ($default !== "") {
            $mes .= Language::get("form.error");
        }
        if (empty($amount)) $amount = 1;
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.position.form.position"), Language::get("input.example", ["1,15,30,world"]), $position),
                Elements::getInput(Language::get("process.addParticle.form.particle"), Language::get("input.example", ["explode"]), $particle),
                Elements::getInput(Language::get("process.addParticle.form.amount"), Language::get("input.example", ["1"]), (string)$amount),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        return Form::encodeJson($data);
    }

    public function parseFormData(array $data) {
        $status = true;
        if ($data[1] === "" or $data[2] === "") {
            $status = null;
        } else {
            $pos = $this->parsePosition($data[1]);
            if ($pos === false) $status = false;
            $data[3] = $data[3] ?? 1;
        }
        return ["status" => $status, "contents" => $data[1]."[particle]".$data[2]."[amount]".$data[3], "delete" => $data[4], "cancel" => $data[5]];
    }

}
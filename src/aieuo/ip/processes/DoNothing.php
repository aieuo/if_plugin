<?php

namespace aieuo\ip\processes;

use aieuo\ip\utils\Language;

class DoNothing extends Process {

    protected $id = self::DO_NOTHING;
    protected $name = "@process.donothing.name";
    protected $description = "@process.donothing.description";

    public function getMessage() {
        return Language::get("process.donothing.detail");
    }
}
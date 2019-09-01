<?php

namespace aieuo\ip\conditions;

class NoCheck extends Condition {

    protected $id = self::NO_CHECK;
    protected $name = "@condition.nocheck.name";
    protected $description = "@condition.nocheck.description";
    protected $detail = "@condition.nocheck.detail";

    public function check() {
        return self::MATCHED;
    }
}
<?php

namespace aieuo\ip\processes;

class DoNothing extends Process {

    protected $id = self::DO_NOTHING;
    protected $name = "@process.donothing.name";
    protected $description = "@process.donothing.description";
    protected $detail = "@process.donothing.detail";

    }
}
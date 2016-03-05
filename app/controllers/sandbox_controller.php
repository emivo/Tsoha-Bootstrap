<?php

class SandboxController extends BaseController
{

    public static function sandbox()
    {
        $first = Recipe::find(1);
        $recipes = Recipe::all();

        Kint::dump($recipes);
        Kint::dump($first);
    }

}

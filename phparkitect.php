<?php

declare(strict_types=1);

use Arkitect\ClassSet;
use Arkitect\CLI\Config;
use Arkitect\Expression\ForClasses\HaveNameMatching;
use Arkitect\Expression\ForClasses\NotHaveDependencyOutsideNamespace;
use Arkitect\Expression\ForClasses\NotHaveNameMatching;
use Arkitect\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Arkitect\Rules\Rule;

return static function (Config $config): void {
    $mvcClassSet = ClassSet::fromDir(__DIR__.'/src');

    $rules = [];

    $rules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\Application\Controller'))
        ->should(new HaveNameMatching('*Controller'))
        ->because('we want uniform naming');

    $rules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\Domain'))
        ->should(new NotHaveDependencyOutsideNamespace('App\Domain'))
        ->because('we want protect our domain');

    $rules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App'))
        ->should(new NotHaveNameMatching('*Manager'))
        ->because('*Manager is too vague in naming classes');

    $config
        ->add($mvcClassSet, ...$rules);
};

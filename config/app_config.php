<?php

// config/app_config.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;


return static function (ContainerConfigurator $container) {
    $container->parameters()
        // the parameter name is an arbitrary string (the 'app.' prefix is recommended
        // to better differentiate your parameters from Symfony parameters).
        ->set('app.admin_email', 'angel.ananiev@gmail.com')

        // How much in the future appointments can be made
        ->set('app.months_in_advance', 5)

        // Working days in week
        ->set('app.working_days', [1,2,3,4,5])

        // Working hours for appointments
        ->set('app.working_hours', '9:00-17:00');
};

?>
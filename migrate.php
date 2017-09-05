#!/usr/bin/env php
<?php

/**
 *
 * Черновое решение проблемы ненулевого статуса. Не лечит все кейсы.
 * По-нормальному должен вылечить автор модуля, которому подано обращение:
 * https://bitbucket.org/andrey_ryabin/sprint.migration/issues/31/non-zero-exit-status
 *
 */
try {

    require_once realpath(__DIR__) . '/local/modules/sprint.migration/tools/migrate.php';

} catch (Throwable $exception) {

    echo sprintf(
        "[%s] %s (%s)\n%s\n",
        get_class($exception),
        $exception->getMessage(),
        $exception->getCode(),
        $exception->getTraceAsString()
    );

    //Provide non-zero exit status
    die(1);

}

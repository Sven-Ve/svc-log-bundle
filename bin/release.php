#!/usr/bin/env php
<?php

declare(strict_types=1);

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) 2025 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$version = '9.6.0';
$message = 'Tested with svc-utilbundle 7.x too.';

echo "Running phpstan:\n";
system('composer run-script phpstan', $res);
if ($res > 0) {
    echo "\nError during execution phpstan. Releasing canceled.\n";

    return 1;
}

echo "Running tests:\n";
system('composer run-script test', $res);
if ($res > 0) {
    echo "\nError during execution test scripts. Releasing cannceled.\n";

    return 1;
}

file_put_contents('CHANGELOG.md', "\n\n## Version " . $version, FILE_APPEND);
file_put_contents('CHANGELOG.md', "\n*" . date('r') . '*', FILE_APPEND);
file_put_contents('CHANGELOG.md', "\n- " . $message . "\n", FILE_APPEND);

$res = shell_exec('git add .');
$res = shell_exec('git commit -S -m "' . $message . '"');
$res = shell_exec('git push');

$res = shell_exec('git tag -a -s ' . $version . ' -m "' . $message . '"');
$res = shell_exec('git push origin ' . $version);

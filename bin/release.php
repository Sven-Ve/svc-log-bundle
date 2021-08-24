#!/usr/bin/env php
<?php

$version = "1.5.0";
$message = "added color classes to the log result table";
file_put_contents("CHANGELOG.md", "\n\n## Version " . $version, FILE_APPEND);
file_put_contents("CHANGELOG.md", "\n*" . date("r") . "*", FILE_APPEND);
file_put_contents("CHANGELOG.md", "\n- " . $message . "\n", FILE_APPEND);

$res = shell_exec('git add .');
$res = shell_exec('git commit -S -m "' . $message . '"');
$res = shell_exec('git push');

$res = shell_exec('git tag -a -s ' . $version . ' -m "' . $message . '"');
$res = shell_exec('git push origin ' . $version);

?>

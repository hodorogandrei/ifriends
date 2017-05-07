<?php

/*
 * This is the process being called by the user.
 * From here, we'll launch child process child.php
 */

$p1 = 20000;
$p2 = 40000;


// open child process
$handle = popen('/home/ifriwqgi/public_html/PHPtestbed/child.php', 'r');

/*
 * Do some work, while already doing other
 * work in the child process.
 */

$counter = 0;
    for ($i = 0; $i < $p1; $i++) {
        $counter++;
    }

// get response from child (if any) as soon at it's ready:
echo $handle . gettype($handle) . "\n";
$read = fread($handle, 1048);
echo $read;
pclose($handle);


?>
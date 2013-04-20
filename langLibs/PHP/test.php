<?php

include('./prflr.php');

// configure profiler
//  // set  profiler server:port  and  set Source for timers 
PRFLR::init('127.0.0.1', '5000', '11msHost');

PRFLR::Begin('checkUDP');
for ($i = 0; $i < 100000; $i++) {
//start timer
    $r = rand(1,9);
    PRFLR::Begin('test.timer'.$r);
    //sleep(1);
    PRFLR::End('test.timer'.$r, "step {$i}");
}
PRFLR::End('checkUDP');
?>

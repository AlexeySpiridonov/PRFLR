<?php

include('./prflr.php');

// configure profiler
//  // set  profiler server:port  and  set Group for timers 
PRFLR::init('127.0.0.1', '4000', 'localhost.test');

PRFLR::Begin('checkUDP');
for ($i = 0; $i < 10; $i++) {
//start timer
    PRFLR::Begin('test.timer');
    sleep(1);
    PRFLR::End('test.timer', "step {$i}");
}
PRFLR::End('checkUDP');
?>

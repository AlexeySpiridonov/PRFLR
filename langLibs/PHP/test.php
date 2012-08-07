<?php

include('./prflr.php');

// configure profiler
//  // set  profiler server:port  and  set Group for timers 
PRFLR::init('46.4.114.218', '4000', 'AirBookSpall');

PRFLR::Begin('checkUDP');
for ($i = 0; $i < 100000000; $i++) {
//start timer
    PRFLR::Begin('test.timer');
    //sleep(1);
    PRFLR::End('test.timer', "step {$i}");
}
PRFLR::End('checkUDP');
?>

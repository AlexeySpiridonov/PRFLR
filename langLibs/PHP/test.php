<?php

include('./prflr.php');

// configure profiler
//  // set  profiler server:port  and  set Group for timers 
PRFLR::init('46.4.114.218', '4000', 'AirBookSpall');

PRFLR::Begin('checkUDP');
for ($i = 0; $i < 1000; $i++) {
//start timer
    $r = rand(1,9);
    PRFLR::Begin('test.timer'.$r);
    //sleep(1);
    PRFLR::End('test.timer'.$r, "step {$i}");
}
PRFLR::End('checkUDP','info');
?>

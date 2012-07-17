<?php

class dispatcher {

    public $request;

    public function stat_last() {
        return array(
            array(
                'timer' => "first.timer.1",
                'group' => "myluckyserver.ru",
                'thread' => "fgr456dg5674hfgc",
                'info' => 'good request',
                'time' => array(
                    'current' => 23,
                    'min' => 12,
                    'max' => 1234,
                    'average' => 45,
                    'total' => 123345,
                ),
                'count' => 456,
            ),
            array(
                'timer' => "first.timer.2",
                'group' => "mybadserver.ru",
                'thread' => "fgr456dg5674hfgc",
                'info' => 'bad request',
                'time' => array(
                    'current' => 13,
                    'min' => 122,
                    'max' => 714,
                    'average' => 45,
                    'total' => 123345,
                ),
                'count' => 456,
            ),
        );
    }

    public function stat_aggregate() {
        return array(
            array(
                'timer' => "first.timer.1",
                'group' => "myluckyserver.ru",
                'time' => array(
                    'min' => 12,
                    'max' => 1234,
                    'average' => 45,
                    'total' => 123345,
                ),
                'count' => 456,
            ),
            array(
                'timer' => "first.timer.2",
                'group' => "mybadserver.ru",
                'time' => array(
                    'min' => 122,
                    'max' => 714,
                    'average' => 45,
                    'total' => 123345,
                ),
                'count' => 456,
            ),
        );
    }

    public function stat_graph() {
        return array(
            array(
                'timer' => 'my.first.timer',
                'data' => array(
                    '0' => 12,
                    '1' => 15,
                    '2' => 17,
                ),
            ),
                        array(
                'timer' => 'my.second.timer',
                'data' => array(
                    '0' => 22,
                    '1' => 17,
                    '2' => 10,
                ),
            ),
        );
    }

    public function stat_groups() {
        
    }

    public function stat_slow() {
         return array(
            array(
                'timer' => "first.timer.1",
                'group' => "myluckyserver.ru",
                'time' => array(
                    'min' => 12,
                    'max' => 1234,
                    'average' => 45,
                    'total' => 123345,
                ),
                'count' => 456,
            ),
            array(
                'timer' => "first.timer.2",
                'group' => "mybadserver.ru",
                'time' => array(
                    'min' => 122,
                    'max' => 714,
                    'average' => 45,
                    'total' => 123345,
                ),
                'count' => 456,
            ),
        );       
    }

    public function settings() {
        return array(
            'store' => 60*15,
            'block' => array(
                'timers' => array(
                    'btime.rrt.*',
                    'mtime.*.ff',
                ),
                'groups' => array(
                    '127.0.0.*',
                    '192.168.0.*',
                ),
            ),
        );
    }

}

$d = new dispatcher();

$d->request = $_GET;

$r = str_replace('/', '_', $_GET['r']);

echo json_encode($d->$r);

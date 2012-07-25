<?php

class dispatcher {

    public $request;
    private $mongo;
    private $data;

    public function __construct() {
        $this->mongo = new Mongo("mongodb://prflr:prflr@localhost");
        $this->data = $this->mongo->prflr->timers;
    }

    public function __destruct() {
        $this->mongo->close();
    }

    public function init() {

        for ($i = 0; $i < 3000; $i++) {
            $this->data->insert(array(
                'group' => 'group.' . rand(1, 2),
                'timer' => 'timer.' . rand(1, 9),
                'info' => 'info' . rand(1, 3),
                'thread' => 'somethread' . rand(1, 3),
                'duration' => rand(2, 999),
            ));
        }
        return array('add' => true);
    }

    private function prepareCriteria() {

        $par = split('/', $_GET["filter"]);

        $criteria = array();

        if (isset($par[0]) && $par[0] != '*')
            $criteria['group'] = new MongoRegex("/^" . $par[0] . "/i");
        if (isset($par[1]) && $par[1] != '*')
            $criteria['timer'] = new MongoRegex("/^" . $par[1] . "/i");
        if (isset($par[2]) && $par[2] != '*')
            $criteria['info'] = new MongoRegex("/^" . $par[2] . "/i");
        if (isset($par[3]) && $par[3] != '*')
            $criteria['thread'] = new MongoRegex("/^" . $par[3] . "/i");

        return $criteria;
    }

    private function prepareGroupBy() {

        $keys = array("timer" => 1, "group" => 2);
        $initial = array("time" => array("min" => 0, "max" => 0, "total" => 0), "count" => 0);
        $reduce = "function (obj, prev) {
prev.count++;
prev.time.total += prev.duration;
if (prev.time.min > obj.duration) prev.time.min = obj.duration;
if (prev.time.max < obj.duration) prev.time.max = obj.duration;

}";

        return array($keys, $initial, $reduce);
    }

    public function stat_last() {

        $criteria = $this->prepareCriteria();

        $gr = $this->prepareGroupBy();

        $data = $collection->group($gr[0], $gr[1], $gr[2], $criteria)->sort(array('time.max' => -1));

        return $data;

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

        $criteria = $this->prepareCriteria();

        $gr = $this->prepareGroupBy();

        $data = $collection->group($gr[0], $gr[1], $gr[2], $criteria)->sort(array('time.max' => -1));

        return $data;

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
            'store' => 60 * 15,
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

unset($d);


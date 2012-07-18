<?

class PRFLR {

    private $timers;
    private $delayedSend = false;
    public static $group;
    public static $thread;

    public function __construct($group = false) {

        if (!$group)
            $this->group = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'Unknown';
        else
            $this->group = $group;
    }

    public static function Start($timer) {        
        $this->timers[$timer] = microtime();
    }

    public static function Stop($timer, $info = '') {

        if (!isset($this->timers[$timer]))
            return false;

        $delay = microtime() - $this->timers[$timer];



        if (!$this->delayedSend) {
            $this->send();
        }
    }

    private static function send() {

        unset($this->timers[$timer]);
    }

}


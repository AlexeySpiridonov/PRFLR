<?

class PRFLR {

    private static $sender;

    public static function init($server, $port, $group, $delayedSend) {
        self::$sender = new PRFLRSender();
        self::$sender->server = $server;
        self::$sender->port = $port;
        self::$sender->delayedSend = $delayedSend;
        self::$sender->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if (!$group)
            self::$sender->group = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'Unknown';
        else
            self::$sender->group = $group;
        self::$sender->thread = uniqid();
    }

    public static function begin($timer) {
        self::$sender->Begin($timer);
    }

    public static function end($timer, $info = '') {
        self::$sender->End($timer, $info);
    }

    public function __destruct() {
        unset(self::$sender);
    }

}

class PRFLRSender {

    private $timers;
    private $socket;
    public $delayedSend = false;
    public $group;
    public $thread;
    public $server;
    public $port;

    public function __construct() {
        
    }

    public function __destruct() {
        socket_close($this->socket);
    }

    public function Begin($timer) {
        $this->timers[$timer] = microtime();
    }

    public function End($timer, $info = '') {

        if (!isset($this->timers[$timer]))
            return false;

        $delay = microtime() - $this->timers[$timer];

        //if (!$this->delayedSend) {
        $this->send($timer, $delay, $info);
        
        unset($this->timers[$timer]);
        //}
    }

    private function send($timer, $duration, $info = '') {
        
        $message = join( array($this->thread, $this->group, $timer, $duration, $info), '|');
        
        if ($this->socket) {
            socket_sendto($socket, $message, strlen($message), 0, $this->server, $this->port);
        } else {
            throw new Exception("Socket not exist\n");
        }
       
    }

}
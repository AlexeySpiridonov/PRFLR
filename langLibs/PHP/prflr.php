<?

/*
 *  HOW TO USE 
 * 
 * // configure profiler
 * // set  profiler server:port  and  set source for timers 
 * PRFLR::init(192.168.1.45-testApp', 'yourApiKey');
 * 
 * 
 * //start timer
 * PRFLR::Begin('mongoDB.save');
 * 
 * //some code
 * sleep(1);
 * 
 * //stop timer
 * PRFLR::End('mongoDB.save');
 * 
 */

class PRFLR {

    private static $sender;

    public static function init($source, $apikey) {
        self::$sender = new PRFLRSender();

        if (!self::$sender->apikey = $apikey)
            throw new Exception('Unknown apikey.');

        if (!self::$sender->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))
            throw new Exception('Can\'t open socket.');

        if (!$source)
            self::$sender->source = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'Unknown';
        else
            self::$sender->source = $source;
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
    public $socket;
    public $delayedSend = false;
    public $source;
    public $thread;
    public $ip;
    public $port = 4000;
    public $apikey;

    public function __construct() {
        $this->ip = gethostbyname("prflr.org");
    }

    public function __destruct() {
        socket_close($this->socket);
    }

    public function Begin($timer) {
        $this->timers[$timer] = microtime(true);
    }

    public function End($timer, $info = '') {

        if (!isset($this->timers[$timer]))
            return false;

        $time = round(( microtime(true) - $this->timers[$timer] ) * 1000, 3);

        $this->send($timer, $time, $info);

        unset($this->timers[$timer]);
    }

    private function send($timer, $time, $info = '') {

        // format the message
        $message = join(array(
            substr($this->thread, 0, 32),
            substr($this->source, 0, 32),
            substr($timer, 0, 48),
            $time,
            substr($info, 0, 32),
            substr($this->apikey, 0, 32),

                ), '|');

        if ($this->socket) {
            socket_sendto($this->socket, $message, strlen($message), 0, $this->ip, $this->port);
        } else {
            throw new Exception("Socket not exist\n");
        }
    }

}

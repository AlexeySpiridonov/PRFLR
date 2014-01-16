use PRFLR;
use Time::HiRes qw(usleep nanosleep);
sub test(){
	PRFLR::begin("mainTest");
	for(my $_=0;$_<5;$_++){
		PRFLR::begin("test $_");
		#some code here
		usleep(50000);
		PRFLR::end("test $_", "ended");
	}
	PRFLR::end("mainTest", "good");
}
PRFLR::init("PerlExample", "bas9312");
$PRFLR::overflowCount = 50;
test();

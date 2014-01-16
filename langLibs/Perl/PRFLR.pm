package PRFLR;
use strict;
use warnings;
use Time::HiRes qw ( time );
use threads;
use IO::Socket::INET;
$| = 1;

sub init($$) {
	$PRFLR::source = shift;
	die "Unknown source." if not $PRFLR::source;
	$PRFLR::apikey = shift;
	die "Unknown apikey." if not $PRFLR::apikey;
	$PRFLR::timers = {};
	my $socket;
	$PRFLR::socket = new IO::Socket::INET (
		PeerAddr   => 'prflr.org:4000',
		Proto        => 'udp'
	) or die "ERROR in Socket Creation : $!\n";
	$PRFLR::overflowCount = 100;
	$PRFLR::counter = 0;
}
sub begin($){
	my $timerName = shift;
	
	if($PRFLR::counter > $PRFLR::overflowCount){
		%PRFLR::timers = {};
		$PRFLR::counter = 0;
	}
	$PRFLR::counter++;
	$PRFLR::timers{threads->tid() . $timerName} = time();
}
sub end($$){
	my $timerName = shift;
	my $info = shift;
	my $thread = threads->tid();
	if(!exists($PRFLR::timers{$thread . $timerName})){
		return 0;
	}
	$PRFLR::counter--;
	my $diffTime = sprintf("%.3f", (time() - $PRFLR::timers{$thread . $timerName})*1000);
	delete $PRFLR::timers{$thread . $timerName};
	PRFLR::send($timerName, $diffTime, $thread, $info);
}
sub send($$$$){
	my($timerName, $diffTime, $thread, $info) = @_;
	my $data = join("|",
		substr($thread, 0, 32),
		substr($PRFLR::source, 0, 32),
		substr($timerName, 0, 48),
		$diffTime,
		substr($info, 0, 32),
		substr($PRFLR::apikey, 0, 32));
	$PRFLR::socket->send($data);
}
1;


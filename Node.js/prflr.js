var source = "unknown";
var apiKey = "unknown";
var overflowCount = 100;
var timers = {};

var dgram = require("dgram");
var socket;

exports.init = function(Source, ApiKey){
	socket = dgram.createSocket("udp4");
	if(!ApiKey) {
		throw "Unknown apikey.";
	} else {
		apikey = ApiKey.substring(0, 32);
	}

	if(!Source){
		throw "Unknown source."
	} else {
		source = Source.substring(0, 32);
	}

	timers.map = {};
}

exports.begin = function(timerName){
	if(Object.keys(timers).length > overflowCount){
		delete timers.map;
		timers.map = {}
	}
	timers.map[timerName] = process.hrtime();
}

var precision = Math.pow(10,3);

exports.end = function(timerName, info){
	var diffTime = process.hrtime(timers.map[timerName]);
	var thread = "" + process.pid;
	delete timers.map[timerName];
	var trueDiffTime = ((diffTime[0] * 1e9 + diffTime[1]) / 1000000 * precision) / precision;
	send(timerName, "" + trueDiffTime, "" + thread, info);
}

exports.setOverflowCount = function(count){
	overflowCount = count;
}

function send(timerName, time, thread, info){
	var dataForSend = 
		thread.substring(0, 32) + "|"	
		+ source + "|" 
		+ timerName.substring(0, 48) + "|"
		+ time + "|"
		+ info.substring(0, 32) + "|"
		+ apikey;

		var message = new Buffer(dataForSend);
		//should work with serverIP, but it doesn't
		socket.send(message, 0, message.length, 4000, 'prflr.org', function(err, bytes){
			if(err) throw err;
		});
}

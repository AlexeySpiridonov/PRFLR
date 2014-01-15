var source = "unknown";
var apiKey = "unknown";
var overflowCount = 100;
var port = 4000;
var serverIP;
var timers = {};


var dgram = require("dgram");
var dns = require("dns");
var socket;

exports.init = function(Source, ApiKey){
	dns.resolve4('prflr.org', function (err, addresses) {
	  if (err) {
	  	err.message = "Host unknown.";
	  	throw err;
	  }
	  serverIP = JSON.stringify(addresses[0]);
	  console.log('address: ' + JSON.stringify(addresses[0]));
  	});

	socket = dgram.createSocket("udp4");
	if(!ApiKey) {
		throw "Unknown apikey.";
	} else {
		apikey = ApiKey;
	}

	if(!Source){
		throw "Unknown source."
	} else {
		source = Source;
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
	console.log()
	var trueDiffTime = ((diffTime[0] * 1e9 + diffTime[1]) / 1000000 * precision) / precision;
	console.log("trueDiffTime:" + trueDiffTime);
	send(timerName, "" + trueDiffTime, "" + thread, info);
}

exports.setOverflowCount = function(count){
	overflowCount = count;
}

function cleanTimers(){
	delete timers.map;
}

function send(timerName, time, thread, info){
	var dataForSend = 
		thread.substring(0, 32) + "|"	
		+ source.substring(0, 32) + "|" 
		+ timerName.substring(0, 48) + "|"
		+ time + "|"
		+ info.substring(0, 32) + "|"
		+ apikey.substring(0, 32);

		console.log(dataForSend);

		var message = new Buffer(dataForSend);
		//should work with serverIP, but it doesn't
		socket.send(message, 0, message.length, port, 'prflr.org', function(err, bytes){
			if(err) throw err;
		});
}
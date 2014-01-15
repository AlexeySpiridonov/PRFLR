
var PRFLP = require('./prflr.js');



	function test(){
		PRFLP.init("192.168.1.45-nodejstest", "testKey");

		PRFLP.setOverflowCount(50);
		PRFLP.begin("mongoDB.save");

		var i = 0;
		var ID = setInterval(function(){
			if(i > 10){
				clearInterval(ID);
			}

			PRFLP.begin("mongoDB.save step" + i);
			PRFLP.end("mongoDB.save step" + i, "step " + i);
			i++;
		}, 10);
	}

test();
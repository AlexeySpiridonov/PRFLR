//PRFLR UDP server and web panel
package main

import (
	"net"
//    "fmt"
    "log"
    "strings"
	"strconv"
	"labix.org/v2/mgo"
)

/**
 * UDP Package struct
 */
type Timer struct {
    Thrd string
	Src string
	Timer string
    Time float32
    Info string
}

/**
 * Global variables
 */
var (
    dbName = "prflr"
    dbHosts = "127.0.0.1"
    dbCollection = "timers"
    udpPort = ":4000"
)

/* UDP Handlers */
func  saveMessage(dbc *mgo.Collection, msg string) {
	err:= dbc.Insert( prepareMessage(msg)  )
    if err != nil {
        log.Fatal(err)
    }
}

func prepareMessage(msg string) (timer Timer) {
    fields := strings.Split(msg, "|")
//	fmt.Println(fields)
	time, err := strconv.ParseFloat(fields[3], 32);
	if err != nil {
		log.Fatal(err)
	}
	return Timer{fields[0], fields[1], fields[2], float32(time), fields[4]}
}

func main() {
	/* Starting UDP Server */
    //add here UDP aggregator  in  different thread
    laddr, err := net.ResolveUDPAddr("udp", udpPort); 
    if err != nil {
		log.Fatal(err)
	} 

    l, err := net.ListenUDP("udp", laddr); 
	if err != nil {
		log.Fatal(err)
	}

	// init Mongo connect
	db, err := mgo.Dial(dbHosts)
    if err != nil {
        log.Fatal(err)
    }
    defer db.Close()

    // Optional. Switch the session to a monotonic behavior.
    db.SetMode(mgo.Monotonic, true)
    dbc := db.DB(dbName).C(dbCollection)

	// is Buffer enough?!?!
	var buffer [1500]byte
	for {
		n, _, err := l.ReadFromUDP(buffer[0:])
		if err != nil {
			log.Fatal(err)
		}
		go saveMessage(dbc, string(buffer[0:n]))
	}
}

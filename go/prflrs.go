//PRFLR server
package main

import (
	//"io"
	"log"
	"net"
    "strings"
//	"fmt"
	"strconv"
	"labix.org/v2/mgo"
//  "labix.org/v2/mgo/bson"
)

type Timer struct {
    Thrd string
    Timer string
    Src string
    Time float32
    Info string
}

var (
    dbName = "prflr"
    dbHosts = "127.0.0.1"
    dbCollection = "timers"
    udpPort = ":5000"
)

func main() {
	laddr, err := net.ResolveUDPAddr("udp", udpPort); 
    if err != nil {
		log.Fatal(err)
	} 

    l, err := net.ListenUDP("udp", laddr); 
	if err != nil {
		log.Fatal(err)
	}

	db, err := mgo.Dial(dbHosts)
    if err != nil {
        log.Fatal(err)
    }
    defer db.Close()

    // Optional. Switch the session to a monotonic behavior.
    db.SetMode(mgo.Monotonic, true)
    dbc := db.DB(dbName).C(dbCollection)

	var buffer [1500]byte
	for {
		//conn, err := l.Accept()
		n, addr, err := l.ReadFromUDP(buffer[0:])
		if err != nil {
			log.Fatal(err)
		}
		go saveMessage(dbc, string(buffer[0:n])+"|"+addr.String())
	}
}

func prepareMessage(msg string) (timer Timer) {
    fields := strings.Split(msg, "|")

	//fmt.Printf("Fields: %s, %s, %s, %s, %s\n", fields[0], fields[1], fields[2], fields[3], fields[4])

    //TODO  add validator here
	//len(fields) == 5, etc.

	time, err := strconv.ParseFloat(fields[3], 32);
	if err != nil {
		log.Fatal(err)
	}

	return Timer{fields[0], fields[1], fields[2], float32(time), fields[4]}
}

func  saveMessage(dbc *mgo.Collection, msg string) {
	err:= dbc.Insert(prepareMessage(msg))
    if err != nil {
        log.Fatal(err)
    }
}
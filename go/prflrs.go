//PRFLR server
package main

import (
	//"io"
	"log"
	"net"
    "strings"
	"labix.org/v2/mgo"
    //"labix.org/v2/mgo/bson"
)

type Timer struct {
    Thrd string
    Timer string
    Src string
    Time float32
    Info string
}

var (
    //mgoSession     *mgo.Session
    dbName = "myDB"
    dbHosts = "192.168.1.1"
    dbPort = "1111"
    dbCollection = "prflr"
    udpPort = ":5000"
)

func main() {

	l, err := net.Listen("udp", udpPort)
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

	for {
		conn, err := l.Accept()
		if err != nil {
			log.Fatal(err)
		}
		go saveMessage(dbc, conn)
	}
}

func prepareMessage(conn net.Conn) (msg Timer) {
	//msg := io.Copy(c, c)
    strings.Split("123|timername|srcname|1|nfo", "|")
    //TODO  add validator here
	return Timer{"123", "yiiapp", "test.getConnect", 1.456, "info"}
}

func  saveMessage(dbc *mgo.Collection, conn net.Conn) {
			err:= dbc.Insert( prepareMessage(conn) )
        	if err != nil {
                log.Fatal(err)
       		}
			// Shut down the connection.
			conn.Close()
}


//PRFLR UDP server and web panel
package main

import (
	"encoding/json"
	"fmt"
	"html/template"
	"labix.org/v2/mgo"
	"labix.org/v2/mgo/bson"
	"log"
	"net"
	"net/http"
	"strconv"
	"strings"
)

/**
 * UDP Package struct
 */
type Timer struct {
	Thrd   string
	Src    string
	Timer  string
	Time   float32
	Info   string
	Apikey string
}

/**
 * User struct
 */
type User struct {
	Email   string
	Password    string
	Apikey  string
	Token   float32
	Info   string
}

/**
 * Web panel Struct
 */
type Stat struct {
	Src   string
	Timer string
	Count int
	Total float32
	Min   float32
	Avg   float32
	Max   float32
}

/**
 * Global variables
 */
var (
	dbName                  = "prflr"
	dbHosts                 = "127.0.0.1"
	dbTimers                = "timers"
	dbUsers                 = "users"
	udpPort                 = ":4000"
	httpPort                = ":8080"
	cappedCollectionMaxByte = 100000000 // 100Mb
	cappedCollectionMaxDocs = 500000    // 500k
)

/* HTTP Handlers */
func mainHandler(w http.ResponseWriter, r *http.Request) {
	t, _ := template.ParseFiles("assets/main.html")
	t.Execute(w, nil)
}

func lastHandler(w http.ResponseWriter, r *http.Request) {
	db, err := mgo.Dial(dbHosts)
	if err != nil {
		log.Panic(err)
	}
	defer db.Close()

	db.SetMode(mgo.Monotonic, true)
	dbc := db.DB(dbName).C(dbTimers)

	// Query All
	var results []Timer

	//TODO add criteria builder
	err = dbc.Find(makeCriteria(r.FormValue("filter"))).Sort("-_id").Limit(100).All(&results)
	if err != nil {
		log.Panic(err)
	}

	j, err := json.Marshal(&results)
	if err != nil {
		log.Panic(err)
	}
	fmt.Fprintf(w, "%s", j)

	db.Close()
}

func aggregateHandler(w http.ResponseWriter, r *http.Request) {
	//TODO
	db, err := mgo.Dial(dbHosts)
	if err != nil {
		log.Panic(err)
	}
	defer db.Close()

	db.SetMode(mgo.Monotonic, true)
	dbc := db.DB(dbName).C(dbTimers)

	// Query All
	var results []Stat

	grouplist := make(map[string]interface{})
	groupparam := make(map[string]interface{})

	grouplist["count"] = bson.M{"$sum": 1}
	grouplist["total"] = bson.M{"$sum": "$time"}
	grouplist["min"] = bson.M{"$min": "$time"}
	grouplist["avg"] = bson.M{"$avg": "$time"}
	grouplist["max"] = bson.M{"$max": "$time"}

	q := strings.Split(r.FormValue("groupby"), ",")

	if len(q) >= 1 && q[0] == "src" {
		groupparam["src"] = "$src"
		grouplist["src"] = bson.M{"$first": "$src"}
	}
	if len(q) >= 2 && q[1] == "timer" {
		grouplist["timer"] = bson.M{"$first": "$timer"}
		groupparam["timer"] = "$timer"
	}
	grouplist["_id"] = groupparam
	group := bson.M{"$group": grouplist}
	sort := bson.M{"$sort": bson.M{r.FormValue("sortby"): -1}}
	match := bson.M{"$match": makeCriteria(r.FormValue("filter"))}
	aggregate := []bson.M{match, group, sort}

	err = dbc.Pipe(aggregate).All(&results)

	if err != nil {
		log.Panic(err)
	}

	j, err := json.Marshal(results)
	if err != nil {
		log.Fatal(err)
	}
	fmt.Fprintf(w, "%s", j)

	db.Close()
}

func makeCriteria(filter string) interface{} {
	q := strings.Split(filter, "/")
	c := make(map[string]interface{})

	if len(q) >= 1 && q[0] != "" && q[0] != "*" {
		c["src"] = &bson.RegEx{q[0], "i"}
	}
	if len(q) >= 2 && q[1] != "" && q[1] != "*" {
		c["timer"] = &bson.RegEx{q[1], "i"}
	}
	if len(q) >= 3 && q[2] != "" && q[2] != "*" {
		c["info"] = &bson.RegEx{q[2], "i"}
	}
	if len(q) >= 4 && q[3] != "" && q[3] != "*" {
		c["thrd"] = q[3]
	}
	return c
}

/* UDP Handlers */
func saveMessage(dbc *mgo.Collection, msg string) {
	err := dbc.Insert(prepareMessage(msg))
	if err != nil {
		log.Panic(err)
	}
}

func prepareMessage(msg string) (timer Timer) {
	fields := strings.Split(msg, "|")
	//fmt.Println(fields)

	time, err := strconv.ParseFloat(fields[3], 32)
	if err != nil {
		log.Panic(err)
	}
	//return Timer{fields[0][0:16], fields[1][0:16], fields[2][0:48], float32(time), fields[4][0:16]}
	//TODO add check for apikey and crop for fields lenght
	return Timer{fields[0], fields[1], fields[2], float32(time), fields[4], fields[5]}
}

func main() {

	/* Starting Web Server */
	http.Handle("/assets/", http.StripPrefix("/assets/", http.FileServer(http.Dir("./assets"))))
	http.Handle("/favicon.ico", http.FileServer(http.Dir("./assets"))) //cool code for favicon! :)  it's very important!
	http.HandleFunc("/last/", lastHandler)
	http.HandleFunc("/aggregate/", aggregateHandler)
	http.HandleFunc("/", mainHandler)

	go http.ListenAndServe(httpPort, nil)

	/* Starting UDP Server */
	//add here UDP aggregator  in  different thread
	laddr, err := net.ResolveUDPAddr("udp", udpPort)
	if err != nil {
		log.Fatal(err)
	}

	l, err := net.ListenUDP("udp", laddr)
	if err != nil {
		log.Fatal(err)
	}

	// init MongoDB connect
	db, err := mgo.Dial(dbHosts)
	if err != nil {
		log.Fatal(err)
	}
	defer db.Close()

	// Optional. Switch the session to a monotonic behavior.
	db.SetMode(mgo.Monotonic, true)

	err = db.DB(dbName).DropDatabase()
	if err != nil {
		log.Fatal(err)
	}
	dbc := db.DB(dbName).C(dbTimers)

	// creating capped collection
	dbc.Create(&mgo.CollectionInfo{Capped: true, MaxBytes: cappedCollectionMaxByte, MaxDocs: cappedCollectionMaxDocs})

	// Insert Test Datas
	err = dbc.Insert(&Timer{Thrd: "1234567890", Timer: "prflr.check", Src: "test.src", Time: 1, Info: "test data", Apikey: "PRFLRApiKey"})
	if err != nil {
		log.Fatal(err)
	}

	// is Buffer enough?!?!
	var buffer [500]byte
	for {
		n, _, err := l.ReadFromUDP(buffer[0:])
		if err != nil {
			log.Panic(err)
		}
		go saveMessage(dbc, string(buffer[0:n]))
	}
}
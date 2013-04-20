//PRFLR web panel
package main

import (
	"html/template"
    //"io/ioutil"
	"net"
    "net/http"
    "fmt"
    "log"
    "strings"
	"strconv"
    //"regexp"
	//"fmt"
	"labix.org/v2/mgo"
    "labix.org/v2/mgo/bson"
    "encoding/json"
)

/**
 * UDP Package struct
 */
type Timer struct {
    Thrd string
    Timer string
    Src string
    Time float32
    Info string
}

/**
 * Web panel Struct
 */
type Stat struct {
    Src string
    Timer string
    Count int
    Total float32
    Min float32
    Avg float32
    Max float32
}

/**
 * Global variables
 */
var (
    dbName = "prflr"
    dbHosts = "127.0.0.1"
    dbCollection = "timers"
    udpPort = ":5000"
	httpPort = ":8080"
)



/* HTTP Handlers */
func mainHandler(w http.ResponseWriter, r *http.Request) {
	t, _ := template.ParseFiles("assets/main.html")
    t.Execute(w,nil)
}

func lastHandler(w http.ResponseWriter, r *http.Request) {
	db, err := mgo.Dial(dbHosts)
    if err != nil {
        log.Fatal(err)
    }
    defer db.Close()
    
    db.SetMode(mgo.Monotonic, true)
    dbc := db.DB(dbName).C(dbCollection)
	
	// Query All
	var results []Timer

	//TODO add criteria builder
	err = dbc.Find( makeCriteria(r.FormValue("filter")) ).Sort("-_id").Limit(100).All(&results)

	if err != nil {
		panic(err)
	}

	jsonOut2(w, &results)

    db.Close()
}

func initHandler(w http.ResponseWriter, r *http.Request) {
	initDB()
	fmt.Fprintf(w, "Cilinder recreated!")
}

func initDB() {
	session, err := mgo.Dial(dbHosts)
	if err != nil {
		panic(err)
	}
	defer session.Close()
	session.SetMode(mgo.Monotonic, true)
	err = session.DB(dbName).DropDatabase()
	if err != nil {
		panic(err)
	}
	c := session.DB(dbName).C(dbCollection)
	// Insert Test Datas
	err = c.Insert(&Timer{Thrd:"1234567890", Timer: "prflr.check", Src: "test.src", Time: 1, Info: "test data"})
	if err != nil {
		panic(err)
	}
	session.Close()
}

func aggregateHandler(w http.ResponseWriter, r *http.Request) {
	//TODO
    db, err := mgo.Dial(dbHosts)
    if err != nil {
        log.Fatal(err)
    }
    defer db.Close()

    db.SetMode(mgo.Monotonic, true)
    dbc := db.DB(dbName).C(dbCollection)
	
	// Query All
	var results []Stat

	grouplist := make(map[string]interface{})
	groupparam := make(map[string]interface{})

	grouplist["count"] = bson.M{"$sum":1}
	grouplist["total"] = bson.M{"$sum":"$time"}
	grouplist["min"]   = bson.M{"$min":"$time"}
	grouplist["avg"]   = bson.M{"$avg":"$time"}
	grouplist["max"]   = bson.M{"$max":"$time"}

	q := strings.Split(r.FormValue("groupby"), ",")
	
	if len(q) >= 1 && q[0] == "src" {
		groupparam["src"] = "$src"
		grouplist["src"]   = bson.M{"$first":"$src"}
	}
	if len(q) >= 2 && q[1] == "timer" {
		grouplist["timer"] = bson.M{"$first":"$timer"}
		groupparam["timer"] = "$timer"
	}	
	grouplist["_id"] = groupparam
	group := bson.M{"$group": grouplist}
	sort  := bson.M{"$sort": bson.M{ r.FormValue("sortby"):-1 }}
	match := bson.M{"$match": makeCriteria(r.FormValue("filter"))}
	aggregate := []bson.M{match, {"$limit": 1000}, group, sort }

	err = dbc.Pipe(aggregate).All(&results)

	if err != nil {
		panic(err)
	}

	jsonOut(w, &results)

    db.Close()
}

/*
func sortData(sort string) interface[]{

	return nil
}
*/

func jsonOut(w http.ResponseWriter, data *[]Stat) {
	j, err := json.Marshal(data)
	if err != nil {
		panic(err)
	}
	fmt.Fprintf(w, "%s", j)
}
func jsonOut2(w http.ResponseWriter, data *[]Timer) {
	j, err := json.Marshal(data)
	if err != nil {
		panic(err)
	}
	fmt.Fprintf(w, "%s", j)
}

func makeCriteria(filter string) interface{} {
	q := strings.Split(filter, "/")
	c := make(map[string]interface{})

	if len(q) >= 1 && q[0] != "" && q[0] != "*" {
		c["src"]   = &bson.RegEx{Pattern: q[0]}
	}
	if len(q) >= 2 && q[1] != "" &&  q[1] != "*" {
		c["timer"] = &bson.RegEx{Pattern: q[1]}
	}
	if len(q) >= 3 && q[2] != "" &&  q[2] != "*" {
		c["info"]  = &bson.RegEx{Pattern: q[2]}
	}
	if len(q) >= 4 && q[3] != "" &&  q[3] != "*" {
		c["thrd"] = q[3]
	}

	return c
}


/* UDP Handlers */
func  saveMessage(dbc *mgo.Collection, msg string) {
	err:= dbc.Insert( prepareMessage(msg) )
    if err != nil {
        log.Fatal(err)
    }
}

func prepareMessage(msg string) (timer Timer) {
    fields := strings.Split(msg, "|")

    //TODO  add validator here
	//len(fields) == 5, etc.

	time, err := strconv.ParseFloat(fields[3], 32);
	if err != nil {
		log.Fatal(err)
	}

	return Timer{fields[0], fields[1], fields[2], float32(time), fields[4]}
}

func main() {

	/* Starting Web Server */
	http.Handle("/assets/", http.StripPrefix("/assets/", http.FileServer(http.Dir("./assets"))))
	http.Handle("/favicon.ico", http.FileServer(http.Dir("./assets")))  //cool code for favicon! :)  it's very important! 

	http.HandleFunc("/last/", lastHandler)
	http.HandleFunc("/init/", initHandler)
	http.HandleFunc("/aggregate/", aggregateHandler)
	http.HandleFunc("/", mainHandler)

    http.ListenAndServe(httpPort, nil)

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

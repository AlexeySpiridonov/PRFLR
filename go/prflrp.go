//PRFLR web panel
package main

import (
	"html/template"
    //"io/ioutil"
    "net/http"
    "fmt"
    "log"
    "strings"
    //"regexp"
	"labix.org/v2/mgo"
    "labix.org/v2/mgo/bson"
    "encoding/json"
)

type Timer struct {
    Thrd string
    Timer string
    Src string
    Time float32
    Info string
}

type Stat struct {
    Src string
    Timer string
    Count int
    Total float32
    Min float32
    Max float32
}

var (
    dbName = "prflr"
    dbHosts = "127.0.0.1"
    dbCollection = "timers"
    udpPort = ":5000"
)

func mainHandler(w http.ResponseWriter, r *http.Request) {
	t, _ := template.ParseFiles("assets/main.html")
    t.Execute(w,nil)
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

/*
 $criteria = array();
        if (isset($_GET["filter"])) {
            $par = explode('/', $_GET["filter"]);
            if (isset($par[0]) && $par[0] != '*')
                $criteria['source'] = new MongoRegex("/" . $par[0] . "/i");
            if (isset($par[1]) && $par[1] != '*')
                $criteria['timer'] = new MongoRegex("/" . $par[1] . "/i");
            if (isset($par[2]) && $par[2] != '*')
                $criteria['info'] = new MongoRegex("/" . $par[2] . "/i");
            if (isset($par[3]) && $par[3] != '*')
                $criteria['thread'] = $par[3];
        }
        return $criteria;
*/

func makeCriteria(filter string) interface{} {
	if filter != "1" {
		q := strings.Split(filter, "/")
		if q[0:] !=nil {
			return bson.M{"Timer": "test2"}
		}

	}
	return nil
}

func makeGroupBy(r *http.Request) {
	return
}

func jsonOut(w http.ResponseWriter, data *[]struct ) {
	j, err := json.Marshal(data)
	if err != nil {
		panic(err)
	}
	fmt.Fprintf(w, "%s", j)
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
	var results []Stat

	//TODO add criteria builder
	err = dbc.Find( makeCriteria(r.FormValue("filter")) ).Sort("-_id").Limit(100).All(&results)

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
	var results []Timer

	//var mapreduce  string
	//var mapreduce = "function (obj, prev) {prev.count++; prev.time.total += obj.time.current; if (prev.time.min > obj.time.current) prev.time.min = obj.time.current; if (prev.time.max < obj.time.current) prev.time.max = obj.time.current; }"

	//TODO add criteria builder
	//err = dbc.Group(mapreduce).Find( makeCriteria(r.FormValue("filter")) ).All(&results)
	err = dbc.Find( makeCriteria(r.FormValue("filter")) ).All(&results)

	if err != nil {
		panic(err)
	}

	//jsonOut(w, sortData("123") )

    db.Close()

}

func initHandler(w http.ResponseWriter, r *http.Request) {
	initDB()
	fmt.Fprintf(w, "Cilinder recreated!")
}

func main() {

	http.Handle("/assets/", http.StripPrefix("/assets/", http.FileServer(http.Dir("./assets"))))
	http.Handle("/favicon.ico", http.FileServer(http.Dir("./assets")))  //cool code for favicon! :)  it's very important! 

	http.HandleFunc("/last/", lastHandler)
	http.HandleFunc("/init/", initHandler)
	http.HandleFunc("/aggregate/", aggregateHandler)
	http.HandleFunc("/", mainHandler)

    http.ListenAndServe(":8080", nil)

    //add here UDP aggregator  in  different thread
}



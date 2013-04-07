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
    //"labix.org/v2/mgo/bson"
    "encoding/json"
)

type Timer struct {
    Thread string
    Source string
    Timer string
    Duration float32
    Info string
}

var (
    dbName = "prflr"
    dbHosts = "127.0.0.1"
    dbPort = "27017"
    dbCollection = "timers"
    udpPort = ":5000"
)

func mainHandler(w http.ResponseWriter, r *http.Request) {
	t, _ := template.ParseFiles("assets/main.html")
    t.Execute(w,nil)
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
func makeCriteria(filter string) {
	if filter != "" {
		q := strings.Split(filter, "/")
		if q[0:] !=nil {
			//return bson.M{"timer": "test2"}
		}

	}
	return
}

func makeGroupBy(r *http.Request) {
	return
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
	err = dbc.Find( nil ).Sort("-_id").Limit(100).All(&results)

	if err != nil {
		panic(err)
	}

	j, err := json.Marshal(results)
	if err != nil {
		panic(err)
	}

	fmt.Fprintf(w, "%s", j)

    db.Close()

}

func aggregateHandler(w http.ResponseWriter, r *http.Request) {
	//TODO
	fmt.Fprintf(w, "test = %s", r.FormValue("test") )
    fmt.Fprintf(w, "Hi there, I love %s!", r.URL.Path[1:])
}

func main() {
	http.Handle("/assets/", http.StripPrefix("/assets/", http.FileServer(http.Dir("./assets"))))
	http.HandleFunc("/last/", lastHandler)
	http.HandleFunc("/aggregate/", aggregateHandler)
	http.HandleFunc("/", mainHandler)
    http.ListenAndServe(":8080", nil)
}



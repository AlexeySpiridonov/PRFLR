//PRFLR web panel
package main

import (
	"html/template"
    //"io/ioutil"
    "net/http"
    "fmt"
    //"regexp"
	//"labix.org/v2/mgo"
    //"labix.org/v2/mgo/bson"
)

type Message struct {
    thread string
    source string
    timer string
    duration float32
    info string
}

var (
    //mgoSession     *mgo.Session
    dbName = "myDB"
    dbHosts = "192.168.1.1"
    dbPort = "1111"
    dbCollection = "prflr"
    udpPort = ":5000"
)

func mainHandler(w http.ResponseWriter, r *http.Request) {
	t, _ := template.ParseFiles("assets/main.html")
    t.Execute(w,nil)
}

func lastHandler(w http.ResponseWriter, r *http.Request) {
	fmt.Fprintf(w, "test = %s", r.FormValue("test") )
    fmt.Fprintf(w, "Hi there, I love %s!", r.URL.Path[1:])
}

func aggregateHandler(w http.ResponseWriter, r *http.Request) {
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



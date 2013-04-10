package main

import (
        "fmt"
        "labix.org/v2/mgo"
        "labix.org/v2/mgo/bson"
)

type Person struct {
        Source string
        Timer string
}

func main() {
        session, err := mgo.Dial("127.0.0.1")
        if err != nil {
                panic(err)
        }
        defer session.Close()

        // Optional. Switch the session to a monotonic behavior.
        session.SetMode(mgo.Monotonic, true)

        c := session.DB("prflr").C("timers")
        //err = c.Insert(&Person{"Ale", "+55 53 8116 9639"},
	//               &Person{"Cla", "+55 53 8402 8510"})
        //if err != nil {
        //        panic(err)
        //}

        result := Person{}
        err = c.Find(bson.M{"timer": "test"}).One(&result)
        if err != nil {
                panic(err)
        }

        fmt.Println("Phone:", result.Timer)
}
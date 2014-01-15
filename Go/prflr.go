package prflr

import (
	"crypto/rand"
	"encoding/base64"
	"fmt"
	"net"
	"time"
)

type PRFLR struct {
	Source string
	Apikey string
	Timers map[string]time.Time
	Thid   string
	Conn   *net.UDPConn
}

func (p *PRFLR) Setup(url string) error {
	serverAddr, err := net.ResolveUDPAddr("udp", url)
	p.Conn, err = net.DialUDP("udp", nil, serverAddr)
	p.Thid = uniqid(32)
	return err
}

func (p *PRFLR) Begin(timer string) {
	if p.Timers == nil {
		p.Timers = make(map[string]time.Time)
	}
	p.Timers[timer] = time.Now()
}

func (p *PRFLR) End(timer string, info string) {
	if start, ok := p.Timers[timer]; ok {
		dur := fmt.Sprintf("%.3f", millisecond(time.Since(start)))
		p.send(timer, dur, info)
	}
	delete(p.Timers, timer)
}

func (p *PRFLR) send(timer string, dur string, info string) {
	p.Conn.Write([]byte(fmt.Sprintf("%.32s|%.32s|%.48s|%s|%.32s|%.32s\n", p.Thid, p.Source, timer, dur,info, p.Apikey)))

}

func substr(str string, strlen int) string {
	if len(str) < strlen {
		return str
	}
	return string([]byte(str)[0:strlen])
}
func millisecond(d time.Duration) float64 {
	msec := d / time.Millisecond
	nsec := d % time.Millisecond
	return float64(msec) + float64(nsec)*1e-9
}

func uniqid(strlen int) string {
	line := make([]byte, strlen)
	rand.Read(line)
	en := base64.StdEncoding
	uid := make([]byte, en.EncodedLen(len(line)))
	en.Encode(uid, line)
	return string(uid)
}

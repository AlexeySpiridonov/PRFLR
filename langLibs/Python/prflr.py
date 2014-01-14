#!/usr/bin/env python

# Threadsafe api
# usage example:

# from Prflr import Prflr
# p = Prflr('key', '192.168.1.45-testApp')
# p.begin('mongoDB.save')
# # do something usefull
# p.end('mongoDB.save', 'results')

# Run test: python prflr.py or python3 prflr.py

import sys
import time
import socket
import threading

# True if we are running on Python 3.
PY3 = sys.version_info[0] == 3

class Prflr:
    def __init__(self, api_key, source='', host='prflr.org', port=4000):
        self.addr = (host, port)
        self.source = source if source else socket.gethostname()
        self.socket = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        self.key = api_key
        self.timers = {}
        self.lock = threading.RLock()

    def begin(self, timer):
        thread_id = threading.current_thread().ident
        with self.lock:
            if not self.timers.get(thread_id):
                self.timers[thread_id] = {}
            self.timers[thread_id][timer] = time.time()

    def end(self, timer, info=''):
        now = time.time()
        thread_id = threading.current_thread().ident
        timers = self.timers.get(thread_id)
        if not timers:
            return False
        prev_time = timers.get(timer)
        if not prev_time:
            return False
        spent = round((now - prev_time) * 1000, 3)
        self.send(timer, spent, info)
        with self.lock:
            timers.pop(timer)
        return True

    def send(self, timer, time, info):
        thread_id = threading.current_thread().ident
        msg = '|'.join([str(thread_id)[:32],
                        self.source[:32],
                        timer[:48],
                        str(time)[:16],
                        info[:32],
                        self.key[:32]])
        # print(msg)
        if PY3:
            self.socket.sendto(bytes(msg, 'utf-8'), self.addr)
        else:
            self.socket.sendto(msg, self.addr)


def run_test():
    from threading import Thread

    def test_fun(p):
        p.begin('mongoDB.save')
        time.sleep(1)
        p.end('mongoDB.save', 'info')

    def run_test_server():
        srv = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        srv.bind(('localhost', 4000))
        print('server is running')
        for i in range(1, 10):
            print('received: {}'.format(srv.recv(2048)))

    p = Thread(target=run_test_server)
    p.start()
    prflr = Prflr('key', '192.168.1.45-testApp', 'localhost')
    threads = []
    for i in range(10):
        t = Thread(target=test_fun, args=[prflr])
        t.start()
        print('started: {}'.format(t.ident))
        threads.append(t)

    for t in threads:
        t.join()

if __name__ == '__main__':
    run_test()

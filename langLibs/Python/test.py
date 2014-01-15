#!/usr/bin/env python

# Run test: python test.py or python3 test.py

import time
import socket
from threading import Thread
from prflr import Prflr

def run_test():

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
    prflr = Prflr('key', '192.168.1.45-testApp')
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

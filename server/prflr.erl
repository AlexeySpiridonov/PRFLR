-module(prflr).
-export([start/0]).
-include ("/opt/Erlang/lib/erlang/lib/mongodb-master/include/mongo_protocol.hrl").

start() ->
    spawn(fun() -> server(4000, localhost) end).

server(Port, MongoHost) ->
    {ok, Socket} = gen_udp:open(Port, [binary, {active, false}]),
    io:format("Server opened socket:~p~n",[Socket]),

    application:start(mongodb),
    Host = {MongoHost, 27017},
    {ok, Conn} = mongo:connect(Host),
    io:format("Conn is : ~p~n", [Conn]),
    DbConn = {test, Conn},

    loop(Socket, DbConn).

loop(Socket, DbConn) ->
    inet:setopts(Socket, [{active, once}]),
        receive
            {udp, Socket, Host, Port, Bin} ->
                io:format("Server received:~p~n",[Bin]),
                loop(Socket)
end.
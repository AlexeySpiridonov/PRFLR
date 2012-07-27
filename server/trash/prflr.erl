-module(prflr).
-export([start/0]).
-import(string, [tokens/2]).
%%%-include ("./mongodb/include/mongo_protocol.hrl").

start() ->
    spawn(fun() -> server(4000, "188.127.227.36") end).

server(Port, MongoHost) ->
    {ok, Socket} = gen_udp:open(Port, [binary, {active, false}]),
    io:format("Server opened socket:~p~n",[Socket]),

    application:start(mongodb),
    Host = {MongoHost, 27017},
    {ok, Conn} = mongo:connect(Host),
    io:format("Conn is : ~p~n", [Conn]),
    DbConn = {test, Conn},

    loop(Socket, Conn).
    %loop(Socket, MongoHost).

loop(Socket, DbConn) ->
    inet:setopts(Socket, [{active, once}]),
        receive
            {udp, Socket, Host, Port, Bin} ->
                 io:format("Server received:~p~n",[Bin]),
                %{ok, LastErr} = do(fun() ->  
%makemessage(Bin),
        {ok, Conn} = mongo:connect("188.127.227.36"),
        mongo:do (safe, master, Conn, prflr, fun () ->
		Team = {thread, <<"1234567890">>, timer, <<"testtimer">>, group, <<"server">>, duration, <<"3">>, info, <<"test">>},
		mongo:insert(timers, Team)
		
	end),
        mongo:disconnect (Conn),
                   %mongo:insert(DbConn, "timers", [{"thread",'tread'}, {"group","Group"}, {"timer","Timer"}, {"duration","Duration"}, {"info","Info"}] ),
                %end),       
                loop(Socket, DbConn)
end.

stop(_State) ->
    ok.


makemessage(Bin) ->
    [Thread, Group, Timer, Duration, Info] = tokens([Bin], "|"),
    [{"thread",Thread}, {"group",Group}, {"timer",Timer}, {"duration",Duration}, {"info",Info}].
-module(prflr).
-export([start/0]).
-import(string, [tokens/2]).
-include ("./mongodb/include/mongo_protocol.hrl").

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
        mongo:do (safe, master, DbConn, prflr, fun () ->
		Teams0 = [
			{thread, <<"Yankees">>, timer, <<"American">>, group, <<"American">>, duration, <<"3">>}
			],
		mongo:insert_all (timers, Teams0)
		
	end),
                   %mongo:insert(DbConn, "timers", [{"thread",'tread'}, {"group","Group"}, {"timer","Timer"}, {"duration","Duration"}, {"info","Info"}] ),
                %end),       
                loop(Socket, DbConn)
end.

stop(_State) ->
    ok.


makemessage(Bin) ->
    [Thread, Group, Timer, Duration, Info] = tokens([Bin], "|"),
    [{"thread",Thread}, {"group",Group}, {"timer",Timer}, {"duration",Duration}, {"info",Info}].
-module(prflr).
-export([start/0]).
-import(string, [tokens/2]).

start() ->
    spawn(fun() -> server(4000, "127.0.0.1") end).

server(Port, MongoHost) ->
    {ok, Socket} = gen_udp:open(Port, [binary, {active, false}]),
    io:format("Server opened socket:~p~n",[Socket]),

    application:start(mongodb),
    application:start(bson),
    Host = {MongoHost, 27017},
    {ok, Conn} = mongo:connect(Host),
    io:format("Conn is : ~p~n", [Conn]),

    loop(Socket, Conn).

loop(Socket, Conn) ->
    inet:setopts(Socket, [{active, once}]),
        receive
            {udp, Socket, Host, Port, Bin} ->
                io:format("Server received:~p~n",[Bin]),
		[Thread, Group, Timer, Duration, Info] = tokens(binary_to_list(Bin), "|"),
        mongo:do (safe, master, Conn, prflr, fun() ->
		%mongo:save(timers, {x,1,y,2})
		Team = {thread, <<"1234567890">>, timer, <<"testtimer">>, group, <<"server">>, duration, <<"3">>, info, <<"test">>},
		%%%Team = {thread,Thread, timer,Timer, group,Group, duration,Duration, info,Info},
		mongo:insert(timers, Team)
	end),
                loop(Socket, Conn)
end.

stop(_State) ->
    ok.


makemessage(Bin) ->
    [Thread, Group, Timer, Duration, Info] = tokens([Bin], "|"),
    [{"thread",Thread}, {"group",Group}, {"timer",Timer}, {"duration",Duration}, {"info",Info}].

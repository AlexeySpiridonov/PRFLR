-module(prflr_app).

-behaviour(application).

-import(string, [tokens/2]).

%% Application callbacks
-export([start/2, stop/1]).

%% include mongo
%-include ("/usr/local/lib/erlang/lib/mongodb-master/include/mongo_protocol.hrl").

%% ===================================================================
%% Application callbacks
%% ===================================================================

start(_StartType, _StartArgs) ->
    prflr_sup:start_link(),
    spawn(fun() -> server(4000, "188.127.227.36") end).

server(Port, MongoHost) ->
    {ok, Socket} = gen_udp:open(Port, [binary, {active, false}]),
    io:format("Server opened socket:~p~n",[Socket]),

    application:start(mongodb),
    Host = {MongoHost, 27017},
    {ok, Conn} = mongo:connect(Host),
    io:format("Conn is : ~p~n ", [Conn]),
    DbConn = {test, Conn},
    loop(Socket, DbConn).

loop(Socket, DbConn) ->
    inet:setopts(Socket, [{active, once}]),
        receive
            {udp, Socket, Host, Port, Bin} ->
%%%                 io:format("Server received:~p~n",[Bin]),
                %{ok, LastErr} = do(fun() ->  
                    mongo:insert(DbConn, "timers", makemessage(Bin) ),
                %end),       
                loop(Socket, DbConn)
end.


makemessage(Bin) ->
    [Thread, Group, Timer, Duration, Info] = tokens(Bin, "|"),
    [{"thread",Thread}, {"group",Group}, {"timer",Timer}, {"duration",Duration}, {"info",Info}].

stop(_State) ->
    ok.

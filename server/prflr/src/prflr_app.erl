-module(prflr_app).

-behaviour(application).

-import(string, [tokens/2]).

%% Application callbacks
-export([start/2, stop/1]).


%% ===================================================================
%% Application callbacks
%% ===================================================================

start(_StartType, _StartArgs) ->   
    spawn(fun() -> server("127.0.0.1") end),
    prflr_sup:start_link().

server(MongoHost) ->
    {ok, Socket} = gen_udp:open(4000, [binary, {active, false}]),
    io:format("Server opened socket:~p~n",[Socket]),
    application:start(mongodb),
    application:start(bson),
    Host = {MongoHost, 27017},
    {ok, Conn} = mongo:connect(Host),
    io:format("Conn to mongo is : ~p~n", [Conn]),
    loop(Socket, Conn).

loop(Socket, Conn) ->
    inet:setopts(Socket, [{active, once}]),
        receive
            {udp, Socket, Host, Port, Bin} ->
                io:format("Server received:~p~n",[Bin]),
                mongo:do (safe, master, Conn, prflr, fun() ->                 
                    mongo:insert(timers, makemessage(Bin))
                end),      
                loop(Socket, Conn)
end.


makemessage(Bin) ->
    [Thread, Group, Timer, Duration, Info] = tokens(binary_to_list(Bin), "|"),
    {thread, <<"1234567890">>, timer, <<"testtimer">>, group, <<"server">>, duration, <<"3">>, info, <<"test">>}.


stop(_State) ->
    ok.

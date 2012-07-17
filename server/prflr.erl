-module(prflr).
-export([start/0]).

start() ->
    spawn(fun() -> server(4000) end).

server(Port) ->
    {ok, Socket} = gen_udp:open(Port, [binary, {active, false}]),
        io:format("Server opened socket:~p~n",[Socket]),
    loop(Socket).

loop(Socket) ->
    inet:setopts(Socket, [{active, once}]),
        receive
            {udp, Socket, Host, Port, Bin} ->
            io:format("Server received:~p~n",[Bin]),
        loop(Socket)
end.
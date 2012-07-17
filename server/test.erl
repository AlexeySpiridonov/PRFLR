-module(client).
-export([start/0]).

start() ->
    spawn(fun() -> client() end).

client() ->
    {ok, Socket} = gen_udp:open(0, [binary]),
    io:format("Client opened socket:~p~n",[Socket]),
    send(Socket),
    gen_udp:close(Socket).


send(Socket)->
    % {Mega, Seconds, _} = erlang:now(),
    ok = gen_udp:send(Socket, "localhost", 4000, 555 ),
    send(Socket).
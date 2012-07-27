ERL=$(shell which erl)
REBAR=$(shell which rebar || echo ./rebar)
APP=prflr
PA=./ebin ./deps/*/ebin

.PHONY: deps

all: deps compile

compile:
	@$(REBAR) compile
deps:
	@$(REBAR) get-deps
clean:
	@$(REBAR) clean
distclean: clean
	@$(REBAR) delete-deps
# Run a set of test.
test: clean all
	@$(ERL) -pa $(PA) -eval 'prflr_tests:test(), init:stop().' -noshell
doc: all
	@$(REBAR) doc

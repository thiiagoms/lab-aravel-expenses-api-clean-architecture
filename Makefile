.PHONY: test

test:
	rm -rf reports
	composer pint src tests
	composer coverage

UnityHTTPLogger
===============

A HTTP logger for Unity.
Originally used in my Medialogy project "A Shepherd's Tale" on 5th semester.

## Usage
There are four main classes:

- `LogAPI`
	- This classes takes care of all the networking and actual communication with the API.
- `Logger`
	- This class is the interface between `GameObject`s that wish to log a `LogEntry` and the `LogAPI` class.
- `Loggable`
	- This a MonoBehaviour that is able to log `LogEntry`s.
- `LogEntry`
	- A single entry in the log, consisting of some basic data as well as dynamic meta-data.

## Setup
- Rename reroute base in .htaccess


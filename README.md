# big-binary-files

Object orientated file access most suited for large data sets.
All read and write operations use file locking,
which always leaves the file in a valid state,
so there can be no concurrency issues.
Waiting for disc IO might seem slow but it's faster than reading large files to memory.

# test_cases.py
# This file stores the sample test cases used in the report and demo.
# Keeping the test cases here makes the program easier to organise.


# Sample processes for CPU scheduling
# Each process has:
# - pid: process ID
# - arrival: when the process arrives
# - burst: how much CPU time it needs
# - priority: lower number means higher priority
CPU_PROCESSES = [
    {"pid": "P1", "arrival": 0, "burst": 5, "priority": 2},
    {"pid": "P2", "arrival": 1, "burst": 3, "priority": 1},
    {"pid": "P3", "arrival": 2, "burst": 8, "priority": 3},
    {"pid": "P4", "arrival": 3, "burst": 6, "priority": 2}
]


# Time quantum used for Round Robin
TIME_QUANTUM = 3


# Context switch overhead used by all CPU scheduling algorithms
CONTEXT_SWITCH = 1


# Segment table for segmentation tests
# Each segment has:
# - base: starting physical address
# - bound: maximum valid size
# - direction: upward or downward
SEGMENT_TABLE = {
    0: {"base": 1000, "bound": 500, "direction": "upward"},
    1: {"base": 3000, "bound": 400, "direction": "downward"},
    2: {"base": 5000, "bound": 600, "direction": "upward"}
}


# Page size used for paging tests
PAGE_SIZE = 1024


# Page table for paging tests
# None means the page is not loaded, so accessing it causes a page fault
PAGE_TABLE = {
    0: 3,
    1: 7,
    2: 1,
    3: None
}
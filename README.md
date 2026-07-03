# CPU Scheduling and Memory Translation Simulator

This project is a simple educational Operating Systems simulator written in Python.

It demonstrates two main Operating Systems concepts:

1. CPU scheduling
2. Memory translation

## Features

### CPU Scheduling

The simulator implements three CPU scheduling algorithms:

- First Come, First Served
- Round Robin
- Priority Scheduling

The CPU scheduling output includes:

- Gantt chart
- Completion time
- Turnaround time
- Waiting time
- Average waiting time
- Average turnaround time

### Memory Translation

The simulator implements two memory translation techniques:

- Segmentation
- Paging

The segmentation part supports:

- Segment number
- Offset
- Base
- Bound
- Upward-growing segments
- Downward-growing stack segments
- Segmentation fault detection

The paging part supports:

- Page size
- Virtual address
- VPN calculation
- Offset calculation
- Page table lookup
- Physical address calculation
- Page fault detection

## Project Structure

```text
os-simulator-project/
│
├── main.py
├── scheduler.py
├── memory.py
├── test_cases.py
├── README.md



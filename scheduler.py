# scheduler.py
# This file contains the CPU scheduling algorithms:
# 1. First Come, First Served
# 2. Round Robin
# 3. Priority Scheduling


def copy_processes(processes):
    """
    This function creates a copy of the process list.

    This is important because Round Robin changes the remaining burst time.
    If we did not copy the processes, one algorithm could accidentally change
    the data used by another algorithm.
    """

    copied = []

    for p in processes:
        copied.append({
            "pid": p["pid"],
            "arrival": p["arrival"],
            "burst": p["burst"],
            "priority": p["priority"],

            # remaining is used mainly for Round Robin
            "remaining": p["burst"],

            # these values are calculated later
            "completion": 0,
            "turnaround": 0,
            "waiting": 0
        })

    return copied


def calculate_times(processes):
    """
    Calculates turnaround time, waiting time, and the averages.

    Turnaround Time = Completion Time - Arrival Time
    Waiting Time = Turnaround Time - Burst Time
    """

    total_waiting = 0
    total_turnaround = 0

    for p in processes:
        p["turnaround"] = p["completion"] - p["arrival"]
        p["waiting"] = p["turnaround"] - p["burst"]

        total_waiting += p["waiting"]
        total_turnaround += p["turnaround"]

    average_waiting = total_waiting / len(processes)
    average_turnaround = total_turnaround / len(processes)

    return average_waiting, average_turnaround


def print_gantt_chart(gantt):
    """
    Prints the Gantt chart.

    The Gantt chart shows when each process runs.
    Example:
    0 | P1 | 5 means P1 ran from time 0 to time 5.
    """

    print("\nGantt Chart:")

    for start, name, end in gantt:
        print(f"{start} | {name} | {end}", end="   ")

    print()


def print_result(title, processes, gantt, average_waiting, average_turnaround):
    """
    Prints the final result for one scheduling algorithm.
    This includes the Gantt chart, process table, and averages.
    """

    print("\n" + "=" * 70)
    print(title)
    print("=" * 70)

    print_gantt_chart(gantt)

    print("\nProcess Results:")
    print("PID\tArrival\tBurst\tPriority\tCompletion\tTurnaround\tWaiting")

    for p in processes:
        print(
            f"{p['pid']}\t{p['arrival']}\t{p['burst']}\t{p['priority']}\t\t"
            f"{p['completion']}\t\t{p['turnaround']}\t\t{p['waiting']}"
        )

    print(f"\nAverage Waiting Time: {average_waiting:.2f}")
    print(f"Average Turnaround Time: {average_turnaround:.2f}")


def fcfs(original_processes, context_switch):
    """
    First Come, First Served scheduling.

    The process that arrives first runs first.
    This algorithm is non-preemptive, so once a process starts,
    it runs until it finishes.
    """

    processes = copy_processes(original_processes)

    # Sort processes by arrival time
    processes.sort(key=lambda p: p["arrival"])

    time = 0
    gantt = []

    for i in range(len(processes)):
        p = processes[i]

        # If the CPU is free but the next process has not arrived yet,
        # the CPU stays idle.
        if time < p["arrival"]:
            gantt.append((time, "IDLE", p["arrival"]))
            time = p["arrival"]

        # Run the process until it finishes
        start = time
        time += p["burst"]
        p["completion"] = time

        # Add this process to the Gantt chart
        gantt.append((start, p["pid"], time))

        # Add context switch overhead between processes
        if i < len(processes) - 1 and context_switch > 0:
            gantt.append((time, "CS", time + context_switch))
            time += context_switch

    average_waiting, average_turnaround = calculate_times(processes)

    print_result(
        "First Come, First Served Scheduling",
        processes,
        gantt,
        average_waiting,
        average_turnaround
    )


def priority_scheduling(original_processes, context_switch):
    """
    Non-preemptive Priority Scheduling.

    The scheduler chooses the ready process with the highest priority.
    In this simulator, a lower priority number means higher priority.
    Example: priority 1 is higher than priority 3.
    """

    processes = copy_processes(original_processes)

    waiting_processes = processes.copy()
    finished_processes = []

    time = 0
    gantt = []

    while len(waiting_processes) > 0:
        ready = []

        # Find all processes that have already arrived
        for p in waiting_processes:
            if p["arrival"] <= time:
                ready.append(p)

        # If no process has arrived yet, the CPU is idle
        if len(ready) == 0:
            next_arrival = min(p["arrival"] for p in waiting_processes)
            gantt.append((time, "IDLE", next_arrival))
            time = next_arrival
            continue

        # Start by assuming the first ready process has the highest priority
        selected = ready[0]

        # Compare all ready processes and choose the one with the lowest priority number
        for p in ready:
            if p["priority"] < selected["priority"]:
                selected = p

            # If priority is the same, choose the process that arrived earlier
            elif p["priority"] == selected["priority"] and p["arrival"] < selected["arrival"]:
                selected = p

        # Run the selected process until it finishes
        start = time
        time += selected["burst"]
        selected["completion"] = time

        gantt.append((start, selected["pid"], time))

        waiting_processes.remove(selected)
        finished_processes.append(selected)

        # Add context switch overhead if there are still processes left
        if len(waiting_processes) > 0 and context_switch > 0:
            gantt.append((time, "CS", time + context_switch))
            time += context_switch

    # Sort results by process ID so the final table is easier to read
    finished_processes.sort(key=lambda p: p["pid"])

    average_waiting, average_turnaround = calculate_times(finished_processes)

    print_result(
        "Priority Scheduling",
        finished_processes,
        gantt,
        average_waiting,
        average_turnaround
    )


def add_arrived_processes(processes, ready_queue, next_index, time):
    """
    Adds processes to the ready queue when their arrival time has been reached.

    This function is used by Round Robin.
    """

    while next_index < len(processes) and processes[next_index]["arrival"] <= time:
        ready_queue.append(processes[next_index])
        next_index += 1

    return next_index


def round_robin(original_processes, quantum, context_switch):
    """
    Round Robin scheduling.

    Each process gets a fixed amount of CPU time called the time quantum.
    If the process does not finish during its quantum, it goes back to the queue.
    """

    processes = copy_processes(original_processes)

    # Sort by arrival time so processes enter the queue in the correct order
    processes.sort(key=lambda p: p["arrival"])

    time = 0
    next_index = 0
    completed = 0

    ready_queue = []
    finished_processes = []
    gantt = []

    # Add any processes that arrive at time 0
    next_index = add_arrived_processes(processes, ready_queue, next_index, time)

    while completed < len(processes):

        # If the ready queue is empty, jump to the next process arrival time
        if len(ready_queue) == 0:
            next_arrival = processes[next_index]["arrival"]
            gantt.append((time, "IDLE", next_arrival))
            time = next_arrival
            next_index = add_arrived_processes(processes, ready_queue, next_index, time)
            continue

        # Take the first process from the ready queue
        current = ready_queue.pop(0)

        # The process runs for either the full quantum or whatever time it has left
        if current["remaining"] > quantum:
            run_time = quantum
        else:
            run_time = current["remaining"]

        start = time
        time += run_time
        current["remaining"] -= run_time

        gantt.append((start, current["pid"], time))

        # Add any new processes that arrived while the current process was running
        next_index = add_arrived_processes(processes, ready_queue, next_index, time)

        # If the process is finished, record its completion time
        if current["remaining"] == 0:
            current["completion"] = time
            finished_processes.append(current)
            completed += 1

        # Otherwise, put it back at the end of the queue
        else:
            ready_queue.append(current)

        # Add context switch overhead if there are still unfinished processes
        if completed < len(processes) and context_switch > 0:
            gantt.append((time, "CS", time + context_switch))
            time += context_switch

            # New processes might arrive during context switch time
            next_index = add_arrived_processes(processes, ready_queue, next_index, time)

    finished_processes.sort(key=lambda p: p["pid"])

    average_waiting, average_turnaround = calculate_times(finished_processes)

    print_result(
        "Round Robin Scheduling",
        finished_processes,
        gantt,
        average_waiting,
        average_turnaround
    )
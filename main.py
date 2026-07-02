# main.py
# This is the main file that runs the simulator.
# It shows the menu and calls functions from the other files.

from scheduler import fcfs, round_robin, priority_scheduling
from memory import segmentation_translation, paging_translation
from test_cases import CPU_PROCESSES, TIME_QUANTUM, CONTEXT_SWITCH
from test_cases import SEGMENT_TABLE, PAGE_SIZE, PAGE_TABLE


def get_int(message):
    """
    Asks the user for a whole number.

    This prevents the program from crashing if the user types text
    instead of a number.
    """

    while True:
        try:
            value = int(input(message))
            return value
        except ValueError:
            print("Please enter a whole number.")


def run_sample_cpu_tests():
    """
    Runs the predefined CPU scheduling test case.

    This is useful for the report, screenshots, and presentation demo.
    """

    print("\nRunning sample CPU scheduling tests...")

    fcfs(CPU_PROCESSES, CONTEXT_SWITCH)
    round_robin(CPU_PROCESSES, TIME_QUANTUM, CONTEXT_SWITCH)
    priority_scheduling(CPU_PROCESSES, CONTEXT_SWITCH)


def enter_custom_cpu_data():
    """
    Allows the user to enter their own process data.
    Then all three CPU scheduling algorithms are run.
    """

    processes = []

    number = get_int("Number of processes: ")

    for i in range(number):
        print(f"\nProcess {i + 1}")

        pid = input("Process ID: ")
        arrival = get_int("Arrival Time: ")
        burst = get_int("Burst Time: ")
        priority = get_int("Priority: ")

        process = {
            "pid": pid,
            "arrival": arrival,
            "burst": burst,
            "priority": priority
        }

        processes.append(process)

    quantum = get_int("\nTime Quantum for Round Robin: ")
    context_switch = get_int("Context Switch Overhead: ")

    fcfs(processes, context_switch)
    round_robin(processes, quantum, context_switch)
    priority_scheduling(processes, context_switch)


def run_sample_memory_tests():
    """
    Runs the predefined memory translation examples.

    It includes:
    - valid segmentation example
    - downward stack segmentation example
    - segmentation fault example
    - valid paging example
    - page fault example
    """

    print("\nRunning sample memory translation tests...")

    segmentation_translation(SEGMENT_TABLE, 0, 200)
    segmentation_translation(SEGMENT_TABLE, 1, 100)
    segmentation_translation(SEGMENT_TABLE, 2, 700)

    paging_translation(PAGE_SIZE, 2500, PAGE_TABLE)
    paging_translation(PAGE_SIZE, 3500, PAGE_TABLE)


def enter_custom_segmentation_data():
    """
    Allows the user to enter their own segment table and translate one address.
    """

    segment_table = {}

    number = get_int("Number of segments: ")

    for i in range(number):
        print(f"\nSegment {i + 1}")

        segment_number = get_int("Segment Number: ")
        base = get_int("Base: ")
        bound = get_int("Bound: ")
        direction = input("Direction, upward or downward: ").lower()

        segment_table[segment_number] = {
            "base": base,
            "bound": bound,
            "direction": direction
        }

    segment_number = get_int("\nSegment number to translate: ")
    offset = get_int("Offset: ")

    segmentation_translation(segment_table, segment_number, offset)


def enter_custom_paging_data():
    """
    Allows the user to enter a page table and translate one virtual address.
    """

    page_size = get_int("Page Size: ")
    number = get_int("Number of page table entries: ")

    page_table = {}

    for i in range(number):
        print(f"\nPage Table Entry {i + 1}")

        vpn = get_int("VPN: ")
        loaded = input("Is this page loaded? yes/no: ").lower()

        if loaded == "yes":
            frame = get_int("Frame Number: ")
            page_table[vpn] = frame
        else:
            page_table[vpn] = None

    virtual_address = get_int("\nVirtual Address: ")

    paging_translation(page_size, virtual_address, page_table)


def main_menu():
    """
    Main menu of the simulator.

    The user chooses which part of the simulator to run.
    """

    while True:
        print("\n==============================")
        print("OS Simulator")
        print("==============================")
        print("1. Run sample CPU scheduling tests")
        print("2. Enter custom CPU scheduling data")
        print("3. Run sample memory translation tests")
        print("4. Enter custom segmentation data")
        print("5. Enter custom paging data")
        print("6. Exit")

        choice = input("Choose an option: ")

        if choice == "1":
            run_sample_cpu_tests()

        elif choice == "2":
            enter_custom_cpu_data()

        elif choice == "3":
            run_sample_memory_tests()

        elif choice == "4":
            enter_custom_segmentation_data()

        elif choice == "5":
            enter_custom_paging_data()

        elif choice == "6":
            print("Program ended.")
            break

        else:
            print("Invalid choice. Please try again.")


# This starts the program.
main_menu()
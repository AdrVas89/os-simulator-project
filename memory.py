# memory.py
# This file contains the memory translation functions:
# 1. Segmentation
# 2. Paging


def segmentation_translation(segment_table, segment_number, offset):
    """
    Translates a segmented address into a physical address.

    Inputs:
    - segment_table: stores base, bound, and direction for each segment
    - segment_number: the segment the user wants to access
    - offset: position inside the segment

    If the offset is outside the bound, a segmentation fault occurs.
    """

    print("\n" + "=" * 70)
    print("Segmentation Translation")
    print("=" * 70)

    # Check whether the segment exists
    if segment_number not in segment_table:
        print("Result: Segmentation fault")
        print("Reason: Segment number does not exist.")
        return

    segment = segment_table[segment_number]

    base = segment["base"]
    bound = segment["bound"]
    direction = segment["direction"]

    print(f"Segment Number: {segment_number}")
    print(f"Offset: {offset}")
    print(f"Base: {base}")
    print(f"Bound: {bound}")
    print(f"Direction: {direction}")

    # Check whether the offset is valid
    # Offset must be between 0 and bound - 1
    if offset < 0 or offset >= bound:
        print("Result: Segmentation fault")
        print("Reason: Offset is outside the segment bound.")
        return

    # For normal upward-growing segments, add the offset to the base
    if direction == "upward":
        physical_address = base + offset
        print(f"Calculation: {base} + {offset} = {physical_address}")

    # For downward-growing stack segments, subtract the offset from the base
    elif direction == "downward":
        physical_address = base - offset
        print(f"Calculation: {base} - {offset} = {physical_address}")

    else:
        print("Result: Invalid segment direction.")
        return

    print(f"Physical Address: {physical_address}")


def paging_translation(page_size, virtual_address, page_table):
    """
    Translates a virtual address into a physical address using paging.

    Steps:
    1. Calculate VPN.
    2. Calculate offset.
    3. Look up the VPN in the page table.
    4. If the page exists, calculate the physical address.
    5. If the page does not exist, report a page fault.
    """

    print("\n" + "=" * 70)
    print("Paging Translation")
    print("=" * 70)

    # The VPN tells us which virtual page contains the address
    vpn = virtual_address // page_size

    # The offset tells us the position inside that page
    offset = virtual_address % page_size

    print(f"Virtual Address: {virtual_address}")
    print(f"Page Size: {page_size}")
    print(f"VPN: {vpn}")
    print(f"Offset: {offset}")

    # If the VPN is missing or marked as None, the page is not loaded
    if vpn not in page_table or page_table[vpn] is None:
        print("Result: Page fault")
        print("Reason: VPN is not loaded in the page table.")
        return

    # Get the physical frame number from the page table
    frame = page_table[vpn]

    # Calculate the final physical address
    physical_address = frame * page_size + offset

    print(f"Frame Number: {frame}")
    print(f"Calculation: {frame} * {page_size} + {offset} = {physical_address}")
    print(f"Physical Address: {physical_address}")
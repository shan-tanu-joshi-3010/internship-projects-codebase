# analytics_service/time_utils.py

from datetime import datetime, timedelta

BUSINESS_START_HOUR = 9
BUSINESS_START_MINUTE = 30
BUSNESS_END_HOUR = 18
BUSINESS_END_MINUTE = 30


# ============================================================
# MAIN TIME FILTER BUILDER
# ============================================================

def build_time_filter(time_range):

    if not time_range:
        return None

    t_type = time_range.get("type")
    value = time_range.get("value")

    # ============================================================
    # RELATIVE TIME (e.g. 7d, 30d)
    # ============================================================
    if t_type == "relative" and isinstance(value, str) and value.endswith("d"):

        days = int(value.replace("d", ""))

        now = datetime.utcnow()
        start_time = now - timedelta(days=days)

        return {
            "range": {
                "processed_time": {
                    "gte": start_time.isoformat() + "Z",
                    "lte": now.isoformat() + "Z"
                }
            }
        }

    # ============================================================
    # ABSOLUTE TIME
    # ============================================================
    if t_type == "absolute" and isinstance(value, str):

        # --------------------------------------------------------
        # CASE 1: EXACT TIMESTAMP (e.g. 2026-02-16T09:30:00)
        # --------------------------------------------------------
        if "T" in value and "_to_" not in value:

            return {
                "term": {
                    "processed_time": value
                }
            }

        # --------------------------------------------------------
        # CASE 2: SINGLE DATE (e.g. 2026-02-16)
        # --------------------------------------------------------
        if "_to_" not in value:

            start = f"{value}T00:00:00Z"
            end = f"{value}T23:59:59Z"

            return {
                "range": {
                    "processed_time": {
                        "gte": start,
                        "lte": end
                    }
                }
            }

        # --------------------------------------------------------
        # CASE 3: DATE RANGE (e.g. 2026-02-01_to_2026-02-15)
        # --------------------------------------------------------
        if "_to_" in value:

            start, end = value.split("_to_")

            if "T" not in start:
                start = start + "T00:00:00Z"

            if "T" not in end:
                end = end + "T23:59:59Z"

            return {
                "range": {
                    "processed_time": {
                        "gte": start,
                        "lte": end
                    }
                }
            }

    return None


# ============================================================
# BUSINESS HOUR FILTER (OPTIONAL)
# ============================================================

def build_business_hour_filter():

    return {
        "script": {
            "script": {
                "source": """
                def hour = doc['processed_time'].value.getHour();
                def minute = doc['processed_time'].value.getMinute();

                boolean afterStart =
                    (hour > params.start_hour) ||
                    (hour == params.start_hour && minute >= params.start_min);

                boolean beforeEnd =
                    (hour < params.end_hour) ||
                    (hour == params.end_hour && minute <= params.end_min);

                return afterStart && beforeEnd;
                """,
                "params": {
                    "start_hour": BUSINESS_START_HOUR,
                    "start_min": BUSINESS_START_MINUTE,
                    "end_hour": BUSNESS_END_HOUR,
                    "end_min": BUSINESS_END_MINUTE
                }
            }
        }
    }

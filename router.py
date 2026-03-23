# analytics_service/router.py

from analytics_service.primitives.current import run as current_run
from analytics_service.primitives.trend import run as trend_run
from analytics_service.primitives.compare import run as compare_run

def execute(query_object, client):

    intent = query_object["intent"]

    if intent == "current":
        return current_run(query_object, client)

    if intent == "trend":
        return trend_run(query_object, client)

    if intent == "compare":
        return compare_run(query_object, client)

    raise ValueError("Unsupported intent")

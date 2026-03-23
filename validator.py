ALLOWED_INTENTS = ["current", "trend", "compare", "anomaly", "forecast", "analysis"]

ALLOWED_METRICS = ["latest", "average", "sum", "max", "min", "percent_change", "volatility"]

def validate_query_object(q):

    if not isinstance(q, dict):
        raise ValueError("Query object must be a dictionary.")

    # Required keys
    required_keys = [
        "intent",
        "metric",
        "software",
        "feature",
        "time_range",
        "aggregation_scope",
        "group_by",
        "comparison",
        "filters"
    ]

    for key in required_keys:
         if key not in q:
            q[key] = None


    if q["intent"] not in ALLOWED_INTENTS:
        raise ValueError(f"Invalid intent: {q['intent']}")

    if q["metric"] not in ALLOWED_METRICS:
        raise ValueError(f"Invalid metric: {q['metric']}")

    if not isinstance(q["software"], list):
        raise ValueError("software must be a list")

    if not isinstance(q["feature"], list):
        raise ValueError("feature must be a list")

    if not isinstance(q["time_range"], dict):
        raise ValueError("time_range must be an object")

    if "type" not in q["time_range"] or "value" not in q["time_range"]:
        raise ValueError("time_range must contain type and value")

    return q
